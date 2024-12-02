<?php
include 'database/db.php';
global $conn;

$data = json_decode(file_get_contents('php://input'), true);

if ($data) {
    foreach ($data as $task) {
        $updateTask = $conn->prepare("UPDATE `cards` SET `position` = ? WHERE id = ?");
        $updateTask->execute([$task['order'], $task['id']]);
    }
    echo "Reorder successful";
} else {
    echo "No data received";
}
?>

<!--the first logic I used to handle reordering process -->
<?php
//include 'database/db.php';
//global $conn;
//
//$taskId = $_GET['id'];
//$direction = $_GET['direction'];
//$listId = $_GET['list_id'];
//
//// Get the current order of the task
//$currentOrderQuery = $conn->prepare("SELECT `order` FROM `cards` WHERE id = ?");
//$currentOrderQuery->execute([$taskId]);
//$currentOrder = $currentOrderQuery->fetch(PDO::FETCH_ASSOC)['order'];
//
//if ($direction == 'up') {
//    $newOrder = $currentOrder - 1;
//} else if ($direction == 'down') {
//    $newOrder = $currentOrder + 1;
//}
//
//// Check if new order is valid
//$checkOrderQuery = $conn->prepare("SELECT id, `order` FROM `cards` WHERE `order` = ? AND `category` = ?");
//$checkOrderQuery->execute([$newOrder, $listId]);
//$otherTask = $checkOrderQuery->fetch(PDO::FETCH_ASSOC);
//
//if ($otherTask) {
//    // Swap the orders
//    $updateOtherTask = $conn->prepare("UPDATE `cards` SET `order` = ? WHERE id = ?");
//    $updateOtherTask->execute([$currentOrder, $otherTask['id']]);
//}
//
//$updateTask = $conn->prepare("UPDATE `cards` SET `order` = ? WHERE id = ?");
//$updateTask->execute([$newOrder, $taskId]);
//
//// Redirect back to the list detail page
//header("Location: listDetail.php?id=" . urlencode($listId));
//exit();
//?>
