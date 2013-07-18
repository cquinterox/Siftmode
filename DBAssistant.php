<?php

/*
 * This is a database assistant class made to speeds up
 * the process of using a database. 
 * 
 * By default it logs errors to the apache error log but 
 * can display error messages for debugging by setting
 * $output_errots to true.
 * 
 * Usage: 
 * $assistant = new DbAssistant();
 * $tmp = $assistant->query("SELECT * FROM mytable"); //returns a basic result array
 * $assistant->queryPrint("SELECT * FROM Tweets"); // prints the returned array
 * 
 * v1.1 Cesar Quinteros 2013 // Quinteros.me
 */

Class DBAssistant {
    
    private $mysql_host;
    private $mysql_database;
    private $mysql_user;
    private $mysql_password;
    private $mysql_error_log;
    private $mysql_show_errors;
    
    public function __construct() {
        
        // Settings
        $this->mysql_host = 'localhost';    
        $this->mysql_database = 'siftmode';
        $this->mysql_user = 'root';
        $this->mysql_password = '';
        $this->mysql_error_log = getcwd() . '/Logs/sql.log';
        $this->mysql_show_errors = true;  
        // End Settings
        ($this->mysql_show_errors) ? ini_set('display_errors','On') : ini_set('display_errors','Off');
        
    } 
    
    function sqlog($message) {   
        $error_message = "MySQL error log '$this->mysql_error_log' can't be created or is not writable.";
        if (file_exists($this->mysql_error_log) && is_writable($this->mysql_error_log)) { 
            $message = '(' . $this->errTime(0) . ') Error: ' . $message . "\n";
            error_log($message, 3, $this->mysql_error_log);
        } else {
            if(!$fileHandle = fopen($this->mysql_error_log, 'w')) {
                if ($this->mysql_show_errors) {
                    echo $error_message;
                }  
            } else {
                fclose($fileHandle);
                $this->sqlog($message);
            }
        }
    }
    
    private function errTime($index) {
        $zones = array();
        $zones[] = new DateTimeZone('America/New_York');
        $zones[] = new DateTimeZone('America/Los_Angeles');
        $time = new DateTime(date('F j, Y g:i:s A', time()));
        $time->setTimezone($zones[$index]);
        return $time->format('F j, Y g:i:s A');
    }
    
    public function query($sql) {
        $db_conn = $this->getConnection();
        if (!$result = mysqli_query($db_conn, $sql)) {
            die($this->sqlog(mysqli_errno($db_conn) . ': ' . mysqli_error($db_conn)));
        }
        if (!is_bool($result)) {
            mysqli_close($db_conn);
            return $result;
        } else { 
            return (int)mysqli_affected_rows($db_conn);
        }
    }
    
    public function queryPrint($sql) {
        $result = $this->query($sql);
        if (!is_int($result)) {
            while($row = mysqli_fetch_array($result))
            {
                echo '<pre>';
                print_r($row);
                echo '</pre>';
            }      
        } else {
            echo $result . ' rows affected.';
        }
    } 
    public function sanitize($input, $strip_html_tags_bool) {
        if (is_array($input)) {
            foreach($input as $var => $val) {
                $output[$var] = sanitize($val, $strip_html_tags_bool);
            }
        } else {
            // htmlpsecialchars should be run on output but to avoid the hassle of remembering it's run here before it's saved.
            get_magic_quotes_gpc() ?  $input = stripslashes($input) : null;
            $strip_html_tags_bool ? $input  = strip_tags($input) : null;
            $input  = htmlentities($input, ENT_QUOTES);
            $output = mysqli_real_escape_string($this->getConnection(), $input);
            return $output;
        }
    }
    private function getConnection() {
        $db_conn = mysqli_connect($this->mysql_host, $this->mysql_user, $this->mysql_password, $this->mysql_database);   
        (!$db_conn) ? die($this->sqlog(mysqli_errno($db_conn) . ': ' . mysqli_error($db_conn))) : null;
        return $db_conn;
    }
}
?>
    