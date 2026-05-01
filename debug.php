<?php
// Comprehensive debugging script
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Educational Platform Debug Report</h1>";

// 1. PHP Version and Extensions
echo "<h2>PHP Environment</h2>";
echo "PHP Version: " . phpversion() . "<br>";
echo "Required Extensions:<br>";
echo "- PDO: " . (extension_loaded('pdo') ? "✓" : "✗ MISSING") . "<br>";
echo "- PDO MySQL: " . (extension_loaded('pdo_mysql') ? "✓" : "✗ MISSING") . "<br>";
echo "- Session: " . (extension_loaded('session') ? "✓" : "✗ MISSING") . "<br>";
echo "- JSON: " . (extension_loaded('json') ? "✓" : "✗ MISSING") . "<br>";
echo "- MBString: " . (extension_loaded('mbstring') ? "✓" : "✗ MISSING") . "<br>";

// 2. File Structure Check
echo "<h2>File Structure</h2>";
$required_files = [
    'config/database.php',
    'includes/functions.php',
    'includes/header.php',
    'includes/footer.php',
    'lang/ar.php',
    'lang/en.php',
    'css/style.css',
    'index.php'
];

foreach ($required_files as $file) {
    echo "- $file: " . (file_exists($file) ? "✓" : "✗ MISSING") . "<br>";
}

// 3. Database Connection
echo "<h2>Database Connection</h2>";
try {
    $pdo = new PDO("mysql:host=localhost", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "MySQL Connection: ✓<br>";
    
    // Check database
    $stmt = $pdo->query("SHOW DATABASES LIKE 'educational_platform'");
    if ($stmt->rowCount() > 0) {
        echo "Database 'educational_platform': ✓<br>";
        
        // Check tables
        $pdo->exec("USE educational_platform");
        $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
        echo "Tables found: " . implode(", ", $tables) . "<br>";
    } else {
        echo "Database 'educational_platform': ✗ NOT FOUND<br>";
    }
} catch(PDOException $e) {
    echo "Database Error: " . $e->getMessage() . "<br>";
}

// 4. Session Test
echo "<h2>Session Test</h2>";
session_start();
echo "Session Status: " . (session_status() === PHP_SESSION_ACTIVE ? "✓ Active" : "✗ Inactive") . "<br>";
$_SESSION['test'] = 'working';
echo "Session Write: ✓<br>";

// 5. Language Files
echo "<h2>Language Files</h2>";
$lang_files = ['ar.php', 'en.php'];
foreach ($lang_files as $file) {
    $path = "lang/$file";
    if (file_exists($path)) {
        $lang_data = include $path;
        echo "- $file: ✓ (" . count($lang_data) . " translations)<br>";
    } else {
        echo "- $file: ✗ MISSING<br>";
    }
}

// 6. Include Tests
echo "<h2>Include Tests</h2>";
try {
    require_once 'config/database.php';
    echo "config/database.php: ✓<br>";
} catch(Exception $e) {
    echo "config/database.php: ✗ " . $e->getMessage() . "<br>";
}

try {
    require_once 'includes/functions.php';
    echo "includes/functions.php: ✓<br>";
} catch(Exception $e) {
    echo "includes/functions.php: ✗ " . $e->getMessage() . "<br>";
}

// 7. Permissions
echo "<h2>File Permissions</h2>";
$dirs = ['uploads/', 'logs/'];
foreach ($dirs as $dir) {
    if (is_dir($dir)) {
        echo "- $dir: ✓ Writable<br>";
    } else {
        echo "- $dir: ✗ Not writable or missing<br>";
    }
}

echo "<h2>Next Steps</h2>";
echo "<p>If any items show ✗, fix them before proceeding with the application.</p>";
echo "<p><a href='setup_database.php'>Run Database Setup</a> | <a href='index.php'>Test Main App</a></p>";
?>
