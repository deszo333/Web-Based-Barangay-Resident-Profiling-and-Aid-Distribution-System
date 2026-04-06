<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// --- STRICT CACHE CONTROL ---
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// If the user does not have a session user_id or role, redirect to login
if (empty($_SESSION['user_id']) || empty($_SESSION['role'])) {
    header("Location: ../public/login.php");
    exit();
}

// Securely fetch the user's full name
require_once __DIR__ . '/db_connect.php';

$userId = (int)$_SESSION['user_id']; 
$query = "SELECT first_name, last_name, username FROM users WHERE id = $userId";
$result = mysqli_query($conn, $query);

if ($result && mysqli_num_rows($result) > 0) {
    $user = mysqli_fetch_assoc($result);
    $firstName = trim($user['first_name'] ?? '');
    $lastName  = trim($user['last_name'] ?? '');
    $username  = trim($user['username'] ?? '');

    if ($firstName && $lastName) {
        $currentName = $firstName . ' ' . $lastName;
    } elseif ($firstName) {
        $currentName = $firstName;
    } elseif ($username) {
        $currentName = $username;
    } else {
        $currentName = 'Admin';
    }
} else {
    $currentName = 'Admin';
}
?>
