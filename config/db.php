<?php

$host = "localhost";   
$user = "root";       
$pass = "";            
$db   = "library_system";


$conn = new mysqli($host, $user, $pass, $db);


if ($conn->connect_error) {
    die("Database Connection Failed: " . $conn->connect_error);
}
// echo "Database Connected Successfully"; 
?>