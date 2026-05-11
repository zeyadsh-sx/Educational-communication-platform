<?php
include "../../config/database.php";
include "../../includes/auth.php";
include "../../includes/notification_functions.php";

requireLogin();

$database = new Database();
$conn = $database->connect();

$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['question_id']) || !isset($data['answer'])) {
  http_response_code(400);
  echo json_encode(["success" => false, "message" => "Missing required fields"]);
  exit;
}

$question_id = $data['question_id'];
$answer_text = trim($data['answer']);

if (empty($answer_text)) {
  http_response_code(400);
  echo json_encode(["success" => false, "message" => "Answer cannot be empty"]);
  exit;
}

$user_id = $_SESSION['user']['id'];
$user_type = $_SESSION['user']['user_type'];

if ($user_type !== 'professor') {
  http_response_code(403);
  echo json_encode(["success" => false, "message" => "Only professors can answer questions"]);
  exit;
}

// Verify the professor owns this question
$questionStmt = $conn->prepare("SELECT student_id, course_id FROM questions WHERE id = ? AND professor_id = ? AND status = 'pending'");
$questionStmt->execute([$question_id, $user_id]);
$question = $questionStmt->fetch();

if (!$question) {
  http_response_code(404);
  echo json_encode(["success" => false, "message" => "Question not found or already answered"]);
  exit;
}

$updateStmt = $conn->prepare("UPDATE questions SET answer = ?, status = 'answered', answered_at = NOW() WHERE id = ?");
$result = $updateStmt->execute([$answer_text, $question_id]);

if ($result) {
  // Send notification to student
  $courseStmt = $conn->prepare("SELECT course_name FROM courses WHERE id = ?");
  $courseStmt->execute([$question['course_id']]);
  $course = $courseStmt->fetch();
  $course_name = $course ? $course['course_name'] : 'Unknown Course';

  sendNotification($question['student_id'], "تم الرد على سؤالك في كورس {$course_name}");

  // Award points to professor
  include "../../includes/gamification.php";
  awardPoints($user_id, 'answer_question');

  echo json_encode(["success" => true, "message" => "Answer submitted successfully"]);
} else {
  http_response_code(500);
  echo json_encode(["success" => false, "message" => "Failed to submit answer"]);
}
