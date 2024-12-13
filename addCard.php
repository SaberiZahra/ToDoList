<?php
session_start();
include 'database/db.php';
global $conn;

$userId = $_SESSION['user_id'];

if (isset($_POST['sub'])) {
    $name = $_POST['todo'];
    $deadline = $_POST['date'];
    $insert = $conn->prepare("INSERT INTO cards (name, deadline, user_id) VALUES (?, ?, ?)");
    $insert->bindValue(1, $name);
    $insert->bindValue(2, $deadline);
    $insert->bindValue(3, $userId);
    $insert->execute();
    header('location:index.php');
}
?>
