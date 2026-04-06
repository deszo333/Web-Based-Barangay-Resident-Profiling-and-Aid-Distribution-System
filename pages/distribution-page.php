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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Distribution Page</title>
    <link rel="stylesheet" href="../assets/css/distribution-page.css">
    <link rel="stylesheet" href="../includes/sidebars.css">
    <link rel="stylesheet" href="../fontawesome/fontawesome/css/all.css">
    <style>
        .status-badge { padding: 4px 10px; border-radius: 6px; font-weight: bold; font-size: 12px; }
        .badge-scheduled { background-color: #e0f2fe; color: #0284c7; } 
        .badge-ongoing { background-color: #dcfce7; color: #16a34a; }   
        .badge-paused { background-color: #fef08a; color: #ca8a04; }    
        .badge-completed { background-color: #f3f4f6; color: #4b5563; } 
    </style>
</head>
<body>

<nav class="rp-navbar">
    <button class="toggle-sidebar" id="toggleBtn">
        <i class="fa-solid fa-bars" id="toggleIcon"></i>
    </button>
    <a href="<?php echo $backLink; ?>" class="back-btn">
        <i class="fa-solid fa-arrow-left"></i>
    </a>
    <div class="rp-navbar-content">
        <img src="../assets/images/logos.png" alt="Barangay Logo">
        <div class="nav-text">
            <span class="page-title">Barangay Abangan Norte</span>
            <p>Household Data Management System</p>
        </div>
    </div>
</nav>

<main class="rp-dashboard">
    <div class="rp-card">
    <?php
    $tab = $_GET['tab'] ?? 'ongoing';
    $search = $_GET['search'] ?? '';
    ?>

    <div class="rp-header">
        <div class="header-text">
            <h2>Select Aid for Distribution</h2>
            <p>Enter an event to control the scanner and distribute aid</p>

            <div class="tabs-container">
                <a href="?tab=ongoing&search=<?php echo urlencode($search); ?>" class="tab <?= ($tab === 'ongoing') ? 'active' : '' ?>">Live / Paused</a>
                <a href="?tab=scheduled&search=<?php echo urlencode($search); ?>" class="tab <?= ($tab === 'scheduled') ? 'active' : '' ?>">Scheduled</a>
                <a href="?tab=completed&search=<?php echo urlencode($search); ?>" class="tab <?= ($tab === 'completed') ? 'active' : '' ?>">Completed</a>
                <a href="?tab=all&search=<?php echo urlencode($search); ?>" class="tab <?= ($tab === 'all') ? 'active' : '' ?>">All</a>
            </div>
        </div>    

        <div class="rp-actions">
            <form method="GET">
                <input type="text" name="search" placeholder="Search aid programs..." value="<?php echo htmlspecialchars($search); ?>">
                <input type="hidden" name="tab" value="<?php echo htmlspecialchars($tab); ?>">
            </form>
        </div>
    </div>

    <div class="distribution-grid">
    <?php
    $searchEscaped = mysqli_real_escape_string($conn, trim($search));
    $whereConditions = [];

    // SEARCH FILTER
    if ($searchEscaped !== "") {
        $whereConditions[] = "(program_name LIKE '%$searchEscaped%' OR aid_type LIKE '%$searchEscaped%')";
    }

    // TAB FILTER (Strictly checks Database Status, NOT Date)
    if ($tab === 'ongoing') {
        $whereConditions[] = "status IN ('Ongoing', 'Paused')";
    } elseif ($tab === 'scheduled') {
        $whereConditions[] = "status = 'Scheduled'";
    } elseif ($tab === 'completed') {
        $whereConditions[] = "status = 'Completed'";
    }
    
    // Hide archived from this screen completely
    $whereConditions[] = "status != 'Archived'";

    $whereSQL = count($whereConditions) > 0 ? "WHERE " . implode(" AND ", $whereConditions) : "";

    $sql = "SELECT * FROM aid_program $whereSQL ORDER BY id DESC";
    $result = mysqli_query($conn, $sql);

    if ($result && mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $badgeClass = 'badge-' . strtolower($row['status']);
            $btnLink = "start-distribution.php?program_id={$row['id']}";
            
            // Dynamic Button Logic based on State
            if ($row['status'] === 'Ongoing') {
                $btnText = "Enter Scanner";
                $btnStyle = "background-color: #16a34a; color: white;"; // Green
            } elseif ($row['status'] === 'Paused') {
                $btnText = "Resume Event";
                $btnStyle = "background-color: #ca8a04; color: white;"; // Yellow/Orange
            } elseif ($row['status'] === 'Scheduled') {
                $btnText = "Open Event Settings";
                $btnStyle = "background-color: #0284c7; color: white;"; // Blue
            } else {
                $btnText = "View Report";
                $btnStyle = "background-color: #4b5563; color: white;"; // Gray
                $btnLink = "reports-logs.php?program_id={$row['id']}"; // Route completed straight to reports
            }

            echo "
            <div class='distribution-card'>
                <div class='card-top'>
                    <h3>{$row['program_name']}</h3>
                    <span class='status-badge {$badgeClass}'>{$row['status']}</span>
                </div>
                <div class='card-body'>
                    <div class='info-row'>
                        <span class='label'>Type</span>
                        <span class='value'>{$row['aid_type']}</span>
                    </div>
                    <div class='info-row'>
                        <span class='label'>Scheduled</span>
                        <span class='value'>{$row['date_scheduled']}</span>
                    </div>
                    <div class='info-row'>
                        <span class='label'>Target</span>
                        <span class='value'>{$row['beneficiaries']} beneficiaries</span>
                    </div>
                </div>
                <div class='card-footer'>
                    <a href='{$btnLink}' class='start-btn' style='{$btnStyle}; width: 100%; text-align: center; padding: 10px; border-radius: 6px; display: block; font-weight: bold; text-decoration: none;'>
                        {$btnText}
                    </a>
                </div>
            </div>";
        }
    } else {
        echo "<p style='grid-column: 1/-1; text-align:center; padding: 40px; color: #64748b;'>No programs found for this category.</p>";
    }

    mysqli_close($conn);
    ?>
    </div>
    </div>
</main>
<script src="../includes/sidebarss.js" defer></script><?php include '../includes/sidebar.php'; ?>
</body>
</html>
