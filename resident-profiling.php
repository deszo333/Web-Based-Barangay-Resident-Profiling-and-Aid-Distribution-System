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
    <title>Resident Profiling</title>
    <link rel="stylesheet" href="assets/css/resident-profiling.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"/>
</head>
<body>

<!-- NAVBAR -->
<nav class="rp-navbar">
    <a href="<?php echo $backLink; ?>" class="back-btn">
        <i class="fa-solid fa-arrow-left"></i>
    </a>
    
    <div class="nav-text">
        <span class="page-title">Resident Profiling</span>
        <p>Manage resident and household information</p>
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
                <form method="GET" style="display:inline;">
                    <input type="text" name="search" placeholder="Search residents..." value="<?php echo $_GET['search'] ?? ''; ?>">
                </form>
                <button class="add-resident"><i class="fa-solid fa-plus"></i> Add Resident</button>
            </div>
        </div>

        <!-- LOWER PART: TABLE -->
        <div class="rp-table">
            <table>
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Age</th>
                        <th>Gender</th>
                        <th>Civil Status</th>
                        <th>Occupation</th>
                        <th>Contact</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $conn = mysqli_connect("localhost", "root", "Password", "barangay_db");
                    $search = $_GET['search'] ?? '';

                    if ($search !== '') {
                        $search = mysqli_real_escape_string($conn, $search);

                        $sql = "
                            SELECT * FROM registered_resi
                            WHERE
                                first_name LIKE '%$search%' OR
                                middle_name LIKE '%$search%' OR
                                last_name LIKE '%$search%' OR
                                age LIKE '%$search%' OR
                                gender LIKE '%$search%' OR
                                civil_status LIKE '%$search%' OR
                                occupation LIKE '%$search%' OR
                                contact LIKE '%$search%'
                            ORDER BY id DESC
                        ";
                    } else {
                        $sql = "SELECT * FROM registered_resi ORDER BY id DESC";
                    }

                    $result = mysqli_query($conn, $sql);

                    while ($row = mysqli_fetch_assoc($result)) {
                        echo "<tr>
                            <td>{$row['first_name']} {$row['middle_name']} {$row['last_name']}</td>
                            <td>{$row['age']}</td>
                            <td>{$row['gender']}</td>
                            <td>{$row['civil_status']}</td>
                            <td>{$row['occupation']}</td>
                            <td>{$row['contact']}</td>
                            <td>
                                <button class='edit'
                                    data-id='{$row['id']}'
                                    data-first='{$row['first_name']}'
                                    data-middle='{$row['middle_name']}'
                                    data-last='{$row['last_name']}'
                                    data-age='{$row['age']}'
                                    data-gender='{$row['gender']}'
                                    data-civil='{$row['civil_status']}'
                                    data-occupation='{$row['occupation']}'
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
                </tbody>
            </table>
        </div>

    </div>

</main>

<div class="modal-overlay" id="modalOverlay"></div>

<!-- Add/Edit Resident Modal -->
<div class="resident-modal" id="residentModal">
    <div class="resident-modal-content">
        <span class="close-btn" id="closeModal">&times;</span>
        <h3>Add / Edit Resident</h3>
        <form id="addResidentForm">
            <input type="hidden" name="resident_id" id="resident_id">

            <label>First Name</label>
            <input type="text" name="first_name" placeholder="First Name" required>

            <label>Middle Name</label>
            <input type="text" name="middle_name" placeholder="Middle Name">

            <label>Last Name</label>
            <input type="text" name="last_name" placeholder="Last Name" required>

            <label>Age</label>
            <input type="number" name="age" placeholder="Age" required>

            <label>Gender</label>
            <select name="gender" required>
                <option value="" disabled selected>Select Gender</option>
                <option value="Male">Male</option>
                <option value="Female">Female</option>
            </select>

            <label>Civil Status</label>
            <select name="civil_status" required>
                <option value="" disabled selected>Select Civil Status</option>
                <option value="Single">Single</option>
                <option value="Married">Married</option>
                <option value="Widowed">Widowed</option>
                <option value="Divorced">Divorced</option>
            </select>

            <label>Occupation</label>
            <input type="text" name="occupation" placeholder="Occupation">

            <label>Contact</label>
            <input type="tel" name="contact" placeholder="Contact Number" maxlength="11" pattern="[0-9]{11}" inputmode="numeric">

            <button type="submit">Save Resident</button>
        </form>
    </div>
</div>

<script src="assets/js/resident-profiling.js"></script>

</body>
</html>
