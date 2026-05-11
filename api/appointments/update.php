<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

include "../../config/database.php";
include "../../includes/auth.php";

requireLogin();

$database = new Database();
$conn = $database->connect();

$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['appointment_id']) || !isset($data['status'])) {
  http_response_code(400);
  echo json_encode(["success" => false, "message" => "Invalid data"]);
  exit;
}

$appointment_id = $data['appointment_id'];
$status = $data['status'];

$user_id = $_SESSION['user']['id'];
$user_type = $_SESSION['user']['user_type'];

// Verify the user owns this appointment
if ($user_type === 'professor') {
  $stmt = $conn->prepare("SELECT id FROM appointments WHERE id = ? AND professor_id = ?");
} else {
  $stmt = $conn->prepare("SELECT id FROM appointments WHERE id = ? AND student_id = ?");
}

$stmt->execute([$appointment_id, $user_id]);
$appointment = $stmt->fetch();

if (!$appointment) {
  http_response_code(403);
  echo json_encode(["success" => false, "message" => "Unauthorized"]);
  exit;
}

$updateStmt = $conn->prepare("UPDATE appointments SET status = ? WHERE id = ?");
$result = $updateStmt->execute([$status, $appointment_id]);

if ($result) {
  echo json_encode(["success" => true]);
} else {
  http_response_code(500);
  echo json_encode(["success" => false, "message" => "Database error"]);
}
