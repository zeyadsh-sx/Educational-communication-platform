<?php

$pdo = new PDO("mysql:host=127.0.0.1:3307", "root", "");
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

function runFile($file, $pdo) {
    $sql = file_get_contents($file);
    $queries = explode(';', $sql);

    foreach ($queries as $query) {
        $query = trim($query);
        if (!empty($query)) {
            $pdo->exec($query);
        }
    }
}

try {
    runFile(__DIR__ . '/../database_schema.sql', $pdo);
    runFile(__DIR__ . '/../database_seed.sql', $pdo);

    echo "Database created successfully ✅";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}