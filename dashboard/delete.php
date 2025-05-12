<?php
session_start();
include('../config/db.php');

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

if (isset($_GET['id'])) {
    $link_id = $_GET['id'];
    $user_id = $_SESSION['user_id'];

    // Ensure the link belongs to the logged-in user
    $query = "SELECT * FROM links WHERE id = ? AND user_id = ?";
    $stmt = $pdo->prepare($query);
    $stmt->bindValue(1, $link_id, PDO::PARAM_INT);  // Bind parameters using bindValue
    $stmt->bindValue(2, $user_id, PDO::PARAM_INT);  // Bind parameters using bindValue
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        // Delete the link and its associated clicks
        $delete_link_query = "DELETE FROM links WHERE id = ?";
        $delete_clicks_query = "DELETE FROM clicks WHERE link_id = ?";

        try {
            $pdo->beginTransaction(); // Start transaction

            // Delete the clicks first
            $stmt = $pdo->prepare($delete_clicks_query);
            $stmt->bindValue(1, $link_id, PDO::PARAM_INT); // Bind parameter
            $stmt->execute();

            // Delete the link
            $stmt = $pdo->prepare($delete_link_query);
            $stmt->bindValue(1, $link_id, PDO::PARAM_INT); // Bind parameter
            $stmt->execute();

            $pdo->commit(); // Commit transaction
            header("Location: index.php");  // Redirect back to dashboard
            exit();
        } catch (Exception $e) {
            $pdo->rollBack(); // Rollback in case of an error
            echo "Error: " . $e->getMessage();
        }
    } else {
        echo "Unauthorized access!";
    }
} else {
    echo "No link ID provided!";
}
?>
