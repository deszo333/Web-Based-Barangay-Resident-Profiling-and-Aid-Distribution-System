<?php
$conn = mysqli_connect("localhost", "root", "Password", "barangay_db");
if (!$conn) { echo "error"; exit; }

$rfid_id = $_POST['rfid_id'] ?? '';
$status  = $_POST['status'] ?? '';

if (empty($rfid_id) || empty($status)) { echo "missing"; exit; }
if (!in_array($status, ["Active", "Inactive"])) { echo "invalid_status"; exit; }

if ($status === 'Active') {
    $get_hh = mysqli_prepare($conn, "SELECT household_id FROM rfid_tags WHERE id = ?");
    mysqli_stmt_bind_param($get_hh, "i", $rfid_id);
    mysqli_stmt_execute($get_hh);
    $hh_result = mysqli_stmt_get_result($get_hh);
    $row = mysqli_fetch_assoc($hh_result);
    $household_id = $row['household_id'];
    mysqli_stmt_close($get_hh);

    $check_active = mysqli_prepare($conn, "SELECT id FROM rfid_tags WHERE household_id = ? AND status = 'Active' AND id != ?");
    mysqli_stmt_bind_param($check_active, "ii", $household_id, $rfid_id);
    mysqli_stmt_execute($check_active);
    
    if (mysqli_stmt_get_result($check_active)->num_rows > 0) {
        echo "has_active";
        mysqli_stmt_close($check_active);
        mysqli_close($conn);
        exit;
    }
    mysqli_stmt_close($check_active);
}

$stmt = $conn->prepare("UPDATE rfid_tags SET status = ? WHERE id = ?");
$stmt->bind_param("si", $status, $rfid_id);
if ($stmt->execute()) { echo "success"; } else { echo "error"; }

$stmt->close(); $conn->close();
?>
