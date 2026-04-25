<?php
require_once __DIR__ . '/database.php';

function getDB() {
    static $pdo = null;
    if ($pdo === null) {
        $database = new Database();
        $pdo = $database->connect();
    }
    return $pdo;
}

function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

function getCurrentUserId() {
    return $_SESSION['user_id'] ?? null;
}

function getCurrentUserType() {
    return $_SESSION['user_type'] ?? null;
}

function isProfessor() {
    return getCurrentUserType() === 'professor';
}

function isStudent() {
    return getCurrentUserType() === 'student';
}

function redirect($url) {
    header("Location: $url");
    exit;
}

function showError($message) {
    return '<div class="alert alert-error">' . htmlspecialchars($message) . '</div>';
}

function showSuccess($message) {
    return '<div class="alert alert-success">' . htmlspecialchars($message) . '</div>';
}

function sanitize($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

function formatDate($date) {
    return date('Y-m-d H:i', strtotime($date));
}