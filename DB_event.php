<?php

require_once('DB_rsvp.php');
require_once('DB_message.php');
require_once('DB_rawData.php');

interface iEvent
{

    public function deleteEvent(User $user);

    public function getEventID();
}

class Event implements iEvent
{

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
    public function __construct(User $user, $addEvent = 0, $EventName = NULL, $EventDate = NULL, $EventID = NULL, $HebrewDate = NULL, $EventTime = NULL,
                                $Venue = NULL, $Address = NULL, $EventEmail = NULL, $EventPhone = NULL, $Password = NULL, $Secret = NULL, $DeviceID = NULL)
    {

        $events = $user->getEvents();
        // user exists

        if (!$addEvent and $events['event1'] != NULL) {
            $this->eventID = $events['event1'];
            $this->rsvp = new RSVP($this->eventID);
            $this->messages = new Messages($this->eventID);
            $this->rawData = new RawData($this->eventID);
        } // Event is not in Events table (new Event)
        elseif (($EventName and $EventDate) or $addEvent) {
            // initiate Database with user Database
            // Make strings query safe
            $rootID = $user->getID();
            $eventName = DB::quote($EventName);
            $eventDate = DB::quote($EventDate);
            $eventEmail = DB::quote($EventEmail);
            $hebrewDate = "09-10-1989"; //todo: $this->makeHebrewDate($EventDate);
            $venue = DB::quote($Venue);
            $address = DB::quote($Address);
            $eventTime = DB::quote($EventTime);
            $eventPhone = DB::quote($EventPhone);
            $password = DB::quote($Password);
            $secret = DB::quote($Secret);
            $deviceID = DB::quote($DeviceID);

            // Add new event to Events table
            $result = DB::query("INSERT INTO Events (EventName, EventDate, HebrewDate, EventTime, Venue, Address, RootID, Email, Phone, Password, Secret, DeviceID) VALUES
                                        ($eventName, $eventDate, $hebrewDate, $eventTime, $venue, $address, $rootID, $eventEmail, $eventPhone, $password, $secret, $deviceID)");
            if (!$result) {
                throw new Exception("Event New : Event not inserted to Events table");
                return false;
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
     */
    public function deleteEvent(User $user)
    {
        // Check user permission for event
        $result = $user->getEvents();
        $eventID = DB::quote($this->eventID);

        if ($result["permission1"] === 'root') {
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
                    return false;
                }
                DB::query("UPDATE Users SET Event$i=NULL, Permission$i=NULL WHERE Event$i=$eventID");
                //if event updated
                if (DB::affectedRows() < 0) {
                    throw new Exception("Event deleteEvent: couldn't delete event$eventID from Users table");
                    return false;
                }
            }
            // event deleted for user, shift all events left
            $user->shiftAllEvents();
            return true;
        }

        throw new Exception("Event deleteEvent: only root user can delete event$eventID");
        return false;

    }


    /**
     * getEventID:  get Event ID
     * @return int  Event ID / false if ID yet initialized
     */
    public function getEventID()
    {
        if (isset($this->eventID)) {
            return $this->eventID;
        }
        return false;
    }



    private function makeHebrewDate($Date)              // todo: make this work
    {
        jdtojewish(gregoriantojd(10, 9, 1989), true, CAL_JEWISH_ADD_GERESHAYIM + CAL_JEWISH_ADD_ALAFIM + CAL_JEWISH_ADD_ALAFIM_GERESH);
    }

}

?>