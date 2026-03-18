<?php
require 'auth_check.php';

$backLink = "login.php"; // default fallback
if (isset($_SESSION['role'])) {
    if ($_SESSION['role'] === 'admin') {
        $backLink = "admin-dashboard.php";
    } elseif ($_SESSION['role'] === 'staff') {
        $backLink = "staff-dashboard.php";
    }
}

// get the program id from the url
$program_id = isset($_GET['program_id']) ? (int)$_GET['program_id'] : 0;
$program_name = "Unknown Program";

// get the program name from database
if ($program_id > 0) {
    $conn = mysqli_connect("localhost", "root", "Password", "barangay_db");
    if ($conn) {
        $stmt = $conn->prepare("SELECT program_name FROM aid_program WHERE id = ?");
        $stmt->bind_param("i", $program_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            $program_name = htmlspecialchars($row['program_name']);
        }
        $stmt->close();
        $conn->close();
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
                    <input type="text" placeholder="Enter RFID number">
                    <button class="process-btn">Process</button>
                </div>
            </div>

        </div>

        <!-- RIGHT SIDE -->


        <div class="right-section">
            <div class="transaction-card">
                <h3>Recent Transaction</h3>

                <ul class="transaction-list" id="recentTransactionsList">
                </ul>

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

        fetch(`fetch_recent_transactions.php?program_id=${currentProgramId}`)
        .then(res => res.json())
        .then(data => {
            const ul = document.getElementById("recentTransactionsList");
            ul.innerHTML = ""; 

            if (data.status === "success" && data.data.length > 0) {

                data.data.forEach(txn => {
                    ul.innerHTML += `<li><strong>${txn.head_of_family}</strong> <br> <span style="font-size: 12px; color: #white;">${txn.formatted_date}</span></li>`;
                });
            } else {

                ul.innerHTML = `<li id='no-transactions' style='text-align:center; color:#white; background:transparent;'>No recent transactions yet.</li>`;
            }
        })
        .catch(err => console.error("Error loading transactions:", err));
    }

    loadRecentTransactions();
    

    if (currentProgramId === 0) {
        alert("error no program selected");
        scanBtn.disabled = true; // actual program validation
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

        fetch("get_householdinfo.php", 
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
                alert(data.message);
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

            fetch("rfid/process_scan.php", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({ rfid_number: finalRfid, program_id: currentProgramId })
            })
            .then(res => res.json())


            //create card after confirming
            .then(data => {
                if (data.status === "success") {
                    alert("Aid successfully recorded for " + data.head_of_family);
                    closeModal();
                    
                    const ul = document.getElementById("recentTransactionsList");
                    const noTransMsg = document.getElementById("no-transactions");

                    if (noTransMsg) {
                        ul.innerHTML = ""; 
                    }   
                    //generate time
                    const now = new Date();
                    const timeString = now.toLocaleDateString('en-US', { 
                        month: 'short', day: 'numeric', year: 'numeric', 
                        hour: 'numeric', minute: '2-digit', hour12: true 
                    });


                    const newLi = `<li><strong>${data.head_of_family}</strong> <br> <span style="font-size: 12px; color: #9cb1c4;">${timeString}</span></li>`;

                    
                    ul.innerHTML = newLi + ul.innerHTML;

                    rfidInput.value = ""; 
                    
                } else {
                    alert(data.message);
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

<script src="assets/popup/popup.js" defer></script>

<script src="assets/js/start-distribution.js"></script>
<script src="includes/sidebarss.js" defer></script><?php include 'includes/sidebar.php'; ?>

</body>
</html>
