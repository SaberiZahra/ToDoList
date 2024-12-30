<?php
session_start();
include 'database/db.php';
global $conn;

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$userId = $_SESSION['user_id'];

$userStmt = $conn->prepare("SELECT name, email, isAdmin FROM users WHERE id = ?");
$userStmt->execute([$userId]);
$user = $userStmt->fetch(PDO::FETCH_ASSOC);
$isAdmin = $user['isAdmin'] ?? false;

// Fetch all users
$usersStmt = $conn->prepare("SELECT id, name, email FROM users");
$usersStmt->execute();
$users = $usersStmt->fetchAll(PDO::FETCH_ASSOC);

// Handle form submission for adding a new task
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $taskName = $_POST['task_name'];
    $taskDeadline = $_POST['task_deadline'];
    $assignedUserId = $_POST['user_id'] ?? null; // For assigning tasks to specific users if needed

    if (!empty($taskName) && !empty($taskDeadline)) {
        $positionQuery = $conn->prepare("SELECT COALESCE(MAX(position), 0) + 1 AS new_position FROM cards WHERE category = -1 AND user_id = ?");
        $positionQuery->execute([$assignedUserId ?: $userId]); // If user is not assigned, use the logged-in user
        $newPosition = $positionQuery->fetch(PDO::FETCH_ASSOC)['new_position'];

        $insertQuery = $conn->prepare("INSERT INTO cards (name, deadline, category, position, user_id) VALUES (?, ?, ?, ?, ?)");
        $insertQuery->execute([$taskName, $taskDeadline, -1, $newPosition, $assignedUserId ?: $userId]);

        header("Location: assignTask.php");
        exit();
    } else {
        echo "<script>alert('Please fill in all fields.');</script>";
    }
}

// Fetch all cards in category -1
if ($isAdmin) {
    // Admin sees all tasks
    $cardsStmt = $conn->prepare("
        SELECT c.id, c.name AS task_name, c.deadline, u.name AS assigned_user, u.email AS assigned_email, c.completed
        FROM cards c
        LEFT JOIN users u ON c.user_id = u.id
        WHERE c.category = -1
        ORDER BY c.completed ASC, c.position ASC
    ");
    $cardsStmt->execute();
} else {
    // Regular users see only their tasks
    $cardsStmt = $conn->prepare("
        SELECT c.id, c.name AS task_name, c.deadline, u.name AS assigned_user, u.email AS assigned_email, c.completed
        FROM cards c
        LEFT JOIN users u ON c.user_id = u.id
        WHERE c.category = -1 AND c.user_id = ?
        ORDER BY c.completed ASC, c.position ASC
    ");
    $cardsStmt->execute([$userId]);
}
$cards = $cardsStmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/style.css">
    <link rel="icon" href="img/ico.png" type="image/png">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.14.0/Sortable.min.js"></script>
    <title>Assign Tasks</title>
</head>
<body>

<div class="home">
    <div class="container-main">
        <div class="container-left" id="containerLeft">
            <div class="profile center top">
                <img src="img/profile.png" alt="">
                <div>
                    <p class="name"><?= htmlspecialchars($user['name']) ?></p>
                    <p class="email"><?= htmlspecialchars($user['email']) ?></p>
                </div>
            </div>
            <div class="menu center">
                <!-- Task Adding Form (only visible to admins) -->
                <?php if ($isAdmin): ?>
                    <div class="assign-task-form">
                        <form method="POST">
                            <div>
                                <label for="task_name">Task Name:</label>
                                <input type="text" name="task_name" id="task_name" required>
                            </div>
                            <div>
                                <label for="task_deadline">Deadline:</label>
                                <input type="date" name="task_deadline" id="task_deadline" required>
                            </div>
                            <div>
                                <label for="user_id">Assign to:</label>
                                <select name="user_id" id="user_id">
                                    <option value="" selected>Unassigned</option>
                                    <?php foreach ($users as $userOption): ?>
                                        <option value="<?= $userOption['id'] ?>">
                                            <?= htmlspecialchars($userOption['name']) ?> (<?= htmlspecialchars($userOption['email']) ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <button type="submit">Add Task</button>
                        </form>
                    </div>
                <?php endif; ?>
                <ul>
                    <li><a href="index.php">Home</a></li>
                    <li><a href="assignTask.php">Assigned Tasks</a></li>
                    <?php
                    $lists = $conn->prepare("SELECT id, name FROM lists WHERE user_id = ? OR id = 0 ORDER BY id");
                    $lists->execute([$userId]);
                    foreach ($lists->fetchAll(PDO::FETCH_ASSOC) as $list) { ?>
                        <li><a href="listDetail.php?id=<?= htmlspecialchars($list['id']) ?>"><?= htmlspecialchars($list['name']) ?></a></li>
                    <?php } ?>
                </ul>
            </div>
            <div class="logout center">
                <hr>
                <a href="login.php" id="logoutLink"><i class='bx bx-log-out'></i><span>Logout</span></a>
            </div>
        </div>

        <div class="container-right">
            <div class="container-sub center">
                <div class="header">
                    <h2>Assign Tasks</h2>
                </div>

                <!-- Display Tasks in Category -1 -->
                <div class="assigned-tasks">
                    <h3>Assigned Tasks to users by admin (Category -1)</h3>
                    <div class="card-holder" id="taskList">
                        <?php foreach ($cards as $card): ?>
                            <div class="card align" data-id="<?= $card['id'] ?>" style="opacity: 1;">
                                <input type="checkbox" name="task" id="task-<?= $card['id'] ?>" onchange="toggleTask(<?= $card['id'] ?>)" <?= $card['completed'] ? 'checked' : '' ?>>
                                <div class="marker" style="<?= $card['completed'] ? 'text-decoration: line-through; opacity: 0.6;' : '' ?>">
                                    <span><?= htmlspecialchars($card['task_name']) ?></span>
                                    <p class="date"><?= htmlspecialchars($card['deadline']) ?></p>
                                </div>

                                <!-- Show assigned user details only for admin -->
                                <?php if ($isAdmin): ?>
                                    <p class="assigned-user">
                                        -  Assigned to: <?= htmlspecialchars($card['assigned_user']) ?> (<?= htmlspecialchars($card['assigned_email']) ?>)
                                    </p>
                                <?php endif; ?>

                                <!-- Only show delete button if the user is admin -->
                                <?php if ($isAdmin): ?>
                                    <a href="deleteCard.php?id=<?= $card['id'] ?>" onclick="return confirm('Are you sure you want to delete this task?')">
                                        <i class="bx bx-trash-alt"></i> Delete
                                    </a>
                                <?php endif; ?>

                                <span class="drag-handle">&#9776;</span>
                            </div>
                        <?php endforeach; ?>

                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
<script src="js/toggleTask.js"></script>
<script src="js/reorder.js"></script>

</body>
</html>
