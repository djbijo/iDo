<?php

require_once('DB_user.php');
require_once('DB_rsvp.php');
require_once('DB_message.php');
require_once('DB_rawData.php');

interface iEvent {

    public function deleteEvent(User $user);

    public function getEventID();
}

class Event implements iEvent {

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
    public function __construct(User $user, $EventName = NULL, $EventDate = NULL, $EventID = NULL, $HebrewDate = NULL, $EventTime = NULL, $Venue = NULL, $Address = NULL, $EventEmail = NULL, $EventPhone = NULL, $Password = NULL, $Secret = NULL, $DeviceID = NULL) {

        // user exists          ??? when using new user($ID)
        if ($events = $user->getEvents() and $EventID === NULL and $events['event1'] != NULL) {
            $this->eventID = $events['event1'];
            $this->rsvp = new RSVP($this->eventID);
            $this->messages = new Messages($this->eventID);
            $this->rawData = new RawData($this->eventID);
        }

        if ($EventID != NULL and ! isset($this->eventID)) {
            $this->eventID = $EventID;
            $this->rsvp = new RSVP($this->eventID);
            $this->messages = new Messages($this->eventID);
            $this->rawData = new RawData($this->eventID);
        }

        // Event is not in Events table (new Event) 
        elseif ($EventName and $EventDate) {
            // initiate Database with user Database
            // Make strings query safe
            /*
            $rootID = $user->getID();
            $eventName = DB::quote($EventName);
            $eventDate = DB::quote($EventDate);
            $eventEmail = DB::quote($EventEmail);
            $hebrewDate = "09-10-1989"; //$this->makeHebrewDate($EventDate);
            //($EventTime != NULL) ? $eventTime = DB::qoute($EventTime) : $eventTime = NULL;
            ($Venue != NULL) ? $venue = DB::qoute($Venue) : $venue = NULL;
            ($Address != NULL) ? $address = DB::qoute($Address) : $address = NULL;
            ($EventTime != NULL) ? $eventTime = DB::qoute($EventTime) : $eventTime = NULL;
            ($EventPhone != NULL) ? $eventPhone = DB::qoute($EventPhone) : $eventPhone = NULL;
            ($Password != NULL) ? $password = DB::qoute($Password) : $password = NULL;
            ($Secret != NULL) ? $secret = DB::qoute($Secret) : $secret = NULL;
            ($DeviceID != NULL) ? $deviceID = DB::qoute($DeviceID) : $deviceID = NULL;
            

            $eventTime = '18:00';
            $what = '19:00:00';
            echo "event Name: $eventName";
            echo "event Date: $eventDate";
            echo "Event ID: $EventID";
            echo "Hebrew Date: $hebrewDate";
            echo "Event time: $eventTime";
            echo "Venue: $venue";
            echo "address: $address";
            echo "event email: $eventEmail";
            echo "event phone: $eventPhone";
            echo "password: $password";
            echo "secret: $secret";
            echo "Device ID: $deviceID";
            echo "RootID: $rootID";
            */

            // Add new event to Events table
            //$result = DB::query("INSERT INTO Events (EventName, EventDate, HebrewDate, EventTime, Venue, Address, RootID, Email, Phone, Password, Secret, DeviceID) VALUES
            //                            ($eventName, $eventDate, $hebrewDate, $eventTime, $venue, $address, $rootID, $eventEmail, $eventPhone, $password, $secret, $deviceID)");
            $result = DB::query("INSERT INTO Events (EventName, EventDate, HebrewDate, EventTime, Venue, Address, RootID, Email, Phone, Password, Secret, DeviceID) VALUES
                                        ('Dan and moshe', '2009-09-24', 'Tu Beav', '19:00','אולמי הנסיכה', '31 אורכידאה','123', 'oriah@gmail.com', '051-1111111', 'Bil123','79fhdasfAA', '76851Ad')");

            if (!$result) {
                echo "why?!!?";
                return false;
            }
            // set eventID if not already set.
            if (!isset($this->eventID)) {
                
                $this->eventID = DB::insertID();
            }

            // make new RSVP, Messages and RawData tables
            $this->rsvp = new RSVP($this->eventID);
            $this->messages = new Messages($this->eventID);
            $this->rawData = new RawData($this->eventID);
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
                $sql = DB::query("DELETE FROM Events WHERE ID=$eventID");
                // delete RSVP[eventID] table
                $sqlRSVP = $this->rsvp->delete();
                // delete Messages[eventID] table
                $sqlMessages = $this->messages->delete();
                // delete RawData[eventID] table
                $sqlRawData = $this->rawData->delete();
                if ($sqlRSVP and $sqlMessages and $sqlRawData) {


                    //}
                }
                $sql = DB::query("UPDATE Users SET Event$i=NULL, Permission$i=NULL
                                            WHERE Event$i=$eventID");
                // event deleted for user, shift all events left                    ??????
            }
            return true;
        }
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

    private function makeHebrewDate($Date) {
        jdtojewish(gregoriantojd(10, 9, 1989), true, CAL_JEWISH_ADD_GERESHAYIM + CAL_JEWISH_ADD_ALAFIM + CAL_JEWISH_ADD_ALAFIM_GERESH);
    }

}

?>