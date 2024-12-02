<?php
include 'database/db.php';
global $conn;

$id = $_GET['id'];
$listId = $_GET['list_id'];

$delete = $conn->prepare("DELETE FROM `cards` WHERE id=?");
$delete->bindValue(1, $id);
$delete->execute();

header("Location: listDetail.php?id=" . urlencode($listId));
exit();
?>