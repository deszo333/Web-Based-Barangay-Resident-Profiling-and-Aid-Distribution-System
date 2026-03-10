<?php
session_start();
header('Content-Type: application/json');

$conn = mysqli_connect("localhost", "root", "Password", "barangay_db");
if (!$conn) {
    echo json_encode(['status' => 'error', 'message' => 'Database connection failed.']);
    exit;
}

$program_id = isset($_GET['program_id']) ? (int)$_GET['program_id'] : 0;
$transactions = [];

if ($program_id > 0) {
    $stmt = $conn->prepare("
        SELECT d.date_claimed, r.head_of_family 
        FROM distribution_logs d
        JOIN registered_household r ON d.household_number = r.household_number
        WHERE d.program_id = ?
        ORDER BY d.date_claimed DESC
        LIMIT 5
    ");
    $stmt->bind_param("i", $program_id);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $row['formatted_date'] = date('M d, Y, h:i A', strtotime($row['date_claimed']));
        $transactions[] = $row;
    }
    $stmt->close();
}

echo json_encode(['status' => 'success', 'data' => $transactions]);
$conn->close();
?>