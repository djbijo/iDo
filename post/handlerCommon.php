<?php
require_once ("../DB_user.php");
require_once("../common.php");

$errors         = array();      // array to hold validation errors
$response       = array();      // array to pass back data
$params         = array();
$response['status'] = "error";

switch($_SERVER['REQUEST_METHOD'])
{
    case 'GET': $request = &$_GET; break;
    case 'POST': $request = &$_POST; break;
    default:
}

//make sure we don't try to access null object
$action = isset($request['action']) ? $request['action'] : "error";

function getEvent()
{
    global $errors;
    if (!isset($_SESSION['userId'])){
        $errors['user'] = "אין משתמש פעיל";
    } else if (!isset ($_SESSION['eventId'])) {
        $errors['event'] = "לא נבחר אירוע עדיין";
    }
    if (empty($errors)) {
        try {
            $event = new Event($_SESSION['userId'], $_SESSION['eventId']);
            return $event;
        } catch (Exception $e) {
            $errors['event'] = $e->getMessage();
        }
    }
    return false;
}

function sendResponse(){
    global $errors, $response;
    if (!empty($errors)){
        $response['errors'] = $errors;
        $response['status'] = 'error';
    } else {
        $response['status'] = "success";
    }
    echo json_encode($response);
}
