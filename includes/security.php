<?php
/**
 * Security Helper Functions
 * Secure input validation and sanitization
 */

/**
 * Safely get GET parameter with validation
 */
function getSafeGet($key, $default = null, $type = 'string') {
    if (!isset($_GET[$key])) {
        return $default;
    }
    
    $value = $_GET[$key];
    
    switch ($type) {
        case 'int':
            return filter_var($value, FILTER_VALIDATE_INT) !== false ? intval($value) : $default;
        case 'email':
            return filter_var($value, FILTER_VALIDATE_EMAIL) ? $value : $default;
        case 'url':
            return filter_var($value, FILTER_VALIDATE_URL) ? $value : $default;
        case 'string':
        default:
            return trim(htmlspecialchars($value, ENT_QUOTES, 'UTF-8'));
    }
}

/**
 * Safely get POST parameter with validation
 */
function getSafePost($key, $default = null, $type = 'string') {
    if (!isset($_POST[$key])) {
        return $default;
    }
    
    $value = $_POST[$key];
    
    switch ($type) {
        case 'int':
            return filter_var($value, FILTER_VALIDATE_INT) !== false ? intval($value) : $default;
        case 'email':
            return filter_var($value, FILTER_VALIDATE_EMAIL) ? $value : $default;
        case 'url':
            return filter_var($value, FILTER_VALIDATE_URL) ? $value : $default;
        case 'string':
        default:
            return trim(htmlspecialchars($value, ENT_QUOTES, 'UTF-8'));
    }
}

/**
 * Safely get SESSION value
 */
function getSafeSession($key, $default = null) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    return isset($_SESSION[$key]) ? $_SESSION[$key] : $default;
}

/**
 * Get user ID from session safely
 */
function getSafeUserId() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    return isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : null;
}

/**
 * Validate file upload
 */
function validateFileUpload($fileKey, $allowedExtensions = [], $maxSize = 10485760) {
    if (!isset($_FILES[$fileKey])) {
        return ['valid' => false, 'error' => 'لم يتم رفع ملف'];
    }
    
    $file = $_FILES[$fileKey];
    
    // Check for upload errors
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $errors = [
            UPLOAD_ERR_INI_SIZE => 'حجم الملف يتجاوز الحد المسموح به',
            UPLOAD_ERR_FORM_SIZE => 'حجم الملف يتجاوز الحد المسموح به',
            UPLOAD_ERR_PARTIAL => 'لم يتم رفع الملف بالكامل',
            UPLOAD_ERR_NO_FILE => 'لم يتم رفع ملف',
            UPLOAD_ERR_NO_TMP_DIR => 'خطأ في الخادم',
            UPLOAD_ERR_CANT_WRITE => 'خطأ في الخادم',
            UPLOAD_ERR_EXTENSION => 'الملف مرفوع بسبب أثناء المعالجة'
        ];
        return ['valid' => false, 'error' => $errors[$file['error']] ?? 'خطأ غير معروف'];
    }
    
    // Check file size
    if ($file['size'] > $maxSize) {
        return ['valid' => false, 'error' => 'حجم الملف كبير جداً'];
    }
    
    // Check file extension
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!empty($allowedExtensions) && !in_array($ext, $allowedExtensions)) {
        return ['valid' => false, 'error' => 'نوع الملف غير مسموح'];
    }
    
    return ['valid' => true, 'file' => $file, 'extension' => $ext];
}

/**
 * Validate date format (Y-m-d or d/m/Y)
 */
function validateDate($date, $format = 'Y-m-d') {
    $d = DateTime::createFromFormat($format, $date);
    return $d && $d->format($format) === $date;
}

/**
 * Validate time format (H:i or H:i:s)
 */
function validateTime($time) {
    $pattern = '/^([0-1][0-9]|2[0-3]):[0-5][0-9](:[0-5][0-9])?$/';
    return preg_match($pattern, $time) === 1;
}

/**
 * Log errors securely
 */
function logError($message, $context = []) {
    $logDir = __DIR__ . '/../logs';
    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }
    
    $logFile = $logDir . '/error-' . date('Y-m-d') . '.log';
    $timestamp = date('Y-m-d H:i:s');
    
    $logEntry = "[$timestamp] $message";
    if (!empty($context)) {
        $logEntry .= "\nContext: " . json_encode($context);
    }
    $logEntry .= "\n---\n";
    
    error_log($logEntry, 3, $logFile);
}

/**
 * Respond with JSON and exit
 */
function jsonResponse($status, $data = [], $httpCode = 200) {
    http_response_code($httpCode);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'status' => $status,
        'data' => $data
    ]);
    exit;
}

/**
 * Respond with error
 */
function jsonError($message, $code = 400) {
    jsonResponse('error', ['message' => $message], $code);
}

/**
 * Respond with success
 */
function jsonSuccess($data = [], $message = 'تم بنجاح') {
    jsonResponse('success', array_merge(['message' => $message], $data), 200);
}

/**
 * Check if user owns resource
 */
function userOwnsResource($userId, $resourceUserId) {
    return intval($userId) === intval($resourceUserId);
}

/**
 * CSRF Token generation and validation
 */
function generateCSRFToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function validateCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Password hashing
 */
function hashPassword($password) {
    return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
}

function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

/**
 * Sanitize file path to prevent traversal
 */
function sanitizeFilePath($path) {
    // Remove directory traversal attempts
    $path = str_replace(['..', '\\'], ['', '/'], $path);
    // Remove special characters that could be problematic
    $path = preg_replace('/[^a-zA-Z0-9._\/-]/', '', $path);
    return $path;
}

/**
 * Generate safe filename
 */
function generateSafeFilename($originalName) {
    $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
    $name = pathinfo($originalName, PATHINFO_FILENAME);
    $name = preg_replace('/[^a-zA-Z0-9_-]/', '', $name);
    $name = substr($name, 0, 50);
    return $name . '_' . time() . '.' . $ext;
}

/**
 * Rate limiting helper
 */
class RateLimiter {
    private $pdo;
    private $limit;
    private $window;
    
    public function __construct($pdo, $limit = 10, $window = 60) {
        $this->pdo = $pdo;
        $this->limit = $limit;
        $this->window = $window;
    }
    
    public function isAllowed($identifier) {
        $now = time();
        $windowStart = $now - $this->window;
        
        // Note: This requires a rate_limit_attempts table
        // CREATE TABLE rate_limit_attempts (
        //     id INT AUTO_INCREMENT PRIMARY KEY,
        //     identifier VARCHAR(255),
        //     attempt_time INT,
        //     INDEX idx_identifier_time (identifier, attempt_time)
        // );
        
        try {
            $stmt = $this->pdo->prepare("
                SELECT COUNT(*) FROM rate_limit_attempts 
                WHERE identifier = ? AND attempt_time > ?
            ");
            $stmt->execute([$identifier, $windowStart]);
            $count = $stmt->fetchColumn();
            
            if ($count >= $this->limit) {
                return false;
            }
            
            $stmt = $this->pdo->prepare("
                INSERT INTO rate_limit_attempts (identifier, attempt_time) 
                VALUES (?, ?)
            ");
            $stmt->execute([$identifier, $now]);
            
            return true;
        } catch (Exception $e) {
            logError('Rate limiter error', ['error' => $e->getMessage()]);
            return true; // Fail open
        }
    }
}
