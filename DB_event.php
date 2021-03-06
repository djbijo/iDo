<?php

require_once('DB_rsvp.php');
require_once('DB_message.php');
require_once('DB_rawData.php');
require_once('DB_groups.php');

interface iEvent
{

    public function deleteEvent(User &$user);

    public function switchEvent($EventID);

    public function getEventID();

    public function get();

    public function update($colName, $id, $value);

    public function getUsers();

    public function sendMessages($MessageID);

    public function getMessages();
}

class Event implements iEvent
{
    private $userID;
    private $eventID;
    public $rsvp;
    public $messages;
    public $rawData;
    public $groups;

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
            // check for valid eventID
            $sql = DB::select("SELECT * FROM Events WHERE ID=$EventID");
            if (!$sql){
                throw new Exception("שגיאה: האירוע לא מופיע במאגרי האתר.");
            }
            $this->eventID = $EventID;
            $this->userID = $UserID;
            $permission = $this->getPermission();
            $this->rsvp = new RSVP($this->eventID, $permission);
            $this->messages = new Messages($this->eventID, $permission);
            $this->rawData = new RawData($this->eventID, $permission);
            $this->groups = new Groups($this->eventID, $permission);

        } // Event is not in Events table (new Event)
        elseif (($EventName and $EventDate) or $addEvent) {
            // initiate Database with user Database
            // Make strings query safe
            $this->userID = $UserID;
            $eventName = DB::quote($EventName);
            $eventDate = DB::quote($EventDate);
            $eventEmail = DB::quote($EventEmail);
//            $hebrewDate = DB::quote($this->makeHebrewDate($EventDate));
            $hebrewDate = DB::quote('תאריך');             //TODO: this is for running OOO
            $venue = DB::quote($Venue);
            $address = DB::quote($Address);
            $eventTime = DB::quote($EventTime);
            $eventPhone = DB::quote($EventPhone);
            $password = DB::quote($Password);
            $secret = DB::quote(randStrGen(10)); //fixme [gil] - the secret will be displayed to user but we decide what it is to ensure uniqueness
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
            $this->rsvp = new RSVP($this->eventID, 'root');
            $this->messages = new Messages($this->eventID, 'root');
            $this->rawData = new RawData($this->eventID, 'root');
            $this->groups = new Groups($this->eventID, 'root');

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
    public function deleteEvent(User &$user, $make = 0)
    {
        // Check user permission for event
        $eventID = DB::quote($this->eventID);

        if ($make or $this->getPermission('root')) {
                // delete event from Events table
                $sql = DB::query("DELETE FROM Events WHERE ID=$eventID");
                // delete RSVP[eventID] table
                $sqlRSVP = $this->rsvp->destruct();
                // delete Messages[eventID] table
                $sqlMessages = $this->messages->destruct();
                // delete RawData[eventID] table
                $sqlRawData = $this->rawData->destruct();
                // delete Groups[eventID] table
                $sqlGroups = $this->groups->destruct();

                if (!$sql or !$sqlRSVP or !$sqlMessages or !$sqlRawData or !$sqlGroups) {
                    throw new Exception("Event deleteEvent: couldn't delete event tables");
                }
            for ($i = 1; $i <= 3; $i++) {
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
        throw new Exception("שגיאה: רק משתמש בעל השראת מנהל יכול למחוק את האירוע, אנא פנה למנהל האירוע ובקש הרשאה מתאימה.");
    }


    /**
     * switchEvent: change the event id
     * @param int $EventID : the EventID that we would like to change to
     * @return bool true if eventID changed / false otherwise
     */
    public function switchEvent($EventID) {
        $this->eventID = $EventID;
        $this->rsvp->switchEvent($EventID);
        $this->rawData->switchEvent($EventID);
        $this->messages->switchEvent($EventID);
        $this->groups->switchEvent($EventID);
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
    public function get()
    {
        $eventID = $this->eventID;

        $result = DB::select("SELECT * FROM Events WHERE ID=$eventID");

        if (empty($result[0])) {
            throw new Exception("Event get: couldn't get row for event$eventID from Events table ");
        }

        unset($result[0]['RootID']);

        if (!$this->getPermission('root')){
            unset($result[0]['Email']);
            unset($result[0]['Phone']);
            unset($result[0]['Password']);
            unset($result[0]['Secret']);
            unset($result[0]['DeviceID']);
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

        // make sure secret can't be changed
        if($colName=='Secret'){
            throw new Exception("שגיאה: לא ניתן לערוך שדה זה.");
        }

        if(!$this->getPermission('root')){
            throw new Exception("שגיאה: רק משתמש בעל השראת מנהל יכול לעדכן את האירוע, אנא פנה למנהל האירוע ובקש הרשאה מתאימה.");
        }

        // if date update - update also hebrew date
        if ($colName==='EventDate'){
            $hebrewDate = DB::quote($this->makeHebrewDate($Value));

            DB::query("UPDATE Events SET $colName = $value, HebrewDate = $hebrewDate WHERE id = $eventID");

            if (DB::affectedRows() < 0) {
                throw new Exception("Event update: couldn't update Events table with EventDate=$value and HebrewDate=$hebrewDate for Event$eventID");
            }
            return true;
        }

        // update events table
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
     * sendMessages: send messages to relevant guests
     * @return bool true if messages sent
     * @throws Exception "שגיאה: בכדי לשלוח הודעה יש צורך בהתחברות לאתר smsGateway והכנסת הפרטים תחת 'ניהול אירוע'"
     */
    public function sendMessages($MessageID){

        // check for 'root' or 'send' permissions
        if(!$this->getPermission('root,send')){
                throw new Exception("שגיאה: רק משתמש בעל השראת מנהל או הרשאת שליחה יכול לשלוח הודעות, אנא פנה למנהל האירוע ובקש הרשאה מתאימה.");
        }

        $messageID = $MessageID;//DB::quote($MessageID); //FIXME: this added some slashes
        $eventID = $this->eventID;

        $message = DB::select("SELECT * FROM Messages$eventID WHERE ID=$messageID");
        // check MessageID exists
        if (!$message){
            throw new Exception("שגיאה: הודעה $messageID זו איננה במאגרי האתר. אנא בחר הודעה המתאימה לאירוע זה.");
        }
        // get guests from rsvp table - TODO: inorder to send to all contacts, $message[0]['Groups']='all'
        $guests = $this->rsvp->getByGroups('rsvp',$message[0]['Groups'],1);
        $event = $this->get();

        // check email
        if(!$event['Email'] or !$event['Password']){
            throw new Exception("שגיאה: בכדי לשלוח הודעה יש צורך בהתחברות לאתר smsGateway והכנסת הפרטים תחת 'ניהול אירוע'");
        }

        // update device if not updated
        if(!isset($event['DeviceID'])) {
            $smsGateway = new SmsGateway($event['Email'], $event['Password']);
            $result = $smsGateway->getDevices(1);
            $result = $result['response'];
            if (isset($result['success'])) {
                if ($result['result']['total'] > 0) {
                    $device = $result['result']['data'][0]['id'];
                    $response = $this->update('DeviceID',$this->eventID, $device);
                    if (!$response){
                        throw new Exception("שגיאה: בכדי לשלוח הודעה יש צורך בהתחברות לאתר smsGateway והכנסת פרטי המכשיר תחת 'ניהול אירוע'");
                    }
                }

            } else {
                throw new Exception("שגיאה: בכדי לשלוח הודעה יש צורך בהתחברות לאתר smsGateway והכנסת פרטי המכשיר תחת 'ניהול אירוע'");
            }
        }

        //unset all irrelevant columns
        unset($event['Created']);
        unset($event['Secret']);
        unset($message[0]['Groups']);

        return $this->messages->sendMessages($event, $guests, $message[0]);
    }

    /**
     * getMessages: get messages as batch from smsGateway servers
     * @return bool true if messages received
     * @throws Exception "שגיאה: בכדי לקבל הודעה יש צורך בהתחברות לאתר smsGateway והכנסת הפרטים תחת 'ניהול אירוע'"
     * @throws Exception "לא התקבלו הודעות חדשות אשר מקושרות לאירוע זה."
     */
    public function getMessages(){
        //check permissions
        if(!$this->getPermission('root,edit')){
            throw new Exception("שגיאה: רק משתמש בעל השראת מנהל או הרשאת עריכה יכול לשלוח הודעות, אנא פנה למנהל האירוע ובקש הרשאה מתאימה.");
        }

        // get messages and insert to rawData[eventID] table
        $event = $this->get();

        // check email
        if(!$event['Email']){
            throw new Exception("שגיאה: בכדי לקבל הודעה יש צורך בהתחברות לאתר smsGateway והכנסת הפרטים תחת 'ניהול אירוע'");
        }

        // get new raw data in the format of Table : array[i]['Phone'/'Message'/'Recived'/'RSVP'/'Ride']
        $rawData = $this->rawData->getMessages($event['Email'], $event['Password'], $event['DeviceID']);

        if(!$rawData){
            throw new Exception("לא התקבלו הודעות חדשות אשר מקושרות לאירוע זה.");
        }

        $upsideRaw = array_reverse($rawData);

        // update RSVP according to RawData and get back name,surname, email and group
        $rsvpData = $this->rsvp->updateFromRaw($upsideRaw);

        if(!$rsvpData){
            throw new Exception("לא התקבלו הודעות חדשות אשר מקושרות לאירוע זה.");
        }

        // insert to rawData table
        return $this->rawData->insertBatch($rsvpData);
    }

    /**
     * messageReceived: get message (only received messages) and update Event tables accordingly
     * @param string $DeviceID : ['device_id'] from received message
     * @param string $Message : ['message'] from received message
     * @param string $Secret : ['secret']  from received message
     * @param string $Phone : ['phone'] from received message
     * @return bool true if messages received and event updated - return message is echoed (up to 5 times), false otherwise.
     */
    static function messageReceived($DeviceID, $Message, $Secret, $Phone){

        // check valid params
        if (!$Message or !$DeviceID or !$Secret) return false;
        // make strings query safe
        $deviceID = DB::quote($DeviceID);
        $message = DB::quote($Message);
        $secret = DB::quote($Secret);
        $phone = validatePhone($Phone);
        if (!$phone) return false;

        // get eventID
        $event = DB::select("SELECT * FROM Events WHERE DeviceID=$deviceID AND Secret=$secret");
        // check stored deviceID and Secret
        if(!$event) return false;

        $eventID = $event[0]['ID'];
        $rootID = $event[0]['RootID'];

        $event = new Event($rootID, $eventID);
        // update RSVP according to Message and get back name,surname, email, group, RSVP, uncertin, ride and complex
        $rsvpData = $event->rsvp->updateFromMessage($message, $phone);
        if(!$rsvpData) return false;

        // insert to rawData table
        $raw = $event->rawData->insertMessage($rsvpData, $message, $phone);

        if (!$raw) return false;

        // event updated, echo "thank you" message
         return $event->thankYou(($rsvpData[0]['Messages']));
    }

    /**
     * getPermission: get the user permission for this event;
     * @return string permission of this event for a specific user / string 'root' if user is root for this event
     * @throws Exception "שגיאה: האירוע המבוקש לא נמצא במאגרי האתר."
     */
    public function getPermission($relevant = NULL) {
        $eventID = $this->eventID;
        $userID = $this->userID;

        $result = DB::select("SELECT * FROM Users WHERE ID=$userID");

        if (!$result) {
            throw new Exception("שגיאה: האירוע המבוקש לא נמצא במאגרי האתר.");
        }

        // if given relevant function permissions, return true if any exist, false otherwise
        if ($relevant!=NULL){
            $array = explode(',',$relevant);
            $regexp = '';
            foreach ($array as $permission){
                $regexp = $regexp.'\b'."$permission".'\b|';
            }
            // set /$regexp/ and trim last | from regexp
            $regexp = "/".rtrim($regexp,"|")."/";

            return (filter_var($result[0]['Permission1'], FILTER_VALIDATE_REGEXP,
                array("options"=>array("regexp"=>$regexp)))) ? true : false;
        }
        // if no relevant permissions give, return permissions string
        return $result[0]['Permission1'];
    }

    /**
     * makeHebrewDate:  change date to Heberw date
     * @param Date $Date : the date to be converted to hebrew date
     * @return String hebrew date
     * @throws Exception "Event makeHebrewDate: date template is YEAR-MONTH-DAY (XXXX-XX-XX), $Date doesn't comply with this format"
     */
     static function makeHebrewDate($Date){
        // break date into an array
        $date = explode('-',$Date);

        // if empty group
        if (!$date[0] or !$date[1] or !$date[2]) throw new Exception("Event makeHebrewDate: date template is YEAR-MONTH-DAY (XXXX-XX-XX), $Date doesn't comply with this format");
        $response = file_get_contents("http://www.hebcal.com/converter/?cfg=json&gy=$date[0]&gm=$date[1]&gd=$date[2]&g2h=1");
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

    /**
     * thankYou: echo "thank you" message as ling as $message<=5
     * @param int $messages : number of messages received
     * @return bool true if message sent / false otherwise
     */
    private function thankYou($messages){
        // get hard coded "thank you" message and echo it
        if($messages<=5) echo file_get_contents( "thankYou.txt" );
        return true;
    }
}

?>