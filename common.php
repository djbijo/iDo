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
    date_default_timezone_set("UTC");
    return date("Y-m-d G:i:s", $dateTime);
}

function UNIX2GER($unixTime){
    // set default timezone
    date_default_timezone_set('Asia/Jerusalem');
    return date("Y-m-d G:i:s", $unixTime);
}

function get_numerics ($str) {
    preg_match_all('/\d+/', $str, $matches);
    return $matches[0];
}

function dictionary($string){

    $patterns = array();
    $replacements = array();

    $patterns[0] = '/אחד/';
    $patterns[1] = '/אחת/';
    $patterns[2] = '/one/';
    $patterns[3] = '/שניים/';
    $patterns[4] = '/שתיים/';
    $patterns[5] = '/two/';
    $patterns[6] = '/שלוש/';
    $patterns[7] = '/שלושה/';
    $patterns[8] = '/three/';
    $patterns[9] = '/ארבע/';
    $patterns[10] = '/ארבעה/';
    $patterns[11] = '/four/';
    $patterns[12] = '/חמש/';
    $patterns[13] = '/חמישה/';
    $patterns[14] = '/five/';
    $patterns[15] = '/שש/';
    $patterns[16] = '/שישה/';
    $patterns[17] = '/six/';
    $patterns[18] = '/שבע/';
    $patterns[19] = '/שבעה/';
    $patterns[20] = '/seven/';
    $patterns[21] = '/שמונה/';
    $patterns[22] = '/שמונה/';
    $patterns[23] = '/eight/';
    $patterns[24] = '/תשע/';
    $patterns[25] = '/תשעה/';
    $patterns[26] = '/nine/';
    $patterns[27] = '/עשר/';
    $patterns[28] = '/עשרה/';
    $patterns[29] = '/ten/';

    $replacements[0] = '1';
    $replacements[1] = '1';
    $replacements[2] = '1';
    $replacements[3] = '2';
    $replacements[4] = '2';
    $replacements[5] = '2';
    $replacements[6] = '3';
    $replacements[7] = '3';
    $replacements[8] = '3';
    $replacements[9] = '4';
    $replacements[10] = '4';
    $replacements[11] = '4';
    $replacements[12] = '5';
    $replacements[13] = '5';
    $replacements[14] = '5';
    $replacements[15] = '6';
    $replacements[16] = '6';
    $replacements[17] = '6';
    $replacements[18] = '7';
    $replacements[19] = '7';
    $replacements[20] = '7';
    $replacements[21] = '8';
    $replacements[22] = '8';
    $replacements[23] = '8';
    $replacements[24] = '9';
    $replacements[25] = '9';
    $replacements[26] = '9';
    $replacements[27] = '10';
    $replacements[28] = '10';
    $replacements[29] = '10';

    return preg_replace($patterns,$replacements, $string);

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



