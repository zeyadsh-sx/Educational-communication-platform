<?php
// Application Settings

// Site Information
define('SITE_NAME', 'منصة الاتصال التعليمي');
define('SITE_URL', 'http://localhost');

// Database Settings
define('DB_HOST', 'localhost');
define('DB_NAME', 'educational_platform');
define('DB_USER', 'root');
define('DB_PASS', '');

// File Upload Settings
define('UPLOAD_DIR', __DIR__ . '/../uploads/');
define('MAX_FILE_SIZE', 10485760); // 10MB in bytes
define('ALLOWED_FILE_TYPES', ['pdf', 'doc', 'docx', 'ppt', 'pptx', 'txt', 'zip', 'jpg', 'jpeg', 'png']);

// Session Settings
define('SESSION_LIFETIME', 3600); // 1 hour in seconds

// Pagination
define('ITEMS_PER_PAGE', 20);

// Timezone
date_default_timezone_set('Africa/Cairo');

// Error Reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Security
define('CSRF_TOKEN_NAME', 'csrf_token');
?>
