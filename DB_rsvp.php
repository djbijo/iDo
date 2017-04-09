<?php

require_once ("DB_tables.php");

class RSVP extends Table {

    /**
     * create: create new RSVP table named rsvp[$eventID] in the database
     * @return bool true if table created / false if table not created
     */
    public function create() {

        $eventID = $this->eventID;       

        $result = DB::query("CREATE TABLE IF NOT EXISTS RSVP$eventID (
                ID INT(5) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                Name VARCHAR(50) NOT NULL,
                Surname VARCHAR(50) NOT NULL,
                Nickname VARCHAR(50) DEFAULT NULL,
                Invitees INT(3) NOT NULL,
                Phone VARCHAR(12) DEFAULT NULL,
                Email VARCHAR(50) DEFAULT NULL,
                Groups VARCHAR(50) DEFAULT NULL,
                RSVP INT(3) DEFAULT NULL,
                Uncertin int(3) DEFAULT NULL,
                Ride BOOLEAN DEFAULT FALSE
                ) DEFAULT CHARACTER SET utf8");

        if (!$result) {
            throw new Exception("RSVP create: Error adding RSVP$eventID to Database");
            return false;
        }
        return true;
    }

    /**
     * destroy:  delete RSVP table from database ($rsvp[eventID])
     * @return bool true if rsvp[$eventID] table deleted / false if table wasn't
     */
    public function destroy() {
        // check that rsvp is initiallised

        $eventID = $this->eventID;
        $result = DB::query("DROP TABLE IF EXISTS rsvp$eventID");

        if (!$result) {
            throw new Exception("RSVP destroy: Error deleting RSVP$eventID table from Database");
            return false;
        }
        return true;
    }

    /**
     * get:  get RSVP table for specific event
     * @return result RSVP[$eventID] table
     */
    public function get() {
        $eventID = $this->eventID;
        $result = DB::select("SELECT * FROM rsvp$eventID");
        return $result;
    }
    
    /**
     * getByPhone:  get RSVP table for specific event by phone number (one row)
     * @param string $Phone : the phone number of the guest
     * @return row of specific guest (specified by phone number)
     */
    public function getByPhone($Phone) {
        $eventID = $this->eventID;
        
        $phone = DB::quote($Phone);
        $result = DB::select("SELECT * FROM rsvp$eventID WHERE Phone=$phone");
        return $result;
    }

    /**
     * getByGroups:  get RSVP table for specific event by Groups
     * @param string $Group : groups separated by comma
     * @return result RSVP[$eventID] table of guests from all the groups combined / false if group is empty
     */
    public function getByGroups($Groups) {
        
        $eventID = $this->eventID;
        
        // break groups into an array
        $array = explode(',',$Groups);
        
        // if empty group
        if ($array[0] === NULL) return false;
        
        // prepare query (append while array[i] is not null)
        $i=1;
        // make query safe
        $arrayI = DB::quote($array[0]);
        $query = "SELECT * FROM rsvp$eventID WHERE Groups=$arrayI";
        
        while ($array[$i]){
            $arrayI = DB::quote($array[$i]);
            $query = $query . " OR Groups=$arrayI";
            $i++;
        }
        
        $result = DB::select($query);
        return $result;
    }

    /**
     * update:  update rsvp[$eventID] table in database
     * @return bool true if table updated / false if table not updated
     */
    public function update($colName, $id, $value) {
        return Table::updateTable('rsvp', $colName, $id, $value);
    }

    /**
     * add:  add row to rsvp[$eventID] table in database
     * @param string $Name : guest name
     * @param string $SurName : guest surname
     * @param int invitees : number of people invited with guest
     * @param string $NickName : guest nickname (default null)
     * @param string $Phone : guest phone (default null)
     * @param string $Email : guest email (default null)
     * @param string $Groups : group in which the guest is categorised (default null)
     * @param int $RSVP : amount of guests coming (default 0)
     * @param bool $Ride : if the guest ordered a ride or not (default false)
     * @return bool true if row added / false otherwise
     */
    public function add($Name, $SurName, $Invitees, $NickName = 'NULL', $Phone = 'NULL', $Email = 'NULL', $Groups = 'NULL', $RSVP = 0, $Uncertin = 0,$Ride = 0) {

        // Make strings query safe
        $name = DB::quote($Name);
        $surName = DB::quote($SurName);
        $nickName = DB::quote($NickName);
        $phone = DB::quote($Phone);
        $email = DB::quote($Email);
        $groups = DB::quote($Groups);

        $eventID = $this->eventID;

        //check that phone and email are not already in rsvp table
        if (NULL !== $email and DB::select("SELECT * FROM rsvp$eventID WHERE Email=$email")) {
            throw new Exception("RSVP add: Email $email already in RSVP table");
        }
        if (NULL !== $phone and DB::select("SELECT * FROM rsvp$eventID WHERE Phone=$phone")) {
            throw new Exception("RSVP add: Phone $phone already in RSVP table");
        }

        //insert guest to RSVP table
        $result = DB::query("INSERT INTO rsvp$eventID (Name, Surname, Nickname, Invitees, Phone, Email, Groups, RSVP, Uncertin, Ride) VALUES
                    ($name, $surName, $nickName, $Invitees, $phone, $email, $groups, $RSVP, $Uncertin, $Ride)");

        if (!$result) {
            throw new Exception("RSVP add: Error adding guest $name $surName to RSVP$eventID table");
        }
        return DB::insertID();
    }

    /**
     * delete:  delete row in table rsvp[$eventID] at database
     * @param string $id : table id column (table id not user id)
     * @return bool true if row deleted / false otherwise
     */
    public function delete($id) {
        return Table::deleteFromTable('rsvp', $id);
    }

    /**
     * importFullExcel:  import RSVP table from excel file (deleting previous rsvp table)
     * @param file $excel : excel file
     * @return bool true if excel imported / false if excel not imported
     */
    public function importFullExcel($excel) {
        // delete rsvp[eventID] table if exists
        $this->destruct();
        // create new (empty) rsvp[eventID] table
        new RSVP();                                         //todo: should we de new rsvp?! gil...
        return $this->importPartExcel($excel);
    }

    /**
     * importPartExcel:  import RSVP table from excel file (deleting previous rsvp table)
     * @param file $excel : excel file
     * @return bool true if excel imported / false if excel not imported
     */
    public function importPartExcel($excel) {
        // open excel file
        $file = fopen($excel, "r");
        $count = 0;
        $eventID = $this->eventID;

        // insert data to relevant rsvp table
        while (($empData = fgetcsv($file, 10000, ",")) !== false) {
            $count++;
            if ($count > 1) {  // discard title
                $result = DB::query("INSERT INTO rsvp$eventID (Name, Surname, Nickname, Invitees, Phone, Email, Groups, RSVP, Uncertin, Ride) VALUES
                            ('$empData[0]','$empData[1]','$empData[2]','$empData[3]','$empData[4]','$empData[5]','$empData[6]','$empData[7]','$empData[8]','$empData[9]')");
                if (!$result) {
                    return false;
                }
            }
        }
        return true;
    }

    /**
     * exportExcel:  export rsvp table to csv format
     * thanks John Peter for this solution: http://stackoverflow.com/questions/15699301/export-mysql-data-to-excel-in-php
     * @return bool true if excel imported / false if excel not imported
     */
    public function exportExcel($sample = false) {

        $eventID = $this->eventID;

        $DB_Server = "localhost"; //MySQL Server    
        $DB_Username = "root"; //MySQL Username     
        $DB_Password = "";             //MySQL Password     
        $DB_DBName = "idodb";         //MySQL Database Name 
        $DB_TBLName = "rsvp$eventID"; //MySQL Table Name     
        $filename = "RSVP";         //File Name

        if ($sample) {
            $DB_TBLName = "RSVPsample";
            $filename = "RSVP_sample";
        }

        /*         * *****YOU DO NOT NEED TO EDIT ANYTHING BELOW THIS LINE****** */
        //create MySQL connection   
        $sql = "Select * from $DB_TBLName";
        $Connect = @mysql_connect($DB_Server, $DB_Username, $DB_Password) or die("Couldn't connect to MySQL:<br>" . mysql_error() . "<br>" . mysql_errno());
        //select database   
        $Db = @mysql_select_db($DB_DBName, $Connect) or die("Couldn't select database:<br>" . mysql_error() . "<br>" . mysql_errno());
        //execute query 
        $result = @mysql_query($sql, $Connect) or die("Couldn't execute query:<br>" . mysql_error() . "<br>" . mysql_errno());
        $file_ending = "xls";
        //header info for browser
        header("Content-Type: application/xls");
        header("Content-Disposition: attachment; filename=$filename.xls");
        header("Pragma: no-cache");
        header("Expires: 0");
        /*         * *****Start of Formatting for Excel****** */
        //define separator (defines columns in excel & tabs in word)
        $sep = "\t"; //tabbed character
        //start of printing column names as names of MySQL fields
        for ($i = 0; $i < mysql_num_fields($result); $i++) {
            echo mysql_field_name($result, $i) . "\t";
        }
        print("\n");
        //end of printing column names  
        //start while loop to get data
        while ($row = mysql_fetch_row($result)) {
            $schema_insert = "";
            for ($j = 0; $j < mysql_num_fields($result); $j++) {
                if (!isset($row[$j]))
                    $schema_insert .= "NULL" . $sep;
                elseif ($row[$j] != "")
                    $schema_insert .= "$row[$j]" . $sep;
                else
                    $schema_insert .= "" . $sep;
            }
            $schema_insert = str_replace($sep . "$", "", $schema_insert);
            $schema_insert = preg_replace("/\r\n|\n\r|\n|\r/", " ", $schema_insert);
            $schema_insert .= "\t";
            print(trim($schema_insert));
            print "\n";
        }
    }

    /**
     * sampleExcel:  export sample rsvp table to csv format for user usage
     * @return bool true if excel imported / false if excel not imported
     */
    public function getSampleExcel() {
        $this->exportExcel(true);
    }

}

?>