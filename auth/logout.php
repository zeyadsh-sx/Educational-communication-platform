<?php
session_start();

$_SESSION = [];

if (session_destroy()) {
    header('Location: /auth/login.php');
    exit;
} else {
    header('Location: /index.php');
    exit;
}
