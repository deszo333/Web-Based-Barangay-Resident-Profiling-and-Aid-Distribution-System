<?php
require_once __DIR__ . '/../config/auth_check.php';
require_once __DIR__ . '/../config/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $id = intval($_POST['id']);

    // Fetch program info before deletion
    $infoRes = mysqli_fetch_assoc(mysqli_query($conn, "SELECT program_name, aid_type FROM aid_program WHERE id = $id"));
    $prog_name = $infoRes['program_name'] ?? "ID: $id";
    $aid_type  = $infoRes['aid_type'] ?? "";

    $sql = "DELETE FROM aid_program WHERE id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $id);

    if (mysqli_stmt_execute($stmt)) {
        $current_user_id = $_SESSION['user_id'] ?? null;
        $audit_data = [
            "action_summary" => "Aid Program Deleted",
            "program_name" => $prog_name,
            "program_id" => $id,
            "aid_type" => $aid_type
        ];
        log_audit($conn, $current_user_id, "Delete", "Aid Program Setup", json_encode($audit_data));
        echo "success";
    } else {
        error_log("Delete Aid Program Error: " . mysqli_error($conn));
        echo "error";
    }

    mysqli_stmt_close($stmt);
} else {
    echo "Invalid request";
}

mysqli_close($conn);
?>
