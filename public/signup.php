<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/db_connect.php';

// Admin-only page
if (empty($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header("Location: login.php");
    exit();
}

$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['signup'])) {

    $first_name = trim($_POST['first_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $role = trim($_POST['role'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $confirm_password = trim($_POST['confirm_password'] ?? '');

    if ($first_name === "" || $last_name === "" || $role === "" || $username === "" || $password === "" || $confirm_password === "") {
        $error = "Please fill in all fields.";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } else {
        // if username already exists
        $stmt = mysqli_prepare($conn, "SELECT id FROM users WHERE username = ?");
        mysqli_stmt_bind_param($stmt, "s", $username);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);

        if (mysqli_stmt_num_rows($stmt) > 0) {
            $error = "Username already taken.";
        } else {
            // hash password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // insert new user
            $stmt_insert = mysqli_prepare($conn, "INSERT INTO users (first_name, last_name, role, username, password) VALUES (?, ?, ?, ?, ?)");
            mysqli_stmt_bind_param($stmt_insert, "sssss", $first_name, $last_name, $role, $username, $hashed_password);
            if (mysqli_stmt_execute($stmt_insert)) {
                $success = "Account created successfully. You can now <a href='login.php'>login</a> or go to <a href='../index.php'>home</a>."; 
            } else {
                $error = "Error creating account: " . mysqli_error($conn);
            }
            mysqli_stmt_close($stmt_insert);
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
    <title>Sign Up - Barangay System</title>
    <link rel="stylesheet" href="../assets/css/signup.css">
</head>
<body class="auth-body">
<div class="auth-box">

    <div class="auth-header">
        <div class="auth-image">
            <img src="../assets/images/profiles.png" alt="Profile Picture">
        </div>
        <div class="auth-text">
            <h2>Create Account</h2>
            <p class="authorized-p">Register Authorized Personnel for System Access</p>
        </div>
    </div>

    <?php if ($error) { echo "<p style='color:red; text-align:center;'>$error</p>"; } ?>
    <?php if ($success) { echo "<p style='color:green; text-align:center;'>$success</p>"; } ?>

    <form method="POST" action="">
        <div class="row">
            <div class="input-group">
                <label>First Name</label>
                <input type="text" name="first_name" placeholder="First Name" required>
            </div>
            <div class="input-group">
                <label>Last Name</label>
                <input type="text" name="last_name" placeholder="Last Name" required>
            </div>
        </div>

        <label>Role</label>
        <select name="role" required>
            <option value="" disabled selected>Select Role</option>
            <option value="admin">Admin</option>
            <option value="staff">Staff</option>
            <option value="official">Barangay Official</option>
        </select>

        <label>Username</label>
        <input type="text" name="username" placeholder="Enter Username" required>

        <div class="row">
            <div class="input-group">
                <label>Password</label>
                <input type="password" name="password" placeholder="Enter Password" required>
            </div>
            <div class="input-group">
                <label>Confirm Password</label>
                <input type="password" name="confirm_password" placeholder="Confirm Password" required>
            </div>
        </div>

        <button type="submit" name="signup">Create Account</button>
    </form>

</div>
</body>
</html>
