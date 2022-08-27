<?php

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_URL, 'https://api.discogs.com/oauth/identity?oauth_consumer_key=HImaFJAFPsapXobmbqzh&oauth_nonce=CkW2K&oauth_signature_method=HMAC-SHA1&oauth_timestamp=1539469231&oauth_token=FXCDdfQoaBiVWxduinTOKPqgHwlEFZfVCZmXVFgM&oauth_version=1.0&oauth_signature=HD2ISh%2FhBTkU9a5YvIHKSCnnPeI%3D');
    
    
    //?oauth_secret=aaOTMMDrvyhBjTTgspoJixggDdHSDEjkCDBEIvFe&oauth_token=pFXmhylZNkRlsDlPzWREeLoyUxSKwUOluKDhiomB');
    
    //curl_setopt($ch, CURLOPT_USERAGENT, "BlendWax-1.0");
    //curl_setopt($ch, CURLOPT_USERAGENT, "BlendWax-1.0");
    //curl_setopt($ch, CURLOPT_HEADER, $result['header']);
    //curl_setopt($ch, CURLOPT_ENCODING , "gzip");
    
    /*$header = 'oauth_secret="aaOTMMDrvyhBjTTgspoJixggDdHSDEjkCDBEIvFe",
            oauth_token ="pFXmhylZNkRlsDlPzWREeLoyUxSKwUOluKDhiomB"';*/
            /*
$header = array(
        'oauth_secret'=>"aaOTMMDrvyhBjTTgspoJixggDdHSDEjkCDBEIvFe",
            'oauth_token' =>"pFXmhylZNkRlsDlPzWREeLoyUxSKwUOluKDhiomB"
            );*/
    //curl_setopt($ch, CURLOPT_POST, true);
    /*curl_setopt($ch, CURLOPT_POSTFIELDS, rawurldecode(http_build_query(array(
        'oauth_secret' => 'aaOTMMDrvyhBjTTgspoJixggDdHSDEjkCDBEIvFe',
        'oauth_token' => 'pFXmh'oauth_secret' => 'aaOTMMDrvyhBjTTgspoJixggDdHSDEjkCDBEIvFe',
        'oauth_token' => 'pFXmhylZNkRlsDlPzWREeLoyUxSKwUOluKDhiomB'ylZNkRlsDlPzWREeLoyUxSKwUOluKDhiomB'
    ))));*/
            
    //curl_setopt($ch, CURLOPT_AUTHORIZATION, $header);
    curl_setopt($ch, CURLOPT_USERAGENT, "BlendWax-1.0");
    
    $r = curl_exec($ch);
    curl_close($ch);
    print_r($r);
    
    
/*
//zapisywanie obrazka
$ch = curl_init('https://img.discogs.com/VH6r5Of5sHJD2Ty-ooe_Slbfmws=/fit-in/150x150/filters:strip_icc():format(jpeg):mode_rgb():quality(40)/discogs-images/R-39813-1211470116.jpeg.jpg');
$fp = fopen('test.jpg', 'wb');
curl_setopt($ch, CURLOPT_FILE, $fp);
curl_setopt($ch, CURLOPT_HEADER, 0);
curl_setopt($ch, CURLOPT_USERAGENT, "BlendWax-1.0");
curl_exec($ch);
curl_close($ch);
fclose($fp);
*/

?>