<?php $heading = 'Your EnoughEdu subscription renews soon';
ob_start();
?><p>Your <?= e($plan ?? 'subscription') ?> expires on <?= e(
     $date ?? 'soon',
 ) ?>. Renew to keep uninterrupted access to your tools and academic resources.</p>
<p>
<a href="<?= e(
    url('/pricing'),
) ?>" style="display:inline-block;background:#2563eb;color:white;padding:12px 18px;border-radius:10px;text-decoration:none;font-weight:bold">Manage subscription</a>
</p><?php
$content = ob_get_clean();
include __DIR__ . '/base.php';

