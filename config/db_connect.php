<?php
// FIX: Pointing strictly to our portable MySQL port 3307 to avoid PC conflicts
$conn = mysqli_connect("127.0.0.1", "root", "", "barangay_db", 3307);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Global Audit Function mapped exactly to your barangay_db.sql schema
function log_audit($conn, $user_id, $action_type, $target_module, $details) {
    $stmt = mysqli_prepare($conn, "INSERT INTO audit_logs (user_id, action_type, target_module, details) VALUES (?, ?, ?, ?)");
    
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "isss", $user_id, $action_type, $target_module, $details);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    } else {
        error_log("Audit Log Failed: " . mysqli_error($conn));
    }
}
?>