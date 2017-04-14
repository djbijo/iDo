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

if (empty($errors))
    switch ($action){
        case 'send':

            break;
        default:
            $errors['action'] = "no known action was set, action is: ".$action;
    }

if (!empty($errors)){
    $response['status'] = "error";
    $response['errors'] = $errors;
} else {
    $response['status'] = 'success';
}

echo json_encode($response);