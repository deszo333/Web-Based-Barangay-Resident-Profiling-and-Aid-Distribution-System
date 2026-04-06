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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Reports & Logs</title>
    <link rel="stylesheet" href="../assets/css/reports-logs.css">
    <link rel="stylesheet" href="../includes/sidebars.css">
    <link rel="stylesheet" href="../fontawesome/fontawesome/css/all.css">
    <script src="../assets/js/chart.umd.min.js"></script>
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

<main class="rp-dashboard">

    <?php 
        // ==========================================
        // the bridge: catch url parameter from scanner
        // ==========================================
        $auto_load_program = "";

        if (isset($_GET['program_id'])) {
            $pid = (int)$_GET['program_id'];
            $br_stmt = mysqli_prepare($conn, "SELECT program_name FROM aid_program WHERE id = ?");
            mysqli_stmt_bind_param($br_stmt, "i", $pid);
            mysqli_stmt_execute($br_stmt);
            $br_res = mysqli_stmt_get_result($br_stmt);
            if ($br_row = mysqli_fetch_assoc($br_res)) {
                $auto_load_program = $br_row['program_name'];
            }
            mysqli_stmt_close($br_stmt);
        }
    ?>

    <script>const autoLoadProgram = "<?php echo $auto_load_program; ?>";</script>

    <div class="rp-card audit-card">
        <div class="rp-header">
            <div class="header-text">
                <h2>Audit Logs</h2>
                <p>Track system activities and user actions</p>
            </div>
        </div>
        <div class="audit-table-wrapper">
            <table class="audit-table">
                <thead>
                    <tr>
                        <th>Date & Time</th>
                        <th>User</th>
                        <th>Module</th>
                        <th>Action Details</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // fetch real audit logs and join with the users table to get the name
                    $audit_sql = "
                        SELECT a.timestamp, a.user_id, a.target_module, a.details, 
                               u.first_name, u.last_name, u.username
                        FROM audit_logs a
                        LEFT JOIN users u ON a.user_id = u.id
                        ORDER BY a.timestamp DESC 
                        LIMIT 50
                    ";
                    $audit_res = mysqli_query($conn, $audit_sql);
                    
                    if ($audit_res && mysqli_num_rows($audit_res) > 0) {
                        while ($log = mysqli_fetch_assoc($audit_res)) {
                            // parse json if the details column is stored as json
                            $details_text = $log['details'];
                            $json = json_decode($details_text, true);
                            if (json_last_error() === JSON_ERROR_NONE && isset($json['action_summary'])) {
                                $details_text = $json['action_summary'];
                            }
                            
                            // construct the display name (first last) with fallbacks
                            if (!empty($log['first_name']) && !empty($log['last_name'])) {
                                $displayName = $log['first_name'] . ' ' . $log['last_name'];
                            } elseif (!empty($log['username'])) {
                                $displayName = $log['username'];
                            } else {
                                $displayName = "User ID: " . $log['user_id'];
                            }
                            
                            echo "<tr>";
                            echo "<td>" . date("M d, Y - h:i A", strtotime($log['timestamp'])) . "</td>";
                            echo "<td><strong>" . htmlspecialchars($displayName) . "</strong></td>";
                            echo "<td><span style='background:#f1f1f1; padding:4px 8px; border-radius:4px; font-size:12px;'>" . htmlspecialchars($log['target_module']) . "</span></td>";
                            echo "<td>" . htmlspecialchars($details_text) . "</td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='4'>No audit logs found.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
        
    <div class="rp-card">
        <div class="rp-header">
            <div class="header-text">
                <h2>Generate Report</h2>
                <p>Configure and generate distribution reports</p>
            </div>
        </div>

        <div class="report-controls">

            <div class="form-field">
                <label>Program Name</label>
                <select id="reportType">
                    <option value="" disabled selected>Select Program</option>
                    <?php
                    $sql = "SELECT DISTINCT program_name FROM aid_program";
                    $result = mysqli_query($conn, $sql);

                    while ($row = mysqli_fetch_assoc($result)) {
                        echo "<option value='{$row['program_name']}'>{$row['program_name']}</option>";
                    }
                    ?>
                </select>
            </div>

            <div class="form-field">
                <label>Aid Type</label>
                <select id="program" disabled>
                    <option value="" disabled selected>Select Aid Type</option>
                </select>
            </div>

            <div class="generate-wrapper">
                <button class="generate-report">
                    Generate Report
                </button>
            </div>

        </div>

        <div class="program-list-wrapper" style="margin-top: 40px;">
            <div class="program-list" id="programListArea">
                <div class="empty-state" style="text-align:center; padding: 50px 20px; color:#888; background: #fcfcfc; border-radius: 10px; border: 2px dashed #ccc;">
                    <i class="fa-solid fa-chart-pie" style="font-size: 50px; color:#ddd; margin-bottom: 15px;"></i>
                    <h2 style="color: #555; margin-bottom: 10px;">No Program Selected</h2>
                    <p style="font-size: 15px;">Please select a program from the dropdown and click <b>Generate Report</b>.</p>
                </div>
            </div>
        </div>
    </div>
</main>

<div class="modal-overlay" id="detailedReportModal">
    <div class="modal-content" style="width: 800px; max-width: 95%; max-height: 85vh; overflow-y: auto; text-align: left; position: relative;">
        <span class="close-modal" id="closeReportModal" style="position: absolute; right: 20px; top: 15px; font-size: 24px;"><i class="fa-solid fa-times"></i></span>
        <div id="modalReportContent">
        </div>
    </div>
</div>


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

<script src="../assets/popup/popup.js" defer></script>


<script src="../assets/js/reports-logs.js"></script>
<script src="../includes/sidebarss.js?v=2" defer></script><?php include '../includes/sidebar.php'; ?>

</body>
</html>
