<?php
require_once __DIR__ . '/../config/auth_check.php';
require_once __DIR__ . '/../config/db_connect.php';

$backLink = "../public/login.php"; // default fallback if no login session
if (isset($_SESSION['role'])) {
    if ($_SESSION['role'] === 'admin') {
        $backLink = "../pages/admin-dashboard.php";
    } elseif ($_SESSION['role'] === 'staff') {
        $backLink = "../pages/staff-dashboard.php";
    }
}

// get the program id from the url
$program_id = isset($_GET['program_id']) ? (int)$_GET['program_id'] : 0;
$program_name = "Unknown Program";

// get the program name from database
if ($program_id > 0) {
    $stmt = $conn->prepare("SELECT program_name FROM aid_program WHERE id = ?");
    $stmt->bind_param("i", $program_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $program_name = htmlspecialchars($row['program_name']);
    }
    $stmt->close();
}
?>





<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Start Distribution</title>

    <link rel="stylesheet" href="../assets/css/start-distribution.css">
    <link rel="stylesheet" href="../includes/sidebars.css">
    <link rel="stylesheet" href="../fontawesome/fontawesome/css/all.css">
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
        <img src="../assets/images/logos.png" alt="Barangay Logo">
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
            <h2 id="programName"><?php echo $program_name; ?></h2>
            <p>Distribution in Progress</p>
        </div>

        <div class="program-right">
            <a href="distribution-page.php" class="change-program-btn" style="text-decoration:none; display:inline-block;">
                <i class="fa-solid fa-repeat"></i> Change Program
            </a>
        </div>
    </div>

    <!-- DISTRIBUTION CONTENT -->
    <div class="distribution-container">

        <!-- LEFT SIDE -->
        <div class="left-section">

            <!-- SCAN CARD -->
            <div class="scan-card">
    <div class="scan-icon-wrapper pulse" id="scan_trigger">
    <i class="fa-solid fa-id-card scan-icon"></i>
</div>
    <h3 id="scan_status">Click to Start Distribution</h3>
     <!-- temp textbox to put the scanned rfid uid -->
    <input type="hidden" id="hidden_rfid_input" name="scanned_rfid">
</div>

            <!-- MANUAL ENTRY -->
            <div class="manual-entry-card">
                <h4>Manual RFID Entry</h4>
                <div class="manual-input-group">
                    <input type="text" id="manual_rfid_input" placeholder="Enter RFID number">
                    <button class="process-btn" id="manual_process_btn">Process</button>
                </div>
            </div>

        </div>

        <!-- RIGHT SIDE -->
        <div class="right-section">
            
            <div class="progress-card">
                <h3>Distribution Progress</h3>
                <div class="progress-stats">
                    <span id="progClaimed">0</span> / <span id="progTarget">0</span> Distributed
                </div>
                <div class="progress-bar-container">
                    <div class="progress-bar-fill" id="progressBarFill"></div>
                </div>
            </div>

            <div class="lists-container">
                <div class="transaction-card single-view">
                <div class="card-header-flex">
                    <h3 style="color: #ffffff; margin-bottom: 0;">
                        <i style="color:#14AE5C;"></i> Recent Claims
                    </h3>
                    <a href="reports-logs.php?program_id=<?php echo $program_id; ?>" class="view-report-btn">
                        <i class="fa-solid fa-file-lines"></i> View Report
                    </a>
                </div>
                <ul class="transaction-list claimed-list" id="recentTransactionsList"></ul>
            </div>

        </div>

        

</main>

<!-- Custom Popup -->
<link rel="stylesheet" href="../assets/popup/popup.css">
<div id="popup-container"></div>



<script>
fetch("../assets/popup/popup.html")
    .then(res => res.text())
    .then(html => {
        document.getElementById("popup-container").innerHTML = html;
    });
</script>



<script src="../assets/js/rfid_scanner.js"> //load the fuckening js before the script</script>



<div class="modal-overlay" id="confirmOverlay"></div>
<div class="confirm-modal" id="confirmModal">
    <div class="modal-header">
        <h3>Household Details</h3>
        <span class="close-btn" id="closeConfirm">&times;</span>
    </div>
    <div class="modal-body">
        <p><strong>Household No:</strong> <span id="modHhNo"></span></p>
        <p><strong>Head of Family:</strong> <span id="modHead"></span></p>
        <p><strong>Address:</strong> <span id="modAddress"></span></p>
        <p><strong>Members:</strong> <span id="modMembers"></span></p>
        
        <div id="claimedWarning" style="display:none; color:#d43c3c; margin-top:15px; font-weight:bold; background:#fee2e2; padding:10px; border-radius:6px; text-align:center;">
            <i class="fa-solid fa-triangle-exclamation"></i> Aid already claimed by this household!
        </div>
    </div>
    <div class="modal-footer">
        <button id="cancelDistBtn" class="cancel-btn">Cancel</button>
        <button id="confirmDistBtn" class="confirm-btn">Confirm & Log Aid</button>
    </div>
</div>


<script>
document.addEventListener("DOMContentLoaded", () => {
    const scanBtn = document.getElementById("scan_trigger");
    const rfidInput = document.getElementById("hidden_rfid_input");
    // modal elemnts
    const overlay = document.getElementById("confirmOverlay");
    const modal = document.getElementById("confirmModal");
    const closeBtn = document.getElementById("closeConfirm");
    const cancelBtn = document.getElementById("cancelDistBtn");
    const confirmBtn = document.getElementById("confirmDistBtn");
    let pendingRFID = "";
    const currentProgramId = <?php echo $program_id; ?>;
    const scanStatus = document.getElementById("scan_status");

    // load immed the recent transactions
    function loadRecentTransactions() {
        if (currentProgramId === 0) return;
        
        fetch(`../api/fetch_recent_transactions.php?program_id=${currentProgramId}`)
        .then(res => res.json())
        .then(data => {
            if (data.status === "success") {
                // 1. Update Progress Bar
                document.getElementById("progTarget").textContent = data.target;
                document.getElementById("progClaimed").textContent = data.claimed;
                
                let percent = 0;
                if (data.target > 0) {
                    percent = Math.min((data.claimed / data.target) * 100, 100);
                }
                document.getElementById("progressBarFill").style.width = percent + "%";
                

                // 3. Render Claimed List
                const claimedUl = document.getElementById("recentTransactionsList");
                claimedUl.innerHTML = "";
                if (data.recent.length > 0) {
                    data.recent.forEach(txn => {
                        claimedUl.innerHTML += `<li><strong>${txn.household_number}</strong> - ${txn.head_of_family} <br> <span style="font-size: 11px; color: #f7f7f7;">${txn.formatted_date}</span></li>`;
                    });
                } else {
                    claimedUl.innerHTML = `<li style='text-align:center; background:transparent; border:none; color:#9cb1c4;'>No claims yet.</li>`;
                }

            }
        })
        .catch(err => console.error("Error loading stats:", err));
    }

    loadRecentTransactions();
    
    // === NEW: MANUAL RFID ENTRY LOGIC ===
    const manualInput = document.getElementById("manual_rfid_input");
    const manualProcessBtn = document.getElementById("manual_process_btn");

    if (manualProcessBtn && manualInput) {
        // 1. Click Listener for the Process Button
        manualProcessBtn.addEventListener("click", () => {
            const manualRfid = manualInput.value.trim().toUpperCase(); 
            
            if (manualRfid === "") {
                Popup.open({ 
                    title: "Input Required", 
                    message: "Please enter an RFID number.", 
                    type: "warning" 
                });
                return;
            }

            // Feed the typed ID into your existing scanner logic!
            assignRFIDToInput(manualRfid);
            
            // Clear the input box after processing
            manualInput.value = ""; 
        });

        // 2. Keyboard Listener (Press "Enter" to submit)
        manualInput.addEventListener("keypress", (e) => {
            if (e.key === "Enter") {
                e.preventDefault();
                manualProcessBtn.click();
            }
        });
    }
    // ====================================
    
    if (currentProgramId === 0) {
        Popup.open({
            title: "Configuration Error", 
            message: "No aid program was selected. Please go back and select a program to begin distribution.", 
            type: "danger"
        });
        if(scanBtn) scanBtn.style.pointerEvents = "none";
    }


    if (scanBtn) {
    scanBtn.addEventListener("click", () => {

        // if already connected do nothing
        if (typeof rfidPort !== "undefined" && rfidPort) {
            console.log("RFID already connected");
            return;
        }

        console.log("Connecting RFID scanner...");

        // connect scanner
        connectRFIDScanner(assignRFIDToInput, scanBtn);

        // update UI
        scanStatus.textContent = "Waiting for Scan...";
        scanBtn.classList.remove("pulse");
    });
}

    // uid is console logged
    function assignRFIDToInput(scannedID) {
        console.log("Distributing to household rfid: ", scannedID);
        
        // uid to input box (invisible)
        if (rfidInput) {
            rfidInput.value = scannedID;
        }

        fetch("../api/get_householdinfo.php", 
        {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ rfid_number: scannedID, program_id: currentProgramId })
        })
        .then(res => res.json())
        .then(data => 
        {
            if (data.status === "success") 
                {
                showModal(data.household, data.claimed);
                } else 
            {
                Popup.open({ 
                    title: "Scan Rejected", 
                    message: data.message, 
                    type: "danger" 
                });
                rfidInput.value = "";
            }
        })
        .catch(err => console.error("Error fetching household:", err));
    }

    // show the confirmation modal
    function showModal(household, claimed) {
        document.getElementById("modHhNo").textContent = household.household_number;
        document.getElementById("modHead").textContent = household.head_of_family;
        document.getElementById("modAddress").textContent = household.address;
        document.getElementById("modMembers").textContent = household.household_members;

        const warningMsg = document.getElementById("claimedWarning");
        
        if (claimed) {
            warningMsg.style.display = "block";
            confirmBtn.disabled = true; // block logging if already claimed
        } else {
            warningMsg.style.display = "none";
            confirmBtn.disabled = false;
        }

        overlay.classList.add("show");
        modal.classList.add("show");
    }

    // confirm button listener added after load
    if (confirmBtn) {
        confirmBtn.addEventListener("click", () => {
            confirmBtn.disabled = true;
            confirmBtn.textContent = "Processing...";

            // hidden to visible
            const finalRfid = rfidInput.value;

            fetch("../api/process_scan.php", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({ rfid_number: finalRfid, program_id: currentProgramId })
            })
            .then(res => res.json())


            //create card after confirming
            .then(data => {
                if (data.status === "success") {
                    Popup.open({ 
                        title: "Aid Claimed!", 
                        message: `Aid successfully recorded for <b>${data.head_of_family}</b>.`, 
                        type: "success" 
                    });
                    closeModal();
                    loadRecentTransactions();
                    
                    const ul = document.getElementById("recentTransactionsList");
                    const noTransMsg = document.getElementById("no-transactions");
                    if (noTransMsg) ul.innerHTML = "";
                    
                    const now = new Date();
                    const timeString = now.toLocaleDateString('en-US', { 
                        month: 'short', day: 'numeric', year: 'numeric', 
                        hour: 'numeric', minute: '2-digit', hour12: true 
                    });
                    
                    const newLi = `<li><strong>${data.head_of_family}</strong> <br> <span style="font-size: 12px; color: #9cb1c4;">${timeString}</span></li>`;
                    ul.innerHTML = newLi + ul.innerHTML;
                    rfidInput.value = "";
                } else {
                    Popup.open({ 
                        title: "Transaction Failed", 
                        message: data.message, 
                        type: "danger" 
                    });
                    confirmBtn.disabled = false;
                    confirmBtn.textContent = "Confirm & Log Aid";
                }
            })
            .catch(err => {
                console.error("Processing error:", err);
                confirmBtn.disabled = false;
                confirmBtn.textContent = "Confirm & Log Aid";
            });
        });
    }

    // modal Close 
    function closeModal() {
        if (overlay) overlay.classList.remove("show");
        if (modal) modal.classList.remove("show");
        if (confirmBtn) confirmBtn.textContent = "Confirm & Log Aid";
    }

    if (closeBtn) closeBtn.addEventListener("click", closeModal);
    if (cancelBtn) cancelBtn.addEventListener("click", closeModal);
    if (overlay) overlay.addEventListener("click", closeModal);
});
</script>

<script src="../assets/popup/popup.js" defer></script>

<script src="../assets/js/start-distribution.js"></script>
<script src="../includes/sidebarss.js?v=2" defer></script><?php include '../includes/sidebar.php'; ?>

</body>
</html>
