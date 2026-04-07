<?php
require_once __DIR__ . '/../config/auth_check.php';
require_once __DIR__ . '/../config/db_connect.php';

// Initialize logging array for browser console
$logs = [];

// Helper function to add logs
function addLog(&$logs, $message) {
    $logs[] = $message;
    error_log($message);
}

$rfid_id = trim($_POST['rfid_id'] ?? '');
$rfid_number = trim($_POST['rfid_number'] ?? '');
$household_id = trim($_POST['household_id'] ?? '');

if (empty($rfid_number) || empty($household_id)) {
    echo json_encode(["status" => "missing", "logs" => $logs]);
    exit;
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
    echo json_encode(["status" => "has_active", "logs" => $logs]);
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
if ($check->num_rows > 0) { 
    echo json_encode(["status" => "rfid_exists", "logs" => $logs]);
    exit; 
}

// Start Transaction to safely manage tag states
mysqli_begin_transaction($conn);
try {
    // If issuing a new tag, disable old tags for this household first
    if ($rfid_id === '') {
        addLog($logs, "[RFID] NEW TAG ISSUANCE INITIATED -> HOUSEHOLD_ID: $household_id | RFID_NUMBER: $rfid_number");
        
        $conn->query("UPDATE rfid_tags SET status='Disabled' WHERE household_id = " . intval($household_id));
        $stmt = $conn->prepare("INSERT INTO rfid_tags (household_id, rfid_number, status) VALUES (?, ?, 'Active')");
        $stmt->bind_param("is", $household_id, $rfid_number);
        $stmt->execute();
        $new_rfid_id = mysqli_insert_id($conn);
        
        addLog($logs, "[RFID] NEW TAG CREATED -> NEW_RFID_ID: $new_rfid_id | STATUS: Active");
        
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
        
        addLog($logs, "[RFID] AUDIT LOG RECORDED -> RFID_ID: $new_rfid_id | USER_ID: $current_user_id | HOUSEHOLD: $hh_number");
    } else {
        // UPDATE (OCC Enabled) - Secure One-Step OCC with Detailed Logging
        addLog($logs, "[OCC] ========== RFID EDIT PROCESS STARTED ==========");
        
        $version = isset($_POST['version']) && $_POST['version'] !== '' ? (int)$_POST['version'] : null;
        if ($version === null) { 
            addLog($logs, "[OCC] ✗ FAILED: version parameter is NULL or empty");
            echo json_encode(["status" => "error", "logs" => $logs]);
            exit; 
        }

        addLog($logs, "[OCC] EDIT INITIATED -> RFID_ID: $rfid_id_int | SENT_VERSION: $version | NEW_HOUSEHOLD_ID: $household_id | NEW_RFID_NUMBER: $rfid_number");

        $stmt = $conn->prepare("
            UPDATE rfid_tags
            SET household_id = ?, rfid_number = ?, version = version + 1
            WHERE id = ? AND version = ?
        ");
        $stmt->bind_param("isii", $household_id, $rfid_number, $rfid_id_int, $version);
        $stmt->execute();

        addLog($logs, "[OCC] DATABASE UPDATE -> Affected Rows: " . $stmt->affected_rows);

        if ($stmt->affected_rows === 0) {
            // Check what the actual current version is to help diagnose the conflict
            $conflict_check = mysqli_query($conn, "SELECT version FROM rfid_tags WHERE id = $rfid_id_int");
            $conflict_row = mysqli_fetch_assoc($conflict_check);
            $actual_version = $conflict_row['version'] ?? "NOT_FOUND";
            
            addLog($logs, "[OCC] ✗ CONFLICT DETECTED -> SENT_VERSION: $version | ACTUAL_DB_VERSION: $actual_version (MISMATCH!)");
            addLog($logs, "[OCC] ========== RFID EDIT PROCESS FAILED ==========");
            
            mysqli_rollback($conn);
            echo json_encode(["status" => "conflict", "logs" => $logs]);
            exit;
        }

        addLog($logs, "[OCC] ✓ UPDATE SUCCESS -> VERSION INCREMENTED (old: $version → new: " . ($version + 1) . ")");
        
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
        
        addLog($logs, "[OCC] AUDIT LOG RECORDED -> RFID_ID: $rfid_id_int | USER_ID: $current_user_id | HOUSEHOLD: $hh_number");
        addLog($logs, "[OCC] ========== RFID EDIT PROCESS COMPLETED SUCCESSFULLY ==========");
    }
    
    mysqli_commit($conn);
    echo json_encode(["status" => "success", "logs" => $logs]);
} catch (Exception $e) {
    mysqli_rollback($conn);
    addLog($logs, "[ERROR] Exception caught: " . $e->getMessage());
    echo json_encode(["status" => "error", "logs" => $logs]);
}
$conn->close();

