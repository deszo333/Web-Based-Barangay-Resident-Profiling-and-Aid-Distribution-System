<?php
// Get the current page filename
$currentPage = basename($_SERVER['PHP_SELF']);
// Determine role for conditional sidebar items
$sidebarRole = $_SESSION['role'] ?? 'staff';
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
            <?php if ($sidebarRole === 'admin'): ?>
            <li>
                <a href="aid-program-setup.php" class="<?= ($currentPage == 'aid-program-setup.php') ? 'active' : '' ?>">
                    <i class="fa-solid fa-hand-holding-heart"></i> <span>Aid Programs</span>
                </a>
            </li>
            <?php endif; ?>
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

    <?php if ($sidebarRole === 'admin'): ?>
    <!-- SETTINGS SECTION — Admin Only -->
    <div class="sidebar-section">
        <p class="sidebar-section-title">Settings</p>
        <ul class="sidebar-menu">
            <li>
                <a href="account-man.php" class="<?= ($currentPage == 'account-man.php') ? 'active' : '' ?>">
                    <i class="fa-solid fa-cogs"></i> <span>Account Management</span>
                </a>
            </li>
        </ul>
    </div>
    <?php endif; ?>

    <!-- LOGOUT AT THE BOTTOM -->
    <div class="sidebar-logout">
        <button class="logout" id="logoutBtn">
            <i class="fa-solid fa-right-from-bracket"></i>
            <span>Logout</span>
        </button>
    </div>

</div>