<?php $heading = ($type ?? 'purchase') === 'payment' ? 'Payment received' : 'Purchase confirmed';
ob_start();
?><p>Thanks, <?= e(
    $name ?? 'Student',
) ?>. Your purchase is confirmed and access is now available in your dashboard.</p>
<p>
<strong><?= e(
    $product ?? 'EnoughEdu access',
) ?></strong>
<br>Amount: ₹<?= e($amount ?? '0') ?><br>Order: <?= e(
    $orderNo ?? '—',
) ?></p>
<p>
<a href="<?= e(
    url('/dashboard/purchases'),
) ?>" style="display:inline-block;background:#2563eb;color:white;padding:12px 18px;border-radius:10px;text-decoration:none;font-weight:bold">View purchase</a>
</p><?php
$content = ob_get_clean();
include __DIR__ . '/base.php';

