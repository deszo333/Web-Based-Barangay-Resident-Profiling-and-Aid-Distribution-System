<?php
require_once __DIR__ . '/../config/auth_check.php';
require_once __DIR__ . '/../config/db_connect.php';
header('Content-Type: application/json');

// Enable exceptions to easily catch the duplicate database insert error!
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$data = json_decode(file_get_contents('php://input'), true);
$rfid_number = trim($data['rfid_number'] ?? '');
$program_id = (int)($data['program_id'] ?? 0);

if (empty($rfid_number) || $program_id === 0) {
    echo json_encode(['status' => 'error', 'message' => 'Missing RFID or Program ID.']);
    exit;
}

try {
    // ==========================================
    // 1. VALIDATE PROGRAM STATE (Must be Ongoing)
    // ==========================================
    $prog_stmt = $conn->prepare("
        SELECT status, beneficiaries, 
               (SELECT COUNT(id) FROM distribution_logs WHERE program_id = ?) as claimed_count 
        FROM aid_program WHERE id = ?
    ");
    $prog_stmt->bind_param("ii", $program_id, $program_id);
    $prog_stmt->execute();
    $prog_result = $prog_stmt->get_result();

    if ($prog_result->num_rows === 0) {
        echo json_encode(['status' => 'error', 'message' => 'Program not found.']);
        exit;
    }
    $prog_data = $prog_result->fetch_assoc();
    $prog_stmt->close();

    // The Ultimate Lock
    if ($prog_data['status'] !== 'Ongoing') {
        echo json_encode(['status' => 'error', 'message' => 'Event is currently ' . $prog_data['status'] . '. Scanner is locked.']);
        exit;
    }

    if ($prog_data['claimed_count'] >= $prog_data['beneficiaries']) {
        echo json_encode(['status' => 'error', 'message' => 'Program capacity reached.']);
        exit;
    }

    // ==========================================
    // 2. VALIDATE RFID TAG
    // ==========================================
    $stmt = $conn->prepare("
        SELECT 
            h.id AS household_id, 
            rt.id AS rfid_tag_id, 
            rt.status AS tag_status,
            h.household_number, 
            CONCAT(head.first_name, ' ', head.last_name) AS head_of_family 
        FROM rfid_tags rt
        JOIN registered_household h ON rt.household_id = h.id
        LEFT JOIN registered_resi head ON h.head_of_family_id = head.id
        WHERE rt.rfid_number = ?
    ");
    $stmt->bind_param("s", $rfid_number);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        echo json_encode(['status' => 'error', 'message' => 'Unrecognized RFID Card.']);
        exit;
    }

    $household = $result->fetch_assoc();
    $stmt->close();

    // Specific Error Messaging
    if ($household['tag_status'] === 'Disabled') {
        echo json_encode(['status' => 'error', 'message' => 'This RFID card has been Disabled.']);
        exit;
    } elseif ($household['tag_status'] === 'Lost') {
        echo json_encode(['status' => 'error', 'message' => 'This RFID card is marked as LOST. Confiscate it.']);
        exit;
    } elseif ($household['tag_status'] !== 'Active') {
         echo json_encode(['status' => 'error', 'message' => 'This RFID card is not active.']);
         exit;
    }

    $household_id = $household['household_id'];
    $rfid_tag_id = $household['rfid_tag_id'];
    $household_no = $household['household_number'];

    // ==========================================
    // 3. ATTEMPT INSERT (Relying on DB Armor)
    // ==========================================
    // We don't check if they claimed anymore. We just try to insert. 
    // If they already claimed, Phase 1's Unique Constraint will trigger an error!
    $insert_stmt = $conn->prepare("INSERT INTO distribution_logs (program_id, household_id, rfid_tag_id, rfid_snapshot) VALUES (?, ?, ?, ?)");
    $insert_stmt->bind_param("iiis", $program_id, $household_id, $rfid_tag_id, $rfid_number);
    $insert_stmt->execute();
    $insert_stmt->close();

    // If it gets here, the insert was a success!
    echo json_encode([
        'status' => 'success', 
        'message' => 'Aid distributed successfully!',
        'household_number' => $household_no,
        'head_of_family' => $household['head_of_family']
    ]);

} catch (mysqli_sql_exception $e) {
    // 1062 is the exact MySQL error code for a "Duplicate Entry"
    if ($e->getCode() == 1062) {
        echo json_encode(['status' => 'error', 'message' => 'Aid already claimed by this household!']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
    }
}

$conn->close();
?>