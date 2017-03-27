<?php

require_once('DB.php');
require_once('DB_user.php');

interface iEvent {

    public function deleteEvent(User $user);
}

class Event implements iEvent {

    protected static $db;
    public $eventID;
    public $rsvp;
    public $messages;
    public $rawData;

    /*
      private function createRSVP(int $eventID) {
      $sql = "CREATE TABLE IF NOT EXISTS RSVP_'$eventID' (
      ID INT(5) NOT NULL AUTO_INCREMENT PRIMARY KEY,
      Name VARCHAR(100) NOT NULL,
      Surname VARCHAR(100) NOT NULL,
      Nickname VARCHAR(100) DEFAULT NULL,
      Invitees INT(3) NOT NULL,
      Cell VARCHAR(12) DEFAULT NULL,
      Email VARCHAR(100) DEFAULT NULL,
      Groups VARCHAR(100) DEFAULT NULL,
      RSVP INT(3) DEFAULT NULL,
      Ride BOOLEAN DEFAULT FALSE
      ) DEFAULT CHARACTER SET utf8";

      if (!mysqli_query($link, $sql)) {
      $output = 'Error deleting user from Users table: ' . mysqli_error($link);
      include 'output.html.php';
      exit();
      }
      }

      private function createMessages(int $eventID) {
      $sql = "CREATE TABLE IF NOT EXISTS Messages_'$eventID' (
      ID INT(2) NOT NULL AUTO_INCREMENT PRIMARY KEY,
      MessageType VARCHAR(100) NOT NULL,
      Message TEXT NOT NULL,
      Groups VARCHAR(100) DEFAULT NULL,
      SendDate DATE NOT NULL,
      SendTime TIME NOT NULL
      ) DEFAULT CHARACTER SET utf8";

      if (!mysqli_query($link, $sql)) {
      $output = 'Error deleting user from Users table: ' . mysqli_error($link);
      include 'output.html.php';
      exit();
      }
      }

      private function createRawData(int $eventID) {
      $sql = "CREATE TABLE IF NOT EXISTS RawData_'$eventID' (
      ID INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
      Name VARCHAR(100) NOT NULL,
      Surname VARCHAR(100) NOT NULL,
      Cell VARCHAR(12) DEFAULT NULL,
      Email VARCHAR(100) DEFAULT NULL,
      Groups VARCHAR(100) DEFAULT NULL,
      RSVP INT(3) DEFAULT NULL,
      Message TEXT NOT NULL,
      RecivedDate DATE NOT NULL,
      RecivedTime TIME NOT NULL
      ) DEFAULT CHARACTER SET utf8";

      if (!mysqli_query($link, $sql)) {
      $output = 'Error deleting user from Users table: ' . mysqli_error($link);
      include 'output.html.php';
      exit();
      }
      }
     */

    /**
     * __construct: create new Event object. if Event not in Events table (eventName,eventDate!=Null) add Event to Events table (do not make any change to Users Table!)
     * @param User $user : user element connected to this instance of event
     * @param string $EventName : name of event owner/owners or name of event
     * @param date $EventDate : date of event
     * @param string $Email : Email to use for sending and receiving Emails
     * @param string $EventPhone : Phone to use for sending and receiving messages
     * @return object Event
     */
    public function __construct(User $user, $EventName = NULL, $EventDate = NULL, $EventPhone = NULL, $EventEmail = NULL) {

        if (!isset(self::$db)) {
            self::$db = $user->getDB();
        }
        // Event is not in Events table (new Event)
        if ($EventName != NULL and $EventDate != NULL and $EventDate != NULL and $EventEmail != NULL and $EventPhone != NULL) {
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
            /*
              $rsvp = new RSVP();
              $messages = new Messages();
              $rawData = new RawData();
             */
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
            if ($result["event$i"] === $this->eventID) {
                $eventID = $this->eventID;
                if ($result["permission$i"] === 'root') {
                    // delete event from Events table
                    $sql = self::$db->query("DELETE FROM Events WHERE ID=$eventID");
                    if ($sql) {
                        /*
                          // delete RSVP[eventID] table
                          $sql = $db->query("DROP TABLE RSVP$eventID");
                          // delete Messages[eventID] table
                          $sql = $db->query("DROP TABLE Messages$eventID");
                          // delete RawData[eventID] table
                          $sql = $db->query("DROP TABLE RawData$eventID");
                          if ($sql) {
                         */
                        self::$db->query("UPDATE Users SET Event$i=NULL, Permission$i=NULL
                                            WHERE Event$i=$eventID");
                        return true;
                        //}
                    }
                }
            }
        }
        return false;
    }

}

?>