<?php
$conn = mysqli_connect("localhost", "root", "Password", "barangay_db");
if (!$conn) {
    die("error");
}

$rfid_id          = $_POST['rfid_id'] ?? '';
$rfid_number      = $_POST['rfid_number'] ?? '';
$household_number = $_POST['household_number'] ?? '';
$head_of_family   = $_POST['head_of_family'] ?? '';

if (empty($rfid_number) || empty($head_of_family)) {
    echo "missing";
    exit;
}

/* ===============================
   CHECK DUPLICATE RFID
   (ignore current record when editing)
================================ */
if ($rfid_id !== '') {
    $check = $conn->prepare(
        "SELECT 1 FROM rfid_tags WHERE rfid_number = ? AND rfid_id != ?"
    );
    $check->bind_param("si", $rfid_number, $rfid_id);
} else {
    $check = $conn->prepare(
        "SELECT 1 FROM rfid_tags WHERE rfid_number = ?"
    );
    $check->bind_param("s", $rfid_number);
}

$check->execute();
$check->store_result();

if ($check->num_rows > 0) {
    echo "rfid_exists";
    exit;
}

/* ===============================
   INSERT or UPDATE
================================ */
if ($rfid_id === '') {
    // INSERT
    $stmt = $conn->prepare("
        INSERT INTO rfid_tags (rfid_number, household_number, head_of_family, status)
        VALUES (?, ?, ?, 'Active')
    ");
    $stmt->bind_param("sss", $rfid_number, $household_number, $head_of_family);
} else {
    // UPDATE
    $stmt = $conn->prepare("
        UPDATE rfid_tags
        SET rfid_number = ?, household_number = ?, head_of_family = ?
        WHERE rfid_id = ?
    ");
    $stmt->bind_param("sssi", $rfid_number, $household_number, $head_of_family, $rfid_id);
}

$stmt->execute();

echo "success";
