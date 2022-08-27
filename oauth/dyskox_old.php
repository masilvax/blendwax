<?php
///////////////////////////////////\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
// Google API OAuth Authorization using the OAuthSimple library
//
// Author: Guido Schlabitz
// Email: guido.schlabitz@gmail.com
//
// This example uses the OAuthSimple library for PHP
// found here:  http://unitedHeroes.net/OAuthSimple
//
// For more information about the OAuth process for web applications
// accessing Google APIs, read this guide:
// http://code.google.com/apis/accounts/docs/OAuth_ref.html
//
//////////////////////////////////////////////////////////////////////
require 'OAuthSimple.php';
require '../core/db.php';
$oauthObject = new OAuthSimple();
$db = DB::getInstance();
session_start();

if(isset($_SESSION['user']['login'])){
    header("Location:http://blendwax.com");
    die();
}

/*if(isset($_COOKIE['id_uzytkownika'])){
    if($uzytkownik = $db->getUzytkownikById($_COOKIE['id_uzytkownika'])){
        $_SESSION['user']=$uzytkownik;
        header("Location:http://blendwax.com");
        die();
    }
}*/



function jsonp_decode($jsonp, $assoc = false) { // PHP 5.3 adds depth as third parameter to json_decode
    if($jsonp[0] !== '[' && $jsonp[0] !== '{') { // we have JSONP
       $jsonp = substr($jsonp, strpos($jsonp, '('));
    }
    return json_decode(trim($jsonp,'();'), $assoc);
}

// As this is an example, I am not doing any error checking to keep 
// things simple.  Initialize the output in case we get stuck in
// the first step.
$output = 'Authorizing...';
// Fill in your API key/consumer key you received when you registered your 
// application with Google.
$signatures = array( 'consumer_key'     => 'HImaFJAFPsapXobmbqzh',
                     'shared_secret'    => 'suzJDrNBhkdRVTMCaWQHmTayDKeSlqFQ');
// In step 3, a verifier will be submitted.  If it's not there, we must be
// just starting out. Let's do step 1 then.



if (!isset($_GET['oauth_verifier'])) {
    ///////////////////////////////////\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
    // Step 1: Get a Request Token
    //
    // Get a temporary request token to facilitate the user authorization 
    // in step 2. We make a request to the OAuthGetRequestToken endpoint,
    // submitting the scope of the access we need (in this case, all the 
    // user's calendars) and also tell Google where to go once the token
    // authorization on their side is finished.
    //
    $result = $oauthObject->sign(array(
        'path'      =>'https://api.discogs.com/oauth/request_token',
        'parameters'=> array(
            /*'scope'         => 'http://www.google.com/calendar/feeds/',*/
            'oauth_callback'=> 'http://blendwax.com/oauth/dyskox.php'),
        'signatures'=> $signatures));
    // The above object generates a simple URL that includes a signature, the 
    // needed parameters, and the web page that will handle our request.  I now
    // "load" that web page into a string variable.
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_URL, $result['signed_url']);

    //curl_setopt($ch, CURLOPT_HEADER, $result['header']);
    //curl_setopt($ch, CURLOPT_ENCODING , "gzip");
    curl_setopt($ch, CURLOPT_USERAGENT, "BlendWax-1.0"); 
    
    $r = curl_exec($ch);
    curl_close($ch);
    // We parse the string for the request token and the matching token
    // secret. Again, I'm not handling any errors and just plough ahead 
    // assuming everything is hunky dory.
    
    //print_r($r);
    //sleep(5);
    
    if(isset($_GET['denied'])){
        header("Location:http://blendwax.com");
        die();//die($_GET['denied']);//przekierowanie do blendwax home
    }
    
    parse_str($r, $returned_items);
    $request_token = $returned_items['oauth_token'];
    $request_token_secret = $returned_items['oauth_token_secret'];
    // We will need the request token and secret after the authorization.
    // Google will forward the request token, but not the secret.
    // Set a cookie, so the secret will be available once we return to this page.
    //setcookie("oauth_token_secret", $request_token_secret, time()+3600*25,"/");
    $_SESSION['oauth_token_secret'] = $request_token_secret;
    
    /*echo "setting a cookie:";
    echo $_COOKIE['oauth_token_secret'];
    sleep(5);*/

    //
    //////////////////////////////////////////////////////////////////////
//setcookie("kukiz1",json_encode($_GET),time()+3600);
    ///////////////////////////////////\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
    // Step 2: Authorize the Request Token
    //
    // Generate a URL for an authorization request, then redirect to that URL
    // so the user can authorize our access request.  The user could also deny
    // the request, so don't forget to add something to handle that case.
    $result = $oauthObject->sign(array(
        'path'      =>'https://www.discogs.com/oauth/authorize',
        'parameters'=> array(
            'oauth_token' => $request_token),
        'signatures'=> $signatures));
    // See you in a sec in step 3.
    header("Location:$result[signed_url]");
    exit;
    //////////////////////////////////////////////////////////////////////
}
else {

//setcookie("kukiz2",json_encode($_GET),time()+3600);
    ///////////////////////////////////\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
    // Step 3: Exchange the Authorized Request Token for a Long-Term
    //         Access Token.
    //
    // We just returned from the user authorization process on Google's site.
    // The token returned is the same request token we got in step 1.  To 
    // sign this exchange request, we also need the request token secret that
    // we baked into a cookie earlier. 
    //
    // Fetch the cookie and amend our signature array with the request
    // token and secret.
    /*
    if(!isset($_COOKIE['oauth_token_secret'])){
        echo "THE COOKIE IS NOT SET. PLEASE MAKE SURE YOUR BROWSER ACCEPT COOKIES. (phase 2)";
    }
    else if($_COOKIE['oauth_token_secret']==''){
        echo "THE COOKIE IS EMPTY. (phase 2)";
    }*/
    
    /*echo "restoring a cookie:";
    echo $_COOKIE['oauth_token_secret'];
    sleep(5);*/
    
    //$signatures['oauth_secret'] = $_COOKIE['oauth_token_secret'];
    $signatures['oauth_secret'] = $_SESSION['oauth_token_secret'];
    $signatures['oauth_token'] = $_GET['oauth_token'];
    
    // Build the request-URL...
    $result = $oauthObject->sign(array(
        'path'      => 'https://api.discogs.com/oauth/access_token',
        'parameters'=> array(
            'oauth_verifier' => $_GET['oauth_verifier'],
            'oauth_token'    => $_GET['oauth_token']),
        'signatures'=> $signatures));
    // ... and grab the resulting string again. 
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_URL, $result['signed_url']);
    
    curl_setopt($ch, CURLOPT_USERAGENT, "BlendWax-1.0"); 
    
    $r = curl_exec($ch);
    //moja linia
    curl_close($ch);
    
    // Voila, we've got a long-term access token.
    parse_str($r, $returned_items);
    $access_token = $returned_items['oauth_token'];
    $access_token_secret = $returned_items['oauth_token_secret'];
    
    // We can use this long-term access token to request Google API data,
    // for example, a list of calendars. 
    // All Google API data requests will have to be signed just as before,
    // but we can now bypass the authorization process and use the long-term
    // access token you hopefully stored somewhere permanently.
    $signatures['oauth_token'] = $access_token;
    $signatures['oauth_secret'] = $access_token_secret;
    //////////////////////////////////////////////////////////////////////
    
    // Example Google API Access:
    // This will build a link to an RSS feed of the users calendars.
    $oauthObject->reset();
    $result = $oauthObject->sign(array(
        'path'      =>'https://api.discogs.com/oauth/identity',
        //'parameters'=> array('orderby' => 'starttime'),
        'signatures'=> $signatures));
    // Instead of going to the list, I will just print the link along with the 
    // access token and secret, so we can play with it in the sandbox:
    // http://googlecodesamples.com/oauth_playground/
    //
    
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_URL, $result['signed_url']);
    
    curl_setopt($ch, CURLOPT_USERAGENT, "BlendWax-1.0");
    
    //curl_setopt($ch, CURLOPT_URL, $result['signed_url']);
    $output = "<p>Access Token: $access_token<BR>
                  Token Secret: $access_token_secret</p>
               <p><a href='$result[signed_url]'>List of Calendars</a></p>";

    $r = curl_exec($ch);
    curl_close($ch);
    
    //print_r($r);
    
    $dyskoxUserArr = jsonp_decode($r, true);
    //echo "uuuuu: ".$dyskoxUserArr['username'];
    
    //print_r($signatures);

            
    //najpierw user spr czy w bazie jak nie to dodac do bazy i updejtowac tokeny secrety itd.
    if($czyUser = $db->getUzytkownikByLogin($dyskoxUserArr['username'])){

        $userArr = array(
                    'id'=>$czyUser['id'],
                    'access_token'=>$access_token,
                    'access_token_secret'=>$access_token_secret,
                    'ost_logowanie'=>date("Y-m-d H:i:s")
        );
    }else{
        $userArr = array(
                    'id'=>'0',
                    'login'=>$dyskoxUserArr['username'],
                    'access_token'=>$access_token,
                    'access_token_secret'=>$access_token_secret,
                    'data_dod'=>date("Y-m-d H:i:s"),
                    'ost_logowanie'=>date("Y-m-d H:i:s")
        );
    }
    $db->saveOrUpdateUzytkownik($userArr);
            
    //$_SESSION['user'] = $db->getUzytkownikByLogin($dyskoxUserArr['username']);
    $_SESSION['username'] = $dyskoxUserArr['username'];
    //setcookie("id_uzytkownika",$_SESSION['user']['id'],time()+(3600*24*30));
            
    /*if($czyUser){
        header("Location:http://blendwax.com");
        die();
    }else{*/
        header("Location:http://blendwax.com/#!/register");
        die();
    //}
            
    //$res = curl_exec($ch);
    //print_r($res);

}
?>
