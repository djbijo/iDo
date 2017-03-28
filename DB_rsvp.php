<?php

require_once ("DB_event.php");

interface iRSVP {


}

class RSVP implements iRsvp {

    protected static $db;
    protected static $eventID;

    /**
     * __construct: create new RSVP object. if user not in Users table (name,email,phone,eventName,eventDate!=Null) add user to users list.
     * "event" is 1st event on list, until chosen otherwise.
     * @param string $ID : user id
     * @param string $Name : user name , DEFAULT=NULL
     * @param string $Email : user login email , DEFAULT=NULL
     * @param string $Phone : user cell phone number , DEFAULT=NULL
     * @param string $EventName : name of event owner/owners or name of event , DEFAULT=NULL
     * @param date $EventDate : date of event , DEFAULT=NULL
     * @return object user  
     */
    public function __construct(Event $Event) {
        
        if (isset(self::$eventID) And isset(self::$db)) {
            return;
        }
        
        // get DataBase from event (initially from user)
        if (!isset(self::$db)) {
            self::$db = $Event->getDB();
        }
        // get EventID from event
        if (!isset(self::$eventID)) {
            self::$eventID = $Event->eventID;
        }
        
        $eventID = self::$eventID;
        
        $result = self::$db->query("CREATE TABLE IF NOT EXISTS RSVP$eventID (
                ID INT(5) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                Name VARCHAR(50) NOT NULL,
                Surname VARCHAR(50) NOT NULL,
                Nickname VARCHAR(50) DEFAULT NULL,
                Invitees INT(3) NOT NULL,
                Phone VARCHAR(12) DEFAULT NULL,
                Email VARCHAR(50) DEFAULT NULL,
                Groups VARCHAR(50) DEFAULT NULL,
                RSVP INT(3) DEFAULT NULL,
                Ride BOOLEAN DEFAULT FALSE
                ) DEFAULT CHARACTER SET utf8");

        if (!$result) {
            echo "Error adding RSVP$eventID to Database";
            return false;
        }
        return;
    }

}

?>