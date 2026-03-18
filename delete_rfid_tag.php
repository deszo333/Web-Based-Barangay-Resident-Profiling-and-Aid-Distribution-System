<?php
$conn = mysqli_connect("localhost", "root", "Password", "barangay_db");
if (!$conn) {
    echo "error";
    exit;
}

$rfid_id = $_POST['rfid_id'] ?? '';

if (empty($rfid_id)) {
    echo "missing";
    exit;
}

$stmt = $conn->prepare("DELETE FROM rfid_tags WHERE rfid_id = ?");
$stmt->bind_param("i", $rfid_id);

if ($stmt->execute()) {
    echo "success";
} else {
    echo "error";
}

$stmt->close();
$conn->close();
?>
