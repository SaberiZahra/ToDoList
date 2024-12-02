<?php
include 'database/db.php';
global $conn;
if(isset($_POST['sub'])){
    $name = $_POST['todo'];
    $deadline = $_POST['date'];
    $insert = $conn->prepare("INSERT INTO `cards` (name, deadline) VALUES (?, ?)");
    $insert->bindValue(1, $name);
    $insert->bindValue(2, $deadline);
    $insert->execute();
    header('location:index.php');
}
?>

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
    <script src="js/reorder.js"></script>

    <title>To-Do-List</title>
</head>
<body>
    <div id="overlay">
        <div class="loader"></div>
    </div>
    <div class="home">
        <div class="container-main">

            <div class="container-left"  id="containerLeft">
                <div class="dots">
                    <div id="d1"></div>
                    <div id="d2"></div>
                    <div id="d3"></div>
                </div>

                <div class="profile center top">
                    <img src="img/profile.png" alt="">
                    <div>
                        <p class="name" id="name">Loading...</p>
                        <p class="email" id="email">Loading...</p>
                    </div>
                </div>

                <div class="menu center">
                    <div class="search-holder">
                        <input type="search" name="search" id="search" placeholder=" Search">
                    </div>
                    <div class="list">

                        <ul>
                            <?php
                            $lists = $conn->prepare("SELECT id, name, description FROM `lists` ORDER BY id");
                            $lists->execute();
                            $list = $lists->fetchAll(PDO::FETCH_ASSOC);
                            foreach ($list as $topic) { ?>
                                <li> <a href="listDetail.php?id=<?= htmlspecialchars($topic['id']) ?>"> <?= htmlspecialchars($topic['name']) ?> </a> </li>
                            <?php } ?>
                            <li id="add-new"><a href="#" onclick="showAddListForm()"><i class='bx bx-plus'></i><span>Add list</span></a></li>
                        </ul>

                        <div id="add-list-form" style="display: none;">
                            <form action="addList.php" method="post">
                                <input type="text" name="list_name" placeholder="New list name" required>
                                <textarea name="list_description" placeholder="List description"></textarea>
                                <button type="submit">Add List</button>
                                <button type="button" onclick="hideAddListForm()">Cancel</button>
                            </form>
                        </div>

                        <script>
                            function showAddListForm() {
                                document.getElementById('add-list-form').style.display = 'block';
                            }

                            function hideAddListForm() {
                                document.getElementById('add-list-form').style.display = 'none';
                            }
                        </script>
                    </div>
                </div>

                <div class="logout center">
                    <hr>
                    <a href="#" id="logoutLink"><i class='bx bx-log-out'></i><span>Logout</span></a>
                </div>

            </div>

            <div class="container-right">

                <div class="hamburger-container">
                    <div class="burger" id="burgerIcon">
                      <div class="line"></div>
                      <div class="line"></div>
                      <div class="line"></div>
                    </div>
                  </div>

                <div class="container-sub center">
                    <div class="header">
                        <h2 id="header_title">All tasks</h2>
                        <div class="buttons">
                            <div></div>
                            <div></div>
                            <div></div>
                        </div>
                    </div>

                    <div class="card-holder" id="TaskContainer">
                        <div id="taskList">
                        <?php
                        $todos = $conn->prepare("SELECT id, name, deadline, category, position FROM `cards` ORDER BY position ASC");
                        $todos->execute();
                        $todo = $todos->fetchAll(PDO::FETCH_ASSOC);

                        foreach ($todo as $task) {
                            $taskId = htmlspecialchars($task['id']);
                            $listId = htmlspecialchars($task['category']);
                            $taskName = htmlspecialchars($task['name']);
                            $deadline = htmlspecialchars($task['deadline']);
                            ?>
                            <div class="card align" data-id="<?= $taskId ?>" style="opacity: 1;">
                                <input type="checkbox" name="task" id="task-<?= $taskId ?>" onchange="toggleTask(<?= $taskId ?>)">
                                <div class="marker">
                                    <span id="task-name-<?= $taskId ?>"><?= $taskName ?></span>
                                    <p class="date today"><?= $deadline ?></p>
                                </div>
                                <i class="bx bx-trash-alt"></i>
                                <a href="deleteCard.php?id=<?= $taskId ?>&list_id=<?= $listId ?>"
                                   onclick="return confirm('Are you sure you want to delete this task?')">
                                    Delete</a>
                                <!--the first logic I used to handle reordering process -->
<!--                                <a href="reorderCard.php?id=--><?php //= $taskId ?><!--&direction=up&list_id=--><?php //= $listId ?><!--">Move Up</a>-->
<!--                                <a href="reorderCard.php?id=--><?php //= $taskId ?><!--&direction=down&list_id=--><?php //= $listId ?><!--">Move Down</a>-->
                                <span class="drag-handle">&#9776;</span>
                            </div>

                        <?php } ?>
                        </div>
                        <div class="add-new ">
                        <div class="card-new align">
                            <form method="post">
                                <div class="group1">
                                    <div style="width: 70%;">
                                        <button type="submit" name="sub"><i class='bx bx-plus'></i></button>
                                        <input type="text" name="todo" id="todo" placeholder="Add a task" required>
                                    </div>
                                    <div>
                                        <input type="date" name="date" id="duedate" required>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>