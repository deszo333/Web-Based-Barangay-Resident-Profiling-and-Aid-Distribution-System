<?php
session_start();
require 'auth_check.php';
$conn = mysqli_connect("localhost", "root", "Password", "barangay_db");

$program_name = isset($_POST['program_name']) ? mysqli_real_escape_string($conn, $_POST['program_name']) : '';
if (empty($program_name)) exit;

// get program id
$stmt = mysqli_prepare($conn, "SELECT id FROM aid_program WHERE program_name = ?");
mysqli_stmt_bind_param($stmt, "s", $program_name);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
$prog_id = ($row = mysqli_fetch_assoc($res)) ? $row['id'] : 0;
mysqli_stmt_close($stmt);

// query: claimed
$claimed = [];
$c_sql = "SELECT h.household_number, CONCAT(head.first_name, ' ', head.last_name) AS head_of_family, d.date_claimed 
          FROM distribution_logs d
          JOIN registered_household h ON d.household_id = h.id
          LEFT JOIN registered_resi head ON h.head_of_family_id = head.id
          WHERE d.program_id = $prog_id ORDER BY d.date_claimed DESC";
$c_res = mysqli_query($conn, $c_sql);
while($row = mysqli_fetch_assoc($c_res)) { 
    $claimed[] = $row; 
}

// query: unclaimed
$unclaimed = [];
$u_sql = "SELECT h.household_number, CONCAT(head.first_name, ' ', head.last_name) AS head_of_family
          FROM registered_household h
          LEFT JOIN registered_resi head ON h.head_of_family_id = head.id
          WHERE h.is_archived = 0 AND h.id NOT IN (SELECT household_id FROM distribution_logs WHERE program_id = $prog_id)
          ORDER BY h.household_number ASC";
$u_res = mysqli_query($conn, $u_sql);
while($row = mysqli_fetch_assoc($u_res)) { 
    $unclaimed[] = $row; 
}
?>

<div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #f1f1f1; padding-bottom: 15px; margin-bottom: 20px; padding-right: 40px">
    <h2 style="color:#144876; margin: 0;"><i class="fa-solid fa-list"></i> <?php echo htmlspecialchars($program_name); ?> details</h2>
    <div style="display: flex; gap: 10px;">
        <a href="export_report.php?program_name=<?php echo urlencode($program_name); ?>" style="background: #FFCC00; padding: 8px 15px; border-radius: 8px; text-decoration: none; color: #000; font-weight: bold;">
            <i class="fa-solid fa-file-csv"></i> Download csv
        </a>
    </div>
</div>

<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
    <div style="border: 1px solid #ddd; border-radius: 8px; padding: 15px; max-height: 400px; overflow-y: auto;">
        <h3 style="color: #006B2D; margin-bottom: 10px; position: sticky; top: -15px; background: #fff; padding-bottom: 10px;"> Claimed (<?php echo count($claimed); ?>)</h3>
        <ul style="list-style: none; padding: 0; margin: 0;">
            <?php if(count($claimed) > 0): foreach($claimed as $c): ?>
                <li style="padding: 10px 0; border-bottom: 1px solid #f1f1f1;">
                    <strong><?php echo $c['household_number']; ?></strong> - <?php echo $c['head_of_family']; ?><br>
                    <small style="color: #888;"><?php echo date("M d, Y h:i A", strtotime($c['date_claimed'])); ?></small>
                </li>
            <?php endforeach; else: ?>
                <p style="color: #888;">No claims yet.</p>
            <?php endif; ?>
        </ul>
    </div>

    <div style="border: 1px solid #ddd; border-radius: 8px; padding: 15px; max-height: 400px; overflow-y: auto;">
        <h3 style="color: #C81B20; margin-bottom: 10px; position: sticky; top: -15px; background: #fff; padding-bottom: 10px;"></i> Remaining (<?php echo count($unclaimed); ?>)</h3>
        <ul style="list-style: none; padding: 0; margin: 0;">
            <?php if(count($unclaimed) > 0): foreach($unclaimed as $u): ?>
                <li style="padding: 10px 0; border-bottom: 1px solid #f1f1f1;">
                    <strong><?php echo $u['household_number']; ?></strong> - <?php echo $u['head_of_family']; ?>
                </li>
            <?php endforeach; else: ?>
                <p style="color: #006B2D;">100% Claimed</p>
            <?php endif; ?>
        </ul>
    </div>
</div>
<?php mysqli_close($conn); ?>
