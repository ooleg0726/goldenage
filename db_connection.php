<?php
// db_connection.php
$servername = "localhost"; // Your database server name or IP address
$username = "root";        // Your database username
$password = "";            // Your database password
$dbname = "dbgold";      // Your database name

// Create connection
$conn = mysqli_connect($servername, $username, $password, $dbname);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
?>
