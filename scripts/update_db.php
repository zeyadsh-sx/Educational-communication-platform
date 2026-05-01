<?php
require_once __DIR__ . '/../config/database.php';

try {
    $pdo = getDB();
    
    // Add columns to users table
    $pdo->exec("ALTER TABLE users ADD COLUMN IF NOT EXISTS points INT DEFAULT 0");
    $pdo->exec("ALTER TABLE users ADD COLUMN IF NOT EXISTS profile_image VARCHAR(255) DEFAULT NULL");
    
    // Create achievements table
    $pdo->exec("CREATE TABLE IF NOT EXISTS student_achievements (
        id INT PRIMARY KEY AUTO_INCREMENT,
        student_id INT,
        achievement_name VARCHAR(100),
        achievement_icon VARCHAR(50),
        earned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE
    )");
    
    echo "Database updated successfully!";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
