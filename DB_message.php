<?php

require_once ("DB_tables.php");
require_once ("smsGateway.php");
require_once ("common.php");

class Messages extends Table {

    /**
     * create: create new messages table named messages[$eventID] in the database 
     * @return bool true if table created / false if table not created
     * @throws Exception "Messages create: Error adding Messages$eventID to Database"
     */
    public function create() {

        $eventID = $this->eventID;

        $result = DB::query("CREATE TABLE IF NOT EXISTS Messages$eventID ( 
                ID INT(2) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                MessageType VARCHAR(10) NOT NULL,
                Message TEXT NOT NULL,
                Groups VARCHAR(100) DEFAULT NULL,
                SendDate DATE NOT NULL,
                SendTime TIME NOT NULL,
                Sent BOOLEAN DEFAULT FALSE
                ) DEFAULT CHARACTER SET utf8");

        if (!$result) {
            throw new Exception("Messages create: Error adding Messages$eventID to Database");
        }
        return true;
    }

    /**
     * destroy:  delete messages table from database ($messages[eventID])
     * @return bool true if messages[$eventID] table deleted / false if table wasn't
     * @throws Exception "Messages destroy: Error deleting Messages$eventID table from Database"
     */
    public function destroy() {

        $eventID = $this->eventID;
        $result = DB::query("DROP TABLE IF EXISTS messages$eventID");
        if (!$result) {
            throw new Exception("Messages destroy: Error deleting Messages$eventID table from Database");
        }
        return true;
    }

    /**
     * getMessages:  get messages table for specific event
     * @return result messages[$eventID] table or false if not messages[$eventID] exists
     */
    public function get() {
        if (!$this->isPermission('root,send')){
            throw new Exception("שגיאה: לא קיימת הרשאת גישה לצפייה בהודעות. אנא פנה למנהל האירוע ובקש הרשאה מתאימה.");
        }
        $eventID = $this->eventID;
        $result = DB::select("SELECT * FROM messages$eventID");
        return $result;
    }

    /**
     * update: update messages[$eventID] table in database
     * @param int $ID : the row ID of the message to be updated
     * @param $MessageType : type of message [SaveTheDate/Reminder/ThankYou]
     * @param $Message : message text to be sent
     * @param SendDate : date to send the message
     * @param $SendTime : time of day to send the message
     * @param $Groups: group that message should be sent to (default null)
     * @return int updated id (if updated)
     * @throws Exception "שגיאה: לא ניתן להכניס את ההודעה בפורמט הנוכחי, אנא נסח מחדש את ההודעה ונסה שנית."
     */
    public function update($ID, $MessageType, $Message, $SendDate, $SendTime, $Groups = NULL) {
        if (!$this->isPermission('root,send')){
            throw new Exception("שגיאה: לא קיימת הרשאת גישה לעריכת הודעות. אנא פנה למנהל האירוע ובקש הרשאה מתאימה.");
        }

        // Make strings query safe
        $id = DB::quote($ID);
        $messageType = DB::quote($MessageType);
        $message = DB::quote($Message);
        $groups = $this->appendGroups($Groups);
        $sendTime = DB::quote($SendTime);
        $sendDate = DB::quote($SendDate);

        $eventID = $this->eventID;

        DB::query("UPDATE Messages$eventID SET MessageType=$messageType, Message=$message, Groups=$groups, SendDate=$sendDate, SendTime=$sendTime  WHERE id = $id");

        if (DB::affectedRows() < 0) {
            throw new Exception("שגיאה: לא ניתן להכניס את ההודעה בפורמט הנוכחי, אנא נסח מחדש את ההודעה ונסה שנית.");
        }
        return $id;
    }

    /**
     * updateTable:  override UpdateTable method in Tables so it does not applay to messages
     * @throws Exception "שגיאה: לא ניתן להשתמש בפונקציה"."updateTable"."עבור הודעות. נא השתמש בפונקציה"."update."
     */
    public function updateTable($tableType, $colName, $id, $Value) {
        throw new Exception("שגיאה: לא ניתן להשתמש בפונקציה"."updateTable"."עבור הודעות. נא השתמש בפונקציה"."update.");
    }

    /**
     * add:  add row to messages[$eventID] table in database
     * @param $MessageType : type of message [SaveTheDate/Reminder/ThankYou]
     * @param $Message : message text to be sent
     * @param SendDate : date to send the message
     * @param $SendTime : time of day to send the message
     * @param $Groups: group that message should be sent to (default null)
     * @return int insert id if added
     * @throws Exception "Messages add: Error adding guest $message to Messages$eventID table"
     */
    public function add($MessageType, $Message, $SendDate, $SendTime, $Groups = NULL) {
        if (!$this->isPermission('root,send')){
            throw new Exception("שגיאה: לא קיימת הרשאת גישה להוספת הודעות. אנא פנה למנהל האירוע ובקש הרשאה מתאימה.");
        }

        // Make strings query safe
        $messageType = DB::quote($MessageType);
        $message = DB::quote($Message);
        $groups = $this->appendGroups($Groups);
        $sendTime = DB::quote($SendTime);
        $sendDate = DB::quote($SendDate);

        $eventID = $this->eventID;

        $result = DB::query("INSERT INTO messages$eventID (MessageType, Message, Groups, SendDate, SendTime) VALUES
                    ($messageType, $message, $groups, $sendDate, $sendTime)");
        if (!$result) {
            throw new Exception("Messages add: Error adding Message: $message to Messages$eventID table");
        }
        return DB::insertID();


        return true;
    }

    /**
     * delete:  delete row in table messages[$eventID] at database
     * @param string $id : table id column (table id not user id)
     * @return bool true if row deleted / false otherwise
     */
    public function delete($id) {
        if (!$this->isPermission('root,send')){
            throw new Exception("שגיאה: לא קיימת הרשאת גישה למחיקת הודעות. אנא פנה למנהל האירוע ובקש הרשאה מתאימה.");
        }
        $id = DB::quote($id);

        return Table::deleteFromTable('messages', $id);
    }
    
    
    /**
     * appendGroups:  append groups before inserting to groups column in messages table
     * @param array $Groups : array of Groups to be inserted as string in messages table (Groups column)
     * @return string groups separated by comma
     */
    private function appendGroups($Groups){
        // if empty group
        if (!isset($Groups[0][0])) return 'NULL';
        // only one group
        if(!isset($Groups[0][1])) return DB::quote($Groups);

        // todo: make sure this works with more than one group
        
        // prepare query (append while array[i] is not null)
        $i=1;
        // make query safe
        $group = DB::quote($Groups[0]);
        $string = "$group";
        
        while (isset($Groups[0][$i])){
            $group = DB::quote($Groups[$i]);
            $string = $string . ",$group";
            $i++;
        }
        
        return DB::quote($string);
    }

    /**
     * markAsSent:  mark message as sent (after sending to the sms-site)
     *              this function is for using with the united messages table in order to update each message[$eventID] table
     * @param int $messageID : the row id of the message to be marked as sent
     * @param int $eventID : the id of the event the message was sent from
     * @return bool true if message marked as sent / false otherwise
     * @throws Exception "Messages markAsSent: Error marking Message$messageID in Event$eventID as sent"
     */
    public function markAsSent($eventID ,$messageID){

        DB::query("UPDATE Messages$eventID SET Sent = 1 WHERE id = $messageID");
        if (DB::affectedRows() <= 0) {
            throw new Exception("שגיאה: לא ניתן לעדכן את שליחת ההודעה בשרתי האתר.");
        }
        return true;
    }

    /**
     * sendMessage: send costume message to smsGateway => to guests of a specific group
     * @param array $event : all relevant event details
     * @param table $guests : guests this message should be sent to
     * @param array $Message : details of message to be sent
     * @return bool true if message was sent and was marked as sent
     * @throws Exception "שגיאה: יש לבחור בזמן שליחת ההודעה שהינו גדול מהשעה הנוכחית."
     * @throws Exception "שגיאה: שליחת ההודעות נכשלה כשלון קולוסלי. הודעת שגיאה: $errorMsg"
     */
    public function sendMessages($event, $guests, $Message){
        if (!$this->isPermission('root,send')){
            throw new Exception("שגיאה: לא קיימת הרשאת גישה לשליחת הודעות. אנא פנה למנהל האירוע ובקש הרשאה מתאימה.");
        }

        // set time in UTC
        $time = GER2UTC($Message['SendDate'], $Message['SendTime']);
        $i=0;
        $data = array();

        //check that the event has guests:
        if (empty($guests)){
            throw new Exception("לאירוע אין אפילו מוזמין אחד");
        }
        // check that sending time > current time
        $currTime = time();
        $sendTime = strtotime($time);
        if ($currTime>$sendTime){
            throw new Exception("שגיאה: יש לבחור בזמן שליחת ההודעה שהינו גדול מהשעה הנוכחית."." sendTime=$sendTime, currTime=$currTime");
        }

        // connect to smsGateway
        $smsGateway = new SmsGateway($event['Email'], $event['Password']);

        // save deviceID
        $deviceID = $event['DeviceID'];

        //unset all irrelevant columns for function updateMessage
        unset($event['Email']);
        unset($event['Password']);
        unset($event['DeviceID']);

        // update message with details
        $message = $this->updateMessage($Message['Message'], $event);

        // send messages to smsGateway with time and date in UTC
        foreach ($guests as $guest){
            // set time with delay (avoid malfunctions)
            $delay = $i*5;                          // delay in seconds
            $timeToSend = $time." +$delay seconds";
            $expire = $timeToSend." +60 minutes";
            $i++;
            // prepare guest message
            $guestMessage = $this->updateGuestMessage($message, $guest);
            // prepare data
            $data[] = [
                'device' => $deviceID,
                'number' => $guest['Phone'],
                'message' => $guestMessage,
                'send_at' => strtotime($timeToSend),
                'expire_at' => strtotime($expire)
            ];
        }
        // send messages to smsGateway
        $response = $smsGateway->sendManyMessages($data);

        // check and return errors
        if(!empty($response['response']['result']['fails'])){
            $errorMsg = print_r($response['response']['result']['fails'][0]['errors'],true);
            throw new Exception("שגיאה: שליחת ההודעות נכשלה כשלון קולוסלי. הודעת שגיאה: $errorMsg". "data sent=".var_dump($data));
        }

        // mark message as sent
        return $this->markAsSent($event['ID'],$Message['ID']);
    }

    /**
     * updateMessage:  update message with the Name,Date,HebrewDate,Time,Address and venue of the event
     * @param string $Message : message that should be sent (containing relevant patterns)
     * @param array $event : all relevant event details
     * @return string updated message with all replacements
     */
    private function updateMessage($Message, $event){
        // update timestamp
        $time = date("G:i", strtotime($event['EventTime']));

        $patterns = array();
        $replacements = array();

        $patterns[0] = '/{אירוע}/';
        $patterns[1] = '/{תאריך}/';
        $patterns[2] = '/{תאריךע}/';
        $patterns[3] = '/{שעה}/';
        $patterns[4] = '/{כתובת}/';
        $patterns[5] = '/{מקום}/';

        $replacements[0] = $event['EventName'];
        $replacements[1] = $event['EventDate'];
        $replacements[2] = $event['HebrewDate'];
        $replacements[3] = $time;
        $replacements[4] = $event['Address'];
        $replacements[5] = $event['Venue'];

        return preg_replace($patterns,$replacements, $Message);

    }

    /**
     * updateGuestMessage:  update message with the Name,Surname and nickname of the guest
     * @param string $message : message that should be sent (containing relevant patterns)
     * @param array $guest : all relevant guest details
     * @return string updated message with all replacements
     */
    private function updateGuestMessage($message, $guest){
        $patterns = array();
        $replacements = array();

        $patterns[0] = '/{שם}/';
        $patterns[1] = '/{משפחה}/';
        $patterns[2] = '/{כינוי}/';

        $replacements[0] = $guest['Name'];
        $replacements[1] = $guest['Surname'];
        $replacements[2] = $guest['Nickname'];

        return preg_replace($patterns,$replacements, $message);
    }

}

?>