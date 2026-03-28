<?php
require_once 'db_connect.php';

$rfid_id = trim($_POST['rfid_id'] ?? '');
$rfid_number = trim($_POST['rfid_number'] ?? '');
$household_id = trim($_POST['household_id'] ?? '');

if (empty($rfid_number) || empty($household_id)) {
    echo "missing"; exit;
}

$household_id = (int)$household_id;
$rfid_id_int = !empty($rfid_id) ? (int)$rfid_id : 0;

$check_sql = "SELECT rfid_number FROM rfid_tags WHERE household_id = ? AND status = 'Active'";

if ($rfid_id_int !== 0) {
    $check_sql .= " AND id != ?";
}

$stmt_check = mysqli_prepare($conn, $check_sql);

if ($rfid_id_int !== 0) {
    mysqli_stmt_bind_param($stmt_check, "ii", $household_id, $rfid_id_int);
} else {
    mysqli_stmt_bind_param($stmt_check, "i", $household_id);
}

mysqli_stmt_execute($stmt_check);
$check_result = mysqli_stmt_get_result($stmt_check);

if (mysqli_num_rows($check_result) > 0) {
    echo "has_active";
    mysqli_stmt_close($stmt_check);
    mysqli_close($conn);
    exit;
}
mysqli_stmt_close($stmt_check);

// Check for duplicate RFID number globally
if ($rfid_id !== '') {
    $check = $conn->prepare("SELECT 1 FROM rfid_tags WHERE rfid_number = ? AND id != ?");
    $check->bind_param("si", $rfid_number, $rfid_id);
} else {
    $check = $conn->prepare("SELECT 1 FROM rfid_tags WHERE rfid_number = ?");
    $check->bind_param("s", $rfid_number);
}
$check->execute(); $check->store_result();
if ($check->num_rows > 0) { echo "rfid_exists"; exit; }

// Start Transaction to safely manage tag states
mysqli_begin_transaction($conn);
try {
    // If issuing a new tag, disable old tags for this household first
    if ($rfid_id === '') {
        $conn->query("UPDATE rfid_tags SET status='Disabled' WHERE household_id = " . intval($household_id));
        $stmt = $conn->prepare("INSERT INTO rfid_tags (household_id, rfid_number, status) VALUES (?, ?, 'Active')");
        $stmt->bind_param("is", $household_id, $rfid_number);
        $stmt->execute();
        $new_rfid_id = mysqli_insert_id($conn);
        
        $current_user_id = $_SESSION['user_id'] ?? null;
        
        // Get household number for readable details
        $hhInfo = mysqli_fetch_assoc(mysqli_query($conn, "SELECT household_number FROM registered_household WHERE id = $household_id"));
        $hh_number = $hhInfo['household_number'] ?? "ID: $household_id";
        
        // Log RFID tag issuance
        $audit_data = [
            "action_summary" => "RFID Tag Issued",
            "rfid_number" => $rfid_number,
            "rfid_id" => $new_rfid_id,
            "household_number" => $hh_number,
            "status" => "Active"
        ];
        log_audit($conn, $current_user_id, "Create", "RFID Tag Issuance", json_encode($audit_data));
    } else {
        // UPDATE (OCC Enabled)
        $version = isset($_POST['version']) && $_POST['version'] !== '' ? (int)$_POST['version'] : null;
        if ($version === null) { echo "error"; exit; }

        $stmt = $conn->prepare("UPDATE rfid_tags SET household_id = ?, rfid_number = ?, version=version+1 WHERE id = ? AND version=?");
        $stmt->bind_param("isii", $household_id, $rfid_number, $rfid_id, $version);
        $stmt->execute();
        
        // Check for conflict
        if ($stmt->affected_rows === 0) {
            echo "conflict";
            mysqli_rollback($conn);
            $conn->close();
            exit;
        }
        
        // === TRIGGER AUDIT LOG FOR RFID EDIT ===
        // Get household number for readable audit details
        $hhInfo = mysqli_fetch_assoc(mysqli_query($conn, "SELECT household_number FROM registered_household WHERE id = $household_id"));
        $hh_number = $hhInfo['household_number'] ?? "ID: $household_id";
        
        $current_user_id = $_SESSION['user_id'] ?? null;
        $audit_data = [
            "action_summary" => "RFID Tag Reassigned",
            "rfid_number" => $rfid_number,
            "rfid_id" => $rfid_id,
            "household_number" => $hh_number
        ];
        log_audit($conn, $current_user_id, "Update", "RFID Management", json_encode($audit_data));
    }
    
    mysqli_commit($conn);
    echo "success";
} catch (Exception $e) {
    mysqli_rollback($conn);
    echo "error";
}
$conn->close();
