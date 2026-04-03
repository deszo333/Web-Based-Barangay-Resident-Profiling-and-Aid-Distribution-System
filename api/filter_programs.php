<?php
require_once __DIR__ . '/../config/auth_check.php';
require_once __DIR__ . '/../config/db_connect.php';

$program = isset($_POST['program_name']) ? mysqli_real_escape_string($conn, $_POST['program_name']) : '';
if (empty($program)) {
    echo "<p style='text-align:center; color:#FF6384;'>please select a program.</p>";
    exit;
}

// get target beneficiaries
$target = 0; 
$prog_id = 0;
$stmt = mysqli_prepare($conn, "SELECT id, beneficiaries FROM aid_program WHERE program_name = ?");
mysqli_stmt_bind_param($stmt, "s", $program);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
if($row = mysqli_fetch_assoc($res)) { 
    $target = $row['beneficiaries']; 
    $prog_id = $row['id']; 
}
mysqli_stmt_close($stmt);

// get real distributed count
$claimed = 0;
$stmt = mysqli_prepare($conn, "SELECT COUNT(id) AS total FROM distribution_logs WHERE program_id = ?");
mysqli_stmt_bind_param($stmt, "i", $prog_id);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
if($row = mysqli_fetch_assoc($res)) { 
    $claimed = $row['total']; 
}
mysqli_stmt_close($stmt);

$remaining = max($target - $claimed, 0);

// return the obviously tappable ui
?>
<div class="program-item tappable-chart" data-program="<?php echo htmlspecialchars($program); ?>" 
     style="cursor: pointer; transition: all 0.2s ease; text-align: center; padding: 30px; border: 3px dashed #144876; border-radius: 12px; background: #fff;"
     onmouseover="this.style.transform='scale(1.02)'; this.style.borderColor='#006B2D'; this.style.boxShadow='0 8px 25px rgba(0,0,0,0.1)';" 
     onmouseout="this.style.transform='scale(1)'; this.style.borderColor='#144876'; this.style.boxShadow='none';">
    
    <h2 style="color:#fffff; margin-bottom: 15px; font-size: 24px;"><?php echo htmlspecialchars($program); ?></h2>
    
    <div class="program-details" style="justify-content: center; gap: 30px; margin-bottom: 25px; font-size: 18px;">
        <span><strong>Target:</strong> <?php echo number_format($target); ?></span>
        <span style="color:#006B2D;"><strong>Claimed:</strong> <?php echo number_format($claimed); ?></span>
        <span style="color:#C81B20;"><strong>Remaining:</strong> <?php echo number_format($remaining); ?></span>
    </div>

    <div class="chart-wrapper" style="position: relative; width: 250px; height: 250px; margin: 0 auto;">
        <canvas class="mini-chart" data-claimed="<?php echo $claimed; ?>" data-remaining="<?php echo $remaining; ?>"></canvas>
    </div>

    <p style="margin-top: 25px; font-size: 14px; color: #888; font-style: italic;">
        click the chart above to view the detailed household list and export options.
    </p>
</div>
<?php mysqli_close($conn); ?>