<?php
require_once __DIR__ . '/../config/auth_check.php';
require_once __DIR__ . '/../config/db_connect.php';

// Security check: Only admins can update accounts
if ($_SESSION['role'] !== 'admin') {
    echo "error|" . json_encode(['message' => 'Unauthorized. Only admins can update accounts.']);
    exit;
}

$user_id = (int)($_POST['user_id'] ?? 0);
$version = isset($_POST['version']) && $_POST['version'] !== '' ? (int)$_POST['version'] : null;
$fname = trim($_POST['first_name'] ?? '');
$lname = trim($_POST['last_name'] ?? '');
$user  = trim($_POST['username'] ?? '');
$role  = $_POST['role'] ?? 'staff';

// ============================================
// OCC: CHECK VERSION PARAMETER
// ============================================
if ($version === null) {
    echo "error|" . json_encode(['message' => 'Version information missing']);
    exit;
}

if (!$user_id || !$fname || !$lname || !$user) {
    echo "error|" . json_encode(['message' => 'All fields are required.']);
    exit;
}

// Get current username to check if it's being changed
$get_current_sql = "SELECT username FROM users WHERE id = ?";
$get_current_stmt = mysqli_prepare($conn, $get_current_sql);
mysqli_stmt_bind_param($get_current_stmt, "i", $user_id);
mysqli_stmt_execute($get_current_stmt);
mysqli_stmt_store_result($get_current_stmt);

if (mysqli_stmt_num_rows($get_current_stmt) === 0) {
    echo "error|" . json_encode(['message' => 'User not found.']);
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
        echo "error|" . json_encode(['message' => 'That username is already taken.']);
        mysqli_stmt_close($check_stmt);
        exit;
    }
    mysqli_stmt_close($check_stmt);
}

// ============================================
// OCC: FETCH DB VERSION BEFORE UPDATE
// ============================================
$pre = mysqli_prepare($conn, "SELECT version FROM users WHERE id=?");
mysqli_stmt_bind_param($pre, "i", $user_id);
mysqli_stmt_execute($pre);
mysqli_stmt_bind_result($pre, $pre_version);
mysqli_stmt_fetch($pre);
mysqli_stmt_close($pre);

// ============================================
// OCC: PERFORM UPDATE WITH VERSION CHECK
// ============================================
$update_sql = "UPDATE users SET first_name = ?, last_name = ?, username = ?, role = ?, version = version + 1 WHERE id = ? AND version = ?";
$stmt = mysqli_prepare($conn, $update_sql);
mysqli_stmt_bind_param($stmt, "ssssii", $fname, $lname, $user, $role, $user_id, $version);
mysqli_stmt_execute($stmt);
mysqli_stmt_close($stmt);

// ============================================
// OCC: VERIFY THE UPDATE ACTUALLY HAPPENED
// ============================================
$pre_version = (int) $pre_version;

if ($pre_version === $version) {
    // Our version matched what was in DB before update = we owned this update
    // Log the audit
    log_audit($conn, (int)$_SESSION['user_id'], 'UPDATE', 'users', "Updated user account ID: $user_id (Username: $user, Role: $role)");
    echo "success";
} else {
    // Version mismatch = conflict
    echo "conflict";
}

mysqli_close($conn);
?>
