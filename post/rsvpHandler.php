<?php
session_start();
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

$action = isset($request['action']) ? $request['action'] : "error";
if (!isset ($_SESSION['eventId'])){
    $errors['event'] = "לא נבחר אירוע עדיין";
}
if (!isset($_SESSION['userId'])){
    $errors['user'] = "אין משתמש פעיל";
}
switch ($action){
    case 'getTable': break;
    case 'addRow':
        addRowVal();
        break;
    case 'deleteRows' :
        if (empty($request['ids'])){
            $errors["ids"] = "לא נבחרו שורות למחיקה";
            break;
        }
        $params['ids']   = $request['ids'];
        break;
    case 'cellUpdate':
        cellUpdateVal();
        break;
    case 'getRawData':
        if (isset($request['phone'])){
            $params['phone'] = $request['phone'];
        } else {
            $errors['phone'] = "לא נשלח מס טלפון";
        }
        break;
    default:
        $errors["action"] = "no action was set";
        $errors["request"] = $request;
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
            case 'getRawData':
                try{
                    $response['data'] = $event->rawData->getByPhone($params['phone']);
                } catch (Exception $e){
                    $errors['getRawData'] = $e->getMessage();
                }
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

function addRowVal(){
    global $request, $params, $errors;
    $params['name']     = $request['data']['Name'];
    $params['surname']  = $request['data']['Surname'];
    $params['nickname'] = $request['data']['NickName'];
    $params['invitees'] = $request['data']['Invitees'];
    $params['phone']    = $request['data']['Phone'];
    $params['email']    = $request['data']['Email'];
    $params['groups']   = $request['data']['Groups'];
    $params['rsvp']     = $request['data']['Rsvp'];
    $params['ride']     = $request['data']['Ride'];
    if (empty($params['name'])) {
        $errors["Name"] = "שם המוזמן לא יכול להשאר ריק";
    }
    if (empty($params['surname'])){
        $errors['Surname'] = "שם משפחה לא יכול להשאר ריק"; //FIXME: maybe it can be empty
    }
    if (empty($params['invitees'])){
        $errors['Invitees'] = "חייבים למלא מספר מוזמנים, אפשר גם 0";
    }
    if (!empty($params['phone'])){
        $params['Phone'] = validatePhone($params['phone']);
        if (!$params['phone']){
            $errors['phone'] = "מספר הטלפון שהוקש לא תקין";
        }
    }
}

function cellUpdateVal(){
    global $request, $params, $errors;
    $params['pk']    = $request['pk'];
    $params['name']  = $request['name'];
    $params['value'] = $request['value'];

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