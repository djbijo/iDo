<?php
// signinuser.php
session_start();
require dirname(__DIR__) . '/vendor/autoload.php';
//require_once 'vendor/autoload.php';
require_once dirname(__DIR__).'/DB_user.php';


//FIXME: check if this is necessary maybe it's better onlt to check if the user hadn't changed
//if (isset($_SESSION['loggedin'])) {
//// remove all session variables
//    session_unset();
//
//// destroy the session
//    session_destroy();
////    $_SESSION['beenHere'] = 0;
//}
header('Content-type: application/json');
//$response['status'] = 'error';
// Get $id_token via HTTPS POST.
$id_token = $_POST['idtoken'];
$access_token = $_POST['accesstoken'];
$client = new Google_Client(['client_id' => '1072089522959-lncmb7n5llcqm2sjoei28ufm6g63fatm.apps.googleusercontent.com']);
$client->setScopes('email');
//$client->setDefaultOption('verify', false); //FIXME to be removed in production
$client->setAccessToken($access_token);
try {
    $payload = $client->verifyIdToken($id_token);
} catch (Exception $e) {
    session_unset();
    session_destroy();
    echo "";
    return;
}
if ($payload) {
  $usrId = $payload['sub'];
  $response['user'] = $usrId;
  $response['email'] = $payload['email'];
  $response['status'] = 'success';
  $response['name'] =  $payload['name'];
  $event = null;
//  if (!isset($_SESSION['loggedin'])){
//    $event = new Event();
    if (!User::checkUserID($usrId)){
//        echo ' user does not exist';
        $user = new User($usrId, $payload['name'], $payload['email']);
    }
    else {
        $user = new User($usrId);
    }
    if ($user === null)
        throw new Exception("User is null");
    if (!User::checkUserID($usrId)) {
        $response['status'] = 'error';
        $response['reason'] = "couldnt create user";
    }
    if ($user->event === null){
        $event = $user->addEvent("andh","2000-10-10");
        if ($event === null){
            throw new ErrorException("clouldn't create event");
        }
    }
    //FIXME: just for testing
    $user->selectEvent(4);

    if ($user->event === null){
        var_dump ($user->getEvents());
        throw new ErrorException("event in user is null");

    }
    $_SESSION['loggedin'] = true;
    $_SESSION['userId'] = $usrId;//$user->getID();
    $_SESSION['eventId'] = $user->event->getEventID();
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
    $response['error'] = "google couldn't verify user";
//	echo 'error';
  // Invalid ID token
}
echo json_encode($response);