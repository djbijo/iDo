<?php
session_start();
require_once("../DB_user.php");

$errors         = array();      // array to hold validation errors
$response           = array();      // array to pass back data
$response['success'] = false;
$secsWaited = 0;
//$response['user'] = $_SESSION['userId'];
//$response['event'] = $_SESSION['eventId'];
while (!isset($_SESSION['userId'])){
    sleep(1); //fixme: need to wait for user to log in
    $secsWaited += 1;
    if ($secsWaited > 30)
        return;
}

if (isset($_SESSION['userId']) and isset($_SESSION['eventId']) and isset($_SESSION['loggedin'])) {
    try {
        $user = new User($_SESSION['userId']);
    } catch (Exeption $e){
        echo ("userid = ".$_SESSION['userId']);
        echo $e;
        return;
    }
    $event = new Event($user, NULL, NULL, $_SESSION['eventId']);
    if ($event !== null) {
        $rsvp = $event->rsvp;
        $response['table'] = $rsvp->get();
//        echo json_encode($rsvp->get()[0]);
        if (isset($response['table'])){
            $response['success'] = true;
//            echo js$response['table'];
        }
//        echo json_encode($response);
    }
}
echo json_encode($response);
//echo "Error: something is wrong";
//throw new ErrorException("couldn't get table");
