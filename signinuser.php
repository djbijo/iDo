<?php
// signinuser.php
require_once 'vendor/autoload.php';
require_once 'DB_user.php';

session_start();
//FIXME: check if this is necessary maybe it's better onlt to check if the user hadn't changed
if (isset($_SESSION['loggedin'])) {
// remove all session variables
    session_unset();

// destroy the session
    session_destroy();
//    $_SESSION['beenHere'] = 0;
}
header('Content-type: application/json');
//$response['status'] = 'error';
// Get $id_token via HTTPS POST.
$id_token = $_POST['idtoken'];
$access_token = $_POST['accesstoken'];
$client = new Google_Client(['client_id' => '1072089522959-lncmb7n5llcqm2sjoei28ufm6g63fatm.apps.googleusercontent.com']);
$client->setScopes('email');
//$client->setDefaultOption('verify', false); //FIXME to be removed in production
$client->setAccessToken($access_token);
$payload = $client->verifyIdToken($id_token);
if ($payload) {
  $usrId = $payload['sub'];
  $response['user'] = $usrId;
  $response['email'] = $payload['email'];
  $response['status'] = 'success';
//  if (!isset($_SESSION['loggedin'])){
//    $event = new Event();
    if (!User::checkUserID($usrId)){
//        echo ' user does not exist';
        $user = new User($usrId, $payload['name'], $payload['email'], 0, 'test', '0001');
    }
    else {
        $user = new User($usrId);
    }
    if (User::$event !== null){
        $event = new Event($user,-1, "baa", 55, 121, "dd");
    }
    $_SESSION['loggedin'] = true;
    $_SESSION['user'] = $user;
    $_SESSION['beenHere'] = isset($_SESSION['beenHere']) ? $_SESSION['beenHere'] + 1 : 0;
    $response['beenHere'] = $_SESSION['beenHere'];
//  }
//  else {
////      echo 'user is already logged in with session';
//      $response['session_active'] = 'true';
//  }
  // If request specified a G Suite domain:
  //$domain = $payload['hd'];
} else {
    $response['status'] = 'error';
//	echo 'error';
  // Invalid ID token
}
echo json_encode($response);