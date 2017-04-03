<?php

require_once ("DB_tables.php");

class Messages extends Table {

    /**
     * create: create new messages table named messages[$eventID] in the database 
     * @return bool true if table created / false if table not created
     */
    public function create() {

        $eventID = Table::$eventID;

        $result = Table::$db->query("CREATE TABLE IF NOT EXISTS Messages$eventID ( 
                ID INT(2) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                MessageType VARCHAR(10) NOT NULL,
                Message TEXT NOT NULL,
                Groups VARCHAR(100) DEFAULT NULL,
                SendDate DATE NOT NULL,
                SendTime TIME NOT NULL
              ) DEFAULT CHARACTER SET utf8");

        if (!$result) {
            echo "Error adding message$eventID to Database";
            return false;
        }
        return;
    }

    /**
     * destroy:  delete messages table from database ($messages[eventID])
     * @return bool true if messages[$eventID] table deleted / false if table wasn't
     */
    public function destroy() {

        $eventID = Table::$eventID;
        $result = Table::$db->query("DROP TABLE IF EXISTS messages$eventID");
        return $result;
    }

    /**
     * getMessages:  get messages table for specific event
     * @return result messages[$eventID] table or false if not messages[$eventID] exists
     */
    public function get() {
        $eventID = self::$eventID;
        $result = self::$db->select("SELECT * FROM messages$eventID");
        return $result;
    }

    /**
     * update:  update messages[$eventID] table in database
     * @return bool true if table updated / false if table not updated
     */
    public function update($colName, $id, $value) {
        return Table::updateTable('messages', $colName, $id, $value);
    }

    /**
     * add:  add row to messages[$eventID] table in database
     * @param $MessageType : type of message [SaveTheDate/Reminder/ThankYou]
     * @param $Message : message text to be sent
     * @param SendDate : date to send the message
     * @param $SendTime : time of day to send the message
     * @param $Groups: group that message should be sent to (default null)
     * @return bool true if row added / false otherwise
     */
    public function add($MessageType, $Message, $SendDate, $SendTime, $Groups = NULL) {

        // Make strings query safe
        $messageType = Table::$db->qoute($MessageType);
        $message = Table::$db->qoute($Message);
        ($Groups != NULL) ? $groups = Table::$db->qoute($Groups) : $groups = NULL;

        $eventID = Table::$eventID;

        $result = Table::$db->query("INSERT INTO messages$eventID (MessageType, Message, Groups, SendDate, SendTime) VALUES
                    ( $messageType, $message, $groups, $SendDate, $SendTime)");
        return $result;
    }

    /**
     * delete:  delete row in table messages[$eventID] at database
     * @param string $id : table id column (table id not user id)
     * @return bool true if row deleted / false otherwise
     */
    public function delete($id) {
        return Table::deleteFromTable('messages', $id);
    }
}

?>