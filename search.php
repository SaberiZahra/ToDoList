<?php
session_start();
include 'database/db.php';
global $conn;

$userId = $_SESSION['user_id'];
$searchTerm = isset($_GET['term']) ? $_GET['term'] : '';

$searchTerm = '%' . $searchTerm . '%';

$stmt = $conn->prepare("SELECT id, name, deadline, category, position FROM cards WHERE user_id = ? AND name LIKE ? ORDER BY position ASC");
$stmt->execute([$userId, $searchTerm]);

$cards = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo json_encode($cards);
?>
