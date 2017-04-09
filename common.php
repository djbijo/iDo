<?php

require_once ("DB.php");
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

function phoneFromStr($str){
$sanitized = filter_var($str, FILTER_SANITIZE_NUMBER_INT);

}

function validatePhone($phone){
    $regexp = "/05([-]*\d){8}|(\+972(-*5)(-*\d){8})/";
    return filter_var($phone, FILTER_VALIDATE_REGEXP,
        array("options"=>array("regexp"=>$regexp)));
}

/**
 * createMessageUnion:  create the united messages table
 * @return bool true if table created / false otherwise
 */

public function getMessageUnion(){

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



?>