<?php
require 'auth_check.php';
require_once 'db_connect.php';

if($_SERVER['REQUEST_METHOD'] === 'POST'){

    $id = trim($_POST['resident_id'] ?? '');
    $household_number = trim($_POST['household_number'] ?? '');
    
    // CHANGED: We now receive IDs instead of names
    $head_of_family_id = trim($_POST['head_of_family_id'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $household_member_ids = trim($_POST['household_member_ids'] ?? '');
    $rfid_raw = trim($_POST['rfid'] ?? '');

    if($address === '' || $head_of_family_id === ''){
        echo "Please fill all required fields";
        exit;
    }

    $head_id = $_POST['head_of_family_id'];
    $current_household_id = isset($_POST['resident_id']) ? $_POST['resident_id'] : 0;

    $check_head_sql = "SELECT household_number FROM registered_household WHERE head_of_family_id = ? AND is_archived = 0";
    
    if (!empty($current_household_id)) {
        $check_head_sql .= " AND id != ?";
    }

    $stmt_check = mysqli_prepare($conn, $check_head_sql);
    
    if (!empty($current_household_id)) {
        mysqli_stmt_bind_param($stmt_check, "ii", $head_id, $current_household_id);
    } else {
        mysqli_stmt_bind_param($stmt_check, "i", $head_id);
    }

    mysqli_stmt_execute($stmt_check);
    $check_result = mysqli_stmt_get_result($stmt_check);

    if ($row = mysqli_fetch_assoc($check_result)) {
        echo "head_conflict:" . $row['household_number'];
        mysqli_stmt_close($stmt_check);
        mysqli_close($conn);
        exit;
    }
    mysqli_stmt_close($stmt_check);

    // Convert member IDs to array and ensure Head is included
    $member_ids = array_filter(array_map('intval', explode(',', $household_member_ids)));
    if (!in_array((int)$head_of_family_id, $member_ids)) {
        $member_ids[] = (int)$head_of_family_id;
    }

    // START TRANSACTION
    mysqli_begin_transaction($conn);

    try {
        if($id === '') {
            // ===== AUTO-GENERATE HOUSEHOLD NUMBER =====
            $result = mysqli_query($conn, "SELECT household_number FROM registered_household ORDER BY id DESC LIMIT 1 FOR UPDATE");
            if($row = mysqli_fetch_assoc($result)){
                $lastNum = intval(substr($row['household_number'], 3));
                $newNum = $lastNum + 1;
            } else {
                $newNum = 1;
            }
            $household_number = 'HH-' . str_pad($newNum, 5, '0', STR_PAD_LEFT);
            // ========================================

            // INSERT new household 
            $stmt = mysqli_prepare($conn, "INSERT INTO registered_household (household_number, head_of_family_id, address) VALUES (?, ?, ?)");
            mysqli_stmt_bind_param($stmt, "sis", $household_number, $head_of_family_id, $address);
            mysqli_stmt_execute($stmt);
            $household_id = mysqli_insert_id($conn);
            
            // Log household creation
            $current_user_id = $_SESSION['user_id'] ?? null;
            $audit_data = [
                "action_summary" => "New Household Created",
                "household_number" => $household_number,
                "head_of_family_id" => $head_of_family_id,
                "address" => $address
            ];
            log_audit($conn, $current_user_id, "Create", "Household Management", json_encode($audit_data));

        } else {
            // UPDATE existing household with occ
            $household_id = $id;
            $version = isset($_POST['version']) && $_POST['version'] !== '' ? (int)$_POST['version'] : null;
            if ($version === null) { echo "error"; exit; }

            $stmt = mysqli_prepare($conn, "UPDATE registered_household SET head_of_family_id=?, address=?, version=version+1 WHERE id=? AND version=?");
            mysqli_stmt_bind_param($stmt, "isii", $head_of_family_id, $address, $household_id, $version);
            mysqli_stmt_execute($stmt);
            
            // If 0 rows affected, the version didnt match 
            if (mysqli_stmt_affected_rows($stmt) === 0) {
                echo "conflict";
                mysqli_rollback($conn);
                exit;
            }
            
            // === TRIGGER AUDIT LOG FOR HOUSEHOLD EDIT ===
            // Get household number and head's name for readable audit details
            $hhInfo = mysqli_fetch_assoc(mysqli_query($conn, "SELECT household_number FROM registered_household WHERE id = $household_id"));
            $headInfo = mysqli_fetch_assoc(mysqli_query($conn, "SELECT CONCAT(first_name, ' ', last_name) as full_name FROM registered_resi WHERE id = $head_of_family_id"));
            $head_name = $headInfo['full_name'] ?? "ID: $head_of_family_id";
            
            $current_user_id = $_SESSION['user_id'] ?? null;
            $audit_data = [
                "action_summary" => "Household Profile Updated",
                "household_number" => $hhInfo['household_number'],
                "head_of_family" => $head_name,
                "address" => $address
            ];
            log_audit($conn, $current_user_id, "Update", "Household Management", json_encode($audit_data));

            // Unlink old members first
            mysqli_query($conn, "UPDATE registered_resi SET household_id = NULL WHERE household_id = $household_id");
        }

        // LINK ALL RESIDENTS TO THIS HOUSEHOLD
        if (count($member_ids) > 0) {
            $ids_string = implode(',', $member_ids);
            mysqli_query($conn, "UPDATE registered_resi SET household_id = $household_id WHERE id IN ($ids_string)");
        }

        // HARDWARE RFID SYNC
        if ($rfid_raw !== '') {
            mysqli_query($conn, "UPDATE rfid_tags SET status='Disabled' WHERE household_id = $household_id AND rfid_number != '$rfid_raw'");
            $rfid_stmt = mysqli_prepare($conn, "INSERT INTO rfid_tags (household_id, rfid_number, status) VALUES (?, ?, 'Active') ON DUPLICATE KEY UPDATE household_id=VALUES(household_id), status='Active'");
            mysqli_stmt_bind_param($rfid_stmt, "is", $household_id, $rfid_raw);
            mysqli_stmt_execute($rfid_stmt);
        }

        mysqli_commit($conn);
        echo "success";

    } catch (Exception $e) {
        mysqli_rollback($conn);
        error_log("Add Household DB Error: " . $e->getMessage());
        echo "error";
    }

    mysqli_close($conn);

} else {
    echo "Invalid request";
}
?>