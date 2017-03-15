<?php

include ("DB.php");
include ("DB_user.php");

interface iEvent {

    public function createEvent($Name, $EventDate);

    public function deleteEvent($Event, $UserID);
}

class Event implements iEvent {

    protected static $eventID;

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


    /*
     * createEvent: create new event in Events table
     * $Name 		string: name of event owner/owners or name of event
     * $EventDate 	date: date of event
     * @return: false=event not created, eventID=event created successfuly
     */

    public function createEvent($Name, $EventDate) {

        $db = new DB();
        // Make strings query safe
        $name = $db->quote($Name);

        // Add new event to Events table
        $result = $db->query("INSERT INTO Events SET Name = '$name', Date = '$EventDate'");

        if (!$result) {
            return false;
        }

        if (!isset(self::$event)) {
            self::$event = $db->insert_id;
            return self::$event;
        }
    }

    /*
     * deleteEvent: delete relevant event from events table, delete also RSVP table, Messages table and RawData table
     * $Event int: the ID of the event to erase
     * @return: false = event not erased or no 'root' permission for user, true = event erased successfuly
     */

    public function deleteEvent($Event, $UserID) {
        
        $db = new DB();
        $user = new User();
        // Check user permission for event
        $result = $user->getEvents($UserID);
        for ($i = 1; $i <= 3; $i++) {
            if ($result["event'$i'"] == $Event) {
                if ($result["permission'$i'"] == 'root') {
                    // delete event from Events table
                    $sql = $db->query("DELETE FROM Events WHERE ID = '$Event'");
                    if ($sql) {
                        // delete event[eventID] table
                        $sql = $db->query("DROP TABLE Event'$Event'");
                        if ($sql) {
                            // delete RSVP[eventID] table
                            $sql = $db->query("DROP TABLE RSVP'$Event'");
                            if ($sql) {
                                // delete Messages[eventID] table
                                $sql = $db->query("DROP TABLE Messages'$Event'");
                                if ($sql) {
                                    // delete RawData[eventID] table
                                    $sql = $db->query("DROP TABLE RawData'$Event'");
                                    return true;
                                }
                            }
                        }
                    }
                }
            }
        }
        return false;
    }
}

?>