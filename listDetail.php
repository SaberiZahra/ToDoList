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

//to handle header:index.php when deleting a card doesnt belong to any list.
if (!$topic) {
    //list does not found or belongs to another user
    header("Location: index.php");
    exit();
}

if (isset($_POST['sub'])) {
    $name = $_POST['todo'];
    $deadline = $_POST['date'];

    // The MAX(position) logic must be separated into two queries
    $positionQuery = $conn->prepare("SELECT COALESCE(MAX(position), 0) + 1 AS new_position FROM cards WHERE category = ? AND user_id = ?");
    $positionQuery->execute([$listId, $userId]);
    $newPosition = $positionQuery->fetch(PDO::FETCH_ASSOC)['new_position'];

    $insert = $conn->prepare("INSERT INTO cards (name, deadline, category, position, user_id) VALUES (?, ?, ?, ?, ?)");
    $insert->execute([$name, $deadline, $listId, $newPosition, $userId]);

    header('Location: listDetail.php?id=' . urlencode($listId));
    exit();
}
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
    <script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.14.0/Sortable.min.js"></script>
    <title>To-Do List</title>
</head>
<body>

<div class="home">
    <div class="container-main">
        <div class="container-left" id="containerLeft">
            <div class="profile center top">
                <img src="img/profile.png" alt="">
                <div>
                    <p class="name" id="name"><?= htmlspecialchars($user['name']) ?></p>
                    <p class="email" id="email"><?= htmlspecialchars($user['email']) ?></p>
                </div>
            </div>
            <div class="menu center">
                <ul>
                    <form action="deleteList.php" method="post" onsubmit="return confirm('Are you sure you want to delete this list?')">
                        <input type="hidden" name="id" value="<?= htmlspecialchars($topic['id']) ?>">
                        <button type="submit">Delete List</button>
                    </form>
                    <form action="editList.php" method="post">
                        <input type="hidden" name="id" value="<?= htmlspecialchars($topic['id']) ?>">
                        <input type="text" name="list_name" value="<?= htmlspecialchars($topic['name']) ?>" required>
                        <textarea name="list_description" required><?= htmlspecialchars($topic['description']) ?></textarea>
                        <button type="submit">Edit List</button>
                    </form>
                    <div class="list">
                        <li><a href="index.php">Home</a></li>
                        <?php
                        $lists = $conn->prepare("SELECT id, name FROM lists WHERE user_id = ? ORDER BY id");
                        $lists->execute([$userId]);
                        foreach ($lists->fetchAll(PDO::FETCH_ASSOC) as $list) { ?>
                            <li><a href="listDetail.php?id=<?= htmlspecialchars($list['id']) ?>"><?= htmlspecialchars($list['name']) ?></a></li>
                        <?php } ?>
                    </div>
                </ul>
            </div>
        </div>

        <div class="container-right">
            <div class="container-sub center">
                <div class="header">
                    <h2><?= htmlspecialchars($topic['name']) ?></h2>
                    <h3><?= htmlspecialchars($topic['description']) ?></h3>
                </div>
                <div class="card-holder" id="taskList">
                    <!-- Modal Structure for AI Help -->
                    <div id="aiHelpModal" style="display:none;">
                        <div id="loadingSpinner" style="display:none;">Loading...</div>
                        <div id="aiHelpMessage">Loading AI suggestion...</div>
                    </div>
                    <?php
                    $cards = $conn->prepare("SELECT id, name, deadline, category, position, completed FROM cards WHERE category = ? AND user_id = ? ORDER BY completed ASC, position ASC");
                    $cards->execute([$listId, $userId]);
                    foreach ($cards->fetchAll(PDO::FETCH_ASSOC) as $card) {
                        $taskId = htmlspecialchars($card['id']);
                        $taskName = htmlspecialchars($card['name']);
                        $deadline = htmlspecialchars($card['deadline']);
                        ?>
                        <div class="card align" data-id="<?= $taskId ?>" style="opacity: 1;">
                            <input type="checkbox" name="task" id="task-<?= $taskId ?>" onchange="toggleTask(<?= $taskId ?>)" <?= $card['completed'] ? 'checked' : '' ?>>
                            <div class="marker" style="<?= $card['completed'] ? 'text-decoration: line-through; opacity: 0.6;' : '' ?>">
                                <span><?= $taskName ?></span>
                                <p class="date"><?= $deadline ?></p>
                            </div>

                            <a href="#"
                               onclick="openAiHelp(<?= $taskId ?>, '<?= htmlspecialchars($taskName) ?>', '<?= htmlspecialchars($deadline) ?>'); return false;"
                               style="<?= $card['completed'] ? 'display: none;' : '' ?>">
                                <i class="bx bx-brain"></i> AI Help
                            </a>

                            <a href="deleteCard.php?id=<?= $taskId ?>&list_id=<?= $listId ?>"
                               onclick="return confirm('Are you sure you want to delete this task?')">
                                <i class="bx bx-trash-alt"></i> Delete
                            </a>
                            <span class="drag-handle">&#9776;</span>
                        </div>


                    <?php } ?>
                </div>

                <div class="add-new">
                    <div class="card-new align">
                        <form method="post">
                            <input type="text" name="todo" placeholder="Add a task" required>
                            <input type="date" name="date" required>
                            <button type="submit" name="sub">Add</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="js/reorder.js"></script>
<script src="js/toggleTask.js"></script>
<script>
    window.openAiHelp = async function (taskId, taskName, deadline) {
        const modal = document.getElementById('aiHelpModal');
        const messageElement = document.getElementById('aiHelpMessage');
        const spinner = document.getElementById('loadingSpinner');
        modal.style.display = 'block';
        messageElement.innerText = `Fetching AI suggestion for "${taskName}"...`;
        spinner.style.display = 'block'; // Show the spinner

        try {
            const response = await fetch('aiRequest.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    taskName: taskName,
                    deadline: deadline,
                    userQuery: `Give me some steps to complete the task "${taskName}" by ${deadline}`
                })
            });

            if (response.ok) {
                const data = await response.json();
                if (data.message) {
                    messageElement.innerText = `AI Suggestion for "${taskName}":\n\n${data.message}`;
                } else {
                    messageElement.innerText = 'No AI response available. Please try again.';
                }
            } else {
                messageElement.innerText = 'Failed to fetch AI suggestions. Please try again.';
            }
        } catch (error) {
            console.error('Error:', error);
            messageElement.innerText = 'An error occurred while fetching AI suggestions.';
        }

        spinner.style.display = 'none'; // Hide the spinner once response is received
    };
</script>

</body>
</html>