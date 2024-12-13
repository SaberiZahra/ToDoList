<?php
session_start();
include 'database/db.php';
global $conn;

$userId = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $listName = $_POST['list_name'];
    $listDescription = $_POST['list_description'];
    $insert = $conn->prepare("INSERT INTO lists (name, description, user_id) VALUES (?, ?, ?)");
    $insert->bindValue(1, $listName);
    $insert->bindValue(2, $listDescription);
    $insert->bindValue(3, $userId);
    $insert->execute();
    header('location:index.php');
}
?>


