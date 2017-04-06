<?php

// database preperation
/*
 * use utf8 for hebrew:
 * set character_set_server=utf8;
 * set character_set_results=utf8;
 * set character_set_database=utf8;
 * set character_set_connection=utf8;
 * set character_set_client=utf8;
 * set names 'utf8';
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

?>