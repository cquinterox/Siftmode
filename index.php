<?php
//    function getLocalTime() {
//        // Simple function that gets the current time for us
//        $zone = new DateTimeZone('America/New_York');
//        $time = new DateTime(date("Y-m-d H:i:s", time()));
//        $time->setTimezone($zone);
//        return $time->format('Y-m-d H:i:s');
//    }
//    echo getLocalTime();
//echo "<h1>PHP</h1>";
//
//echo "1. Gives you current unix timestamp (int)<br />";
//echo "Time() = " . time();
//
//echo "<br />";
//echo "<br />";
//
//echo "2. Gives you unix timestamp (int) from english phrase of time.<br />";
//echo "strtotime('yesterday') = " . strtotime("yesterday");
//
//echo "<br />";
//echo "<br />";
//
//
//echo "3. Gives you unix timestamp (int) from any date/time. <br />";
//echo "strtotime('2013-07-11') = ";
//echo strtotime('2013-07-11');
//
//echo "<br />";
//echo "<br />";
//
//echo "4. Return current Unix timestamp with microseconds. <br />";
//echo "Microtime() = " . microtime();
//
//echo "<br />";
//echo "<br />";
//
//echo "<h1>Save to MySQL</h1>";
//
//echo "1. Returns (in SQL) the timestamp format from a unix int;";
//
//echo "<br />";
//echo "<hr />";
//echo strtotime(date("Y-m-d 00:00:00", time())) . "<br />";
//echo strtotime('2013-07-15 00:00:00') . "<br />";
//echo strtotime('Jul 15,13');
//echo "<br />";
//echo "<hr />";
//
//$the_date = strtotime("2013-07-15 00:00:00");
//echo gmdate("Y-m-d\TH:i:s\Z", $the_date);

//echo phpinfo();

include_once 'Siftmode.php';
$x = new Siftmode();
$x->FetchRSS(9);

//            // Fetch posts
//            $content = file_get_contents("http://rss.cnn.com/rss/cnn_topstories.rss");
//            $x = new SimpleXmlElement($content);
//            print_r($x);
//date_default_timezone_set('America/New_York'); 
//$date = strtotime('7/17/2013 12:45:00 AM');
//echo gmdate("Y-m-d H:i:s", $date); 

?>


