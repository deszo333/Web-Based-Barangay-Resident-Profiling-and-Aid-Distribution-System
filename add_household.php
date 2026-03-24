<?php
require 'auth_check.php';

// DB connection
$conn = mysqli_connect("localhost", "root", "Password", "barangay_db");
if(!$conn){
    error_log("Database connection failed: " . mysqli_connect_error());
    die("error");
}

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

        } else {
            // UPDATE existing household
            $household_id = $id;
            $stmt = mysqli_prepare($conn, "UPDATE registered_household SET head_of_family_id=?, address=? WHERE id=?");
            mysqli_stmt_bind_param($stmt, "isi", $head_of_family_id, $address, $household_id);
            mysqli_stmt_execute($stmt);

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