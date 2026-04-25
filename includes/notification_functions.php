<?php
include "../config/database.php";

function sendNotification($user_id, $message) {
    $database = new Database();
    $conn = $database->connect();

    $conn->prepare("INSERT INTO notifications(user_id,message)
    VALUES (?,?)")
    ->execute([$user_id,$message]);
}
?> 