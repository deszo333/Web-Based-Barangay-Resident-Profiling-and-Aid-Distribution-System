<?php
$conn = mysqli_connect("localhost", "root", "Password", "barangay_db");

$id = $_POST['resident_id'];
$first = $_POST['first_name'];
$middle = $_POST['middle_name'];
$last = $_POST['last_name'];
$address = $_POST['address'];
$birthdate = $_POST['birthdate'];
$age = $_POST['age'];
$gender = $_POST['gender'];
$civil = $_POST['civil_status'];
$occupation = $_POST['occupation'];
$voters_registration_no = $_POST['voters_registration_no'];
$contact = $_POST['contact'];

$stmt = mysqli_prepare($conn,
    "UPDATE registered_resi SET
    first_name=?, middle_name=?, last_name=?, address=?, birthdate=?, age=?, gender=?, civil_status=?, occupation=?, voters_registration_no=?, contact=?
    WHERE id=?"
);

mysqli_stmt_bind_param(
    $stmt,
    "sssissssi",
    $first, $middle, $last, $address, $birthdate, $age, $gender, $civil, $occupation, $voters_registration_no, $contact, $id
);

echo mysqli_stmt_execute($stmt) ? "success" : "error";

mysqli_close($conn);
