<?php
$conn = mysqli_connect("localhost", "root", "Password", "barangay_db");

$search = $_GET['search'] ?? '';

$sql = "SELECT * FROM registered_resi";

if ($search !== '') {
    $search_safe = mysqli_real_escape_string($conn, $search);

    $sql .= " WHERE 
        first_name LIKE '%$search_safe%' OR
        middle_name LIKE '%$search_safe%' OR
        last_name LIKE '%$search_safe%' OR
        address LIKE '%$search_safe%' OR
        birthdate LIKE '%$search_safe%' OR
        gender LIKE '%$search_safe%' OR
        civil_status LIKE '%$search_safe%' OR
        occupation LIKE '%$search_safe%' OR
        voters_registration_no LIKE '%$search_safe%' OR
        contact LIKE '%$search_safe%'";
}

$sql .= " ORDER BY id DESC";

$result = mysqli_query($conn, $sql);

while ($row = mysqli_fetch_assoc($result)) {

    $voterDisplay = ($row['voters_registration_no'] === "Not Registered")
        ? "<span class='not-registered'>Not Registered</span>"
        : htmlspecialchars($row['voters_registration_no']);

    $contactDisplay = empty($row['contact']) || $row['contact'] === "N/A"
        ? "<span class='not-registered'>N/A</span>"
        : htmlspecialchars($row['contact']);

    echo "<tr>
        <td>{$row['first_name']} {$row['middle_name']} {$row['last_name']}</td>
        <td>{$row['address']}</td>
        <td>{$row['birthdate']}</td>
        <td>{$row['gender']}</td>
        <td>{$row['civil_status']}</td>
        <td>{$row['occupation']}</td>
        <td>$voterDisplay</td>
        <td>$contactDisplay</td>
        <td>
            <button class='edit' data-id='{$row['id']}'>
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