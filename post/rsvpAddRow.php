<?php
require_once ("../DB_user.php");
session_start();
$errors         = array();      // array to hold validation errors
$response           = array();      // array to pass back data

$name     = $_POST['name'];
$surname  = $_POST['surname'];
$nickname = $_POST['nickName'];
$invitees = $_POST['invitees'];
$phone    = $_POST['phone'];
$email    = $_POST['email'];
$groups   = $_POST['groups'];
$rsvp     = $_POST['rsvp'];
$ride     = $_POST['ride'];

$response;
if (empty($name)) {
    $errors["name"] = "name field shouldn't be empty";
}
if (!empty($errors)) {
    //validation succeeded
    if (isset($_SESSION['user'])) {
        if ($_SESSION['user'] :: $event . $rsvp !== null) {
            $rsvp = User:: $event . $rsvp;
            $createdRow =  $rsvp.add($name, $surname, $invitees);
            $response["sqlData"] = $createdRow;
            $response['success'] = true;
            $response['message'] = "סבבה";
        }

    } else {
        $response['errors']['usr'] = "user not defined";
        $response['success'] = false;
    }
} else {
    $response['errors'] = $errors;
    $response['success'] = false;
}

echo json_encode($response);