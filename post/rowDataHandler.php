<?php
session_start();
//include common handler code
require_once("handlerCommon.php");

$event = getEvent();
if (empty($errors))
    switch ($action){
        case 'updateFromServer':
            updateFromServer();
            break;
        default:
            $errors['action'] = "no known action was set, action received: ".$action;
    }

sendResponse();

function updateFromServer(){
    global $errors, $event, $response;
    try {
        $response['newMsgs'] = $event->getMessages();
    } catch (Exception $e){
        $errors['get'] = $e->getMessage();
    }
}