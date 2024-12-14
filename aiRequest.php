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

// Check if the list exists and belongs to the logged-in userif (!$topic) {
if (!$topic) {
    error_log("Topic not found for listId: $listId and userId: $userId");
    echo json_encode(['message' => 'The requested list is not available or you do not have access.']);
    exit();
}


// Check if data is being received
$data = json_decode(file_get_contents("php://input"), true);

// Retrieve task details
$taskName = $data['taskName'] ?? '';
$deadline = $data['deadline'] ?? '';
$userQuery = $data['userQuery'] ?? '';

// Add API Key from an environment variable or a secure file
$apiKey = getenv('Q4aR8PnLF522nRmd7FBKeZZqD0Dg8QyuKs8aS9Us'); // You can also directly assign it, but using environment variables is more secure

if (!$apiKey) {
    echo json_encode(['message' => 'API Key is missing. Please check your configuration.']);
    exit();
}

$curl = curl_init();

curl_setopt_array($curl, [
    CURLOPT_URL => "https://api.openai.com/v1/completions",
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_HTTPHEADER => [
        "Authorization: Bearer $apiKey",
        "Content-Type: application/json"
    ],
    CURLOPT_POSTFIELDS => json_encode([
        'model' => 'text-davinci-003', // You can choose a different model here
        'prompt' => $userQuery,
        'max_tokens' => 150
    ]),
]);

$response = curl_exec($curl);
curl_close($curl);

// Process the response from OpenAI
if ($response === false) {
    echo json_encode(['message' => 'An error occurred while fetching AI suggestions.']);
    exit();
}

$responseData = json_decode($response, true);
if (isset($responseData['choices'][0]['text'])) {
    echo json_encode(['message' => $responseData['choices'][0]['text']]);
} else {
    echo json_encode(['message' => 'No AI response available. Please try again.']);
}
error_log("API Key: $apiKey");  // Log the API key value to check if it's being fetched correctly

?>
