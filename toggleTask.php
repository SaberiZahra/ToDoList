<?php
session_start();
include 'database/db.php';
global $conn;

$userId = $_SESSION['user_id'];

$data = json_decode(file_get_contents('php://input'), true);
$taskId = $data['taskId'];

// Ensure the task belongs to the current user
$taskStmt = $conn->prepare("SELECT completed FROM cards WHERE id = ? AND user_id = ?");
$taskStmt->execute([$taskId, $userId]);
$task = $taskStmt->fetch(PDO::FETCH_ASSOC);

if ($task) {
    $newStatus = $task['completed'] ? 0 : 1;
    $updateStmt = $conn->prepare("UPDATE cards SET completed = ? WHERE id = ? AND user_id = ?");
    $updateStmt->execute([$newStatus, $taskId, $userId]);

    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false]);
}
?>
