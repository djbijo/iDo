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
} else {
    try {
        $event = new Event($_SESSION['userId'], $_SESSION['eventId']);
        $messages = $event->messages;
    } catch (Exception $e){
        $errors['event'] = $e->getMessage();
    }
}

if (empty($errors))
    switch ($action){
        case 'get':
            $response['table'] = $messages->get();
            break;
        case 'send':
//            addMsg($params, $errors, $messages);
//            break;
        case 'add':
            addMsg($params, $errors, $messages);
            break;
        default:
            $errors['action'] = "no known action was set, action is: ".$action;
            break;
    }

if (!empty($errors)){
    $response['status'] = "error";
    $errors['post'] = $_POST;
    $response['errors'] = $errors;
} else {
    $response['status'] = 'success';
}

echo json_encode($response);

//helper functions:
function val_add(&$params, &$errors){
    if (empty($_POST['message'])){
        $errors['message'] = "תוכן ההודעה לא יכול להשאר ריק";
    }
    if (empty($_POST['date'])){
        $errors['date'] = "חובה למלא תאריך";
    }
    if (empty($_POST['time'])){
        $errors['date'] = "חובה למלא שעה";
    }
    if (!empty($errors)) return false;
    if (empty($_POST['msgType'])){
        $params['msgType'] = 'default';
    } else {
        $params['msgType'] = $_POST['msgType'];
    }
    if (!empty($_POST['groups'])){
        $params['groups'] = $_POST['groups'];
    } else {
        $params['groups'] = null;
    }
    $params['message'] = $_POST['message'];
    $params['date'] = $_POST['date'];
    $params['time'] = $_POST['time'];
    return true;
}

function addMsg(&$params, &$errors, &$messages){
    if (!val_add($params, $errors)) return;
    try{
        $messages->add($params['msgType'], $params['message'], $params['date'], $params['time']);
//        $messages->add($params['msgType'], $params['message'], $params['date'], $params['time'], $params['groups']);
    } catch (Exception $e){
        $errors['addMsg'] = $e->getMessage();
        $errors['params'] = $params;
    }
}