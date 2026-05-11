<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';

requireLogin();

$database = new Database();
$conn = $database->connect();

$course_id = $_GET['course_id'] ?? null;

if (!$course_id) {
  http_response_code(400);
  echo json_encode(["success" => false, "message" => "Course ID is required"]);
  exit;
}

$user_id = $_SESSION['user']['id'];
$user_type = $_SESSION['user']['user_type'];

// Check if user has access to this course
if ($user_type === 'student') {
  $accessStmt = $conn->prepare("SELECT id FROM course_enrollments WHERE course_id = ? AND student_id = ? AND status = 'active'");
} else {
  $accessStmt = $conn->prepare("SELECT id FROM courses WHERE id = ? AND professor_id = ?");
}
$accessStmt->execute([$course_id, $user_id]);
$access = $accessStmt->fetch();

if (!$access) {
  http_response_code(403);
  echo json_encode(["success" => false, "message" => "Access denied"]);
  exit;
}

$stmt = $conn->prepare("
    SELECT q.*, 
           u_student.full_name as student_name, 
           u_professor.full_name as professor_name,
           c.course_name
    FROM questions q
    LEFT JOIN users u_student ON q.student_id = u_student.id
    LEFT JOIN users u_professor ON q.professor_id = u_professor.id
    JOIN courses c ON q.course_id = c.id
    WHERE q.course_id = ?
    ORDER BY q.created_at DESC
");
$stmt->execute([$course_id]);

$questions = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode([
  "success" => true,
  "questions" => $questions
]);
