<?php
include 'database/db.php';
global $conn;

if (isset($_POST['name']) && isset($_POST['email'])) {
    $name = $_POST['name'];
    $email = $_POST['email'];

    // Prepare a statement to prevent SQL injection
    $stmt = $conn->prepare("SELECT * FROM users WHERE name = ? AND email = ?");
    $stmt->execute([$name, $email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        echo "Welcome back, " . htmlspecialchars($user['name']) . "!";
        // Here, you could redirect the user to their dashboard or home page
        // header('Location: dashboard.php');
    } else {
        if (isset($_POST['signup']) && $_POST['signup'] == 'yes') {
            // Insert new user into the database
            $stmt = $conn->prepare("INSERT INTO users (name, email) VALUES (?, ?)");
            $stmt->execute([$name, $email]);
            echo "Sign up successful! Welcome, " . htmlspecialchars($name) . "!";
            // Redirect to user dashboard or home page
            // header('Location: dashboard.php');
        } else {
            echo "User not found. Would you like to sign up with this name and email?";
            // Display signup form
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

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Form</title>
</head>
<body>
<form action="checkUser.php" method="post">
    <label for="name">Name:</label>
    <input type="text" id="name" name="name" required>
    <br>
    <label for="email">Email:</label>
    <input type="email" id="email" name="email" required>
    <br>
    <button type="submit">Login</button>
</form>
</body>
</html>
