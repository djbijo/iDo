<?php
session_start();
$pk = $_POST['pk'];
$name = $_POST['name'];
$value = $_POST['value'];

$response;
if ($value == "err" || !$value) {
    $response["success"] = false;
    $response["msg"] = "oops!";
}
else
    $response["success"] = true;

echo json_encode($response);