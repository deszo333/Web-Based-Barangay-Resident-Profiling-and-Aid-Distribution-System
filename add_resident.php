<?php
$conn = mysqli_connect("localhost", "root", "Password", "barangay_db");
if (!$conn) {
    echo "error";
    exit;
}

$first = $_POST['first_name'] ?? '';
$middle = $_POST['middle_name'] ?? '';
$last = $_POST['last_name'] ?? '';
$address = $_POST['address'] ?? '';
$birthdate = $_POST['birthdate'] ?? '';
$age = $_POST['age'] ?? '';
$gender = $_POST['gender'] ?? '';
$civil = $_POST['civil_status'] ?? '';
$occupation = $_POST['occupation'] ?? '';
$voters_registration_no = $_POST['voters_registration_no'] ?? '';
$contact = $_POST['contact'] ?? '';

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

echo mysqli_stmt_execute($stmt) ? "success" : "error";

mysqli_stmt_close($stmt);
mysqli_close($conn);
?>