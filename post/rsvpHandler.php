<?php
session_start();
require_once ("../DB_user.php");
require_once("../common.php");

$errors         = array();      // array to hold validation errors
$response       = array();      // array to pass back data
$params         = array();
$response['status'] = "error";

$action = isset($_POST['action']) ? $_POST['action'] : "error";
switch ($action){
    case 'getTable': break;
    case 'addRow':
        addRowVal($params, $errors);
        break;
    case 'deleteRows' :
        if (empty($_POST['ids'])){
            $errors["ids"] = "לא נבחרו שורות למחיקה";
            break;
        }
        $params['ids']   = $_POST['ids'];
        break;
    case 'cellUpdate':
        cellUpdateVal($params, $errors);
        break;
    default:
        $errors["action"] = "no action was set";
        $errors["post"] = $_POST;
}


if (!isset ($_SESSION['eventId'])){
    $errors['event'] = "לא נבחר אירוע עדיין";
}
if (!isset($_SESSION['userId'])){
    $errors['user'] = "אין משתמש פעיל";
}

if (empty($errors)) {
    //validation succeeded
    try {
        $event = new Event($_SESSION['userId'], $_SESSION['eventId']);
    } catch (Exception $e){
        $errors['event'] = $e->getMessage();
    }
    if ($event !== null) {
        $rsvp = $event->rsvp;
        switch ($action) {
            case 'getTable':
                $response['table'] = $rsvp->get();
                if (isset($response['table'])) {
                    $success = true;
                }
                break;
            case 'addRow':
                $response['ID'] = $rsvp->add($params['name'], $params['surname'], $params['invitees']);
                if (!$response['ID']) $errors['add'] = "ההוספה נכשלה";
                break;
            case 'deleteRows':
                foreach ($params['ids'] as &$id){
                    if (!($success = $rsvp->delete($id))){
                        $errors['remove'] = "המחיקה נכשלה";
                        break;
                    }
                }
                break;
            case 'cellUpdate':
                try{
                    $response['rowId'] =  $rsvp->update($params['name'], $params['pk'], $params['value']);
                } catch (Exception $e) {
                    $errors['cell'] = "העדכון נכשל".$e->getMessage();
                }
                if (!$response['rowId'])
                    $errors['row'] = "העדכון נכשל";
                break;
            default:
                $errors["action"] = "no action was set";
        }
    } else {
        $errors['event'] = "לא הצלחתי ליצור אירוע";
    }
}
if (!empty($errors)){
    $response['errors'] = $errors;
    $response['status'] = 'error';
} else {
    $response['status'] = "success";
}
echo json_encode($response);

function addRowVal(&$params, &$errors){
    $params['name']     = $_POST['data']['Name'];
    $params['surname']  = $_POST['data']['Surname'];
    $params['nickname'] = $_POST['data']['NickName'];
    $params['invitees'] = $_POST['data']['Invitees'];
    $params['phone']    = $_POST['data']['Phone'];
    $params['email']    = $_POST['data']['Email'];
    $params['groups']   = $_POST['data']['Groups'];
    $params['rsvp']     = $_POST['data']['Rsvp'];
    $params['ride']     = $_POST['data']['Ride'];
    if (empty($params['name'])) {
        $errors["Name"] = "שם המוזמן לא יכול להשאר ריק";
    }
    if (empty($params['surname'])){
        $errors['Surname'] = "שם משפחה לא יכול להשאר ריק"; //FIXME: maybe it can be empty
    }
    if (empty($params['invitees'])){
        $errors['Invitees'] = "חייבים למלא מספר מוזמנים, אפשר גם 0";
    }
}

function cellUpdateVal(&$params, &$errors){
    $params['pk']    = $_POST['pk'];
    $params['name']  = $_POST['name'];
    $params['value'] = $_POST['value'];

    if (empty($params['name'])){
        //TODO: handle fields differently
        $errors['value'] = "שדה זה לא יכול להשאר ריק";
    }
    if (empty($params['name'])){
        $errors['fatal'] = "יש תקלה עם הטור הזה";
    }
    if (empty($params['pk'])){
        $errors['pk'] = "השורה לא נמצאת במסד הנתונים, נסו לרענן את הטבלה";
    }
}