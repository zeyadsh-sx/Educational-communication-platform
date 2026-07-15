<?php
/**
 * API — طرح سؤال
 * POST /api/questions/ask.php
 */
if (session_status() === PHP_SESSION_NONE) session_start();
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';

if (!isLoggedIn()) { http_response_code(401); echo json_encode(['success'=>false,'message'=>'Unauthorized']); exit; }

$userId   = (int)($_SESSION['user_id']   ?? 0);
$userType = $_SESSION['user_type'] ?? '';

if ($userType !== 'student') {
    http_response_code(403); echo json_encode(['success'=>false,'message'=>'Students only']); exit;
}

$data = json_decode(file_get_contents('php://input'), true);
if (!$data || empty($data['course_id']) || empty(trim($data['question'] ?? ''))) {
    http_response_code(400); echo json_encode(['success'=>false,'message'=>'Missing required fields']); exit;
}

$courseId     = (int)$data['course_id'];
$questionText = trim($data['question']);
$pdo          = getDB();

// Verify enrollment
$enrStmt = $pdo->prepare("SELECT id FROM course_enrollments WHERE course_id=? AND student_id=? AND status='active'");
$enrStmt->execute([$courseId, $userId]);
if (!$enrStmt->fetch()) {
    http_response_code(403); echo json_encode(['success'=>false,'message'=>'Not enrolled in this course']); exit;
}

// Get professor
$cStmt = $pdo->prepare("SELECT professor_id FROM courses WHERE id=?");
$cStmt->execute([$courseId]);
$course = $cStmt->fetch();
if (!$course) { http_response_code(404); echo json_encode(['success'=>false,'message'=>'Course not found']); exit; }

$pdo->prepare("INSERT INTO questions (question_text,student_id,professor_id,course_id,status,created_at) VALUES (?,?,?,?,'pending',NOW())")
    ->execute([$questionText, $userId, $course['professor_id'], $courseId]);

// Award points
if (function_exists('awardPoints')) {
    require_once __DIR__ . '/../../includes/gamification.php';
    awardPoints($userId, 'ask_question');
}

echo json_encode(['success'=>true,'message'=>'Question submitted successfully']);
