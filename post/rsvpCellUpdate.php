<?php
session_start();
require_once ("../DB_user.php");

$pk = $_POST['pk'];
$name = $_POST['name'];
$value = $_POST['value'];

$errors         = array();      // array to hold validation errors
$response           = array();      // array to pass back data
$response['success'] = false;

if (!isset($value)){
    $errors["value"] = "השם ריק";
}

if (empty($errors)) {
    //validation succeeded
    if (isset($_SESSION['userId']) and isset($_SESSION['eventId'])) {
        $user = new User($_SESSION['userId']);
        $event = new Event($user, NULL, NULL,$_SESSION['eventId']);
        if ($event !== null) {
            $rsvp = $event->rsvp;
            $createdRow =  $rsvp->update($name, $pk, $value);
            if ($createdRow) {
                $response['success'] = true;
            } else {
                throw new ErrorException("add row to rsvp doesn't work");
            }
        }
    } else {
        $errors['usr'] = "user not defined";
    }
}
if (!empty($errors)){
    $response['errors'] = $errors;
    $response['success'] = false;
}
echo json_encode($response);