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
        //echo $user;
        
        // new user with new event
        $user = new User($ID);
        $event = new Event($user, $EventName, $EventDate)
        //var_dump($user);
        
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
        //$user = new User($ID, $Name, $Email, $Phone, $EventName, $EventDate, $EventPhone);
        //$Email = 'Dan@gmail.com';
        //$Permission = 'edit';
        //$user->addUserPermissions($Email, $Permission);
        //deleteEvent
        //$user = new User($ID);
        //$user::$event->deleteEvent($user);
        
        //rsvp add
        /*
        $user = new User($ID);
        $user->deleteUser();
        
        $user = new User($ID, $Name, $Email, $Phone, $EventName, $EventDate, $EventPhone);
        
        $Name = 'gil';
        $SurName = 'levy';
        $Invitees = 2;
        $NickName = 'Bijo';
        $Phone = '5555555555';
        $Email = 'dj@gmail.com';
        $Groups = 'Friends';
        $RSVP = 1;
        $Ride = true;
        $user::$event->rsvp->add($Name, $SurName, $Invitees, $NickName, $Phone, $Email, $Groups, $RSVP, $Ride);
        */
        
        //rsvp get
        /*
        $user = new User($ID);
        $user->deleteUser();
        
        $user = new User($ID, $Name, $Email, $Phone, $EventName, $EventDate, $EventPhone);
        $user::$event->rsvp->add('gil', 'levy', 2, 'Bijo', '0555555555', 'Gil@gmail.com', 'Friends', 3, true);
        $user::$event->rsvp->add('mor', 'shalom', 1, '' , '0500000000', 'Mor@gmail.com', 'Friends', 0, false);
        $user::$event->rsvp->add('Motti', 'bakish', 10, 'Bakbak' , '051111111', 'bak@gmail.com', 'Family', 12, true);
        
        var_dump($user::$event->rsvp->get());
         */
        
        // RSVP getByGroups
        /*
        $eventID = 1;
        $array = explode(',','dogs');
        
        // prepare query (append while array[i] is not null)
        $i=1;
        // make query safe
        $query = "SELECT * FROM rsvp$eventID WHERE Groups=$array[0]";
        
        while ($array[$i] != NULL){
            $query = $query . " OR Groups=$array[$i]";
            $i++;
        }
        
        echo $query;
         */
        
        // messages append groups
        /*
        $Groups[0] = 'dogs';
        //$Groups[1] = 'cats';
        //$Groups[2] = 'mice';
        
        // prepare query (append while array[i] is not null)
        $i=1;
        // make query safe
        $group = $Groups[0];
        $string = "$group";
        
        while ($Groups[$i]){
            $group = $Groups[$i];
            $string = $string . ",$group";
            $i++;
        }
        
        echo $string;
         */
        
        
        
        
        ?>
    </body>
</html>
