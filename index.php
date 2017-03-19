<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <title>iDO user testing</title>
    </head>
    <body>
        <?php
        include ("DB_user.php");

        $ID = '324345tyhdfglsdfjtuhkgflid';
        $Name = 'Yossi';
        $Email = 'YossiHamelech@gmail.com';
        $Phone = '050-000000000';
        $EventName = 'WotWot';
        $EventDate = '2018-05-12';
        $user = new User();
        $event = new Event();
        
        $result = $event ->deleteEvent(14, '32');

        echo nl2br ("\n User added: " . $result."\n");
        
        ?>
    </body>
</html>
