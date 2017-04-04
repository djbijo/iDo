<?php

require_once('DB_user.php');
require_once('DB_rsvp.php');

interface iEvent {

    public function deleteEvent(User $user);

    public function getEventID();

    public function getDB();
}

class Event implements iEvent {

    protected static $db;
    public $eventID;
    public $rsvp;
    public $messages;
    public $rawData;

    /**
     * __construct: create new Event object. if Event not in Events table (eventName,eventDate!=Null) add Event to Events table (do not make any change to Users Table!)
     * @param User $user : user element connected to this instance of event
     * @param string $EventName : name of event owner/owners or name of event
     * @param date $EventDate : date of event
     * @param string $EventPhone : Phone to use for sending and receiving messages 
     * @param string $EventEmail : Email to use for sending and receiving Emails
     * @return object Event
     */
    public function __construct(User $user, $EventID = NULL, $EventName = NULL, $EventDate = NULL, $EventPhone = NULL, $EventEmail = NULL) {

        if (!isset(self::$db)) {
            self::$db = $user->getDB();
        }
        
        // user exists          ??? when using new user($ID)
        if ($events = $user->getEvents() and $EventID === NULL and $events['event1'] != NULL) {
            $this->eventID = $events['event1'];
        }

        if ($EventID != NULL and $EventID != -1 and ! isset($this->eventID)) {
            $this->eventID = $EventID;
        }

        // Event is not in Events table (new Event)
        elseif ($EventName != NULL and $EventDate != NULL and $EventEmail != NULL and $EventPhone != NULL) {
            // initiate Database with user Database
            // Make strings query safe
            $rootID = $user->getID();
            $eventName = self::$db->quote($EventName);
            $eventDate = self::$db->quote($EventDate);
            $eventEmail = self::$db->quote($EventEmail);
            $eventPhone = self::$db->quote($EventPhone);

            // Add new event to Events table
            $result = self::$db->query("INSERT INTO Events (EventName, EventDate, RootID, Email, Phone) VALUES
                                        ($eventName, $eventDate, $rootID, $eventEmail, $eventPhone)");
            if (!$result) {
                return false;
            }
            // set eventID if not already set.
            if (!isset($this->eventID)) {
                $this->eventID = self::$db->insertID();
            }
            
            // make new RSVP, Messages and RawData tables
            $this->rsvp = new RSVP($this);
            $this->messages = new Messages();
            $this->rawData = new RawData();

        } else {
            //throw exeption
        }
        // Event is in Events table
        return;
    }

    /**
     * deleteEvent: delete relevant event from events table, delete also RSVP table, Messages table and RawData table
     * @param User $user : user object related to this event
     * @return bool false = event not erased or no 'root' permission for user, true = event erased successfully
     */
    public function deleteEvent(User $user) {
        // Check user permission for event
        $result = $user->getEvents();
        for ($i = 1; $i <= 3; $i++) {
            $eventID = $this->eventID;
            if ($result["event$i"] === $this->eventID and $result["permission$i"] === 'root') {
                
                    // delete event from Events table
                    $sql = self::$db->query("DELETE FROM Events WHERE ID=$eventID");
                    // delete RSVP[eventID] table
                    $sqlRSVP = $this->rsvp->delete();
                    // delete Messages[eventID] table
                    $sqlMessages = $this->messages->delete();
                    // delete RawData[eventID] table
                    $sqlRawData = $this->rawData->delete();
                    if ($sqlRSVP and $sqlMessages and $sqlRawData) {
                    
                    
                    }
            }
            $sql = self::$db->query("UPDATE Users SET Event$i=NULL, Permission$i=NULL
                                            WHERE Event$i=$eventID");
            // event deleted for user, shift all events left                    ??????
        }
        return true;
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
     * getEventID:  get Event ID
     * @return int  Event ID / false if ID yet initialized
     */
    public function getEventID() {
        if (isset(self::$eventID)) {
            return self::$eventID;
        }
        return false;
    }

}

?>