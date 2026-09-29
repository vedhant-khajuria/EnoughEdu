<?php
declare(strict_types=1);

function rm_json(array $data, int $status = 200): never
{
    http_response_code($status);
    header('Content-Type: application/json');
    header('Cache-Control: no-store');
    echo json_encode($data, JSON_UNESCAPED_SLASHES);
    exit();
}
function rm_user(): array
{
    $u = user();
    if (!$u) {
        rm_json(
            ['error' => 'Please sign in before setting up a membership.', 'login' => '/login'],
            401,
        );
    }
    return $u;
}
function rm_api(string $method, string $path, ?array $payload = null): array
{
    [$status, $data, $error] = razorpay_api($method, $path, $payload);
    if ($status < 200 || $status >= 300 || !is_array($data)) {
        throw new RuntimeException('Razorpay could not confirm this request. Please retry later.');
    }
    return $data;
}
function rm_latest(int $userId): ?array
{
    $q = db()->prepare(
        'SELECT * FROM resource_memberships WHERE user_id=? ORDER BY id DESC LIMIT 1',
    );
    $q->execute([$userId]);
    return $q->fetch() ?: null;
}
function rm_price(int $paise): string
{
    return '₹' . number_format($paise / 100, 2);
}
function rm_offer(): array
{
    return [
        'enabled' => (string) setting('resource_membership_enabled', '0') === '1',
        'amount' => (int) setting('resource_monthly_paise', 0),
        'plan' => (string) setting('resource_plan_id', ''),
    ];
}

// Entitlement always requires provider verification. Browser callbacks alone never grant access.
function rm_sync(array $row): array
{
    if (empty($row['gateway_id'])) {
        return $row;
    }
    $remote = rm_api('GET', 'subscriptions/' . rawurlencode($row['gateway_id']));
    if (
        ($remote['id'] ?? '') !== $row['gateway_id'] ||
        ($remote['plan_id'] ?? '') !== $row['plan_id'] ||
        (int) ($remote['quantity'] ?? 0) !== 1
    ) {
        throw new RuntimeException('Subscription details did not match.');
    }
    $status = (string) ($remote['status'] ?? 'unknown');
    $invoices = rm_api(
        'GET',
        'invoices?subscription_id=' . rawurlencode($row['gateway_id']) . '&count=100',
    );
    $setup = false;
    $paidEnd = 0;
    $setupId = null;
    $records = [];
    $pdo = db();
    $known = $pdo->prepare(
        'SELECT payment_id,access_until FROM resource_payment_receipts WHERE membership_id=?',
    );
    $known->execute([$row['id']]);
    $periods = $known->fetchAll(PDO::FETCH_KEY_PAIR);
    foreach ($invoices['items'] ?? [] as $invoice) {
        if (
            ($invoice['subscription_id'] ?? '') !== $row['gateway_id'] ||
            ($invoice['status'] ?? '') !== 'paid' ||
            ($invoice['currency'] ?? '') !== 'INR' ||
            empty($invoice['payment_id'])
        ) {
            continue;
        }
        $amount = (int) ($invoice['amount_paid'] ?? 0);
        if ($amount !== 500 && $amount !== (int) $row['amount_paise']) {
            continue;
        }
        $invoicePaidAt = (int) ($invoice['paid_at'] ?? 0);
        $mayBeSetup =
            $amount === 500 &&
            !empty($row['trial_end']) &&
            $invoicePaidAt < (int) $row['trial_end'] &&
            ((int) $row['trial_end'] > time() || empty($row['setup_payment_id']));
        $mayBeCurrent =
            $amount === (int) $row['amount_paise'] &&
            $invoicePaidAt >= (int) ($remote['current_start'] ?? PHP_INT_MAX);
        if (
            !$mayBeSetup &&
            !$mayBeCurrent &&
            (int) ($periods[$invoice['payment_id']] ?? 0) <= time()
        ) {
            continue;
        }
        $payment = rm_api('GET', 'payments/' . rawurlencode($invoice['payment_id']));
        if (
            ($payment['status'] ?? '') !== 'captured' ||
            ($payment['currency'] ?? '') !== 'INR' ||
            (int) ($payment['amount'] ?? 0) !== $amount ||
            (int) ($payment['amount_refunded'] ?? 0) > 0 ||
            ($payment['invoice_id'] ?? '') !== ($invoice['id'] ?? '')
        ) {
            continue;
        }
        $paidAt = (int) ($invoice['paid_at'] ?? ($payment['created_at'] ?? 0));
        $periodEnd = (int) ($periods[$payment['id']] ?? 0);
        if ($amount === 500 && !empty($row['trial_end']) && $paidAt < (int) $row['trial_end']) {
            $setup = true;
            $setupId = $payment['id'];
        }
        if (
            $amount === (int) $row['amount_paise'] &&
            (!empty($row['trial_end']) ? $paidAt >= (int) $row['trial_end'] : true) &&
            $paidAt >= (int) ($remote['current_start'] ?? PHP_INT_MAX) &&
            (int) ($remote['current_end'] ?? 0) > $paidAt
        ) {
            $periodEnd = max($periodEnd, (int) $remote['current_end']);
        }
        $paidEnd = max($paidEnd, $periodEnd);
        $records[] = [$payment['id'], $row['id'], $amount, $paidAt, $periodEnd];
    }
    // A cancelled membership retains the last verified paid period. No future period is granted.
    $until = $paidEnd;
    if (
        $setup &&
        in_array(
            $status,
            ['authenticated', 'active', 'cancelled', 'pending', 'halted', 'completed'],
            true,
        )
    ) {
        $until = max($until, (int) $row['trial_end']);
    }
    if (
        !in_array(
            $status,
            ['authenticated', 'active', 'cancelled', 'pending', 'halted', 'completed'],
            true,
        )
    ) {
        $until = 0;
    }
    $pdo->beginTransaction();
    try {
        foreach ($records as $record) {
            $pdo->prepare(
                'INSERT INTO resource_payment_receipts(payment_id,membership_id,amount_paise,paid_at,access_until) VALUES(?,?,?,?,?) ON DUPLICATE KEY UPDATE amount_paise=VALUES(amount_paise),access_until=VALUES(access_until)',
            )->execute($record);
        }
        $pdo->prepare(
            'UPDATE resource_memberships SET status=?,access_until=?,setup_payment_id=COALESCE(?,setup_payment_id),updated_at=? WHERE id=?',
        )->execute([$status, $until, $setupId, time(), $row['id']]);
        $pdo->commit();
    } catch (Throwable $e) {
        $pdo->rollBack();
        throw $e;
    }
    $row['status'] = $status;
    $row['access_until'] = $until;
    $row['setup_payment_id'] = $setupId ?: $row['setup_payment_id'];
    return $row;
}
function rm_legacy_access(array $u, array $item): bool
{
    $pdo = db();
    $q = $pdo->prepare(
        'SELECT 1 FROM user_materials WHERE user_id=? AND material_type=? AND material_id=? LIMIT 1',
    );
    $q->execute([$u['id'], $item['kind'], $item['id']]);
    if ($q->fetchColumn()) {
        return true;
    }
    $q = $pdo->prepare(
        'SELECT s.scope_type,p.limits_json FROM subscriptions s LEFT JOIN plans p ON p.id=s.plan_id WHERE s.user_id=? AND s.status="active" AND s.starts_at<=NOW() AND (s.expires_at IS NULL OR s.expires_at>NOW())',
    );
    $q->execute([$u['id']]);
    foreach ($q->fetchAll() as $s) {
        $contents = plan_contents($s['limits_json'] ?? null);
        if ($contents !== null) {
            if (
                plan_includes(
                    $contents,
                    $item['kind'] === 'note' ? 'notes' : 'resources',
                    (int) $item['id'],
                )
            ) {
                return true;
            }
            continue;
        }
        if ($s['scope_type'] === 'degree') {
            return true;
        }
        if (in_array($s['scope_type'], ['branch', 'semester'], true)) {
            $b = $pdo->prepare(
                'SELECT 1 FROM resource_branches WHERE material_kind=? AND material_id=? AND branch_id=?',
            );
            $b->execute([$item['kind'], $item['id'], (int) ($u['branch_id'] ?? 0)]);
            if (
                $b->fetchColumn() &&
                ($s['scope_type'] === 'branch' ||
                    $item['semester'] === null ||
                    (int) $item['semester'] === (int) $u['semester'])
            ) {
                return true;
            }
        }
    }
    return false;
}
function rm_can_download(array $u, array $item): bool
{
    if (($u['role'] ?? '') === 'admin' || rm_legacy_access($u, $item)) {
        return true;
    }
    $row = rm_latest((int) $u['id']);
    if (!$row) {
        return false;
    }
    try {
        $row = rm_sync($row);
        return (int) $row['access_until'] > time();
    } catch (Throwable) {
        return false;
    }
}
function rm_popup(): void
{
    $offer = rm_offer(); ?>
    <dialog class="rl-dialog" id="rl-membership-dialog" aria-labelledby="rl-offer-title">
<button class="rl-close" type="button" data-rl-close aria-label="Close">&times;</button>
<span class="eyebrow">Original PDF downloads</span>
<h2 id="rl-offer-title">Your resource membership</h2>
    <?php if ($offer['enabled'] && $offer['amount'] > 0): ?><p class="rl-price"><?= e(
    rm_price($offer['amount']),
) ?><small> / month</small>
</p>
<p>
<strong>₹5 setup fee today.</strong> The first 24 hours have no subscription charge; your monthly autopay starts afterwards.</p>
<p class="muted">Previews remain free. Review the exact first-charge time and authorize autopay securely on the next screen. Cancel from your membership page before the next charge.</p>
<a class="btn btn-primary btn-block" href="/resources/membership">Review membership &rarr;</a><?php else: ?><p>New download memberships are not available yet. You can keep reading the free previews.</p><?php endif; ?><button class="btn btn-secondary btn-block" type="button" data-rl-close style="margin-top:12px">Keep reading for free</button>
</dialog>
    <?php
}
function rm_page(): void
{
    header('Cache-Control: no-store');
    header('X-Robots-Tag: noindex, nofollow');
    $u = user();
    $offer = rm_offer();
    if ((string) setting('maintenance_enabled', '0') === '1') {
        $offer['enabled'] = false;
    }
    $row = $u ? rm_latest((int) $u['id']) : null;
    $syncError = false;
    if ($row && !empty($row['gateway_id'])) {
        try {
            $row = rm_sync($row);
        } catch (Throwable) {
            $syncError = true;
        }
    }
    page_start('Resource membership', 'Manage your monthly PDF download membership.');
    rl_assets();
    ?>
    <section class="section">
<div class="container" style="max-width:760px">
<a class="card-link" href="/resources">&larr; Back to resources</a>
<h1 class="rl-heading">Download membership</h1>
    <?php if (
        $row
    ): ?><div class="panel" style="margin-bottom:24px">
<h3>Your membership</h3>
<p>Status: <?= e(
    $row['status'],
) ?> · <?= e(rm_price((int) $row['amount_paise'])) ?> / month</p><?php
 if (
     $syncError
 ): ?><p class="alert alert-error">We could not refresh your payment status. Downloads remain locked until Razorpay can confirm access. Try refreshing later.</p><?php elseif (
     (int) $row['access_until'] > time()
 ): ?><p>Download access until <?= e(
    date('d M Y, h:i A T', (int) $row['access_until']),
) ?>.</p><?php endif;
 if (!empty($row['trial_end'])): ?><p>First scheduled monthly charge: <?= e(
    date('d M Y, h:i A T', (int) $row['trial_end']),
) ?>.</p><?php endif;
 if (
     !empty($row['gateway_id']) &&
     !in_array($row['status'], ['cancelled', 'completed', 'expired'], true)
 ): ?><form method="post" action="/resources/membership/cancel"><?= csrf_field() ?><input type="hidden" name="membership_id" value="<?= (int) $row[
    'id'
] ?>">
<button class="btn btn-secondary">Cancel autopay</button>
<p class="muted">Stops future renewals; a charge already processing may still complete. Verified access remains until its expiry.</p>
</form><?php endif;
 ?></div><?php endif; ?>
    <?php if (
        !$row ||
        in_array($row['status'], ['created', 'cancelled', 'expired', 'completed'], true)
    ): ?>
    <div class="panel">
<h2>Read freely. Download with membership.</h2><?php if (
        $offer['enabled'] &&
        $offer['amount'] > 0
    ): ?><p class="rl-price"><?= e(
    rm_price($offer['amount']),
) ?><small> / month</small>
</p>
<p>First membership: <strong>₹5 setup fee</strong> and 24 hours with no monthly subscription charge. Then <?= e(
    rm_price($offer['amount']),
) ?> per month by autopay, for up to 120 monthly payments unless cancelled earlier.</p>
<p class="muted">Your 24-hour trial clock starts when you press “Prepare secure checkout”. Downloads unlock after Razorpay confirms both the setup payment and autopay authorization. The exact first-charge time is shown before payment. Returning members do not receive another trial or setup fee. Your payment method may require a temporary authorization debit shown by Razorpay.</p>
    <?php if (
        $u
    ): ?><form data-rm-create method="post" action="/resources/membership/create"><?= csrf_field() ?><input type="hidden" name="offer_plan" value="<?= e(
    $offer['plan'],
) ?>">
<input type="hidden" name="offer_amount" value="<?= $offer[
    'amount'
] ?>">
<label class="rl-consent">
<input type="checkbox" name="consent" value="1" required> I agree to the setup fee and monthly automatic charges shown above. I can cancel future renewals from this page.</label>
<button class="btn btn-primary" type="submit">Prepare secure checkout</button>
</form>
<div id="rm-checkout-summary" hidden>
</div>
<button id="rm-pay" class="btn btn-primary" hidden type="button">Continue to Razorpay</button><?php else: ?><a class="btn btn-primary" href="/login">Sign in to continue</a><?php endif;else: ?><p>New memberships are currently paused. Existing memberships can still be managed above.</p><?php endif; ?></div><?php endif; ?>
    <p id="rm-feedback" role="status" aria-live="polite">
</p>
<p class="muted">The ₹5 setup fee means this is not a completely free signup. This membership covers resource PDFs only; tool purchases are separate.</p>
</div>
</section><script src="https://checkout.razorpay.com/v1/checkout.js" defer></script><?php page_end();
}
function rm_create(): never
{
    $u = rm_user();
    verify_csrf();
    $pdo = db();
    $lock = 'enoughedu_resource_' . (int) $u['id'];
    $locked = false;
    $out = [];
    $http = 200;
    try {
        if ((string) setting('maintenance_enabled', '0') === '1') {
            throw new DomainException('New memberships are paused during maintenance.');
        }
        if (($_POST['consent'] ?? '') !== '1') {
            throw new DomainException('Consent to recurring charges is required.');
        }
        $offer = rm_offer();
        if (
            !$offer['enabled'] ||
            $offer['amount'] < 100 ||
            !preg_match('/^plan_[A-Za-z0-9]+$/', $offer['plan'])
        ) {
            throw new DomainException('New memberships are currently paused.');
        }
        if (
            ($_POST['offer_plan'] ?? '') !== $offer['plan'] ||
            (int) ($_POST['offer_amount'] ?? 0) !== $offer['amount']
        ) {
            throw new DomainException(
                'The offer changed. Refresh this page and review the current price.',
            );
        }
        $q = $pdo->prepare('SELECT GET_LOCK(?,0)');
        $q->execute([$lock]);
        $locked = (bool) $q->fetchColumn();
        if (!$locked) {
            throw new DomainException('Another signup is processing. Please wait.');
        }
        $row = rm_latest((int) $u['id']);
        if ($row && !empty($row['gateway_id'])) {
            $row = rm_sync($row);
        }
        if ($row && in_array($row['status'], ['creating', 'uncertain'], true)) {
            throw new DomainException(
                'A previous signup needs reconciliation. Contact support before creating another.',
            );
        }
        if (
            $row &&
            !in_array($row['status'], ['cancelled', 'expired', 'completed', 'created'], true)
        ) {
            throw new DomainException(
                'You already have a membership. Refresh this page to manage it.',
            );
        }
        if ($row && $row['status'] === 'created') {
            if ($row['plan_id'] !== $offer['plan']) {
                throw new DomainException(
                    'Cancel the pending signup before accepting the new price.',
                );
            }
        } else {
            $q = $pdo->prepare(
                'SELECT COUNT(*) FROM resource_memberships WHERE user_id=? AND setup_payment_id IS NOT NULL',
            );
            $q->execute([$u['id']]);
            $first = !(bool) $q->fetchColumn();
            $now = time();
            $trialEnd = $first ? $now + 86400 : null;
            $pdo->prepare(
                'INSERT INTO resource_memberships(user_id,plan_id,amount_paise,status,trial_end,consent_at,created_at,updated_at) VALUES(?,?,?,"creating",?,?,?,?)',
            )->execute([$u['id'], $offer['plan'], $offer['amount'], $trialEnd, $now, $now, $now]);
            $id = (int) $pdo->lastInsertId();
            $payload = [
                'plan_id' => $offer['plan'],
                'total_count' => 120,
                'quantity' => 1,
                'customer_notify' => 1,
                'expire_by' => $now + 1800,
                'notes' => [
                    'resource_membership_id' => (string) $id,
                    'user_id' => (string) $u['id'],
                    'purpose' => 'resource_downloads',
                ],
            ];
            if ($first) {
                $payload['start_at'] = $trialEnd;
                $payload['addons'] = [
                    [
                        'item' => [
                            'name' => 'EnoughEdu resource membership setup fee',
                            'amount' => 500,
                            'currency' => 'INR',
                        ],
                    ],
                ];
            }
            try {
                $remote = rm_api('POST', 'subscriptions', $payload);
                if (!preg_match('/^sub_[A-Za-z0-9]+$/', (string) ($remote['id'] ?? ''))) {
                    throw new RuntimeException('Invalid subscription response.');
                }
                $pdo->prepare(
                    'UPDATE resource_memberships SET gateway_id=?,status="created",updated_at=? WHERE id=?',
                )->execute([$remote['id'], time(), $id]);
            } catch (Throwable $e) {
                $pdo->prepare(
                    'UPDATE resource_memberships SET status="uncertain",updated_at=? WHERE id=?',
                )->execute([time(), $id]);
                throw $e;
            }
            $row = rm_latest((int) $u['id']);
        }
        $out = [
            'key' => (string) config('razorpay.key_id'),
            'subscription_id' => $row['gateway_id'],
            'membership_id' => (int) $row['id'],
            'name' => 'EnoughEdu',
            'description' => 'Resource downloads · monthly membership',
            'prefill' => ['name' => $u['name'], 'email' => $u['email']],
            'amount_label' => rm_price((int) $row['amount_paise']),
            'trial_end' => !empty($row['trial_end'])
                ? date('d M Y, h:i A T', (int) $row['trial_end'])
                : null,
            'csrf' => csrf_token(),
        ];
    } catch (Throwable $e) {
        $out = [
            'error' =>
                $e instanceof DomainException
                    ? $e->getMessage()
                    : 'Razorpay could not prepare checkout. Contact support if this persists; do not repeat payments.',
        ];
        $http = 400;
    } finally {
        if ($locked) {
            $q = $pdo->prepare('SELECT RELEASE_LOCK(?)');
            $q->execute([$lock]);
        }
    }
    rm_json($out, $http);
}
function rm_verify(): never
{
    $u = rm_user();
    verify_csrf();
    $id = (int) ($_POST['membership_id'] ?? 0);
    $q = db()->prepare('SELECT * FROM resource_memberships WHERE id=? AND user_id=?');
    $q->execute([$id, $u['id']]);
    $row = $q->fetch();
    $payment = (string) ($_POST['razorpay_payment_id'] ?? '');
    $sig = (string) ($_POST['razorpay_signature'] ?? '');
    if (
        !$row ||
        !preg_match('/^pay_[A-Za-z0-9]+$/', $payment) ||
        !hash_equals(
            hash_hmac(
                'sha256',
                $payment . '|' . $row['gateway_id'],
                (string) config('razorpay.key_secret'),
            ),
            $sig,
        )
    ) {
        rm_json(['error' => 'Payment signature could not be verified.'], 400);
    }
    try {
        $row = rm_sync($row);
        rm_json([
            'ok' => true,
            'active' => (int) $row['access_until'] > time(),
            'message' =>
                (int) $row['access_until'] > time()
                    ? 'Your download access is ready.'
                    : 'Authorization received. Waiting for Razorpay to confirm the captured payment. Refresh this page shortly.',
        ]);
    } catch (Throwable) {
        rm_json(
            [
                'error' =>
                    'Payment is awaiting confirmation. Refresh this page shortly; do not pay again.',
            ],
            503,
        );
    }
}
function rm_cancel(): never
{
    $u = require_auth();
    verify_csrf();
    $q = db()->prepare('SELECT * FROM resource_memberships WHERE id=? AND user_id=?');
    $q->execute([(int) ($_POST['membership_id'] ?? 0), $u['id']]);
    $row = $q->fetch();
    try {
        if (!$row || empty($row['gateway_id'])) {
            throw new DomainException('Membership not found.');
        }
        $row = rm_sync($row);
        if (!in_array($row['status'], ['cancelled', 'completed', 'expired'], true)) {
            rm_api('POST', 'subscriptions/' . rawurlencode($row['gateway_id']) . '/cancel', [
                'cancel_at_cycle_end' => 0,
            ]);
        }
        rm_sync($row);
        flash(
            'success',
            'Autopay cancelled. No new renewal is scheduled; a payment already processing may still complete.',
        );
    } catch (Throwable) {
        flash(
            'error',
            'Cancellation could not be confirmed. Retry or contact support before the next charge.',
        );
    }
    redirect('/resources/membership');
}
function rm_status(): never
{
    $u = user();
    $item = rl_item((string) ($_GET['kind'] ?? ''), (int) ($_GET['id'] ?? 0));
    rm_json(['allowed' => $u && $item ? rm_can_download($u, $item) : false]);
}
function rm_webhook(): never
{
    $raw = (string) file_get_contents('php://input');
    if (
        !razorpay_verify_webhook_signature(
            $raw,
            (string) ($_SERVER['HTTP_X_RAZORPAY_SIGNATURE'] ?? ''),
        )
    ) {
        rm_json(['error' => 'Invalid signature'], 401);
    }
    $event = json_decode($raw, true);
    if (!is_array($event)) {
        rm_json(['error' => 'Invalid body'], 400);
    }
    $id = (string) ($event['payload']['subscription']['entity']['id'] ?? '');
    if ($id === '') {
        rm_json(['ok' => true, 'ignored' => true]);
    }
    $q = db()->prepare('SELECT * FROM resource_memberships WHERE gateway_id=?');
    $q->execute([$id]);
    $row = $q->fetch();
    if (!$row) {
        rm_json(['ok' => true, 'ignored' => true]);
    }
    // Fetch current provider state rather than applying stale or repeated webhook payloads.
    try {
        rm_sync($row);
        rm_json(['ok' => true]);
    } catch (Throwable) {
        rm_json(['error' => 'Could not reconcile subscription'], 503);
    }
}
function rm_shared_webhook(): void
{
    $raw = (string) file_get_contents('php://input');
    if (
        !razorpay_verify_webhook_signature(
            $raw,
            (string) ($_SERVER['HTTP_X_RAZORPAY_SIGNATURE'] ?? ''),
        )
    ) {
        return;
    }
    $event = json_decode($raw, true);
    if (!is_array($event)) {
        return;
    }
    $gateway = (string) ($event['payload']['subscription']['entity']['id'] ?? '');
    try {
        if ($gateway === '') {
            $invoiceId = (string) ($event['payload']['payment']['entity']['invoice_id'] ?? '');
            if (!preg_match('/^inv_[A-Za-z0-9]+$/', $invoiceId)) {
                return;
            }
            $invoice = rm_api('GET', 'invoices/' . $invoiceId);
            $gateway = (string) ($invoice['subscription_id'] ?? '');
        }
        if ($gateway === '') {
            return;
        }
        $q = db()->prepare('SELECT * FROM resource_memberships WHERE gateway_id=?');
        $q->execute([$gateway]);
        $row = $q->fetch();
        if (!$row) {
            return;
        }
        rm_sync($row);
        rm_json(['ok' => true]);
    } catch (Throwable) {
        rm_json(['error' => 'Subscription payment confirmation is temporarily unavailable'], 503);
    }
}
function rm_admin_form(): void
{
    $offer = rm_offer(); ?>
    <section class="panel" style="margin-bottom:24px">
<h3>Resource download membership</h3>
<p class="muted">₹5 setup fee for first signup, then 24 hours with no monthly charge, followed by monthly autopay. Changes apply to new signups; existing members keep their agreed price.</p>
<form method="post" action="/admin/resources/membership"><?= csrf_field() ?><div class="field">
<label for="rm-price">Monthly price (₹)</label>
<input class="input" type="number" id="rm-price" name="monthly_price" min="1" max="15000" step="0.01" required value="<?= e(
    $offer['amount'] > 0 ? number_format($offer['amount'] / 100, 2, '.', '') : '',
) ?>">
</div>
<label class="rl-consent">
<input type="checkbox" name="enabled" value="1" <?= $offer[
    'enabled'
]
    ? 'checked'
    : '' ?>> Enable new memberships after testing Razorpay Subscriptions and the webhook</label>
<button class="btn btn-primary">Save membership settings</button>
</form>
<p class="muted">Subscription webhook: <?= e(
    url('/resources/membership/webhook'),
) ?>. Use the same webhook secret as the existing Razorpay configuration. Your account needs Subscriptions activated.</p>
</section>
    <?php
    $pending = db()
        ->query(
            'SELECT id,user_id,status,gateway_id FROM resource_memberships WHERE status IN ("creating","uncertain") ORDER BY id DESC LIMIT 30',
        )
        ->fetchAll();
    if ($pending): ?>
    <section class="panel" style="margin-bottom:24px">
<h3>Signups needing reconciliation</h3>
<p class="muted">A network interruption can leave a Razorpay subscription created without its response reaching this website. Find it in Razorpay by its resource_membership_id note, then enter its subscription ID here. Do not create another charge.</p><?php foreach (
        $pending
        as $p
    ): ?><form method="post" action="/admin/resources/reconcile" class="rl-actions"><?= csrf_field() ?><input type="hidden" name="membership_id" value="<?= (int) $p[
    'id'
] ?>">
<label>Membership #<?= (int) $p['id'] ?> · user #<?= (int) $p[
     'user_id'
 ] ?><input class="input" name="gateway_id" required pattern="sub_[A-Za-z0-9]+" placeholder="sub_...">
</label>
<button class="btn btn-secondary">Verify and reconnect</button>
</form><?php endforeach; ?></section><?php endif;
}
function rm_admin_reconcile(): never
{
    require_admin();
    verify_csrf();
    $pdo = db();
    try {
        $id = (int) ($_POST['membership_id'] ?? 0);
        $gateway = (string) ($_POST['gateway_id'] ?? '');
        if (!preg_match('/^sub_[A-Za-z0-9]+$/', $gateway)) {
            throw new DomainException('Enter a Razorpay subscription ID.');
        }
        $q = $pdo->prepare(
            'SELECT * FROM resource_memberships WHERE id=? AND status IN ("creating","uncertain")',
        );
        $q->execute([$id]);
        $row = $q->fetch();
        if (!$row) {
            throw new DomainException('This signup no longer needs reconciliation.');
        }
        $remote = rm_api('GET', 'subscriptions/' . $gateway);
        if (
            (int) ($remote['notes']['resource_membership_id'] ?? 0) !== $id ||
            (int) ($remote['notes']['user_id'] ?? 0) !== (int) $row['user_id'] ||
            ($remote['plan_id'] ?? '') !== $row['plan_id']
        ) {
            throw new DomainException('That Razorpay subscription does not belong to this signup.');
        }
        $pdo->prepare(
            'UPDATE resource_memberships SET gateway_id=?,status=?,updated_at=? WHERE id=?',
        )->execute([$gateway, $remote['status'], time(), $id]);
        $row['gateway_id'] = $gateway;
        rm_sync($row);
        flash('success', 'Signup reconnected and verified.');
    } catch (Throwable $e) {
        flash(
            'error',
            $e instanceof DomainException
                ? $e->getMessage()
                : 'Razorpay verification failed. No new subscription was created.',
        );
    }
    redirect('/admin/resources');
}
function rm_admin_save(): never
{
    require_admin();
    verify_csrf();
    $pdo = db();
    try {
        $raw = trim((string) ($_POST['monthly_price'] ?? ''));
        if (!preg_match('/^\d{1,5}(\.\d{1,2})?$/', $raw)) {
            throw new DomainException('Enter a valid monthly price.');
        }
        $paise = (int) round((float) $raw * 100);
        if ($paise < 100 || $paise > 1500000) {
            throw new DomainException('Monthly price must be between ₹1 and ₹15,000.');
        }
        $offer = rm_offer();
        $plan = $offer['plan'];
        if ($paise !== $offer['amount'] || $plan === '') {
            $remote = rm_api('POST', 'plans', [
                'period' => 'monthly',
                'interval' => 1,
                'item' => [
                    'name' => 'EnoughEdu resource downloads',
                    'amount' => $paise,
                    'currency' => 'INR',
                    'description' => 'Monthly PDF download membership',
                ],
            ]);
            $plan = (string) ($remote['id'] ?? '');
            if (!preg_match('/^plan_[A-Za-z0-9]+$/', $plan)) {
                throw new RuntimeException('Invalid plan response.');
            }
        }
        $pdo->beginTransaction();
        foreach (
            [
                'resource_monthly_paise' => (string) $paise,
                'resource_plan_id' => $plan,
                'resource_membership_enabled' => !empty($_POST['enabled']) ? '1' : '0',
            ]
            as $key => $value
        ) {
            $pdo->prepare(
                'INSERT INTO settings(setting_key,setting_value,setting_group,is_public) VALUES(?,?,"resources",0) ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value)',
            )->execute([$key, $value]);
        }
        $pdo->commit();
        flash(
            'success',
            'Monthly membership saved. Existing subscriptions retain their agreed price.',
        );
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        flash(
            'error',
            $e instanceof DomainException
                ? $e->getMessage()
                : 'Razorpay could not create the monthly plan. Confirm that Subscriptions is enabled and your API keys are correct.',
        );
    }
    redirect('/admin/resources');
}
