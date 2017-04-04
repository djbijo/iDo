<?php

require_once ("DB_tables.php");

class rawData extends Table {

    /**
     * create: create new rawData table named rawData[$eventID] in the database 
     * @return bool true if table created / false if table not created
     */
    public function create() {

        $eventID = Table::$eventID;

        $result = Table::$db->query("CREATE TABLE IF NOT EXISTS rawData$eventID ( 
                ID INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                Name VARCHAR(50) NOT NULL,
                Surname VARCHAR(50) NOT NULL,
                Phone VARCHAR(12) DEFAULT NULL,
                Email VARCHAR(50) DEFAULT NULL,
                Groups VARCHAR(50) DEFAULT NULL,
                RSVP INT(3) DEFAULT NULL,
                Ride BOOLEAN DEFAULT FALSE,
                Message TEXT NOT NULL,
                Recived datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
                ) DEFAULT CHARACTER SET utf8;");

        if (!$result) {
            echo "Error adding rawData$eventID to Database";
            return false;
        }
        return;
    }

    /**
     * destroy:  delete rawData table from database ($messages[eventID])
     * @return bool true if messages[$eventID] table deleted / false if table wasn't
     */
    public function destroy() {

        $eventID = Table::$eventID;
        $result = Table::$db->query("DROP TABLE IF EXISTS rawData$eventID");
        return $result;
    }

    /**
     * getMessages:  get rawData table for specific event
     * @return result rawData[$eventID] table or false if not rawData[$eventID] exists
     */
    public function get() {
        $eventID = self::$eventID;
        $result = self::$db->select("SELECT * FROM rawData$eventID");
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
        $name = Table::$db->qoute($Name);
        $surName = Table::$db->qoute($SurName);
        $message = Table::$db->qoute($Message);
        ($Phone != NULL) ? $phone = Table::$db->qoute($Phone) : $phone = NULL;
        ($Email != NULL ) ? $email = Table::$db->qoute($Email) : $email = NULL;
        ($Groups != NULL) ? $groups = Table::$db->qoute($Groups) : $groups = NULL;
        
        //$RecivedDate, $RecivedTime;

        $eventID = Table::$eventID;

        $result = Table::$db->query("INSERT INTO rawData" . $eventID . "  (Name, Surname, Phone, Email, Groups, RSVP, Ride, Message) VALUES
                    ( $name, $surName, $phone, $email, $groups, $RSVP, $Ride, $message)");
        return $result;
    }

    /**
     * delete:  delete row in table rsvp[$eventID] at database
     * @param string $id : table id column (table id not user id)
     * @return bool true if row deleted / false otherwise
     */
    public function delete($id) {
        return Table::deleteFromTable('rawData', $id);
    }

}

?>