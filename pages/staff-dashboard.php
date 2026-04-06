<?php
require_once __DIR__ . '/../config/auth_check.php';
require_once __DIR__ . '/../config/db_connect.php';

// Get total residents
$result = mysqli_query($conn, "SELECT COUNT(*) AS total FROM registered_resi");
$row = mysqli_fetch_assoc($result);
$total_residents = $row['total'];


$result = mysqli_query($conn, "SELECT COUNT(*) AS total FROM registered_household");
$row = mysqli_fetch_assoc($result);
$total_households = $row['total'];

$result = mysqli_query($conn, "SELECT COUNT(*) AS total FROM aid_program WHERE status = 'Active'");
$row = mysqli_fetch_assoc($result);
$total_active_programs = $row['total'];

// Close connection
mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Staff Dashboard</title>
    <link rel="stylesheet" href="../assets/css/admin-dashboard.css">
    <link rel="stylesheet" href="../includes/sidebars.css">
    <link rel="stylesheet" href="../fontawesome/fontawesome/css/all.css">
</head>
<body>

<?php include '../includes/sidebar.php'; ?>

<!-- NAVBAR -->
<nav class="navbar">
    <div class="nav-left">
        <button class="toggle-btn" id="toggleSidebar">
            <i class="fa-solid fa-bars"></i>
        </button>

        <img src="../assets/images/logos.png" alt="Barangay Logo">
        <div class="nav-text">
            <span>Barangay Abangan Norte</span>
            <p>Household Data Management System</p>
        </div>
    </div>

    <div class="nav-right" style="display: flex; align-items: center; gap: 10px;">
        <span style="font-weight: 600;">Hello, <?php echo htmlspecialchars($currentName); ?></span>
        <img src="../assets/images/profiles.png" alt="User" style="width: 40px; height: 40px; border-radius: 50%;">
    </div>
</nav>

<!-- MAIN CONTENT -->
<main class="dashboard" id="mainContent">

    <!-- TOP STATS -->
    <section class="stats">
    <div class="stat-card" style="background: linear-gradient(90deg, hsla(208, 80%, 16%, 1) 0%, hsla(208, 46%, 26%, 1) 49%, hsla(208, 64%, 32%, 1) 100%);">
        <div class="stat-icon"><i class="fas fa-users"></i></div>
        <div class="stat-info">
            <h3>Total Residents</h3>
        </div>
        <p class="stat-number"><?php echo number_format($total_residents); ?></p>
    </div>

    <div class="stat-card" style="background: linear-gradient(90deg, hsla(145, 95%, 15%, 1) 0%, hsla(144, 76%, 19%, 1) 50%, hsla(145, 72%, 32%, 1) 100%);">
        <div class="stat-icon"><i class="fas fa-home"></i></div>
        <div class="stat-info">
            <h3>Total Households</h3>
        </div>
        <p class="stat-number"><?php echo number_format($total_households); ?></p>
    </div>

    <div class="stat-card" style="background: linear-gradient(90deg, rgb(186, 29, 37) 0%, rgb(166, 41, 45) 53%, rgb(131, 31, 34) 100%);">
        <div class="stat-icon"><i class="fas fa-hand-holding-heart"></i></div>
        <div class="stat-info">
            <h3>Active Programs</h3>
        </div>
        <p class="stat-number"><?php echo number_format($total_active_programs); ?></p>
    </div>
</section>



    <!-- ACTION CARDS -->
    <section class="actions">
    <a href="resident-profiling.php" class="actions-card-1">
        <div class="card-content">
            <span class="card-icon"><i class="fas fa-users"></i></span>
            <div class="card-text">
                <span>Resident Profiling</span>
                <p>Manage resident and household information</p>
            </div>
        </div>
    </a>

    <a href="household-management.php" class="actions-card-2">
        <div class="card-content">
            <span class="card-icon"><i class="fas fa-home"></i></span>
            <div class="card-text">
                <span>Household Management</span>
                <p>Group Residents into households</p>
            </div>
        </div>
    </a>

    <a href="distribution-page.php" class="actions-card-3">
        <div class="card-content">
            <span class="card-icon"><i class="fas fa-hand-holding-heart"></i></span>
            <div class="card-text">
                <span>Distribution Page</span>
                <p>Start and manage aid distribution events</p>
            </div>
        </div>
    </a>

    <a href="rfid-tags-insurance.php" class="actions-card-4">
        <div class="card-content">
            <span class="card-icon"><i class="fas fa-id-card"></i></span>
            <div class="card-text">
                <span>RFID Tag Insurance</span>
                <p>Assign RFID tags to households</p>
            </div>
        </div>
    </a>


    <a href="reports-logs.php" class="actions-card-5">
        <div class="card-content">
            <span class="card-icon"><i class="fas fa-file-alt"></i></span>
            <div class="card-text">
                <span>Reports & Logs</span>
                <p>Generate distribution reports</p>
            </div>
        </div>
    </a>

    <a href="reports-logs.php" class="actions-card-6" style="display:none;">
        <div class="card-content">
            <span class="card-icon"><i class="fas fa-file-alt"></i></span>
            <div class="card-text">
                <span>Reports & Logs</span>
                <p>Generate distribution reports</p>
            </div>
        </div>
    </a>

</section>

</main>

<link rel="stylesheet" href="../assets/popup/popup.css">
<div id="popup-container"></div>

<script>
fetch("../assets/popup/popup.html")
    .then(res => res.text())
    .then(html => {
        document.getElementById("popup-container").innerHTML = html;
        const script = document.createElement("script");
        script.src = "../assets/popup/popup.js";
        document.body.appendChild(script);
    });
</script>

<script src="../includes/sidebarss.js" defer></script>



</body>
</html>
