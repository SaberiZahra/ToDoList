<?php
session_start();
include 'database/db.php';
global $conn;

$userId = $_SESSION['user_id'];
$listId = $_GET['id'];

$userStmt = $conn->prepare("SELECT name, email FROM users WHERE id = ?");
$userStmt->execute([$userId]);
$user = $userStmt->fetch(PDO::FETCH_ASSOC);

$lists = $conn->prepare("SELECT id, name, description FROM lists WHERE id = ? AND user_id = ?");
$lists->execute([$listId, $userId]);
$topic = $lists->fetch(PDO::FETCH_ASSOC);

// Check if the list exists and belongs to the logged-in user
if (!$topic) {
    error_log("Topic not found for listId: $listId and userId: $userId");
    echo json_encode(['message' => 'The requested list is not available or you do not have access.']);
    exit();
}

// Check if data is being received
$data = json_decode(file_get_contents("php://input"), true);
if (!$data || !isset($data['userQuery'])) {
    echo json_encode(['message' => 'Invalid request data. Please check your input.']);
    exit();
}

// Retrieve task details
$taskName = $data['taskName'] ?? '';
$deadline = $data['deadline'] ?? '';
$userQuery = $data['userQuery'] ?? '';

// Add Cohere API Key securely
$apiKey = getenv('Q4aR8PnLF522nRmd7FBKeZZqD0Dg8QyuKs8aS9Us');  // Ensure you use your actual API key securely

if (!$apiKey) {
    echo json_encode(['message' => 'API Key is missing. Please check your configuration.']);
    exit();
}

// Cohere API request to generate response using V2
$curl = curl_init();

// Prepare the API request data for V2
$payload = json_encode([
    'model' => 'command-r-plus',  // V2 model for chat-based interactions
    'messages' => [
        ['role' => 'user', 'content' => $userQuery]  // Structure the user input message
    ],
]);

curl_setopt_array($curl, [
    CURLOPT_URL => "https://api.cohere.ai/v2/chat",  // Updated endpoint for chat-based requests
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_HTTPHEADER => [
        "Authorization: Bearer $apiKey",
        "Content-Type: application/json"
    ],
    CURLOPT_POSTFIELDS => $payload
]);

$response = curl_exec($curl);
curl_close($curl);

// Process the response from Cohere
if ($response === false) {
    echo json_encode(['message' => 'An error occurred while fetching AI suggestions.']);
    exit();
}

$responseData = json_decode($response, true);
if (isset($responseData['message'])) {
    echo json_encode(['message' => $responseData['message']]);
} else {
    echo json_encode(['message' => 'No AI response available. Please try again.']);
}
?>
