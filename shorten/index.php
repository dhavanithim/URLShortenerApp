<?php
session_start();
$isLoggedIn = isset($_SESSION['user_id']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Shorten URL</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
</head>
<body>
    <h1>Shorten Your URL</h1>

    <?php if ($isLoggedIn): ?>
        <p>Welcome, <?= htmlspecialchars($_SESSION['username']); ?>! <a href="../dashboard/index.php">Dashboard</a> | <a href="../auth/logout.php">Logout</a></p>
    <?php else: ?>
        <p><a href="../auth/login.php">Login</a> or <a href="../auth/register.php">Register</a> for tracking your links.</p>
    <?php endif; ?>

    <form action="shorten.php" method="post">
        <label for="original_url">Enter URL:</label>
        <input type="url" name="original_url" id="original_url" required>
        <button type="submit">Shorten</button>
    </form>

    <?php if (isset($_GET['short'])): ?>
    <p>Your shortened URL: 
        <a href="https://<?= $_SERVER['HTTP_HOST'] . '/UrlShortenerApp/' . htmlspecialchars($_GET['short']); ?>" target="_blank">
    https://<?= $_SERVER['HTTP_HOST'] . '/UrlShortenerApp/' . htmlspecialchars($_GET['short']); ?>
</a>

    </p>
<?php endif; ?>

</body>
</html>
