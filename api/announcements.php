<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/security.php';
require_once __DIR__ . '/../includes/auth.php';

try {
    $pdo = getDB();
    
    // Check if user is logged in
    $userId = getSafeSession('user_id');
    if (!$userId) {
        jsonError('يجب تسجيل الدخول أولاً', 401);
    }
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Add announcement
        $title = getSafePost('title', '', 'string');
        $content = getSafePost('content', '', 'string');
        $courseId = getSafePost('course_id', null, 'int');
        
        // Validate input
        if (empty($title) || strlen($title) < 3) {
            jsonError('العنوان يجب أن يكون على الأقل 3 أحرف');
        } elseif (empty($content) || strlen($content) < 5) {
            jsonError('المحتوى يجب أن يكون على الأقل 5 أحرف');
        } elseif (!$courseId || $courseId <= 0) {
            jsonError('معرف الكورس غير صحيح');
        }
        
        // Check if user is professor of this course
        $checkStmt = $pdo->prepare("
            SELECT id FROM courses 
            WHERE id = ? AND professor_id = ?
        ");
        $checkStmt->execute([$courseId, $userId]);
        
        if (!$checkStmt->fetch()) {
            jsonError('أنت لا تملك صلاحية إضافة إعلانات لهذا الكورس', 403);
        }
        
        // Insert announcement
        $stmt = $pdo->prepare("
            INSERT INTO announcements (course_id, title, content, created_by, created_at) 
            VALUES (?, ?, ?, ?, NOW())
        ");
        $stmt->execute([$courseId, $title, $content, $userId]);
        
        jsonSuccess(['id' => $pdo->lastInsertId()], 'تم إنشاء الإعلان بنجاح');
        
    } else {
        // Get announcements
        $courseId = getSafeGet('course_id', null, 'int');
        
        if (!$courseId || $courseId <= 0) {
            jsonError('معرف الكورس غير صحيح');
        }
        
        // Check if user has access to this course
        $checkStmt = $pdo->prepare("
            SELECT c.id FROM courses c
            LEFT JOIN course_enrollments ce ON c.id = ce.course_id
            WHERE c.id = ? AND (c.professor_id = ? OR ce.student_id = ?)
        ");
        $checkStmt->execute([$courseId, $userId, $userId]);
        
        if (!$checkStmt->fetch()) {
            jsonError('أنت لا تملك صلاحية الوصول إلى هذا الكورس', 403);
        }
        
        // Get announcements
        $stmt = $pdo->prepare("
            SELECT 
                a.id, a.course_id, a.title, a.content, a.created_at,
                u.full_name as created_by_name,
                u.id as created_by_id
            FROM announcements a
            JOIN users u ON a.created_by = u.id
            WHERE a.course_id = ?
            ORDER BY a.created_at DESC
        ");
        $stmt->execute([$courseId]);
        
        $announcements = $stmt->fetchAll(PDO::FETCH_ASSOC);
        jsonResponse('success', ['announcements' => $announcements], 200);
    }
    
} catch (PDOException $e) {
    logError('API Announcements Error', ['error' => $e->getMessage()]);
    jsonError('حدث خطأ في الخادم', 500);
}
