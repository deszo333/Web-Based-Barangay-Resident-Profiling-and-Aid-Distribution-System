<?php
require 'auth_check.php';

// DB connection
$conn = mysqli_connect("localhost", "root", "Password", "barangay_db");
if(!$conn){
    error_log("Database connection failed: " . mysqli_connect_error());
    die("error");
}

if($_SERVER['REQUEST_METHOD'] === 'POST'){

    $id = trim($_POST['resident_id'] ?? '');
    $household_number = trim($_POST['household_number'] ?? '');
    $head_of_family = trim($_POST['head_of_family'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $household_members = trim($_POST['household_members'] ?? '');
    $rfid_raw = trim($_POST['rfid'] ?? '');
    $rfid = ($rfid_raw === '') ? null : $rfid_raw;

    if($address === '' || $household_members === ''){
        echo "Please fill all required fields";
        exit;
    }

    if($id === '') {

        // ===== AUTO-GENERATE HOUSEHOLD NUMBER =====
        $result = mysqli_query(
            $conn,
            "SELECT household_number 
             FROM registered_household 
             ORDER BY id DESC 
             LIMIT 1"
        );

        if($row = mysqli_fetch_assoc($result)){
            $lastNum = intval(substr($row['household_number'], 3));
            $newNum = $lastNum + 1;
        } else {
            $newNum = 1;
        }

        $household_number = 'HH-' . str_pad($newNum, 5, '0', STR_PAD_LEFT);
        // ========================================

        // INSERT new household
        $stmt = mysqli_prepare(
            $conn,
            "INSERT INTO registered_household 
            (household_number, head_of_family, address, household_members, rfid) 
            VALUES (?, ?, ?, ?, ?)"
        );

        mysqli_stmt_bind_param(
            $stmt,
            "sssss",
            $household_number,
            $head_of_family,
            $address,
            $household_members,
            $rfid
        );

    } else {

        // UPDATE existing household
        $stmt = mysqli_prepare(
            $conn,
            "UPDATE registered_household 
             SET head_of_family=?, address=?, household_members=?, rfid=? 
             WHERE id=?"
        );

        mysqli_stmt_bind_param(
            $stmt,
            "ssssi",
            $head_of_family,
            $address,
            $household_members,
            $rfid,
            $id
        );
    }

    if(mysqli_stmt_execute($stmt)){
        echo "success";
    } else {
        error_log("Add Household DB Error: " . mysqli_error($conn));
        echo "error"; 
    }

    mysqli_stmt_close($stmt);
    mysqli_close($conn);

} else {
    echo "Invalid request";
}
?>
