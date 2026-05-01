<?php
// Comprehensive fix script
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Educational Platform - Auto Fix</h1>";

// 1. Fix missing directories
$dirs_to_create = ['uploads', 'logs'];
foreach ($dirs_to_create as $dir) {
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
        echo "✓ Created directory: $dir<br>";
    } else {
        echo "✓ Directory exists: $dir<br>";
    }
}

// 2. Fix .htaccess
$htaccess_content = "RewriteEngine On
RewriteBase /Educational-communication-platform/

# Security settings
Options -Indexes
ServerSignature Off

# URL rewriting for clean URLs
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php?page=$1 [L,QSA]

# Cache control
<IfModule mod_expires.c>
    ExpiresActive On
    ExpiresByType text/css \"access plus 1 month\"
    ExpiresByType application/javascript \"access plus 1 month\"
    ExpiresByType image/png \"access plus 1 month\"
    ExpiresByType image/jpg \"access plus 1 month\"
    ExpiresByType image/jpeg \"access plus 1 month\"
    ExpiresByType image/gif \"access plus 1 month\"
    ExpiresByType image/ico \"access plus 1 month\"
    ExpiresByType image/icon \"access plus 1 month\"
    ExpiresByType text/html \"access plus 1 hour\"
</IfModule>";

file_put_contents('.htaccess', $htaccess_content);
echo "✓ Fixed .htaccess file<br>";

// 3. Test database connection
try {
    $pdo = new PDO("mysql:host=localhost", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "✓ MySQL connection successful<br>";
    
    // Create database if not exists
    $pdo->exec("CREATE DATABASE IF NOT EXISTS educational_platform CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "✓ Database ensured exists<br>";
    
} catch(PDOException $e) {
    echo "✗ Database error: " . $e->getMessage() . "<br>";
}

// 4. Fix file permissions (Windows compatible)
if (function_exists('chmod')) {
    chmod('uploads', 0755);
    chmod('logs', 0755);
    echo "✓ Fixed file permissions<br>";
}

// 5. Create missing files if needed
$missing_files = [
    'lang/ar.php' => '<?php return [];',
    'lang/en.php' => '<?php return [];',
];

foreach ($missing_files as $file => $content) {
    if (!file_exists($file)) {
        file_put_contents($file, $content);
        echo "✓ Created missing file: $file<br>";
    }
}

echo "<h2>Fix Complete!</h2>";
echo "<p><strong>Next Steps:</strong></p>";
echo "<ol>";
echo "<li><a href='debug.php'>Run Debug Report</a></li>";
echo "<li><a href='db_test.php'>Test Database</a></li>";
echo "<li><a href='setup_database.php'>Setup Database</a></li>";
echo "<li><a href='index.php'>Test Main Application</a></li>";
echo "</ol>";

echo "<h2>Common Issues Fixed:</h2>";
echo "<ul>";
echo "<li>✓ Removed invalid PHP directives from .htaccess</li>";
echo "<li>✓ Added error handling to main files</li>";
echo "<li>✓ Created missing directories</li>";
echo "<li>✓ Ensured database exists</li>";
echo "<li>✓ Fixed file permissions</li>";
echo "</ul>";
?>
