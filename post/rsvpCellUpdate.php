<?php
session_start();
require_once ("../DB_user.php");

$pk = 0;//$_POST['pk'];
$pk = $_POST['pk'];
$name = $_POST['name'];
$value = $_POST['value'];

$errors         = array();      // array to hold validation errors
$response           = array();      // array to pass back data
$response['status'] = 'error';
if (!isset($value) or empty($value)){
    $errors["value"] = "השם לא יכול להיות ריק";
}
if (is_null($pk) or !isset($pk)){
    return;
}
if (empty($errors)) {
    //validation succeeded
    if (isset($_SESSION['userId']) and isset($_SESSION['eventId'])) {
        $user = new User($_SESSION['userId']);
        $event = new Event($user, NULL, NULL,$_SESSION['eventId']);
        if ($event !== null) {
            $rsvp = $event->rsvp;
            try{
                $createdRow =  $rsvp->update($name, $pk, $value);
            } catch (Exception $e) {
                echo ($e);
                return;
            }

            if ($createdRow) {
                $response['status'] = 'success';
            } else {
                throw new ErrorException("add row to rsvp doesn't work");
            }
        }
    } else {
        $errors['usr'] = "user not defined";
    }
}
if (!empty($errors)){
    $response['error'] = $errors['value'];
    $response['status'] = 'error';
}
echo json_encode($response);