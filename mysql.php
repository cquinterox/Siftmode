<?php
// SETTINGS
define('db_server', '');
define('db_user', '');
define('db_password', '');
define('database', '');
define('db_data_table', '');
define('db_word_counts', '');
define('db_set_counts', '');

function connect_to_mysql() {
    $conn = mysqli_connect(db_server, db_user, db_password);
    if (!$conn) {
        error_log(mysqli_errno($db_conn) . ": " . mysqli_error($db_conn), 0);
        exit();
    }
    if (!mysqli_select_db($conn, database)) {
        error_log(mysqli_errno($db_conn) . ": " . mysqli_error($db_conn), 0);
        exit();
    }
    return $conn;
}
?>