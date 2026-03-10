<?php
session_start();

$backLink = "login.php"; // default fallback
if (isset($_SESSION['role'])) {
    if ($_SESSION['role'] === 'admin') {
        $backLink = "admin-dashboard.php";
    } elseif ($_SESSION['role'] === 'staff') {
        $backLink = "staff-dashboard.php";
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Start Distribution</title>

    <link rel="stylesheet" href="assets/css/start-distribution.css">
    <link rel="stylesheet" href="includes/sidebars.css">
    <link rel="stylesheet" href="fontawesome/fontawesome/css/all.css">
</head>
<body>

<!-- NAVBAR -->
<nav class="rp-navbar">
    <!-- Sidebar Toggle -->
    <button class="toggle-sidebar" id="toggleBtn">
        <i class="fa-solid fa-bars" id="toggleIcon"></i>
    </button>

    <!-- Back Button -->
    <a href="<?php echo $backLink; ?>" class="back-btn">
        <i class="fa-solid fa-arrow-left"></i>
    </a>

    <!-- Navbar Content -->
    <div class="rp-navbar-content">
        <img src="assets/images/logos.png" alt="Barangay Logo">
        <div class="nav-text">
            <span class="page-title">Barangay Abangan Norte</span>
            <p>Household Data Management System</p>
        </div>
    </div>
</nav>

<!-- MAIN CONTENT -->
<main class="rp-dashboard">

    <!-- PROGRAM HEADER CARD -->
    <div class="program-card">
        <div class="program-left">
            <h2 id="programName">Rice Assistance Program</h2>
            <p>Distribution in Progress</p>
        </div>

        <div class="program-right">
            <button class="change-program-btn">
                <i class="fa-solid fa-repeat"></i> Change Program
            </button>
        </div>
    </div>

    <!-- DISTRIBUTION CONTENT -->
    <div class="distribution-container">

        <!-- LEFT SIDE -->
        <div class="left-section">

            <!-- SCAN CARD -->
            <div class="scan-card">
    <div class="scan-icon-wrapper" id="scan_trigger">
        <i class="fa-solid fa-id-card scan-icon"></i>
    </div>
    <h3 id="scan_status">Waiting for Scan</h3>
     <!-- temp textbox to put the scanned rfid uid -->
    <input type="hidden" id="hidden_rfid_input" name="scanned_rfid">
</div>

            <!-- MANUAL ENTRY -->
            <div class="manual-entry-card">
                <h4>Manual RFID Entry</h4>
                <div class="manual-input-group">
                    <input type="text" placeholder="Enter RFID number">
                    <button class="process-btn">Process</button>
                </div>
            </div>

        </div>

        <!-- RIGHT SIDE -->
        <div class="right-section">
            <div class="transaction-card">
                <h3>Recent Transaction</h3>

                <ul class="transaction-list">
                    <li>RFID: 123456789 - Juan Dela Cruz</li>
                    <li>RFID: 987654321 - Maria Santos</li>
                </ul>

            </div>
        </div>

    </div>

</main>

<!-- Custom Popup -->
<link rel="stylesheet" href="assets/popup/popup.css">
<div id="popup-container"></div>



<script>
fetch("assets/popup/popup.html")
    .then(res => res.text())
    .then(html => {
        document.getElementById("popup-container").innerHTML = html;
    });
</script>



<script src="rfid/rfid_scanner.js"> //load the fuckening js before the script</script>


<script>
document.addEventListener("DOMContentLoaded", () => {
    const scanBtn = document.getElementById("scan_trigger");
    const rfidInput = document.getElementById("hidden_rfid_input");


    if (scanBtn) {
        scanBtn.addEventListener("click", () => {


            if (typeof rfidPort !== "undefined" && rfidPort) 
            {
                console.log("already connected blocked the click")
                return;
            }
            
            
            console.log("Distributing to household rfid: ");
            connectRFIDScanner(assignRFIDToInput, scanBtn);

        });

        
    }

    // uid is console logged
    function assignRFIDToInput(scannedID) {
        console.log("Distributing to household rfid: ", scannedID);
        
        // uid to input box (invisible)
        if (rfidInput) {
            rfidInput.value = scannedID;
        }
        
        
    }
        
    });
</script>

<script src="assets/popup/popup.js" defer></script>

<script src="assets/js/start-distribution.js"></script>
<script src="includes/sidebarss.js" defer></script><?php include 'includes/sidebar.php'; ?>

</body>
</html>
