<?php


$msg = " שלום {כינוי} אתה מוזמן {כינוי} למסיבה וקוראים לך {שם}";

$patterns = array();
$replacements = array();

$patterns[0] = '/{כינוי}/';
$patterns[1] = '/{שם}/';

$replacements[0] = "ביגו";
$replacements[1] = "גיל";

echo preg_replace($patterns,$replacements, $msg);
