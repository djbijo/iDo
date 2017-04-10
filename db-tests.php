<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <title>iDO user testing</title>
    </head>
    <body>
        <?php
        include ("DB_user.php");

        $ID = '123';
        $Name = 'Oriah';
        $Email = 'oriah@gmail.com';
        $Phone = '050-1111111';
        $Event = 1;
        $EventName = 'Oria@Chen';
        $EventDate = '2018-08-12';
        $EventPhone = '050-1111111';
//        $user = new User($ID, $Name, $Email, $Phone, $EventName, $EventDate, $EventPhone);

        //echo $result['permission2'];
        echo Event::makeHebrewDate($EventDate);

        ?>
    </body>
</html>
