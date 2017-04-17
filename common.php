<?php
require  'vendor/autoload.php';
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


function validatePhone($phone){
    if ($phone==NULL or $phone=='NULL') return 'NULL';

    $phoneUtil = \libphonenumber\PhoneNumberUtil::getInstance();
    //throws exception if number is invalid
    try {
        $ilNumberProto = $phoneUtil->parse($phone, "IL");
    } catch (\libphonenumber\NumberParseException  $e){
        return false;
    }
    if ($phoneUtil->isValidNumberForRegion($ilNumberProto,"IL")){
        return "'".$phoneUtil->format($ilNumberProto, \libphonenumber\PhoneNumberFormat::NATIONAL)."'";
    }
    return false;
}

function validateEmail($email){
    if ($email==NULL or $email=='NULL') return 'NULL';
    return "'".filter_var($email, FILTER_VALIDATE_EMAIL)."'";
}

//from http://www.developphp.com/video/PHP/Random-String-Generator-PHP-Function-Programming-Tutorial
// (with modifications)
function randStrGen($len){
    $result = "";
    $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
    $charArray = str_split($chars);
    for($i = 0; $i < $len; $i++){
        $randItem = array_rand($charArray);
        $result .= "".$charArray[$randItem];
    }
    return $result;
}

function GER2UTC($date,$time){
    // set default timezone
    date_default_timezone_set('Asia/Jerusalem');
    $dateTime =  strtotime("$date"."$time");
    date_default_timezone_set("UTC");
    return date("Y-m-d G:i:s", $dateTime);
}

function UNIX2GER($unixTime){
    $utcTime = new DateTime(null, new DateTimeZone('UTC'));
    $utcTime->setTimestamp($unixTime);

    $utcTime->setTimezone(new DateTimeZone('Asia/Jerusalem'));
    return $utcTime->format("Y-m-d G:i:s");
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



