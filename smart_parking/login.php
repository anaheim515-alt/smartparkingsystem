<?php
session_start();
include 'db.php';

// Redirect if already logged in
if(isset($_SESSION['user'])) {
    header("Location: index.php");
    exit;
}

$error = "";

if(isset($_POST['username'], $_POST['password'])) {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    $stmt = $conn->prepare("SELECT * FROM users WHERE username=? AND password=?");
    $stmt->bind_param("ss", $username, $password); // plain password
    $stmt->execute();
    $res = $stmt->get_result();

    if($res->num_rows > 0) {
        $_SESSION['user'] = $username;
        header("Location: index.php");
        exit;
    } else {
        $error = "Invalid username or password";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login - Smart Car Parking</title>
<link rel="stylesheet" href="css/style.css">
<style>
body {
    background: #f8f9fa;
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 100vh;
    margin: 0;
    font-family: Arial, Helvetica, sans-serif;
}

.login-container {
    background: #ffffff;
    padding: 30px 40px;
    border-radius: 12px;
    box-shadow: 0 12px 40px rgba(0,0,0,0.1);
    width: 350px;
    text-align: center;
}

h2 {
    color: #35408e;
    margin-bottom: 20px;
}

input {
    width: 100%;
    padding: 12px;
    margin-bottom: 15px;
    border-radius: 6px;
    border: 1px solid #ccc;
    font-size: 14px;
    color: #35408e;
}

button {
    width: 100%;
    padding: 12px;
    border: none;
    border-radius: 6px;
    background: #35408e;
    color: #ffd41c;
    font-weight: bold;
    cursor: pointer;
    transition: 0.3s;
}

button:hover {
    background: #ffd41c;
}

.error-msg {
    color: #e74c3c;
    margin-bottom: 15px;
    font-weight: bold;
}
</style>
</head>
<body>

<div class="login-container">
    <h2>Login</h2>
    <?php if($error) echo "<div class='error-msg'>$error</div>"; ?>
    <form method="POST">
        <input type="text" name="username" placeholder="Username" required>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit">LOGIN</button>
    </form>
</div>

</body>
</html>
