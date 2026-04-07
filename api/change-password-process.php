<?php
// DO NOT call session_start() here — auth_check.php already starts the session
require_once __DIR__ . '/../config/auth_check.php';
require_once __DIR__ . '/../config/db_connect.php';

$user_id = $_SESSION['user_id'];
$new_password = $_POST['new_password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';

if ($new_password !== $confirm_password) {
    die("New passwords do not match.");
}

if (strlen($new_password) < 6) {
    die("Password must be at least 6 characters.");
}

$hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

$sql = "UPDATE users SET password = ? WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("si", $hashed_password, $user_id);

if ($stmt->execute()) {
    // Log the password change
    $audit_data = [
        "action_summary" => "Password Changed",
        "user_id" => $user_id
    ];
    log_audit($conn, (int)$user_id, "Update", "Account Management", json_encode($audit_data));
    echo "Password changed successfully.";
} else {
    echo "Failed to update password.";
}

$conn->close();
?>