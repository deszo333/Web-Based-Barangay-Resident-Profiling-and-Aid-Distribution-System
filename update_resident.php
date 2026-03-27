<?php
require_once 'db_connect.php';
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
$voters_registration_no = trim($_POST['voters_registration_no'] ?? '');
$contact     = trim($_POST['contact'] ?? '');

if ($voters_registration_no === '') $voters_registration_no = "Not Registered";
if ($contact === '')               $contact = "N/A";

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