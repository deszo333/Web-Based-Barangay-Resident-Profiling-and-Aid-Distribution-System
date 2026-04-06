<?php
require_once __DIR__ . '/../config/auth_check.php';
require_once __DIR__ . '/../config/db_connect.php';

header('Content-Type: application/json');

// Only allow Admins to change the state of an entire event
if ($_SESSION['role'] !== 'admin') {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized. Only admins can start or stop an event.']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$program_id = isset($data['program_id']) ? (int)$data['program_id'] : 0;
$new_status = isset($data['new_status']) ? trim($data['new_status']) : '';

$valid_statuses = ['Scheduled', 'Ongoing', 'Paused', 'Completed', 'Archived'];

if ($program_id === 0 || !in_array($new_status, $valid_statuses)) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid Request. Missing Program ID or Invalid Status.']);
    exit;
}

// Update the master switch in the database
$stmt = $conn->prepare("UPDATE aid_program SET status = ? WHERE id = ?");
$stmt->bind_param("si", $new_status, $program_id);

if ($stmt->execute()) {
    // Log the audit
    log_audit($conn, (int)$_SESSION['user_id'], 'UPDATE', 'aid_program', "Changed program status to $new_status (Program ID: $program_id)");
    echo json_encode(['status' => 'success', 'new_status' => $new_status]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Database update failed.']);
}

$stmt->close();
$conn->close();
?>
