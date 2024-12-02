<?php
include 'database/db.php';
global $conn;

$listId = $_POST['id'];
$listName = $_POST['list_name'];
$listDescription = $_POST['list_description'];

$sql = "UPDATE lists SET name = ?, description = ? WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->execute([$listName, $listDescription, $listId]);

header("Location: listDetail.php?id=$listId");
?>
