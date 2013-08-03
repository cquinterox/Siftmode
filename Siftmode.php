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
            $sql = "DELETE FROM `siftmode`.`feeds` WHERE `ID`= {$feed_id}"; // Sift002
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
    
    public function ProcessPostText($input_string, $int_min_letters) { // returns a word array, IMPORTANT: 4 LETTER MIN FOR MATCH AGAINST
            // lowercase text, remove links, usernames
            $text = strtolower($input_string);
            // remove hyperlinks
            $text = preg_replace("/\b(([\w-]+:\/\/?|www[.])[^\s()<>]+(?:\([\w\d]+\)|([^[:punct:]\s]|\/)))/m", ' ', $text);
            // strips away html
            $text = strip_tags($text);
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
    
    public function ProcessAndInsertPost($feed_id, $post) {
        
        if (is_int($feed_id)) {
            if ($post != null ) {

                $post_link = $this->db_assistant->sanitize($post->link, true);
                $post_pubdate = strtotime($this->db_assistant->sanitize($post->pubDate, true)); // Convert to UCT Integer
                $post_pubdate = gmdate("Y-m-d H:i:s", $post_pubdate); // Convert to UCT Timestamp
                
                // Don't sanitize yet as it will add unecessary numbers to the text
                $post_title = $post->title;
                $post_description = $post->description;
                $post_title_description = $post->title . ' ' . $post->description;
                $post_body = $this->file_get_data($post_link);
                
                // Strip content of data we can't really analyze (for now)
                $post_title_words = implode(',', $this->ProcessPostText($post_title, 4));
                $post_description_words = implode(',', $this->ProcessPostText($post_description, 4));
                $post_title_description_words = implode(',', $this->ProcessPostText($post_title_description, 4));

                // Now lets strip excess characters off before saving
                $post_title = $this->db_assistant->sanitize($post_title, true);
                $post_description =  $this->db_assistant->sanitize($post_description, true);
                $post_title_description = $this->db_assistant->sanitize($post_title_description, true);
                $post_body = $this->db_assistant->sanitize($post_body, false);
                
                $sql = "INSERT INTO `SIFTMODE`.`POSTS` (`FEED_ID`, `LINK`, `PUBDATE`, `TITLE`, `TITLE_WORDS`, `DESCRIPTION`, `DESCRIPTION_WORDS`, `BODY`, `TITLE_DESCRIPTION_WORDS`, `CREATED_ON`) 
                        SELECT * FROM (SELECT {$feed_id}, '{$post_link}', '{$post_pubdate}', '{$post_title}', '{$post_title_words}', '{$post_description}', '{$post_description_words}', '{$post_body}', '{$post_title_description_words}', UTC_TIMESTAMP()) AS tmp 
                        WHERE NOT EXISTS (SELECT * FROM `SIFTMODE`.`POSTS` WHERE `FEED_ID`= {$feed_id} AND `PUBDATE` = '{$post_pubdate}') LIMIT 1;";    
                
                if (!in_array($this->db_assistant->query($sql), array(0,1))) { // returns rows inserted so 0 and 1 are okay
                    $this->applog("Failed to insert post into `POSTS` table. Feed Info: URL '{$post_link}', TITLE '{$post_title}', DESCRIPTION '{$post_description}', PUBLISHED '{$post_pubdate}'");
                }
            } else {
                $this->applog("Failed to insert post into `POSTS` table. The feed name was blank.");
            }
        }
    }
    
        
    function file_get_data($url) { /* gets the data from a URL */
            $ch = curl_init();
            $timeout = 5;
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
            curl_setopt($ch, CURLOPT_USERAGENT,'Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 6.1; Trident/6.0)'); // Lets pretend
            $data = curl_exec($ch);
            curl_close($ch);
            return $data;
    }

    public function FetchRSS($feed_id) {
        if (is_int($feed_id)) {
            //Get the URL
            $sql = "SELECT `feed_url` FROM `siftmode`.`feeds` WHERE `ID`= {$feed_id}";
            $result = $this->db_assistant->query($sql);
            $rows = mysqli_fetch_array($result);
            $feed_url = $rows[0];
            
            //Get the date of the last post fetched
            $sql = "SELECT `PUBDATE` FROM `siftmode`.`POSTS` WHERE `FEED_ID` = {$feed_id} ORDER BY `PUBDATE` DESC LIMIT 1";
            $result = $this->db_assistant->query($sql);
            $rows = mysqli_fetch_array($result);
            $last_feed_stored = strtotime($rows[0]);
            
            if ($last_feed_stored == null) {
                // If this is a new entry, only fetch today's posts
                $todays_date = date("Y-m-d 00:00:00");
                $last_feed_stored = strtotime('-1 day', strtotime($todays_date)); // If nothing is stored, get the last day's feeds.
            }
            
            // Fetch posts
            $data = $this->file_get_data($feed_url);
            $posts = new SimpleXmlElement($data);
            
            foreach($posts->channel->item as $post) {
                // Save post if greater than the latest one on the database.
                $post_pubdate = strtotime($this->db_assistant->sanitize($post->pubDate, true));
                if ($post_pubdate > $last_feed_stored) {
                    $this->ProcessAndInsertPost($feed_id, $post);
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


