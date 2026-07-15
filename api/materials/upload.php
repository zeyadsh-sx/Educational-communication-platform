<?php
/**
 * API — رفع مادة دراسية (base64)
 * POST /api/materials/upload.php
 * Body JSON: { course_id, file: { name, content (base64) } }
 */
if (session_status() === PHP_SESSION_NONE) session_start();
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';

if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$userId   = (int)($_SESSION['user_id']   ?? 0);
$userType = $_SESSION['user_type'] ?? '';

if ($userType !== 'professor') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Only professors can upload materials']);
    exit;
}

$data     = json_decode(file_get_contents('php://input'), true);
$courseId = (int)($data['course_id'] ?? 0);
$fileData = $data['file'] ?? null;

if (!$courseId || !$fileData || empty($fileData['name']) || empty($fileData['content'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Course ID and file data are required']);
    exit;
}

$pdo = getDB();

// Verify professor owns the course
$chk = $pdo->prepare("SELECT id FROM courses WHERE id=? AND professor_id=?");
$chk->execute([$courseId, $userId]);
if (!$chk->fetch()) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => "You don't own this course"]);
    exit;
}

// Decode and validate
$fileContent = base64_decode($fileData['content'], true);
if ($fileContent === false) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid file data (base64 decode failed)']);
    exit;
}

// Validate extension
$originalName = basename($fileData['name']);
$ext          = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
$allowed      = ['pdf','doc','docx','ppt','pptx','txt','zip','xls','xlsx'];
if (!in_array($ext, $allowed)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'File type not allowed']);
    exit;
}

// Save file
$uploadDir = __DIR__ . '/../../uploads/materials';
if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

$safeBase = preg_replace('/[^a-zA-Z0-9_-]/', '', pathinfo($originalName, PATHINFO_FILENAME));
$fileName = ($safeBase ?: 'file') . '_' . time() . '.' . $ext;
$filePath = $uploadDir . '/' . $fileName;

if (file_put_contents($filePath, $fileContent) === false) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to save file']);
    exit;
}

// Insert DB record
try {
    $pdo->prepare("
        INSERT INTO materials (title, file_name, file_path, file_type, course_id, professor_id, uploaded_by, upload_date)
        VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
    ")->execute([
        $originalName,
        $originalName,
        'uploads/materials/' . $fileName,
        $ext,
        $courseId,
        $userId,
        $userId,
    ]);

    echo json_encode(['success' => true, 'message' => 'Material uploaded successfully']);
} catch (Exception $e) {
    // Clean up file on DB failure
    @unlink($filePath);
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to save material info']);
}
