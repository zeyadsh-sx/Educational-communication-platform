<?php
// Test database connection
try {
    $pdo = new PDO("mysql:host=localhost", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "MySQL connection successful!<br>";
    
    // Check if database exists
    $stmt = $pdo->query("SHOW DATABASES LIKE 'educational_platform'");
    if ($stmt->rowCount() > 0) {
        echo "Database 'educational_platform' exists.<br>";
    } else {
        echo "Database 'educational_platform' does not exist. Creating...<br>";
        $pdo->exec("CREATE DATABASE educational_platform CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        echo "Database created successfully!<br>";
    }
    
    // Test connection to the database
    $pdo->exec("USE educational_platform");
    echo "Connected to database successfully!<br>";
    
} catch(PDOException $e) {
    echo "Database Error: " . $e->getMessage() . "<br>";
}

// Test PHP extensions
echo "<br>PHP Extensions Check:<br>";
echo "PDO: " . (extension_loaded('pdo') ? "✓" : "✗") . "<br>";
echo "PDO MySQL: " . (extension_loaded('pdo_mysql') ? "✓" : "✗") . "<br>";
echo "Session: " . (extension_loaded('session') ? "✓" : "✗") . "<br>";
echo "JSON: " . (extension_loaded('json') ? "✓" : "✗") . "<br>";
?>
