<?php
require_once __DIR__ . '/../config/auth_check.php';
require_once __DIR__ . '/../config/db_connect.php';

$id = $_POST['id'] ?? '';
$program_name = $_POST['program_name'];
$aid_type = $_POST['aid_type'];
$date_scheduled = $_POST['date_scheduled'];
$beneficiaries = $_POST['beneficiaries'];
$status = $_POST['status'];

if ($id == "") {
    // INSERT
    $stmt = mysqli_prepare($conn,
        "INSERT INTO aid_program 
        (program_name, aid_type, date_scheduled, beneficiaries, status)
        VALUES (?, ?, ?, ?, ?)"
    );
    mysqli_stmt_bind_param($stmt, "sssis",
        $program_name, $aid_type, $date_scheduled, $beneficiaries, $status
    );
    
    if (mysqli_stmt_execute($stmt)) {
        $new_id = mysqli_insert_id($conn);
        $current_user_id = $_SESSION['user_id'] ?? null;
        
        // Log program creation
        $audit_data = [
            "action_summary" => "New Aid Program Created",
            "program_name" => $program_name,
            "program_id" => $new_id,
            "aid_type" => $aid_type,
            "date_scheduled" => $date_scheduled,
            "beneficiaries" => $beneficiaries,
            "status" => $status
        ];
        log_audit($conn, $current_user_id, "Create", "Aid Program Setup", json_encode($audit_data));
        
        echo "success";
    } else {
        echo "error";
    }
} else {
    // UPDATE (OCC Enabled)
    $version = isset($_POST['version']) && $_POST['version'] !== '' ? (int)$_POST['version'] : null;
    if ($version === null) { echo "error"; exit; }

    // 1. DO THE OCC CHECK FIRST (before version is incremented)
    $check_exists = mysqli_query($conn, "SELECT id FROM aid_program WHERE id=$id AND version=$version");
    if (mysqli_num_rows($check_exists) === 0) {
        echo "conflict";
        mysqli_close($conn);
        exit;
    }

    // 2. THEN DO THE UPDATE (now version will be incremented)
    $stmt = mysqli_prepare($conn,
        "UPDATE aid_program SET
            program_name=?, aid_type=?, date_scheduled=?, beneficiaries=?, status=?, version=version+1
        WHERE id=? AND version=?"
    );
    
    // THE FIX: Exactly 7 letters (sssisii) for 7 variables
    mysqli_stmt_bind_param($stmt, "sssisii",
        $program_name, $aid_type, $date_scheduled, $beneficiaries, $status, $id, $version
    );
    mysqli_stmt_execute($stmt);
    
    // === TRIGGER AUDIT LOG FOR AID PROGRAM EDIT ===
    $current_user_id = $_SESSION['user_id'] ?? null;
    $audit_data = [
        "action_summary" => "Aid Program Updated",
        "program_name" => $program_name,
        "program_id" => $id,
        "aid_type" => $aid_type,
        "date_scheduled" => $date_scheduled,
        "beneficiaries" => $beneficiaries,
        "status" => $status
    ];
    log_audit($conn, $current_user_id, "Update", "Aid Programs", json_encode($audit_data));
    
    echo "success";
}

mysqli_stmt_close($stmt);
mysqli_close($conn);
?>
