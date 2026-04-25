<?php
include "../config/database.php";
include "../includes/auth.php";

$database = new Database();
$conn = $database->connect();

session_start();

$doctor_id = $_POST['doctor_id'];
$date = $_POST['date_time'];

$conn->prepare("INSERT INTO appointments(doctor_id,student_id,date_time)
VALUES (?,?,?)")
->execute([$doctor_id,$_SESSION['user']['id'],$date]);

echo "Booked";
?>