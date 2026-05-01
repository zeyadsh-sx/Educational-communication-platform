<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/gamification.php';

if (!isLoggedIn()) {
    die("Unauthorized");
}

$id = $_GET['id'] ?? 0;
$pdo = getDB();

$stmt = $pdo->prepare("SELECT * FROM materials WHERE id = ?");
$stmt->execute([$id]);
$file = $stmt->fetch();

if ($file && file_exists(__DIR__ . '/../' . $file['file_path'])) {
    // Award points to student if it's a student downloading
    if (isStudent()) {
        awardPoints(getCurrentUserId(), 'download_material');
    }
    
    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . basename($file['file_name']) . '"');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . filesize(__DIR__ . '/../' . $file['file_path']));
    readfile(__DIR__ . '/../' . $file['file_path']);
    exit;
} else {
    die("File not found.");
}
