<?php
session_start();
include('../config/db.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Make sure original_url is set
    if (!isset($_POST['original_url'])) {
        die("No URL provided or invalid request.");
    }

    $original_url = trim($_POST['original_url']);

    if (!filter_var($original_url, FILTER_VALIDATE_URL)) {
        die("Invalid URL");
    }

    // Generate unique short code
    $short_code = substr(md5(uniqid(rand(), true)), 0, 6);

    // Check for logged-in user
    $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

    // Save to database using PDO
    $stmt = $pdo->prepare("INSERT INTO links (user_id, original_url, short_code) VALUES (:user_id, :original_url, :short_code)");
    $stmt->execute([
        ':user_id' => $user_id,
        ':original_url' => $original_url,
        ':short_code' => $short_code
    ]);

    // Redirect back to index page with short code
    header("Location: ../index.php?short=$short_code");
    exit();
} else {
    header("Location: ../index.php");
    exit();
}
