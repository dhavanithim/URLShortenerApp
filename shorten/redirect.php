<?php
file_put_contents('debug.log', "Code received: " . $_GET['code'] . "\n", FILE_APPEND);

// Adjust paths based on how the script is accessed
$base_path = dirname(dirname(__FILE__));
require_once $base_path . '/config/db.php';
require_once $base_path . '/includes/functions.php';

// Get short code - try both methods (clean URL or GET parameter)
$code = '';
if (isset($_GET['code'])) {
    // If accessed via redirect.php?code=ABC
    $code = sanitizeInput($_GET['code']);
} else {
    // If accessed via clean URL (domain.com/ABC)
    $uri = $_SERVER['REQUEST_URI'];
    $parts = explode('/', trim($uri, '/'));
    $code = sanitizeInput(end($parts));
}

// If no code found, redirect to home
if (empty($code)) {
    header('Location: ../index.php');
    exit;
}

try {
    // Retrieve original URL for the given short code
    $stmt = $pdo->prepare("SELECT original_url, id FROM links WHERE short_code = ?");
    $stmt->execute([$code]);
    
    if ($stmt->rowCount() > 0) {
        $link = $stmt->fetch(PDO::FETCH_ASSOC);
        $original_url = $link['original_url'];
        $link_id = $link['id'];

        // Update click count in the links table
        $update_stmt = $pdo->prepare("UPDATE links SET click_count = click_count + 1 WHERE short_code = ?");
        $update_stmt->execute([$code]);

        // Insert into clicks table (tracking IP, country, browser)
        $ip_address = $_SERVER['REMOTE_ADDR']; // Get the IP address of the visitor
        $country = 'India'; // Set a default value or use an API for geolocation
        $browser = $_SERVER['HTTP_USER_AGENT']; // Get the user's browser info

        $insert_click_stmt = $pdo->prepare("INSERT INTO clicks (link_id, ip_address, country, browser) VALUES (?, ?, ?, ?)");
        $insert_click_stmt->execute([$link_id, $ip_address, $country, $browser]);

        // Make sure URL has proper protocol
        if (!preg_match("~^(?:f|ht)tps?://~i", $original_url)) {
            $original_url = "http://" . $original_url;
        }

        // Redirect to the original URL
        header("Location: $original_url");
        exit;
    } else {
        echo "<h1>URL Not Found</h1>";
        echo "<p>The shortened URL you requested could not be found.</p>";
        echo "<p><a href='../index.php'>Return to homepage</a></p>";
    }
} catch (PDOException $e) {
    echo "<h1>System Error</h1>";
    echo "<p>We're experiencing technical difficulties. Please try again later.</p>";
    error_log("Redirect Error: " . $e->getMessage());
}
?>
