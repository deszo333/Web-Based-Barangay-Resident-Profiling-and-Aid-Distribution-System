<?php
require_once __DIR__ . '/../config/auth_check.php';
// 1. Require our new hub
require_once __DIR__ . '/../config/db_connect.php';

$first = $_POST['first_name'] ?? '';
$middle = $_POST['middle_name'] ?? '';
$last = $_POST['last_name'] ?? '';
$address = $_POST['address'] ?? '';
$birthdate = $_POST['birthdate'] ?? '';
$age = $_POST['age'] ?? '';
$gender = $_POST['gender'] ?? '';
$civil = $_POST['civil_status'] ?? '';
$occupation = $_POST['occupation'] ?? '';
$voters_registration_no = trim($_POST['voters_registration_no'] ?? '');
$contact = trim($_POST['contact'] ?? '');

if ($voters_registration_no === '') {
    $voters_registration_no = "Not Registered";
}

if ($contact === '') {
    $contact = "N/A";
}

$stmt = mysqli_prepare($conn,
    "INSERT INTO registered_resi
    (first_name, middle_name, last_name, address, birthdate, age, gender, civil_status, occupation, voters_registration_no, contact)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
);
mysqli_stmt_bind_param(
    $stmt,
    "sssssisssss",
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
    $contact
);

// 2. Execute and Trigger Audit Log
if (mysqli_stmt_execute($stmt)) {
    $new_id = mysqli_insert_id($conn);
    $current_user_id = $_SESSION['user_id'] ?? null;
    $full_name = trim("$first $middle $last");
    
    // Log the creation
    $audit_data = [
        "action_summary" => "New Resident Profile Created",
        "resident_name" => $full_name,
        "resident_id" => $new_id,
        "address" => $address,
        "birthdate" => $birthdate
    ];
    log_audit($conn, $current_user_id, "Create", "Resident Profiling", json_encode($audit_data));
    
    echo "success";
} else {
    echo "error";
}

mysqli_stmt_close($stmt);
mysqli_close($conn);
?>