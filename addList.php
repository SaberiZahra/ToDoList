<?php
include 'database/db.php';
global $conn;
//it can be used for both adding list and card
$listName = $_POST['list_name'];
$listDescription = $_POST['list_description'];
$sql = "INSERT INTO lists (name, description) VALUES ('$listName', '$listDescription')";
$conn->query($sql);
header("Location: index.php");
?>