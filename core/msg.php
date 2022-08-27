<?php 
if(!isset($_REQUEST['email']) || $_REQUEST['email']=="" || !strpos($_REQUEST['email'],'@'))
	die("podaj poprawny adres e-mail<br/>i ponów wysyłanie".$_REQUEST['email']);

if(!isset($_REQUEST['tresc']) || $_REQUEST['tresc']=="")
	die("podaj swój telefon<br/>i ponów wysyłanie");

try{
	
	$subject = 'ZAPYTANIE Z BLENDWAXA!!!';
	$subject = "=?UTF-8?B?".base64_encode($subject)."?=";

	//from musi byc jolpress.pl inaczej mail zwroci false
	$headers = 'From: office@blendwax.com' . "\r\n" .
			'Reply-To: '.$_REQUEST['email']. "\r\n" .
			'X-Mailer: PHP/' . phpversion(). "\r\n" .
			'Content-type:text/plain;charset=UTF-8';
	
	$message = 'e-mail: '.$_REQUEST['email']."\n"
	."Data: ".date("d-m-Y")."\n\n"
	.$_REQUEST['tresc']."\n";
	
	$to = 'office@blendwax.com';
	
	
	if(!mail($to, $subject, $message, $headers)){
		throw new Exception("ERROR during sending message!");
	}
		
	die("OK");
}catch(Exception $e){
	die($e->getMessage());
}
?>