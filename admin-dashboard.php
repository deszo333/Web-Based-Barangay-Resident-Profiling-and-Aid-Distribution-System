<?php
session_start();

// Connect to DB
$conn = mysqli_connect("localhost", "root", "Password", "barangay_db");
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Get total residents
$result = mysqli_query($conn, "SELECT COUNT(*) AS total FROM registered_resi");
$row = mysqli_fetch_assoc($result);
$total_residents = $row['total'];

// Close connection
mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="assets/css/admin-dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body>

<!-- NAVBAR -->
<nav class="navbar">
    <div class="nav-left">
        <img src="assets/images/logo.png" alt="Barangay Logo">
        <div class="nav-text">
            <span>Barangay Abangan Norte</span>
            <p>Household Data Management System</p>
        </div>
    </div>

    <div class="nav-right">
        <img src="assets/images/profile.png" alt="User">
        <span>Welcome, Admin</span>
        <button class="logout" id="logoutBtn">
        <i class="fa-solid fa-right-from-bracket"></i>
        Logout
    </button>
    </div>
</nav>

<!-- MAIN CONTENT -->
<main class="dashboard">

    <!-- TOP STATS -->
    <section class="stats">
        <div class="stat-card">
            <h3>Total Residents</h3>
            <p><?php echo number_format($total_residents); ?></p>
        </div>
        <div class="stat-card">
            <h3>Total Households</h3>
            <p>312</p>
        </div>
        <div class="stat-card">
            <h3>Active Programs</h3>
            <p>6</p>
        </div>
        <div class="stat-card">
            <h3>Recent Transactions</h3>
            <p>48</p>
        </div>
    </section>

    <!-- ACTION CARDS -->
    <section class="actions">
    <a href="resident-profiling.php" class="action-card">
        Resident Profiling
        <p>Manage resident and household information</p>
    </a>

    <a href="household-management.php" class="action-card">
        Household Management
        <p>Group Residents into households</p>
    </a>

    <a href="aid-program-setup.html" class="action-card">
        Aid Program Setup
        <p>Manage aid distribution programs</p>
    </a>

    <a href="rfid-tag-insurance.html" class="action-card">
        RFID Tag Insurance
        <p>Assign RFID tags to households</p>
    </a>

    <a href="distribution-page.html" class="action-card">
        Distribution Page
        <p>RFID scanning during aid events</p>
    </a>

    <a href="transactions-page.html" class="action-card">
        Transactions Page
        <p>View transaction history</p>
    </a>

    <a href="reports-logs.html" class="action-card">
        Reports & Logs
        <p>Generate distribution reports</p>
    </a>

    <a href="admin-settings.html" class="action-card">
        Admin Settings
        <p>System configuration</p>
    </a>

    <a href="signup.php" class="action-card">
        Create Staff Account
        <p>Create new staff accounts</p>
    </a>
</section>

</main>

<div id="logout-toast" class="toast">
    <p>Are you sure you want to logout?</p>
    <div class="toast-buttons">
        <button id="confirm-logout">Yes</button>
        <button id="cancel-logout">No</button>
    </div>
</div>

<script>
const logoutBtn = document.getElementById('logoutBtn');
const toast = document.getElementById('logout-toast');
const confirmBtn = document.getElementById('confirm-logout');
const cancelBtn = document.getElementById('cancel-logout');

logoutBtn.addEventListener('click', () => {
    toast.style.display = 'block'; // show toast
});

confirmBtn.addEventListener('click', () => {
    window.location.href = "login.php"; // redirect
});

cancelBtn.addEventListener('click', () => {
    toast.style.display = 'none'; // hide toast
});
</script>

</body>
</html>
