<?php
if (is_file('db.php')){
    require_once('db.php');
}else{
    require_once 'core/db.php';
}
class SessionHandlerr{
	private $db = null;
 
        public function __construct(){
		session_start();
		$this->db = DB::getInstance();
		if(!isset($_SESSION['refresh'])){
			$_SESSION['refresh'] = 1;
		}else{
			$_SESSION['refresh'] = $_SESSION['refresh'] + 1;
		}
	}
    
	public function logIn($username,$pass){
		//$_SESSION['user'] = "ester";
        session_unset();
        session_destroy();
        session_start();
        $this->db->query("select * from uzytkownicy where login=? and haslo=?",array($username,md5($pass)));
		if($this->db->getRowCount()!=1){
			throw new Exception("Incorrect login or password!!!");
		}else{
                    $user = $this->db->getResult();
                    $_SESSION['user'] = $user[0];
		}
	}
    
	public function checkWhosLoggedIn(){
            if(isset($_SESSION['user']['login'])){
                return $_SESSION['user'];
            }/*else if(isset($_SESSION['username'])){//w trakcie rejestracji
                return $db->getUzytkownikByLogin($_SESSION['username']);
            }*/
            return false;
    }
 
        
    public function logOut(){
        session_unset();
        session_destroy();
    }
    
    public function potwierdzCiasto(){
        $_SESSION['user']['potwierdzoneCiasto']=true;
    }
    
}

if(isset($_REQUEST['akcja_s'])){
    try{
            
            $sessionHandlerr = new sessionHandlerr();
            
            switch($_REQUEST['akcja_s']){
                case 'login':
                    if(!isset($_REQUEST['login']) || !isset($_REQUEST['haslo']) ){
                        die("please type in your login and password");
                    }else{
                        $sessionHandlerr->logIn($_REQUEST['login'], $_REQUEST['haslo']);
                        
                        if(!$kto = $sessionHandlerr->checkWhosLoggedIn()){
                            die("NIE");
                        }else{
                            print json_encode($kto);
                        }
                    }
                    break;

                case 'logout':
                    $sessionHandlerr->logOut();
                    die("OK");
                    break;

                case 'check':
                    if(!$kto = $sessionHandlerr->checkWhosLoggedIn()){
                        die("NIE");
                    }else{
                        print json_encode($kto);
                    }
                    break;
                    
                case 'potwierdzCiasto':
                    $sessionHandlerr->potwierdzCiasto();
                    die("OK");
                    break;
                    
                default:
                    die("NIEOK");
            }

    }catch(Exception $e){
        die($e->getMessage());
    }
}



?>