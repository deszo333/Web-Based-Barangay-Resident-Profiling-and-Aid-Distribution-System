<?php
require_once __DIR__ . '/../config/auth_check.php';
require_once __DIR__ . '/../config/db_connect.php';

header('Content-Type: application/json');

// grab the id 
$data = json_decode(file_get_contents('php://input'), true);
$id = isset($data['id']) ? (int)$data['id'] : (isset($_POST['id']) ? (int)$_POST['id'] : 0);

if ($id === 0) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid User ID.']);
    exit;
}

//  prevent the logged-in user from deactivating srili
if ($id === (int)$_SESSION['user_id']) {
    echo json_encode(['status' => 'error', 'message' => 'You cannot change your own account status.']);
    exit;
}

// get status
$sql = "SELECT status FROM users WHERE id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if ($row = mysqli_fetch_assoc($result)) {
    // FIX: Safely check lowercase, but push Capitalized to the database
    $currentStatus = strtolower($row['status']);
    $newStatus = ($currentStatus === 'active') ? 'Inactive' : 'Active';

    // update status
    $update_stmt = mysqli_prepare($conn, "UPDATE users SET status = ? WHERE id = ?");
    mysqli_stmt_bind_param($update_stmt, "si", $newStatus, $id);
    
    if (mysqli_stmt_execute($update_stmt)) {
        // Get username for audit
        $userInfo = mysqli_fetch_assoc(mysqli_query($conn, "SELECT username, CONCAT(first_name, ' ', last_name) as full_name FROM users WHERE id = $id"));
        $audit_data = [
            "action_summary" => "User Account Status Changed to $newStatus",
            "target_user" => $userInfo['full_name'] ?? "ID: $id",
            "username" => $userInfo['username'] ?? "",
            "user_id" => $id,
            "new_status" => $newStatus
        ];
        log_audit($conn, (int)$_SESSION['user_id'], "Update", "Account Management", json_encode($audit_data));
        echo json_encode(['status' => 'success', 'new_status' => $newStatus]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Database update failed.']);
    }
    mysqli_stmt_close($update_stmt);
} else {
    echo json_encode(['status' => 'error', 'message' => 'User not found.']);
}

mysqli_stmt_close($stmt);
mysqli_close($conn);
?>