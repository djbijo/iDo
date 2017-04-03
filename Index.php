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
        $EventEmail = 'oriah@gmail.com';
        
        // checkUserID
        //$user = User::checkUserID($ID);
        
        // new user with new event
        //$user = new User($ID, $Name, $Email, $Phone, $EventName, $EventDate, $EventPhone);
        
        // new user w/o new event
        //$user = new User($ID, $Name, $Email);
        
        // delete user
        //$user = new user($ID);
        //$user->deleteUser();
        
        // getEevents
        //$user = new User($ID);
        //var_dump($user->getEvents());
        
        // add user phone
        //$user = new User($ID);
        //$user->addUserPhone($Phone);
        
        // add user permission
        //$Email = 'Dan@gmail.com';
        //$Permission = 'edit';
        //$user = new User($ID);
        //$user->addUserPermissions($Email, $Permission);
        
        // editUserPermission
        //$Email = 'Dan@gmail.com';
        //$Permission = 'view';
        //$user = new User($ID);
        //$user->editUserPermissions($Email, $Permission);
        
        //make new event
        //$user = new User ($ID);
        //$user::$event = new Event($user, NULL , $EventName, $EventDate, $EventPhone, $EventEmail)
        
        // before delete
        
        
        //deleteEvent
        //$user = new User($ID, $Name, $Email, $Phone, $EventName, $EventDate, $EventPhone);
        //$Email = 'Dan@gmail.com';
        //$Permission = 'edit';
        //$user->addUserPermissions($Email, $Permission);
        
        $user = new User($ID);
        $user::$event->deleteEvent($user);
        
        
        ?>
    </body>
</html>
