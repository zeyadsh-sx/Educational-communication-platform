<?php
/**
 * questions/answer.php — إجابة على سؤال طالب (ريديريكت للصفحة الجديدة)
 * هذا الملف كان مبني على النظام القديم، الآن يُوجّه للصفحة الصحيحة
 */
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

if (!isLoggedIn()) {
    redirect('/auth/login.php');
    exit;
}

if (!isProfessor()) {
    redirect('/student/dashboard.php');
    exit;
}

// وجّه للصفحة الجديدة مع الحفاظ على الـ question id anchor
$qId  = (int)($_GET['id'] ?? 0);
$base = getBaseUrl();

header('Location: ' . $base . '/professor/questions.php' . ($qId ? '#q-' . $qId : ''));
exit;
