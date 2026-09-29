<?php $heading = 'Verify your EnoughEdu admin password change';
ob_start();
?><p>A password change was requested from the EnoughEdu administrator panel.</p>
<p style="font-size:30px;letter-spacing:8px;font-weight:800;color:#1d4ed8;margin:28px 0"><?= e(
    $otp ?? '',
) ?></p>
<p>This single-use verification code expires in <?= e(
    $minutes ?? '10',
) ?> minutes. Enter it only on enoughedu.vedhant.in.</p>
<p style="color:#64748b;font-size:13px">If you did not request this change, do not share the code. Your current password remains unchanged.</p><?php
 $content = ob_get_clean();
 include __DIR__ . '/base.php';

