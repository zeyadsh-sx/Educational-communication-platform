<?php
header('Content-Type: application/json');
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

if (!isLoggedIn()) {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$userId = getCurrentUserId();
$pdo = getDB();

try {
    // Get unread notifications
    $stmt = $pdo->prepare("SELECT id, message, created_at, is_read 
                           FROM notifications 
                           WHERE user_id = ? 
                           ORDER BY created_at DESC LIMIT 10");
    $stmt->execute([$userId]);
    $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get unread count
    $countStmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = FALSE");
    $countStmt->execute([$userId]);
    $unreadCount = $countStmt->fetchColumn();
    
    echo json_encode([
        'status' => 'success',
        'notifications' => $notifications,
        'unread_count' => (int)$unreadCount
    ]);
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
