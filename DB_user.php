<?php

require_once("DB.php");
require_once("DB_event.php");

interface iUser
{


    public function addUser($ID, $Name, $Email, $Phone = 'NULL', $Event1 = 'NULL', $Permission1 = 'NULL', $Event2 = 'NULL', $Permission2 = 'NULL', $Event3 = 'NULL', $Permission3 = 'NULL');

    public function deleteUser();

    public function addUserPermissions($Email, $Permission);

    public function editUserPermissions($Email, $Permission);

    public function addUserPhone($Phone);

    public function addEvent($EventName, $EventDate, $EventTime = 'NULL', $Venue = 'NULL', $Address = 'NULL', $EventEmail = 'NULL', $EventPhone = 'NULL', $Password = 'NULL', $Secret = 'NULL', $DeviceID = 'NULL');

    public function selectEvent($EventID);

    public function shiftEvents();

    public function getEvents();

    public function getID();

}

class User implements iUser
{

    public $event;
    protected $id;

    /**
     * __construct: create new user object. if user not in Users table (name,email!=Null) add user to users list.
     * "event" is 1st event on list, until chosen otherwise.
     * @param string $ID : user id
     * @param string $Name : user name (DEFAULT=NULL)
     * @param string $Email : user login email (DEFAULT=NULL)
     * @param string $Phone : user cell phone number (DEFAULT=NULL)
     * @return User object
     * @throws Exception "User New: addUser function error"
     */
    public function __construct($ID, $Name = 'NULL', $Email = 'NULL', $Phone = 'NULL')
    {

        // user is in users table (registered to iDO)
        if ($this->checkUserID($ID)) {

            if (!isset($this->id)) $this->id = DB::quote($ID);
            //shift user events left
            $this->shiftEvents();
            return;
        }

        // check if future user (email in system, id=-1)
        if ($this->checkUserEmail($Email)){
            $id = DB::quote($ID);
            $name = DB::quote($Name);
            $email = validateEmail($Email);
            $phone = validatePhone($Phone);
            DB::query("UPDATE Users SET ID=$id, Name=$name, Phone=$phone WHERE Email=$email AND ID=-1");
            if (DB::affectedRows() > 0) return;
        }

        // user is not in users table (not registered to EZVITE)
        $result = $this->addUser($ID, $Name, $Email, $Phone);
        if (!$result) {
            throw new Exception("User New: addUser function error");
        }
        return;
    }

    /**
     * checkUserID:  Check if User ID exists in the database
     * @param string $ID : user ID
     * @return bool: false = user not in database / true = user in database
     */
    static function checkUserID($ID)
    {
        // Make strings query safe
        $id = DB::quote($ID);

        // Search for user ID in Users table
        if (!DB::select("SELECT * FROM Users WHERE ID=$id")) return false;
        return true;
    }

    /**
     * shiftEvents:  shift events left after one event has been deleted
     * @return int  Event ID / false if ID yet initialized
     * @throws "User shiftEvents: couldn't shift events left for user $id"
     */
    public function shiftEvents()
    {
        $result = $this->getEvents();
        if(!$result) $this->event = NULL;

        $id = $this->id;

        for ($i = 1; $i <= 2; $i++) {
            $j = $i + 1;

            if ($result["event$i"] == NULL and $result["event$j"] != NULL) {

                $eventJ = DB::quote($result["event$j"]);
                $permissionJ = DB::quote($result["permission$j"]);

                DB::query("UPDATE Users SET Event$i=$eventJ, Permission$i=$permissionJ WHERE ID=$id");
                $sql1 = DB::affectedRows();
                DB::query("UPDATE Users SET Event$j=NULL, Permission$j=NULL");
                $sql2 = DB::affectedRows();

                if ($sql1 < 0 or $sql2 < 0) {
                    throw new Exception("User shiftEvents: couldn't shift events left for user $id");
                }
            }
        }

        $events = $this->getEvents();
        if ($events['event1'] === NULL) return true;
        $this->event = new Event($this->id, $events['event1']);
        return true;
    }

    /**
     * getEvents: get all events and permissions for user
     * @return array[6]  array['event1'] = event1 id, array['permission1'] = event1 permission,
     *                   array['event2'] = event2 id, array['permission2'] = event2 permission,
     *                   array['event3'] = event3 id, array['permission3'] = event3 permission
     * @throws "User getEvents: couldn't find user is users table"
     */
    public function getEvents()
    {
        $id = $this->id;

        $result = DB::select("SELECT * FROM Users WHERE ID=$id");
        if ($result) {
            $out['event1'] = $result[0]['Event1'];
            $out['permission1'] = $result[0]['Permission1'];
            $out['event2'] = $result[0]['Event2'];
            $out['permission2'] = $result[0]['Permission2'];
            $out['event3'] = $result[0]['Event3'];
            $out['permission3'] = $result[0]['Permission3'];
            return ($out['event1'] != NULL or $out['event2'] != NULL or $out['event2'] != NULL ) ? $out : false;
        }
        throw new Exception("User getEvents: couldn't find user is users table");
    }

    /**
     * getEventNames: get all events names for the user
     * @return array[6]  array[i]['id'] = event i id, array[i]['name'] = event i name
     * @throws "שגיאה: לא ניתן להציג את האירועים עבור משתמש זה."
     */
    public function getEventNames()
    {
        $events = $this->getEvents();

        $i=1;
        while(isset($events["event$i"])){
            if($i=1) $query = $events["event$i"];
            $query = $query." OR ".$events["event$i"];
        }

        $result = DB::select("SELECT * FROM Events WHERE ID=$query");
        if ($result) {
            $i=0;
            foreach ($result as $event){
                $out[$i]['name'] = $event['EventName'];
                $out[$i]['id'] = $event['ID'];
                $i++;
            }
            return $out;
        }
        throw new Exception("שגיאה: לא ניתן להציג את האירועים עבור משתמש זה.");
    }

    /**
     * addUser: Add user to Users table
     * @param string $ID : user id
     * @param string $Name : user name
     * @param string $Email : user login email
     * @param string $Phone : user cell phone number (DEFAULT NULL)
     * @param int|string $Event1 : event id for 1st event (DEFAULT NULL)
     * @param string $Permission1 : user permission type for event1(root/edit/review) (DEFAULT NULL)
     * @param int|string $Event2 : event id for 2nd event (DEFAULT NULL)
     * @param string $Permission2 : user permission type for event2(root/edit/review) (DEFAULT NULL)
     * @param int|string $Event3 : event id for 3rd event (DEFAULT NULL)
     * @param string $Permission3 : user permission type for event3(root/edit/review) (DEFAULT NULL)
     * @return bool false = user already in Users table / true = user added to Users table (DEFAULT NULL)
     * @throws Exception "User addUser: User already registered to iDO"
     * @throws Exception "User addUser: the Email address: '$Email' is already registered to iDO"
     * @throws Exception "Name field in null"
     * @throws Exception "Email field in null"
     * @throws Exception "User addUser: couldn't add user $this->id to users table"
     */
    public function addUser($ID, $Name, $Email, $Phone = 'NULL', $Event1 = 'NULL', $Permission1 = 'NULL', $Event2 = 'NULL', $Permission2 = 'NULL', $Event3 = 'NULL', $Permission3 = 'NULL')
    {
        //check if ID/Phone/Email already in Users table
        if ($this->checkUserID($ID)) {
            throw new Exception("User addUser: User already registered to iDO");
        }
        if ($this->checkUserEmail($Email)) {
            throw new Exception("User addUser: the Email address: '$Email' is already registered to iDO");
        }
        if (!$Name) {
            throw new Exception("Name field in null");
        }
        if (!$Email) {
            throw new Exception("Email field in null");
        }
        // Make strings query safe
        $email = validateEmail($Email);
        $phone = validatePhone($Phone);
        $name = DB::quote($Name);
        $permission1 = DB::quote($Permission1);
        $permission2 = DB::quote($Permission2);
        $permission3 = DB::quote($Permission3);

        // save user ID
        if (!isset($this->id)) $this->id = DB::quote($ID);
        $id = $this->id;

        //insert user to Users table
        $result = DB::query("INSERT INTO Users (ID, Name, Email, Phone, Event1, permission1, Event2, permission2, Event3, permission3) VALUES
			($id, $name, $email, $phone, $Event1, $permission1, $Event2, $permission2, $Event3, $permission3)");
        if (!$result) {
            throw new Exception("User addUser: couldn't add user $this->id to users table");
        }
        return true;
    }

    /**
     * addFutureUser: Add user to Users table
     * @param string $ID : user id
     * @param string $Name : user name
     * @param string $Email : user login email
     * @param string $Phone : user cell phone number (DEFAULT NULL)
     * @param int|string $Event1 : event id for 1st event (DEFAULT NULL)
     * @param string $Permission1 : user permission type for event1(root/edit/review) (DEFAULT NULL)
     * @param int|string $Event2 : event id for 2nd event (DEFAULT NULL)
     * @param string $Permission2 : user permission type for event2(root/edit/review) (DEFAULT NULL)
     * @param int|string $Event3 : event id for 3rd event (DEFAULT NULL)
     * @param string $Permission3 : user permission type for event3(root/edit/review) (DEFAULT NULL)
     * @return bool false = user already in Users table / true = user added to Users table (DEFAULT NULL)
     * @throws Exception "User addUser: User already registered to iDO"
     * @throws Exception "User addUser: the Email address: '$Email' is already registered to iDO"
     * @throws Exception "Name field in null"
     * @throws Exception "Email field in null"
     * @throws Exception "User addUser: couldn't add user $this->id to users table"
     */
    public function addFutureUser($Email, $Permission)
    {
        // check valid email
        if (!$Email) {
            throw new Exception("שגיאה: יש לתת כתובת email תקינה לשם הוספה לאתר.");
        }
        // check for root permissions
        if (!$this->event->getPermission('root')){
            throw new Exception("שגיאה: רק משתמש בעל השראות מנהל יכול להוסיף משתמשים לאירוע. אנא פנה למנהל האירוע ובקש הרשאה מתאימה.");
        }

        // Make strings query safe
        $email = validateEmail($Email);
        $permission = DB::quote($Permission);
        $eventID = $this->event->getEventID();

        //insert user to Users table as future user
        $result = DB::query("INSERT INTO Users (ID, Name, Email, Event1, permission1) VALUES
			('-1', 'John Doe', $email, $eventID, $permission)");
        if (!$result) {
            throw new Exception("שגיאה: לא ניתן להוסיף את המשתמש".$email );
        }
        return true;
    }

    /**
     * checkUserEmail:  Check if User email exists in the database
     * @param string $Email :  user login email
     * @return bool : false = email not in database / true = email in database
     */
    private function checkUserEmail($Email)
    {
        if (NULL == $Email or 'NULL' == $Email) return false;
        // Make strings query safe
        $email = validateEmail($Email);

        // Search for user Email or phone in Users table
        if (!DB::select("SELECT * FROM Users WHERE Email=$email")) {
            return false;
        }
        return true;
    }

    /**
     * deleteUser:     delete user row from Users table
     * @return bool false = user not in Users table / true = user deleted from Users table
     * @throws Exception "User deleteUser: user not deleted from users table"
     */
    public function deleteUser()
    {
        $id = $this->id;
        $events = $this->getEvents();

        // check and delete event if no more users are connected to this event
        for ($i = 1; $i <= 3; $i++) {
            $eventID = $events["event$i"];
            if ('NULL'==$eventID or NULL==$eventID) break;

            $eventUsers = DB::select("SELECT * FROM Users WHERE Event1=$eventID OR Event2=$eventID OR Event3=$eventID");
            // if only one user is connected to the event, delete this event

            if (empty($eventUsers[1])){
                $this->event->switchEvent($eventID);
                $this->event->deleteEvent($this,1);
            }
        }

        // Delete user from Users table
        if (!DB::query("DELETE FROM Users WHERE ID=$id")) {
            throw new Exception("User deleteUser: user not deleted from users table");
        }
        return true;
    }

    /**
     * addUserPermissions: add a permissions granted for the user. one can add permission only to the event chosen
     * @param string $Email : user email
     * @param string $Permission : change to this permission
     * @return bool false = user not in Users table or already has 3 events / true = user permission added
     * @throws Exception "User addUserPermissions: the user with Email address:'$Email' is not registered to iDO. please register the user before granting permissions"
     * @throws Exception "User addUserPermissions: Only the user which created the event can change permissions to it"
     * @throws Exception "User addUserPermissions:User $email has too many events registered (max 3 events per user at a time)"
     */
    public function addUserPermissions($Email, $Permission, $overRide = 0)
    {
        // check for root permissions
        if (!$this->event->getPermission('root') and $overRide==0){
            throw new Exception("שגיאה: רק משתמש בעל השראות מנהל יכול להוסיף משתמשים לאירוע. אנא פנה למנהל האירוע ובקש הרשאה מתאימה.");
        }

        //check that the user is already registered to iDO services
        if (!$this->checkUserEmail($Email)) {
            // add user as future user
            return $this->addFutureUser($Email, $Permission);
        }

        // Make strings query safe
        $email = validateEmail($Email);
        $permission = DB::quote($Permission);

        //check if user is root for this event
        $eventID = $this->event->getEventID();

        $result = DB::select("SELECT * FROM Events WHERE ID=$eventID");
        $rootID = DB::quote($result[0]['RootID']);

        if ($rootID != $this->id) {
            throw new Exception("User addUserPermissions: Only the user which created the event can change permissions to it");
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
    }


    /**
     * editUserPermissions: edit the permissions granted for the user. one can edit permission only to the event chosen
     * @param string $Email : user email
     * @param string $Permission : change to this permission
     * @return bool false = user not in Users table or user not root fur this event / true = user permission changed or no need to modify
     * @throws Exception "User editUserPermissions: the user with Email address:'$Email' is not registered to iDO. please register the user before granting permissions"
     * @throws Exception "User editUserPermissions: Only the user which created the event can change permissions to it"
     * @throws Exception "User editUserPermissions: user $email has is not registered to this event, please add user to event with relevant permission"
     */
    public function editUserPermissions($Email, $Permission)
    {

        // check for root permissions
        if (!$this->event->getPermission('root')){
            throw new Exception("שגיאה: רק משתמש בעל השראת מנהל יכול לערוך הרשאות לאירוע, אנא פנה למנהל האירוע ובקש הרשאה מתאימה.");
        }

        //check that the user is already registered to iDO services
        if (!$this->checkUserEmail($Email)) {
            throw new Exception("User editUserPermissions: the user with Email address:'$Email' is not registered to iDO. please register the user before granting permissions");
        }

        // Make strings query safe
        $email = validateEmail($Email);
        $permission = DB::quote($Permission);

        //check if user is root for this event
        $eventID = $this->event->getEventID();
        $result = DB::select("SELECT * FROM Events WHERE ID=$eventID");
        $rootID = DB::quote($result[0]['RootID']);

        if ($rootID != $this->id) {
            throw new Exception("User editUserPermissions: Only the user which created the event can change permissions to it");
        }

        // Update relevant user in user table
        for ($i = 1; $i <= 3; $i++) {
            DB::query("UPDATE Users SET Permission$i=$permission WHERE Email=$email AND Event$i=$eventID");

            //if event updated
            if (DB::affectedRows() > 0) return true;
        }

        throw new Exception("User editUserPermissions: user $email has is not registered to this event, please add user to event with relevant permission");
    }

    /**
     * addUserPhone: add phone number for user
     * @param string $Phone : user phone number to be added
     * @return bool true if phone number added to user
     * @throws Exception "User addUserPhone: the Phone number $phone is already registered to iDO"
     * @throws Exception "User addUserPhone: the Phone number $phone could not be added to iDO"
     */
    public function addUserPhone($Phone)
    {
        // Make strings query safe
        $phone = validatePhone($Phone);
        $id = $this->id;

        //check if phone number already in iDO database
        if ($this->checkUserPhone($Phone)) {
            throw new Exception("User addUserPhone: the Phone number $phone is already registered to iDO");
        }

        // update user with phone number
        DB::query("UPDATE Users SET Phone=$phone WHERE ID=$id");

        if (DB::affectedRows() < 0) {
            throw new Exception("User addUserPhone: the Phone number $phone could not be added to iDO");
        }
        return true;
    }

    /**
     * checkUserPhone:  Check if User phone exists in the database
     * @param string $Phone :  user cell phone
     * @return bool : false = phone not in database / true = phone already in database
     */
    private function checkUserPhone($Phone)
    {
        // Make strings query safe
        $phone = validatePhone($Phone);

        // Search for user phone in Users table
        if (!DB::select("SELECT * FROM Users WHERE Phone=$phone")) {
            return false;
        }
        return true;
    }

    /**
     * __construct: create new Event object. if Event not in Events table (eventName,eventDate!=Null) add Event to Events table (do not make any change to Users Table!)
     * @param string $EventName : name of event owner/owners or name of event (Default 'NULL')
     * @param date|string $EventDate : date of event (Default 'NULL')
     * @param string $EventTime : time the event should start (Default 'NULL')
     * @param string $Venue : place of the event (Default 'NULL')
     * @param string $Address : event address (Default 'NULL')
     * @param string $EventEmail : Email to use for sending and receiving Emails (Default 'NULL')
     * @param string $EventPhone : Phone to use for sending and receiving messages (Default 'NULL')
     * @param string $Password : pasword from sms site (Default 'NULL')
     * @param string $Secret : secret from sms site (Default 'NULL')
     * @param string $DeviceID : device id from sms site (Default 'NULL')
     * @return Event object
     * @throws Exception"User addEvent: couldn't get user $id from users table"
     */
    public function addEvent($EventName, $EventDate, $EventTime = 'NULL', $Venue = 'NULL', $Address = 'NULL', $EventEmail = 'NULL', $EventPhone = 'NULL', $Password = 'NULL', $Secret = 'NULL', $DeviceID = 'NULL')
    {
        $id = $this->id;
        $result = DB::select("SELECT * FROM USERS WHERE ID=$id");
        if (!$result) {
            throw new Exception("User addEvent: couldn't get user $id from users table");
        }
        if($result[0]['Event3']!=NULL){
            throw new Exception("שגיאה: לא ניתן ליצור אירוע, ישנה הגבלה של עד 3 אירועים למשתמש בכל זמן נתון.");
        }

        $this->event = new Event($id, NULL,1, $EventName, $EventDate, $EventTime, $Venue, $Address, $EventEmail, $EventPhone, $Password, $Secret, $DeviceID);

        $this->addUserPermissions($result[0]['Email'], 'root',1);
        $this->selectEvent($this->event->getEventID());
        return $this->event;
    }

    /**
     * selectEvent: select Event out of user possible events (call getEvents() function before of this function)
     * @param int $EventID : the event to change to, according to user choice
     * @return void
     * @throws Exception "User selectEvent: error inserting event as event1"
     * @throws Exception "User selectEvent: couldn't switch event$EventID and event$event1 in users records"
     */
    public function selectEvent($EventID)
    {
        $events = $this->getEvents();

        if($events['event1']!=$EventID and $events['event2']!=$EventID and $events['event3']!=$EventID ){
            throw new Exception("שגיאה: אין למשתמש אפשרות גישה לאירוע");
        }

        $eventID = DB::quote($EventID);
        $id = $this->id;
        $events = $this->getEvents();
        $permission = NULL;

        // delete event from users records - w/o deleting the events tables
        for ($i = 1; $i <= 3; $i++) {
            DB::query("UPDATE Users SET Event$i=NULL , Permission$i=NULL WHERE Event$i=$eventID AND ID=$id");
            if (DB::affectedRows() >= 1) {
                $permission = DB::quote($events["permission$i"]);
                $j = $i;
            }
        }
        if ($permission === NULL) throw new Exception("User selectEvent: error inserting event as event1");
        // clear 1st event in user record

        // switch event1 with relevant event
        $event1 = DB::quote($events['event1']);
        $permission1 = DB::quote($events['permission1']);

        DB::query("UPDATE Users SET Event1=$eventID, Permission1=$permission, Event$j=$event1, Permission$j=$permission1 WHERE ID=$id");
        if (DB::affectedRows() < 0) {
            throw new Exception("User selectEvent: couldn't switch event$EventID and event$event1 in users records");
        }
        //FIXME: gil added, need to make sure it's sanitized
        $this->event = new Event($id, $EventID);
    }

    /**
     * getID:  get User ID
     * @return string  User ID / false if ID yet initialized
     */
    public function getID()
    {
        if (isset($this->id)) {
            return $this->id;
        }
        return false;
    }
}

?>