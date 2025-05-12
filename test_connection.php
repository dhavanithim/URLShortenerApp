<?php
echo "Starting script...<br>";

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "url_shortener";

// Create connection
$pdo = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($pdo->connect_error) {
    echo "Connection failed: " . $pdo->connect_error;
} else {
    echo "Connected to the database successfully doneee!";
}
?>
