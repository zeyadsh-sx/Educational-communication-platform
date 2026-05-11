<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';

requireLogin();

$database = new Database();
$conn = $database->connect();

$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['professor_id']) || !isset($data['date_time'])) {
  http_response_code(400);
  echo json_encode(["success" => false, "message" => "Missing required fields"]);
  exit;
}

$professor_id = $data['professor_id'];
$date_time = $data['date_time'];
$notes = trim($data['notes'] ?? '');
$course_id = $data['course_id'] ?? null;

$user_id = $_SESSION['user']['id'];
$user_type = $_SESSION['user']['user_type'];

if ($user_type !== 'student') {
  http_response_code(403);
  echo json_encode(["success" => false, "message" => "Only students can book appointments"]);
  exit;
}

// Verify professor exists
$professorStmt = $conn->prepare("SELECT id FROM users WHERE id = ? AND user_type = 'professor'");
$professorStmt->execute([$professor_id]);
$professor = $professorStmt->fetch();

if (!$professor) {
  http_response_code(404);
  echo json_encode(["success" => false, "message" => "Professor not found"]);
  exit;
}

// Check if the time slot is available (no overlapping appointments)
$conflictStmt = $conn->prepare("
    SELECT id FROM appointments 
    WHERE professor_id = ? 
    AND DATE(date_time) = DATE(?) 
    AND ABS(TIMESTAMPDIFF(MINUTE, TIME(date_time), TIME(?))) < 60
    AND status IN ('pending', 'confirmed')
");
$conflictStmt->execute([$professor_id, $date_time, $date_time]);
$conflict = $conflictStmt->fetch();

if ($conflict) {
  http_response_code(409);
  echo json_encode(["success" => false, "message" => "Time slot not available"]);
  exit;
}

$insertStmt = $conn->prepare("INSERT INTO appointments (student_id, professor_id, course_id, date_time, notes, status, created_at) VALUES (?, ?, ?, ?, ?, 'pending', NOW())");
$result = $insertStmt->execute([
  $user_id,
  $professor_id,
  $course_id,
  $date_time,
  $notes
]);

if ($result) {
  // Award points to student
  include "../../includes/gamification.php";
  awardPoints($user_id, 'book_appointment');
} else {
  http_response_code(500);
  echo json_encode(["success" => false, "message" => "Failed to book appointment"]);
}
