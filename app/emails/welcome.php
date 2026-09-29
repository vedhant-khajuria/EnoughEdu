<?php $heading = 'Welcome to EnoughEdu, ' . e($name ?? 'Student') . '!';
ob_start();
?><p>Your academic workspace is ready. Add your branch and semester, then explore tools, notes, planners and AI-assisted career tools.</p>
<p>
<a href="<?= e(
    url('/dashboard'),
) ?>" style="display:inline-block;background:#2563eb;color:white;padding:12px 18px;border-radius:10px;text-decoration:none;font-weight:bold">Open my dashboard</a>
</p><?php
$content = ob_get_clean();
include __DIR__ . '/base.php';

