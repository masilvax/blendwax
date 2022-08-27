<?php
require_once 'db.php';
require_once 'session.php';

class HttpAlbum{
    private $db = null;
    private $sessionHandlerr = null;
    
    public function __construct(){
        $this->db = DB::getInstance();
        $this->sessionHandlerr = new SessionHandlerr();
        if(!$this->sessionHandlerr->checkWhosLoggedIn()){
            die("Sesja wygasła - proszę odświezyć stronę i zalogować się ponownie");
        }
    }
    
    public function album(){
        if(!isset($_REQUEST['idAlbumu']) || $_REQUEST['idAlbumu']=='0' || $_REQUEST['idAlbumu']==''){
            die('ERROR - no album id found in request parameters');
        }
        //$album = $this->db->albumUzytkownika($_REQUEST['idAlbumu'],$_SESSION['user']['id']);
        $album = $this->db->album($_REQUEST['idAlbumu']);
        $trackLista = $this->db->piosenkiAlbumu($_REQUEST['idAlbumu']);
        
        $retArr = array(
                    'odp' => 'OK',
                    'album' => $album,
                    'trackLista' => $trackLista
        );
        print json_encode($retArr);
    }
    
}


try{
    if(isset($_REQUEST['akcja'])){
        
        $httpAlbum = new HttpAlbum();
        
        switch($_REQUEST['akcja']){
                
            case 'release':
                $httpAlbum->album();
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
