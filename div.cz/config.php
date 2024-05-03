<?php

$servername = "localhost";
$username = "div.cz_martin";
$password = "your_secret_password";
$dbname = "DIV.cz_Tasks";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}


?>
