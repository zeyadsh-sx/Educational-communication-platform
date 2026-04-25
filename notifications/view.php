<?php
include "../db.php";

$user_id = $_GET['user_id'];

$stmt = $conn->prepare("SELECT * FROM notifications WHERE user_id=?");
$stmt->execute([$user_id]);

echo json_encode($stmt->fetchAll());
?>