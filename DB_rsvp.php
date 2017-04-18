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
                Messages INT DEFAULT 0,
                Complex BOOLEAN DEFAULT NULL
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
     * @throws Exception "שגיאה: לא קיימת הרשאת גישה לצפייה במוזמנים. אנא פנה למנהל האירוע ובקש הרשאה מתאימה."
     */
    public function get() {
        $result = $this->getByGroups('rsvp',$this->permissions);

        if(!$result){
            throw new Exception("שגיאה: לא קיימת הרשאת גישה לצפייה במוזמנים. אנא פנה למנהל האירוע ובקש הרשאה מתאימה.");
        }
        return $result;
    }
    
    /**
     * getByPhone:  get RSVP table for specific event by phone number (one row)
     * @param string $Phone : the phone number of the guest
     * @return string row of specific guest (specified by phone number)
     */
    public function getByPhone($Phone) {
        return Table::getByPhones('rsvp',$Phone);

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
    public function add($Name, $SurName, $Invitees, $NickName = 'NULL', $Phone = 'NULL', $Email = 'NULL', $Groups = 'NULL', $RSVP = 0, $Uncertin = 0,$Ride = 0) {

        if (!$this->isPermission("root,edit,$Groups")){
            throw new Exception("שגיאה: לא קיימת הרשאת גישה להוספה לטבלה זו. אנא פנה למנהל האירוע ובקש הרשאה מתאימה.");
        }

        // Make strings query safe
        $name = DB::quote($Name);
        $surName = DB::quote($SurName);
        $nickName = DB::quote($NickName);
        $phone = validatePhone($Phone);
        $email = validateEmail($Email);
        $groups = DB::quote($Groups);
        $rsvp = DB::quote($RSVP);
        $uncertin = DB::quote($Uncertin);
        $ride = DB::quote($Ride);

        $eventID = $this->eventID;

        //check that phone and email are not already in rsvp table
        if ('NULL' !== $email and DB::select("SELECT * FROM rsvp$eventID WHERE Email=$email")) {
            throw new Exception("RSVP add: Email $email already in RSVP table");
        }
        if ('NULL' !== $phone and DB::select("SELECT * FROM rsvp$eventID WHERE Phone=$phone")) {
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
     * @throws Exception "שגיאה: לא ניתן למצוא את הרשומה הרצויה."
     * @throws Exception "שגיאה: לא קיימת הרשאת גישה למחיקה מטבלה זו. אנא פנה למנהל האירוע ובקש הרשאה מתאימה."
     */
    public function delete($id) {
        $id = DB::quote($id);
        $eventID = $this->eventID;
        $result = DB::select("SELECT FROM rsvp$eventID WHERE ID=$id");
        if(!$result){
            throw new Exception("שגיאה: לא ניתן למצוא את הרשומה הרצויה.");
        }
        $group = $result[0]['Groups'];

        if (!$this->isPermission("root,edit,$group")){
            throw new Exception("שגיאה: לא קיימת הרשאת גישה למחיקה מטבלה זו. אנא פנה למנהל האירוע ובקש הרשאה מתאימה.");
        }
        return Table::deleteFromTable('rsvp', $id);
    }

    /**
     * importFullExcel:  import RSVP table from excel file (deleting previous rsvp table)
     * @param file $excel : excel file
     * @return bool true if excel imported / false if excel not imported
     * @throws Exception "שגיאה: לא קיימת הרשאת גישה להעלאת קובץ מוזמנים. אנא פנה למנהל האירוע ובקש הרשאה מתאימה."
     */
    public function importFullExcel($excel) {
        if (!$this->isPermission("root,edit")){
            throw new Exception("שגיאה: לא קיימת הרשאת גישה להעלאת קובץ מוזמנים. אנא פנה למנהל האירוע ובקש הרשאה מתאימה.");
        }
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
        if (!$this->isPermission("root,edit")){
            throw new Exception("שגיאה: לא קיימת הרשאת גישה להעלאת קובץ מוזמנים. אנא פנה למנהל האירוע ובקש הרשאה מתאימה.");
        }
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
                $phone = validatePhone($empData[4]);
                $email = validateEmail($empData[5]);
                $group = groups::validateGroup($empData[6]);
                $rsvp = (int)$empData[7];
                $uncertin = (int)$empData[8];
                $ride =(int)$empData[9];

                // validate phone/email
                if (!$phone or !$email){
                    $errors++;
                    continue;
                }

                // validate phone/email are not already in rsvp list
                $sql = DB::query("SELECT * FROM rsvp$eventID WHERE Phone=$phone OR Email=$email");
                if ($sql or !isset($phone) or !isset($email) or !isset($group)){
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

    /**
     * updateFromRaw: update rsvp table from messages added from smsGateway through rawData
     * @param table $rawData : array[i]['Phone'/'Message'/'Recived'/'RSVP'/'Uncertin'/'Ride']
     * @return array[i]['Name'/'Surname'/'Email'/'Groups'/'Phone'/'Message'/'Recived'/'RSVP'/'Uncertin'/'Ride']
     * @throws Exception "שגיאה: משהו מוזר קרה, אנא נסה שנית."
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
            $phone = validatePhone($raw['Phone']);
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
                'Phone' => $phone,
                'Message' => $raw['Message'],
                'Received' => $raw['Received'],
                'RSVP' => $raw['RSVP'],
                'Uncertin' => $raw['Uncertin'],
                'Ride' => $raw['Ride']
            ];
        }
        return $rsvpData;
    }

    /**
     * updateFromMessage: update rsvp table from message received from smsGateway
     * @param string $message : the ['message'] from received message
     * @param string $phone : the ['phone'] from received message
     * @return array[i]['Name'/'Surname'/'Email'/'Groups'/'Phone'/'Message'/'RSVP'/'Uncertin'/'Ride']
     */
    public function updateFromMessage($message, $phone) {

        $eventID = $this->eventID;
//        $rsvpData = array();

        // extract data from $message
        $extracted = $this->extract($message);
        $rsvp =  DB::quote($extracted['RSVP']);
        $uncertin = DB::quote($extracted['Uncertin']);
        $ride = DB::quote($extracted['Ride']);
        $complex = DB::quote($extracted['Complex']);

        // get needed information from RSVP table
        $data = DB::select("SELECT * FROM rsvp$eventID WHERE Phone=$phone");
        // if message is not from guest in rsvp table
        if (!$data) return false;
        // update rsvp table if RSVP/Uncertin/Ride == 'NULL'
        DB::query("UPDATE rsvp$eventID SET RSVP=IF($rsvp!='NULL',$rsvp,RSVP), Uncertin=IF($uncertin!='NULL',$uncertin,Uncertin),
                    Ride=IF(Ride=0,$ride,Ride), Messages = Messages + 1, Complex=$complex WHERE Phone=$phone");
        if (DB::affectedRows() < 0) {
            return false;
        }
        // create data to be inserted back to rawData table (exclude messages from non guests)
        $rsvpData[] = [
            'Name' => $data[0]['Name'],
            'Surname' => $data[0]['Surname'],
            'Email' => $data[0]['Email'],
            'Groups' => $data[0]['Groups'],
            'Messages' => $data[0]['Messages']+1,
            'RSVP' => $extracted['RSVP'],
            'Uncertin' => $extracted['Uncertin'],
            'Ride' => $extracted['Ride'],
            'Complex' => $extracted['Complex'],
        ];
        return $rsvpData;
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
        (isset($matches[0][0])) ? $numbers['RSVP']=$matches[0][0] : $numbers['RSVP']='NULL';                            // TODO: this returns only the 1st number in the string after dict
        (isset($matches[0][1])) ? $numbers['Uncertin']=$matches[0][1] : $numbers['Uncertin']='NULL';                    // TODO: this returns only the 2nd number in the string after dict
        (isset($matches[0][2])) ? $numbers['Ride']=$matches[0][2] : $numbers['Ride']=0;                                 // TODO: this returns only the 3rd number in the string after dict
        ($numbers['Ride'] !=0 ) ? $numbers['Ride']=1 : $numbers['Ride']=0;

        (strlen($updatedString)>11) ? $numbers['Complex']=1 : $numbers['Complex']=0;                                    // TODO: this relays on string being longer than 3 seperate dnumbers after dictionary

        return $numbers;
    }
}

?>