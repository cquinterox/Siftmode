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
        $this->app_show_errors = true;
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
    
    public function InsertFeed($user_id, $category_id, $feed_url, $feed_name, $feed_description = "") {
        if (is_int($category_id) && is_int($user_id)) {
            if (strlen(trim($feed_name)) > 0) {
                
                $feed_url = $this->db_assistant->sanitize($feed_url, true);
                $feed_name = $this->db_assistant->sanitize($feed_name, true);
                $feed_description = $this->db_assistant->sanitize($feed_description, true);
                
                $sql = "INSERT INTO `SIFTMODE`.`FEEDS` (`USER_ID`,`CATEGORY_ID`, `FEED_URL`, `NAME`, `DESCRIPTION`, `CREATED_ON`) 
                        SELECT * FROM (SELECT {$user_id}, {$category_id},'{$feed_url}','{$feed_name}','{$feed_description}', UTC_TIMESTAMP()) AS tmp 
                        WHERE NOT EXISTS (SELECT * FROM `SIFTMODE`.`FEEDS` WHERE `USER_ID`= {$user_id} AND `CATEGORY_ID` = {$category_id} AND (`FEED_URL` = '{$feed_url}' OR `NAME` = '{$feed_name}')) LIMIT 1;";
                        
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
                
                $sql = "INSERT INTO `siftmode`.`categories` (`USER_ID`, `CATEGORY_NAME`, `COMMON_WORDS`, `CREATED_ON`) VALUES ({$user_id}, '{$category_name}', '{$common_words_string}', UTC_TIMESTAMP());";
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
    
    public function ProcessPostText($input_string, $int_min_letters) { // returns a word array
            // lowercase text, remove links, usernames
            $text = strtolower($input_string);
            // remove hyperlinks
            $text = preg_replace("/\b(([\w-]+:\/\/?|www[.])[^\s()<>]+(?:\([\w\d]+\)|([^[:punct:]\s]|\/)))/m", ' ', $text);
            // strips away &'s and such
            $text = html_entity_decode($text);
            // remove commas (helps with larger numbers)
            $text = preg_replace("/(,)/", '', $text);
            // remove non-words except hash tags, twitter names etc
            $text = preg_replace("/([^\w+-])/", ' ', $text);
            // remove trailing commas that could be left over
            // Sanitize finally (just in case)
            $text = $this->db_assistant->sanitize($text, true);
            // match only words X letters long or greater
            preg_match_all("/\w{" . $int_min_letters . ",}/m", $text . ' ', $tmp_array, PREG_PATTERN_ORDER);
            // turn resulting array into an array of unique words
            $tmp_array[0] = array_unique($tmp_array[0]);
            return $tmp_array[0];
    }
    
    public function ProcessAndInsertPost($feed_id, $entry_array) {
        
        if (is_int($feed_id)) {
            if ($entry_array != null ) {

                $entry_link = $this->db_assistant->sanitize($entry_array->link, true);
                $entry_published = strtotime($this->db_assistant->sanitize($entry_array->pubDate, true)); // Convert to UCT Integer
                $entry_published = gmdate("Y-m-d H:i:s", $entry_published); // Convert to UCT Timestamp
                
                // Don't sanitize yet as it will add unecessary numbers to the text
                $entry_title = $entry_array->title;
                $entry_description = $entry_array->description;
                
                // Strip content of data we can't really analyze (for now)
                $entry_headline_array_string = implode(',', $this->ProcessPostText($entry_title, 3));
                $entry_summary_array_string = implode(',', $this->ProcessPostText($entry_description, 3));
                
                // Now lets strip excess characters off before saving
                $entry_title = $this->db_assistant->sanitize($entry_title, true);
                $entry_description = $this->db_assistant->sanitize($entry_description, true);
                
                
                $sql = "INSERT INTO `SIFTMODE`.`FEEDS_DATA` (`FEED_ID`, `POST_LINK`, `PUBLISHED_ON`, `POST_HEADLINE`, `POST_HEADLINE_ARRAY`, `POST_SUMMARY`, `POST_SUMMARY_ARRAY`, `CREATED_ON`) 
                        SELECT * FROM (SELECT {$feed_id}, '{$entry_link}', '{$entry_published}', '{$entry_title}', '{$entry_headline_array_string}', '{$entry_description}', '{$entry_summary_array_string}', UTC_TIMESTAMP()) AS tmp 
                        WHERE NOT EXISTS (SELECT * FROM `SIFTMODE`.`FEEDS_DATA` WHERE `FEED_ID`= {$feed_id} AND `PUBLISHED_ON` = '{$entry_published}') LIMIT 1;";    
                
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
            $last_feed_stored = $rows[0];
            
            if ($last_feed_stored == null) {
                // If this is a new entry, only fetch today's posts
                $todays_date = date("Y-m-d 00:00:00");
                $last_feed_stored = strtotime('-1 day', strtotime($todays_date)); // If nothing is stored, get the last day's feeds.
            } 
            
            // Fetch posts
            $content = file_get_contents($feed_url);
            $x = new SimpleXmlElement($content);
            
            foreach($x->channel->item as $entry) {
                // Save post if greater than the latest one on the database.
                $entry_published = strtotime($this->db_assistant->sanitize($entry->pubDate, true));
                if ($entry_published > $last_feed_stored) {
                    $this->ProcessAndInsertPost($feed_id, $entry);
                }
            }
        }
    }

}

?>

// TODO
1. Option for deleting account. Delete all of the users feeds, their cats, and their summaries.
2. Stop duplicates on other tables.
3. 


