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
    
    private function appLog($message) {   
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
    
    public function insertFeed($user_id, $category_id, $feed_url, $feed_name, $feed_description = "") {
        if (is_int($category_id) && is_int($user_id)) {
            if (strlen(trim($feed_name)) > 0) {
                
                $feed_url = $this->db_assistant->sanitize($feed_url, true);
                $feed_name = $this->db_assistant->sanitize($feed_name, true);
                $feed_description = $this->db_assistant->sanitize($feed_description, true);
                
                $sql = "CALL `insertFeed`({$category_id},'{$feed_url}','{$feed_name}','{$feed_description}')";
                        
                if ($this->db_assistant->query($sql) > 0) {
                    $this->appLog("The following query failed: {$sql}");
                }
            } else {
                $this->appLog("Failed to insert feed into `feeds` table. The feed name was blank.");
            }
        }
    }
   
    public function deleteFeed($feed_id) {
        if (is_int($feed_id)) {             
            $sql = "CALL `deleteFeed`({$feed_id})"; // Sift002
            if ($this->db_assistant->query($sql) > 0) {
                $this->appLog("The following query failed: {$sql}");
            }
        }
    }
    
    public function insertCategory($user_id, $category_name, $common_words_array_or_string) {
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
                
                $sql = "CALL `insertCategory`({$user_id}, '{$category_name}', '{$common_words_string}');";
                if ($this->db_assistant->query($sql) > 0) {
                    $this->appLog("The following query failed: {$sql}");
                }
            } else {
                $this->appLog("Tried to insert a category with a blank name.");
            }
        }
    }
    
    public function deleteCategory($category_id) {
        if (is_int($category_id)) {             
            $sql = "Call `deleteCategory`({$category_id})";
            if ($this->db_assistant->query($sql) > 0) {
                $this->appLog("The following query failed: {$sql}");
            }
        }
    }
    
    public function processPostText($input_string, $int_min_letters) { // returns a word array, IMPORTANT: 4 LETTER MIN FOR MATCH AGAINST
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
    
    public function processAndInsertPost($feed_id, $post, $fetch_article = 0) {
        
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
                    $post_article = $this->fetchData($post_link);
                }
                // Strip content of data we can't really analyze (for now)
                $post_title_words = implode(',', $this->processPostText($post_title, 4)); // 4 characters because of a full-text search limitation
                $post_description_words = implode(',', $this->processPostText($post_description, 4));
                $post_title_description_words = implode(',', $this->processPostText($post_title_description, 4));

                // Now lets strip excess characters off before saving
                $post_title = $this->db_assistant->sanitize($post_title, true);
                $post_description =  $this->db_assistant->sanitize($post_description, true);
                $post_title_description = $this->db_assistant->sanitize($post_title_description, true);
                if ($fetch_article) {
                    $post_article = $this->db_assistant->sanitize($post_article, false);
                }
                
                $sql = "CALL `InsertPost` ({$feed_id},'{$post_link}','{$post_pubdate}','{$post_title}','{$post_title_words}','{$post_description}','{$post_description_words}','{$post_article}','{$post_title_description_words}')";    

                if (!in_array($this->db_assistant->query($sql), array(0,1))) { // returns rows inserted so 0 and 1 are okay
                    $this->appLog("The following query failed: {$sql}");
                }
            } else {
                $this->appLog("Failed to insert post into `POSTS` table. The feed name was blank.");
            }
        }
    }
    
        
    function fetchData($url) { /* gets the data from a URL */
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

    public function fetchPosts($feed_id) {
        if (is_int($feed_id)) {
            //Get the URL
            $sql = "CALL `GetFeedURL`({$feed_id})";
            $result = $this->db_assistant->query($sql);
            $rows = mysqli_fetch_array($result);
            $feed_url = $rows[0];
            $fetch_articles = (int)$rows[1];
            
            // Fetch posts
            $data = $this->fetchData($feed_url);
            $posts = new SimpleXmlElement($data);
            
            foreach($posts->channel->item as $post) {
                $this->processAndInsertPost($feed_id, $post, $fetch_articles);
            }
        }
    }
    
    public function fetchCategory($cat_id) {
        if (is_int($cat_id)) {
            //Get the categories' article ids
            $sql = "CALL `GetCategoryFeedIDs`({$cat_id})";
            $result = $this->db_assistant->query($sql);
            $feeds_updated = 0;
            while($row = mysqli_fetch_array($result))
            {
                if (is_int($feed_id = (int)$row['feed_id'])) {
                    $this->fetchPosts($feed_id);
                    $feeds_updated++;
                }
            }     
            if ($feeds_updated > 0) {
                $sql = "CALL `UpdateCategoriesLastRun`({$cat_id})";
                $this->db_assistant->query($sql);
            }
        }
    }
    
    public function generateSummary($cat_id, $start_time, $type) {
        
        if (is_int($cat_id) && $this->isValidDate($start_time) && $this->isValidType($type)) {
            // This might take a while so lets set some time limits
            $time_limit = 0; // Seconds
            switch ($time_limit) {
                case 'D':
                    $time_limit = 180; // 3 mins
                    break;
                case 'W':
                    $time_limit = 360; // 6 mins
                    break;
                case 'M':
                    $time_limit = 1800; // 30 mins
                    break;
                case 'Y':
                    $time_limit = 21600; // 360 mins
                    break;
                default:
                    $time_limit = 180; // 3 mins
                    break;
            }
            set_time_limit($time_limit); 
            
            // Add an stdClass so we could add some meta data to our summary before we save it
            $summaries = new stdClass();
            $summaries->category_id = $cat_id;
            $summaries->type = $type;
            $summaries->start_time = $start_time;
            $summaries->end_time = null;

            // Store summary rows here.
            $summary_list = array();    
        
            // Process and return a summary
            $result = $this->db_assistant->query("CALL `Core_Summarize`({$cat_id},'{$start_time}','{$type}')");

            // Add our summary rows to our summary_list
            while($row = mysqli_fetch_array($result))
            {
                $summary = new stdClass();
                $summary->feed_id = $row['feed_id'];
                $summary->post_id = $row['post_id'];
                $summary->match_id = $row['match_id'];
                $summary->match_priority = $row['match_priority'];
                $summary->match_string = $row['match_string'];
                array_push($summary_list, $summary);
                if ($summaries->end_time == null) {
                    $summaries->end_time = $row['end_time'];
                }
            } 
            
            $summaries->summary_list = $summary_list;
            // Convert to JSON
            $json_string = json_encode($summaries);
            
            // Save the summary data into the summaries table
            $result = $this->db_assistant->query("CALL `Core_SaveSummary`({$cat_id},'{$start_time}','{$type}','{$json_string}')");
            
            if ($result < 1) {
                $this->appLog("The following summarization yielded no results: 'CALL `Core_SaveSummary`({$cat_id},'{$start_time}','{$type}','{$json_string}')'");
            }
        }
    }
    private function isValidType($type) {
        $accepted_types = array('D', 'W', 'M', 'Y');
        if (!in_array($type, $accepted_types)) {
            return false;
            $this->appLog("The following summary type is invalid: {$type}");
        } else {
            return true;
        }
    }
    private function isValidDate($date) {
        date_default_timezone_set('UTC');
        if (($timestamp = strtotime($date)) === false) {
            return false;
             $this->appLog("The following date is invalid: {$date}");
        } else {
            return true;
        }
    }
}

?>
