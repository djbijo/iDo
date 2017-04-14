<?php

// database preparation
/*
 * use utf8 for hebrew:
 set character_set_server=utf8;
 set character_set_results=utf8;
 set character_set_database=utf8;
 set character_set_connection=utf8;
 set character_set_client=utf8;
 set names 'utf8';
 * 
 */

// MySQL commands
//SELECT * FROM rsvp1 ORDER BY ID DESC;

function phoneFromStr($str){
$sanitized = filter_var($str, FILTER_SANITIZE_NUMBER_INT);

}

function validatePhone($phone){         // Todo: handle too many numbers, Null returns true
    $regexp = '/05([-]*\d){8}|(\+972(-*5)(-*\d){8})/';
    return filter_var($phone, FILTER_VALIDATE_REGEXP,
        array("options"=>array("regexp"=>$regexp)));
}

function validateEmail($email){         // Todo: handle valid email address, Null returns true
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

/**
 * @return Event|null
 */
function postGetEvent(){
    if (isset($_SESSION['userId']) and isset($_SESSION['eventId'])) {
        try {
            $user = new User($_SESSION['userId']);
        } catch (Exception $e){
            return null;
        }
        return  new Event($user, NULL, NULL, $_SESSION['eventId']);
    }
    return null;
}

function GER2UTC($date,$time){
    // set default timezone
    date_default_timezone_set('Asia/Jerusalem');

    $dateTime =  strtotime("$date"."$time");

    date("Y-m-dTG:i:sz",$dateTime);
    date_default_timezone_set("UTC");
    $UTCtime = date("Y-m-dTG:i:sz", $dateTime);

    // get rid of "UTC" in $UTCtime
    $timeArray = explode('UTC',$UTCtime);
    $newTime = $timeArray[0]." ".$timeArray[1];

    //take only the Date XXXX-XX-XX and number XX:XX:XX (total 19 characters)
    return substr($newTime, 0, 19);
}



/**
 * createMessageUnion:  create the united messages table
 * @return bool true if table created / false otherwise
 */
/*
function getMessageUnion(){

    // get events id's
    $IDs = DB::select("SELECT * FROM Events");
    if (!$IDs) {
        throw new Exception("createMessageUnion: couldn't get Events table from database");
    }

    // prepare query (append while ID[i]['ID'] is not null)
    $i=1;
    // make query safe
    $id = $IDs[0]['ID'];
    $string = "(SELECT * FROM messages$id) ";

    while ($IDs[$i]['ID']){
        $id = $IDs[$i]['ID'];
        $string = $string . "UNION (SELECT * FROM messages$id) ";
        $i++;
    }

    $result = DB::select($string);
    if (!$result) {
        throw new Exception("createMessageUnion: couldn't create Message Union table from database");
    }
    return $result;
}
*/



