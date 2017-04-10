<?php
session_start();
require_once ("../DB_user.php");
require_once("../common.php");

$errors         = array();      // array to hold validation errors
$response           = array();      // array to pass back data
$response['status'] = "error";

$action = isset($_POST['action']) ? $_POST['action'] : "error";
switch ($action){
    case 'addRow':
        $name     = $_POST['data']['Name'];
        $surname  = $_POST['data']['Surname'];
        $nickname = $_POST['data']['NickName'];
        $invitees = $_POST['data']['Invitees'];
        $phone    = $_POST['data']['Phone'];
        $email    = $_POST['data']['Email'];
        $groups   = $_POST['data']['Groups'];
        $rsvp     = $_POST['data']['Rsvp'];
        $ride     = $_POST['data']['Ride'];
        if (empty($name)) {
            $errors["name"] = "name field shouldn't be empty";
        }
        break;
    case 'deleteRows' :
        if (empty($_POST['ids'])){
            $errors["ids"] = "לא נבחרו שורות למחיקה";
            break;
        }
        $ids      = $_POST['ids'];
        break;
    default:
        $errors["action"] = "no action was set";
}
//echo json_encode($response);
//return;


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
            $success = false;
            switch ($action) {
                case 'addRow':
                    $success = $rsvp->add($name, $surname, $invitees);
                    break;
                case 'deleteRows':
                    foreach ($ids as &$id){
                        if (!($success = $rsvp->delete($id))){
                            $errors['remove'] = "המחיקה נכשלה";
                            break;
                        }
                    }
                    break;
                default:
                    $errors["action"] = "no action was set";
                    $success = false;
            }
            if ($success) {
                $response['status'] = "success";
            } else {
//                throw new ErrorException("add row to rsvp doesn't work");
                $errors['addRow'] = "הפעולה נכשלה";
            }
        } else {
            $errors['event'] = "event don't exist";
        }
    } else {
        $errors['usr'] = "user not defined";
    }
}
if (!empty($errors)){
    $response['errors'] = $errors;
    $response['status'] = 'error';
}
echo json_encode($response);