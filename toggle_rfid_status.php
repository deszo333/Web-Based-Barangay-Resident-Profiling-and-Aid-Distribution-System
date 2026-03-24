<?php
$conn = mysqli_connect("localhost", "root", "Password", "barangay_db");
if (!$conn) { echo "error"; exit; }

$rfid_id = $_POST['rfid_id'] ?? '';
$status  = $_POST['status'] ?? '';

if (empty($rfid_id) || empty($status)) { echo "missing"; exit; }
if (!in_array($status, ["Active", "Disabled"])) { echo "invalid_status"; exit; }

$stmt = $conn->prepare("UPDATE rfid_tags SET status = ? WHERE id = ?");
$stmt->bind_param("si", $status, $rfid_id);
if ($stmt->execute()) { echo "success"; } else { echo "error"; }

$stmt->close(); $conn->close();
?>
