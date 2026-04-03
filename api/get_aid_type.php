<?php
require_once __DIR__ . '/../config/db_connect.php';

if (isset($_POST['program_name'])) {
    $program_name = mysqli_real_escape_string($conn, $_POST['program_name']);

    $sql = "SELECT aid_type FROM aid_program WHERE program_name = '$program_name' LIMIT 1";
    $result = mysqli_query($conn, $sql);

    if ($row = mysqli_fetch_assoc($result)) {
        echo $row['aid_type'];
    }
}
?>