<?php
require_once 'db.php';
require_once 'session.php';

class HttpSety{
    private $db = null;
    private $sessionHandlerr = null;
    
    public function __construct(){
        $this->db = DB::getInstance();
        $this->sessionHandlerr = new SessionHandlerr();
        if(!$this->sessionHandlerr->checkWhosLoggedIn()){
            die("Sesja wygasła - proszę odświezyć stronę i zalogować się ponownie");
        }
    }
    
    function jsonp_decode($jsonp, $assoc = false) { // PHP 5.3 adds depth as third parameter to json_decode
        if($jsonp[0] !== '[' && $jsonp[0] !== '{') { // we have JSONP
            $jsonp = substr($jsonp, strpos($jsonp, '('));
        }
        return json_decode(trim($jsonp,'();'), $assoc);
    }
    
    public function mojeSety(){
        
        if(!isset($_REQUEST['strona']) || $_REQUEST['strona']=='' || !is_numeric($_REQUEST['strona']) || strpos($_REQUEST['strona'],".")!==false ){
            $strona = 1;
        }else{
            $strona = $_REQUEST['strona'];
        }
        
        $limit=10;
        $offset=($strona-1) * $limit;

        if(!isset($_REQUEST['desc']) || $_REQUEST['desc']==''){
            $desc = '';
        }else{
            $desc = 'desc';
        }
        
        if(!isset($_REQUEST['sort'])){
            $sort = 'nazwa '.$desc;
        }else if($_REQUEST['sort']=='data'){
            $sort = 'data_dod '.$desc;
        }else if($_REQUEST['sort']=='styl'){
            $sort = 'styl '.$desc;
        }else{
            $sort = 'nazwa '.$desc;
        }

        if(isset($_REQUEST['szukaj']) && $_REQUEST['szukaj']!=''){
            $sety = $this->db->mojeSetySzukej($_SESSION['user']['id'],$limit,$offset,$sort,$_REQUEST['szukaj']);//z szukajem
            $ile = $this->db->mojeSetyCountSzukej($_SESSION['user']['id'],$_REQUEST['szukaj']);
        }else{
            $sety = $this->db->mojeSety($_SESSION['user']['id'],$limit,$offset,$sort);
            $ile = $this->db->mojeSetyCount($_SESSION['user']['id']);
        }
        
        //$ile['ile'] = count($blendy);
        
        if($ile['ile'] % $limit > 0){
            $strony = floor($ile['ile']/$limit) + 1;
        }else{
            $strony = floor($ile['ile']/$limit);
        }
    
        $lista = array(
            'odp' => 'OK',
            'sety' => $sety,
            'ile' => $ile['ile'],
            'strony' => $strony,
            'strona' => $strona
        );
        print json_encode($lista);
    }
    
    public function set(){
        if(!isset($_REQUEST['idSetu']) || $_REQUEST['idSetu']=='0' || $_REQUEST['idSetu']==''){
            die('ERROR - no set id found in request parameters');
        }
        $set = $this->db->set($_REQUEST['idSetu'],$_SESSION['user']['id']);
        if(!$piosenki = $this->db->setPiosenki($_REQUEST['idSetu'])){
            $piosenki = array();
        }
        
        $retArr = array(
                    'odp' => 'OK',
                    'set' => $set,
                    'piosenki' => $piosenki
        );
        print json_encode($retArr);
    }
    
    public function nowySet(){
        $set = array(
            'id'=>'0',
            'nazwa'=>'',
            'styl'=>'',
            'link'=>'',
            'publiczny'=>'1'
        );
        
        $retArr = array(
                    'odp' => 'OK',
                    'set' => $set,
                    'piosenki' => array()
        );
        print json_encode($retArr);
    }
    
    public function cudzySet(){
        if(!isset($_REQUEST['idSetu']) || $_REQUEST['idSetu']=='0' || $_REQUEST['idSetu']==''){
            die('ERROR - no set id found in request parameters');
        }
        $set = $this->db->cudzySet($_REQUEST['idSetu'],$_SESSION['user']['id']);
        if(!$piosenki = $this->db->setPiosenki($_REQUEST['idSetu'])){
            $piosenki = array();
        }
        
        $retArr = array(
                    'odp' => 'OK',
                    'set' => $set,
                    'piosenki' => $piosenki
        );
        print json_encode($retArr);
    }
    
    public function zapiszSet(){
        if(!isset($_REQUEST['idSetu']) || $_REQUEST['idSetu']==''){//0 dodanie
            die('ERROR - no set id found in request parameters');
        }
        if(!isset($_REQUEST['nazwa']) || $_REQUEST['nazwa']==''){
            die('ERROR - no set name found in request parameters');
        }
        if(!isset($_REQUEST['styl']) || $_REQUEST['styl']==''){
            $styl='';
        }else{
            $styl=$_REQUEST['styl'];
        }
        if(!isset($_REQUEST['link']) || $_REQUEST['link']==''){
            $link='';
        }else{
            $link=$_REQUEST['link'];
            if(substr( $link, 0, 7 ) !== "http://" && substr( $link, 0, 8 ) !== "https://"){
                $link = 'http://'.$link;
            }
        }
        if(!isset($_REQUEST['publiczny']) || $_REQUEST['publiczny']=='0'){
            $publiczny='0';
        }else{
            $publiczny='1';
        }
        
        if(!isset($_REQUEST['piosenki']) || $_REQUEST['piosenki']=='' || $_REQUEST['piosenki']=='[]'){
            die('ERROR - please add some tracks to set');
        }
        
        
        
        $daneArr = array(
            'id'=>$_REQUEST['idSetu'],
            'nazwa'=>$_REQUEST['nazwa'],
            'link'=>$link,
            'styl'=>$styl,
            'publiczny'=>$publiczny,
            'id_uzytkownika'=>$_SESSION['user']['id']
        );
        
        $this->db->saveOrUpdateSet($daneArr);
        if($_REQUEST['idSetu']!='0'){
            $idSetu = $_REQUEST['idSetu'];
        }else{
            $idSetu = $this->db->getLastInsertId();
        }
        
        //sejwowanie piosenek w setach
        $piosnki = $this->jsonp_decode($_REQUEST['piosenki'],true);
        $ilePiosenek = count($piosnki);
        $i = 0;
        $przecinek = ',';
        $wartosci = array();
        $sql = 'insert into piosenki_w_setach (id_piosenki,id_setu,kolejnosc,opis) values ';
        foreach($piosnki as $p){
            $i++;
            if($i==$ilePiosenek){
                $przecinek = '';
            }
            $sql = $sql."(?,?,?,?)".$przecinek;
            $wartosci[]=$p['id'];
            $wartosci[]=$idSetu;
            $wartosci[]=$p['kolejnosc'];
            $wartosci[]=$p['opis'];
        }
        
        //echo $sql.implode($wartosci);
        $this->db->query("delete from piosenki_w_setach where id_setu=?",array($idSetu));
        $this->db->query($sql,$wartosci);
        //sejwowanie piosenek w setach - KONIEC
        
        $retArr = array(
                    'odp' => 'OK',
                    'idSetu' => $idSetu
        );
        print json_encode($retArr);
        die();
    }
    
    public function zaladujPiosenki(){
        if(!isset($_REQUEST['szukaj']) || $_REQUEST['szukaj']=='0' || $_REQUEST['szukaj']==''){
            die('ERROR - no search key found in request parameters');
        }
        if(strlen($_REQUEST['szukaj'])<4){
            die('ERROR - search key too short');
        }
        
        $piosenki = $this->db->szukajPiosenek($_REQUEST['szukaj'],$_SESSION['user']['id']);
        
        $retArr = array(
                    'odp' => 'OK',
                    'piosenki' => $piosenki
        );
        print json_encode($retArr);
        die();
    }
    
    public function blendPiosenki(){
        if(!isset($_REQUEST['idPiosenki']) || $_REQUEST['idPiosenki']=='0' || $_REQUEST['idPiosenki']==''){
            die('ERROR - no last track id found in request parameters');
        }
        
        $piosenki = $this->db->blendPiosenki($_REQUEST['idPiosenki'],$_SESSION['user']['id']);//to zmienic... czemu? chyba zapomnialem odkomentowac
        
        $retArr = array(
                    'odp' => 'OK',
                    'piosenki' => $piosenki
        );
        print json_encode($retArr);
        die();
    }
    
    public function usunSet(){
        if(!isset($_REQUEST['idSetu']) || $_REQUEST['idSetu']=='0' || $_REQUEST['idSetu']==''){
            die('ERROR - no set id found in request parameters');
        }
        $this->db->usunSet($_REQUEST['idSetu']);
        $retArr = array(
            'odp'=>"OK"
        );
        print json_encode($retArr);
        die();
    }
    
    public function togglePubliczny(){
        if(!isset($_REQUEST['idSetu']) || $_REQUEST['idSetu']=='0' || $_REQUEST['idSetu']==''){
            die('ERROR - no set id found in request parameters');
        }
        
        $this->db->togglePublicznySet($_REQUEST['idSetu']);
        
        $retArr = array(
            'odp'=>"OK"
        );
        print json_encode($retArr);
        die();
    }
    
    public function cudzeSety(){
        
        if(!isset($_REQUEST['strona']) || $_REQUEST['strona']=='' || !is_numeric($_REQUEST['strona']) || strpos($_REQUEST['strona'],".")!==false ){
            $strona = 1;
        }else{
            $strona = $_REQUEST['strona'];
        }
        
        $limit=10;
        $offset=($strona-1) * $limit;

        if(!isset($_REQUEST['desc']) || $_REQUEST['desc']==''){
            $desc = '';
        }else{
            $desc = 'desc';
        }
        
        if(!isset($_REQUEST['sort'])){
            $sort = 'nazwa '.$desc;
        }else if($_REQUEST['sort']=='data'){
            $sort = 'data_dod '.$desc;
        }else if($_REQUEST['sort']=='styl'){
            $sort = 'styl '.$desc;
        }else{
            $sort = 'nazwa '.$desc;
        }

        if(isset($_REQUEST['szukaj']) && $_REQUEST['szukaj']!=''){
            $sety = $this->db->cudzeSetySzukej($_SESSION['user']['id'],$limit,$offset,$sort,$_REQUEST['szukaj']);//z szukajem
            $ile = $this->db->cudzeSetyCountSzukej($_REQUEST['szukaj']);
        }else{
            $sety = $this->db->cudzeSety($_SESSION['user']['id'],$limit,$offset,$sort);
            $ile = $this->db->cudzeSetyCount();
        }
        
        //$ile['ile'] = count($blendy);
        
        if($ile['ile'] % $limit > 0){
            $strony = floor($ile['ile']/$limit) + 1;
        }else{
            $strony = floor($ile['ile']/$limit);
        }
    
        $lista = array(
            'odp' => 'OK',
            'sety' => $sety,
            'ile' => $ile['ile'],
            'strony' => $strony,
            'strona' => $strona
        );
        print json_encode($lista);
    }
    
    public function likeIt(){
        if(!isset($_REQUEST['idSetu']) || $_REQUEST['idSetu']=='0' || $_REQUEST['idSetu']==''){
            die('ERROR - no set id found in request parameters');
        }
        
        $idLapki = '0';
        $lapka = 'gora';
        
        if($ocena = $this->db->getLapkaS($_REQUEST['idSetu'],$_SESSION['user']['id'])){
            $idLapki = $ocena['id'];
            if($ocena['lapka']=='gora'){
                $lapka = 'dol';
            }
        }
        
        $lapkaArr = array(
            'id'=>$idLapki,
            'lapka'=>$lapka,
            'id_setu'=>$_REQUEST['idSetu'],
            'id_uzytkownika'=>$_SESSION['user']['id']
        );
        
        $this->db->saveOrUpdateLapkaS($lapkaArr);
        $retArr = array(
            'odp'=>"OK"
        );
        print json_encode($retArr);
        die();
    }
    
    public function dislikeIt(){
        if(!isset($_REQUEST['idSetu']) || $_REQUEST['idSetu']=='0' || $_REQUEST['idSetu']==''){
            die('ERROR - no set id found in request parameters');
        }
        $idLapki = '0';
        $lapka = 'dol';
        if($ocena = $this->db->getLapkaS($_REQUEST['idSetu'],$_SESSION['user']['id'])){
            $idLapki = $ocena['id'];
            if($ocena['lapka']=='dol'){
                $lapka = 'gora';
            }
        }
        
        $lapkaArr = array(
            'id'=>$idLapki,
            'lapka'=>$lapka,
            'id_setu'=>$_REQUEST['idSetu'],
            'id_uzytkownika'=>$_SESSION['user']['id']
        );
        
        $this->db->saveOrUpdateLapkaS($lapkaArr);
        
        $retArr = array(
            'odp'=>"OK"
        );
        print json_encode($retArr);
        die();
    }
    
}


try{
    if(isset($_REQUEST['akcja'])){
        
        $httpSety = new HttpSety();
        
        switch($_REQUEST['akcja']){
                
            case 'mojeSety':
                $httpSety->mojeSety();
                break;
                
            case 'set':
                $httpSety->set();
                break;
                
            case 'nowySet':
                $httpSety->nowySet();
                break;
                
            case 'zaladujPiosenki':
                $httpSety->zaladujPiosenki();
                break;
                
            case 'usunSet':
                $httpSety->usunSet();
                break;

            case 'zapiszSet':
                $httpSety->zapiszSet();
                break;
                
            case 'togglePubliczny':
                $httpSety->togglePubliczny();
                break;
                
            case 'blendPiosenki':
                $httpSety->blendPiosenki();
                break;
                
            case 'cudzeSety':
                $httpSety->cudzeSety();
                break;
                
            case 'cudzySet':
                $httpSety->cudzySet();
                break;
                
            case 'likeIt':
                $httpSety->likeIt();
                break;
                
            case 'dislikeIt':
                $httpSety->dislikeIt();
                break;
                
            default:
               die("Incorrent request parameters");
        }
    }else{
        die('ajaxem!!!');
    }
}catch(Exception $e){
    die($e->getMessage());
}
?> 
 
