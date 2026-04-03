<?php
session_start();
require_once __DIR__ . '/../config/auth_check.php';

// DB connection
require_once __DIR__ . '/../config/db_connect.php';

// Get session user (adjust if needed)
$user_id = $_SESSION['user_id'];

$new_password = $_POST['new_password'];
$confirm_password = $_POST['confirm_password'];

// 1. Check if passwords match
if ($new_password !== $confirm_password) {
    die("New passwords do not match.");
}

// 2. Validate password strength (optional but recommended)
if (strlen($new_password) < 6) {
    die("Password must be at least 6 characters.");
}

// 3. Hash new password
$hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

// 4. Update password in DB
$sql = "UPDATE users SET password = ? WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("si", $hashed_password, $user_id);

if ($stmt->execute()) {
    echo "Password changed successfully.";
} else {
    echo "Failed to update password.";
}

$conn->close();
?>