<?php
$conn = mysqli_connect("localhost", "root", "Password", "barangay_db");

$id = $_POST['id'];

$stmt = mysqli_prepare($conn, "UPDATE registered_resi SET is_archived = 1 WHERE id=?");
mysqli_stmt_bind_param($stmt, "i", $id);

echo mysqli_stmt_execute($stmt) ? "success" : "error";

mysqli_close($conn);
