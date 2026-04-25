<?php
include "../../config/database.php";
include "../../includes/auth.php";

$database = new Database();
$conn = $database->connect();

session_start();

$conn->prepare("INSERT INTO appointments(doctor_id,student_id,date_time)
VALUES (?,?,?)")
->execute([
$_POST['doctor_id'],
$_SESSION['user']['id'],
$_POST['date_time']
]);

echo json_encode(["status"=>"booked"]);
?>