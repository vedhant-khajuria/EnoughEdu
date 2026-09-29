<?php $heading = 'Reset your EnoughEdu password';
ob_start();
?><p>We received a password reset request for your account. This secure link expires in 60 minutes.</p>
<p>
<a href="<?= e(
    $resetUrl ?? '#',
) ?>" style="display:inline-block;background:#2563eb;color:white;padding:12px 18px;border-radius:10px;text-decoration:none;font-weight:bold">Reset password</a>
</p>
<p style="color:#64748b;font-size:13px">If you did not request this, you can safely ignore this email.</p><?php
$content = ob_get_clean();
include __DIR__ . '/base.php';

