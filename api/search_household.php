<?php
require_once __DIR__ . '/../config/db_connect.php';

$search = $_GET['search'] ?? '';
$search_safe = mysqli_real_escape_string($conn, $search);

$sql = "
    SELECT h.id, h.version, h.household_number, h.address, h.head_of_family_id,
           CONCAT(head.first_name, ' ', head.last_name) AS head_name,
           rt.rfid_number AS rfid,
           GROUP_CONCAT(CONCAT(m.first_name, ' ', m.last_name) SEPARATOR ', ') AS members_list,
           GROUP_CONCAT(m.id SEPARATOR ',') AS members_ids
    FROM registered_household h
    LEFT JOIN registered_resi head ON h.head_of_family_id = head.id
    LEFT JOIN registered_resi m ON m.household_id = h.id
    LEFT JOIN rfid_tags rt ON rt.household_id = h.id AND rt.status = 'Active'
    WHERE (h.household_number LIKE '%$search_safe%'
       OR CONCAT(head.first_name, ' ', head.last_name) LIKE '%$search_safe%'
       OR h.address LIKE '%$search_safe%')
       AND h.is_archived = 0
    GROUP BY h.id ORDER BY h.id DESC
";

$result = mysqli_query($conn, $sql);

if ($result && mysqli_num_rows($result) > 0) {

    while ($row = mysqli_fetch_assoc($result)) {

        $membersArray = array_filter(array_map('trim', explode(',', $row['members_ids'])));
        $membersCount = count($membersArray);
        $membersList = htmlspecialchars($row['members_list'], ENT_QUOTES);
        $membersIds = htmlspecialchars($row['members_ids'], ENT_QUOTES);
        $displayMembers = $membersCount . " members";

        echo "
        <tr>
            <td>{$row['household_number']}</td>
            <td>{$row['head_name']}</td>
            <td>{$row['address']}</td>
            <td><span class='member-count' data-members='{$membersList}'>{$displayMembers}</span></td>
            <td>" . ($row['rfid'] ? htmlspecialchars($row['rfid']) : 'N/A') . "</td>
            <td>
                <button class='edit'
                    data-id='{$row['id']}'
                    data-version='{$row['version']}'
                    data-number='{$row['household_number']}'
                    data-headname='{$row['head_name']}'
                    data-headid='{$row['head_of_family_id']}'
                    data-address='{$row['address']}'
                    data-membernames='{$membersList}'
                    data-memberids='{$membersIds}'
                    data-rfid='" . ($row['rfid'] ? htmlspecialchars($row['rfid']) : '') . "'>
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