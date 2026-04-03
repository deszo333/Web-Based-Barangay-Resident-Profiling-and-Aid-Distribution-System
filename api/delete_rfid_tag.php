<?php
require_once __DIR__ . '/../config/auth_check.php';
require_once __DIR__ . '/../config/db_connect.php';

$rfid_id = $_POST['rfid_id'] ?? '';

if (empty($rfid_id)) {
    echo "missing";
    exit;
}

$stmt = $conn->prepare("DELETE FROM rfid_tags WHERE id = ?");
$stmt->bind_param("i", $rfid_id);

if ($stmt->execute()) {
    echo "success";
} else {
    echo "error";
}

$stmt->close();
$conn->close();
?>
