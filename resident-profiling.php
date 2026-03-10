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
    <title>Resident Profiling</title>
    <link rel="stylesheet" href="assets/css/resident-profiling.css">
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
                <h2>Registered Residents</h2>
                <p>View and manage all residents in the barangay</p>
            </div>
            
            <div class="rp-actions">
                    <input 
                        type="text" 
                        name="search" 
                        id="searchInput"
                        placeholder="Search residents..."
                        value="<?php echo $_GET['search'] ?? ''; ?>">
                <button class="add-resident"><i class="fa-solid fa-plus"></i> Add Resident</button>
            </div>
        </div>

        <!-- LOWER PART: TABLE -->
        <div class="rp-table">
            <table>
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Address</th>
                        <th>Birthdate</th>
                        <th>Gender</th>
                        <th>Civil Status</th>
                        <th>Occupation</th>
                        <th>Voters Registration Number</th>
                        <th>Contact</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody id="residentTableBody">
                <?php
                $conn = mysqli_connect("localhost", "root", "Password", "barangay_db");
                $search = $_GET['search'] ?? '';

                // PAGINATION VARIABLES
                $limit = 8; // records per page
                $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
                $offset = ($page - 1) * $limit;

                // COUNT TOTAL RECORDS
                if ($search !== '') {
                    $search_safe = mysqli_real_escape_string($conn, $search);
                    $count_sql = "
                        SELECT COUNT(*) as total FROM registered_resi
                        WHERE first_name LIKE '%$search_safe%'
                        OR middle_name LIKE '%$search_safe%'
                        OR last_name LIKE '%$search_safe%'
                        OR address LIKE '%$search_safe%'
                        OR birthdate LIKE '%$search_safe%'
                        OR gender LIKE '%$search_safe%'
                        OR civil_status LIKE '%$search_safe%'
                        OR occupation LIKE '%$search_safe%'
                        OR voters_registration_no LIKE '%$search_safe%'
                        OR contact LIKE '%$search_safe%'
                    ";
                } else {
                    $count_sql = "SELECT COUNT(*) as total FROM registered_resi";
                }
                $count_result = mysqli_query($conn, $count_sql);
                $total_records = mysqli_fetch_assoc($count_result)['total'];
                $total_pages = ceil($total_records / $limit);

                // MODIFY ORIGINAL SQL TO INCLUDE LIMIT
                if ($search !== '') {
                    $sql = "
                        SELECT * FROM registered_resi
                        WHERE first_name LIKE '%$search_safe%'
                        OR middle_name LIKE '%$search_safe%'
                        OR last_name LIKE '%$search_safe%'
                        OR address LIKE '%$search_safe%'
                        OR birthdate LIKE '%$search_safe%'
                        OR gender LIKE '%$search_safe%'
                        OR civil_status LIKE '%$search_safe%'
                        OR occupation LIKE '%$search_safe%'
                        OR voters_registration_no LIKE '%$search_safe%'
                        OR contact LIKE '%$search_safe%'
                        ORDER BY id DESC
                        LIMIT $limit OFFSET $offset
                    ";
                } else {
                    $sql = "SELECT * FROM registered_resi ORDER BY id DESC LIMIT $limit OFFSET $offset";
                }

                $result = mysqli_query($conn, $sql);

                while ($row = mysqli_fetch_assoc($result)) {
                    // Voter registration display
                    $voterDisplay = ($row['voters_registration_no'] === "Not Registered")
                        ? "<span class='not-registered'>Not Registered</span>"
                        : htmlspecialchars($row['voters_registration_no']);

                    // Contact display
                    $contactDisplay = empty($row['contact']) || $row['contact'] === "N/A"
                        ? "<span class='not-registered'>N/A</span>"
                        : htmlspecialchars($row['contact']);

                    // Custom tooltip for age
                    $birthdate = htmlspecialchars($row['birthdate']);
                    $age = htmlspecialchars($row['age']);
                    $birthdateTooltip = "<span class='birthdate-tooltip' data-age='Age: $age'>$birthdate</span>";

                    echo "<tr>
                        <td>{$row['first_name']} {$row['middle_name']} {$row['last_name']}</td>
                        <td>{$row['address']}</td>
                        <td>$birthdateTooltip</td>
                        <td>{$row['gender']}</td>
                        <td>{$row['civil_status']}</td>
                        <td>{$row['occupation']}</td>
                        <td>$voterDisplay</td>
                        <td>$contactDisplay</td>
                        <td>
                            <button class='edit'
                                data-id='{$row['id']}'
                                data-first='{$row['first_name']}'
                                data-middle='{$row['middle_name']}'
                                data-last='{$row['last_name']}'
                                data-address='{$row['address']}'
                                data-birthdate='{$row['birthdate']}'
                                data-age='{$row['age']}'
                                data-gender='{$row['gender']}'
                                data-civil='{$row['civil_status']}'
                                data-occupation='{$row['occupation']}'
                                data-voters='{$row['voters_registration_no']}'
                                data-contact='{$row['contact']}'>
                                <i class='fa-solid fa-pen-to-square'></i>
                            </button>
                            <button class='delete' data-id='{$row['id']}'>
                                <i class='fa-solid fa-trash'></i>
                            </button>
                        </td>
                    </tr>";
                }

                mysqli_close($conn);
                ?>

                <tr id="noResultRow" style="display:none;">
                    <td colspan="9" style="text-align:center; padding:20px; color:#777;">
                        No matching residents found
                    </td>
                </tr>

                </tbody>
            </table>
        </div>

       <!-- PAGINATION -->
<?php if ($total_records >= 8): ?>
<div class="pagination">

    <?php
        $max_pages_to_show = 5;

        // Sliding window
        $start = max(1, $page - 2);
        $end = min($total_pages, $start + $max_pages_to_show - 1);

        if ($end - $start < $max_pages_to_show - 1) {
            $start = max(1, $end - $max_pages_to_show + 1);
        }
    ?>

    <!-- PREVIOUS -->
    <?php if ($page > 1): ?>
        <a href="?search=<?= $search ?>&page=<?= $page - 1 ?>">&lt;</a>
    <?php else: ?>
        <span class="disabled">&lt;</span>
    <?php endif; ?>

    <!-- PAGE NUMBERS -->
    <?php for ($i = $start; $i <= $end; $i++): ?>
        <a href="?search=<?= $search ?>&page=<?= $i ?>" class="<?= $i == $page ? 'active' : '' ?>">
            <?= $i ?>
        </a>
    <?php endfor; ?>

    <!-- NEXT -->
    <?php if ($page < $total_pages): ?>
        <a href="?search=<?= $search ?>&page=<?= $page + 1 ?>">&gt;</a>
    <?php else: ?>
        <span class="disabled">&gt;</span>
    <?php endif; ?>

</div>
<?php endif; ?>




    </div>

</main>


<div class="modal-overlay" id="modalOverlay"></div>

<!-- Add/Edit Resident Modal -->
<div class="resident-modal" id="residentModal">
    <div class="resident-modal-content">
        <div class="modal-header">
            <div class="modal-title">
                <i class="fa-solid fa-house" id="modalIcon"></i>
                <h3 id="modalTitle">Add New Household</h3>
            </div>
            <span class="close-btn" id="closeModal">&times;</span>
        </div>
        
        <form id="addResidentForm" class="resident-form-grid">
            <input type="hidden" name="resident_id" id="resident_id">

            <!-- ROW 1 -->
            <div class="form-row three">
                <div class="form-field">
                    <label>First Name</label>
                    <input type="text" name="first_name" placeholder="First Name" required>
                </div>

                <div class="form-field">
                    <label>Middle Name</label>
                    <input type="text" name="middle_name" placeholder="Middle Name">
                </div>

                <div class="form-field">
                    <label>Last Name</label>
                    <input type="text" name="last_name" placeholder="Last Name" required>
                </div>
            </div>

            <!-- ROW 2 -->
            <div class="form-row one">
                <div class="form-field">
                <label>Address</label>
                <input type="text" name="address" placeholder="Address">
                </div>
            </div>

            <div class="form-row three">
                    <div class="form-field">
                    <label>Birthdate</label>
                    <input type="date" name="birthdate" placeholder="Birthdate">
                    </div>

                    <div class="form-field">
                    <label>Age</label>
                    <input type="number" name="age" placeholder="Age" readonly>
                    </div>

                    <div class="form-field">
                    <label>Gender</label>
                    <select name="gender" required>
                        <option value="" disabled selected>Select Gender</option>
                        <option value="Male">Male</option>
                        <option value="Female">Female</option>
                    </select>
                    </div>          
            </div>

            <div class="form-row one">
                <div class="form-field">
                    <label>Civil Status</label>
                    <select name="civil_status" required>
                        <option value="" disabled selected>Select Civil Status</option>
                        <option value="Single">Single</option>
                        <option value="Married">Married</option>
                        <option value="Widowed">Widowed</option>
                        <option value="Divorced">Divorced</option>
                    </select>
                    </div>
            </div>

            <!-- ROW 4 -->
            <div class="form-row three">
                <div class="form-field">
                <label>Occupation</label>
                <input type="text" name="occupation" placeholder="Occupation">
                </div>
                
                <div class="form-field">
                <label>Contact</label>
                <input type="tel" name="contact" placeholder="Contact Number" maxlength="11" inputmode="numeric">
                </div>

                <div class="form-field">
                <label>Voters Registration Number</label>
                <input type="text" name="voters_registration_no" placeholder="Voters Registration Number">
                </div>
            </div>

            <button type="submit">Save Resident</button>
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


<script src="assets/js/resident-profilingss.js"></script>
<script src="includes/sidebarss.js" defer></script><?php include 'includes/sidebar.php'; ?>

</body>
</html>
