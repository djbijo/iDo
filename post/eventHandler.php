<?php
session_start();
require_once ("../DB_user.php");
require_once("../common.php");

$errors             = array();      // array to hold validation errors
$response           = array();      // array to pass back data
$response['status'] = "error";
$params             = array();

$action = isset($_POST['action']) ? $_POST['action'] : "error";
if ((!isset($_SESSION['eventId']) or !$_SESSION['eventId']) and $action !== 'create'){
    $errors['event'] = "לא קיימים אירועים למשתמש";
}
if (empty($errors)) {
    switch ($action) {
        case 'getEvents' :
            try {
                $user = new User($_SESSION['userId']);
                $response['events'] = $user->getEventNames();
            } catch (Event $e){
                $errors['getEvents'] = $e->getMessage();
            }
            break;
        case 'update' :
            $event = new Event($_SESSION['userId'], $_SESSION['eventId']);
            if ($event !== null) {
                try {
                    $response['msg'] = $event->update($_POST['name'], $_POST['pk'], $_POST['value']);
                } catch (Exception $e) {
                    $errors['event'] = $e->getMessage();
                    $errors['name'] = $_POST['name'];
                    $errors['value'] = $_POST['value'];
                }
            } else {
                $errors['event'] = "event is null";
            }
            break;
        case 'updateSms':
            //TODO: test smsgateway before submitting
            $event = new Event($_SESSION['userId'], $_SESSION['eventId']);
            if ($event !== null) {
                $formData = $_POST['data'];
                try {
                    foreach ($formData as $field => $value)
                    $response['post'] = $_POST;
                    $response['msg'] = $event->update($field, $_POST['pk'], $value);
                } catch (Exception $e) {
                    $errors['event'] = $e->getMessage();
                    $errors['name'] = $_POST['name'];
                    $errors['value'] = $_POST['value'];
                }
            } else {
                $errors['event'] = "event is null";
            }
            break;
        case 'getEventData' :
            try {
                $event = new Event($_SESSION['userId'], $_SESSION['eventId']);
            } catch (Exception $e){
                $errors['event'] = $e->getMessage();
                break;
            }
            if ($event !== null) {
                try {
                    $response['event'] = $event->get();
                } catch (Exception $e) {
                    $errors['eventGet'] = $e->getMessage();
                }
                if (empty($response['event'])) {
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
                        try {
                            $user->addEvent($params['EventName'], $params['EventDate']);
                            $_SESSION['eventId'] = $user->event->getEventID();
                        } catch (Exception $e) {
                            $errors['newevent'] = $e->getMessage();
                        }
                    } catch (Exception $e) {
                        $errors['user'] = "new User failed";
                    }
                } else {
                    $errors['user'] = "צריך להתחבר לפני יצירת אירוע חדש";
                }
            break;
        case 'delete':
            $event = new Event($_SESSION['userId'], $_SESSION['eventId']);
            $user = new User($_SESSION['userId']);
            try {
                $event->deleteEvent($user);
                if ($user->event !== null)
                    $_SESSION['eventId'] = $user->event->getEventID();
                else unset($_SESSION['eventId']);
            } catch (Exception $e) {
                $errors['delete'] = $e->getMessage();
            }
            break;
        default:
            $errors['action'] = "no action is set, action i got was: " . $action;
            $errors['post'] = $_POST;
            break;
    }
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

