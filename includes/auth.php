<?php
session_start();

function isLoggedIn() {
    return isset($_SESSION['user']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        http_response_code(401);
        echo json_encode(["error" => "Unauthorized"]);
        exit;
    }
}

function currentUser() {
    return $_SESSION['user'] ?? null;
}

function loginUser($user) {
    $_SESSION['user'] = $user;
}

function logoutUser() {
    session_destroy();
}
?>