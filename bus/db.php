<?php 

// Database connection
$servername = "localhost"; // Change if not using localhost
$username = "root";        // Replace with your database username
$password = "";            // Replace with your database password
$dbname = "busease"; // Replace with your database name

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

?>