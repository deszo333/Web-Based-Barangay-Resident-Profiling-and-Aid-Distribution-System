<?php
require_once __DIR__ . '/../config/auth_check.php';
require_once __DIR__ . '/../config/db_connect.php';

// Admin-only page — redirect staff away
if (($_SESSION['role'] ?? '') !== 'admin') {
    header("Location: staff-dashboard.php");
    exit();
}

$backLink = "../pages/admin-dashboard.php";
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
    <title>Account Management</title>
    <link rel="stylesheet" href="../assets/css/account-man.css">
    <link rel="stylesheet" href="../includes/sidebars.css">
    <link rel="stylesheet" href="../fontawesome/fontawesome/css/all.css">
</head>
<body>

<!-- NAVBAR -->
<nav class="rp-navbar" style="display: flex; justify-content: space-between; align-items: center; padding-right: 30px;">
    <div style="display: flex; align-items: center; gap: 15px;">
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
    </div>

    <div style="display: flex; align-items: center; gap: 10px; color: white;">
        <span style="font-weight: 600; font-size: 15px;">Hello, <?php echo htmlspecialchars($currentName); ?></span>
        <img src="../assets/images/profiles.png" alt="User" style="width: 40px; height: 40px; border-radius: 50%;">
    </div>
</nav>

<!-- MAIN CONTENT -->
<main class="rp-dashboard">

<?php
$status = strtolower($_GET['status'] ?? 'active');
$search = $_GET['search'] ?? '';
?>

    <div class="rp-card">

        <!-- UPPER PART -->
        <div class="rp-header">
            <div class="header-text">
                <h2>User Account</h2>
                <p>Create and manage user accounts</p>
                <div class="tabs-container">
                    <a href="?status=active&search=<?php echo urlencode($_GET['search'] ?? ''); ?>" 
                    class="tab <?php echo ($status == 'active') ? 'active' : ''; ?>">
                    Active
                    </a>
                    <a href="?status=inactive&search=<?php echo urlencode($_GET['search'] ?? ''); ?>" 
                    class="tab <?php echo ($status == 'inactive') ? 'active' : ''; ?>">
                    Inactive
                    </a>
                    <a href="?status=all&search=<?php echo urlencode($_GET['search'] ?? ''); ?>" 
                    class="tab <?php echo ($status == 'all') ? 'active' : ''; ?>">
                    All
                    </a>
                </div>
            </div>
            
            <div class="rp-actions" style="display: flex; gap: 10px; align-items: center;">
                <form method="GET" style="display:flex; gap:10px; margin:0;">
                    <input type="text" name="search" id="searchInput" placeholder="Search users..." value="<?php echo htmlspecialchars($search); ?>">
                    <input type="hidden" name="status" value="<?php echo htmlspecialchars($status); ?>">
                </form>
                <button id="openAddAccountBtn" style="background-color: #16a34a; color: white; padding: 10px 16px; border: none; border-radius: 6px; cursor: pointer; font-weight: 600; display: flex; gap: 8px; align-items: center; white-space: nowrap;">
                    <i class="fa-solid fa-plus"></i> Add Account
                </button>
            </div>
        </div>

        <!-- LOWER PART: TABLE -->
        <div class="rp-table">
            <table>
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Username</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>

                <tbody id="residentTableBody">

<?php
$search = $_GET['search'] ?? '';
$status = strtolower($_GET['status'] ?? 'active');

$limit = 8;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1; 
$offset = ($page - 1) * $limit;

// =====================
// WHERE CONDITIONS
// =====================
$conditions = [];

if ($search !== '') {
    $search_safe = mysqli_real_escape_string($conn, $search);

    $conditions[] = "(first_name LIKE '%$search_safe%'
                    OR last_name LIKE '%$search_safe%'
                    OR username LIKE '%$search_safe%'
                    OR role LIKE '%$search_safe%')";
}

if ($status !== 'all') {
    $status_safe = mysqli_real_escape_string($conn, strtolower($status));
    $conditions[] = "status = '$status_safe'";
}

$whereSQL = '';
if (!empty($conditions)) {
    $whereSQL = "WHERE " . implode(" AND ", $conditions);
}

// =====================
// COUNT QUERY
// =====================
$count_sql = "SELECT COUNT(*) as total FROM users $whereSQL";
$count_result = mysqli_query($conn, $count_sql);
$total_records = mysqli_fetch_assoc($count_result)['total'];
$total_pages = ceil($total_records / $limit);

// =====================
// MAIN QUERY
// =====================
$sql = "
    SELECT * FROM users
    $whereSQL
    ORDER BY id DESC
    LIMIT $limit OFFSET $offset
";

$result = mysqli_query($conn, $sql);

while ($row = mysqli_fetch_assoc($result)) {

    $fullName = htmlspecialchars($row['first_name'] . ' ' . $row['last_name']);
    $username = htmlspecialchars($row['username']);
    $role = htmlspecialchars($row['role']);
    
    // Safely get status and force lowercase for logic checks
    $currentStatus = strtolower(trim($row['status'] ?? 'active'));
    $display_status = ($currentStatus === 'active') ? 'Active' : 'Inactive';
    $status_class = ($currentStatus === 'active') ? 'active' : 'inactive';

    echo "<tr>
        <td>{$fullName}</td>
        <td>{$username}</td>
        <td>{$role}</td>
        <td><span class='status {$status_class}'>".$display_status."</span></td>
        <td>";

    // Toggle button
    if ($currentStatus === 'active') {
        echo "<button class='deactivate' data-id='{$row['id']}'>
                Deactivate
              </button>";
    } else {
        echo "<button class='activate' data-id='{$row['id']}'>
                Activate
              </button>";
    }

    echo "</td>
    </tr>";
}
?>

                <tr id="noResultRow" style="display:none;">
                    <td colspan="5" style="text-align:center; padding:20px; color:#777;">
                        No matching users found
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
            $start = max(1, $page - 2);
            $end = min($total_pages, $start + $max_pages_to_show - 1);

            if ($end - $start < $max_pages_to_show - 1) {
                $start = max(1, $end - $max_pages_to_show + 1);
            }
            ?>

            <?php if ($page > 1): ?>
                <a href="?search=<?= $search ?>&status=<?= $status ?>&page=<?= $page - 1 ?>">&lt;</a>
            <?php else: ?>
                <span class="disabled">&lt;</span>
            <?php endif; ?>

            <?php for ($i = $start; $i <= $end; $i++): ?>
                <a href="?search=<?= $search ?>&status=<?= $status ?>&page=<?= $i ?>" class="<?= $i == $page ? 'active' : '' ?>">
                    <?= $i ?>
                </a>
            <?php endfor; ?>

            <?php if ($page < $total_pages): ?>
                <a href="?search=<?= $search ?>&status=<?= $status ?>&page=<?= $page + 1 ?>">&gt;</a>
            <?php else: ?>
                <span class="disabled">&gt;</span>
            <?php endif; ?>

        </div>
        <?php endif; ?>

    </div>

</main>

<!-- ADD ACCOUNT MODAL -->
<div id="addAccountModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.6); z-index:1000; align-items:center; justify-content:center;">
    <div style="background:white; padding:30px; border-radius:10px; width:400px; box-shadow:0 10px 25px rgba(0,0,0,0.2);">
        <h2 style="margin-top:0; margin-bottom:20px; color:#1e293b; font-size: 20px;">Add New Account</h2>
        <form id="addAccountForm" style="display:flex; flex-direction:column; gap:15px;">
            <div>
                <label style="font-weight:600; font-size:13px; color:#475569;">First Name <span style="color:red;">*</span></label>
                <input type="text" name="first_name" required style="width:100%; padding:10px; margin-top:5px; border:1px solid #cbd5e1; border-radius:6px; box-sizing:border-box;">
            </div>
            <div>
                <label style="font-weight:600; font-size:13px; color:#475569;">Last Name <span style="color:red;">*</span></label>
                <input type="text" name="last_name" required style="width:100%; padding:10px; margin-top:5px; border:1px solid #cbd5e1; border-radius:6px; box-sizing:border-box;">
            </div>
            <div>
                <label style="font-weight:600; font-size:13px; color:#475569;">Username <span style="color:red;">*</span></label>
                <input type="text" name="username" required autocomplete="new-password" style="width:100%; padding:10px; margin-top:5px; border:1px solid #cbd5e1; border-radius:6px; box-sizing:border-box;">
            </div>
            
            <div>
                <label style="font-weight:600; font-size:13px; color:#475569;">Password <span style="color:red;">*</span></label>
                <div style="position: relative; margin-top: 5px;">
                    <input type="password" name="password" id="addPassword" required autocomplete="new-password" style="width:100%; padding:10px; padding-right:40px; border:1px solid #cbd5e1; border-radius:6px; box-sizing:border-box;">
                    <span id="toggleAddPassword" style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); cursor: pointer; padding: 5px; color: #64748b;">
                        <i class="fa-solid fa-eye"></i>
                    </span>
                </div>
            </div>

            <div>
                <label style="font-weight:600; font-size:13px; color:#475569;">Confirm Password <span style="color:red;">*</span></label>
                <div style="position: relative; margin-top: 5px;">
                    <input type="password" name="confirm_password" id="addConfirmPassword" required autocomplete="new-password" style="width:100%; padding:10px; padding-right:40px; border:1px solid #cbd5e1; border-radius:6px; box-sizing:border-box;">
                    <span id="toggleAddConfirmPassword" style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); cursor: pointer; padding: 5px; color: #64748b;">
                        <i class="fa-solid fa-eye"></i>
                    </span>
                </div>
            </div>

            <div>
                <label style="font-weight:600; font-size:13px; color:#475569;">Role <span style="color:red;">*</span></label>
                <select name="role" required style="width:100%; padding:10px; margin-top:5px; border:1px solid #cbd5e1; border-radius:6px; box-sizing:border-box;">
                    <option value="staff">Staff</option>
                    <option value="admin">Admin</option>
                </select>
            </div>
            <div style="display:flex; justify-content:flex-end; gap:10px; margin-top:10px;">
                <button type="button" id="closeAddAccountBtn" style="padding:10px 16px; border:none; border-radius:6px; cursor:pointer; background:#e2e8f0; color:#475569; font-weight:600;">Cancel</button>
                <button type="submit" style="padding:10px 16px; border:none; border-radius:6px; cursor:pointer; background:#16a34a; color:white; font-weight:600; min-width:120px;">Save Account</button>
            </div>
        </form>
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
        const popupScript = document.createElement("script");
        popupScript.src = "../assets/popup/popup.js";
        popupScript.onload = () => {
            const amScript = document.createElement("script");
            amScript.src = "../assets/js/account-man.js?v=<?php echo time(); ?>";
            amScript.onload = () => {
                if (typeof window.initAccountMan === 'function') window.initAccountMan();
            };
            document.body.appendChild(amScript);
        };
        document.body.appendChild(popupScript);
    })
    .catch(err => console.error('Popup HTML load error:', err));
</script>

<script src="../includes/sidebarss.js" defer></script><?php include '../includes/sidebar.php'; ?>

</body>
</html>
