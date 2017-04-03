<?php

require_once ("DB_event.php");

/**
 * Table class for different tables in the database [rsvp/messages/rawData]
 */
abstract class Table {

    protected static $db;
    protected static $eventID;

    /**
     * __construct: create new object.
     * "eventID" is of 1st event on list, until chosen otherwise.
     * @return object table[rsvp,messages,rawData]
     */
    public function __construct(Event $Event = NULL) {

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
        // create table - this is an abstract method
        $this->create();
        return;
    }

    /**
     * delete:  delete table for specific event
     * @return bool true if table deleted / false if table wasn't deleted
     */
    public function destruct() {
        // check that table is initiallised
        if (!isset(self::$eventID) And ! isset(self::$db)) {
            return false;
        }

        return $this->destroy();
    }

    /**
     * updateTable:  update table in database
     * @param string $tableType : the type of the table [rsvp/messages/rawData]
     * @param string $colName : name of column to be updated
     * @param string $id : table id column (table id not user id)
     * @param string/int value : value to be inserted to the table
     * @return bool true if table updated / false if table not updated
     */
    public function updateTable($tableType, $colName, $id, $value) {
        // handel data
        ($value === "") ? $value = NULL : $value = self::$db->qoute($value);
        $eventID = self::$eventID;

        // generate mysql command
        if ($stmt = self::$db->prepare("UPDATE " . $tableType . $eventID . " SET " . $colName . " = ? WHERE id = ?")) {
            $stmt->bind_param("si", $value, $id);
            $result = $stmt->execute();
            $stmt->close();
        }
        return $result;
    }

    /**
     * deleteFromTable:  delete row in table at database
     * @param string $tableType : the type of the table [rsvp/messages/rawData]
     * @param string $id : table id column (table id not user id)
     * @return bool true if row deleted / false otherwise
     */
    public function deleteFromTable($tableType, $id) {
        $eventID = self::$eventID;

        // generate mysql command
        if ($stmt = self::$db->prepare("DELETE FROM ".$tableType.$eventID."  WHERE id = ?")) {
            $stmt->bind_param("i", $id);
            $result = $stmt->execute();
            $stmt->close();
        }
        return $result;
    }

}

?>