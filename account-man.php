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
    <title>Account Management</title>
    <link rel="stylesheet" href="assets/css/account-man.css">
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
                <h2>User Accounts</h2>
                <p>View and manage system users</p>
            </div>
            
            <div class="rp-actions">
                <input 
                    type="text" 
                    name="search" 
                    id="searchInput"
                    placeholder="Search users..."
                    value="<?php echo $_GET['search'] ?? ''; ?>">

                
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
$conn = mysqli_connect("localhost", "root", "Password", "barangay_db");
$search = $_GET['search'] ?? '';

$limit = 8;
$page = isset($_GET['page']) ? (int)$page : 1;
$offset = ($page - 1) * $limit;

// COUNT
if ($search !== '') {
    $search_safe = mysqli_real_escape_string($conn, $search);

    $count_sql = "
        SELECT COUNT(*) as total 
        FROM users
        WHERE first_name LIKE '%$search_safe%'
        OR last_name LIKE '%$search_safe%'
        OR username LIKE '%$search_safe%'
        OR role LIKE '%$search_safe%'
    ";
} else {
    $count_sql = "SELECT COUNT(*) as total FROM users";
}

$count_result = mysqli_query($conn, $count_sql);
$total_records = mysqli_fetch_assoc($count_result)['total'];
$total_pages = ceil($total_records / $limit);

// QUERY
if ($search !== '') {
    $sql = "
        SELECT * FROM users
        WHERE first_name LIKE '%$search_safe%'
        OR last_name LIKE '%$search_safe%'
        OR username LIKE '%$search_safe%'
        OR role LIKE '%$search_safe%'
        ORDER BY id DESC
        LIMIT $limit OFFSET $offset
    ";
} else {
    $sql = "SELECT * FROM users ORDER BY id DESC LIMIT $limit OFFSET $offset";
}

$result = mysqli_query($conn, $sql);

while ($row = mysqli_fetch_assoc($result)) {

    $fullName = htmlspecialchars($row['first_name'] . ' ' . $row['last_name']);
    $username = htmlspecialchars($row['username']);
    $role = htmlspecialchars($row['role']);
    $status = $row['status'] ?? 'active';

    $statusBadge = ($status === 'active')
        ? "<span style='color:green;font-weight:600;'>Active</span>"
        : "<span style='color:red;font-weight:600;'>Inactive</span>";

    echo "<tr>
        <td>{$fullName}</td>
        <td>{$username}</td>
        <td>{$role}</td>
        <td>{$statusBadge}</td>
        <td>";

    // ONLY TOGGLE BUTTON
    if ($status === 'active') {
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

mysqli_close($conn);
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
                <a href="?search=<?= $search ?>&page=<?= $page - 1 ?>">&lt;</a>
            <?php else: ?>
                <span class="disabled">&lt;</span>
            <?php endif; ?>

            <?php for ($i = $start; $i <= $end; $i++): ?>
                <a href="?search=<?= $search ?>&page=<?= $i ?>" class="<?= $i == $page ? 'active' : '' ?>">
                    <?= $i ?>
                </a>
            <?php endfor; ?>

            <?php if ($page < $total_pages): ?>
                <a href="?search=<?= $search ?>&page=<?= $page + 1 ?>">&gt;</a>
            <?php else: ?>
                <span class="disabled">&gt;</span>
            <?php endif; ?>

        </div>
        <?php endif; ?>

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


<script src="assets/js/account-man.js"></script>
<script src="includes/sidebarss.js" defer></script><?php include 'includes/sidebar.php'; ?>

</body>
</html>
