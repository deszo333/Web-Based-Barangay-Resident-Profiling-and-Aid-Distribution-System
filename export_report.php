<?php
session_start();
require 'auth_check.php'; 

$conn = mysqli_connect("localhost", "root", "Password", "barangay_db");
$program_name = isset($_GET['program_name']) ? $_GET['program_name'] : '';

if (empty($program_name)) die("Error: No program selected.");

// 1. Get the exact Program ID first
$stmt = mysqli_prepare($conn, "SELECT id FROM aid_program WHERE program_name = ?");
mysqli_stmt_bind_param($stmt, "s", $program_name);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
$prog_id = ($row = mysqli_fetch_assoc($res)) ? $row['id'] : 0;
mysqli_stmt_close($stmt);

if ($prog_id === 0) die("Error: Program not found.");

// Set Headers to force the browser to download a pure CSV file
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=Distribution_Report_' . preg_replace('/[^A-Za-z0-9_\-]/', '_', $program_name) . '.csv');

// Open the output stream
$output = fopen('php://output', 'w');

// Write the Column Headers
fputcsv($output, array('Household Number', 'Claim Status', 'Head of Family', 'All Household Members', 'Date Claimed', 'Program Name'));

// 2. Query ALL active households, check if they claimed, and get ALL members
$sql = "
    SELECT 
        h.household_number,
        IF(d.id IS NOT NULL, 'Claimed', 'Not Claimed') AS claim_status,
        CONCAT(head.first_name, ' ', head.last_name) AS head_of_family,
        
        -- Grabs every resident in the household and combines them with a comma
        (SELECT GROUP_CONCAT(CONCAT(r.first_name, ' ', r.last_name) SEPARATOR ', ') 
         FROM registered_resi r 
         WHERE r.household_id = h.id) AS all_members,
         
        d.date_claimed
    FROM registered_household h
    LEFT JOIN registered_resi head ON h.head_of_family_id = head.id
    LEFT JOIN distribution_logs d ON h.id = d.household_id AND d.program_id = ?
    WHERE h.is_archived = 0
    ORDER BY claim_status ASC, d.date_claimed DESC, h.household_number ASC
";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $prog_id);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);

// Loop through the data and write each row to the CSV
while ($row = mysqli_fetch_assoc($res)) {
    // Format the date or show N/A if not claimed
    $date_claimed = $row['date_claimed'] ? date("M d, Y h:i A", strtotime($row['date_claimed'])) : 'N/A';
    
    // Write the row
    fputcsv($output, array(
        $row['household_number'],
        $row['claim_status'],
        $row['head_of_family'],
        $row['all_members'],
        $date_claimed,
        $program_name
    ));
}

fclose($output);
mysqli_stmt_close($stmt);
mysqli_close($conn);
?>