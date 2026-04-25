<?php
include "../config/database.php";

$database = new Database();
$conn = $database->connect();

$stmt = $conn->query("SELECT * FROM appointments WHERE status='pending'");
echo json_encode($stmt->fetchAll());
?>