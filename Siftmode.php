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
    
    private function GetLocalTime() {
        // Simple function that gets the current time for us
        $zone = new DateTimeZone('America/New_York');
        $time = new DateTime(date("Y-m-d g:i:sA", time()));
        $time->setTimezone($zone);
        return $time->format('Y-m-d g:i:sA');
    }
    
    private function AppLog($message) {   
        // Simple function that writes to the application log
        $error_message = "Application log '" . $this->app_error_log . "' can't be created or is not writable.";
        if (file_exists($this->app_error_log) && is_writable($this->app_error_log)) { 
            $message = '(' . $this->GetLocalTime() . ') Msg: ' . $message . "\n";
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
                
                $sql = "INSERT INTO `SIFTMODE`.`FEEDS` (`CATEGORY_ID`, `FEED_URL`, `NAME`, `DESCRIPTION`, `CREATED_ON`) 
                        SELECT * FROM (SELECT {$category_id},'{$feed_url}','{$feed_name}','{$feed_description}', UTC_TIMESTAMP()) AS tmp 
                        WHERE NOT EXISTS (SELECT * FROM `SIFTMODE`.`FEEDS` WHERE CATEGORY_ID` = {$category_id} AND (`FEED_URL` = '{$feed_url}' OR `NAME` = '{$feed_name}')) LIMIT 1;";
                        
                if ($this->db_assistant->query($sql) > 0) {
                    $this->AppLog("Failed to insert feed into `feeds` table. Feed Info: URL '{$feed_url}', NAME '{$feed_name}', DESCRIPTION '{$feed_description}'");
                }
            } else {
                $this->AppLog("Failed to insert feed into `feeds` table. The feed name was blank.");
            }
        }
    }
   
    public function DeleteFeed($feed_id) {
        if (is_int($feed_id)) {             
            $sql = "DELETE FROM `siftmode`.`feeds` WHERE `ID`= {$feed_id}"; // Sift002
            if ($this->db_assistant->query($sql) > 0) {
                $this->AppLog("Failed to delete feed from `feeds` table. Feed Info: ID '{$feed_id}'");
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
                    $this->AppLog("Failed to insert feed category into `categories` table. Category Info: User ID '{$user_id}', CATEGORY NAME '{$category_name}', COMMON WORDS '{$common_words_string}'");
                }
            } else {
                $this->AppLog("Failed to insert feed category into `categories` table. The category name was blank.");
            }
        }
    }
    
    public function DeleteCategory($category_id) {
        if (is_int($category_id)) {             
            $sql = "DELETE FROM `siftmode`.`categories` WHERE `ID`= {$category_id}";
            if ($this->db_assistant->query($sql) > 0) {
                $this->AppLog("Failed to delete category from `categories` table. Category Info: ID '{$category_id}'");
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
    
    public function ProcessAndInsertPost($feed_id, $post, $fetch_article = 0) {
        
        if (is_int($feed_id)) {
            if ($post != null ) {

                $post_link = $this->db_assistant->sanitize($post->link, true);
                date_default_timezone_set('UTC');
                $post_pubdate = strtotime($this->db_assistant->sanitize($post->pubDate, true)); // Convert to UCT Integer
                $post_pubdate = gmdate("Y-m-d H:i:s", $post_pubdate); // Convert to UCT Timestamp
                
                // Don't sanitize yet as it will add unecessary numbers to the text
                $post_title = $post->title;
                $post_description = $post->description;
                $post_title_description = $post->title . ' ' . $post->description;
                $post_article = NULL;
                if ($fetch_article) {
                    $post_article = $this->FetchData($post_link);
                }
                // Strip content of data we can't really analyze (for now)
                $post_title_words = implode(',', $this->ProcessPostText($post_title, 4));
                $post_description_words = implode(',', $this->ProcessPostText($post_description, 4));
                $post_title_description_words = implode(',', $this->ProcessPostText($post_title_description, 4));

                // Now lets strip excess characters off before saving
                $post_title = $this->db_assistant->sanitize($post_title, true);
                $post_description =  $this->db_assistant->sanitize($post_description, true);
                $post_title_description = $this->db_assistant->sanitize($post_title_description, true);
                if ($fetch_article) {
                    $post_article = $this->db_assistant->sanitize($post_article, false);
                }
                
                $sql = "CALL `SM_InsertPost` ({$feed_id},'{$post_link}','{$post_pubdate}','{$post_title}','{$post_title_words}','{$post_description}','{$post_description_words}','{$post_article}','{$post_title_description_words}')";    

                if (!in_array($this->db_assistant->query($sql), array(0,1))) { // returns rows inserted so 0 and 1 are okay
                    $this->AppLog("Failed to insert post into `POSTS` table. Feed Info: URL '{$post_link}', TITLE '{$post_title}', DESCRIPTION '{$post_description}', PUBLISHED '{$post_pubdate}'");
                }
            } else {
                $this->AppLog("Failed to insert post into `POSTS` table. The feed name was blank.");
            }
        }
    }
    
        
    function FetchData($url) { /* gets the data from a URL */
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

    public function FetchPosts($feed_id) {
        if (is_int($feed_id)) {
            //Get the URL
            $sql = "CALL `SM_GetFeedURL`({$feed_id})";
            $result = $this->db_assistant->query($sql);
            $rows = mysqli_fetch_array($result);
            $feed_url = $rows[0];
            $fetch_articles = (int)$rows[1];
            
            // Fetch posts
            $data = $this->FetchData($feed_url);
            $posts = new SimpleXmlElement($data);
            
            foreach($posts->channel->item as $post) {
                $this->ProcessAndInsertPost($feed_id, $post, $fetch_articles);
            }
        }
    }
    
    public function FetchCategory($cat_id) {
        if (is_int($cat_id)) {
            //Get the categories' article ids
            $sql = "CALL `SM_GetCategoryFeedIDs`({$cat_id})";
            $result = $this->db_assistant->query($sql);
            $feeds_updated = 0;
            while($row = mysqli_fetch_array($result))
            {
                if (is_int($feed_id = (int)$row['feed_id'])) {
                    $this->FetchPosts($feed_id);
                    $feeds_updated++;
                }
            }     
            if ($feeds_updated > 0) {
                $sql = "CALL `SM_UpdateCategoriesLastRun`({$cat_id})";
                $this->db_assistant->query($sql);
            }
        }
    }
}
?>
