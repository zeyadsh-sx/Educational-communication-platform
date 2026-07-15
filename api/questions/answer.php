<?php
/**
 * API — الرد على سؤال
 * POST /api/questions/answer.php
 */
if (session_status() === PHP_SESSION_NONE) session_start();
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';

if (!isLoggedIn()) { http_response_code(401); echo json_encode(['success'=>false,'message'=>'Unauthorized']); exit; }

$userId   = (int)($_SESSION['user_id']   ?? 0);
$userType = $_SESSION['user_type'] ?? '';

if ($userType !== 'professor') {
    http_response_code(403); echo json_encode(['success'=>false,'message'=>'Professors only']); exit;
}

$data = json_decode(file_get_contents('php://input'), true);
if (!$data || empty($data['question_id']) || empty(trim($data['answer'] ?? ''))) {
    http_response_code(400); echo json_encode(['success'=>false,'message'=>'Missing required fields']); exit;
}

$questionId = (int)$data['question_id'];
$answerText = trim($data['answer']);
$pdo        = getDB();

// Verify ownership + pending status
$qStmt = $pdo->prepare("SELECT student_id, course_id FROM questions WHERE id=? AND professor_id=? AND status='pending'");
$qStmt->execute([$questionId, $userId]);
$question = $qStmt->fetch();
if (!$question) {
    http_response_code(404); echo json_encode(['success'=>false,'message'=>'Question not found or already answered']); exit;
}

// Update — use answer_text column (correct column name)
$pdo->prepare("UPDATE questions SET answer_text=?, status='answered', answered_at=NOW() WHERE id=?")
    ->execute([$answerText, $questionId]);

// Notify student
$cStmt = $pdo->prepare("SELECT course_name FROM courses WHERE id=?");
$cStmt->execute([$question['course_id']]);
$courseName = $cStmt->fetchColumn() ?: 'الكورس';
require_once __DIR__ . '/../../includes/subscription_functions.php';
sendSystemNotification($question['student_id'], "✅ تم الرد على سؤالك في $courseName", 'general');

// Award points to professor
require_once __DIR__ . '/../../includes/gamification.php';
awardPoints($userId, 'answer_question');

echo json_encode(['success'=>true,'message'=>'Answer submitted successfully']);
