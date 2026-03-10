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
    <title>Distribution Page</title>

    <link rel="stylesheet" href="assets/css/distribution-page.css">
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

    <div class="rp-card">

        <!-- UPPER PART -->
        <div class="rp-header">
            <div class="header-text">
                <h2>Select Aid for Distribution</h2>
                <p>Choose an aid program to start distribution</p>
            </div>

            <div class="rp-actions">
                <form method="GET">
                    <input type="text" name="search" placeholder="Search aid programs..."
                        value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>">
                </form>
            </div>
        </div>

        <!-- LOWER PART: AID CARDS -->
        <div class="distribution-grid">

        <?php
        $conn = mysqli_connect("localhost", "root", "Password", "barangay_db");
        if (!$conn) {
            die("Connection failed: " . mysqli_connect_error());
        }

        $search = trim($_GET['search'] ?? '');
        $whereConditions = ["status = 'Active'"];

        if ($search !== "") {
            $searchEscaped = mysqli_real_escape_string($conn, $search);
            $whereConditions[] = "(program_name LIKE '%$searchEscaped%' 
                                  OR aid_type LIKE '%$searchEscaped%')";
        }

        $whereSQL = "WHERE " . implode(" AND ", $whereConditions);

        $today = date("Y-m-d");

        // Sort by relative date: Today → Future → Past
        $sql = "SELECT *,
                CASE
                    WHEN date_scheduled = '$today' THEN 1
                    WHEN date_scheduled > '$today' THEN 2
                    ELSE 3
                END AS display_order
                FROM aid_program
                $whereSQL
                ORDER BY display_order ASC, date_scheduled ASC";

        $result = mysqli_query($conn, $sql);

        if ($result && mysqli_num_rows($result) > 0) {

            while ($row = mysqli_fetch_assoc($result)) {

                $dateScheduled = $row['date_scheduled'];

                // BUTTON LOGIC
                if ($dateScheduled == $today) {
                    $btnText = "Start Distribution";
                    $btnClass = "btn-start";
                    $btnLink = "start-distribution.php?program_id={$row['id']}";
                } elseif ($dateScheduled < $today) {
                    $btnText = "Complete";
                    $btnClass = "btn-complete";
                    $btnLink = "#";
                } else {
                    $btnText = "Locked";
                    $btnClass = "btn-locked";
                    $btnLink = "#";
                }

                echo "
                <div class='distribution-card'>

                    <div class='card-top'>
                        <h3>{$row['program_name']}</h3>
                        <span class='status-badge active'>{$row['status']}</span>
                    </div>

                    <div class='card-body'>
                        <div class='info-row'>
                            <span class='label'>Type</span>
                            <span class='value'>{$row['aid_type']}</span>
                        </div>

                        <div class='info-row'>
                            <span class='label'>Date</span>
                            <span class='value'>{$row['date_scheduled']}</span>
                        </div>

                        <div class='info-row'>
                            <span class='label'>Beneficiaries</span>
                            <span class='value'>{$row['beneficiaries']}</span>
                        </div>
                    </div>

                    <div class='card-footer'>
                        <a href='{$btnLink}' class='start-btn {$btnClass}'>
                            {$btnText}
                        </a>
                    </div>

                </div>
                ";
            }

        } else {
            echo "<p>No active aid programs available.</p>";
        }

        mysqli_close($conn);
        ?>

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

<script src="assets/popup/popup.js" defer></script>

<script src="assets/js/distribution-page.js"></script>
<script src="includes/sidebarss.js" defer></script><?php include 'includes/sidebar.php'; ?>

</body>
</html>
