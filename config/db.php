<?php
$servername = "localhost";  // or your database server
$username = "root";         // your database username
$password = "";             // your database password
$dbname = "url_shortener";  // your database name

try {
    // Use $servername instead of $host in the PDO connection string
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8mb4", $username, $password);
    
    // Set PDO error mode to exception
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>
