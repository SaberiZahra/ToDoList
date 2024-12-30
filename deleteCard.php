<?php
session_start();
include 'database/db.php';
global $conn;

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$userId = $_SESSION['user_id'];

// Fetch the admin status of the user
$userStmt = $conn->prepare("SELECT isAdmin FROM users WHERE id = ?");
$userStmt->execute([$userId]);
$user = $userStmt->fetch(PDO::FETCH_ASSOC);
$isAdmin = $user['isAdmin'] ?? false;

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $listId = $_GET['list_id'] ?? null;

    if ($isAdmin) {
        // Admins can delete any task
        $delete = $conn->prepare("DELETE FROM `cards` WHERE id = ?");
        $delete->execute([$id]);

        // Redirect to the appropriate page
        if ($listId) {
            header("Location: listDetail.php?id=" . urlencode($listId));
        } else {
            header("Location: assignTask.php");
        }
        exit();
    } else {
        // Non-admin users can only delete their own cards
        $checkStmt = $conn->prepare("SELECT id FROM cards WHERE id = ? AND user_id = ?");
        $checkStmt->execute([$id, $userId]);

        if ($checkStmt->fetch()) {
            $delete = $conn->prepare("DELETE FROM `cards` WHERE id = ?");
            $delete->execute([$id]);

            if ($listId) {
                header("Location: listDetail.php?id=" . urlencode($listId));
            } else {
                header("Location: assignTask.php");
            }
            exit();
        } else {
            echo "Error: Card not found or does not belong to you.";
        }
    }
} else {
    echo "Error: Missing required parameters.";
}
?>
