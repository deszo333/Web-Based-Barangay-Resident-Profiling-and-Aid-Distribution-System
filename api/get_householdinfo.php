<?php
require_once __DIR__ . '/../config/auth_check.php';
require_once __DIR__ . '/../config/db_connect.php';
header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);
$rfid_number = trim($data['rfid_number'] ?? '');
$program_id = $data['program_id'] ?? '';

if (empty($rfid_number)) {
    echo json_encode(['status' => 'error', 'message' => 'No RFID provided.']);
    exit;
}

// rfid lookup (Normalized DB)
$stmt = $conn->prepare("
    SELECT 
        h.id AS household_id,
        h.household_number, 
        CONCAT(head.first_name, ' ', head.last_name) AS head_of_family, 
        h.address 
    FROM rfid_tags rt
    JOIN registered_household h ON rt.household_id = h.id
    LEFT JOIN registered_resi head ON h.head_of_family_id = head.id
    WHERE rt.rfid_number = ? AND rt.status = 'Active'
");
$stmt->bind_param("s", $rfid_number);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['status' => 'error', 'message' => 'Unregistered or Disabled RFID card.']);
    exit;
}

$household = $result->fetch_assoc();
$household_id = $household['household_id'];

// Get household members as a comma-separated string
$members_stmt = $conn->prepare("SELECT GROUP_CONCAT(CONCAT(first_name, ' ', last_name) SEPARATOR ', ') AS members FROM registered_resi WHERE household_id = ?");
$members_stmt->bind_param("i", $household_id);
$members_stmt->execute();
$members_result = $members_stmt->get_result()->fetch_assoc();
$household['household_members'] = $members_result['members'] ?? 'None';
$members_stmt->close();

// check if already claimed or not
$claimed = false;
if (!empty($program_id)) {
    // Check using household_id now
    $check_stmt = $conn->prepare("SELECT id FROM distribution_logs WHERE program_id = ? AND household_id = ?");
    $check_stmt->bind_param("ii", $program_id, $household_id);
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