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
        case 'update':
            updateMsg($params, $errors, $messages);
            break;
        case 'updateSend':
            updateMsg($params, $errors, $messages);
            //send
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
    if (empty($_POST['data']['message'])){
        $errors['message'] = "תוכן ההודעה לא יכול להשאר ריק";
    }
    if (empty($_POST['data']['date'])){
        $errors['date'] = "חובה למלא תאריך";
    }
    if (empty($_POST['data']['time'])){
        $errors['date'] = "חובה למלא שעה";
    }
    if (!empty($errors)) return false;
    if (empty($_POST['data']['msgType'])){
        $params['msgType'] = 'default';
    } else {
        $params['msgType'] = $_POST['msgType'];
    }
    if (!empty($_POST['data']['groups'])){
        $params['groups'] = $_POST['groups'];
    } else {
        $params['groups'] = 'all';
    }
    if (!empty($_POST['data']['id'])){
        $params['id'] = $_POST['data']['id'];
    }
    $params['message'] = $_POST['data']['message'];
    $params['date'] = $_POST['data']['date'];
    $params['time'] = $_POST['data']['time'];
    return true;
}

function updateMsg(&$params, &$errors, &$messages){
    if (!val_add($params, $errors)) return;
    try{
//        $messages->add($params['msgType'], $params['message'], $params['date'], $params['time']);
        if (!isset($params['id'])) {
            $response['msgId'] = $messages->add($params['msgType'], $params['message'], $params['date'], $params['time'], $params['groups']);
        } else {
            $response['msgId'] = $messages->update($params['id'], $params['msgType'], $params['message'], $params['date'], $params['time'], $params['groups']);
        }
    } catch (Exception $e){
        $errors['addMsg'] = $e->getMessage();
    }
}