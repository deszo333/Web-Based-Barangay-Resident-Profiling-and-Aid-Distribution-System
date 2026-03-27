<?php
$conn = mysqli_connect("localhost", "root", "Password", "barangay_db");

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

$program = $_POST['program_name'] ?? '';
$aid_type = $_POST['aid_type'] ?? '';

$sql = "SELECT program_name, beneficiaries FROM aid_program WHERE 1=1";

if (!empty($program)) {
    $sql .= " AND program_name = '$program'";
}

if (!empty($aid_type) && $aid_type !== "All Programs") {
    $sql .= " AND aid_type = '$aid_type'";
}

$sql .= " ORDER BY id DESC";

$result = mysqli_query($conn, $sql);

if ($result && mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {

        // SAFE numeric values
        $beneficiaries = intval($row['beneficiaries']);

        // TEMP: replace with real distributed data later
        $distributed = intval($row['beneficiaries']); 

        $remaining = $beneficiaries - $distributed;

        echo "
        <div class='program-item expanded-view'>

            <div class='program-name'>{$row['program_name']}</div>

            <div class='program-details'>
                <span><strong>Total:</strong> $beneficiaries</span>
                <span><strong>Distributed:</strong> $distributed</span>
            </div>

            <!-- FULL DETAILS (VISIBLE AFTER SEARCH) -->
            <div class='program-expanded'>
                <p><strong>Remaining:</strong> $remaining</p>

                <canvas class='mini-chart'
                    data-beneficiaries='$beneficiaries'
                    data-distributed='$distributed'>
                </canvas>
            </div>

        </div>";
    }
} else {
    echo "<p style='text-align:center; color:#777;'>No matching data found.</p>";
}

mysqli_close($conn);
?>