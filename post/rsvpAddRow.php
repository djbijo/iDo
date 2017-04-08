<?php
session_start();
require_once ("../DB_user.php");

$errors         = array();      // array to hold validation errors
$response           = array();      // array to pass back data
$response['success'] = true;
$name     = $_POST['Name'];
$surname  = $_POST['Surname'];
$nickname = $_POST['NickName'];
$invitees = $_POST['Invitees'];
$phone    = $_POST['Phone'];
$email    = $_POST['Email'];
$groups   = $_POST['Groups'];
$rsvp     = $_POST['Rsvp'];
$ride     = $_POST['Ride'];

$response;
if (empty($name)) {
    $errors["name"] = "name field shouldn't be empty";
}
//if (!isset ($_SESSION['event'])){
//    $errors['event'] = "לא נבחר אירוע עדיין";
//}
if (empty($errors)) {
    //validation succeeded
    if (isset($_SESSION['userId']) and isset($_SESSION['eventId'])) {
        $user = new User($_SESSION['userId']);
        $event = new Event($user, NULL, NULL,$_SESSION['eventId']);
        if ($event !== null) {
            $rsvp = $event->rsvp;
            $createdRow =  $rsvp->add($name, $surname, $invitees);
            if (!empty($createdRow)) {
                $response["sqlData"] = $createdRow;
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