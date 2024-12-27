<?php
session_start();
global $conn;
include 'database/db.php';

$userId = $_SESSION['user_id'];

// Fetch common tasks with the user's completion status (tasks without a list)
$cardsQuery = $conn->prepare("
    SELECT c.id, c.name, c.deadline, c.category, c.position, 
           IFNULL(ucs.completed, FALSE) AS completed
    FROM cards c
    LEFT JOIN user_card_status ucs ON c.id = ucs.card_id AND ucs.user_id = ?
    WHERE c.category = 0 AND c.user_id = 0
    ORDER BY c.position ASC
");
$cardsQuery->execute([$userId]);
$cards = $cardsQuery->fetchAll(PDO::FETCH_ASSOC);

// Return tasks as JSON
header('Content-Type: application/json');
echo json_encode($cards);

// Handle task completion update when the request method is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    $cardId = $data['cardId'];
    $completed = $data['completed'];

    // Insert or update the completion status of the task for the user
    $statusQuery = $conn->prepare("
        INSERT INTO user_card_status (user_id, card_id, completed)
        VALUES (?, ?, ?)
        ON DUPLICATE KEY UPDATE completed = VALUES(completed)
    ");
    $statusQuery->execute([$userId, $cardId, $completed]);

    echo json_encode(['success' => true]);
}
?>
