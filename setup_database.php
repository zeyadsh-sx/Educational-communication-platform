<?php
// Database setup script
try {
    // Connect to MySQL without database name
    $pdo = new PDO("mysql:host=localhost", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Create database if not exists
    $pdo->exec("CREATE DATABASE IF NOT EXISTS educational_platform CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "Database 'educational_platform' created successfully!<br>";
    
    // Switch to the created database
    $pdo->exec("USE educational_platform");
    
    // Import schema
    $schema = file_get_contents('database_schema.sql');
    $pdo->exec($schema);
    echo "Database schema imported successfully!<br>";
    
    // Import seed data
    $seed = file_get_contents('database_seed.sql');
    $pdo->exec($seed);
    echo "Seed data imported successfully!<br>";
    
    echo "<h3>Setup completed successfully!</h3>";
    echo "<p>You can now access the application at: <a href='index.php'>index.php</a></p>";
    
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
