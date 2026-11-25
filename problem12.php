<?php
session_start();

$users = [
    "student" => [
        "name" => "Student User",
        "password_hash" => password_hash("secret123", PASSWORD_DEFAULT),
    ],
    "admin" => [
        "name" => "Administrator",
        "password_hash" => password_hash("admin123", PASSWORD_DEFAULT),
    ],
];

$error = "";
$view = $_GET["page"] ?? "dashboard";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $action = $_POST["action"] ?? "";
    if ($action === "login") {
        $username = trim($_POST["username"] ?? "");
        $password = $_POST["password"] ?? "";

        if (isset($users[$username]) && password_verify($password, $users[$username]["password_hash"])) {
            session_regenerate_id(true);
            $_SESSION["user"] = [
                "username" => $username,
                "name" => $users[$username]["name"],
            ];
            header("Location: " . $_SERVER["PHP_SELF"] . "?page=dashboard");
            exit;
        } else {
            $error = "Invalid username or password.";
        }
    } elseif ($action === "logout") {
        $_SESSION = [];
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), "", time() - 42000, $params["path"], $params["domain"], $params["secure"], $params["httponly"]);
        }
        session_destroy();
        header("Location: " . $_SERVER["PHP_SELF"]);
        exit;
    }
}

$isLoggedIn = !empty($_SESSION["user"]);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Session Login System (Problem 12)</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h3>Session Login System (Problem 12)</h3>
    <p>
        <a href="?page=dashboard">Protected Dashboard</a> |
        <a href="?page=login">Login Page</a>
    </p>

    <?php if ($view === "dashboard" && !$isLoggedIn): ?>
        <p style="color:red">🔒 Please log in to access the protected page.</p>
    <?php endif; ?>

    <?php if ($isLoggedIn): ?>
        <div class="container">
            <h4>Protected Area — Welcome, <?= htmlspecialchars($_SESSION["user"]["name"]) ?>!</h4>
            <p>Username: <strong><?= htmlspecialchars($_SESSION["user"]["username"]) ?></strong></p>
            <p>You are viewing restricted session content.</p>
            <form method="post">
                <input type="hidden" name="action" value="logout">
                <button type="submit">Log Out</button>
            </form>
        </div>
    <?php else: ?>
        <?php if ($error !== ""): ?>
            <p style="color:red">❌ <?= htmlspecialchars($error) ?></p>
        <?php endif; ?>
        <form method="post">
            <input type="hidden" name="action" value="login">
            <label for="username">Username:</label>
            <input id="username" type="text" name="username" required>
            <label for="password">Password:</label>
            <input id="password" type="password" name="password" required>
            <button type="submit">Log In</button>
        </form>
        <p><small>Demo accounts: <code>student</code> / <code>secret123</code> or <code>admin</code> / <code>admin123</code></small></p>
    <?php endif; ?>
</body>
</html>