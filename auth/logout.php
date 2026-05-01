<?php
session_start();

$_SESSION = [];

if (session_destroy()) {
    redirect('/auth/login.php');
    exit;
} else {
    redirect('/index.php');
    exit;
}
