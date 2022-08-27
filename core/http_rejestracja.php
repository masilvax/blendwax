<?php
require_once 'db.php';
//require_once 'session.php';

class HttpRejestracja{
    private $db = null;
    //private $sessionHandlerr = null;
    
    public function __construct(){
        $this->db = DB::getInstance();
        session_start();
        //$this->sessionHandlerr = new SessionHandlerr();
        /*if(!$this->sessionHandlerr->checkWhosLoggedIn()){
            die("Sesja wygasła - proszę odświezyć stronę i zalogować się ponownie");
        }*/
    }
    
    public function zarejestruj(){
        if(!isset($_REQUEST['haslo']) || $_REQUEST['haslo']==''){
            die('ERROR - please type in the password');
        }
        if(!isset($_REQUEST['haslo2']) || $_REQUEST['haslo2']==''){
            die('ERROR - please confirm the password');
        }
        if($_REQUEST['haslo']!=$_REQUEST['haslo2']){
            die('ERROR - password and password confirmation are not equal!');
        }

        if(!isset($_SESSION['username'])){
            die("Please sign with Discogs first!!!");
        }
        
        $user = $this->db->getUzytkownikByLogin($_SESSION['username']);//podczas sign in z Discogsem to jest ustawiane
        
        $userArr = array(
                    'id'=>$user['id'],
                    'haslo'=>md5($_REQUEST['haslo'])
        );
        $this->db->saveOrUpdateUzytkownik($userArr);
        
        $_SESSION['user'] = $user;
        
        $retArr = array(
                    'odp' => 'OK',
                    'kto' => $_SESSION['username']
        );
        print json_encode($retArr);
        die();
    }
    

    
}


try{
    if(isset($_REQUEST['akcja'])){
        
        $httpRejestracja = new HttpRejestracja();
        
        switch($_REQUEST['akcja']){
                
            case 'zarejestruj':
                $httpRejestracja->zarejestruj();
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
 
