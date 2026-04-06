<?php
require_once __DIR__ . '/../config/auth_check.php';
require_once __DIR__ . '/../config/db_connect.php';

$backLink = "../public/login.php"; 
if (isset($_SESSION['role'])) {
    if ($_SESSION['role'] === 'admin') {
        $backLink = "../pages/admin-dashboard.php";
    } elseif ($_SESSION['role'] === 'staff') {
        $backLink = "../pages/staff-dashboard.php";
    }
}

$program_id = isset($_GET['program_id']) ? (int)$_GET['program_id'] : 0;
$program_name = "Unknown Program";
$program_status = "Scheduled";

if ($program_id > 0) {
    $stmt = $conn->prepare("SELECT program_name, status FROM aid_program WHERE id = ?");
    $stmt->bind_param("i", $program_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $program_name = htmlspecialchars($row['program_name']);
        $program_status = htmlspecialchars($row['status']);
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Distribution Control</title>
    <link rel="stylesheet" href="../assets/css/start-distribution.css">
    <link rel="stylesheet" href="../includes/sidebars.css">
    <link rel="stylesheet" href="../fontawesome/fontawesome/css/all.css">
    <style>
        .btn-minimal { padding: 8px 16px; font-size: 14px; font-weight: 600; border: none; border-radius: 6px; cursor: pointer; color: white; transition: 0.2s; }
        .btn-start { background-color: #16a34a; }
        .btn-start:hover { background-color: #15803d; }
        .btn-pause { background-color: #ca8a04; }
        .btn-pause:hover { background-color: #a16207; }
        .btn-end { background-color: #dc2626; }
        .btn-end:hover { background-color: #b91c1c; }
        .status-badge-header { padding: 5px 12px; border-radius: 20px; font-size: 14px; font-weight: bold; margin-left: 10px; }
        .badge-scheduled { background-color: #e0f2fe; color: #0284c7; } 
        .badge-ongoing { background-color: #dcfce7; color: #16a34a; }   
        .badge-paused { background-color: #fef08a; color: #ca8a04; }    
        .badge-completed { background-color: #f3f4f6; color: #4b5563; } 
    </style>
</head>
<body>

<nav class="rp-navbar">
    <button class="toggle-sidebar" id="toggleBtn"><i class="fa-solid fa-bars" id="toggleIcon"></i></button>
    <a href="distribution-page.php" class="back-btn"><i class="fa-solid fa-arrow-left"></i></a>
    <div class="rp-navbar-content">
        <img src="../assets/images/logos.png" alt="Barangay Logo">
        <div class="nav-text">
            <span class="page-title">Barangay Abangan Norte</span>
            <p>Household Data Management System</p>
        </div>
    </div>
</nav>

<main class="rp-dashboard">
    <div class="program-card">
        <div class="program-left">
            <h2 id="programName" style="display: flex; align-items: center;">
                <?php echo $program_name; ?> 
                <span class="status-badge-header badge-<?php echo strtolower($program_status); ?>"><?php echo $program_status; ?></span>
            </h2>
            <p>Distribution Control Center</p>
        </div>
        <div class="program-right" style="display: flex; gap: 10px; align-items: center;">
            <?php if ($_SESSION['role'] === 'admin'): ?>
                <?php if ($program_status === 'Scheduled' || $program_status === 'Paused'): ?>
                    <button class="btn-minimal btn-start" onclick="toggleProgramStatus(<?php echo $program_id; ?>, 'Ongoing')">Start Event</button>
                <?php endif; ?>

                <?php if ($program_status === 'Ongoing'): ?>
                    <button class="btn-minimal btn-pause" onclick="toggleProgramStatus(<?php echo $program_id; ?>, 'Paused')">Pause</button>
                    <button class="btn-minimal btn-end" onclick="toggleProgramStatus(<?php echo $program_id; ?>, 'Completed')">End Event</button>
                <?php endif; ?>
            <?php endif; ?>

            <a href="distribution-page.php" class="change-program-btn" style="text-decoration:none; display:inline-block;">
                <i class="fa-solid fa-repeat"></i> Change Program
            </a>
        </div>
    </div>

    <div class="distribution-container">
        
        <div class="left-section">
            <?php if ($program_status === 'Ongoing'): ?>
                <div class="scan-card">
                    <div class="scan-icon-wrapper pulse" id="scan_trigger">
                        <i class="fa-solid fa-id-card scan-icon"></i>
                    </div>
                    <h3 id="scan_status">Click to Connect Scanner</h3>
                    <input type="hidden" id="hidden_rfid_input" name="scanned_rfid">
                </div>

                <div class="manual-entry-card">
                    <h4>Manual RFID Entry</h4>
                    <div class="manual-input-group">
                        <input type="text" id="manual_rfid_input" placeholder="Enter RFID number">
                        <button class="process-btn" id="manual_process_btn">Process</button>
                    </div>
                </div>
            <?php else: ?>
                <div style="background: #fff; padding: 40px; text-align: center; border-radius: 10px; color: #64748b; font-size: 16px; border: 1px solid #e2e8f0;">
                    <i class="fa-solid fa-lock" style="font-size: 30px; color: #cbd5e1; margin-bottom: 15px;"></i><br>
                    The scanner is currently locked because this event is <b><?php echo $program_status; ?></b>.<br>
                    <?php echo ($_SESSION['role'] === 'admin') ? "Use the controls above to start the event." : "An Admin must start the event to unlock the scanner."; ?>
                </div>
            <?php endif; ?>
        </div>

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
                        <h3 style="color: #ffffff; margin-bottom: 0;">Recent Claims</h3>
                        <a href="reports-logs.php?program_id=<?php echo $program_id; ?>" class="view-report-btn">
                            <i class="fa-solid fa-file-lines"></i> View Report
                        </a>
                    </div>
                    <ul class="transaction-list claimed-list" id="recentTransactionsList"></ul>
                </div>
            </div>
        </div>

    </div>
</main>

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

<link rel="stylesheet" href="../assets/popup/popup.css">
<div id="popup-container"></div>
<script>
fetch("../assets/popup/popup.html").then(res => res.text()).then(html => { document.getElementById("popup-container").innerHTML = html; });
</script>
<script>
const currentProgramId = <?php echo $program_id; ?>;
</script>
<script src="../assets/popup/popup.js" defer></script>
<script src="../assets/js/rfid_scanner.js"></script>
<script src="../assets/js/start-distribution.js?v=<?php echo time(); ?>"></script>
<script src="../includes/sidebarss.js" defer></script>
<?php include '../includes/sidebar.php'; ?>
</body>
</html>