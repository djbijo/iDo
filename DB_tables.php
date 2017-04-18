<?php

require_once ("DB_event.php");

/**
 * Table class for different tables in the database [rsvp/messages/rawData]
 */
abstract class Table {

    protected $eventID;
    protected $permissions;

    /**
     * __construct: create new object.
     * @param int $eventID : 1st event on list, until chosen otherwise.
     * @return Table object table[rsvp,messages,rawData,groups]
     */
    public function __construct($eventID, $permission) {

        // get EventID from event
        if (!isset($this->eventID)) {
            $this->eventID = $eventID;
        }
        $this->permission = $permission;
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
        DB::query("UPDATE $tableType"."$eventID SET $colName = $value WHERE id = $id");

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

    /**
     * switch: change the event id
     * @param int $EventID : the EventID that we would like to change to
     * @return bool true if eventID changed / false otherwise
     */
    public function switchEvent($EventID) {
        $this->eventID = $EventID;
        return true;
    }

    /**
     * isPermission: check if permission is admin permission[root/edit/send] according to $relevant
     * @param string $relevant : permissions that are relevant for containing function - as permission1,permission2...
     * @return bool true if permission contains one or more of relevant permissions / false otherwise
     */
    protected function isPermission($relevant) {

        // if given relevant function permissions, return true if any exist, false otherwise
        $array = explode(',',$relevant);
        $regexp = '';
        foreach ($array as $permission){
            $regexp = $regexp.'\b'."$permission".'\b|';
        }
        // set /$regexp/ and trim last | from regexp
        $regexp = "/".rtrim($regexp,"|")."/";
        return (filter_var($this->permissions, FILTER_VALIDATE_REGEXP,
            array("options"=>array("regexp"=>$regexp)))) ? true : false;
    }
    /**
 * getByGroups:  get RSVP table for specific event by Groups
 * @param string $tableType ['rsvp'/'rawData']
 * @param string $Groups : groups separated by a comma
 * @param $validPhone
 * @return RSVP table of guests from all the groups combined / false if group is empty
 */
    public function getByGroups($tableType, $Groups, $validPhone = 0) {

        $eventID = $this->eventID;

        // if empty group
        if ($Groups == NULL) return false;
        // if sending to all guests
        if ($this->isPermission('root,edit,send,all')){
            $result = DB::select("SELECT * FROM $tableType"."$eventID");
            return $result;
        }

        // prepare query (append while array[i] is not null)
        $query = $this->orGroups($Groups);
        // make sure no Phones are null
        if ($validPhone){
            $query = $query . " AND Phone <> 'NULL'";
        }

        $result = DB::select("SELECT * FROM $tableType"."$eventID WHERE Groups=$query");
        return $result;
    }

    /**
     * getByGroups:  get RSVP table for specific event by Groups
     * @param string $tableType ['rsvp'/'rawData']
     * @param string $Groups : groups separated by a comma
     * @param $validPhone
     * @return RSVP table of guests from all the groups combined / false if group is empty
     */
    public function getByPhones($tableType, $Phone) {

        $eventID = $this->eventID;
        $phone = validatePhone($Phone);

        // if empty group
        if ($Phone == NULL) return false;
        // if sending to all guests
        if ($this->isPermission('root,edit,send')){
            $result = DB::select("SELECT * FROM $tableType"."$eventID WHERE Phone=$phone");
            return $result;
        }

        // prepare query (append while array[i] is not null)
        $query = $this->orGroups($this->permissions);
        // make sure no Phones are null

        $result = DB::select("SELECT * FROM $tableType"."$eventID WHERE Phone=$phone AND Groups=$query");
        return $result;
    }

    /**
     * orGroups: set string of 'Group1 OR group2...'
     * @param string $Groups : groups separated by a comma
     * @return string 'Group1 OR group2...'
     */
    public function orGroups($Groups) {

        // break groups into an array
        $array = explode(',',$Groups);
        // prepare query (append while array[i] is not null)
        $i=0;
        while (isset($array[$i])){
            $arrayI = DB::quote($array[$i]);
            if ($i==0){
                $orGroup = "$arrayI";
            }
            $orGroup = $orGroup . " OR $arrayI";
            $i++;
        }

        return $orGroup;
    }

}

?>