<?php
include "../../config/database.php";
include "../../includes/auth.php";

requireLogin();

$database = new Database();
$conn = $database->connect();

$user_id = $_GET['user_id'] ?? null;

if (!$user_id) {
  http_response_code(400);
  echo json_encode(["success" => false, "message" => "User ID is required"]);
  exit;
}

// Only allow users to view their own notifications
$current_user_id = $_SESSION['user']['id'];
if ($user_id != $current_user_id) {
  http_response_code(403);
  echo json_encode(["success" => false, "message" => "Access denied"]);
  exit;
}

$stmt = $conn->prepare("
    SELECT * FROM notifications 
    WHERE user_id = ? 
    ORDER BY created_at DESC 
    LIMIT 50
");
$stmt->execute([$user_id]);

$notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Mark notifications as read
$updateStmt = $conn->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ? AND is_read = 0");
$updateStmt->execute([$user_id]);

echo json_encode([
  "success" => true,
  "notifications" => $notifications
]);
