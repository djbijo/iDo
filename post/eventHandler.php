<?php
session_start();
require_once ("../DB_user.php");
require_once("../common.php");

$errors             = array();      // array to hold validation errors
$response           = array();      // array to pass back data
$response['status'] = "error";

$action = isset($_POST['action']) ? $_POST['action'] : "error";
if (!isset($_SESSION['eventId'])) return; //todo: error
switch ($action){
    case 'getEvents' : break;
    case 'getEventData' :
        $event = new Event($_SESSION['userId'], $_SESSION['eventId']);
        if ($event !== null){
            try {
                $response['event'] = $event->get();
            } catch (Exception $e){
                $errors['eventGet'] = $e->getMessage();
            }
            if (empty($response['event'])){
                $errors['event'] = 'event is empty';
            }
        } else {
            $errors['event'] = "event is null";
        }
        break;
    case 'create':
        //todo: create with received data
        if (isset($_SESSION['userId'])) {
            try {
                $user = new User($_SESSION['userId']);
            } catch (Exception $e) {
                $errors['user'] = "new User failed";
            }
            if(!$user->addEvent("עוד אירוע", "2021-05-01"))
                $errors['newevent'] = "לא הצלחתי ליצור אירוע";
        }
        break;
    default:
        $errors['action'] = "no action is set, action i got was: ".$action;
        $errors['post'] = $_POST;
        break;
}

if (!empty($errors)){
    echo "has errors";
    $response['status'] = "error";
    $response['errors'] = $errors;
} else {
    $response['status'] = 'success';
}

echo json_encode($response);

