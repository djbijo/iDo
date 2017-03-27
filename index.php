<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <title>iDO user testing</title>
    </head>
    <body>
        <?php
        include ("DB_user.php");

        $ID = '1111';
        $Name = 'Gil';
        $Email = 'Gil@gmail.com';
        $Phone = '050-00210000';
        $Event = 1;
        $EventName = 'Wroot';
        $EventDate = '2018-08-12';
        $EventPhone = '050-00210000';
        $user = new User($ID);
        
        $user::$event->deleteEvent($user);

        //echo $result['permission2'];
        
        ?>
    </body>
</html>
