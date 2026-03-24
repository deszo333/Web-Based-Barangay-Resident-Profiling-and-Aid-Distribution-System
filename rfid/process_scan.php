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

// rfid to household lookup (Normalized DB)
$stmt = $conn->prepare("
    SELECT 
        h.id AS household_id, 
        rt.id AS rfid_tag_id, 
        h.household_number, 
        CONCAT(head.first_name, ' ', head.last_name) AS head_of_family 
    FROM rfid_tags rt
    JOIN registered_household h ON rt.household_id = h.id
    LEFT JOIN registered_resi head ON h.head_of_family_id = head.id
    WHERE rt.rfid_number = ? AND rt.status = 'Active'
");
$stmt->bind_param("s", $rfid_number);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['status' => 'error', 'message' => 'Unregistered or Disabled RFID Card.']);
    exit;
}

$household = $result->fetch_assoc();
$household_id = $household['household_id'];
$rfid_tag_id = $household['rfid_tag_id'];
$household_no = $household['household_number'];

// household claimed in program?
$check_stmt = $conn->prepare("SELECT id FROM distribution_logs WHERE program_id = ? AND household_id = ?");
$check_stmt->bind_param("ii", $program_id, $household_id);
$check_stmt->execute();
$check_result = $check_stmt->get_result();

if ($check_result->num_rows > 0) {
    echo json_encode(['status' => 'error', 'message' => 'Aid already claimed by this household!']);
    exit;
}
$check_stmt->close();

// every claim will be inserted into distribution log
// Schema requires program_id (int), household_id (int), rfid_tag_id (int), rfid_snapshot (varchar)
$insert_stmt = $conn->prepare("INSERT INTO distribution_logs (program_id, household_id, rfid_tag_id, rfid_snapshot) VALUES (?, ?, ?, ?)");
$insert_stmt->bind_param("iiis", $program_id, $household_id, $rfid_tag_id, $rfid_number);

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