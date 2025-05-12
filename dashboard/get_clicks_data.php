<?php
require_once '../config.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$user_id = $_SESSION['user_id'];

try {
    $stmt = $pdo->prepare("
        SELECT DATE(c.clicked_at) AS click_date, COUNT(*) AS click_count 
        FROM clicks c
        JOIN links l ON c.link_id = l.id
        WHERE l.user_id = :user_id
        GROUP BY DATE(c.clicked_at)
        ORDER BY click_date DESC
        LIMIT 7
    ");
    $stmt->execute(['user_id' => $user_id]);
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($data);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>
