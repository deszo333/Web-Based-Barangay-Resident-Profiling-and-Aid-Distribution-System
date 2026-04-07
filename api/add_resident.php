<?php
require_once __DIR__ . '/../config/auth_check.php';
// 1. Require our new hub
require_once __DIR__ . '/../config/db_connect.php';

$first = $_POST['first_name'] ?? '';
$middle = $_POST['middle_name'] ?? '';
$last = $_POST['last_name'] ?? '';
$address = $_POST['address'] ?? '';
$birthdate = $_POST['birthdate'] ?? '';
$age = $_POST['age'] ?? '';
$gender = $_POST['gender'] ?? '';
$civil = $_POST['civil_status'] ?? '';
$occupation = $_POST['occupation'] ?? '';
$voter_status = $_POST['voter_status'] ?? 'No'; // Get voter status
$voters_registration_no = trim($_POST['voters_registration_no'] ?? '');
$contact = trim($_POST['contact'] ?? '');

// ============================================
// HANDLE VOTER REGISTRATION
// ============================================
// If voter status is "Yes" but no number provided, mark as registered without number
if ($voter_status === 'Yes' && $voters_registration_no === '') {
    $voters_registration_no = "Registered - No Number";
} elseif ($voter_status === 'No' || empty($voter_status)) {
    // If status is "No", mark as not registered
    $voters_registration_no = "Not Registered";
}

if ($contact === '') {
    $contact = "N/A";
}

if (empty($occupation)) {
    $occupation = "N/A";
}

// ============================================
// VALIDATE BIRTHDATE
// ============================================
$validation_error = null;

// Check if birthdate is provided
if (empty($birthdate)) {
    $validation_error = "Birthdate is required";
} else {
    // Validate birthdate format (should be YYYY-MM-DD)
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $birthdate)) {
        $validation_error = "Invalid birthdate format";
    } else {
        // Parse the birthdate
        $birthdate_obj = DateTime::createFromFormat('Y-m-d', $birthdate);
        
        if (!$birthdate_obj) {
            $validation_error = "Invalid date provided";
        } else {
            // Check if birthdate is not in the future
            $today = new DateTime();
            if ($birthdate_obj > $today) {
                $validation_error = "Birthdate cannot be in the future";
            } else {
                // Calculate age
                $interval = $today->diff($birthdate_obj);
                $calculated_age = $interval->y;
                
                // Check reasonable age range (0-150 years)
                if ($calculated_age > 150) {
                    $validation_error = "Age cannot exceed 150 years";
                }
            }
        }
    }
}

// If validation failed, return error
if ($validation_error) {
    echo "error|" . json_encode(['message' => $validation_error]);
    mysqli_close($conn);
    exit();
}

$stmt = mysqli_prepare($conn,
    "INSERT INTO registered_resi
    (first_name, middle_name, last_name, address, birthdate, age, gender, civil_status, occupation, voters_registration_no, contact)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
);
mysqli_stmt_bind_param(
    $stmt,
    "sssssisssss",
    $first,
    $middle,
    $last,
    $address,
    $birthdate,
    $age,
    $gender,
    $civil,
    $occupation,
    $voters_registration_no,
    $contact
);

// 2. Execute and Trigger Audit Log
if (mysqli_stmt_execute($stmt)) {
    $new_id = mysqli_insert_id($conn);
    $current_user_id = $_SESSION['user_id'] ?? null;
    $full_name = trim("$first $middle $last");
    
    // Log the creation
    $audit_data = [
        "action_summary" => "New Resident Profile Created",
        "resident_name" => $full_name,
        "resident_id" => $new_id,
        "address" => $address,
        "birthdate" => $birthdate
    ];
    log_audit($conn, $current_user_id, "Create", "Resident Profiling", json_encode($audit_data));
    
    echo "success";
} else {
    echo "error";
}

mysqli_stmt_close($stmt);
mysqli_close($conn);
?>