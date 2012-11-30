<?php

/**************************************************/
/******* Facebook application configuration *******/
/**************************************************/



$fbconfig['appid' ] = "567748983241208";
$fbconfig['secret'] = "1354c6e6f1287187ae2d9a59ca53501f";

$fbconfig['baseUrl']    = "https://app.mediafactory.com.br/facebook/msccruzeiros/";
$fbconfig['appBaseUrl'] = "https://apps.facebook.com/msccruzeiros";


/* 
 * If user first time authenticated the application facebook
 * redirects user to baseUrl, so I checked if any code passed
 * then redirect him to the application url
 */
if (isset($_GET['code'])){
	header("Location: " . $fbconfig['appBaseUrl']);
	exit;
}
//~~

if (isset($_GET['request_ids'])){
	//user comes from invitation
	//track them if you need
}

$user = null; //facebook user uid

try{
	include_once "facebook.php";
}
catch(Exception $o){
	/*
	echo '<pre>';
	print_r($o);
	echo '</pre>';
	*/
}
// Create our Application instance.
$facebook = new Facebook(array(
	'appId'  => $fbconfig['appid'],
	'secret' => $fbconfig['secret'],
	'cookie' => true,
));

//Facebook Authentication part
$user = $facebook->getUser();
// We may or may not have this data based 
// on whether the user is logged in.
// If we have a $user id here, it means we know 
// the user is logged into
// Facebook, but we don’t know if the access token is valid. An access
// token is invalid if the user logged out of Facebook.

$loginUrl = $facebook->getLoginUrl(
	array(
		'scope'         => 'email,publish_stream'
	)
);

if ($user) {
	try {
	// Proceed knowing you have a logged in user who's authenticated.
		$user_profile = $facebook->api('/me');
	} catch (FacebookApiException $e) {
		//you should use error_log($e); instead of printing the info on browser
		//d($e);  // d is a debug function defined at the end of this file
		$user = null;
	}
}

if (!$user){
	echo "<script type='text/javascript'>top.location.href = '$loginUrl';</script>";
	exit;
}

//get user basic description
$userInfo = $facebook->api("/$user");

function d($d){
	echo '<pre>';
	print_r($d);
	echo '</pre>';
}
?>