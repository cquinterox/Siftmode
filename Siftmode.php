<?php
include_once("DBAssistant.php");
/**
 * SiftmodeFunctions short summary.
 *
 * SiftmodeFunctions description.
 *
 * @version 1.0
 * @author Cesar Quinteros
 */
Class Siftmode {
    
    private $db_assistant;
    private $app_error_log;
    private $app_show_errors;
    
    public function __construct() {
        // Instantiate our database helper class
        $this->db_assistant = new DBAssistant();
        $this->app_error_log = getcwd() . '/Logs/app.log';
        $this->app_show_errors = false;
    }
    
    private function getLocalTime() {
        // Simple function that gets the current time for us
        $zone = new DateTimeZone('America/New_York');
        $time = new DateTime(date("Y-m-d g:i:sA", time()));
        $time->setTimezone($zone);
        return $time->format('Y-m-d g:i:sA');
    }
    
    private function applog($message) {   
        // Simple function that writes to the application log
        $error_message = "Application log '" . $this->app_error_log . "' can't be created or is not writable.";
        if (file_exists($this->app_error_log) && is_writable($this->app_error_log)) { 
            $message = '(' . $this->getLocalTime() . ') Msg: ' . $message . "\n";
            error_log($message, 3, $this->app_error_log);
        } else {
            if(!$fileHandle = fopen($this->app_error_log, 'w')) {
                if ($this->app_show_errors) {
                    echo $error_message;
                }  
            } else {
                fclose($fileHandle);
            }
        }
    }
    
    public function InsertFeed($category_id, $feed_url, $feed_name, $feed_description = "") {
        if (is_int($category_id)) {
            if (strlen(trim($feed_name)) > 0) {
                
                $feed_url = $this->db_assistant->sanitize($feed_url, true);
                $feed_name = $this->db_assistant->sanitize($feed_name, true);
                $feed_description = $this->db_assistant->sanitize($feed_description, true);
                
                $sql = "INSERT INTO `siftmode`.`feeds` (`CATEGORY_ID`, `FEED_URL`, `NAME`, `DESCRIPTION`, `CREATED_ON`) VALUES ({$category_id},'{$feed_url}','{$feed_name}','{$feed_description}', CURRENT_TIMESTAMP)";
                if ($this->db_assistant->query($sql) > 0) {
                    $this->applog("Failed to insert feed into `feeds` table. Feed Info: URL '{$feed_url}', NAME '{$feed_name}', DESCRIPTION '{$feed_description}'");
                }
            } else {
                $this->applog("Failed to insert feed into `feeds` table. The feed name was blank.");
            }
        }
    }
   
    public function DeleteFeed($feed_id) {
        if (is_int($feed_id)) {             
            $sql = "DELETE FROM `siftmode`.`feeds` WHERE `ID`= {$feed_id}";
            if ($this->db_assistant->query($sql) > 0) {
                $this->applog("Failed to delete feed from `feeds` table. Feed Info: ID '{$feed_id}'");
            }
        }
    }
    
    public function InsertCategory($user_id, $category_name, $common_words_array_or_string) {
        if (is_int($user_id)) {
            if (strlen(trim($category_name)) > 0) {
                
                $category_name = $this->db_assistant->sanitize($category_name, true);
                
                // Convert common words array into a string if necessary
                if (is_array($common_words_array_or_string)) {
                    $common_words_string = implode(",", $common_words_array_or_string);
                } else {
                    $common_words_string = $common_words_array_or_string;
                }
                
                $common_words_string = $this->db_assistant->sanitize($common_words_string, true);
                
                $sql = "INSERT INTO `siftmode`.`categories` (`USER_ID`, `CATEGORY_NAME`, `COMMON_WORDS`, `CREATED_ON`) VALUES ({$user_id}, '{$category_name}', '{$common_words_string}', CURRENT_TIMESTAMP);";
                if ($this->db_assistant->query($sql) > 0) {
                    $this->applog("Failed to insert feed category into `categories` table. Category Info: User ID '{$user_id}', CATEGORY NAME '{$category_name}', COMMON WORDS '{$common_words_string}'");
                }
            } else {
                $this->applog("Failed to insert feed category into `categories` table. The category name was blank.");
            }
        }
    }
    
    public function DeleteCategory($category_id) {
        if (is_int($category_id)) {             
            $sql = "DELETE FROM `siftmode`.`categories` WHERE `ID`= {$category_id}";
            if ($this->db_assistant->query($sql) > 0) {
                $this->applog("Failed to delete category from `categories` table. Category Info: ID '{$category_id}'");
            }
        }
    }
    
    public function ProcessAndInsertPost($feed_id, $entry_array) {
        
        if (is_int($feed_id)) {
            if ($entry_array != null ) {
                
                $entry_link = $this->db_assistant->sanitize($entry_array->link, true);
                $entry_title = $this->db_assistant->sanitize($entry_array->title, true);
                $entry_description = $this->db_assistant->sanitize($entry_array->description, true);
                $entry_published = strftime("%Y-%m-%d %H:%M:%S", strtotime($this->db_assistant->sanitize($entry_array->pubDate, true))); // Convert to UCT Timestamp

                $sql = "INSERT INTO `siftmode`.`feeds_data` (`FEED_ID`, `POST_LINK`, `PUBLISHED_ON`, `POST_HEADLINE`, `POST_HEADLINE_ARRAY`, `POST_SUMMARY`, `POST_SUMMARY_ARRAY`, `POST_BODY`, `POST_BODY_ARRAY`, `UPDATED_ON`, `CREATED_ON`) VALUES ({$feed_id}, '{$entry_link}', '{$entry_published}', '{$entry_title}', NULL, '{$entry_description}', NULL, NULL, NULL, NULL, CURRENT_TIMESTAMP);";
                if ($this->db_assistant->query($sql) > 0) {
                    $this->applog("Failed to insert post into `feeds_data` table. Feed Info: URL '{$entry_link}', TITLE '{$entry_title}', DESCRIPTION '{$entry_description}', PUBLISHED '{$entry_published}'");
                }
            } else {
                $this->applog("Failed to insert post into `feeds_data` table. The feed name was blank.");
            }
        }
    }
    
    public function FetchRSS($feed_id) {
        if (is_int($feed_id)) {
            //Get the URL
            $sql = "SELECT `feed_url` FROM `siftmode`.`feeds` WHERE `ID`= {$feed_id}";
            $result = $this->db_assistant->query($sql);
            $rows = mysqli_fetch_array($result);
            $feed_url = $rows[0];
            
            //Get the date of the last post fetched
            $sql = "SELECT `PUBLISHED_ON` FROM `siftmode`.`feeds_data` WHERE `FEED_ID` = {$feed_id} ORDER BY `PUBLISHED_ON` DESC LIMIT 1";
            $result = $this->db_assistant->query($sql);
            $rows = mysqli_fetch_array($result);
            $feed_last_published = $rows[0];
            
            if ($feed_last_published == null) {
                // If this is a new entry, only fetch today's posts
                $todays_date = date("Y-m-d");
                $feed_last_published = strftime("%Y-%m-%d %H:%M:%S", strtotime($todays_date)); 
            } 
            
            // Fetch posts
            $content = file_get_contents($feed_url);
            $x = new SimpleXmlElement($content);
            
            foreach($x->channel->item as $entry) {
                // Save post if greater than the latest one on the database
                $entry_published = strftime("%Y-%m-%d %H:%M:%S", strtotime($this->db_assistant->sanitize($entry->pubDate, true))); // Convert to UCT Timestamp
                if (strtotime($entry_published) > strtotime($feed_last_published)) {
                    $this->ProcessAndInsertPost($feed_id, $entry);
                }
            }
        }
    }

}
$d = new Siftmode();

//Add feed tests
//$d->AddFeed(1,"http://quintero.me/feeds3/", "feed name 3", "feed desc3");
//$d->AddCategory(1,"cesars category", "<>hello, world");
$d->FetchRSS(2);

?>

// TODO
0. Simplify/correct date comparisons if possible.
1. Process text completely before insert.
2. Option for deleting account. Delete all of the users feeds, their cats, and their summaries.

