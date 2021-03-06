<?php

require_once ("DB_tables.php");
require_once ("common.php");


class rawData extends Table {

    /**
     * create: create new rawData table named rawData[$eventID] in the database 
     * @return bool true if table created / false if table not created
     * @throws Exception "Rawdata create: Error adding RawData$eventID to Database"
     */
    public function create() {

        $eventID =$this->eventID;

        $result = DB::query("CREATE TABLE IF NOT EXISTS rawData$eventID ( 
                ID INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
	            Name VARCHAR(50) NOT NULL,
	            Surname VARCHAR(50) NOT NULL,
	            Phone VARCHAR(12) DEFAULT NULL,
	            Email VARCHAR(50) DEFAULT NULL,
	            Groups VARCHAR(50) DEFAULT NULL,
	            Message TEXT NOT NULL,
	            RSVP INT(3) DEFAULT NULL,
	            Uncertin int(3) DEFAULT NULL,
	            Ride BOOLEAN DEFAULT FALSE,
	            Received datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
	            Complex BOOLEAN DEFAULT FALSE
                ) DEFAULT CHARACTER SET utf8;");

        if (!$result) {
            throw new Exception("Rawdata create: Error adding RawData$eventID to Database");
        }
        return true;
    }

    /**
     * destroy:  delete rawData table from database ($messages[eventID])
     * @return bool true if messages[$eventID] table deleted / false if table wasn't
     * @throws Exception "RawData destroy: Error deleting RawData$eventID table from Database"
     */
    public function destroy() {

        $eventID = $this->eventID;
        $result = DB::query("DROP TABLE IF EXISTS rawData$eventID");

        if (!$result) {
            throw new Exception("RawData destroy: Error deleting RawData$eventID table from Database");
        }
        return true;
    }

    /**
     * getMessages:  get rawData table for specific event
     * @return array rawData[$eventID] table or false if no rawData[$eventID] exists
     * @throws Exception "שגיאה: לא קיימת הרשאת גישה לצפייה בטבלה זו. אנא פנה למנהל האירוע ובקש הרשאה מתאימה."
     */
    public function get() {
        // get by root/edit/groups
        $result = $this->getByGroups('rawData',$this->permissions);

        if(!$result){
            throw new Exception("שגיאה: לא קיימת הרשאת גישה לצפייה בטבלה זו. אנא פנה למנהל האירוע ובקש הרשאה מתאימה.");
        }
        return $result;
    }

    /**
     * update:  update rawData[$eventID] table in database
     * @param string $colName : column which value should be updated in
     * @param string $id : id of row to be updated
     * @param $value : value to be inserted to the colName column
     * @return bool true if table updated / false if table not updated
     */
    public function update($colName, $id, $value) {
        return $this->updateTable('rawData', $colName, $id, $value);
    }

    /**
     * updateTable: override updateTable function in Table to fit permission needs
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

        // if root/edit permissios
        if ($this->isPermission('root,edit')){
            DB::query("UPDATE $tableType"."$eventID SET $colName = $value WHERE id = $id");

            if (DB::affectedRows() < 0) {
                throw new Exception("שגיאה: אין אפשרות לעדכן את הערך ".$value." בטבלה.");
            }
            return true;
        }

        // update according to groups in permission
        $query = $this->orGroups($this->permissions);

        DB::query("UPDATE $tableType"."$eventID SET $colName = $value WHERE id = $id AND Groups=$query");

        if (DB::affectedRows() < 0) {
            throw new Exception("שגיאה: אין אפשרות לעדכן את הערך ".$value." בטבלה.");
        }
        return true;
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
 * @param int $Received
 * @return int insert id if added
 * @throws Exception "RawData add: Error adding rawData from $name $surName to RawData$eventID table"
 */
    public function add($Name, $SurName, $Message, $Phone = 'NULL', $Email = 'NULL', $Groups = 'NULL', $RSVP = 0, $Uncertin = 0, $Ride = false, $Received = 0) {
        if (!$this->isPermission('root,edit')){
            throw new Exception("שגיאה: לא קיימת הרשאת גישה להוספה לטבלה זו. אנא פנה למנהל האירוע ובקש הרשאה מתאימה.");
        }

        // Make strings query safe
        $name = DB::quote($Name);
        $surName = DB::quote($SurName);
        $message = DB::quote($Message);
        $phone = validatePhone($Phone);
        $email = validateEmail($Email);
        $groups = DB::quote($Groups);
        $received = DB::quote($Received);

        $eventID = $this->eventID;

        $result = DB::query("INSERT INTO rawData$eventID (Name, Surname, Phone, Email, Groups, Message, RSVP, Ride, Received) VALUES
                    ($name, $surName, $phone, $email, $groups, $RSVP, $Ride, $message, $received)");

        if (!$result) {
            throw new Exception("RawData add: Error adding rawData from $name $surName to RawData$eventID table");
        }
        return DB::insertID();
    }

    /**
     * insertMessage:  add row to rawData[$eventID] table in database
     * @param array $RsvpData : ['Name'/'Surname'/'Email'/'Groups'/'RSVP'/'Uncertin'/'Ride'/'Complex']
     * @param string $Message : message to insert to raw data
     * @param string $Phone : phone number as received from message
     * @return bool true if data inserted to rawData, false otherwise
     */
    public function insertMessage($RsvpData, $Message, $Phone) {

        // Make strings query safe
        $name = DB::quote($RsvpData[0]['Name']);
        $surName = DB::quote($RsvpData[0]['Surname']);
        $message = DB::quote($Message);
        $phone = validatePhone($Phone);
        $email = validateEmail($RsvpData[0]['Email']);
        $groups = DB::quote($RsvpData[0]['Groups']);
        $rsvp = DB::quote($RsvpData[0]['RSVP']);
        $uncertin = DB::quote($RsvpData[0]['Uncertin']);
        $ride = DB::quote($RsvpData[0]['Ride']);
        $complex = DB::quote($RsvpData[0]['Complex']);

        $eventID = $this->eventID;

        // insert to rawData table
        $result = DB::query("INSERT INTO rawData$eventID (Name, Surname, Phone, Email, Groups, Message, RSVP, Uncertin, Ride, Complex) VALUES
                    ($name, $surName, $phone, $email, $groups, $message, $rsvp, $uncertin, $ride, $complex)");
        if (!$result) return false;
        return true;
    }

    /**
     * delete:  delete row in table rsvp[$eventID] at database
     * @param string $id : table id column (table id not user id)
     * @return bool true if row deleted / false otherwise
     * @throws Exception "שגיאה: לא קיימת הרשאת גישה להסרת שורות מטבלה זו. אנא פנה למנהל האירוע ובקש הרשאה מתאימה."
     */
    public function delete($id) {
        if (!$this->isPermission('root,edit')){
            throw new Exception("שגיאה: לא קיימת הרשאת גישה להסרת שורות מטבלה זו. אנא פנה למנהל האירוע ובקש הרשאה מתאימה.");
        }
        $id = DB::quote($id);

        return Table::deleteFromTable('rawData', $id);
    }
    
    /**
     * getByPhone:  get RawData table for specific event by phone number (one row)
     * @param string $Phone : the phone number of the guest
     * @return row of specific guest (specified by phone number)
     */
    public function getByPhone($Phone) {
        return Table::getByPhones('rawData',$Phone);
    }


    /**
     * getMessages:  get messages from smsGateway and extract relevant data from it
     * @param string $Email : email for smsGateway site
     * @param string $Password : password for smsGateway site
     * @param string $DeviceID : device id from smsGateway site
     * @return Table RawData : array[i]['Phone'/'Message'/'Recived'/'RSVP'/''Uncertin/'Ride']
     */
    public function getMessages($Email, $Password, $DeviceID){
        //connect to smsGateway
        $smsGateway = new SmsGateway($Email,$Password);

        //get latest inserted rawData date and time
        $eventID = $this->eventID;
        $latestRaw = DB::select("SELECT * FROM rawData$eventID ORDER BY ID DESC LIMIT 1");

        // mark latest DateTime entry to rsvp table
        ($latestRaw) ? $latestRawTime = strtotime($latestRaw[0]['Received']) : $latestRawTime = strtotime(0);

        // get data from pages
        $page = 1;
        $done = 0;
        $result = $smsGateway->getMessages($page);

        $rawData = array();
        while ($result['response']['success'] and !$done) {                // TODO: validate blank page

            foreach ($result['response']['result'] as $Message) {
                // continue if not received message (only received messages) or if not this deviceID
                if($Message['status']!='received' or $Message['device_id']!=$DeviceID) continue;

                // break if message dateTime<=$lastRawTime
                if($Message['received_at']<=$latestRawTime){
                    $done = 1;
                    break;
                }

                $extracted = $this->extract($Message['message']);

                $rawData[] = [
                    'Phone' => validatePhone($Message['contact']['number']),
                    'Message' => $Message['message'],
                    'Received' => UNIX2GER($Message['received_at']),
                    'RSVP' => $extracted['RSVP'],
                    'Uncertin' => $extracted['Uncertin'],
                    'Ride' => $extracted['Ride']
                ];
            }
//            $page++;                                          //TODO : uncomment, delete break once page issue is resolved
//            $result = $smsGateway->getMessages($page);
            break;
        }
        return $rawData;
    }

    /**
     * insert: insert updated raw data to rawData table
     * @param table array[i]['Name'/'Surname'/'Email'/'Groups'/'Phone'/'Message'/'Recived'/'RSVP'/'Uncertin'/'Ride']
     * @return int latest insert id
     * @throws Exception "שגיאה: משהו מוזר קרה, אנא נסה שנית."
     */
    public function insert($rsvpData){

        // check valid param
        if (!$rsvpData) {
            throw new Exception("שגיאה: משהו מוזר קרה, אנא נסה שנית.");
        }

        $reverseData = array_reverse($rsvpData);

        // insert data to rawData table
        foreach ($reverseData as $data) {
            $insertID = $this->add($reverseData['Name'], $reverseData['SurName'], $reverseData['Message'],
                $reverseData['Phone'], $reverseData['Email'], $reverseData['Groups'], $reverseData['RSVP'],
                $reverseData['Ride'], $reverseData['Received']);
        }
        return $insertID;

    }

    /**
     * insertBatch: insert updated raw data to rawData table as batch (one MySQL command)
     * @param table array[i]['Name'/'Surname'/'Email'/'Groups'/'Phone'/'Message'/'Recived'/'RSVP'/'Uncertin'/'Ride']
     * @return int latest insert id
     * @throws Exception "שגיאה: משהו מוזר קרה, אנא נסה שנית."
     * @throws Exception "שגיאה: לא ניתן להכניס את הרשומות לטבלת ההודעות."
     */
    public function insertBatch($rsvpData){

        // check valid param
        if (!$rsvpData) {
            throw new Exception("שגיאה: משהו מוזר קרה, אנא נסה שנית.");
        }

        $eventID = $this->eventID;
        // prepare values string for insert
        $i = 0;
        $values = '';
        // prepare values for insert
        while (isset($rsvpData[$i])){
            $name = DB::quoteNull($rsvpData[$i]['Name']);
            $surname = DB::quoteNull($rsvpData[$i]['Surname']);
            $message = DB::quoteNull($rsvpData[$i]['Message']);
            $phone = validatePhone($rsvpData[$i]['Phone']);
            $email = validateEmail($rsvpData[$i]['Email']);
            $groups = groups::validateGroup($rsvpData[$i]['Groups']);
            $rsvp = DB::quoteNull($rsvpData[$i]['RSVP']);
            $ride = DB::quoteNull($rsvpData[$i]['Ride']);
            $received = DB::quoteNull($rsvpData[$i]['Received']);

            if($i==0){          // prepare 1st value
                $values = "($name, $surname, $phone, $email, $groups, $rsvp, $ride, $message, $received)";
            }
            else {              // prepare other values
                $values = $values . ", ($name, $surname, $phone, $email, $groups, $rsvp, $ride, $message, $received)";
            }
            $i++;
        }

        // insert data to rawData table as batch
        $result = DB::query("INSERT INTO RawData$eventID (Name, Surname, Phone, Email, Groups, RSVP, Ride, Message, Received) VALUES $values");
        if (!$result) {
            throw new Exception("שגיאה: לא ניתן להכניס את הרשומות לטבלת ההודעות.");
        }
        return $i;
    }

    /**
     * extract: extract RSVP, Uncertin and Ride numbers out of string
     * @param string $String : message to extract rsvp from
     * @return array['RSVP'/'Uncertin/'Ride']
     */
    private function extract($String) {
        $string = DB::quote($String);
        $numbers = [];

        $updatedString = dictionary($string);
        preg_match_all('!\d+!', $updatedString, $matches);
        (isset($matches[0][0])) ? $numbers['RSVP']=$matches[0][0] : $numbers['RSVP']='NULL';  // TODO: this returns only the 1st number in the string after dict
        (isset($matches[0][1])) ? $numbers['Uncertin']=$matches[0][1] : $numbers['Uncertin']='NULL';  // TODO: this returns only the 2nd number in the string after dict
        (isset($matches[0][2])) ? $numbers['Ride']=$matches[0][2] : $numbers['Ride']=0;  // TODO: this returns only the 3rd number in the string after dict
        ($numbers['Ride'] !=0 ) ? $numbers['Ride']=1 : $numbers['Ride']=0;

        return $numbers;
    }
}

?>