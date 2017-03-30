<?php

include ("DB.php");
include ("DB_event.php");

interface iUser {

    public function addUser($ID, $Name, $Email, $Phone = 'NULL', $Event1 = 'NULL', $Permission1 = 'NULL', $Event2 = 'NULL', $Permission2 = 'NULL', $Event3 = 'NULL', $Permission3 = 'NULL');

    public function deleteUser();

    public function editUserPermissions($Email, $Permission);

    public function addUserPermissions($Email, $Permission);

    public function addUserPhone($Phone);

    public function selectEvent($EventID);

    public function getEvents();

    public function getDB();

    public function getID();
}

class User implements iUser {

    protected static $id;
    protected static $db;
    public static $event;

    /**
     * __construct: create new user object. if user not in Users table (name,email,phone,eventName,eventDate!=Null) add user to users list.
     * "event" is 1st event on list, until chosen otherwise.
     * @param string $ID : user id
     * @param string $Name : user name , DEFAULT=NULL
     * @param string $Email : user login email , DEFAULT=NULL
     * @param string $Phone : user cell phone number , DEFAULT=NULL
     * @param string $EventName : name of event owner/owners or name of event , DEFAULT=NULL
     * @param date $EventDate : date of event , DEFAULT=NULL
     * @return object user  
     */
    public function __construct($ID, $Name = 'NULL', $Email = 'NULL', $Phone = 'NULL', $EventName = 'NULL', $EventDate = 'NULL', $EventPhone = 'NULL') {
        // create new DB 
        if (!isset(self::$db)) {
            self::$db = new DB();
        }

        // user is in users table (registered to iDO)
        if ($this->checkUserID($ID)) {
            if (!isset(self::$id)) {
                self::$id = self::$db->quote($ID);
            }

            // construct an events with only events ID to it!!!!            $$$$$$$

            $events = $this->getEvents();
            self::$event = new Event($this);
            self::$event->eventID = $events['event1'];
            return;
        }

        // user is not in users table (not registered to iDO)
        if ($Name != 'NULL' and $Email != 'NULL') {

            // user with new event
            if ($EventName != 'NULL' and $EventDate != 'NULL') {
                if (!isset(self::$id)) {
                    self::$id = self::$db->quote($ID);
                }

                if (!isset($event)) {
                    self::$event = new Event($this, $EventName, $EventDate, $Email, $EventPhone);
                }

                $result = $this->addUser($ID, $Name, $Email, $Phone, self::$event->eventID, 'root');
                if (!$result) {
                    return false;
                }
            }

            // new user w/o new event
            else {
                $result = $this->addUser($ID, $Name, $Email, $Phone);
                if (!$result) {
                    return false;
                }
            }
            return;
        }
        return false;
    }

    /**
     * checkUserID:  Check if User ID exists in the database
     * @param string $ID : user ID
     * @return bool: false = user not in database / true = user in database
     */
    private function checkUserID($ID) {
        // Make strings query safe
        $id = self::$db->quote($ID);

        // Search for user ID in Users table
        if (!self::$db->select("SELECT * FROM Users WHERE ID=$id")) {
            return false;
        }
        return true;
    }

    /**
     * checkUserEmail:  Check if User email exists in the database
     * @param string $Email :  user login email
     * @param string $Phone :  user cell phone
     * @return bool: false = email not in database / true = email in database
     */
    private function checkUserEmail($Email) {
        // Make strings query safe
        $email = self::$db->quote($Email);

        // Search for user Email or phone in Users table
        if (!self::$db->select("SELECT * FROM Users WHERE Email=$email")) {
            return false;
        }
        return true;
    }

    /**
     * checkUserPhone:  Check if User phon exists in the database
     * @param string $Email :  user login email
     * @param string $Phone :  user cell phone
     * @return bool: false = phone not in database / true = phone already in database
     */
    private function checkUserPhone($Phone) {
        // Make strings query safe
        $phone = self::$db->quote($Phone);

        // Search for user phone in Users table
        if (!self::$db->select("SELECT * FROM Users WHERE Phone=$phone")) {
            return false;
        }
        return true;
    }

    /**
     * addUser: Add user to Users table
     * @param string $ID : user id
     * @param string $Name : user name
     * @param string $Email : user login email
     * @param string $Phone : user cell phone number
     * @param int $Event1 : event id for 1st event
     * @param string $Permission1 : user permission type for event1(root/edit/review)
     * @param int $Event2 : event id for 2nd event
     * @param string $Permission2 : user permission type for event2(root/edit/review)
     * @param int $Event3 : event id for 3rd event
     * @param string $Permission3 : user permission type for event3(root/edit/review)
     * @return bool false = user already in Users table / true = user added to Users table
     */
    public function addUser($ID, $Name, $Email, $Phone = 'NULL', $Event1 = 'NULL', $Permission1 = 'NULL', $Event2 = 'NULL', $Permission2 = 'NULL', $Event3 = 'NULL', $Permission3 = 'NULL') {
        //check if ID/Phone/Email already in Users table
        if ($this->checkUserID($ID)) {
            echo 'User already registered to iDO';
            return false;
        }
        if ($this->checkUserEmail($Email)) {
            echo "the Email adress: '$Email' is already registered to iDO";
            return false;
        }
        /*
          if ($this->checkUserPhone($Phone)) {
          echo "the Phone number: '$Phone' is already registered to iDO";
          return false;
          }
         */

        // Make strings query safe
        $email = self::$db->quote($Email);
        $phone = self::$db->quote($Phone);
        $name = self::$db->quote($Name);
        $Permission1 != 'NULL' ? $permission1 = self::$db->quote($Permission1) : $permission1 = 'NULL';
        $Permission2 != 'NULL' ? $permission2 = self::$db->quote($Permission2) : $permission2 = 'NULL';
        $Permission3 != 'NULL' ? $permission3 = self::$db->quote($Permission3) : $permission3 = 'NULL';

        // save user ID
        if (!isset(self::$id)) {
            self::$id = self::$db->quote($ID);
        }
        $id = self::$id;

        //insert user to Users table
        $result = self::$db->query("INSERT INTO Users (ID, Name, Email, Phone, Event1, permission1, Event2, permission2, Event3, permission3) VALUES
			($id, $name, $email, $phone, $Event1, $permission1, $Event2, $permission2, $Event3, $permission3)");
        if (!$result) {
            return false;
        }
        return true;
    }

    /**
     * deleteUser: 	 delete user row from Users table
     * @param string $ID : user ID
     * @return bool false = user not in Users table / true = user deleted from Users table
     */
    public function deleteUser() {
        // Make strings query safe
        $id = self::$id;

        // Delete user from Users table
        if (!self::$db->query("DELETE FROM Users WHERE ID=$id")) {
            return false;
        }
        return true;
    }

    /**
     * editUserPermissions: edit the permissions granted for the user. one can edit permission only to the event chosen
     * @param string $Email : user email
     * @param string $Permission : change to this permission
     * @return bool false = user not in Users table or user not root fur this event / true = user permission changed or no need to modify
     */
    public function editUserPermissions($Email, $Permission) {

        //check that the user is already registered to iDO services
        if (!$this->checkUserEmail($Email)) {
            echo "Error: the user with Email adress:'$Email' is not registered to iDO. please register the user before granting permissions.";
            return false;
        }

        // Make strings query safe
        $email = self::$db->quote($Email);
        $permission = self::$db->quote($Permission);

        //check if user is root for this event
        $eventID = self::$event->eventID;
        $result = self::$db->select("SELECT * FROM Events WHERE ID=$eventID");
        $rootID = self::$db->quote($result[0]['RootID']);

        if ($rootID != self::$id) {
            echo "Only the user which created the event can change permissions to it.";
            return false;
        }

        // Update relevant user in user table
        # Update Event1
        self::$db->query("UPDATE Users SET Permission1=$permission
			WHERE Email=$email AND Event1=$eventID");

        $rows = self::$db->affectedRows();
        # If Event1 not updated, Update Event2
        if ($rows <= 0) {
            $result = self::$db->query("UPDATE Users SET Permission2=$permission
				WHERE Email=$email AND Event2=$eventID");
            $rows = self::$db->affectedRows();
            # If Event1 and Event2 not updated, Update event3			
            if ($rows <= 0) {
                $result = self::$db->query("UPDATE Users SET Permission3=$permission
					WHERE Email=$email AND Event3=$eventID");
                $rows = self::$db->affectedRows();
                # If no Event updated: error
                if ($rows <= 0) {
                    echo "user $email has is not registered to this event, please add user to event with relevant permission.";
                    return false;
                }
            }
        }
        return true;
    }

    /**
     * addUserPermissions: add a permissions granted for the user. one can add permission only to the event chosen
     * @param string $Email : user email
     * @param string $Permission : change to this permission
     * @return bool false = user not in Users table or already has 3 events / true = user permission added
     */
    public function addUserPermissions($Email, $Permission) {

        //check that the user is already registered to iDO services
        if (!$this->checkUserEmail($Email)) {
            echo "Error: the user with Email adress:'$Email' is not registered to iDO. please register the user before granting permissions.";
            return false;
        }

        // Make strings query safe
        $email = self::$db->quote($Email);
        $permission = self::$db->quote($Permission);

        //check if user is root for this event
        $eventID = self::$event->eventID;
        $result = self::$db->select("SELECT * FROM Events WHERE ID=$eventID");
        $rootID = self::$db->quote($result[0]['RootID']);

        if ($rootID != self::$id) {
            echo "Only the user which created the event can add permissions to it.";
            return false;
        }
        // Update relevant user in user table
        # Update Event1
        self::$db->query("UPDATE Users SET Event1=$eventID , Permission1=$permission
			WHERE Email=$email AND Event1 IS NULL");

        $rows = self::$db->affectedRows();
        # If Event1 not updated, Update Event2
        if ($rows <= 0) {
            $result = self::$db->query("UPDATE Users SET Event2=$eventID , Permission2=$permission
				WHERE Email=$email AND Event2 IS NULL");
            $rows = self::$db->affectedRows();
            # If Event1 and Event2 not updated, Update event3			
            if ($rows <= 0) {
                $result = self::$db->query("UPDATE Users SET Event3=$eventID , Permission3=$permission
					WHERE Email=$email AND Event3 IS NULL");
                $rows = self::$db->affectedRows();
                # If no Event updated: error
                if ($rows <= 0) {
                    echo "User $email has too many events registered (max 3 events per user)";
                    return false;
                }
            }
        }
        return true;
    }

    public function addUserPhone($Phone) {
        // Make strings query safe
        $phone = self::$db->quote($Phone);
        $id = self::$id;

        //check if phone number already in iDO database
        if ($this->checkUserPhone($Phone)) {
            echo "the Phone number: $phone is already registered to iDO";
            return false;
        }

        // update user with phone number
        self::$db->query("UPDATE Users SET Phone=$phone WHERE ID=$id");
        $rows = self::$db->affectedRows();
        if ($rows < 0) {
            return false;
        }
        return true;
    }
    
    /**
     * selectEvent: select Event out of user possible events (call getEvents() function ahead of this function)
     * @param int $EventID : the event to change to, according to user choice
     * @return void
     */
    public function selectEvent($EventID) {
        self::$event = $EventID;    
    }

    /**
     * getEvents: get all events and permissions for user
     * @param string $ID : user ID
     * @return array[6]  array['event1'] = event1 id, array['permission1'] = event1 permission, 
     *                   array['event2'] = event2 id, array['permission2'] = event2 permission,
     *                   array['event3'] = event3 id, array['permission3'] = event3 permission,
     */
    public function getEvents() {

        $id = self::$id;

        $result = self::$db->select("SELECT * FROM Users WHERE ID=$id");
        $out['event1'] = $result[0]['Event1'];
        $out['permission1'] = $result[0]['Permission1'];
        $out['event2'] = $result[0]['Event2'];
        $out['permission2'] = $result[0]['Permission2'];
        $out['event3'] = $result[0]['Event3'];
        $out['permission3'] = $result[0]['Permission3'];
        return $out;
    }

    /**
     * getDB:  get the DataBase
     * @return type db (DataBase) / false if Database yet initialized
     */
    public function getDB() {
        if (isset(self::$db)) {
            return self::$db;
        }
        return false;
    }

    /**
     * getDB:  get User ID
     * @return int  User ID / false if ID yet initialized
     */
    public function getID() {
        if (isset(self::$id)) {
            return self::$id;
        }
        return false;
    }

}

?>
