<?php

require_once ("DB_tables.php");

class groups extends Table {

    /**
     * create: create new groups table named groups[$eventID] in the database
     * @return bool true if table created / false if table not created
     * @throws Exception "Groups create: Error adding Groups$eventID to Database"
     */
    public function create() {

        $eventID =$this->eventID;

        $result = DB::query("CREATE TABLE IF NOT EXISTS Groups$eventID ( 
                ID INT(5) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                GroupName VARCHAR(50) NOT NULL
                ) DEFAULT CHARACTER SET utf8;");

        if (!$result) {
            throw new Exception("Groups create: Error adding Groups$eventID to Database");
        }
        return true;
    }

    /**
     * destroy:  delete groups table from database ($messages[eventID])
     * @return bool true if messages[$eventID] table deleted / false if table wasn't
     * @throws Exception "Groups destroy: Error deleting Groups$eventID table from Database"
     */
    public function destroy() {

        $eventID = $this->eventID;
        $result = DB::query("DROP TABLE IF EXISTS groups$eventID");

        if (!$result) {
            throw new Exception("Groups destroy: Error deleting Groups$eventID table from Database");
        }
        return true;
    }

    /**
     * getMessages:  get groups table for specific event
     * @return result groups[$eventID] table or false if not groups[$eventID] exists
     */
    public function get() {

        $eventID = $this->eventID;
        $result = DB::select("SELECT * FROM groups$eventID");
        return $result;
    }

    /**
     * update:  update groups[$eventID] table in database
     * @param string $colName : column which value should be updated in
     * @param string $id : id of row to be updated
     * @param $value : value to be inserted to the colName column
     * @return bool true if table updated / false if table not updated
     * @throws Exception "שגיאה: לא ניתן להוסיף קבוצה בשם זה."
     */
    public function update($colName, $id, $value) {
        if (!$this->isPermission('root,edit')){
            throw new Exception("שגיאה: לא קיימת הרשאת גישה לעריכת קבוצות. אנא פנה למנהל האירוע ובקש הרשאה מתאימה.");
        }
        // prevent adding saved groups names
        self::validateGroup($value);
        return Table::updateTable('groups', $colName, $id, $value);
    }

    /**
     * add:  add row to groups[$eventID] table in database
     * @param string $GroupName : group name
     * @return int insert id if added
     * @throws Exception "groups add: Error adding group $groupName to groups$eventID table"
     */
    public function add($GroupName) {
        if (!$this->isPermission('root,edit')){
            throw new Exception("שגיאה: לא קיימת הרשאת גישה להוספת קבוצות. אנא פנה למנהל האירוע ובקש הרשאה מתאימה.");
        }
        // prevent adding saved groups names
        self::validateGroup($GroupName);
        // Make strings query safe
        $groupName = DB::quote($GroupName);

        $eventID = $this->eventID;

        // check if groupName already in groups table
        if (NULL !== $groupName and DB::select("SELECT * FROM groups$eventID WHERE GroupName=$groupName")) {
            throw new Exception("שגיאה: קבוצה".$groupName." זו כבר קיימת. אין אפשרות להוסיפה למאגר הקבוצות");
        }

        $result = DB::query("INSERT INTO groups" . $eventID . "  (GroupName) VALUES ($groupName)");

        if (!$result) {
            throw new Exception("groups add: Error adding group $groupName to groups$eventID table");
        }
        return DB::insertID();
    }

    /**
     * delete:  delete row in table groups[$eventID] at database
     * @param string $id : table id column (table id not user id)
     * @return bool true if row deleted / false otherwise
     * @throws Exception "שגיאה: לא קיימת הרשאת גישה למחיקת קבוצות. אנא פנה למנהל האירוע ובקש הרשאה מתאימה."
     */
    public function delete($id) {
        if (!$this->isPermission('root,edit')){
            throw new Exception("שגיאה: לא קיימת הרשאת גישה למחיקת קבוצות. אנא פנה למנהל האירוע ובקש הרשאה מתאימה.");
        }
        $id = DB::quote($id);

        return Table::deleteFromTable('groups', $id);
    }


    /**
     * validateGroup:  make sure group is not a saved name
     * @param string $group : group name
     * @return boolean true if group is valid, false otherwise
     */
    static function validateGroup($group){
        // prevent adding saved groups names
        if ($group=='all' or $group=='root' or $group=='edit' or $group=='send'){
            return false;
        }
        return true;
    }

}

?>