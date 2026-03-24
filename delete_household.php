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

    mysqli_begin_transaction($conn);

    try {
        // archive the Household/soft delete
        $stmt1 = mysqli_prepare($conn, "UPDATE registered_household SET is_archived = 1 WHERE id = ?");
        mysqli_stmt_bind_param($stmt1, "i", $id);
        mysqli_stmt_execute($stmt1);
        mysqli_stmt_close($stmt1);

        // free up the residents 
        $stmt2 = mysqli_prepare($conn, "UPDATE registered_resi SET household_id = NULL WHERE household_id = ?");
        mysqli_stmt_bind_param($stmt2, "i", $id);
        mysqli_stmt_execute($stmt2);
        mysqli_stmt_close($stmt2);

        // disable all active RFID tags linked 
        $stmt3 = mysqli_prepare($conn, "UPDATE rfid_tags SET status = 'Disabled' WHERE household_id = ?");
        mysqli_stmt_bind_param($stmt3, "i", $id);
        mysqli_stmt_execute($stmt3);
        mysqli_stmt_close($stmt3);

        // if all worked save
        mysqli_commit($conn);
        echo "success";

    } catch (Exception $e) {
        mysqli_rollback($conn);
        error_log("Household Delete Failed: " . $e->getMessage());
        echo "error";
    }

    mysqli_close($conn);
} else {
    echo "Invalid request";
}
?>