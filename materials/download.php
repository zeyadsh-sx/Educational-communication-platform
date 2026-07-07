<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/security.php';
require_once __DIR__ . '/../includes/gamification.php';

// Check authentication
if (!isLoggedIn()) {
    http_response_code(401);
    die("Unauthorized");
}

try {
    $materialId = getSafeGet('id', null, 'int');
    
    if (!$materialId || $materialId <= 0) {
        http_response_code(400);
        die("معرف الملف غير صحيح");
    }
    
    $pdo = getDB();
    
    // Get material details
    $stmt = $pdo->prepare("
        SELECT m.id, m.file_path, m.file_name, m.course_id, c.professor_id
        FROM materials m
        JOIN courses c ON m.course_id = c.id
        WHERE m.id = ?
    ");
    $stmt->execute([$materialId]);
    $material = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$material) {
        http_response_code(404);
        die("الملف غير موجود");
    }
    
    // Check access permissions
    $userId = getSafeUserId();
    $isEnrolled = false;
    
    if (isStudent()) {
        // Check if student is enrolled in this course
        $enrollStmt = $pdo->prepare("
            SELECT id FROM course_enrollments 
            WHERE course_id = ? AND student_id = ?
        ");
        $enrollStmt->execute([$material['course_id'], $userId]);
        $isEnrolled = $enrollStmt->fetch() !== false;
    }
    
    $isProfessor = $material['professor_id'] == $userId;
    
    if (!$isEnrolled && !$isProfessor) {
        http_response_code(403);
        die("أنت لا تملك صلاحية الوصول إلى هذا الملف");
    }
    
    // Sanitize file path to prevent traversal
    $sanitizedPath = sanitizeFilePath($material['file_path']);
    $baseDir = realpath(__DIR__ . '/../uploads/materials');
    $fullPath = realpath($baseDir . '/' . $sanitizedPath);
    
    // Verify file is within uploads directory
    if (!$fullPath || strpos($fullPath, $baseDir) !== 0 || !file_exists($fullPath)) {
        logError('File traversal attempt', [
            'user_id' => $userId,
            'material_id' => $materialId,
            'requested_path' => $material['file_path']
        ]);
        http_response_code(404);
        die("الملف غير موجود");
    }
    
    // Award points to student if it's a student downloading
    if (isStudent() && $isEnrolled) {
        awardPoints($userId, 'download_material');
    }
    
    // Log download
    $logStmt = $pdo->prepare("
        INSERT INTO material_downloads (material_id, user_id, downloaded_at) 
        VALUES (?, ?, NOW())
    ");
    try {
        $logStmt->execute([$materialId, $userId]);
    } catch (Exception $e) {
        logError('Error logging download', ['error' => $e->getMessage()]);
    }
    
    // Get file info
    $fileSize = filesize($fullPath);
    $fileName = basename($material['file_name']);
    $ext = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION));
    
    // Determine MIME type
    $mimeTypes = [
        'pdf' => 'application/pdf',
        'doc' => 'application/msword',
        'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'xls' => 'application/vnd.ms-excel',
        'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'ppt' => 'application/vnd.ms-powerpoint',
        'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        'zip' => 'application/zip',
        'txt' => 'text/plain',
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png' => 'image/png',
        'gif' => 'image/gif',
        'mp4' => 'video/mp4',
        'mp3' => 'audio/mpeg'
    ];
    
    $mimeType = $mimeTypes[$ext] ?? 'application/octet-stream';
    
    // Set headers
    header('Content-Type: ' . $mimeType);
    header('Content-Disposition: attachment; filename="' . addslashes($fileName) . '"');
    header('Content-Length: ' . $fileSize);
    header('Cache-Control: no-cache, no-store, must-revalidate');
    header('Pragma: no-cache');
    header('Expires: 0');
    
    // Stream file in chunks to prevent memory exhaustion
    $chunkSize = 1024 * 1024; // 1MB chunks
    $handle = fopen($fullPath, 'rb');
    
    if (!$handle) {
        http_response_code(500);
        die("لا يمكن فتح الملف");
    }
    
    while (!feof($handle)) {
        $chunk = fread($handle, $chunkSize);
        if ($chunk === false) {
            break;
        }
        echo $chunk;
        flush();
    }
    
    fclose($handle);
    exit;
    
} catch (Exception $e) {
    logError('Download error', ['error' => $e->getMessage()]);
    http_response_code(500);
    die("حدث خطأ في الخادم");
}
