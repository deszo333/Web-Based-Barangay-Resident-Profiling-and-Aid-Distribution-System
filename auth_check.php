<?php
// Check if a session is already started, if not, start it
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// If the user does not have a session user_id or role, redirect to login
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
    header("Location: login.php");
    exit();
}
?>
