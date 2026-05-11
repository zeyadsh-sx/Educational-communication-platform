<?php
include "../../config/database.php";
include "../../includes/auth.php";

requireLogin();

$database = new Database();
$conn = $database->connect();

$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['course_id']) || !isset($data['question'])) {
  http_response_code(400);
  echo json_encode(["success" => false, "message" => "Missing required fields"]);
  exit;
}

$course_id = $data['course_id'];
$question_text = trim($data['question']);

if (empty($question_text)) {
  http_response_code(400);
  echo json_encode(["success" => false, "message" => "Question cannot be empty"]);
  exit;
}

// Verify user is enrolled in the course
$user_id = $_SESSION['user']['id'];
$enrollmentStmt = $conn->prepare("SELECT id FROM course_enrollments WHERE course_id = ? AND student_id = ? AND status = 'active'");
$enrollmentStmt->execute([$course_id, $user_id]);
$enrollment = $enrollmentStmt->fetch();

if (!$enrollment) {
  http_response_code(403);
  echo json_encode(["success" => false, "message" => "Not enrolled in this course"]);
  exit;
}

// Get professor_id from course
$courseStmt = $conn->prepare("SELECT professor_id FROM courses WHERE id = ?");
$courseStmt->execute([$course_id]);
$course = $courseStmt->fetch();

if (!$course) {
  http_response_code(404);
  echo json_encode(["success" => false, "message" => "Course not found"]);
  exit;
}

$insertStmt = $conn->prepare("INSERT INTO questions (question_text, student_id, professor_id, course_id, status, created_at) VALUES (?, ?, ?, ?, 'pending', NOW())");
$result = $insertStmt->execute([
  $question_text,
  $user_id,
  $course['professor_id'],
  $course_id
]);

if ($result) {
  echo json_encode(["success" => true, "message" => "Question submitted successfully"]);
} else {
  http_response_code(500);
  echo json_encode(["success" => false, "message" => "Failed to submit question"]);
}
