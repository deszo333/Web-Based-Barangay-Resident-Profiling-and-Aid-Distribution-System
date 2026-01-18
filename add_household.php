<?php
session_start();

// DB connection
$conn = mysqli_connect("localhost", "root", "Password", "barangay_db");
if(!$conn){
    die("Connection failed: " . mysqli_connect_error());
}

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $id = trim($_POST['resident_id'] ?? '');
    $household_number = trim($_POST['household_number'] ?? '');
    $head_of_family = trim($_POST['head_of_family'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $household_members = trim($_POST['household_members'] ?? '');

    if($household_number === '' || $address === '' || $household_members === ''){
        echo "Please fill all required fields";
        exit;
    }

    if($id === '') {
        // INSERT new household
        $stmt = mysqli_prepare($conn, "INSERT INTO registered_household (household_number, head_of_family, address, household_members) VALUES (?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "ssss", $household_number, $head_of_family, $address, $household_members);
    } else {
        // UPDATE existing household
        $stmt = mysqli_prepare($conn, "UPDATE registered_household SET household_number=?, head_of_family=?, address=?, household_members=? WHERE id=?");
        mysqli_stmt_bind_param($stmt, "ssssi", $household_number, $head_of_family, $address, $household_members, $id);
    }

    if(mysqli_stmt_execute($stmt)){
        echo "success";
    } else {
        echo "Error: " . mysqli_error($conn);
    }

    mysqli_stmt_close($stmt);
    mysqli_close($conn);
} else {
    echo "Invalid request";
}
?>
