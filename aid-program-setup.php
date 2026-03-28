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
    <title>Aid Program Setup</title>
    <link rel="stylesheet" href="assets/css/aid-programs-setup.css">
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
    <?php
$status = $_GET['status'] ?? 'Active';
$search = $_GET['search'] ?? '';
?>
    <div class="rp-card">

        <!-- UPPER PART -->
        <div class="rp-header">
            <div class="header-text">
                <h2>Aid Programs</h2>
                <p>Create and manage distribution programs</p>

                <div class="tabs-container">
                    

                    <a href="?status=Active&search=<?php echo urlencode($_GET['search'] ?? ''); ?>" 
                       class="tab <?php echo ($status == 'Active') ? 'active' : ''; ?>">
                       Active
                    </a>

                    <a href="?status=Inactive&search=<?php echo urlencode($_GET['search'] ?? ''); ?>" 
                       class="tab <?php echo ($status == 'Inactive') ? 'active' : ''; ?>">
                       Inactive
                    </a>

                    <a href="?status=all&search=<?php echo urlencode($_GET['search'] ?? ''); ?>" 
                       class="tab <?php echo ($status == 'all') ? 'active' : ''; ?>">
                       All
                    </a>
                </div>
            </div>

            <div class="rp-actions">
                <!-- SEARCH -->
                <form method="GET" style="display: flex; gap: 10px;">
                    <input type="text" name="search" placeholder="Search aid programs..." 
                        value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>">

                    <!-- keep status when searching -->
                    <input type="hidden" name="status" value="<?php echo htmlspecialchars($_GET['status'] ?? 'Active'); ?>">
                </form>

                <!-- ADD BUTTON -->
                <button class="add-resident">
                    <i class="fa-solid fa-plus"></i>
                    Add Program
                </button>
            </div>
        </div>

        <!-- LOWER PART: TABLE -->
        <div class="rp-table">
            <table>
                <thead>
                    <tr>
                        <th>Program Name</th>
                        <th>Aid Type</th>
                        <th>Date Scheduled</th>
                        <th>Beneficiaries</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                        $conn = mysqli_connect("localhost", "root", "Password", "barangay_db");
                        if (!$conn) {
                            die("Connection failed: " . mysqli_connect_error());
                        }

                        $search = trim($_GET['search'] ?? '');
                        $status = $_GET['status'] ?? 'Active';

                        $conditions = [];

                        if ($search !== "") {
                            $searchEscaped = mysqli_real_escape_string($conn, $search);
                            $conditions[] = "(program_name LIKE '%$searchEscaped%' 
                                            OR aid_type LIKE '%$searchEscaped%' 
                                            OR date_scheduled LIKE '%$searchEscaped%')";
                        }

                        if ($status !== "all") {
                            $statusEscaped = mysqli_real_escape_string($conn, $status);
                            $conditions[] = "status = '$statusEscaped'";
                        }

                        $whereSQL = "";
                        if (!empty($conditions)) {
                            $whereSQL = "WHERE " . implode(" AND ", $conditions);
                        }

                        $sql = "SELECT * FROM aid_program $whereSQL ORDER BY id DESC";
                        $result = mysqli_query($conn, $sql);

                        if ($result && mysqli_num_rows($result) > 0) {
                            while ($row = mysqli_fetch_assoc($result)) {
                                $badgeClass = ($row['status'] === 'Active') ? 'badge-active' : 'badge-inactive';

                                echo "<tr>
                                    <td>{$row['program_name']}</td>
                                    <td>{$row['aid_type']}</td>
                                    <td>{$row['date_scheduled']}</td>
                                    <td>{$row['beneficiaries']}</td>
                                    <td><span class='status-badge {$badgeClass}'>{$row['status']}</span></td>
                                    <td>
                                         <button class='edit'
                                            data-id='{$row['id']}'
                                            data-version='{$row['version']}'
                                            data-name='{$row['program_name']}'
                                            data-type='{$row['aid_type']}'
                                            data-date='{$row['date_scheduled']}'
                                            data-beneficiaries='{$row['beneficiaries']}'
                                            data-status='{$row['status']}'>
                                            <i class='fa-solid fa-pen-to-square'></i>
                                        </button>
                                        <button class='delete' data-id='{$row['id']}'>
                                            <i class='fa-solid fa-trash'></i>
                                        </button>
                                    </td>
                                </tr>";
                            }
                        } else {
                            echo "<tr><td colspan='6'>No aid programs found.</td></tr>";
                        }

                        mysqli_close($conn);
                    ?>
                </tbody>
            </table>
        </div>

    </div>

</main>

<!-- MODAL OVERLAY -->
<div class="modal-overlay" id="modalOverlay"></div>

<!-- ADD / EDIT PROGRAM MODAL -->
<div class="resident-modal" id="residentModal">
    <div class="resident-modal-content">
        <div class="modal-header">
            <div class="modal-title">
                <i class="fa-solid fa-hand-holding-heart" id="modalIcon" style="color:#144876; font-size: 20px; margin-right: 8px;"></i>
                <h3 id="modalTitle" style="display:inline-block; margin:0; color:#144876;">Add Aid Program</h3>
            </div>
            <span class="close-btn" id="closeModal">&times;</span>
        </div>

        <form id="addResidentForm" class="program-form-grid">
            <input type="hidden" name="id" id="program_id">
            <input type="hidden" name="version" id="program_version">

            <div class="form-row two">
                <div class="form-field">
                    <label>Program Name <span style="color:red;">*</span></label>
                    <input type="text" name="program_name" placeholder="e.g., Ayuda 2024" required style="text-transform: capitalize;">
                </div>
                <div class="form-field">
                    <label>Aid Type <span style="color:red;">*</span></label>
                    <input type="text" name="aid_type" placeholder="e.g., Food Packs, Cash Assistance" required style="text-transform: capitalize;">
                </div>
            </div>

            <div class="form-row two">
                <div class="form-field">
                    <label>Date Scheduled <span style="color:red;">*</span></label>
                    <input type="date" name="date_scheduled" required>
                </div>
                <div class="form-field">
                    <label>Number of Beneficiaries <span style="color:red;">*</span></label>
                    <input type="number" name="beneficiaries" placeholder="Target count" min="0" required>
                </div>
            </div>

            <div class="form-row one">
                <div class="form-field">
                    <label>Status <span style="color:red;">*</span></label>
                    <select name="status" required>
                        <option value="" disabled selected>Select Status</option>
                        <option value="Active">Active</option>
                        <option value="Inactive">Inactive</option>
                    </select>
                </div>
            </div>

            <button type="submit" style="margin-top: 20px;">Save Program</button>
        </form>
    </div>
</div>

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

<script src="assets/js/aid-programs-setup.js"></script>
<script src="includes/sidebarss.js?v=2" defer></script><?php include 'includes/sidebar.php'; ?>
</body>
</html>
