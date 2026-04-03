<?php
require_once __DIR__ . '/../config/auth_check.php';
require_once __DIR__ . '/../config/db_connect.php';

$backLink = "../public/login.php"; // default fallback
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
    <title>RFID Tag Issuance</title>

    <link rel="stylesheet" href="../assets/css/rfid-tags-insurance.css">
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

    <!-- SUMMARY CARDS -->
    <div class="rfid-summary">
        <?php
        $modal_conn = $conn;
        $tot = mysqli_fetch_assoc(mysqli_query($modal_conn, "SELECT COUNT(*) as c FROM rfid_tags"))['c'];
        $act = mysqli_fetch_assoc(mysqli_query($modal_conn, "SELECT COUNT(*) as c FROM rfid_tags WHERE status='Active'"))['c'];
        $dis = mysqli_fetch_assoc(mysqli_query($modal_conn, "SELECT COUNT(*) as c FROM rfid_tags WHERE status!='Active'"))['c'];
        ?>
        <div class="rfid-summary-card-1">
            <div><p>Total Tags Issued</p><h3><?= $tot ?></h3></div>
        </div>
        <div class="rfid-summary-card-2">
            <div><p>Active Tags</p><h3><?= $act ?></h3></div>
        </div>
        <div class="rfid-summary-card-3">
            <div><p>Inactive/Disabled</p><h3><?= $dis ?></h3></div>
        </div>
    </div>

  <div class="rp-card">
    <?php
    $status = $_GET['status'] ?? 'Active';
    $search = $_GET['search'] ?? '';
    ?>
    <div class="rp-header">
        <div class="header-text">
            <h2>RFID Tags</h2>
            <p>Manage household RFID tags</p>

            <!-- TABS -->
            <div class="tabs-container">

                <a href="?status=Active&search=<?php echo urlencode($_GET['search'] ?? ''); ?>" 
                   class="tab <?php echo (($status ?? 'Active') == 'Active') ? 'active' : ''; ?>">
                   Active
                </a>

                <a href="?status=Inactive&search=<?php echo urlencode($_GET['search'] ?? ''); ?>" 
                   class="tab <?php echo (($status ?? 'Active') == 'Inactive') ? 'active' : ''; ?>">
                   Inactive
                </a>

                <a href="?status=all&search=<?php echo urlencode($_GET['search'] ?? ''); ?>" 
                   class="tab <?php echo (($status ?? 'Active') == 'all') ? 'active' : ''; ?>">
                   All
                </a>

            </div>
        </div>

        <div class="rp-actions">
            <!-- SEARCH FORM -->
            <form method="GET" style="display:flex; gap:10px;">
                <input 
                    type="text" 
                    name="search" 
                    placeholder="Search RFID..."
                    value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>"
                >

                <!-- KEEP STATUS WHEN SEARCHING -->
                <input 
                    type="hidden" 
                    name="status" 
                    value="<?php echo htmlspecialchars($_GET['status'] ?? 'Active'); ?>"
                >
            </form>

            <!-- ADD BUTTON -->
            <button class="add-tag">
                <i class="fa-solid fa-plus"></i>
                Issue RFID Tag
            </button>
        </div>
    </div>
    
    <div class="rp-table">
        <table>
            <thead>
                <tr>
                    <th>RFID Number</th>
                    <th>Household No.</th>
                    <th>Head Of Family</th>
                    <th>Date Issued</th>
                    <th>Status</th>
                    <th>Toggle</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Get current status and search query
                $status = $_GET['status'] ?? 'Active';
                $search = $_GET['search'] ?? '';

                // BASE NORMALIZED QUERY
                $query = "
                    SELECT 
                        rt.id AS rfid_id,
                        rt.version,
                        rt.rfid_number,
                        h.household_number,
                        CONCAT(head.first_name, ' ', head.last_name) AS head_of_family,
                        rt.issued_date AS date_issued,
                        rt.status,
                        rt.household_id
                    FROM rfid_tags rt
                    JOIN registered_household h ON rt.household_id = h.id
                    LEFT JOIN registered_resi head ON h.head_of_family_id = head.id
                    WHERE h.is_archived = 0 
                ";

                // Filter by status if not "all"
                if ($status !== 'all') {
                    if ($status === 'Inactive') {
                        $query .= " AND rt.status != 'Active' ";
                    } else {
                        $status_safe = mysqli_real_escape_string($conn, $status);
                        $query .= " AND rt.status = '$status_safe' ";
                    }
                }

                // Filter by search against normalized tables
                if (!empty($search)) {
                    $search_safe = mysqli_real_escape_string($conn, $search);
                    $query .= " AND (
                        rt.rfid_number LIKE '%$search_safe%' OR
                        h.household_number LIKE '%$search_safe%' OR
                        CONCAT(head.first_name, ' ', head.last_name) LIKE '%$search_safe%'
                    ) ";
                }

                // Sort by date issued
                $query .= " ORDER BY rt.issued_date DESC";
                
                $result = mysqli_query($conn, $query);

                if ($result && mysqli_num_rows($result) > 0):
                    while ($row = mysqli_fetch_assoc($result)):
                        
                        $is_active = ($row['status'] === 'Active');
                        $display_status = $is_active ? 'Active' : 'Inactive';
                        $status_class = $is_active ? 'active' : 'inactive';
                ?>
                <tr data-id="<?= $row['rfid_id'] ?>">
                    <td><?= htmlspecialchars($row['rfid_number']) ?></td>
                    <td><?= htmlspecialchars($row['household_number']) ?></td>
                    <td><?= htmlspecialchars($row['head_of_family']) ?></td>
                    <td><?= date("M d, Y", strtotime($row['date_issued'])) ?></td>
                    <td>
                        <span class="status <?= $status_class ?>">
                            <?= $display_status ?>
                        </span>
                    </td>
                    <td>
                        <?php if ($is_active): ?>
                            <button class="deactivate-btn" data-id="<?= $row['rfid_id'] ?>">Deactivate</button>
                        <?php else: ?>
                            <button class="activate-btn" data-id="<?= $row['rfid_id'] ?>">Activate</button>
                        <?php endif; ?>
                    </td>
                    <td>
                        <button class="edit" 
                            data-id="<?= $row['rfid_id'] ?>"
                            data-version="<?= $row['version'] ?>"
                            data-rfid="<?= htmlspecialchars($row['rfid_number']) ?>"
                            data-householdid="<?= $row['household_id'] ?>"
                        >Edit</button>
                        </td>
                </tr>
                <?php
                    endwhile;
                else:
                ?>
                <tr>
                    <td colspan="7" style="text-align:center;">
                        No RFID tags found
                    </td>
                </tr>
                <?php
                endif;
                ?>
            </tbody>
        </table>
    </div>

</div>

</main>

<div class="modal-overlay" id="modalOverlay"></div>

<!-- Add/Edit Resident Modal -->
<div class="resident-modal" id="residentModal">
    <div class="resident-modal-content">
        <div class="modal-header">
            <h3>Add / Edit Tag</h3>
            <span class="close-btn" id="closeModal">&times;</span>
        </div>

        <form id="addResidentForm">
            <input type="hidden" name="rfid_id" id="rfid_id">
            <input type="hidden" name="version" id="rfid_version">

            <div class="modal-body">
                <div class="rfid-row">
                    <label for="rfid_number">RFID Number</label>
                    <div class="rfid-input-group">
                        <input type="text" name="rfid_number" id="rfid_number" class="rfid-number" placeholder="Scan or Type RFID" required>
                        <button type="button" class="rfid-btn" id="scanRfidBtn">
                            <i class="fa-solid fa-id-card"></i>
                        </button>
                    </div>
                </div>

                <div class="modal-row">
                    <label>Assign to Household</label>
                    <select name="household_id" id="household_id" required style="width: 100%; padding: 10px; margin-bottom: 15px; border-radius: 5px; border: 1px solid #ccc;">
                        <option value="">-- Select a Household --</option>
                        <?php
                        $h_res = mysqli_query($conn, "SELECT h.id, h.household_number, CONCAT(head.first_name, ' ', head.last_name) as head_name FROM registered_household h LEFT JOIN registered_resi head ON h.head_of_family_id = head.id WHERE h.is_archived = 0");
                        if ($h_res) {
                            while($h_row = mysqli_fetch_assoc($h_res)){
                                echo "<option value='{$h_row['id']}'>{$h_row['household_number']} - {$h_row['head_name']}</option>";
                            }
                        }
                        ?>
                    </select>
                </div>
            </div>

            <div class="modal-footer">
                <button type="submit" class="save" id="submitBtn">Issue Tag</button>
            </div>
        </form>

        <!-- RFID overlay -->
        <div class="rfid-overlay" id="rfidOverlay">
            <div class="rfid-box">
                <i class="fa-solid fa-id-card"></i>
                <h3>Waiting for RFID Scan</h3>
                <p>Please tap the RFID card</p>
                <button id="cancelRfid">Cancel</button>
            </div>
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

<script src="../assets/js/rfid-tagss.js"></script>
<script src="../includes/sidebarss.js?v=2" defer></script><?php include '../includes/sidebar.php'; ?>

<script src="../assets/js/rfid_scanner.js"></script>

<script>
document.addEventListener("DOMContentLoaded", () => {
    const scanBtn = document.getElementById("scanRfidBtn");
    const rfidInput = document.getElementById("rfid_number");
    const rfidOverlay = document.getElementById("rfidOverlay");
    const cancelRfidBtn = document.getElementById("cancelRfid");

    // scan button click
    if (scanBtn) {
        scanBtn.addEventListener("click", () => {
            // show overlay
            if (rfidOverlay) rfidOverlay.style.display = "flex";

            // connect scanner if not already connected
            if (typeof rfidPort === 'undefined' || !rfidPort) {
                connectRFIDScanner(assignRFIDToInput, scanBtn);
            }
        });
    }

    // callback for successful scan
    function assignRFIDToInput(scannedID) {
        console.log("Assigning RFID to Tag Issuance:", scannedID);
        // uid to input box
        if (rfidInput) {
            rfidInput.value = scannedID;
        }
        
        // hide overlay after
        if (rfidOverlay) {
            rfidOverlay.style.display = "none";
        }
    }

    // close overlay on cancel
    if (cancelRfidBtn) {
        cancelRfidBtn.addEventListener("click", () => {
            if (rfidOverlay) rfidOverlay.style.display = "none";
        });
    }
});
</script>

</body>
</html>
