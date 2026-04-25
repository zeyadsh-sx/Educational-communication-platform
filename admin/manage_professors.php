<?php
include "../config/database.php";

$database = new Database();
$conn = $database->connect();

if (isset($_POST['add'])) {
    $name = $_POST['name'];

    $conn->prepare("INSERT INTO users(name,user_type) VALUES (?, 'professor')")
         ->execute([$name]);
}

$docs = $conn->query("SELECT * FROM users WHERE user_type='professor'")->fetchAll();

foreach ($docs as $d) {
    echo $d['full_name']."<br>";
}
?>

<form method="POST">
    <input name="name" placeholder="Doctor Name">
    <button name="add">Add</button>
</form>
