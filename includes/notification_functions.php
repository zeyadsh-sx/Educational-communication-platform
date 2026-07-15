<?php
/**
 * دوال الإشعارات — تستخدم getDB() singleton بدلاً من إنشاء اتصال جديد
 */
require_once __DIR__ . '/../config/database.php';

/**
 * إرسال إشعار بسيط (بدون نوع)
 * للتوافق مع الكود القديم الذي يستدعي sendNotification()
 */
function sendNotification(int $user_id, string $message): void
{
    $pdo = getDB();
    $pdo->prepare("INSERT INTO notifications (user_id, message) VALUES (?, ?)")
        ->execute([$user_id, $message]);
}

/**
 * عدد الإشعارات غير المقروءة لمستخدم معين
 */
function getUnreadNotificationsCount(int $userId): int
{
    $pdo  = getDB();
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0");
    $stmt->execute([$userId]);
    return (int)$stmt->fetchColumn();
}
