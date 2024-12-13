<?php
include 'database/db.php';
global $conn;

if (isset($_POST['id'])) {
    $listId = $_POST['id'];

    $sql = "DELETE FROM lists WHERE id = ?";
    $stmt = $conn->prepare($sql);

    if ($stmt->execute([$listId])) {
        header("Location: index.php");
    } else {
        echo "Error deleting list.";
    }
} else {
    echo "Invalid request.";
}
exit();
?>
