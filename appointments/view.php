<?php
include "../config/database.php";

$database = new Database();
$conn = $database->connect();

$doctor_id = $_GET['doctor_id'];

$stmt = $conn->prepare("SELECT * FROM appointments WHERE doctor_id=?");
$stmt->execute([$doctor_id]);

echo json_encode($stmt->fetchAll());
?>
