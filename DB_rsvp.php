<?php

require_once ("DB_tables.php");

class RSVP extends Table {

    /**
     * create: create new RSVP table named rsvp[$eventID] in the database
     * @return bool true if table created / false if table not created
     * @throws Exception "RSVP create: Error adding RSVP$eventID to Database"
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
                Ride BOOLEAN DEFAULT FALSE,
                Messages INT DEFAULT 0
                ) DEFAULT CHARACTER SET utf8");

        if (!$result) {
            throw new Exception("RSVP create: Error adding RSVP$eventID to Database");
        }
        return true;
    }

    /**
     * destroy:  delete RSVP table from database ($rsvp[eventID])
     * @return bool true if rsvp[$eventID] table deleted / false if table wasn't
     * @throws Exception "RSVP destroy: Error deleting RSVP$eventID table from Database"
     */
    public function destroy() {
        // check that rsvp is initialised

        $eventID = $this->eventID;
        $result = DB::query("DROP TABLE IF EXISTS rsvp$eventID");

        if (!$result) {
            throw new Exception("RSVP destroy: Error deleting RSVP$eventID table from Database");
        }
        return true;
    }

    /**
     * get:  get RSVP table for specific event
     * @return RSVP table
     */
    public function get() {
        $eventID = $this->eventID;
        $result = DB::select("SELECT * FROM rsvp$eventID");
        return $result;
    }
    
    /**
     * getByPhone:  get RSVP table for specific event by phone number (one row)
     * @param string $Phone : the phone number of the guest
     * @return string row of specific guest (specified by phone number)
     */
    public function getByPhone($Phone) {
        $eventID = $this->eventID;
        
        $phone = DB::quote($Phone);
        $result = DB::select("SELECT * FROM rsvp$eventID WHERE Phone=$phone");
        return $result;
    }

    /**
     * getByGroups:  get RSVP table for specific event by Groups
     * @param string $Groups : groups separated by a comma
     * @return RSVP table of guests from all the groups combined / false if group is empty
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
        
        while (isset($array[$i])){
            $arrayI = DB::quote($array[$i]);
            $query = $query . " OR Groups=$arrayI";
            $i++;
        }
        
        $result = DB::select($query);
        return $result;
    }

    /**
     * update:  update rsvp[$eventID] table in database
     * @param string $colName : column which value should be updated in
     * @param string $id : id of row to be updated
     * @param $value : value to be inserted to the colName column
     * @return bool true if table updated / false if table not updated
     */
    public function update($colName, $id, $value) {
        return Table::updateTable('rsvp', $colName, $id, $value);
    }

    /**
     * add:  add row to rsvp[$eventID] table in database
     * @param string $Name : guest name
     * @param string $SurName : guest surname
     * @param int $Invitees : the number of people invited
     * @param string $NickName : guest nickname (default null)
     * @param string $Phone : guest phone (default null)
     * @param string $Email : guest email (default null)
     * @param string $Groups : group in which the guest is categorised (default null)
     * @param int $RSVP : amount of guests coming (default 0)
     * @param int $Uncertin : amount of people not sure if coming
     * @param bool|int $Ride : if the guest ordered a ride or not (default false)
     * @return int insert id if added
     * @throws Exception "RSVP add: Email $email already in RSVP table"
     * @throws Exception "RSVP add: Phone $phone already in RSVP table"
     * @throws Exception "RSVP add: Error adding guest $name $surName to RSVP$eventID table"
     */
    public function add($Name, $SurName, $Invitees, $NickName = 'NULL', $Phone = 'NULL', $Email = 'NULL', $Groups = 'NULL', $RSVP = 0, $Uncertin = 0,$Ride = 0) {       //TODO: can't add phone +972...

        // Make strings query safe
        $name = DB::quote($Name);
        $surName = DB::quote($SurName);
        $nickName = DB::quote($NickName);
        $phone = DB::quote($Phone);
        $email = DB::quote($Email);
        $groups = DB::quote($Groups);
        $rsvp = DB::quote($RSVP);
        $uncertin = DB::quote($Uncertin);
        $ride = DB::quote($Ride);

        $eventID = $this->eventID;

        //FIXME: WHY? echo "name: $name; surname: $surName; nickName: $nickName; phone: $phone; email: $email; groups: $groups; rsvp: $rsvp; uncertin: $uncertin; ride: $ride";

        //check that phone and email are not already in rsvp table
        if (NULL !== $email and DB::select("SELECT * FROM rsvp$eventID WHERE Email=$email")) {
            throw new Exception("RSVP add: Email $email already in RSVP table");
        }
        if (NULL !== $phone and DB::select("SELECT * FROM rsvp$eventID WHERE Phone=$phone")) {
            throw new Exception("RSVP add: Phone $phone already in RSVP table");
        }

        //insert guest to RSVP table
        $result = DB::query("INSERT INTO rsvp$eventID (Name, Surname, Nickname, Invitees, Phone, Email, Groups, RSVP, Uncertin, Ride) VALUES
                    ($name, $surName, $nickName, $Invitees, $phone, $email, $groups, $rsvp, $uncertin, $ride)");

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
        $this->destroy();
        // create new (empty) rsvp[eventID] table
        $this->create();
        return $this->importPartExcel($excel);
    }

    /**
     * importPartExcel:  import RSVP table from excel file (deleting previous rsvp table)
     * @param $excel : excel file
     * @return int number of errors accuring due to Phone/Email validation
     * @throws Exception "שגיאה: טעינת המידע לשרתינו נחלה כשלון קולוסלי"
     */
    public function importPartExcel($excel) {
        // open excel file
        $file = fopen($excel, "r");
        $count = 0;
        $errors = 0;
        $eventID = $this->eventID;

        // insert data to relevant rsvp table
        while (($empData = fgetcsv($file, 10000, ",")) !== false) {
            $count++;
            if ($count > 1) {  // discard title

                $name = DB::quote($empData[0]);
                $surName = DB::quote($empData[1]);
                $nickName = DB::quote($empData[2]);
                $invitees = (int)$empData[3];
                $phone = DB::quote($empData[4]);
                $email = DB::quote($empData[5]);
                $group = DB::quote($empData[6]);
                $rsvp = (int)$empData[7];
                $uncertin = (int)$empData[8];
                $ride =(int)$empData[9];

                // validate phone/email
                if (!validatePhone($phone) or !validateEmail($email)){
                    $errors++;
                    continue;
                }

                // validate phone/email are not already in rsvp list
                $sql = DB::query("SELECT * FROM rsvp$eventID WHERE Phone=$phone OR Email=$email");
                if ($sql){
                    $errors++;
                    continue;
                }

                $result = DB::query("INSERT INTO rsvp$eventID (Name, Surname, Nickname, Invitees, Phone, Email, Groups, RSVP, Uncertin, Ride) VALUES
                            ($name, $surName, $nickName, $invitees, $phone, $email, $group, $rsvp, $uncertin, $ride)");
                if (!$result) {
                    throw new Exception("שגיאה: טעינת המידע לשרתינו נחלה כשלון קולוסלי");
                }
            }
        }
        return $errors;
    }

    /**     TODO:: update
     * updateFromRaw:  import RSVP table from excel file (deleting previous rsvp table)
     * @param table $rawData : array[i]['Phone'/'Message'/'Recived'/'RSVP'/'Uncertin'/'Ride']
     * @return array[i]['Name'/'Surname'/'Email'/'Groups'/'Phone'/'Message'/'Recived'/'RSVP'/'Uncertin'/'Ride']
     * @throws Exception "שגיאה: משהו מוזר קרה, אנא נסה שנית."
     * @throws Exception "שגיאה: לא ניתן לעדכן את המידע המועבר מההודעות לטבלת המוזמנים. אנא עדכן את המידע באופן ידני."
     * @throws Exception "שגיאה: אין אפשרות לעדכן את המידע המועבר מההודעות לטבלת המוזמנים. אנא עדכן את המידע באופן ידני."
     */
    public function updateFromRaw($rawData) {

        // check valid param
        if (!$rawData) {
            throw new Exception("שגיאה: משהו מוזר קרה, אנא נסה שנית.");
        }

        $eventID = $this->eventID;
        $rsvpData = array();

        // update data in rsvp table and in return value
        foreach ($rawData as $raw) {
            $phone = DB::quote($raw['Phone']);
            $rsvp = DB::quote($raw['RSVP']);
            $ride = DB::quote($raw['Ride']);
            $uncertin = DB::quote($raw['Uncertin']);

            // get needed information from RSVP table
            $data = DB::select("SELECT * FROM rsvp$eventID WHERE Phone=$phone");
            // if message is not from guest in rsvp table
            if (!$data) {
                continue;
            }

            // update rsvp table if RSVP/Uncertin/Ride == 'NULL'
            DB::query("UPDATE rsvp$eventID SET RSVP=IF($rsvp!='NULL',$rsvp,RSVP), Uncertin=IF(Uncertin=NULL,$uncertin,Uncertin), Ride=IF(Ride=0,$ride,Ride), Messages = Messages + 1 WHERE Phone=$phone");
            if (DB::affectedRows() < 0) {
                throw new Exception("שגיאה: אין אפשרות לעדכן את המידע המועבר מההודעות לטבלת המוזמנים. אנא עדכן את המידע באופן ידני.");
            }
            // create data to be inserted back to rawData table (exclude messages from non guests)
            $rsvpData[] = [
                'Name' => $data[0]['Name'],
                'Surname' => $data[0]['Surname'],
                'Email' => $data[0]['Email'],
                'Groups' => $data[0]['Groups'],
                'Phone' => $raw['Phone'],
                'Message' => $raw['Message'],
                'Received' => $raw['Received'],
                'RSVP' => $raw['RSVP'],
                'Uncertin' => $raw['Uncertin'],
                'Ride' => $raw['Ride']
            ];
        }
        return $rsvpData;
    }
}

?>