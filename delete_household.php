<?php
if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $id = $_POST['id'] ?? '';

    if($id === ''){
        echo "Invalid ID";
        exit;
    }

    $conn = mysqli_connect("localhost", "root", "Password", "barangay_db");
    if(!$conn){
        echo "Database connection failed";
        exit;
    }

    $stmt = mysqli_prepare($conn, "DELETE FROM registered_household WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $id);

    if(mysqli_stmt_execute($stmt)){
        echo "success";
    } else {
        echo "Failed to delete";
    }

    mysqli_stmt_close($stmt);
    mysqli_close($conn);
} else {
    echo "Invalid request";
}
?>
