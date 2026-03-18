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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Household Management</title>

    <link rel="stylesheet" href="assets/css/households-management.css">
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

        <!-- HEADER -->
        <div class="rp-header">
            <div class="header-text">
                <h2>Registered Households</h2>
                <p>Manage household groups and their members</p>
            </div>

            <div class="rp-actions">
                <input 
                type="text" 
                id="searchHousehold"
                placeholder="Search households..."
            >
                <button class="add-household">
                    <i class="fa-solid fa-plus"></i> Add Household
                </button>
            </div>
        </div>

        <!-- TABLE -->
        <div class="rp-table">
    <table>
        <thead>
            <tr>
                <th>Household No.</th>
                <th>Head of Family</th>
                <th>Address</th>
                <th>Members</th>
                <th>RFID No.</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody id="householdTableBody">
        <?php
        // Connect to database
        $conn = mysqli_connect("localhost", "root", "Password", "barangay_db");
        if (!$conn) die("Connection failed: " . mysqli_connect_error());

        // Search
        $search = $_GET['search'] ?? '';

        // PAGINATION VARIABLES
        $limit = 8; // 8 records per page
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;

        // Count total records
        if (!empty($search)) {
            $search_safe = mysqli_real_escape_string($conn, $search);
            $count_sql = "
                SELECT COUNT(*) as total FROM registered_household
                WHERE household_number LIKE '%$search_safe%'
                OR head_of_family LIKE '%$search_safe%'
                OR address LIKE '%$search_safe%'
                OR household_members LIKE '%$search_safe%'
            ";
        } else {
            $count_sql = "SELECT COUNT(*) as total FROM registered_household";
        }

        $count_result = mysqli_query($conn, $count_sql);
        $total_records = mysqli_fetch_assoc($count_result)['total'];
        $total_pages = ceil($total_records / $limit);

        // Clamp $page
        if ($page < 1) $page = 1;
        if ($page > $total_pages) $page = $total_pages;

        $offset = ($page - 1) * $limit;

        // Main SQL
        if (!empty($search)) {
            $sql = "
                SELECT * FROM registered_household
                WHERE household_number LIKE '%$search_safe%'
                OR head_of_family LIKE '%$search_safe%'
                OR address LIKE '%$search_safe%'
                OR household_members LIKE '%$search_safe%'
                ORDER BY id DESC
                LIMIT $limit OFFSET $offset
            ";
        } else {
            $sql = "SELECT * FROM registered_household ORDER BY id DESC LIMIT $limit OFFSET $offset";
        }

        $result = mysqli_query($conn, $sql);

        if ($result && mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
                $membersArray = array_filter(array_map('trim', explode(',', $row['household_members'])));
                $membersCount = count($membersArray);
                $membersData = htmlspecialchars($row['household_members'], ENT_QUOTES);
                $displayMembers = $membersCount . " members";

                echo "
                <tr>
                    <td>{$row['household_number']}</td>
                    <td>{$row['head_of_family']}</td>
                    <td>{$row['address']}</td>
                    <td><span class='member-count' data-members='{$membersData}'>{$displayMembers}</span></td>
                    <td>{$row['rfid']}</td>
                    <td>
                        <button class='edit'
                            data-id='{$row['id']}'
                            data-number='{$row['household_number']}'
                            data-head='{$row['head_of_family']}'
                            data-address='{$row['address']}'
                            data-members='{$membersData}'
                            data-rfid='{$row['rfid']}'>
                            <i class='fa-solid fa-pen-to-square'></i>
                        </button>
                        <button class='delete' data-id='{$row['id']}'>
                            <i class='fa-solid fa-trash'></i>
                        </button>
                    </td>
                </tr>";
            }
        } else {
            echo "<tr><td colspan='6' style='text-align:center; padding:20px; color:#777;'>No households found.</td></tr>";
        }

        mysqli_close($conn);
        ?>
        </tbody>
    </table>
</div>

<!-- PAGINATION -->
<?php if ($total_records > $limit): ?>
<div class="pagination">
    <?php
        $max_pages_to_show = 5;
        $start = max(1, $page - 2);
        $end = min($total_pages, $start + $max_pages_to_show - 1);
        if ($end - $start < $max_pages_to_show - 1) {
            $start = max(1, $end - $max_pages_to_show + 1);
        }
    ?>

    <!-- Previous -->
    <?php if ($page > 1): ?>
        <a href="?search=<?= $search ?>&page=<?= $page - 1 ?>">&lt;</a>
    <?php else: ?>
        <span class="disabled">&lt;</span>
    <?php endif; ?>

    <!-- Page numbers -->
    <?php for ($i = $start; $i <= $end; $i++): ?>
        <a href="?search=<?= $search ?>&page=<?= $i ?>" class="<?= $i == $page ? 'active' : '' ?>"><?= $i ?></a>
    <?php endfor; ?>

    <!-- Next -->
    <?php if ($page < $total_pages): ?>
        <a href="?search=<?= $search ?>&page=<?= $page + 1 ?>">&gt;</a>
    <?php else: ?>
        <span class="disabled">&gt;</span>
    <?php endif; ?>
</div>
<?php endif; ?>

    </div>
</main>

<!-- MODAL -->
<div class="modal-overlay" id="modalOverlay"></div>

<div class="resident-modal" id="residentModal">
    <div class="resident-modal-content">

        <!-- HEADER -->
        <div class="modal-header">
            <div class="modal-title">
                <i class="fa-solid fa-house" id="modalIcon"></i>
                <h3 id="modalTitle">Add New Household</h3>
            </div>
            <span class="close-btn" id="closeModal">&times;</span>
        </div>

        <!-- BODY -->
         
        
<form id="addResidentForm">
    <input type="hidden" id="resident_id" name="resident_id">

    <!-- ROW 1 -->
    <div class="form-row two-col">
        <div class="form-group">
            <label>Household Number</label>
            <input type="text" name="household_number" readonly style="background-color: #f8fafc; cursor: not-allowed; color: #64748b;">
        </div>

        <div class="form-group head-picker">
            <label>Head of Family</label>
            <input type="text" name="head_of_family" id="headInput"
                   autocomplete="off" required>
        </div>
    </div>

    <!-- MINI TABLE OVERLAY (SHARED PICKER) -->
    <div class="resident-picker" id="residentPicker">
        <table>
            <thead>
                <tr>
                    <th colspan="3">
                        <input type="text" id="memberSearch"
                               placeholder="Search resident...">
                    </th>
                </tr>
                <tr>
                    <th>Name</th>
                    <th>Address</th>
                    <th style="width:90px;">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $conn = mysqli_connect("localhost", "root", "Password", "barangay_db");
                $res = mysqli_query($conn, "
                    SELECT first_name, middle_name, last_name, address
                    FROM registered_resi
                    ORDER BY last_name
                ");

                while ($r = mysqli_fetch_assoc($res)) {
                    $fullName = trim(
                        $r['first_name'] . ' ' .
                        $r['middle_name'] . ' ' .
                        $r['last_name']
                    );
                    $address = htmlspecialchars($r['address']);

                    echo "
                    <tr>
                        <td>{$fullName}</td>
                        <td>{$address}</td>
                        <td>
                            <button type='button'
                                class='picker-action'
                                data-name=\"{$fullName}\"
                                data-address=\"{$address}\">
                                Select
                            </button>
                        </td>
                    </tr>";
                }
                mysqli_close($conn);
                ?>
            </tbody>
        </table>
    </div>

    <!-- ROW 2 -->
    <div class="form-row">
        <div class="form-group">
            <label>Address</label>
            <input type="text" name="address" required>
        </div>
    </div>

    <!-- ROW 3 -->
    <div class="form-group members-picker">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
            <label style="margin: 0;">Household Members</label>
            <button type="button" id="openMembersPickerBtn" class="add-member-btn">
                <i class="fa-solid fa-plus"></i> Add Member
            </button>
        </div>
        
        <input type="hidden" name="household_members" id="membersInput">

        <div class="members-table-container">
            <table class="inner-members-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th style="width: 70px; text-align: center;">Action</th>
                    </tr>
                </thead>
                <tbody id="membersTableBody">
                    <tr><td colspan="2" style="text-align: center; color: #94a3b8; font-style: italic;">No members added yet.</td></tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- ROW 4 -->
    <div class="form-group">
        <label>Assigned RFID</label>

        <div class="rfid-wrapper">
            <input type="text" name="rfid" id="rfidInput"
                placeholder="Enter RFID Number">

            <button type="button" class="rfid-btn" id="scanRfidBtn">
                <i class="fa-solid fa-id-card"></i>
            </button>
        </div>
    </div>



    <button type="submit" id="saveHouseholdBtn" class="save-btn">
        Save Household
    </button>
</form>

    </div>
</div>


<div class="rfid-overlay" id="rfidOverlay">
    <div class="rfid-box">
        <i class="fa-solid fa-id-card"></i>
        <h3>Waiting for RFID Scan</h3>
        <p>Please tap the RFID card</p>
        <button id="cancelRfid">Cancel</button>
    </div>
</div>

<div class="members-overlay" id="membersOverlay">
    <div class="members-card">
        <div class="members-header">
            <h3>Household Members</h3>
            <span id="closeMembersOverlay">&times;</span>
        </div>
        <div class="members-body" id="membersBody">
            <!-- Members list will appear here -->
        </div>
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

<script src="assets/js/household-managementss.js"></script>
<script src="includes/sidebarss.js" defer></script><?php include 'includes/sidebar.php'; ?>

<script src="rfid/rfid_scanner.js"></script>

<script>
document.addEventListener("DOMContentLoaded", () => {
    const scanBtn = document.getElementById("scanRfidBtn");
    const rfidInput = document.getElementById("rfidInput");
    const rfidOverlay = document.getElementById("rfidOverlay");
    const cancelRfidBtn = document.getElementById("cancelRfid");

    // scan button click?
    if (scanBtn) {
        scanBtn.addEventListener("click", () => {
            // show overlay
            if (rfidOverlay) rfidOverlay.style.display = "flex";

            // !! if scanner is not waiting, prompt to activate
            if (typeof rfidPort === 'undefined' || !rfidPort) {
                // We pass 'assignRFIDToInput' as the callback function
                connectRFIDScanner(assignRFIDToInput, scanBtn);
            }
        });
    }

    // uid is console logged
    function assignRFIDToInput(scannedID) {
        console.log("Assigning RFID to Household:", scannedID);
        
        // uid to input box
        if (rfidInput) {
            rfidInput.value = scannedID;
        }
        
        // hide after
        if (rfidOverlay) {
            rfidOverlay.style.display = "none";
        }
    }

    // close
    if (cancelRfidBtn) {
        cancelRfidBtn.addEventListener("click", () => {
            if (rfidOverlay) rfidOverlay.style.display = "none";
        });
    }
});
</script>
</body>
</html>
