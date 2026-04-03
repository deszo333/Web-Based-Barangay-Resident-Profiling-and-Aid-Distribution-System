<?php
require_once __DIR__ . '/../config/auth_check.php';
require_once __DIR__ . '/../config/db_connect.php';

$id = $_POST['id'];
$stmt = mysqli_prepare($conn, "UPDATE registered_resi SET is_archived = 1 WHERE id=?");
mysqli_stmt_bind_param($stmt, "i", $id);

if (mysqli_stmt_execute($stmt)) {
    $current_user_id = $_SESSION['user_id'] ?? null;
    
    // Get resident's full name
    $residentInfo = mysqli_fetch_assoc(mysqli_query($conn, "SELECT CONCAT(first_name, ' ', last_name) as full_name FROM registered_resi WHERE id = $id"));
    $resident_name = $residentInfo['full_name'] ?? "ID: $id";
    
    // Log the archiving action
    $audit_data = [
        "action_summary" => "Resident Profile Archived",
        "resident_name" => $resident_name,
        "resident_id" => $id
    ];
    log_audit($conn, $current_user_id, "Archive", "Resident Profiling", json_encode($audit_data));
    
    echo "success";
} else {
    echo "error";
}

mysqli_stmt_close($stmt);
mysqli_close($conn);
?>