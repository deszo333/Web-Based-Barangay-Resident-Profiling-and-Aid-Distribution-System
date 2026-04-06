<?php
require_once __DIR__ . '/../config/auth_check.php';
require_once __DIR__ . '/../config/db_connect.php';

header('Content-Type: application/json');

// Security check: Only admins can create accounts
if ($_SESSION['role'] !== 'admin') {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized. Only admins can create accounts.']);
    exit;
}

$fname = trim($_POST['first_name'] ?? '');
$lname = trim($_POST['last_name'] ?? '');
$user  = trim($_POST['username'] ?? '');
$pass  = $_POST['password'] ?? '';
$role  = $_POST['role'] ?? 'staff';

if (!$fname || !$lname || !$user || !$pass) {
    echo json_encode(['status' => 'error', 'message' => 'All fields are required.']);
    exit;
}

// Ensure username is unique
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

// Hash the password and force active status
$hashed = password_hash($pass, PASSWORD_DEFAULT);
$status = 'Active'; 

$insert_sql = "INSERT INTO users (first_name, last_name, username, password, role, status) VALUES (?, ?, ?, ?, ?, ?)";
$stmt = mysqli_prepare($conn, $insert_sql);
mysqli_stmt_bind_param($stmt, "ssssss", $fname, $lname, $user, $hashed, $role, $status);

if (mysqli_stmt_execute($stmt)) {
    // Log the audit
    log_audit($conn, (int)$_SESSION['user_id'], 'CREATE', 'users', "Created new user account: $user (Role: $role)");
    echo json_encode(['status' => 'success']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Database error while creating account.']);
}

mysqli_stmt_close($stmt);
mysqli_close($conn);
?>
