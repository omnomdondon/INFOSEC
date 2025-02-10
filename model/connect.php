<?php
$HOST = 'localhost';
$USER = 'root';
$PASSWORD = '';
$DB = 'blog_db';
$PORT = 3306;

// $HOST = 'localhost';
// $USER = 'root';
// $PASSWORD = 'DonBelle_111522';
// $DB = 'blog_db';
// $PORT = 3307;

$CONN = new mysqli($HOST, $USER, $PASSWORD, $DB, $PORT);
if ($CONN->connect_error) {
    die("Database Connection Failed: " . $CONN->connect_error);
}

return $CONN;
?>
