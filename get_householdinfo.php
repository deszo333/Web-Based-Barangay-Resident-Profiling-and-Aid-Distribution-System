<?php
session_start();
header('Content-Type: application/json');

$conn = mysqli_connect("localhost", "root", "Password", "barangay_db");
if (!$conn) {
    echo json_encode(['status' => 'error', 'message' => 'Database connection failed.']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$rfid_number = trim($data['rfid_number'] ?? '');
$program_id = $data['program_id'] ?? '';

if (empty($rfid_number)) {
    echo json_encode(['status' => 'error', 'message' => 'No RFID provided.']);
    exit;
}

// rfid lokup
$stmt = $conn->prepare("SELECT household_number, head_of_family, address, household_members FROM registered_household WHERE rfid = ?");
$stmt->bind_param("s", $rfid_number);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['status' => 'error', 'message' => 'walang tao']);
    exit;
}

$household = $result->fetch_assoc();

// check if already claimed or not
$claimed = false;
if (!empty($program_id)) {
    $check_stmt = $conn->prepare("SELECT id FROM distribution_logs WHERE program_id = ? AND household_number = ?");
    $check_stmt->bind_param("is", $program_id, $household['household_number']);
    $check_stmt->execute();
    if ($check_stmt->get_result()->num_rows > 0) {
        $claimed = true;
    }
    $check_stmt->close();
}

echo json_encode([
    'status' => 'success',
    'household' => $household,
    'claimed' => $claimed
]);

$stmt->close();
$conn->close();
?>