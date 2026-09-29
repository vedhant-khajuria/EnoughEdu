<?php
declare(strict_types=1);
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit();
}
require dirname(__DIR__) . '/app/bootstrap.php';
$pdo = db();
if (!$pdo) {
    exit("Database unavailable\n");
}
$days = (int) setting('subscription_expiry_reminder_days', 7);
$q = $pdo->prepare(
    'SELECT s.id,s.expires_at,p.name AS plan,u.name,u.email FROM subscriptions s JOIN users u ON u.id=s.user_id LEFT JOIN plans p ON p.id=s.plan_id WHERE s.status="active" AND DATE(s.expires_at)=DATE_ADD(CURDATE(),INTERVAL ? DAY)',
);
$q->execute([$days]);
$sent = 0;
foreach ($q as $row) {
    if (
        send_email($row['email'], 'Your EnoughEdu subscription expires soon', 'expiry', [
            'name' => $row['name'],
            'plan' => $row['plan'] ?? 'EnoughEdu plan',
            'date' => date('d M Y', strtotime($row['expires_at'])),
        ])
    ) {
        $sent++;
    }
}
echo "Sent {$sent} reminder(s).\n";
