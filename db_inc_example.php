<?php
// Nikolozi Gagua
// C00303433
// This is a template for connecting to the database. Copy it to db.inc.php and fill in your actual details.

// These are placeholders for your database info
$host = 'localhost';
$username = 'your_username';
$password = 'your_password';
$database = 'blacklist_manager';

// Connect to the database
$con = mysqli_connect($host, $username, $password, $database);

// Check if the connection worked
if (!$con) {
    die("Connection failed: " . mysqli_connect_error());
}
?>