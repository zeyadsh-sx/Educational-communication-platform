<?php
header("Content-Type: application/json");

include "../includes/auth.php";

logoutUser();

echo json_encode(["success" => true]);
?>