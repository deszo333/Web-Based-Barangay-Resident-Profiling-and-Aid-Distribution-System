<?php
$conn = mysqli_connect("localhost", "root", "Password", "barangay_db");

$id = $_POST['id'];

// get current status
$sql = "SELECT status FROM users WHERE id = $id";
$result = mysqli_query($conn, $sql);
$row = mysqli_fetch_assoc($result);

$newStatus = ($row['status'] === 'active') ? 'inactive' : 'active';

// update
$update = "UPDATE users SET status = '$newStatus' WHERE id = $id";
mysqli_query($conn, $update);

echo $newStatus;
?>