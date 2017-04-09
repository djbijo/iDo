<?php

require_once ("DB_event.php");

/**
 * Table class for different tables in the database [rsvp/messages/rawData]
 */
abstract class Table {

    protected $eventID;

    /**
     * __construct: create new object.
     * @param int $eventID : 1st event on list, until chosen otherwise.
     * @return Table object table[rsvp,messages,rawData,groups]
     */
    public function __construct($eventID) {

        // get EventID from event
        if (!isset($this->eventID)) {
            $this->eventID = $eventID;
        }
        // create table - this is an abstract method
        $this->create();
        return;
    }

    /**
     * delete:  delete table for specific event
     * @return bool true if table deleted / false if table wasn't deleted
     * @throws Exception "Table destruct: eventID not initialized"
     */
    public function destruct() {
        // check that table is initialised
        if (!isset($this->eventID)) {
            throw new Exception("Table destruct: eventID not initialized");
        }
        // destroy table - this is an abstract method
        return $this->destroy();
    }

    /**
     * updateTable:  update table in database
     * @param string $tableType : the type of the table [rsvp/messages/rawData]
     * @param string $colName : name of column to be updated
     * @param string $id : table id column (table id not user id)
     * @param string/int value : value to be inserted to the table
     * @return bool true if table updated / false if table not updated
     * @throws Exception "Table $tableType updateTable: couldn't update table ".$tableType.$eventID." with $colName = $value for row $id"
     */
    public function updateTable($tableType, $colName, $id, $Value) {
        // handel data
        $value = DB::quote($Value);
        $eventID = $this->eventID;

        // generate mysql command
        DB::query("UPDATE ".$tableType.$eventID." SET $colName = $value WHERE id = $id");

        if (DB::affectedRows() < 0) {
            throw new Exception("Table $tableType updateTable: couldn't update table ".$tableType.$eventID." with $colName = $value for row $id");
        }
        return true;
    }

    /**
     * deleteFromTable:  delete row in table at database
     * @param string $tableType : the type of the table [rsvp/messages/rawData]
     * @param string $id : table id column (table id not user id)
     * @return bool true if row deleted / false otherwise
     * @throws Exception "Table deleteFromTable: couldn't delete row $id from table ".$tableType.$eventID
     */
    public function deleteFromTable($tableType, $id) {
        $eventID = $this->eventID;

        // generate mysql command
        $result = DB::query("DELETE FROM ".$tableType.$eventID."  WHERE id = $id");

        if (!$result) {
            throw new Exception("Table deleteFromTable: couldn't delete row $id from table ".$tableType.$eventID);
        }
        return true;
    }

}

?>