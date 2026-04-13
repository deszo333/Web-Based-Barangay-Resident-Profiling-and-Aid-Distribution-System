<?php
require_once __DIR__ . '/../config/auth_check.php';
require_once __DIR__ . '/../config/db_connect.php';

header('Content-Type: application/json');

// Security check: Only admins can update accounts
if ($_SESSION['role'] !== 'admin') {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized. Only admins can update accounts.']);
    exit;
}

$user_id = (int)($_POST['user_id'] ?? 0);
$fname = trim($_POST['first_name'] ?? '');
$lname = trim($_POST['last_name'] ?? '');
$user  = trim($_POST['username'] ?? '');
$role  = $_POST['role'] ?? 'staff';

if (!$user_id || !$fname || !$lname || !$user) {
    echo json_encode(['status' => 'error', 'message' => 'All fields are required.']);
    exit;
}

// Get current username to check if it's being changed
$get_current_sql = "SELECT username FROM users WHERE id = ?";
$get_current_stmt = mysqli_prepare($conn, $get_current_sql);
mysqli_stmt_bind_param($get_current_stmt, "i", $user_id);
mysqli_stmt_execute($get_current_stmt);
mysqli_stmt_store_result($get_current_stmt);

if (mysqli_stmt_num_rows($get_current_stmt) === 0) {
    echo json_encode(['status' => 'error', 'message' => 'User not found.']);
    mysqli_stmt_close($get_current_stmt);
    exit;
}

mysqli_stmt_bind_result($get_current_stmt, $current_username);
mysqli_stmt_fetch($get_current_stmt);
mysqli_stmt_close($get_current_stmt);

// If username is being changed, check if new username is unique
if ($user !== $current_username) {
    $check_sql = "SELECT id FROM users WHERE username = ?";
    $check_stmt = mysqli_prepare($conn, $check_sql);
    mysqli_stmt_bind_param($check_stmt, "s", $user);
    mysqli_stmt_execute($check_stmt);
    mysqli_stmt_store_result($check_stmt);

    if (mysqli_stmt_num_rows($check_stmt) > 0) {
        echo json_encode(['status' => 'error', 'message' => 'That username is already taken.']);
        mysqli_stmt_close($check_stmt);
        exit;
    }
    mysqli_stmt_close($check_stmt);
}

// Update the account
$update_sql = "UPDATE users SET first_name = ?, last_name = ?, username = ?, role = ? WHERE id = ?";
$stmt = mysqli_prepare($conn, $update_sql);
mysqli_stmt_bind_param($stmt, "ssssi", $fname, $lname, $user, $role, $user_id);

if (mysqli_stmt_execute($stmt)) {
    // Log the audit
    log_audit($conn, (int)$_SESSION['user_id'], 'UPDATE', 'users', "Updated user account ID: $user_id (Username: $user, Role: $role)");
    echo json_encode(['status' => 'success']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Database error while updating account.']);
}

mysqli_stmt_close($stmt);
mysqli_close($conn);
?>
