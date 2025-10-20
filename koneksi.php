<?php
// Connection details
$servername = "localhost";
$username = "root";
$password = "";
$dbname   = "budaya_dayak";

// Create connection 
$conn = mysqli_connect($servername, $username, $password, $dbname);

// Check connection 
if (!$conn) {
    die("connection failed: " . mysqli_connect_error());
}
?>