<?php
require 'auth_check.php';

$backLink = "login.php"; // default fallback if no login session
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
    <title>Reports & Logs</title>
    <link rel="stylesheet" href="assets/css/reports-logs.css">
    <link rel="stylesheet" href="includes/sidebars.css">
    <link rel="stylesheet" href="fontawesome/fontawesome/css/all.css">
    <script src="assets/js/chart.umd.min.js"></script>
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

    <!-- AUDIT LOGS CARD -->
    <div class="rp-card audit-card">

        <!-- HEADER -->
        <div class="rp-header">
            <div class="header-text">
                <h2>Audit Logs</h2>
                <p>Track system activities and user actions</p>
            </div>
        </div>

        <!-- TABLE -->
        <div class="audit-table-wrapper">
            <table class="audit-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>User</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>2026-03-28 10:15 AM</td>
                        <td>Admin</td>
                        <td>Generated distribution report</td>
                    </tr>
                    <tr>
                        <td>2026-03-28 09:40 AM</td>
                        <td>Staff 1</td>
                        <td>Added new beneficiary</td>
                    </tr>
                
                </tbody>
            </table>
        </div>

    </div>
        
    <!-- MERGED REPORT CARD -->
    <div class="rp-card">

        <!-- HEADER -->
        <div class="rp-header">
            <div class="header-text">
                <h2>Generate Report</h2>
                <p>Configure and generate distribution reports</p>
            </div>
        </div>

        <!-- REPORT CONTROLS -->
        <div class="report-controls">

            <!-- Program Name -->
            <div class="form-field">
                <label>Program Name</label>
                <select id="reportType">
                    <option value="" disabled selected>Select Program</option>
                    <?php
                    $conn = mysqli_connect("localhost", "root", "Password", "barangay_db");

                    $sql = "SELECT DISTINCT program_name FROM aid_program";
                    $result = mysqli_query($conn, $sql);

                    while ($row = mysqli_fetch_assoc($result)) {
                        echo "<option value='{$row['program_name']}'>{$row['program_name']}</option>";
                    }

                    mysqli_close($conn);
                    ?>
                </select>
            </div>

            <!-- Aid Type -->
            <div class="form-field">
                <label>Aid Type</label>
                <select id="program" disabled>
                    <option value="" disabled selected>Select Aid Type</option>
                </select>
            </div>

            <!-- BUTTON -->
            <div class="generate-wrapper">
                <button class="generate-report">
                    Search Report
                </button>
            </div>

        </div>

        <!-- DOWNLOAD -->
        <div class="download-section">
            <button class="download excel">
                <i class="fa-solid fa-file-excel"></i> Download Excel
            </button>

            <button class="download csv">
                <i class="fa-solid fa-file-csv"></i> Download CSV
            </button>
        </div>

        <!-- ======================= -->
        <!-- PROGRAM DISTRIBUTION -->
        <!-- ======================= -->

        <div class="rp-header" style="margin-top:40px;">
        </div>

        <div class="program-list-wrapper">
            <div class="program-list">
                <?php
                $conn = mysqli_connect("localhost", "root", "Password", "barangay_db");

                $sql = "SELECT program_name, beneficiaries FROM aid_program ORDER BY id DESC";
                $result = mysqli_query($conn, $sql);

                $count = 0;

                while ($row = mysqli_fetch_assoc($result)) {
                    $count++;
                    $hiddenClass = $count > 3 ? "hidden-program" : "";

                    echo "<div class='program-item $hiddenClass'
                        data-program='{$row['program_name']}'
                        data-beneficiaries='{$row['beneficiaries']}'>

                        <div class='program-text'>
                            <div class='program-name'>{$row['program_name']}</div>

                            <div class='program-details'>
                                <span>{$row['beneficiaries']} unique beneficiaries</span>
                                <span>Distributions: 0</span>
                            </div>
                        </div>

                        <div class='chart-wrapper'>
                            <canvas class='mini-chart'></canvas>
                        </div>

                        <div class='chart-legend'>
                            <span class='legend-item remaining'>■ Remaining</span>
                            <span class='legend-item claimed'>■ Claimed</span>
                        </div>

                    </div>";
                }

                mysqli_close($conn);
                ?>
            </div>
        </div>

        <!-- SEE MORE -->
        <?php if ($count > 3): ?>
        <div class="see-more-btn-wrapper">
            <button class="see-more-btn">See More</button>
        </div>
        <?php endif; ?>

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

<script src="assets/popup/popup.js" defer></script>


<script src="assets/js/reports-logs.js"></script>
<script src="includes/sidebarss.js" defer></script><?php include 'includes/sidebar.php'; ?>

</body>
</html>
