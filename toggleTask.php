<?php
session_start();
include 'database/db.php';
global $conn;

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit();
}

$userId = $_SESSION['user_id'];

// Fetch the admin status of the user
$userStmt = $conn->prepare("SELECT isAdmin FROM users WHERE id = ?");
$userStmt->execute([$userId]);
$user = $userStmt->fetch(PDO::FETCH_ASSOC);
$isAdmin = $user['isAdmin'] ?? false;

// Get the task ID from the request
$data = json_decode(file_get_contents('php://input'), true);
$taskId = $data['taskId'];

if (!$taskId) {
    echo json_encode(['success' => false, 'message' => 'Task ID is required']);
    exit();
}

// Check if the task exists and determine permission
if ($isAdmin) {
//    // Admins can toggle any task
//    $taskStmt = $conn->prepare("SELECT completed FROM cards WHERE id = ?");
//    $taskStmt->execute([$taskId]);

    // For regular users, they can toggle their own tasks or unassigned tasks (user_id = 0)
    $taskStmt = $conn->prepare("SELECT completed FROM cards WHERE id = ? AND (user_id = ? OR user_id = 0)");
    $taskStmt->execute([$taskId, $userId]);

} else {
    // Regular users can only toggle their own tasks
    $taskStmt = $conn->prepare("SELECT completed FROM cards WHERE id = ? AND (user_id = ? OR user_id = 0)");
    $taskStmt->execute([$taskId, $userId]);
}

$task = $taskStmt->fetch(PDO::FETCH_ASSOC);

if ($task) {
    $newStatus = $task['completed'] ? 0 : 1;
    $updateStmt = $conn->prepare("UPDATE cards SET completed = ? WHERE id = ?");
    $updateStmt->execute([$newStatus, $taskId]);

    echo json_encode(['success' => true, 'newStatus' => $newStatus]);
} else {
    echo json_encode(['success' => false, 'message' => 'Task not found or access denied']);
}
?>
