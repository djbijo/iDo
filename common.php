<?php

function phoneFromStr($str){
    $sanitized = filter_var($str, FILTER_SANITIZE_NUMBER_INT);
    
}

function validatePhone($phone){
    $regexp = "/05([-]*\d){8}|(\+972(-*5)(-*\d){8})/";
    return filter_var($phone, FILTER_VALIDATE_REGEXP,
            array("options"=>array("regexp"=>$regexp)));
}

?>