<?php
require_once 'db.php';
require_once 'session.php';

class HttpKolekcja{
    private $db = null;
    private $sessionHandlerr = null;
    
    public function __construct(){
        $this->db = DB::getInstance();
        $this->sessionHandlerr = new SessionHandlerr();
        if(!$this->sessionHandlerr->checkWhosLoggedIn()){
            die("Sesja wygasła - proszę odświezyć stronę i zalogować się ponownie");
        }
    }
    
    public function mojaKolekcja(){

        if(!isset($_REQUEST['strona']) || $_REQUEST['strona']=='' || !is_numeric($_REQUEST['strona']) || strpos($_REQUEST['strona'],".")!==false ){
            $strona = 1;
        }else{
            $strona = $_REQUEST['strona'];
        }
        
        $limit=30;
        $offset=($strona-1) * $limit;

        if(!isset($_REQUEST['desc']) || $_REQUEST['desc']==''){
            $desc = '';
        }else{
            $desc = 'desc';
        }
        
        if(!isset($_REQUEST['sort'])){
            $sort = 'artysta '.$desc.', tytul '.$desc.', label '.$desc;
        }else if($_REQUEST['sort']=='label'){
            $sort = 'label '.$desc.', nr_kat '.$desc.', artysta '.$desc.', tytul '.$desc;
        }else if($_REQUEST['sort']=='title'){
            $sort = 'tytul '.$desc.', artysta '.$desc.', label '.$desc;
        }else{
            $sort = 'artysta '.$desc.', tytul '.$desc.', label '.$desc;
        }

        if(isset($_REQUEST['szukaj']) && $_REQUEST['szukaj']!=''){
            $kolekcja = $this->db->mojaKolekcjaSzukej($_SESSION['user']['id'],$limit,$offset,$sort,$_REQUEST['szukaj']);//z szukajem
            $ile = $this->db->mojaKolekcjaCountSzukej($_SESSION['user']['id'],$_REQUEST['szukaj']);
        }else{
            $kolekcja = $this->db->mojaKolekcja($_SESSION['user']['id'],$limit,$offset,$sort);
            $ile = $this->db->mojaKolekcjaCount($_SESSION['user']['id']);
        }
        
        //musi byc ile z bazy bo jak jest paginacja to count($kolekcja) policzy tyle ile ma $limit
        if($ile['ile'] % $limit > 0){
            $strony = floor($ile['ile']/$limit) + 1;
        }else{
            $strony = floor($ile['ile']/$limit);
        }
    
        $lista = array(
            'odp' => 'OK',
            'albumy' => $kolekcja,
            'ile' => $ile['ile'],
            'strony' => $strony,
            'strona' => $strona
        );
        print json_encode($lista);
    }
    
    public function usun(){
        if(!isset($_REQUEST['idAlbumu']) || $_REQUEST['idAlbumu']=='0' || $_REQUEST['idAlbumu']==''){
            die('ERROR - no release id found in request parameters');
        }
        $this->db->usunAlbumZKolekcjiUzytkownika($_SESSION['user']['id'],$_REQUEST['idAlbumu']);
        $retArr = array(
            'odp'=>"OK"
        );
        print json_encode($retArr);
        die();
    }
    
}


try{
    if(isset($_REQUEST['akcja'])){
        
        $httpKolekcja = new HttpKolekcja();
        
        switch($_REQUEST['akcja']){
                
            case 'myCollection':
                $httpKolekcja->mojaKolekcja();
                break;
                
            case 'usun':
                $httpKolekcja->usun();
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
?>