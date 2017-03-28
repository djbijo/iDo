<?php

require_once ("DB_event.php");

interface iRSVP {

        public function getRSVP();
        
        public function deleteRSVP();
        
        public function importFullExcel($excel);
        
        public function importPartExcel($excel);
        
        public function exportExcel();
        
        public function sampleExcel();

}

class RSVP implements iRsvp {

    protected static $db;
    protected static $eventID;

    /**
     * __construct: create new RSVP object. if user not in Users table (name,email,phone,eventName,eventDate!=Null) add user to users list.
     * "event" is 1st event on list, until chosen otherwise.
     * @param string $ID : user id
     * @param string $Name : user name , DEFAULT=NULL
     * @param string $Email : user login email , DEFAULT=NULL
     * @param string $Phone : user cell phone number , DEFAULT=NULL
     * @param string $EventName : name of event owner/owners or name of event , DEFAULT=NULL
     * @param date $EventDate : date of event , DEFAULT=NULL
     * @return object user  
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
        
        $eventID = self::$eventID;
        
        $result = self::$db->query("CREATE TABLE IF NOT EXISTS RSVP$eventID (
                ID INT(5) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                Name VARCHAR(50) NOT NULL,
                Surname VARCHAR(50) NOT NULL,
                Nickname VARCHAR(50) DEFAULT NULL,
                Invitees INT(3) NOT NULL,
                Phone VARCHAR(12) DEFAULT NULL,
                Email VARCHAR(50) DEFAULT NULL,
                Groups VARCHAR(50) DEFAULT NULL,
                RSVP INT(3) DEFAULT NULL,
                Ride BOOLEAN DEFAULT FALSE
                ) DEFAULT CHARACTER SET utf8");

        if (!$result) {
            echo "Error adding RSVP$eventID to Database";
            return false;
        }
        return;
    }
    
    /**
     * deleteRSVP:  delete RSVP table for specific event
     * @return bool true if rsvp[$eventID] table deleted / false if table wasn't deleted
     */
    public function deleteRSVP(){
        // check that rsvp is initiallised
        if (!isset(self::$eventID) And !isset(self::$db)){
            return false;
        }
        
        $eventID = self::$eventID;
        $result = self::$db->query("DROP TABLE IF EXISTS rsvp$eventID");
        if ($result) {return true;}
        return false;
    }
    
    /**
     * getRSVP:  get RSVP table for specific event
     * @return result RSVP[$eventID] table
     */
    public function getRSVP(){
        $eventID = self::$eventID;
        $result = self::$db->query("SELECT * FROM rsvp$eventID");
        return $result;
    }
    
    /**
     * importFullExcel:  import RSVP table from excel file (deleting previous rsvp table)
     * @param file $excel : excel file
     * @return bool true if excel imported / false if excel not imported
     */
    public function importFullExcel($excel) {
        // delete rsvp[eventID] table if exists
        $this->deleteRSVP();
        // create new (empty) rsvp[eventID] table
        $this = new RSVP();
        return $this->importPartExcel($excel);   
    }
    
    /**
     * importPartExcel:  import RSVP table from excel file (deleting previous rsvp table)
     * @param file $excel : excel file
     * @return bool true if excel imported / false if excel not imported
     */
    public function importPartExcel($excel) {
        // open excel file
        $file = fopen($excel,"r");
        $count = 0;
        $eventID = self::$eventID;
        
        // insert data to relevant rsvp table
        while (($emapData = fgetcsv($file, 10000,",")) !== false){
            $count++;
            if ($count>1){  // discard title
                $resutl = self::$db-query("INSERT INTO rsvp$eventID (Name, Surname, Nickname, Invitees, Phone, Email, Groups, RSVP, Ride) VALUES
                            ('$empData[0]','$empData[1]','$empData[2]','$empData[3]','$empData[4]','$empData[5]','$empData[6]','$empData[7]','$empData[8]')");
                if (!$result) {return false;}
            }    
        }
        return true;
    }
    
    
    /**
     * exportExcel:  export rsvp table to csv format
     * thanks John Peter for this solution: http://stackoverflow.com/questions/15699301/export-mysql-data-to-excel-in-php
     * @return bool true if excel imported / false if excel not imported
     */    
    public function exportExcel(bool $sample = false){
        
        $eventID = self::$eventID;
        
        $DB_Server = "localhost"; //MySQL Server    
        $DB_Username = "root"; //MySQL Username     
        $DB_Password = "";             //MySQL Password     
        $DB_DBName = "idodb";         //MySQL Database Name 
        $DB_TBLName = "rsvp$eventID"; //MySQL Table Name     
        $filename = "RSVP";         //File Name
        
        if ($sample){
            $DB_TBLName = "RSVPsample";
            $filename = "RSVP_sample"; 
        }
        
        /*******YOU DO NOT NEED TO EDIT ANYTHING BELOW THIS LINE*******/    
        //create MySQL connection   
        $sql = "Select * from $DB_TBLName";
        $Connect = @mysql_connect($DB_Server, $DB_Username, $DB_Password) or die("Couldn't connect to MySQL:<br>" . mysql_error() . "<br>" . mysql_errno());
        //select database   
        $Db = @mysql_select_db($DB_DBName, $Connect) or die("Couldn't select database:<br>" . mysql_error(). "<br>" . mysql_errno());   
        //execute query 
        $result = @mysql_query($sql,$Connect) or die("Couldn't execute query:<br>" . mysql_error(). "<br>" . mysql_errno());    
        $file_ending = "xls";
        //header info for browser
        header("Content-Type: application/xls");    
        header("Content-Disposition: attachment; filename=$filename.xls");  
        header("Pragma: no-cache"); 
        header("Expires: 0");
        /*******Start of Formatting for Excel*******/   
        //define separator (defines columns in excel & tabs in word)
        $sep = "\t"; //tabbed character
        //start of printing column names as names of MySQL fields
        for ($i = 0; $i < mysql_num_fields($result); $i++) {
        echo mysql_field_name($result,$i) . "\t";
        }
        print("\n");    
        //end of printing column names  
        //start while loop to get data
            while($row = mysql_fetch_row($result))
            {
                $schema_insert = "";
                for($j=0; $j<mysql_num_fields($result);$j++)
                {
                    if(!isset($row[$j]))
                        $schema_insert .= "NULL".$sep;
                    elseif ($row[$j] != "")
                        $schema_insert .= "$row[$j]".$sep;
                    else
                        $schema_insert .= "".$sep;
                }
                $schema_insert = str_replace($sep."$", "", $schema_insert);
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
    public function sampleExcel(){
        $this->exportExcel(true);
    }    
}
?>