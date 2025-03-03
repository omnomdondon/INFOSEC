<?php
// Database configuration
// $HOST = 'localhost';
// $USER = 'root';
// $PASSWORD = '111522';
// $DB = 'blog_db';
// $PORT = 3306;

$HOST = 'localhost';
$USER = 'root';
$PASSWORD = '';
$DB = 'blog_db';
$PORT = 3306;

// Debugging: Log connection attempt
error_log("Attempting to connect to database: host=$HOST, user=$USER, db=$DB, port=$PORT");

// Create a new mysqli connection
$CONN = new mysqli($HOST, $USER, $PASSWORD, $DB, $PORT);
if ($CONN->connect_error) {
    die("Database Connection Failed: " . $CONN->connect_error);
}


// Debugging: Log successful connection
error_log("Database connection successful.");

return $CONN;
?>