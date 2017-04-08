<?php

require_once ("DB.php");
require_once ("DB_event.php");

interface iUser {

    public function addUser($ID, $Name, $Email, $Phone = 'NULL', $Event1 = 'NULL', $Permission1 = 'NULL', $Event2 = 'NULL', $Permission2 = 'NULL', $Event3 = 'NULL', $Permission3 = 'NULL');

    public function deleteUser();

    public function editUserPermissions($Email, $Permission);

    public function addUserPermissions($Email, $Permission);

    public function addUserPhone($Phone);

    public function selectEvent($EventID);

    public function getEvents();

    public function getID();
}

class User implements iUser {

    protected $id;
    public $event;

    /**
     * __construct: create new user object. if user not in Users table (name,email,phone,eventName,eventDate!=Null) add user to users list.
     * "event" is 1st event on list, until chosen otherwise.
     * @param string $ID : user id
     * @param string $Name : user name , DEFAULT=NULL
     * @param string $Email : user login email , DEFAULT=NULL
     * @param string $Phone : user cell phone number , DEFAULT=NULL
     * @return object user  
     */
    public function __construct($ID, $Name = 'NULL', $Email = 'NULL', $Phone = 'NULL') {

        // user is in users table (registered to iDO)
        if ($this->checkUserID($ID)) {

            //shift user events left
            $this->shiftEvents();

            if (!isset($this->id)) {
                $this->id = DB::quote($ID);
            }
            //shift user events left
            $this->shiftEvents();


            // construct an events with only events ID to it
            $events = $this->getEvents();
            if ($events['event1'] === NULL) {
                return;
            }
            $this->event = new Event($this);
            return;
        }

        // user is not in users table (not registered to iDO)
        $result = $this->addUser($ID, $Name, $Email, $Phone);
        if (!$result) {
            throw new Exception("User New: addUser function error");
            return false;
        }
        return true;
    }

    public function addEvent($EventName, $EventDate, $EventTime=NULL, $Venue=NULL, $Address=NULL, $EventEmail=NULL, $EventPhone=NULL, $Password=NULL, $Secret=NULL, $DeviceID=NULL){
        $id = $this->id;
        $this->event = new Event($this,1 ,$EventName, $EventDate, $EventTime, $Venue, $Address, $EventEmail, $EventPhone, $Password, $Secret, $DeviceID);
        $result = DB::select("SELECT * FROM USERS WHERE ID=$id");
        if (!$result){
            throw new Exception("User addEvent: couldn't get user $id from users  table");
            return false;
        }
        $this->addUserPermissions($result[0]['Email'], 'root');
        return $this->event;
    }

    /**
     * checkUserID:  Check if User ID exists in the database
     * @param string $ID : user ID
     * @return bool: false = user not in database / true = user in database
     */
    static function checkUserID($ID) {

        // Make strings query safe
        $id = DB::quote($ID);

        // Search for user ID in Users table
        if (!DB::select("SELECT * FROM Users WHERE ID=$id")) {
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
        $email = DB::quote($Email);

        // Search for user Email or phone in Users table
        if (!DB::select("SELECT * FROM Users WHERE Email=$email")) {
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
        $phone = DB::quote($Phone);

        // Search for user phone in Users table
        if (!DB::select("SELECT * FROM Users WHERE Phone=$phone")) {
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
            throw new Exception("User addUser: User already registered to iDO");
            return false;
        }
        if ($this->checkUserEmail($Email)) {
            throw new Exception("User addUser: the Email address: '$Email' is already registered to iDO");
            return false;
        }
        if (!$Name) {
            throw new Exception("Name field in null");
            return;
        }
        if (!$Email) {
            throw new Exception("Email field in null");
            return;
        }
        // Make strings query safe
        $email = DB::quote($Email);
        $phone = DB::quote($Phone);
        $name = DB::quote($Name);
        $permission1 = DB::quote($Permission1);
        $permission2 = DB::quote($Permission2);
        $permission3 = DB::quote($Permission3);

        // save user ID
        if (!isset($this->id)) {
            $this->id = DB::quote($ID);
        }
        $id = $this->id;

        //insert user to Users table
        $result = DB::query("INSERT INTO Users (ID, Name, Email, Phone, Event1, permission1, Event2, permission2, Event3, permission3) VALUES
			($id, $name, $email, $phone, $Event1, $permission1, $Event2, $permission2, $Event3, $permission3)");
        if (!$result) {
            throw new Exception("User addUser: couldn't add user $this->id to users table");
            return false;
        }
        return true;
    }

    /**
     * deleteUser: 	 delete user row from Users table
     * @return bool false = user not in Users table / true = user deleted from Users table
     */
    public function deleteUser() {
        // Make strings query safe
        $id = $this->id;

        // Delete user from Users table
        if (!DB::query("DELETE FROM Users WHERE ID=$id")) {
            throw new Exception("User deleteUser: user not deleted from users table");
            return false;
        }
        return true;
    }

    /**
     * editUserPermissions: edit the permissions granted for the user. one can edit permission only to the event chosen
     * @param string $Email : user email
     * @param string $Permission : change to this permission
     * @return bool false = user not in Users table or user not root fur this event / true = user permission changed or no need to modify
     * @throws Exception if email already registered to the database
     */
    public function editUserPermissions($Email, $Permission) {

        //check that the user is already registered to iDO services
        if (!$this->checkUserEmail($Email)) {
            throw new Exception("User editUserPermissions: the user with Email adress:'$Email' is not registered to iDO. please register the user before granting permissions.");
            return false;
        }

        // Make strings query safe
        $email = DB::quote($Email);
        $permission = DB::quote($Permission);

        //check if user is root for this event
        $eventID = $this->event->eventID;
        $result = DB::select("SELECT * FROM Events WHERE ID=$eventID");
        $rootID = DB::quote($result[0]['RootID']);

        if ($rootID != $this->id) {
            throw new Exception("User editUserPermissions: Only the user which created the event can change permissions to it");
            return false;
        }

        // Update relevant user in user table
        for ($i = 1; $i <= 3; $i++) {
            DB::query("UPDATE Users SET Permission$i=$permission WHERE Email=$email AND Event$i=$eventID");

            //if event updated
            if (DB::affectedRows() > 0) {
                return true;
            }
        }

        throw new Exception("User editUserPermissions: user $email has is not registered to this event, please add user to event with relevant permission");
        return false;
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
            throw new Exception("User addUserPermissions: the user with Email adress:'$Email' is not registered to iDO. please register the user before granting permissions.");
            return false;
        }

        // Make strings query safe
        $email = DB::quote($Email);
        $permission = DB::quote($Permission);

        //check if user is root for this event
        $eventID = $this->event->eventID;

        $result = DB::select("SELECT * FROM Events WHERE ID=$eventID");
        $rootID = DB::quote($result[0]['RootID']);

        if ($rootID != $this->id) {
            throw new Exception("User addUserPermissions: Only the user which created the event can change permissions to it");
            return false;
        }
        // Update relevant user in user table
        for ($i = 1; $i <= 3; $i++) {
            DB::query("UPDATE Users SET Permission$i=$permission, Event$i=$eventID
			WHERE Email=$email AND Event$i IS NULL");

            //if event updated
            if (DB::affectedRows() > 0) {
                return true;
            }
        }
        throw new Exception("User addUserPermissions:User $email has too many events registered (max 3 events per user at a time)");
        return false;    
    }

    public function addUserPhone($Phone) {
        // Make strings query safe
        $phone = DB::quote($Phone);
        $id = $this->id;

        //check if phone number already in iDO database
        if ($this->checkUserPhone($Phone)) {
            throw new Exception("User addUserPhone: the Phone number $phone is already registered to iDO");
            return false;
        }

        // update user with phone number
        DB::query("UPDATE Users SET Phone=$phone WHERE ID=$id");

        if (DB::affectedRows() < 0) {
            throw new Exception("User addUserPhone: the Phone number $phone could not be added to iDO");
            return false;
        }
        return true;
    }

    /**
     * selectEvent: select Event out of user possible events (call getEvents() function before of this function)
     * @param int $EventID : the event to change to, according to user choice
     * @return void
     */
    public function selectEvent($EventID) {
        $eventID = DB::quote($EventID);
        $this->event->eventID = $eventID;           // todo: update eventID through function in event
        $id = $this->id;
        $events = $this->getEvents();
        $permission = NULL;

        // delete event from users records - w/o deleting the events tables
        for($i=1 ; $i<=3 ; $i++){
            DB::query("UPDATE Users SET Event$i=NULL , Permission$i=NULL WHERE Event$i=$eventID AND ID=$id");
            if (DB::affectedRows() >=1){
                $permission = DB::quote($events["permission$i"]);
                $j = $i;
            }
        }
        if($permission === NULL) throw new Exception("User selectEvent: error inserting event as event1");
        // clear 1st event in user record

        // switch event1 with relevant event
        $event1 = DB::quote($events['event1']);
        $permission1 = DB::quote($events['permission1']);

        DB::query("UPDATE Users SET Event1=$eventID, Permission1=$permission, Event$j=$event1, Permission$j=$permission1 WHERE ID=$id");
        if (DB::affectedRows() < 0) {
            throw new Exception("User selectEvent: couldn't switch event$EventID and event$event1 in users records");
            return false;
        }
    }

    /**
     * shiftEvents:  shift events left after one event has been deleted
     * @return int  Event ID / false if ID yet initialized
     */
    public function shiftEvents() {

        $result = $this->getEvents();
        if(!$result) return false;
        $id = $this->id;

        for ($i = 1; $i <= 2; $i++) {
            $j = $i + 1;

            if ($result["event$i"] === NULL and $result["event$j"] != NULL) {


                $eventJ = DB::quote($result["event$j"]);
                $permissionJ = DB::quote($result["permission$j"]);

                DB::query("UPDATE Users SET Event$i=$eventJ, Permission$i=$permissionJ WHERE ID=$id");
                $sql1 = DB::affectedRows();
                DB::query("UPDATE Users SET Event$j=NULL, Permission$j=NULL");
                $sql2 = DB::affectedRows();

                if ($sql1 < 0 or $sql2 < 0) {
                    throw new Exception(" User shiftEvents: couldn't shift events left for user $id");
                    return false;
                }
            }
        }
        return true;
    }

    /**
     * getEvents: get all events and permissions for user
     * @return array[6]  array['event1'] = event1 id, array['permission1'] = event1 permission,
     *                   array['event2'] = event2 id, array['permission2'] = event2 permission,
     *                   array['event3'] = event3 id, array['permission3'] = event3 permission
     */
    public function getEvents() {
        $id = $this->id;

        $result = DB::select("SELECT * FROM Users WHERE ID=$id");
        if ($result) {
            $out['event1'] = $result[0]['Event1'];
            $out['permission1'] = $result[0]['Permission1'];
            $out['event2'] = $result[0]['Event2'];
            $out['permission2'] = $result[0]['Permission2'];
            $out['event3'] = $result[0]['Event3'];
            $out['permission3'] = $result[0]['Permission3'];
            return ($out['event1'] != NULL) ? $out : false;
        }

        throw new Exception("User getEvents: couldn't find user is users table");
        return false;
    }

    /**
     * getID:  get User ID
     * @return string  User ID / false if ID yet initialized
     */
    public function getID() {
        if (isset($this->id)) {
            return $this->id;
        }
        return false;
    }

}

?>