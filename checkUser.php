<?php
session_start();
include 'database/db.php';
global $conn;

if (isset($_POST['name']) && isset($_POST['email'])) {
    $name = $_POST['name'];
    $email = $_POST['email'];

    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        if ($user['name'] === $name) {
            $_SESSION['user_id'] = $user['id'];
            echo "Welcome back, " . htmlspecialchars($user['name']) . "!";
            header('Location: index.php');
        } else {
            echo "User not found with that name. Please try again.";
        }
    } else {
        if (isset($_POST['signup']) && $_POST['signup'] == 'yes') {
            $stmt = $conn->prepare("INSERT INTO users (name, email) VALUES (?, ?)");
            $stmt->execute([$name, $email]);
            $_SESSION['user_id'] = $conn->lastInsertId();
            echo "Sign up successful! Welcome, " . htmlspecialchars($name) . "!";
            header('Location: index.php');
        } else {
            echo "Email not found. Would you like to sign up with this name and email?";
            echo '<form action="checkUser.php" method="post">
                    <input type="hidden" name="name" value="' . htmlspecialchars($name) . '">
                    <input type="hidden" name="email" value="' . htmlspecialchars($email) . '">
                    <input type="hidden" name="signup" value="yes">
                    <button type="submit">Sign Up</button>
                  </form>';
        }
    }
} else {
    echo "Please enter both name and email.";
}
?>
