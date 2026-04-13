<?php
require_once __DIR__ . '/../config/db_connect.php';

$search = $_GET['search'] ?? '';
$status = $_GET['status'] ?? 'all';

$sql = "SELECT id, first_name, last_name, username, role, status, version FROM users WHERE 1=1";

if ($search !== '') {
    $search_safe = mysqli_real_escape_string($conn, $search);
    $sql .= " AND (
        first_name LIKE '%$search_safe%' OR
        last_name LIKE '%$search_safe%' OR
        username LIKE '%$search_safe%' OR
        role LIKE '%$search_safe%'
    )";
}

if ($status !== 'all') {
    $status_safe = mysqli_real_escape_string($conn, strtolower($status));
    $sql .= " AND LOWER(status) = '$status_safe'";
}

$sql .= " ORDER BY id DESC";

$result = mysqli_query($conn, $sql);

while ($row = mysqli_fetch_assoc($result)) {
    $fullName = htmlspecialchars($row['first_name'] . ' ' . $row['last_name']);
    $username = htmlspecialchars($row['username']);
    $role = htmlspecialchars($row['role']);
    $version = (int)$row['version'];
    
    $currentStatus = strtolower(trim($row['status'] ?? 'active'));
    $display_status = ($currentStatus === 'active') ? 'Active' : 'Inactive';
    $status_class = ($currentStatus === 'active') ? 'active' : 'inactive';

    echo "<tr>
        <td>{$fullName}</td>
        <td>{$username}</td>
        <td>{$role}</td>
        <td><span class='status {$status_class}'>" . $display_status . "</span></td>
        <td style='display:flex; gap:8px;'>";

    // Edit button
    echo "<button class='edit' data-id='{$row['id']}' data-name='$fullName' data-username='$username' data-role='$role' data-version='$version' title='Edit'>
            <i class='fa-solid fa-pen'></i>
          </button>";

    // Toggle button
    if ($currentStatus === 'active') {
        echo "<button class='deactivate' data-id='{$row['id']}' title='Deactivate User'>
                <i class='fa-solid fa-ban'></i>
              </button>";
    } else {
        echo "<button class='activate' data-id='{$row['id']}' title='Activate User'>
                <i class='fa-solid fa-check'></i>
              </button>";
    }

    echo "</td>
    </tr>";
}
?>
