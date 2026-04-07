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
    <title>Aid Program Setup</title>
    <link rel="stylesheet" href="../assets/css/aid-programs-setup.css">
    <link rel="stylesheet" href="../includes/sidebars.css">
    <link rel="stylesheet" href="../fontawesome/fontawesome/css/all.css">
    <style>
        /* New Dynamic Badges for the State Machine */
        .status-badge { padding: 4px 10px; border-radius: 6px; font-weight: bold; font-size: 12px; }
        .badge-scheduled { background-color: #e0f2fe; color: #0284c7; } /* Blue */
        .badge-ongoing { background-color: #dcfce7; color: #16a34a; }   /* Green */
        .badge-paused { background-color: #fef08a; color: #ca8a04; }    /* Yellow */
        .badge-completed { background-color: #f3f4f6; color: #4b5563; } /* Gray */
        .badge-archived { background-color: #fee2e2; color: #dc2626; }  /* Red */
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
    <?php
    $status = $_GET['status'] ?? 'Scheduled';
    $search = $_GET['search'] ?? '';
    ?>
    <div class="rp-card">

        <div class="rp-header">
            <div class="header-text">
                <h2>Aid Programs</h2>
                <p>Create and manage distribution programs</p>

                <div class="tabs-container">
                    <a href="?status=Scheduled&search=<?php echo urlencode($search); ?>" class="tab <?php echo ($status == 'Scheduled') ? 'active' : ''; ?>">Scheduled</a>
                    <a href="?status=Ongoing&search=<?php echo urlencode($search); ?>" class="tab <?php echo ($status == 'Ongoing') ? 'active' : ''; ?>">Ongoing</a>
                    <a href="?status=Paused&search=<?php echo urlencode($search); ?>" class="tab <?php echo ($status == 'Paused') ? 'active' : ''; ?>">Paused</a>
                    <a href="?status=Completed&search=<?php echo urlencode($search); ?>" class="tab <?php echo ($status == 'Completed') ? 'active' : ''; ?>">Completed</a>
                    <a href="?status=Archived&search=<?php echo urlencode($search); ?>" class="tab <?php echo ($status == 'Archived') ? 'active' : ''; ?>">Archived</a>
                    <a href="?status=all&search=<?php echo urlencode($search); ?>" class="tab <?php echo ($status == 'all') ? 'active' : ''; ?>">All</a>
                </div>
            </div>

            <div class="rp-actions">
                <form method="GET" style="display: flex; gap: 10px;">
                    <input type="text" name="search" placeholder="Search programs..." value="<?php echo htmlspecialchars($search); ?>">
                    <input type="hidden" name="status" value="<?php echo htmlspecialchars($status); ?>">
                </form>

                <button class="add-resident">
                    <i class="fa-solid fa-plus"></i> Add Program
                </button>
            </div>
        </div>

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
                        $searchEscaped = mysqli_real_escape_string($conn, trim($search));
                        $conditions = [];

                        if ($searchEscaped !== "") {
                            $conditions[] = "(program_name LIKE '%$searchEscaped%' OR aid_type LIKE '%$searchEscaped%' OR date_scheduled LIKE '%$searchEscaped%')";
                        }

                        if ($status !== "all") {
                            $statusEscaped = mysqli_real_escape_string($conn, $status);
                            $conditions[] = "status = '$statusEscaped'";
                        }

                        $whereSQL = !empty($conditions) ? "WHERE " . implode(" AND ", $conditions) : "";
                        $sql = "SELECT * FROM aid_program $whereSQL ORDER BY id DESC";
                        $result = mysqli_query($conn, $sql);

                        if ($result && mysqli_num_rows($result) > 0) {
                            while ($row = mysqli_fetch_assoc($result)) {
                                // Dynamic Badge Class mapping
                                $badgeClass = 'badge-' . strtolower($row['status']);

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
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<div class="modal-overlay" id="modalOverlay"></div>

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
                    <input type="text" name="aid_type" placeholder="e.g., Food Packs, Cash" required style="text-transform: capitalize;">
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
                        <option value="Scheduled">Scheduled</option>
                        <option value="Ongoing">Ongoing</option>
                        <option value="Paused">Paused</option>
                        <option value="Completed">Completed</option>
                        <option value="Archived">Archived</option>
                    </select>
                </div>
            </div>

            <button type="submit" style="margin-top: 20px;">Save Program</button>
        </form>
    </div>
</div>

<link rel="stylesheet" href="../assets/popup/popup.css">
<div id="popup-container"></div>
<script>
fetch("../assets/popup/popup.html")
    .then(res => res.text())
    .then(html => {
        document.getElementById("popup-container").innerHTML = html;
        const popupScript = document.createElement("script");
        popupScript.src = "../assets/popup/popup.js";
        popupScript.onload = () => {
            const pageScript = document.createElement("script");
            pageScript.src = "../assets/js/aid-programs-setup.js";
            pageScript.onload = () => {
                if (typeof window.initAidPrograms === 'function') window.initAidPrograms();
            };
            document.body.appendChild(pageScript);
        };
        document.body.appendChild(popupScript);
    })
    .catch(err => console.error('Popup HTML load error:', err));
</script>
<script src="../includes/sidebarss.js?v=2" defer></script>
<?php include '../includes/sidebar.php'; ?>
</body>
</html>
