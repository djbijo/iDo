<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <title>iDO user testing</title>
    </head>
    <body>
        <?php
        include ("DB_user.php");

        $ID = '300';
        $Name = 'Gil';
        $Email = 'djbijo@gmail.com';
        $Phone = '052-8599996';
        $Event1 = 1;
        $Permission1 = 'root';
        $user = new User;
        $result = $user->addUser($ID, $Name, $Email, $Phone, $Event1, $Permission1);
        echo "User added: " . $result;

        ?>
    </body>
</html>
