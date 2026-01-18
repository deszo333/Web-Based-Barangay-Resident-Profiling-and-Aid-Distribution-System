<?php
session_start();

$backLink = "login.php"; // default fallback
if (isset($_SESSION['role'])) {
    if ($_SESSION['role'] === 'admin') {
        $backLink = "admin-dashboard.php";
    } elseif ($_SESSION['role'] === 'staff') {
        $backLink = "staff-dashboard.html";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Household Management</title>

    <link rel="stylesheet" href="assets/css/household-management.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"/>
</head>
<body>

<!-- NAVBAR -->
<nav class="rp-navbar">
    <a href="<?php echo $backLink; ?>" class="back-btn">
        <i class="fa-solid fa-arrow-left"></i>
    </a>

    <div class="nav-text">
        <span class="page-title">Household Management</span>
        <p>Group residents into households</p>
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
                <form method="GET" style="display:inline;"> 
                    <input type="text" name="search" placeholder="Search residents..." value="<?php echo $_GET['search'] ?? ''; ?>"> 
                </form>
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
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                // Connect to database
                $conn = mysqli_connect("localhost", "root", "Password", "barangay_db");
                if (!$conn) {
                    die("Connection failed: " . mysqli_connect_error());
                }

                // Search
                $search = $_GET['search'] ?? '';
                if (!empty($search)) {
                    $search = mysqli_real_escape_string($conn, $search);
                    $sql = "SELECT * FROM registered_household
                            WHERE household_number LIKE '%$search%'
                            OR head_of_family LIKE '%$search%'
                            OR address LIKE '%$search%'
                            OR household_members LIKE '%$search%'
                            ORDER BY id DESC";
                } else {
                    $sql = "SELECT * FROM registered_household ORDER BY id DESC";
                }

                $result = mysqli_query($conn, $sql);

                if ($result && mysqli_num_rows($result) > 0) {
                    while ($row = mysqli_fetch_assoc($result)) {

                        $membersArray = array_filter(array_map('trim', explode(',', $row['household_members'])));
                        $membersCount = count($membersArray);

                        $membersData = htmlspecialchars($row['household_members'], ENT_QUOTES);

                        // display text
                        $displayMembers = $membersCount . " members";

                        echo "
                        <tr>
                            <td>{$row['household_number']}</td>
                            <td>{$row['head_of_family']}</td>
                            <td>{$row['address']}</td>
                            <td>
                                <span class='member-count' data-members='{$membersData}'>
                                    {$displayMembers}
                                </span>
                            </td>
                            <td>
                                <button class='edit'
                                    data-id='{$row['id']}'
                                    data-number='{$row['household_number']}'
                                    data-head='{$row['head_of_family']}'
                                    data-address='{$row['address']}'
                                    data-members='{$membersData}'>
                                    <i class='fa-solid fa-pen-to-square'></i>
                                </button>
                                <button class='delete' data-id='{$row['id']}'>
                                    <i class='fa-solid fa-trash'></i>
                                </button>
                            </td>
                        </tr>";
                    }
                } else {
                    echo "<tr><td colspan='5'>No households found.</td></tr>";
                }

                mysqli_close($conn);
                ?>
                </tbody>
            </table>
        </div>

    </div>
</main>

<!-- MODAL -->
<div class="modal-overlay" id="modalOverlay"></div>

<div class="resident-modal" id="residentModal">
    <div class="resident-modal-content">
        <span class="close-btn" id="closeModal">&times;</span>
        <h3>Add / Edit Resident</h3>

        <form id="addResidentForm">
            <input type="hidden" id="resident_id" name="resident_id">

            <label>Household Number</label>
            <input type="text" name="household_number" required>

            <label>Head of Family</label>
            <input type="text" name="head_of_family" required>

            <label>Address</label>
            <input type="text" name="address" required>

            <label>Household Members</label>
            <input type="text" name="household_members" required placeholder="Comma-separated list of members">
            
            <p>Separate multiple members with commas (e.g., Juan Dela Cruz, Maria Dela Cruz)</p>

            <button type="submit">Add Household</button>
        </form>
    </div>
</div>


<div id="members-toast" class="toast">
    <p id="members-text"></p>
    <button id="close-toast">&times;</button>
</div>

<script src="assets/js/household-management.js"></script>
</body>
</html>
