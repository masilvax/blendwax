<?php
require_once 'db.php';
require_once 'session.php';
require_once '../oauth/OAuthSimple.php';

class HttpImport{
    private $db = null;
    private $sessionHandlerr = null;
    private $oauthObject = null;
    private $signatures = null;
    private $doImportu = null;
    
    public function __construct(){

        $this->sessionHandlerr = new SessionHandlerr();
        if(!$this->sessionHandlerr->checkWhosLoggedIn()){
            die("Sesja wygasła - proszę odświezyć stronę i zalogować się ponownie");
        }
        $this->db = DB::getInstance();
        $this->oauthObject = new OAuthSimple();

        $this->signatures = array( 'consumer_key' => 'HImaFJAFPsapXobmbqzh',
                            'shared_secret' => 'suzJDrNBhkdRVTMCaWQHmTayDKeSlqFQ',
                            'oauth_token' => $_SESSION['user']['access_token'],
                            'oauth_secret' => $_SESSION['user']['access_token_secret']
                            );
                            
        $this->doImportu = array();

    }
    
    private function jsonp_decode($jsonp, $assoc = false) { // PHP 5.3 adds depth as third parameter to json_decode
        if($jsonp[0] !== '[' && $jsonp[0] !== '{') { // we have JSONP
            $jsonp = substr($jsonp, strpos($jsonp, '('));
        }
        return json_decode(trim($jsonp,'();'), $assoc);
    }

    
    /**
    * Pobiera kolekcję i obrazki bez importu piosenek
    *
    * sprawdza czy jest obrazek, jak nie to pobiera i zapisuje z nazwa discogs_id (release id)
    * release ids, których nie ma w BlendWax wrzuca do $doImportu, który potem jest brany w js i każdy release id jest
    * pobierany z Discogsa. Te, które są w BlendWax po prsotu są wrzucane do kolekcji usera.
    * Leci stronami i się wywołuje rekurencyjnie (per_page=50 domyślnie)
    *
    */
    private function importAlbumy($strona = 1){

        $this->oauthObject->reset();
        
        $result = $this->oauthObject->sign(array(
                'path'      =>'https://api.discogs.com/users/'.$_SESSION['user']['login'].'/collection/folders/0/releases',
                //'https://api.discogs.com/users/oneofguests/collection/folders/0/releases?per_page=50&page=4',
                'parameters'=> array('per_page' => '50','page'=>$strona),
                'signatures'=> $this->signatures));
                
                
            
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_URL, $result['signed_url']);
            
        curl_setopt($ch, CURLOPT_USERAGENT, "BlendWax-1.0");

        $r = curl_exec($ch);
        curl_close($ch);

        $releasesArr = $this->jsonp_decode($r, true);
        
/*        echo "---- ".$releasesArr['pagination']['page']." -----";
        print "<pre><code>";
        print_r($releasesArr);
        print "</code></pre>";
        print "----------------------------------<br/>";
        */
        
        if(!isset($releasesArr['message'])){
            foreach($releasesArr['releases'] as $r){
            //echo "<br/>wchodze w:".$r['id'];
                if(isset($r['basic_information']['formats'][0]['name']) && $r['basic_information']['formats'][0]['name']=='Vinyl'){
                    
                        //zapisywanie obrazka
                        //SPRAWDZENIE NAJPIERW CZY JEST TEN OBRAZEK
                        if(!file_exists('/gfx/'.$r['id'].'.jpg') || filesize('/gfx/'.$r['id'].'.jpg')==0){
                            $ch = curl_init($r['basic_information']['cover_image']);
                            $fp = fopen('/gfx/'.$r['id'].'.jpg', 'wb');
                            curl_setopt($ch, CURLOPT_FILE, $fp);
                            curl_setopt($ch, CURLOPT_HEADER, 0);
                            curl_setopt($ch, CURLOPT_USERAGENT, "BlendWax-1.0");
                            curl_exec($ch);
                            curl_close($ch);
                            fclose($fp);
                        }
                    
                    
                    if(!$album = $this->db->getAlbumByDiscogsId($r['id'])){
                        //trzeba zaimportowac z discogsa
                        //wrzucic do arraya prywatnego, a potem tego arraya w innej funkcji zwrocic
                        //za wczasu sciagnac obrazek!!!
                        //echo "<br/>dodaje do importu:".$r['basic_information']['formats']['cover_image'];
                        

                        
                        $this->doImportu[]=$r['id'];
                    }else{
                        //jak jest juz w bazie album to go dodac do kolekcji usera
                        $this->db->dodajAlbumDoKolekcjiUzytkownika($_SESSION['user']['id'],$album['id']);
                    }
                }
            }
            
            if($releasesArr['pagination']['pages']>1 && $strona < $releasesArr['pagination']['pages']){
                $strona++;

                $this->importAlbumy($strona);
                if($strona>$releasesArr['pagination']['pages']){
                    die("ERROR - too much pages");
                }
            }
        }else{

            die($releasesArr['message']);

        }
                
        

    }
    
    
    /**
    * Importuje pojedynczego releasa do BlendWax-a zapisując (jeśli nie ma) labela, artystę, album, piosenki
    *
    */
    public function importPiosenek(){    

        if(!isset($_REQUEST['idAlbumuDiscogs']) || $_REQUEST['idAlbumuDiscogs']=='0' || $_REQUEST['idAlbumuDiscogs']==''){
            die('ERROR - no album id found in request parameters');
        }
    
        $this->oauthObject->reset();
        $result = $this->oauthObject->sign(array(
                'path'      =>'https://api.discogs.com/releases/'.$_REQUEST['idAlbumuDiscogs'],
                //'https://api.discogs.com/users/oneofguests/collection/folders/0/releases?per_page=50&page=4',
                //'parameters'=> array('per_page' => '50','page'=>$strona),
                'signatures'=> $this->signatures));
            
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_URL, $result['signed_url']);
            
        curl_setopt($ch, CURLOPT_USERAGENT, "BlendWax-1.0");

        $r = curl_exec($ch);
        curl_close($ch);

        $release = $this->jsonp_decode($r, true);
        
        if(isset($release['message'])){
                
            die('ERROR - '.$release['message']);
                
        }else{
                
            if($artysta = $this->db->getArtystaByNazwa($release['artists_sort'])){
                $idArtysty = $artysta['id'];
            }else{
                $artystaArr = array(
                        'id'=>'0'
                        ,'nazwa'=>$release['artists_sort']
                        );
                $this->db->saveOrUpdateArtysta($artystaArr);
                $idArtysty = $this->db->getLastInsertId();
            }
                
                
            if($label = $this->db->getLabelByDiscogsId($release['labels'][0]['id'])){
                $idLabela = $label['id'];
            }elseif($label = $this->db->getLabelByNazwa($release['labels'][0]['name'])){
                $idLabela = $label['id'];
            }else{
                $labelArr = array(
                        'id'=>'0',
                        'nazwa'=>$release['labels'][0]['name'],
                        'discogs_id'=>$release['labels'][0]['id']
                        );
                $this->db->saveOrUpdateLabel($labelArr);
                $idLabela = $this->db->getLastInsertId();
            }
                
            if(!$this->db->getAlbumByDiscogsId($release['id'])){
                $albumArr = array(
                        'id'=>'0',
                        'tytul'=>$release['title'],
                        'rok'=>$release['year'],
                        'id_artysty'=>$idArtysty,
                        'id_labela'=>$idLabela,
                        'discogs_id'=>$release['id'],
                        'nr_kat'=>$release['labels'][0]['catno']
                );
                $this->db->saveOrUpdateAlbum($albumArr);
                $idAlbumu = $this->db->getLastInsertId();
                    


                foreach ($release['tracklist'] as $t){
                    $piosenkaArr = array(
                            'id'=>'0',
                            'tytul'=>$t['title'],
                            'pozycja'=>$t['position'],
                            'id_albumu'=>$idAlbumu
                    );
                    $this->db->saveOrUpdatePiosenka($piosenkaArr);
                }
            }
            $this->db->dodajAlbumDoKolekcjiUzytkownika($_SESSION['user']['id'],$idAlbumu);//jak juz ma to nie doda
            sleep(1);//moze będzie można zmienic jak będzie oauth
            die($release['artists_sort'].' - '.$release['title']);
        }

        
    }
    
    /**
    * Uruchamia importAlbumy i printuje listę id releasow, które trzeba wciągnąć do BlendWax-a
    *
    */
    public function pobierzAlbumyDoWciagniecia(){
        //echo"chuj";
        $this->importAlbumy();
        $retArr = array(
            'odp'=>'OK',
            'doImportu' => $this->doImportu
        );
        print json_encode($retArr);
    }
    
    public function test(){
        $this->oauthObject->reset();
        
        $result = $this->oauthObject->sign(array(
                'path'      =>'https://api.discogs.com/users/'.$_SESSION['user']['login'].'/collection/folders/0/releases',
                //'https://api.discogs.com/users/oneofguests/collection/folders/0/releases?per_page=50&page=4',
                'parameters'=> array('per_page' => '50','page'=>$strona),
                'signatures'=> $this->signatures));
                
        die($result['signed_url']);
    }
    
}


try{
    if(isset($_REQUEST['akcja'])){
        
        $httpImport = new HttpImport();
        
        switch($_REQUEST['akcja']){
                
            case 'pobierzAlbumyDoWciagniecia':
                $httpImport->pobierzAlbumyDoWciagniecia();
                break;
                
            /*case 'importAlbumy':
                $httpImport->importAlbumy();
                break;*/
                
            case 'importPiosenek':
                $httpImport->importPiosenek();
                break;
                
            case 'pincet':
                $httpImport->test();
                break;
                
            default:
               die("Incorrect request pramaters");
        }
    }else{
        die('ajaxem!!!');
    }
}catch(Exception $e){
    die($e->getMessage());
}

/*
require '../oauth/OAuthSimple.php';
//require '../core/db.php';
$oauthObject = new OAuthSimple();
//$db = DB::getInstance();
session_start();

function jsonp_decode($jsonp, $assoc = false) { // PHP 5.3 adds depth as third parameter to json_decode
    if($jsonp[0] !== '[' && $jsonp[0] !== '{') { // we have JSONP
       $jsonp = substr($jsonp, strpos($jsonp, '('));
    }
    return json_decode(trim($jsonp,'();'), $assoc);
}

$signatures = array( 'consumer_key'     => 'HImaFJAFPsapXobmbqzh',
                     'shared_secret'    => 'suzJDrNBhkdRVTMCaWQHmTayDKeSlqFQ');

$signatures['oauth_token'] = $_SESSION['user']['access_token'];
$signatures['oauth_secret'] = $_SESSION['user']['access_token_secret'];

$oauthObject->reset();
$result = $oauthObject->sign(array(
        'path'      =>'https://api.discogs.com/users/'.$_SESSION['user']['login'].'/collection/folders/0/releases',
        //'https://api.discogs.com/users/oneofguests/collection/folders/0/releases?per_page=50&page=4',
        'parameters'=> array('per_page' => '50','page'=>'3'),
        'signatures'=> $signatures));
    
$ch = curl_init();
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_URL, $result['signed_url']);
    
curl_setopt($ch, CURLOPT_USERAGENT, "BlendWax-1.0");

$r = curl_exec($ch);
curl_close($ch);

$dyskoxUserArr = jsonp_decode($r, true);
        
print "<pre><code>";
print_r($dyskoxUserArr);
print "</code></pre>";
*/
?>
 
