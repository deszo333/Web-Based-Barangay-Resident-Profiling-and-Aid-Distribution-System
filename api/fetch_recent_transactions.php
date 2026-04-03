<?php
require_once __DIR__ . '/../config/auth_check.php';
require_once __DIR__ . '/../config/db_connect.php';
header('Content-Type: application/json');

$program_id = isset($_GET['program_id']) ? (int)$_GET['program_id'] : 0;
if ($program_id === 0) { 
    echo json_encode(['status' => 'error']); 
    exit; 
}

// 1. Get Target Beneficiaries
$target = 0;
$stmt = $conn->prepare("SELECT beneficiaries FROM aid_program WHERE id = ?");
$stmt->bind_param("i", $program_id);
$stmt->execute();
$res = $stmt->get_result();
if($row = $res->fetch_assoc()) $target = $row['beneficiaries'];
$stmt->close();

// 2. Get Total Claimed Count
$claimed = 0;
$stmt = $conn->prepare("SELECT COUNT(id) AS total FROM distribution_logs WHERE program_id = ?");
$stmt->bind_param("i", $program_id);
$stmt->execute();
$res = $stmt->get_result();
if($row = $res->fetch_assoc()) $claimed = $row['total'];
$stmt->close();

// 3. Get Recent Claims List
$recent = [];
$stmt = $conn->prepare("
    SELECT d.date_claimed, h.household_number, CONCAT(head.first_name, ' ', head.last_name) AS head_of_family 
    FROM distribution_logs d
    JOIN registered_household h ON d.household_id = h.id
    LEFT JOIN registered_resi head ON h.head_of_family_id = head.id
    WHERE d.program_id = ? ORDER BY d.date_claimed DESC LIMIT 15
");
$stmt->bind_param("i", $program_id);
$stmt->execute();
$res = $stmt->get_result();
while($row = $res->fetch_assoc()){
    $row['formatted_date'] = date('M d, h:i A', strtotime($row['date_claimed']));
    $recent[] = $row;
}
$stmt->close();


echo json_encode([
    'status' => 'success',
    'target' => $target,
    'claimed' => $claimed,
    'recent' => $recent
]);
$conn->close();
?>