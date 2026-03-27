<?php
$conn = mysqli_connect("localhost", "root", "Password", "barangay_db");

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

if (isset($_POST['program_name'])) {
    $program_name = $_POST['program_name'];

    $sql = "SELECT aid_type FROM aid_program WHERE program_name = '$program_name' LIMIT 1";
    $result = mysqli_query($conn, $sql);

    if ($row = mysqli_fetch_assoc($result)) {
        echo $row['aid_type'];
    }
}

mysqli_close($conn);
?>