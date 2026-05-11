<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

include "../../config/database.php";
include "../../includes/auth.php";

requireLogin();

$data = json_decode(file_get_contents('php://input'), true);

$user_id = $_SESSION['user']['id'] ?? $_SESSION['user_id'];

try {
  $pdo = getDB();

  // Delete all notifications for the user
  $stmt = $pdo->prepare("DELETE FROM notifications WHERE user_id = ?");
  $result = $stmt->execute([$user_id]);

  if ($result) {
    echo json_encode(["success" => true]);
  } else {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Database error"]);
  }
} catch (Exception $e) {
  http_response_code(500);
  echo json_encode(["success" => false, "message" => "Server error"]);
}
