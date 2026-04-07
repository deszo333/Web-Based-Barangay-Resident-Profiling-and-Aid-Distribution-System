<?php
require_once __DIR__ . '/../config/auth_check.php';
require_once __DIR__ . '/../config/db_connect.php';
$current_user_id = $_SESSION['user_id'] ?? null;

$id      = (int) $_POST['resident_id'];
$version = isset($_POST['version']) && $_POST['version'] !== '' ? (int)$_POST['version'] : null;

if ($version === null) {
    echo "error";
    exit;
}

$first       = $_POST['first_name'];
$middle      = $_POST['middle_name'];
$last        = $_POST['last_name'];
$address     = $_POST['address'];
$birthdate   = $_POST['birthdate'];
$age         = $_POST['age'];
$gender      = $_POST['gender'];
$civil       = $_POST['civil_status'];
$occupation  = $_POST['occupation'];
$voter_status = $_POST['voter_status'] ?? 'No'; // Get voter status
$voters_registration_no = trim($_POST['voters_registration_no'] ?? '');
$contact     = trim($_POST['contact'] ?? '');

// ============================================
// HANDLE VOTER REGISTRATION
// ============================================
// If voter status is "Yes" but no number provided, mark as registered without number
if ($voter_status === 'Yes' && $voters_registration_no === '') {
    $voters_registration_no = "Registered - No Number";
} elseif ($voter_status === 'No' || empty($voter_status)) {
    // If status is "No", mark as not registered
    $voters_registration_no = "Not Registered";
}

if ($contact === '')               $contact = "N/A";

if (empty($occupation)) {
    $occupation = "N/A";
}

// ============================================
// VALIDATE BIRTHDATE
// ============================================
$validation_error = null;

// Check if birthdate is provided
if (empty($birthdate)) {
    $validation_error = "Birthdate is required";
} else {
    // Validate birthdate format (should be YYYY-MM-DD)
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $birthdate)) {
        $validation_error = "Invalid birthdate format";
    } else {
        // Parse the birthdate
        $birthdate_obj = DateTime::createFromFormat('Y-m-d', $birthdate);
        
        if (!$birthdate_obj) {
            $validation_error = "Invalid date provided";
        } else {
            // Check if birthdate is not in the future
            $today = new DateTime();
            if ($birthdate_obj > $today) {
                $validation_error = "Birthdate cannot be in the future";
            } else {
                // Calculate age
                $interval = $today->diff($birthdate_obj);
                $calculated_age = $interval->y;
                
                // Check reasonable age range (0-150 years)
                if ($calculated_age > 150) {
                    $validation_error = "Age cannot exceed 150 years";
                }
            }
        }
    }
}

// If validation failed, return error
if ($validation_error) {
    echo "error|" . json_encode(['message' => $validation_error]);
    mysqli_close($conn);
    exit();
}

// Fetch DB version BEFORE update
$pre = mysqli_prepare($conn, "SELECT version FROM registered_resi WHERE id=?");
mysqli_stmt_bind_param($pre, "i", $id);
mysqli_stmt_execute($pre);
mysqli_stmt_bind_result($pre, $pre_version);
mysqli_stmt_fetch($pre);
mysqli_stmt_close($pre);

$stmt = mysqli_prepare($conn,
    "UPDATE registered_resi SET
        first_name=?, middle_name=?, last_name=?, address=?, birthdate=?,
        age=?, gender=?, civil_status=?, occupation=?,
        voters_registration_no=?, contact=?,
        version = version + 1
    WHERE id=? AND version=?"
);

mysqli_stmt_bind_param(
    $stmt, "sssssisssssii",
    $first, 
    $middle, 
    $last, 
    $address, 
    $birthdate,
    $age, 
    $gender, 
    $civil, 
    $occupation,
    $voters_registration_no, 
    $contact,
    $id, 
    $version
);

mysqli_stmt_execute($stmt);
mysqli_stmt_close($stmt);

// Correct check: use pre_version to confirm the WHERE clause matched
$pre_version = (int) $pre_version;

if ($pre_version === $version) {
    // Our version matched what was in DB before update = we owned this update
    
    // === TRIGGER AUDIT LOG FOR RESIDENT EDIT ===
    // Get resident's full name for readable audit details
    $residentInfo = mysqli_fetch_assoc(mysqli_query($conn, "SELECT CONCAT(first_name, ' ', last_name) as full_name FROM registered_resi WHERE id = $id"));
    $resident_name = $residentInfo['full_name'] ?? "ID: $id";
    
    $audit_data = [
        "action_summary" => "Resident Profile Updated",
        "resident_name" => $resident_name,
        "resident_id" => $id,
        "fields_modified" => "Multiple profile fields"
    ];
    log_audit($conn, $current_user_id, "Update", "Resident Profiling", json_encode($audit_data));
    
    echo "success";
} else {
    // DB was already ahead before we even ran = someone else updated first
    echo "conflict";
}

mysqli_close($conn);