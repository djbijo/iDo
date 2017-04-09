<?php

require_once('config.php');

class DB {
    
    private static $initialized = false;
    // Database link
    protected static $link;

    /**
     * connect: Connect to the database
     * @return bool: Failure=false / Success=mysqli object instance
     */

    static function connect() {
        
        if(self::$initialized) return;

        $config = array(
            "db_name" => "iDODB",
            "db_user" => "root",
            "db_password" => "",
            "db_host" => "localhost"
        );

        // Try and connect to the database
        if (!isset(self::$link)) {
            self::$link = new mysqli($config['db_host'], $config['db_user'], $config['db_password'], $config['db_name']);
        }

        // link  not successful
        if (self::$link === false) {
            echo 'Error connecting to iDO Database: ' . DB::error();
            include 'output.html.php';
            return false;
        }

        // link successful
        //self::DBprep();
        self::$initialized = true;
        return self::$link;
    }

    /**
     * query: Query the database
     * @param string $query string: The query string
     * @return mixed The result of the mysqli query() function
     */

    static function query($query) {
        // Connect to the database
        self::connect();
        // Query the database
        $result = self::$link->query($query);

        return $result;
    }

    /**
     * select: Fetch rows from database
     * @param string $query The query string
     * @return bool: Failure=False / Success=array database rows
     */

    static function select($query) {
        $rows = array();
        $result = self::query($query);
        if ($result === false) {
            throw new Exception("select function failed");
        }
        while ($row = $result->fetch_assoc()) {
            $rows[] = $row;
        }
        return $rows;
    }

    /**
     * error: fetch the last error from  database
     * @return string Database error message
     */

    private static function error() {
        return self::$link->error;
    }

    /**
     * quote: Quote and escape value for use in a database query
     * @param string $value The value to be quoted and escaped
     * @return string The quoted and escaped string
     */
    static function quote($value) {
        self::connect();
        if($value === NULL) return NULL;
        if($value === 'NULL') return 'NULL';
        return "'" . self::$link->real_escape_string(strip_tags($value)) . "'";
    }
    
    /**
     * escapeString: escape value for use in a database query
     * @param string $value The value to be quoted and escaped
     * @return string The quoted and escaped string
     */
    static function escapeString($value) {
        self::connect();
        return self::$link->real_escape_string(strip_tags($value));
    }
    
    /**
     * affectedRows: return the number of affected rows in latest query
     * @return int # of affected rows in latest query
     */
    static function affectedRows() {
        self::connect();
        return mysqli_affected_rows(self::$link) ;
    }
    
    /**
     * insertID: return the insert id of latest query
     * @return int insert id of latest query
     */
    static function insertID() {
        self::connect();
        return mysqli_insert_id(self::$link) ;
    }
    
    static function getLink() {
        self::connect();
        if (isset(self::$link)) {
            return self::$link;
        }
        return false;
    }
    
    private static function DBprep(){
        // database preperation - use utf8 for hebrew
        self::query("SET character_set_server=utf8");
        self::query("SET character_set_results=utf8");
        self::query("SET character_set_database=utf8");
        self::query("SET character_set_connection=utf8");
        self::query("SET character_set_client=utf8");
        self::query("SET names 'utf8'");
    }
}
