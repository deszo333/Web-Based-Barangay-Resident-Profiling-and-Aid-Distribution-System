<?php
$conn = mysqli_connect("localhost", "root", "Password", "barangay_db");
if (!$conn) {
    echo "error";
    exit;
}

$first = $_POST['first_name'] ?? '';
$middle = $_POST['middle_name'] ?? '';
$last = $_POST['last_name'] ?? '';
$age = $_POST['age'] ?? '';
$gender = $_POST['gender'] ?? '';
$civil = $_POST['civil_status'] ?? '';
$occupation = $_POST['occupation'] ?? '';
$contact = $_POST['contact'] ?? '';

$stmt = mysqli_prepare($conn,
    "INSERT INTO registered_resi
    (first_name, middle_name, last_name, age, gender, civil_status, occupation, contact)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
);

mysqli_stmt_bind_param(
    $stmt,
    "sssissss",
    $first,
    $middle,
    $last,
    $age,
    $gender,
    $civil,
    $occupation,
    $contact
);

echo mysqli_stmt_execute($stmt) ? "success" : "error";

mysqli_stmt_close($stmt);
mysqli_close($conn);
?>