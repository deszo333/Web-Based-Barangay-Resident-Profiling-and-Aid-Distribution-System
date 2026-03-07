<?php
session_start();
header('Content-Type: application/json');

$conn = mysqli_connect("localhost", "root", "Password", "barangay_db");
if (!$conn) {
    echo json_encode(['status' => 'error', 'message' => 'Database connection failed.']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$rfid_number = $data['rfid_number'] ?? '';
$program_id = $data['program_id'] ?? '';

if (empty($rfid_number) || empty($program_id)) {
    echo json_encode(['status' => 'error', 'message' => 'Missing RFID or Program ID.']);
    exit;
}

// rfid to household lookup
$stmt = $conn->prepare("SELECT household_number, head_of_family FROM registered_household WHERE rfid = ?");
$stmt->bind_param("s", $rfid_number);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['status' => 'error', 'message' => 'Unregistered RFID Card.']);
    exit;
}

$household = $result->fetch_assoc();
$household_no = $household['household_number'];

// household claimed in program? ignore muna
$check_stmt = $conn->prepare("SELECT id FROM distribution_logs WHERE program_id = ? AND household_number = ?");
$check_stmt->bind_param("is", $program_id, $household_no);
$check_stmt->execute();
$check_result = $check_stmt->get_result();

if ($check_result->num_rows > 0) {
    echo json_encode(['status' => 'error', 'message' => 'Aid already claimed by this household!']);
    exit;
}
$check_stmt->close();

// every claim will be inserted into distribution log
$insert_stmt = $conn->prepare("INSERT INTO distribution_logs (program_id, household_number, rfid_number) VALUES (?, ?, ?)");
$insert_stmt->bind_param("iss", $program_id, $household_no, $rfid_number);

if ($insert_stmt->execute()) {
    echo json_encode([
        'status' => 'success', 
        'message' => 'Aid distributed successfully!',
        'household_number' => $household_no,
        'head_of_family' => $household['head_of_family']
    ]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Failed to log distribution.']);
}

$insert_stmt->close();
$stmt->close();
$conn->close();
?>