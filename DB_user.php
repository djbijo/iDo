<?php

include ("DB.php");

interface iUser {

    //private function checkUserID($ID);

    public function addUser($ID, $Name, $Email, $Phone, $Event1, $Permission1, $Event2 = NULL, $Permission2 = NULL, $Event3 = NULL, $Permission3 = NULL);

    public function deleteUser($ID);

    public function editUserPermissions($Email, $Event, $Permission);

    public function getEvents($ID);

    public function newUser($ID, $Name, $Email, $Phone, $EventName, $EventDate);
}

class User implements iUser {
    /*
     * checkUserID:  Check if User ID exists in the database
     * $ID string: 	 user ID
     * @return bool: false = user not in database / true = user in database
     */

    private function checkUserID($ID) {
        $db = new DB();
        // Make strings query safe
        $id = $db->quote($ID);

        // Search for user ID in Users table
        $result = $db->select("SELECT * FROM Users WHERE ID='$id'");
        if (!$result) {
            return false;
        }
        return true;
    }

    /*
     * addUser: Add user to Users table
     * $ID          string: user id
     * $Name        string: user name
     * $Email       string: user login email
     * $Phone       string: user cellphone number
     * $Event1      int:    event id for 1st event
     * $Permission1 string: user permission type for event1(root/edit/review)
     * $Event2      int:    event id for 2nd event
     * $Permission2 string: user permission type for event2(root/edit/review)
     * $Event3      int:    event id for 3rd event
     * $Permission3 string: user permission type for event3(root/edit/review)
     * @return      bool:   false = user already in Users table / true = user added to Users table
     */

    public function addUser($ID, $Name, $Email, $Phone, $Event1, $Permission1, $Event2 = NULL, $Permission2 = NULL, $Event3 = NULL, $Permission3 = NULL) {
        //check if ID already in Users table
        if ($this->checkUserID($ID)) {
            $output = 'Error Inserting user to Users table: User already registered to iDO';
            return false;
        }
        $db = new DB();

        // Make strings query safe
        $id = $db->quote($ID);
        $name = $db->quote($Name);
        $email = $db->quote($Email);
        $phone = $db->quote($Phone);
        $permission1 = $db->quote($Permission1);
        $permission2 = $db->quote($Permission2);
        $permission3 = $db->quote($Permission3);

        //insert user to Users table
        $result = $db->query("INSERT INTO Users (ID, Name, Email, Phone, Event1, Permission1, Event2, Permission2, Event3, Permission3) VALUES
			('$id', '$name', '$email', '$phone', '$Event1', '$permission1', '$Event2', '$permission2', '$Event3', '$permission3')");

        if (!$result) {
            return false;
        }
        return true;
    }

    /*
     * deleteUser: 	 delete user row from Users table
     * $ID string: 	 user ID
     * @return bool: false = user not in Users table / true = user deleted from Users table
     */

    public function deleteUser($ID) {
        $db = new DB();
        // Make strings query safe
        $id = $db->quote($ID);

        // Delete user from Users table
        $result = $db->query("DELETE FROM Users WHERE ID = '$id'");
        if (!$result) {
            return false;
        }
        return true;
    }

    /*
     * editUserPermissions: edit the permissions granted for the user
     * $Email 	string: user email
     * $Event 	int: 	event ID of the relevant event
     * $Permission  string: change to this permission
     * @return      bool: 	false = user not in Users table or already has 3 events / true = user permission changed or no need to modify
     */

    public function editUserPermissions($Email, $Event, $Permission) {
        $db = new DB();

        // Make strings query safe
        $email = $db->quote($Email);
        $permission = $db->quote($Permission);

        // Update relevant user in user table
        # Update Event1
        $result = $db->query("UPDATE Users SET Event1='$Event', Permission1='$permission'
			WHERE Email='$email' AND (Event1='$Event' OR Event1 IS NULL)");

        # If Event1 not updated, Update Event2
        if (!$result) {
            $result = $db->query("UPDATE Users SET Event2='$Event', Permission2='$permission'
				WHERE Email='$email' AND (Event2='$Event' OR Event2 IS NULL)");

            # If Event1 and Event2 not updated, Update event3			
            if (!$result) {
                $result = $db->query("UPDATE Users SET Event3='$Event', Permission3='$permission'
					WHERE Email='$email' AND (Event3='$Event' OR Event3 IS NULL)");

                # If no Event updated: error
                if (!$result) {
                    return false;
                }
            }
        }
        return true;
    }

    /*
     * getevents: get all events and permissions for user
     * $ID string: user ID
     * @return: array[6] when array['event1'] = event1 id, array['permission1'] = event1 permission, 
     *                        array['event2'] = event2 id, array['permission2'] = event2 permission,
     *                        array['event3'] = event3 id, array['permission3'] = event3 permission,
     */

    public function getEvents($ID) {
        $db = new DB();

        // Make strings query safe
        $id = $db->quote($ID);

        $result = $db->query("SELECT * FROM Users WHERE ID='$id'");

        $out['event1'] = $result[6];
        $out['permission1'] = $result[7];
        $out['event2'] = $result[8];
        $out['permission2'] = $result[9];
        $out['event3'] = $result[10];
        $out['permission3'] = $result[11];

        return $out;
    }

    /*
     * newUser: create new user linked to new event with 'root' permission.
     * $ID 		string: user id
     * $Name 	string: user name
     * $Email       string: user login email
     * $Phone 	string: user cellphone number
     * $EventName 	string: name of event owner/owners or name of event
     * $EventDate 	date: 	date of event 
     * @return: 	false = user already in Users table / eventID = user was added to user table  
     */

    public function newUser($ID, $Name, $Email, $Phone, $EventName, $EventDate) {
        $db = new DB();

        // Make strings query safe
        $id = $db->quote($ID);
        $name = $db->quote($Name);
        $email = $db->quote($Email);
        $phone = $db->quote($Phone);
        $eventName = $db->quote($EventName);

        $Event = new Event();
        $event = $Event->createEvent($eventName, $EventDate);
        $result = $this->addUser($id, $name, $email, $phone, $event, 'root');

        if (!$result) {
            return false;
        }
        return $event->eventID;
    }

}

?>