<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/db_connect.php';

// if already have a session (not logged out), send them straight to their dashboard
if (isset($_SESSION['user_id']) && isset($_SESSION['role'])) {
    $role = strtolower($_SESSION['role']);
    if ($role === 'admin') {
        header("Location: ../pages/admin-dashboard.php");
    } elseif ($role === 'staff') {
        header("Location: ../pages/staff-dashboard.php");
    } else {
        header("Location: ../pages/dashboard.html");
    }
    exit();
}

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['login'])) {

    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($username === "" || $password === "") {
        $error = "Please fill in all fields.";
    } else {

        // Get user with role
        $stmt = mysqli_prepare(
            $conn,
            "SELECT id, username, password, role FROM users WHERE username = ?"
        );
        mysqli_stmt_bind_param($stmt, "s", $username);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if ($result && mysqli_num_rows($result) === 1) {
            $user = mysqli_fetch_assoc($result);

            if (password_verify($password, $user['password'])) {

                // Save session data
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];

                // Role-based redirect
                $role = strtolower($user['role']);

                if ($role === 'admin') {
                    header("Location: ../pages/admin-dashboard.php");
                } elseif ($role === 'staff') {
                    header("Location: ../pages/staff-dashboard.php");
                } else {
                    header("Location: ../pages/dashboard.html");
                }
                exit();

            } else {
                $error = "Incorrect password.";
            }

        } else {
            $error = "Account not found.";
        }

        mysqli_stmt_close($stmt);
    }
}

mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Barangay Login</title>
    <link rel="stylesheet" href="../assets/css/login.css">
</head>
<body class="auth-body">

<div class="bg"></div>
<div class="login-card">
    <div class="left-panel">
        <img src="../assets/images/logos.png" class="logo" alt="logo">
        <h1>Barangay Abangan Norte</h1>
        <p id="tagline">Household Data Management System</p>
    </div>

    <div class="right-panel">
        <h2>Sign In</h2>
        
        <form method="POST" action="">
            <label>Username</label>
            <input type="text" name="username" required>

            <label>Password</label>
            <input type="password" name="password" required>

            <button type="submit" name="login">Sign In</button>
        </form>

        <?php if (!empty($error)) { ?>
            <p class="error"><?php echo $error; ?></p>
        <?php } ?>

        <p class="authorized">Authorized Personnel Only</p>
    </div>
</div>

<script src="../assets/js/login.js"></script>
</body>
</html>
