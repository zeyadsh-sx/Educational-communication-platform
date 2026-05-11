<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/gamification.php';

requireLogin();

$database = new Database();
$conn = $database->connect();

$data = json_decode(file_get_contents('php://input'), true);
$course_id = $data['course_id'] ?? null;
$file_data = $data['file'] ?? null; // Assuming base64 encoded file

if (!$course_id || !$file_data) {
  http_response_code(400);
  echo json_encode(["success" => false, "message" => "Course ID and file data are required"]);
  exit;
}

$user_id = $_SESSION['user']['id'];
$user_type = $_SESSION['user']['user_type'];

if ($user_type !== 'professor') {
  http_response_code(403);
  echo json_encode(["success" => false, "message" => "Only professors can upload materials"]);
  exit;
}

// Verify professor owns the course
$courseStmt = $conn->prepare("SELECT id FROM courses WHERE id = ? AND professor_id = ?");
$courseStmt->execute([$course_id, $user_id]);
$course = $courseStmt->fetch();

if (!$course) {
  http_response_code(403);
  echo json_encode(["success" => false, "message" => "You don't own this course"]);
  exit;
}

// Decode base64 file
$file_content = base64_decode($file_data['content']);
$file_name = time() . "_" . $file_data['name'];
$file_path = "../../uploads/materials/" . $file_name;

// Ensure upload directory exists
$upload_dir = dirname($file_path);
if (!is_dir($upload_dir)) {
  mkdir($upload_dir, 0755, true);
}

// Save file
if (file_put_contents($file_path, $file_content) === false) {
  http_response_code(500);
  echo json_encode(["success" => false, "message" => "Failed to save file"]);
  exit;
}

$insertStmt = $conn->prepare("INSERT INTO materials (course_id, file_name, file_path, uploaded_by, uploaded_at) VALUES (?, ?, ?, ?, NOW())");
$result = $insertStmt->execute([
  $course_id,
  $file_data['name'],
  $file_path,
  $user_id
]);

if ($result) {
  // Award points to professor for uploading material
  awardPoints($user_id, 'upload_material');

  echo json_encode(["success" => true, "message" => "Material uploaded successfully"]);
} else {
  // Clean up file if database insert failed
  unlink($file_path);
  http_response_code(500);
  echo json_encode(["success" => false, "message" => "Failed to save material info"]);
}
