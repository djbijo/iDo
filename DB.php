<?php

require_once('config.php');

class Db {

    // Database link
    protected static $link;

    /*
     * connect: Connect to the database
     * @return bool: Failure=false / Success=mysqli object instance
     */

    public function connect() {

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
            $output = 'Error connecting to iDO Database: ' . $this->error();
            include 'output.html.php';
            return false;
        }

        // link successful
        return self::$link;
    }

    /*
     * query: Query the database
     * $query string: The query string
     * @return mixed The result of the mysqli query() function
     */

    public function query($query) {
        // Connect to the database
        $link = $this->connect();

        // Query the database
        $result = $link->query($query);

        return $result;
    }

    /*
     * select: Fetch rows from database
     *
     * @query The query string
     * @return bool: Failure=False / Success=array database rows
     */

    public function select($query) {
        $rows = array();
        $result = $this->query($query);
        if ($result === false) {
            return false;
        }
        while ($row = $result->fetch_assoc()) {
            $rows[] = $row;
        }
        return $rows;
    }

    /*
     * error: fetch the last error from  database
     * @return string Database error message
     */

    public function error() {
        $link = $this->connect();
        return $link->error;
    }

    /*
     * Quote and escape value for use in a database query
     *
     * @param string $value The value to be quoted and escaped
     * @return string The quoted and escaped string
     */

    public function quote($value) {
        $link = $this->connect();
        return "'" . $link->real_escape_string($value) . "'";
    }

}
