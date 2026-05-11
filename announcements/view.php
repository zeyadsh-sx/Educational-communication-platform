<?php
include "../config/database.php";

$database = new Database();
$conn = $database->connect();

$course_id = isset($_GET['course_id']) ? $_GET['course_id'] : 0;

if (!$course_id) {
    die("Please provide a course_id parameter");
}

$stmt = $conn->prepare("SELECT * FROM announcements WHERE course_id=?");
$stmt->execute([$course_id]);

$data = $stmt->fetchAll();

foreach ($data as $a) {
    echo "<h3>".$a['title']."</h3>";
    echo "<p>".$a['content']."</p><hr>";
}
?>
