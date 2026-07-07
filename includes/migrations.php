<?php
/**
 * Database Migration Helper
 * Run migrations to update database schema
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/security.php';

try {
    $pdo = getDB();
    
    // Migration 1: Create material_downloads table if not exists
    $sql = "
    CREATE TABLE IF NOT EXISTS material_downloads (
        id INT AUTO_INCREMENT PRIMARY KEY,
        material_id INT NOT NULL,
        user_id INT NOT NULL,
        downloaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (material_id) REFERENCES materials(id) ON DELETE CASCADE,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        INDEX idx_material_user (material_id, user_id),
        INDEX idx_downloaded_at (downloaded_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ";
    
    try {
        $pdo->exec($sql);
        echo "✓ material_downloads table created successfully\n";
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), 'already exists') !== false) {
            echo "ℹ material_downloads table already exists\n";
        } else {
            throw $e;
        }
    }
    
    // Migration 2: Create rate_limit_attempts table if not exists
    $sql = "
    CREATE TABLE IF NOT EXISTS rate_limit_attempts (
        id INT AUTO_INCREMENT PRIMARY KEY,
        identifier VARCHAR(255) NOT NULL,
        attempt_time INT NOT NULL,
        INDEX idx_identifier_time (identifier, attempt_time)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ";
    
    try {
        $pdo->exec($sql);
        echo "✓ rate_limit_attempts table created successfully\n";
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), 'already exists') !== false) {
            echo "ℹ rate_limit_attempts table already exists\n";
        } else {
            throw $e;
        }
    }
    
    // Migration 3: Ensure users table has necessary columns
    $checkColumns = $pdo->prepare("
        SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS 
        WHERE TABLE_NAME = 'users' AND TABLE_SCHEMA = DATABASE()
    ");
    $checkColumns->execute();
    $columns = array_map(fn($row) => $row['COLUMN_NAME'], $checkColumns->fetchAll(PDO::FETCH_ASSOC));
    
    // Add missing columns if needed
    if (!in_array('created_at', $columns)) {
        try {
            $pdo->exec("ALTER TABLE users ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP");
            echo "✓ Added created_at column to users table\n";
        } catch (PDOException $e) {
            echo "ℹ created_at column already exists in users table\n";
        }
    }
    
    // Migration 4: Ensure materials table has necessary columns
    $checkMaterials = $pdo->prepare("
        SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS 
        WHERE TABLE_NAME = 'materials' AND TABLE_SCHEMA = DATABASE()
    ");
    $checkMaterials->execute();
    $materialColumns = array_map(fn($row) => $row['COLUMN_NAME'], $checkMaterials->fetchAll(PDO::FETCH_ASSOC));
    
    if (!in_array('created_at', $materialColumns)) {
        try {
            $pdo->exec("ALTER TABLE materials ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP");
            echo "✓ Added created_at column to materials table\n";
        } catch (PDOException $e) {
            echo "ℹ created_at column already exists in materials table\n";
        }
    }
    
    // Migration 5: Ensure announcements table has necessary columns
    $checkAnnouncements = $pdo->prepare("
        SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS 
        WHERE TABLE_NAME = 'announcements' AND TABLE_SCHEMA = DATABASE()
    ");
    $checkAnnouncements->execute();
    $announcementColumns = array_map(fn($row) => $row['COLUMN_NAME'], $checkAnnouncements->fetchAll(PDO::FETCH_ASSOC));
    
    if (!in_array('created_at', $announcementColumns)) {
        try {
            $pdo->exec("ALTER TABLE announcements ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP");
            echo "✓ Added created_at column to announcements table\n";
        } catch (PDOException $e) {
            echo "ℹ created_at column already exists in announcements table\n";
        }
    }
    
    echo "\n✓ All migrations completed successfully!\n";
    
} catch (Exception $e) {
    echo "✗ Migration error: " . $e->getMessage() . "\n";
    exit(1);
}
