<?php
$conn = mysqli_connect("localhost", "root", "Password", "barangay_db");

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

mysqli_close($conn);

// Correct check: use pre_version to confirm the WHERE clause matched
$pre_version = (int) $pre_version;

if ($pre_version === $version) {
    // Our version matched what was in DB before update = we owned this update
    echo "success";
} else {
    // DB was already ahead before we even ran = someone else updated first
    echo "conflict";
}