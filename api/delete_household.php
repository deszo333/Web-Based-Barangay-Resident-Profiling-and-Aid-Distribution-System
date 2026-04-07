<?php
require_once __DIR__ . '/../config/auth_check.php';
require_once __DIR__ . '/../config/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? '';
    if ($id === '') { echo "Invalid ID"; exit; }

    mysqli_begin_transaction($conn);
    try {
        // Get info BEFORE archiving for the audit log
        $infoRes = mysqli_fetch_assoc(mysqli_query($conn, "SELECT h.household_number, CONCAT(head.first_name, ' ', head.last_name) as head_name FROM registered_household h LEFT JOIN registered_resi head ON h.head_of_family_id = head.id WHERE h.id = $id"));
        $hh_number = $infoRes['household_number'] ?? "ID: $id";
        $head_name = $infoRes['head_name'] ?? "Unknown";

        $stmt1 = mysqli_prepare($conn, "UPDATE registered_household SET is_archived = 1 WHERE id = ?");
        mysqli_stmt_bind_param($stmt1, "i", $id);
        mysqli_stmt_execute($stmt1);
        mysqli_stmt_close($stmt1);

        $stmt2 = mysqli_prepare($conn, "UPDATE registered_resi SET household_id = NULL WHERE household_id = ?");
        mysqli_stmt_bind_param($stmt2, "i", $id);
        mysqli_stmt_execute($stmt2);
        mysqli_stmt_close($stmt2);

        $stmt3 = mysqli_prepare($conn, "UPDATE rfid_tags SET status = 'Disabled' WHERE household_id = ?");
        mysqli_stmt_bind_param($stmt3, "i", $id);
        mysqli_stmt_execute($stmt3);
        mysqli_stmt_close($stmt3);

        $current_user_id = $_SESSION['user_id'] ?? null;
        $audit_data = [
            "action_summary" => "Household Archived",
            "household_number" => $hh_number,
            "head_of_family" => $head_name,
            "household_id" => $id
        ];
        log_audit($conn, $current_user_id, "Archive", "Household Management", json_encode($audit_data));

        mysqli_commit($conn);
        echo "success";
    } catch (Exception $e) {
        mysqli_rollback($conn);
        error_log("Household Delete Failed: " . $e->getMessage());
        echo "error";
    }
    mysqli_close($conn);
} else {
    echo "Invalid request";
}
?>