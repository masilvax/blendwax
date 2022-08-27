<?php
require_once 'db.php';
require_once 'session.php';

class HttpBlendy{
    private $db = null;
    private $sessionHandlerr = null;
    
    public function __construct(){
        $this->db = DB::getInstance();
        $this->sessionHandlerr = new SessionHandlerr();
        if(!$this->sessionHandlerr->checkWhosLoggedIn()){
            die("Sesja wygasła - proszę odświezyć stronę i zalogować się ponownie");
        }
    }
    
    public function mojeBlendy(){
        /*$lista = array(
            'odp' => 'OK',
            'blendy' => $this->db->mojeBlendy($_SESSION['user']['id'])
        );
        print json_encode($lista);*/
////-------------------------------------------------------///
        
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
            $sort = 'artysta1 '.$desc.', album1 '.$desc.', piosenka1 '.$desc.', artysta2 '.$desc.', album2 '.$desc.', piosenka2 '.$desc;
        }else if($_REQUEST['sort']=='artysta2'){
            $sort = 'artysta2 '.$desc.', album2 '.$desc.', piosenka2 '.$desc.', artysta1 '.$desc.', album1 '.$desc.', piosenka1 '.$desc;
        }else if($_REQUEST['sort']=='album1'){
            $sort = 'album1 '.$desc.', artysta1 '.$desc.', piosenka1 '.$desc.', album2 '.$desc.', artysta2 '.$desc.', piosenka2 '.$desc;
        }else if($_REQUEST['sort']=='album2'){
            $sort = 'album2 '.$desc.', artysta2 '.$desc.', piosenka2 '.$desc.', album1 '.$desc.', artysta1 '.$desc.', piosenka1 '.$desc;
        }else if($_REQUEST['sort']=='piosenka1'){
            $sort = 'piosenka1 '.$desc.', artysta1 '.$desc.', album1 '.$desc.', piosenka2 '.$desc.', artysta2 '.$desc.', album2 '.$desc;
        }else if($_REQUEST['sort']=='piosenka2'){
            $sort = 'piosenka2 '.$desc.', artysta2 '.$desc.', album2 '.$desc.', piosenka1 '.$desc.', artysta1 '.$desc.', album1 '.$desc;
        }else{
            $sort = 'artysta1 '.$desc.', album1 '.$desc.', piosenka1 '.$desc.', artysta2 '.$desc.', album2 '.$desc.', piosenka2 '.$desc;
        }

        if(isset($_REQUEST['szukaj']) && $_REQUEST['szukaj']!=''){
            $blendy = $this->db->mojeBlendySzukej($_SESSION['user']['id'],$limit,$offset,$sort,$_REQUEST['szukaj']);//z szukajem
            $ile = $this->db->mojeBlendyCountSzukej($_SESSION['user']['id'],$_REQUEST['szukaj']);
        }else{
            $blendy = $this->db->mojeBlendy($_SESSION['user']['id'],$limit,$offset,$sort);
            $ile = $this->db->mojeBlendyCount($_SESSION['user']['id']);
        }
        
        //$ile['ile'] = count($blendy);
        
        if($ile['ile'] % $limit > 0){
            $strony = floor($ile['ile']/$limit) + 1;
        }else{
            $strony = floor($ile['ile']/$limit);
        }
    
        $lista = array(
            'odp' => 'OK',
            'blendy' => $blendy,
            'ile' => $ile['ile'],
            'strony' => $strony,
            'strona' => $strona
        );
        print json_encode($lista);
    }
    
    public function blend(){
        if(!isset($_REQUEST['idBlendu']) || $_REQUEST['idBlendu']=='0' || $_REQUEST['idBlendu']==''){
            die('ERROR - no blend id found in request parameters');
        }
        $blend = $this->db->blend($_REQUEST['idBlendu'],$_SESSION['user']['id']);
        
        $retArr = array(
                    'odp' => 'OK',
                    'blend' => $blend
        );
        print json_encode($retArr);
    }
    
    public function nowyBlend(){
        $blend = array(
            'id'=>'0',
            'id_piosenki1'=>'0',
            'id_piosenki2'=>'0',
            'opis'=>'',
            'publiczny'=>'1'
        );
        
        $retArr = array(
                    'odp' => 'OK',
                    'blend' => $blend
        );
        print json_encode($retArr);
    }
    
    public function zapiszBlend(){
        if(!isset($_REQUEST['idBlendu']) || $_REQUEST['idBlendu']==''){//0 dodanie
            die('ERROR - no blend id found in request parameters');
        }
        if(!isset($_REQUEST['idPiosenki1']) || $_REQUEST['idPiosenki1']=='0' || $_REQUEST['idPiosenki1']==''){
            die('ERROR - no first track id found in request parameters');
        }
        if(!isset($_REQUEST['idPiosenki2']) || $_REQUEST['idPiosenki2']=='0' || $_REQUEST['idPiosenki2']==''){
            die('ERROR - no second track id found in request parameters');
        }
        if(!isset($_REQUEST['opis']) || $_REQUEST['opis']==''){
            $opis='';
        }else{
            $opis=$_REQUEST['opis'];
        }
        if(!isset($_REQUEST['publiczny']) || $_REQUEST['publiczny']=='0'){
            $publiczny='0';
        }else{
            $publiczny='1';
        }
        
        $daneArr = array(
            'id'=>$_REQUEST['idBlendu'],
            'id_piosenki1'=>$_REQUEST['idPiosenki1'],
            'id_piosenki2'=>$_REQUEST['idPiosenki2'],
            'opis'=>$opis,
            'publiczny'=>$publiczny,
            'id_uzytkownika'=>$_SESSION['user']['id']
        );
        
        $this->db->saveOrUpdateBlend($daneArr);
        if($_REQUEST['idBlendu']!='0'){
            $idBlendu = $_REQUEST['idBlendu'];
        }else{
            $idBlendu = $this->db->getLastInsertId();
        }
        $retArr = array(
                    'odp' => 'OK',
                    'idBlendu' => $idBlendu
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
    
    public function usunBlend(){
        if(!isset($_REQUEST['idBlendu']) || $_REQUEST['idBlendu']=='0' || $_REQUEST['idBlendu']==''){
            die('ERROR - no blend id found in request parameters');
        }
        $this->db->usunBlend($_REQUEST['idBlendu']);
        $retArr = array(
            'odp'=>"OK"
        );
        print json_encode($retArr);
        die();
    }
    
    public function togglePubliczny(){
        if(!isset($_REQUEST['idBlendu']) || $_REQUEST['idBlendu']=='0' || $_REQUEST['idBlendu']==''){
            die('ERROR - no blend id found in request parameters');
        }
        
        $this->db->togglePubliczny($_REQUEST['idBlendu']);
        
        $retArr = array(
            'odp'=>"OK"
        );
        print json_encode($retArr);
        die();
    }
    
    public function cudzeBlendy(){
        
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
            $sort = 'artysta1 '.$desc.', album1 '.$desc.', piosenka1 '.$desc.', artysta2 '.$desc.', album2 '.$desc.', piosenka2 '.$desc;
        }else if($_REQUEST['sort']=='artysta2'){
            $sort = 'artysta2 '.$desc.', album2 '.$desc.', piosenka2 '.$desc.', artysta1 '.$desc.', album1 '.$desc.', piosenka1 '.$desc;
        }else if($_REQUEST['sort']=='album1'){
            $sort = 'album1 '.$desc.', artysta1 '.$desc.', piosenka1 '.$desc.', album2 '.$desc.', artysta2 '.$desc.', piosenka2 '.$desc;
        }else if($_REQUEST['sort']=='album2'){
            $sort = 'album2 '.$desc.', artysta2 '.$desc.', piosenka2 '.$desc.', album1 '.$desc.', artysta1 '.$desc.', piosenka1 '.$desc;
        }else if($_REQUEST['sort']=='piosenka1'){
            $sort = 'piosenka1 '.$desc.', artysta1 '.$desc.', album1 '.$desc.', piosenka2 '.$desc.', artysta2 '.$desc.', album2 '.$desc;
        }else if($_REQUEST['sort']=='piosenka2'){
            $sort = 'piosenka2 '.$desc.', artysta2 '.$desc.', album2 '.$desc.', piosenka1 '.$desc.', artysta1 '.$desc.', album1 '.$desc;
        }else{
            $sort = 'artysta1 '.$desc.', album1 '.$desc.', piosenka1 '.$desc.', artysta2 '.$desc.', album2 '.$desc.', piosenka2 '.$desc;
        }

        if(isset($_REQUEST['szukaj']) && $_REQUEST['szukaj']!=''){
            $blendy = $this->db->cudzeBlendySzukej($_SESSION['user']['id'],$limit,$offset,$sort,$_REQUEST['szukaj']);//z szukajem
            $ile = $this->db->cudzeBlendyCountSzukej($_SESSION['user']['id'],$_REQUEST['szukaj']);
        }else{
            $blendy = $this->db->cudzeBlendy($_SESSION['user']['id'],$limit,$offset,$sort);
            $ile = $this->db->cudzeBlendyCount($_SESSION['user']['id']);
        }
        
        //$ile['ile'] = count($blendy);
        
        if($ile['ile'] % $limit > 0){
            $strony = floor($ile['ile']/$limit) + 1;
        }else{
            $strony = floor($ile['ile']/$limit);
        }
    
        $lista = array(
            'odp' => 'OK',
            'blendy' => $blendy,
            'ile' => $ile['ile'],
            'strony' => $strony,
            'strona' => $strona
        );
        print json_encode($lista);
    }
    
    public function cudzyBlend(){
        if(!isset($_REQUEST['idBlendu']) || $_REQUEST['idBlendu']=='0' || $_REQUEST['idBlendu']==''){
            die('ERROR - no blend id found in request parameters');
        }
        $blend = $this->db->cudzyBlend($_REQUEST['idBlendu'],$_SESSION['user']['id']);
        
        $retArr = array(
                    'odp' => 'OK',
                    'blend' => $blend
        );
        print json_encode($retArr);
    }
    
    public function likeIt(){
        if(!isset($_REQUEST['idBlendu']) || $_REQUEST['idBlendu']=='0' || $_REQUEST['idBlendu']==''){
            die('ERROR - no blend id found in request parameters');
        }
        
        $idLapki = '0';
        $lapka = 'gora';
        
        if($ocena = $this->db->getLapkaB($_REQUEST['idBlendu'],$_SESSION['user']['id'])){
            $idLapki = $ocena['id'];
            if($ocena['lapka']=='gora'){
                $lapka = 'dol';
            }
        }
        
        $lapkaArr = array(
            'id'=>$idLapki,
            'lapka'=>$lapka,
            'id_blendu'=>$_REQUEST['idBlendu'],
            'id_uzytkownika'=>$_SESSION['user']['id']
        );
        
        $this->db->saveOrUpdateLapkaB($lapkaArr);
        $retArr = array(
            'odp'=>"OK"
        );
        print json_encode($retArr);
        die();
    }
    
    public function dislikeIt(){
        if(!isset($_REQUEST['idBlendu']) || $_REQUEST['idBlendu']=='0' || $_REQUEST['idBlendu']==''){
            die('ERROR - no blend id found in request parameters');
        }
        $idLapki = '0';
        $lapka = 'dol';
        if($ocena = $this->db->getLapkaB($_REQUEST['idBlendu'],$_SESSION['user']['id'])){
            $idLapki = $ocena['id'];
            if($ocena['lapka']=='dol'){
                $lapka = 'gora';
            }
        }
        
        $lapkaArr = array(
            'id'=>$idLapki,
            'lapka'=>$lapka,
            'id_blendu'=>$_REQUEST['idBlendu'],
            'id_uzytkownika'=>$_SESSION['user']['id']
        );

        $this->db->saveOrUpdateLapkaB($lapkaArr);
        
        $retArr = array(
            'odp'=>"OK"
        );
        print json_encode($retArr);
        die();
    }
    
}


try{
    if(isset($_REQUEST['akcja'])){
        
        $httpBlendy = new HttpBlendy();
        
        switch($_REQUEST['akcja']){
                
            case 'mojeBlendy':
                $httpBlendy->mojeBlendy();
                break;
                
            case 'blend':
                $httpBlendy->blend();
                break;
                
            case 'nowyBlend':
                $httpBlendy->nowyBlend();
                break;
                
            case 'zaladujPiosenki':
                $httpBlendy->zaladujPiosenki();
                break;
                
            case 'usunBlend':
                $httpBlendy->usunBlend();
                break;

            case 'zapiszBlend':
                $httpBlendy->zapiszBlend();
                break;
                
            case 'togglePubliczny':
                $httpBlendy->togglePubliczny();
                break;
                
            case 'cudzeBlendy':
                $httpBlendy->cudzeBlendy();
                break;
                
            case 'cudzyBlend':
                $httpBlendy->cudzyBlend();
                break;
                
            case 'likeIt':
                $httpBlendy->likeIt();
                break;
                
            case 'dislikeIt':
                $httpBlendy->dislikeIt();
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
 
