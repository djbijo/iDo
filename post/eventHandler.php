<?php
session_start();
require_once ("../DB_user.php");
require_once("../common.php");

$errors             = array();      // array to hold validation errors
$response           = array();      // array to pass back data
$response['status'] = "error";
$params             = array();

$action = isset($_POST['action']) ? $_POST['action'] : "error";
if (!isset($_SESSION['eventId'])) return; //todo: error
switch ($action){
    case 'getEvents' : break;
    case 'update' :
        $event = new Event($_SESSION['userId'], $_SESSION['eventId']);
        if ($event !== null){
            try {
                $response['msg'] = $event->update($_POST['name'],$_POST['pk'],$_POST['value']);
            } catch (Exception $e){
                $errors['eventGet'] = $e->getMessage();
            }
        } else {
            $errors['event'] = "event is null";
        }
        break;
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
        createVal($params, $errors);
        if (empty($errors))
        if (isset($_SESSION['userId'])) {
            try {
                $user = new User($_SESSION['userId']);
            } catch (Exception $e) {
                $errors['user'] = "new User failed";
            }
            try {
                $user->addEvent("עוד אירוע", "2021-05-01");
            } catch (Exception $e){
                $errors['newevent'] = $e->getMessage();
            }
        } else {
            $errors['user'] = "צריך להתחבר לפני יצירת אירוע חדש";
        }
        break;
    default:
        $errors['action'] = "no action is set, action i got was: ".$action;
        $errors['post'] = $_POST;
        break;
}

if (!empty($errors)){
    $response['status'] = "error";
    $response['errors'] = $errors;
} else {
    $response['status'] = 'success';
}

echo json_encode($response);

function createVal(&$params, &$errors){
    $params['EventName'] = $_POST['data']['EventName'];
    $params['EventDate'] = $_POST['data']['EventDate'];
    $params['EventTime'] = $_POST['data']['EventTime'];
    $params['Venue']     = $_POST['data']['Venue'];
    $params['Address']   = $_POST['data']['Address'];
    if (empty($params['EventName'])){
        $errors['name'] = "חייבים להכניס שם, ניתן לשנות בהמשך";
    }
    if (empty($params['EventDate'])){
        $errors['date'] = "חייבים להכניס תאריך, ניתן לשנות";
    }
    if (empty($params['EventTime'])){}
    if (empty($params['Venue']    )){}
    if (empty($params['Address']  )){}
}
