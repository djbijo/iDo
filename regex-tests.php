<?php

$nick = "ביגו";
$msg = " שלום {כינוי} אתה מוזמן {כינוי} למסיבה וקוראים לך {שם}";
$name = "גיל";

$nickstr = '{כינוי}';
$namestr = '{שם}';
$regex = "/($nickstr)/";
$out = preg_replace($regex,$nick, $msg);
var_dump ($out);
$regex = "/($namestr)/";
$out = preg_replace($regex,$name, $out);
var_dump($out);