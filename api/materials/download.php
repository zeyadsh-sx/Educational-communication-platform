<?php
include "../../config/database.php";
include "../../includes/auth.php";
include "../../includes/gamification.php";

requireLogin();

$database = new Database();
$conn = $database->connect();

$id = $_GET['id'] ?? null;

if (!$id) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Material ID is required"]);
    exit;
}

$user_id = $_SESSION['user']['id'];
$user_type = $_SESSION['user']['user_type'];

$stmt = $conn->prepare("
    SELECT m.*, c.professor_id
    FROM materials m
    JOIN courses c ON m.course_id = c.id
    WHERE m.id = ?
");
$stmt->execute([$id]);
$material = $stmt->fetch();

if (!$material) {
    http_response_code(404);
    echo json_encode(["success" => false, "message" => "Material not found"]);
    exit;
}

// Check access permissions
$hasAccess = false;
if ($user_type === 'professor') {
    $hasAccess = ($material['professor_id'] == $user_id);
} else {
    // Check if student is enrolled in the course
    $enrollmentStmt = $conn->prepare("SELECT id FROM course_enrollments WHERE course_id = ? AND student_id = ? AND status = 'active'");
    $enrollmentStmt->execute([$material['course_id'], $user_id]);
    $hasAccess = $enrollmentStmt->fetch() !== false;
}

if (!$hasAccess) {
    http_response_code(403);
    echo json_encode(["success" => false, "message" => "Access denied"]);
    exit;
}

$file_path = $material['file_path'];
if (!file_exists($file_path)) {
    http_response_code(404);
    echo json_encode(["success" => false, "message" => "File not found on server"]);
    exit;
}

// Award points for downloading material (only for students)
if ($user_type === 'student') {
    awardPoints($user_id, 'download_material');
}

// Set headers for download
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . basename($material['file_name']) . '"');
header('Content-Length: ' . filesize($file_path));
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

readfile($file_path);
exit;
