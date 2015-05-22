<?php
//Application Configuration
$app_id		= "579416092151563";
$app_secret	= "69c1871229eb7f8934989f5ac0ed4ef7";
$site_url	= "http://crime.timcoysh.co.uk/index.php";

//Include the Facebook SDK
try {
	include_once "facebook.php";
} catch (Exception $e) {
	error_log($e);
}

// Create the application instance
$facebook = new Facebook(array(
	'appId'		=> $app_id,
	'secret'	=> $app_secret,
	));

// Get User ID
$user = $facebook->getUser();

//Get login link from fb
if (!$user) {
	$loginUrl = $facebook->getLoginUrl(array(
		'scope'			=> 'read_stream, public_profile, user_about_me, email, user_tagged_places',
		'redirect_uri'	=> $site_url,
		'auth_type'		=> 'rerequest',
		));
	$loginUrl = str_replace('facebook.com/dialog/','facebook.com/v2.0/dialog/',$loginUrl);
}

//Get basic user information - this can be accessed from every page
if ($user) {
	global $access_token;
	$access_token = $facebook->getAccessToken();
	$user_info = $facebook->api('/'.$user);
}
?>