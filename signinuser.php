<?php
// signinuser.php
require_once 'c://wamp64/apps/google-api-php-client/vendor/autoload.php';


// Get $id_token via HTTPS POST.
$id_token = $_POST['idtoken'];
$access_token = $_POST['accesstoken'];
$client = new Google_Client(['client_id' => '1072089522959-lncmb7n5llcqm2sjoei28ufm6g63fatm.apps.googleusercontent.com']);
$client->setScopes('email');
//$client->setDefaultOption('verify', false); //FIXME to be removed in production
$client->setAccessToken($access_token);
$payload = $client->verifyIdToken($id_token);
if ($payload) {
  $userid = $payload['sub'];
  echo 'user '.$userid.' logged in, email:'.$payload['email'];
  
  // If request specified a G Suite domain:
  //$domain = $payload['hd'];
} else {
	echo 'error';
  // Invalid ID token
}