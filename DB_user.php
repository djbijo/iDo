<?php

include ("DB.php");
include ("DB_event.php");

interface iUser {

    //private function checkUserID($ID);

    public function addUser($ID, $Name, $Email, $Phone, $Event1, $Permission1, $Event2 = 'NULL', $Permission2 = 'NULL', $Event3 = 'NULL', $Permission3 = 'NULL');

    public function deleteUser($ID);

    public function editUserPermissions($Email, $Event, $Permission);

    public function addUserPermissions($Email, $Event, $Permission);

    public function getEvents($ID);

    public function newUser($ID, $Name, $Email, $Phone, $EventName, $EventDate);
}

class User implements iUser {

    /**
     * checkUserID:  Check if User ID exists in the database
     * @param string $ID : user ID
     * @return bool: false = user not in database / true = user in database
     */
    private function checkUserID($ID) {
        $db = new DB();
        // Make strings query safe
        $id = $db->quote($ID);

        // Search for user ID in Users table
        $result = $db->select("SELECT * FROM Users WHERE ID=$id");
        if (!$result) {
            return false;
        }
        return true;
    }

    /**
     * checkUserContactInfo:  Check if User phone and/or email exists in the database
     * @param string $Email :  user login email
     * @param string $Phone :  user cell phone
     * @return bool: false = user not in database / true = user in database
     */
    private function checkUserEmail($Email) {
        $db = new DB();
        // Make strings query safe
        $email = $db->quote($Email);

        // Search for user Email or phone in Users table
        $result = $db->select("SELECT * FROM Users WHERE Email=$email");
        if (!$result) {
            return false;
        }
        return true;
    }

    /**
     * checkUserContactInfo:  Check if User phone and/or email exists in the database
     * @param string $Email :  user login email
     * @param string $Phone :  user cell phone
     * @return bool: false = user not in database / true = user in database
     */
    private function checkUserPhone($Phone) {
        $db = new DB();
        // Make strings query safe
        $phone = $db->quote($Phone);

        // Search for user Email or phone in Users table
        $result = $db->select("SELECT * FROM Users WHERE Phone=$phone");
        if (!$result) {
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
    public function addUser($ID, $Name, $Email, $Phone, $Event1, $Permission1, $Event2 = 'NULL', $Permission2 = 'NULL', $Event3 = 'NULL', $Permission3 = 'NULL') {

        //check if ID/Phone/Email already in Users table
        if ($this->checkUserID($ID)) {
            echo 'User already registered to iDO';
            return false;
        }
        if ($this->checkUserEmail($Email)) {
            echo "the Email adress: '$Email' is already registered to iDO";
            return false;
        }
        if ($this->checkUserPhone($Phone)) {
            echo "the Phone number: '$Phone' is already registered to iDO";
            return false;
        }

        $db = new DB();

        // Make strings query safe
        $id = $db->quote($ID);
        $email = $db->quote($Email);
        $phone = $db->quote($Phone);
        $name = $db->quote($Name);
        $permission1 = $db->quote($Permission1);
        $permission2 = $db->quote($Permission2);
        $permission3 = $db->quote($Permission3);

        //insert user to Users table
        $result = $db->query("INSERT INTO Users (ID, Name, Email, Phone, Event1, permission1, Event2, permission2, Event3, permission3) VALUES
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
    public function deleteUser($ID) {
        $db = new DB();
        // Make strings query safe
        $id = $db->quote($ID);

        // Delete user from Users table
        $result = $db->query("DELETE FROM Users WHERE ID=$id");
        if (!$result) {
            return false;
        }
        return true;
    }

    /**
     * editUserPermissions: edit the permissions granted for the user
     * @param string $Email : user email
     * @param int $Event : event ID of the relevant event
     * @param string $Permission : change to this permission
     * @return bool false = user not in Users table / true = user permission changed or no need to modify
     */
    public function editUserPermissions($Email, $Event, $Permission) {

        //check that the user is already registered to iDO services
        if (!$this->checkUserEmail($Email)) {
            echo "Error: the user with Email adress:'$Email' is not registered to iDO. please register the user before granting permissions.";
            return false;
        }

        $db = new DB();
        // Make strings query safe
        $email = $db->quote($Email);
        $permission = $db->quote($Permission);

        // Update relevant user in user table
        # Update Event1
        $db->query("UPDATE Users SET Permission1=$permission
			WHERE Email=$email AND Event1=$Event");

        $rows = $db->affectedRows();
        # If Event1 not updated, Update Event2
        if ($rows <= 0) {
            $result = $db->query("UPDATE Users SET Permission2=$permission
				WHERE Email=$email AND Event2=$Event");
            $rows = $db->affectedRows();
            # If Event1 and Event2 not updated, Update event3			
            if ($rows <= 0) {
                $result = $db->query("UPDATE Users SET Permission3=$permission
					WHERE Email=$email AND Event3=$Event");
                $rows = $db->affectedRows();
                # If no Event updated: error
                if ($rows <= 0) {
                    return false;
                }
            }
        }
        return true;
    }

    /**
     * addUserPermissions: add a permissions granted for the user
     * @param string $Email : user email
     * @param int $Event : event ID of the relevant event
     * @param string $Permission : change to this permission
     * @return bool false = user not in Users table or already has 3 events / true = user permission added
     */
    public function addUserPermissions($Email, $Event, $Permission) {

        //check that the user is already registered to iDO services
        if (!$this->checkUserEmail($Email)) {
            echo "Error: the user with Email adress:'$Email' is not registered to iDO. please register the user before granting permissions.";
            return false;
        }

        $db = new DB();
        // Make strings query safe
        $email = $db->quote($Email);
        $permission = $db->quote($Permission);

        // Update relevant user in user table
        # Update Event1
        $db->query("UPDATE Users SET Event1=$Event , Permission1=$permission
			WHERE Email=$email AND Event1 IS NULL");

        $rows = $db->affectedRows();
        # If Event1 not updated, Update Event2
        if ($rows <= 0) {
            $result = $db->query("UPDATE Users SET Event2=$Event , Permission2=$permission
				WHERE Email=$email AND Event2 IS NULL");
            $rows = $db->affectedRows();
            # If Event1 and Event2 not updated, Update event3			
            if ($rows <= 0) {
                $result = $db->query("UPDATE Users SET Event3=$Event , Permission3=$permission
					WHERE Email=$email AND Event3 IS NULL");
                $rows = $db->affectedRows();
                # If no Event updated: error
                if ($rows <= 0) {
                    return false;
                }
            }
        }
        return true;
    }

    /**
     * getEvents: get all events and permissions for user
     * @param string $ID : user ID
     * @return array[6]  array['event1'] = event1 id, array['permission1'] = event1 permission, 
     *                   array['event2'] = event2 id, array['permission2'] = event2 permission,
     *                   array['event3'] = event3 id, array['permission3'] = event3 permission,
     */
    public function getEvents($ID) {
        $db = new DB();
        // Make strings query safe
        $id = $db->quote($ID);

        $result = $db->select("SELECT * FROM Users WHERE ID=$id");

        $out['event1'] = $result[6];
        $out['permission1'] = $result[7];
        $out['event2'] = $result[8];
        $out['permission2'] = $result[9];
        $out['event3'] = $result[10];
        $out['permission3'] = $result[11];
        return true;
    }

    /**
     * newUser: create new user linked to new event with 'root' permission.
     * @param string $ID : user id
     * @param string $Name : user name
     * @param string $Email : user login email
     * @param string $Phone : user cell phone number
     * @param string $EventName : name of event owner/owners or name of event
     * @param date $EventDate : date of event 
     * @return type false = user already in Users table / eventID = user was added to user table  
     */
    public function newUser($ID, $Name, $Email, $Phone, $EventName, $EventDate) {
        //check if ID/Phone/Email already in Users table
        if ($this->checkUserID($ID)) {
            echo 'User already registered to iDO';
            return false;
        }
        if ($this->checkUserEmail($Email)) {
            echo "the Email adress: '$Email' is already registered to iDO";
            return false;
        }
        if ($this->checkUserPhone($Phone)) {
            echo "the Phone number: '$Phone' is already registered to iDO";
            return false;
        }
        
        // create new user and new event
        $Event = new Event();
        $event = $Event->createEvent($EventName, $EventDate);
        $result = $this->addUser($ID, $Name, $Email, $Phone, $event, 'root');
        
        if (!$result) {
            return false;
        }
        return $event;
    }
}

?>