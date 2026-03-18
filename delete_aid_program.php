<?php
require 'auth_check.php';
$conn = mysqli_connect("localhost", "root", "Password", "barangay_db");
if(!$conn) { 
    error_log("Database connection failed: " . mysqli_connect_error());
    die("error");
}

if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $id = intval($_POST['id']);
    $sql = "DELETE FROM aid_program WHERE id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $id);

    if(mysqli_stmt_execute($stmt)) {
        echo "success";
    } else {
        error_log("Delete Aid Program Error: " . mysqli_error($conn));
        echo "error";
    }

    mysqli_stmt_close($stmt);
} else {
    echo "Invalid request";
}

mysqli_close($conn);
?>
