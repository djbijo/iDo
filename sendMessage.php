<?php
// sendMessage.php
include "smsGateway.php";
require "common.php";
$errors         = array();      // array to hold validation errors
$data           = array();      // array to pass back data

// validate the variables ======================================================
    // if any of these variables don't exist, add an error to our $errors array

    if (empty($_POST['name']))
        $errors['name'] = 'נא להזין שם';
    if (empty($_POST['message']))
        $errors['message'] = 'תוכן ההודעה ריק';
    if (empty($_POST['phone']))
        $errors['phone'] = 'יש להזין מספר טלפון';
    else if (!validatePhone($_POST['phone']))
        $errors['phone'] = 'יש להזין מספר טלפון בעל 10 ספרות';

    // if (empty($_POST['superheroAlias']))
        // $errors['superheroAlias'] = 'Superhero alias is required.';

// return a response ===========================================================

    // if there are any errors in our errors array, return a success boolean of false
    if ( ! empty($errors)) {

        // if there are items in our errors array, return those errors
        $data['success'] = false;
        $data['errors']  = $errors;
    } else {

        // if there are no errors process our form, then return a message
        $smsGateway = new SmsGateway('dj.bijo@gmail.com', 'aphrt149');

        $deviceID = 42911;
        $number = $_POST['phone'];
        $message = $_POST['message'];

        $options = [
        'send_at' => strtotime('+1 minutes'), // Send the message in 10 minutes
        'expires_at' => strtotime('+1 hour') // Cancel the message in 1 hour if the message is not yet sent
        ];

        //Please note options is no required and can be left out
        $result = $smsGateway->sendMessageToNumber($number, $message, $deviceID, $options);

        // show a message of success and provide a true success variable
        $data['success'] = true;
        //$data['message'] = 'Success!';
        $data['message'] = $result;
    }

    // return all our data to an AJAX call
    echo json_encode($data);