<?php

require_once ("DB_tables.php");

class groups extends Table {

    /**
     * create: create new groups table named groups[$eventID] in the database
     * @return bool true if table created / false if table not created
     */
    public function create() {

        $eventID =$this->eventID;

        $result = DB::query("CREATE TABLE IF NOT EXISTS Groups$eventID ( 
                ID INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                GroupName VARCHAR(50) NOT NULL,
                ) DEFAULT CHARACTER SET utf8;");

        if (!$result) {
            throw new Exception("Groups create: Error adding Groups$eventID to Database");
            return false;
        }
        return;
    }

    /**
     * destroy:  delete rawData table from database ($messages[eventID])
     * @return bool true if messages[$eventID] table deleted / false if table wasn't
     */
    public function destroy() {

        $eventID = $this->eventID;
        $result = DB::query("DROP TABLE IF EXISTS rawData$eventID");

        if (!$result) {
            throw new Exception("RawData destroy: Error deleting RawData$eventID table from Database");
            return false;
        }
        return true;
    }

    /**
     * getMessages:  get rawData table for specific event
     * @return result rawData[$eventID] table or false if not rawData[$eventID] exists
     */
    public function get() {
        $eventID = $this->eventID;
        $result = DB::select("SELECT * FROM rawData$eventID");
        return $result;
    }

    /**
     * update:  update rawData[$eventID] table in database
     * @return bool true if table updated / false if table not updated
     */
    public function update($colName, $id, $value) {
        return Table::updateTable('rawData', $colName, $id, $value);
    }

    /**
     * add:  add row to rawData[$eventID] table in database
     * @param string $Name : guest name
     * @param string $SurName : guest surname
     * @param string $Message : sms/email content
     * @param string $Phone : guest phone (default null)
     * @param string $Email : guest email (default null)
     * @param string $Groups : group in which the guest is categorised (default null)
     * @param int $RSVP : amount of guests coming (default 0)
     * @param bool $Ride : if the guest ordered a ride or not (default false)
     * @return bool true if row added / false otherwise
     */
    public function add($Name, $SurName, $Message, $Phone = NULL, $Email = NULL, $Groups = NULL, $RSVP = 0, $Ride = false) {

        // Make strings query safe
        $name = DB::qoute($Name);
        $surName = DB::qoute($SurName);
        $message = DB::qoute($Message);
        $phone = DB::qoute($Phone);
        $email = DB::qoute($Email);
        $groups = DB::qoute($Groups);

        $eventID = $this->eventID;

        $result = DB::query("INSERT INTO rawData" . $eventID . "  (Name, Surname, Phone, Email, Groups, RSVP, Ride, Message) VALUES
                    ( $name, $surName, $phone, $email, $groups, $RSVP, $Ride, $message)");

        if (!$result) {
            throw new Exception("RawData add: Error adding rawData from $name $surName to RawData$eventID table");
            return false;
        }
        return true;
    }

    /**
     * delete:  delete row in table rsvp[$eventID] at database
     * @param string $id : table id column (table id not user id)
     * @return bool true if row deleted / false otherwise
     */
    public function delete($id) {
        return Table::deleteFromTable('rawData', $id);
    }
    
    /**
     * getByPhone:  get RawData table for specific event by phone number (one row)
     * @param string $Phone : the phone number of the guest
     * @return row of specific guest (specified by phone number)
     */
    public function getByPhone($Phone) {
        $eventID = $this->eventID;
        
        $phone = DB::quote($Phone);
        $result = DB::select("SELECT * FROM RawData$eventID WHERE Phone=$phone");
        return $result;
    }

}

?>