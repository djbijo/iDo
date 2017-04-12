<?php

require_once('DB_rsvp.php');
require_once('DB_message.php');
require_once('DB_rawData.php');

interface iEvent
{

    public function deleteEvent(User $user);

    public function switchEvent($EventID);

    public function getEventID();

    public function get();

    public function update($colName, $id, $value);

    public function getUsers();

}

class Event implements iEvent
{
    private $userID;
    private $eventID;
    public $rsvp;
    public $messages;
    public $rawData;

    /**
     * __construct: create new Event object. if Event not in Events table (eventName,eventDate!=Null) add Event to Events table (do not make any change to Users Table!)
     * @param string $UserID : the id of the user creating this event
     * @param int $EventID : Id of event (if event already created - use getEvents)
     * @param bool|int $addEvent : choose if this constructor was called from an addEvent function (Default 0)
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
     * @throws Exception "Event New : Couldn't construct new event"
     * @throws Exception "Event New : this function requires EventID and UserID if not called throw user->addEvent"
     */
    public function __construct($UserID, $EventID = NULL ,$addEvent = 0, $EventName = 'NULL', $EventDate = 'NULL', $EventTime = 'NULL', $Venue = 'NULL',
                                $Address = 'NULL', $EventEmail = 'NULL', $EventPhone = 'NULL', $Password = 'NULL', $Secret = 'NULL', $DeviceID = 'NULL')
    {
        if ((!$addEvent and $EventID===NULL) or !$UserID){
            throw new Exception("Event New : this function requires EventID and UserID if not called throw user->addEvent");
        }

        // user exists
        if (!$addEvent and $EventID !== NULL) {
            $this->eventID = $EventID;
            $this->userID =  DB::quote($UserID);
            $this->rsvp = new RSVP($this->eventID);
            $this->messages = new Messages($this->eventID);
            $this->rawData = new RawData($this->eventID);
        } // Event is not in Events table (new Event)
        elseif (($EventName and $EventDate) or $addEvent) {
            // initiate Database with user Database
            // Make strings query safe
            $this->userID =  DB::quote($UserID);
            $eventName = DB::quote($EventName);
            $eventDate = DB::quote($EventDate);
            $eventEmail = DB::quote($EventEmail);
            $hebrewDate = DB::quote($this->makeHebrewDate($EventDate));
            $venue = DB::quote($Venue);
            $address = DB::quote($Address);
            $eventTime = DB::quote($EventTime);
            $eventPhone = DB::quote($EventPhone);
            $password = DB::quote($Password);
            $secret = DB::quote($Secret);
            $deviceID = DB::quote($DeviceID);

            // Add new event to Events table
            $result = DB::query("INSERT INTO Events (EventName, EventDate, HebrewDate, EventTime, Venue, Address, RootID, Email, Phone, Password, Secret, DeviceID) VALUES
                                        ($eventName, $eventDate, $hebrewDate, $eventTime, $venue, $address, $UserID, $eventEmail, $eventPhone, $password, $secret, $deviceID)");
            if (!$result) {
                throw new Exception("Event New : Event not inserted to Events table");
            }
            // set eventID if not already set.
            $this->eventID = DB::insertID();

            // make new RSVP, Messages and RawData tables
            $this->rsvp = new RSVP($this->eventID);
            $this->messages = new Messages($this->eventID);
            $this->rawData = new RawData($this->eventID);
        } else {
            throw new Exception("Event New : Couldn't construct new event");
        }
        // Event is in Events table
        return;
    }

    /**
     * deleteEvent: delete relevant event from events table, delete also RSVP table, Messages table and RawData table
     * @param User $user : user object related to this event
     * @return bool false = event not erased or no 'root' permission for user, true = event erased successfully
     * @throws Exception "Event deleteEvent: couldn't delete event tables"
     * @throws Exception "Event deleteEvent: couldn't delete event$eventID from Users table"
     * @throws Exception "Event deleteEvent: only root user can delete event$eventID"
     */
    public function deleteEvent(User $user)
    {
        // Check user permission for event
        $permission = $this->getPermission();
        $eventID = DB::quote($this->eventID);

        if ($permission === 'root') {
            for ($i = 1; $i <= 3; $i++) {
                // delete event from Events table
                $sql = DB::query("DELETE FROM Events WHERE ID=$eventID");
                // delete RSVP[eventID] table
                $sqlRSVP = $this->rsvp->destruct();
                // delete Messages[eventID] table
                $sqlMessages = $this->messages->destruct();
                // delete RawData[eventID] table
                $sqlRawData = $this->rawData->destruct();
                if (!$sql or !$sqlRSVP or !$sqlMessages or !$sqlRawData) {
                    throw new Exception("Event deleteEvent: couldn't delete event tables");
                }
                DB::query("UPDATE Users SET Event$i=NULL, Permission$i=NULL WHERE Event$i=$eventID");
                //if event updated
                if (DB::affectedRows() < 0) {
                    throw new Exception("Event deleteEvent: couldn't delete event$eventID from Users table");
                }
            }
            // event deleted for user, shift all events left
            $user->shiftEvents();
            return true;
        }
        throw new Exception("Event deleteEvent: only root user can delete event$eventID");
    }

    /**
     * switchEvent: change the event id
     * @param int $EventID : the EventID that we would like to change to
     * @return bool true if eventID changed / false otherwise
     */
    public function switchEvent($EventID) {
        $this->eventID = $EventID;
        return true;
    }

    /**
     * getEventID:  get Event ID
     * @return int  Event ID / false if ID yet initialized
     */
    public function getEventID() {
        if (isset($this->eventID)) {
            return $this->eventID;
        }
        return false;
    }

    /**
     * get:  get Event row for specific event
     * @return array of event[$eventID]
     * @throws Exception "Event get: couldn't get row for event$eventID from Events table "
     */
    public function get() {
        $eventID = $this->eventID;
        $result = DB::select("SELECT * FROM Events WHERE ID=$eventID ");

        if (empty($result[0])) {
            throw new Exception("Event get: couldn't get row for event$eventID from Events table ");
        }

        return $result[0];
    }

    /**
     * update:  update event in Events table in database
     * @param string $colName : column which value should be updated in
     * @param string $id : id of row to be updated
     * @param $Value : value to be inserted to the colName column
     * @return bool true if table updated / false if table not updated
     * @throws Exception "Table $tableType updateTable: couldn't update table ".$tableType.$eventID." with $colName = $value for row $id"
     */
    public function update($colName, $id, $Value){
        // handel data
        $value = DB::quote($Value);
        $eventID = $this->eventID;

        $permission = $this->getPermission();

        if($permission!=='root'){
            throw new Exception("שגיאה: רק משתמש שהינו בעל הרשאת מנהל יכול לערוך את האירוע.");
        }

        //if date change - change also hebrew date
        if ($colName==='EventDate'){
            $hebrewDate = DB::quote($this->makeHebrewDate($Value));

            DB::query("UPDATE Events SET $colName = $value, HebrewDate = $hebrewDate WHERE id = $eventID");

            if (DB::affectedRows() < 0) {
                throw new Exception("Event update: couldn't update Events table with EventDate=$value and HebrewDate=$hebrewDate for Event$eventID");
            }
        }

        // generate mysql command
        DB::query("UPDATE Events SET $colName = $value WHERE id = $eventID");

        if (DB::affectedRows() < 0) {
            throw new Exception("Event update: couldn't update Events table with $colName = $value for Event$eventID");
        }
        return true;
    }

    /**
     * getUsers:  get all users connected to this event
     * @return table with all users the have the event under Event1,Event2 or Event3
     * @throws Exception "Event getUsers: couldn't get users for event$eventID from Users table"
     */
    public function getUsers(){
        $eventID = $this->eventID;
        $result = DB::select("SELECT * FROM Users WHERE Event1=$eventID OR Event2=$eventID OR Event3=$eventID");

        if (empty($result[0])) {
            throw new Exception("Event getUsers: couldn't get users for event$eventID from Users table");
        }
        return $result;
    }

    /**
     * getPermission: get the user permission for this event;
     * @return permission of this event for a specific user
     * @throws Exception "שגיאה: האירוע המבוקש לא נמצא במאגרי האתר."
     */
    public function getPermission() {
        $eventID = $this->eventID;
        $userID = $this->userID;
        $result = DB::select("SELECT * FROM Users WHERE ID=$userID AND Event1=$eventID");

        if (empty($result[0])) {
            throw new Exception("שגיאה: האירוע המבוקש לא נמצא במאגרי האתר.");
        }

        return $result[0]['permission1'];
    }

    /* ---------- Private Functions ---------- */

    /**
     * makeHebrewDate:  change date to Heberw date
     * @param Date $Date : the date to be converted to hebrew date
     * @return String hebrew date
     * @throws Exception "Event makeHebrewDate: date template is YEAR-MONTH-DAY (XXXX-XX-XX), $Date doesn't comply with this format"
     */
    public static function makeHebrewDate($Date){
        // break date into an array
        $date = explode('-',$Date);

        // if empty group
        if (!$date[0] or !$date[1] or !$date[2]) throw new Exception("Event makeHebrewDate: date template is YEAR-MONTH-DAY (XXXX-XX-XX), $Date doesn't comply with this format");
        $response = file_get_contents("http://www.hebcal.com/converter/?cfg=json&gy=$date[0]&gm=$date[1]&gd=$date[2]&g2h=1");
//        echo $response;
        $response = json_decode($response, true);
        try {
            $hebDate = $response['hebrew'];
        } catch (Exception $e) {
            var_dump($response);
            echo $e->getMessage();
        }
        if (!$hebDate) throw new Exception("couldn't get hebrew date");

        return $hebDate;
    }

}

?>