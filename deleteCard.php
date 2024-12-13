<?php
session_start();
include 'database/db.php';
global $conn;

if (isset($_GET['id']) && isset($_GET['list_id'])) {
    $id = $_GET['id'];
    $listId = $_GET['list_id'];

    $userId = $_SESSION['user_id'];
    $checkStmt = $conn->prepare("SELECT id FROM cards WHERE id = ? AND user_id = ?");
    $checkStmt->execute([$id, $userId]);

    if ($checkStmt->fetch()) {
        $delete = $conn->prepare("DELETE FROM `cards` WHERE id = ?");
        $delete->execute([$id]);

        header("Location: listDetail.php?id=" . urlencode($listId));
        exit();
    } else {
        echo "Error: Card not found or does not belong to you.";
        // Optionally, redirect to an error page or back to the list
        // header("Location: listDetail.php?id=" . urlencode($listId));
        // exit();
    }
} else {
    echo "Error: Missing required parameters.";
    // Optionally, redirect to an error page or back to the list
    // header("Location: index.php");
    // exit();
}
?>