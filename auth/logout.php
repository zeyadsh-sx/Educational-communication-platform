<?php
session_start();
require_once __DIR__ . '/../includes/functions.php';

$_SESSION = [];

if (session_destroy()) {
    redirect('/auth/login.php');
    exit;
} else {
    redirect('/index.php');
    exit;
}
