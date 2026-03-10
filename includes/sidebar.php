<?php
// Get the current page filename
$currentPage = basename($_SERVER['PHP_SELF']);
?>

<!-- sidebar.php -->
<div class="sidebar collapsed" id="sidebar">

    <!-- MANAGEMENT SECTION -->
    <div class="sidebar-section">
        <p class="sidebar-section-title">Management</p>
        <ul class="sidebar-menu">
            <li>
                <a href="resident-profiling.php" class="<?= ($currentPage == 'resident-profiling.php') ? 'active' : '' ?>">
                    <i class="fa-solid fa-users"></i> <span>Residents</span>
                </a>
            </li>
            <li>
                <a href="household-management.php" class="<?= ($currentPage == 'household-management.php') ? 'active' : '' ?>">
                    <i class="fa-solid fa-house"></i> <span>Households</span>
                </a>
            </li>
            <li>
                <a href="aid-program-setup.php" class="<?= ($currentPage == 'aid-program-setup.php') ? 'active' : '' ?>">
                    <i class="fa-solid fa-hand-holding-heart"></i> <span>Aid Programs</span>
                </a>
            </li>
        </ul>
    </div>

    <!-- DISTRIBUTION SECTION -->
    <div class="sidebar-section">
        <p class="sidebar-section-title">Distribution</p>
        <ul class="sidebar-menu">
            <li>
                <a href="rfid-tags-insurance.php" class="<?= ($currentPage == 'rfid-tags-insurance.php') ? 'active' : '' ?>">
                    <i class="fa-solid fa-id-card"></i> <span>RFID Issuance</span>
                </a>
            </li>
            <li>
                <a href="distribution-page.php" class="<?= ($currentPage == 'distribution-page.php') ? 'active' : '' ?>">
                    <i class="fa-solid fa-qrcode"></i> <span>Distribution Page</span>
                </a>
            </li>
        </ul>
    </div>

    <!-- REPORT SECTION -->
    <div class="sidebar-section">
        <p class="sidebar-section-title">Report</p>
        <ul class="sidebar-menu">
            <li>
                <a href="reports-logs.php" class="<?= ($currentPage == 'reports-logs.php') ? 'active' : '' ?>">
                    <i class="fa-solid fa-file-lines"></i> <span>Reports & Logs</span>
                </a>
            </li>
        </ul>
    </div>

    <!-- SETTINGS SECTION -->
    <div class="sidebar-section">
        <p class="sidebar-section-title">Settings</p>
        <ul class="sidebar-menu">
            <li>
                <a href="admin-settings.html" class="<?= ($currentPage == 'admin-settings.html') ? 'active' : '' ?>">
                    <i class="fa-solid fa-cogs"></i> <span>Admin Settings</span>
                </a>
            </li>
            <li>
                <a href="signup.php" class="<?= ($currentPage == 'signup.php') ? 'active' : '' ?>">
                    <i class="fa-solid fa-user-plus"></i> <span>Create Staff Account</span>
                </a>
            </li>
        </ul>
    </div>

    <!-- LOGOUT AT THE BOTTOM -->
    <div class="sidebar-logout">
        <button class="logout" id="logoutBtn">
            <i class="fa-solid fa-right-from-bracket"></i>
            <span>Logout</span>
        </button>
    </div>

</div>