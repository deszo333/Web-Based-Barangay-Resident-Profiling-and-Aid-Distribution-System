<?php
$conn = mysqli_connect("localhost", "root", "Password", "barangay_db");

$search = $_GET['search'] ?? '';
$search_safe = mysqli_real_escape_string($conn, $search);

$sql = "
SELECT * FROM registered_household
WHERE household_number LIKE '%$search_safe%'
OR head_of_family LIKE '%$search_safe%'
OR address LIKE '%$search_safe%'
OR household_members LIKE '%$search_safe%'
ORDER BY id DESC
";

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

    echo "<tr>
            <td colspan='6' style='text-align:center; padding:20px; color:#777;'>
                No households found.
            </td>
          </tr>";
}

mysqli_close($conn);
?>