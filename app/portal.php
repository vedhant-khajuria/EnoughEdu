<?php
declare(strict_types=1);

// Explicit plan contents override legacy scope rules only when enabled.
function plan_contents(?string $json): ?array
{
    $limits = json_decode($json ?? '{}', true);
    return is_array($limits) && is_array($limits['included_items'] ?? null)
        ? $limits['included_items']
        : null;
}
function plan_includes(array $contents, string $kind, int $id): bool
{
    return !empty($contents['all_' . $kind]) ||
        in_array($id, array_map('intval', (array) ($contents[$kind] ?? [])), true);
}
function plan_contents_fields(PDO $pdo, array $plan = []): void
{
    static $catalog = null;
    if ($catalog === null) {
        $catalog = [];
        foreach (
            ['tools' => 'name', 'resources' => 'title', 'notes' => 'title']
            as $table => $label
        ) {
            $catalog[$table] = $pdo
                ->query("SELECT id,$label AS label,status FROM $table ORDER BY $label,id")
                ->fetchAll();
        }
    }
    $contents = $plan ? plan_contents($plan['limits_json'] ?? null) : [];
    ?><fieldset style="margin:16px 0;padding:16px;border:1px solid #8885;border-radius:12px">
<legend>What this plan includes</legend>
    <label class="field">Access mode<select class="input" name="contents_mode">
<option value="legacy" <?= $contents ===
    null
        ? 'selected'
        : '' ?>>Use existing plan-type access</option>
<option value="selected" <?= $contents !== null ? 'selected' : '' ?>>Only the items selected below</option>
</select>
</label>
    <p class="muted">Choose “Only the items selected below” to use these selections. Changes also apply to current subscribers. Free items remain available to everyone.</p>
    <?php foreach (
        $catalog
        as $kind => $items
    ): ?><details style="margin:12px 0">
<summary style="cursor:pointer"><?= e(
    ucfirst($kind),
) ?> — choose included items</summary>
<label style="display:block;padding:8px">
<input type="checkbox" name="all_<?= $kind ?>" value="1" <?= !empty(
    $contents['all_' . $kind]
)
    ? 'checked'
    : '' ?>> All <?= e(
    $kind,
) ?> (including future additions)</label>
<div style="max-height:240px;overflow:auto"><?php
 foreach (
     $items
     as $item
 ): ?><label style="display:block;padding:6px">
<input type="checkbox" name="included_<?= $kind ?>[]" value="<?= (int) $item[
    'id'
] ?>" <?= in_array((int) $item['id'], array_map('intval', (array) ($contents[$kind] ?? [])), true)
    ? 'checked'
    : '' ?>> <?= e($item['label']) ?> <small class="muted">(<?= e(
     $item['status'],
 ) ?> · #<?= (int) $item['id'] ?>)</small>
</label><?php endforeach;
 if (!$items): ?><p class="muted">No <?= e($kind) ?> added yet.</p><?php endif;
 ?></div>
</details><?php endforeach; ?>
    <p class="muted">Other benefits: enter one per line in the features field below. These are descriptions; arrange delivery separately for benefits such as support or mentoring.</p>
</fieldset><?php
}
function save_plan_contents(PDO $pdo, int $id, array &$features, string $type): string
{
    $limits = [];
    if ($id) {
        $q = $pdo->prepare('SELECT limits_json FROM plans WHERE id=?');
        $q->execute([$id]);
        $limits = json_decode((string) $q->fetchColumn(), true);
        if (!is_array($limits)) {
            $limits = [];
        }
    }
    if (($_POST['contents_mode'] ?? 'legacy') === 'selected') {
        if ($type === 'tool') {
            throw new DomainException(
                'For a selectable bundle, choose Full platform, Branch, Semester or Tool category. Single tool is the existing individual-tool listing.',
            );
        }
        $contents = [];
        $labels = [];
        foreach (
            ['tools' => 'name', 'resources' => 'title', 'notes' => 'title']
            as $table => $label
        ) {
            $raw = $_POST['included_' . $table] ?? [];
            if (!is_array($raw)) {
                throw new DomainException('Invalid included items.');
            }
            $ids = [];
            foreach ($raw as $value) {
                if (!is_scalar($value) || !ctype_digit((string) $value) || (int) $value < 1) {
                    throw new DomainException('Invalid included item.');
                }
                $ids[] = (int) $value;
            }
            $ids = array_values(array_unique($ids));
            $contents['all_' . $table] = !empty($_POST['all_' . $table]);
            $contents[$table] = $ids;
            if ($contents['all_' . $table]) {
                $labels[] = 'All ' . $table;
            }
            if ($ids) {
                $q = $pdo->prepare(
                    "SELECT id,$label AS label FROM $table WHERE id IN (" .
                        implode(',', array_fill(0, count($ids), '?')) .
                        ") ORDER BY $label,id",
                );
                $q->execute($ids);
                $items = $q->fetchAll();
                if (count($items) !== count($ids)) {
                    throw new DomainException(
                        'An included item was removed. Refresh the page and select again.',
                    );
                }
                if (!$contents['all_' . $table]) {
                    foreach ($items as $item) {
                        $labels[] = ucfirst(rtrim($table, 's')) . ': ' . $item['label'];
                    }
                }
            }
        }
        $contents['other_features'] = $features;
        $limits['included_items'] = $contents;
        $features = array_values(array_unique(array_merge($labels, $features)));
    } else {
        unset($limits['included_items']);
    }
    return json_encode((object) $limits, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
}

function paid_access(?string $toolSlug = null): bool
{
    if ($toolSlug !== null) {
        $tool = te_tool($toolSlug);
        if (!$tool) {
            return false;
        }
        if (te_free($tool)) {
            return true;
        }
    }
    $u = user();
    if (!$u) {
        return false;
    }
    if (($u['role'] ?? 'student') === 'admin') {
        return true;
    }
    $pdo = db();
    if (!$pdo) {
        return false;
    }
    if ($toolSlug !== null) {
        $q = $pdo->prepare(
            'SELECT 1 FROM user_tools ut JOIN tools t ON t.id=ut.tool_id WHERE ut.user_id=? AND t.slug=? AND (ut.expires_at IS NULL OR ut.expires_at>NOW()) LIMIT 1',
        );
        $q->execute([$u['id'], $toolSlug]);
        if ($q->fetchColumn()) {
            return true;
        }
        $q = $pdo->prepare(
            'SELECT id,category FROM tools WHERE slug=? AND status="active" LIMIT 1',
        );
        $q->execute([$toolSlug]);
        $accessTool = $q->fetch();
        if (!$accessTool) {
            return false;
        }
        $category = (string) $accessTool['category'];
        $q = $pdo->prepare(
            'SELECT s.scope_type,s.scope_id,p.limits_json FROM subscriptions s LEFT JOIN plans p ON p.id=s.plan_id WHERE s.user_id=? AND s.status="active" AND s.starts_at<=NOW() AND (s.expires_at IS NULL OR s.expires_at>NOW())',
        );
        $q->execute([$u['id']]);
        foreach ($q->fetchAll() as $access) {
            $contents = plan_contents($access['limits_json'] ?? null);
            if ($contents !== null) {
                if (plan_includes($contents, 'tools', (int) $accessTool['id'])) {
                    return true;
                }
                continue;
            }
            $scope = $access['scope_type'];
            $scopeId = (string) ($access['scope_id'] ?? '');
            if ($scope === 'degree') {
                return true;
            }
            if ($scope === 'category' && ($scopeId === 'all' || $scopeId === $category)) {
                return true;
            }
            if (
                in_array($scope, ['branch', 'semester'], true) &&
                in_array($category, ['academic', 'productivity', 'engineering'], true)
            ) {
                return true;
            }
        }
        return false;
    }
    $q = $pdo->prepare(
        'SELECT 1 FROM subscriptions WHERE user_id=? AND scope_type="degree" AND status="active" AND starts_at<=NOW() AND (expires_at IS NULL OR expires_at>NOW()) LIMIT 1',
    );
    $q->execute([$u['id']]);
    return (bool) $q->fetchColumn();
}

function has_any_paid_access(PDO $pdo, int $userId): bool
{
    $q = $pdo->prepare(
        'SELECT 1 FROM subscriptions WHERE user_id=? AND status="active" AND starts_at<=NOW() AND (expires_at IS NULL OR expires_at>NOW()) LIMIT 1',
    );
    $q->execute([$userId]);
    if ($q->fetchColumn()) {
        return true;
    }
    $q = $pdo->prepare(
        'SELECT 1 FROM user_tools WHERE user_id=? AND (expires_at IS NULL OR expires_at>NOW()) LIMIT 1',
    );
    $q->execute([$userId]);
    return (bool) $q->fetchColumn();
}

function locked_feature_page(string $name, string $description, string $checkout): void
{
    $u = user();
    page_start($name, $description);
    ?>
    <section class="section">
<div class="container" style="max-width:920px">
      <a href="/tools" class="muted">← Browse all features</a>
      <div class="locked-feature panel">
        <div class="lock-symbol">🔒</div>
        <span class="eyebrow">Membership feature</span>
        <h1><?= e($name) ?></h1>
        <p><?= e(ai_public_text($description)) ?></p>
        <div class="feature-preview">
<b>What this feature does</b>
<span>Fast, private and designed for engineering students.</span>
</div>
        <div class="hero-actions">
          <?php if (
              !$u
          ): ?><a class="btn btn-primary" href="/login">Log in to continue</a>
<a class="btn btn-secondary" href="/signup">Create account</a>
          <?php else: ?><a class="btn btn-primary" href="<?= e(
    $checkout,
) ?>">Unlock this feature</a>
<a class="btn btn-secondary" href="/pricing">Compare plans</a><?php endif; ?>
        </div>
        <small class="muted">The interactive tool is hidden until payment succeeds. No trial or demo data is added to your account.</small>
      </div>
    </div>
</section>
    <?php page_end();
}

function live_checkout_page(): void
{
    $requestedTool = te_tool((string) ($_GET['tool'] ?? ''));
    if (empty($_GET['plan']) && $requestedTool && te_free($requestedTool)) {
        redirect('/tools/' . $requestedTool['slug']);
    }
    $u = require_auth();
    if (($u['role'] ?? 'student') === 'admin') {
        redirect('/admin');
    }
    $pdo = db();
    if (!$pdo) {
        flash('error', 'The database is unavailable.');
        redirect('/pricing');
    }
    $tool = slug((string) ($_GET['tool'] ?? ''));
    $plan = slug((string) ($_GET['plan'] ?? ''));
    $materialKind = in_array($_GET['material_kind'] ?? '', ['note', 'resource'], true)
        ? (string) $_GET['material_kind']
        : '';
    $materialId = (int) ($_GET['material_id'] ?? 0);
    $selected = checkout_product($pdo, $plan, $tool, $materialKind, $materialId);
    if (!$selected) {
        flash('error', 'That purchase option is unavailable.');
        redirect('/pricing');
    }
    $product = (string) $selected['name'];
    $amount = (float) $selected['amount'];
    $tool = $selected['type'] === 'tool' ? (string) $selected['slug'] : '';
    $plan = $selected['type'] === 'plan' ? (string) $selected['slug'] : '';
    $materialKind = $selected['type'] === 'material' ? (string) $selected['kind'] : '';
    $materialId = $selected['type'] === 'material' ? (int) $selected['id'] : 0;
    page_start('Secure checkout', 'Complete your EnoughEdu purchase securely with Razorpay.');
    ?>
    <section class="section">
<div class="container" style="max-width:900px">
      <div class="section-head">
<span class="eyebrow">Secure Razorpay checkout</span>
<h1 style="font-size:46px;margin:18px">Complete your purchase</h1>
<p>Validate an optional coupon first, review the updated total, then continue securely.</p>
</div>
      <div class="calculator">
        <form class="panel checkout-form" method="post" action="/payment/create" data-checkout-form>
          <?= csrf_field() ?>
          <input type="hidden" name="tool_slug" value="<?= e($tool) ?>">
          <input type="hidden" name="plan_slug" value="<?= e($plan) ?>">
          <input type="hidden" name="material_kind" value="<?= e($materialKind) ?>">
          <input type="hidden" name="material_id" value="<?= $materialId ?>">
          <h3>Billing details</h3>
          <div class="field">
<label>Full name</label>
<input class="input" name="name" value="<?= e(
              $u['name'],
          ) ?>" required maxlength="120">
</div>
          <div class="field">
<label>Email</label>
<input class="input" type="email" name="email" value="<?= e(
              $u['email'],
          ) ?>" readonly>
</div>
          <div class="field">
<label>Phone</label>
<input class="input" name="phone" pattern="[6-9][0-9]{9}" placeholder="10-digit mobile number" required>
</div>
          <div class="field">
<label for="checkout-coupon">Coupon code <span class="muted">(optional)</span>
</label>
<div class="coupon-apply-row">
<input class="input" id="checkout-coupon" name="coupon" maxlength="50" autocomplete="off" placeholder="Enter coupon code" data-coupon-input>
<button class="btn btn-secondary" type="button" data-coupon-validate>Apply coupon</button>
</div>
<small class="coupon-feedback muted" data-coupon-feedback role="status" aria-live="polite">Enter a code and select Apply coupon to see your final amount.</small>
</div>
          <button class="btn btn-primary btn-block" data-checkout-submit>Continue to Razorpay →</button>
          <p class="muted" style="font-size:11px;text-align:center">Prices and coupons are verified again on the server before Razorpay opens. EnoughEdu never stores card or UPI credentials.</p>
        </form>
        <aside class="result-box checkout-summary">
          <small>ORDER SUMMARY</small>
<h2 style="font-size:32px;margin:18px 0"><?= e(
              $product,
          ) ?></h2>
          <div class="list-item" style="border-color:#ffffff30">
<span>Subtotal</span>
<b data-summary-subtotal>₹<?= e(
              number_format($amount, 2),
          ) ?></b>
</div>
          <div class="list-item" style="border-color:#ffffff30">
<span>Coupon</span>
<b data-summary-coupon>Not applied</b>
</div>
          <div class="list-item" style="border-color:#ffffff30">
<span>Taxes</span>
<b>Included</b>
</div>
          <div style="display:flex;justify-content:space-between;font-size:23px;margin-top:25px">
<b>Amount to pay</b>
<b data-summary-total>₹<?= e(
              number_format($amount, 2),
          ) ?></b>
</div>
        </aside>
      </div>
    </div>
</section><?php page_end();
}

function institution_search_api(): never
{
    $u = require_auth();
    header('Content-Type: application/json; charset=utf-8');
    if (($u['role'] ?? 'student') === 'admin') {
        http_response_code(403);
        echo json_encode(['results' => []]);
        exit();
    }
    $query = trim((string) ($_GET['q'] ?? ''));
    if (strlen($query) < 2) {
        echo json_encode(['results' => []]);
        exit();
    }
    $query = substr($query, 0, 80);
    $url =
        'https://api.openalex.org/institutions?' .
        http_build_query(
            [
                'search' => $query,
                'filter' => 'country_code:in,type:education,status:active',
                'per_page' => 20,
                'select' => 'id,display_name,geo,type',
            ],
            '',
            '&',
            PHP_QUERY_RFC3986,
        );
    $status = 0;
    $payload = null;
    if (function_exists('curl_init')) {
        [$status, $payload] = google_oauth_request($url);
    } elseif ((bool) ini_get('allow_url_fopen')) {
        $context = stream_context_create([
            'http' => [
                'timeout' => 15,
                'ignore_errors' => true,
                'header' =>
                    "Accept: application/json\r\nUser-Agent: EnoughEdu/1.0 (mailto:hello@vedhant.in)\r\n",
            ],
        ]);
        $body = @file_get_contents($url, false, $context);
        $status = preg_match('#\s(\d{3})\s#', $http_response_header[0] ?? '', $match)
            ? (int) $match[1]
            : 0;
        $payload = is_string($body) ? json_decode($body, true) : null;
    }
    if ($status < 200 || $status >= 300 || !is_array($payload)) {
        http_response_code(503);
        echo json_encode([
            'results' => [],
            'message' => 'The institution directory is temporarily unavailable. Please try again.',
        ]);
        exit();
    }
    $results = [];
    $choices = (array) ($_SESSION['institution_choices'] ?? []);
    foreach ((array) ($payload['results'] ?? []) as $row) {
        $id = preg_replace('#^https://openalex\.org/#', '', (string) ($row['id'] ?? ''));
        $name = trim((string) ($row['display_name'] ?? ''));
        $geo = (array) ($row['geo'] ?? []);
        if (
            !preg_match('/^I\d+$/', $id) ||
            $name === '' ||
            strtoupper((string) ($geo['country_code'] ?? '')) !== 'IN' ||
            ($row['type'] ?? '') !== 'education'
        ) {
            continue;
        }
        $city = trim((string) ($geo['city'] ?? ''));
        $region = trim((string) ($geo['region'] ?? ''));
        $location = implode(', ', array_filter([$city, $region]));
        $choice = [
            'id' => $id,
            'name' => $name,
            'city' => $city,
            'region' => $region,
            'location' => $location,
        ];
        $results[] = $choice;
        $choices[$id] = $choice;
    }
    $_SESSION['institution_choices'] = array_slice($choices, -80, null, true);
    echo json_encode(['results' => $results], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit();
}

function student_onboarding_page(): void
{
    $u = require_auth();
    if (($u['role'] ?? 'student') === 'admin') {
        redirect('/admin');
    }
    if (!empty($u['onboarding_completed_at'])) {
        redirect('/dashboard');
    }
    $pdo = db();
    if (!$pdo) {
        flash('error', 'The database is unavailable.');
        redirect('/');
    }
    $branches = $pdo
        ->query(
            'SELECT id,name,short_name FROM branches WHERE status="active" ORDER BY sort_order,name',
        )
        ->fetchAll();
    $currentYear = (int) date('Y');
    ?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Set up your workspace · EnoughEdu</title>
<link rel="icon" href="/assets/img/favicon.svg">
<link rel="stylesheet" href="<?= e(
    asset_url('/assets/css/app.css'),
) ?>"><style>[data-onboarding-step][hidden]{display:none!important}</style></head>
<body class="onboarding-body">
<main class="onboarding-shell">
<header class="onboarding-top">
<span class="onboarding-brand">
<img class="logo logo-light" src="/assets/img/logo.svg" alt="EnoughEdu">
<img class="logo logo-dark" src="/assets/img/logo-dark.svg" alt="EnoughEdu">
</span>
<span class="onboarding-safe">Private to your account</span>
</header>
<section class="onboarding-card">
<div class="onboarding-progress">
<div class="onboarding-progress-bar">
<i data-onboarding-progress>
</i>
</div>
<span data-onboarding-count aria-live="polite">Step 1 of 5</span>
</div>
<form method="post" action="/onboarding" data-onboarding-form><?= csrf_field() ?>
<section class="onboarding-step active" data-onboarding-step>
<div class="onboarding-visual">✦</div>
<span class="eyebrow">Welcome, <?= e(
    explode(' ', $u['name'])[0],
) ?></span>
<h1>Let’s shape your student workspace.</h1>
<p>Search India’s college and university directory, then select the exact institution you attend.</p>
<div class="field institution-field">
<label for="university">College or university</label>
<input type="hidden" name="university_id" data-institution-id required>
<input class="input onboarding-input" id="university" name="university" maxlength="160" required autocomplete="off" data-institution-search placeholder="Start typing your college or university…" aria-autocomplete="list" aria-controls="institution-results">
<div class="institution-results" id="institution-results" data-institution-results role="listbox" hidden>
</div>
<small class="institution-help" data-institution-help>Type at least 2 letters, then choose one result. India-wide directory powered by OpenAlex and ROR.</small>
</div>
<div class="field">
<label for="graduation_year">Expected graduation year</label>
<select class="input onboarding-input" id="graduation_year" name="graduation_year" required><?php for (
    $year = $currentYear;
    $year <= $currentYear + 8;
    $year++
): ?><option value="<?= $year ?>"><?= $year ?></option><?php endfor; ?></select>
</div>
<div class="onboarding-actions">
<span>
</span>
<button class="btn btn-primary" type="button" data-onboarding-next>Continue →</button>
</div>
</section>
<section class="onboarding-step" data-onboarding-step hidden>
<div class="onboarding-visual">⌘</div>
<span class="eyebrow">Your degree</span>
<h1>What are you studying?</h1>
<p>We’ll use this to prioritise the right notes, tools, and semester resources.</p>
<fieldset class="choice-grid branch-choices">
<legend class="sr-only">Engineering branch</legend><?php foreach (
    $branches
    as $branch
): ?><label class="choice-card">
<input type="radio" name="branch_id" value="<?= $branch[
    'id'
] ?>" required>
<span class="choice-icon"><?= e($branch['short_name'] ?: 'ENG') ?></span>
<b><?= e(
    $branch['name'],
) ?></b>
</label><?php endforeach; ?></fieldset>
<div class="field">
<label>Current semester</label>
<div class="semester-choices"><?php for (
    $semester = 1;
    $semester <= 8;
    $semester++
): ?><label>
<input type="radio" name="semester" value="<?= $semester ?>" required>
<span><?= $semester ?></span>
</label><?php endfor; ?></div>
</div>
<div class="onboarding-actions">
<button class="btn btn-secondary" type="button" data-onboarding-back>← Back</button>
<button class="btn btn-primary" type="button" data-onboarding-next>Continue →</button>
</div>
</section>
<section class="onboarding-step" data-onboarding-step hidden>
<div class="onboarding-visual">◎</div>
<span class="eyebrow">Your ambition</span>
<h1>What matters most right now?</h1>
<p>Your answers shape the priorities and next actions shown on your dashboard.</p>
<fieldset class="choice-grid goal-choices">
<legend class="sr-only">Primary study goal</legend><?php foreach (
    [
        ['Improve CGPA', 'Build stronger academic results', '↗'],
        ['Clear backlogs', 'Get every subject back on track', '✓'],
        ['Exam preparation', 'Prepare calmly and consistently', '▤'],
        ['Build career skills', 'Focus on projects and placements', '◇'],
    ]
    as $goal
): ?><label class="choice-card">
<input type="radio" name="study_goal" value="<?= e(
    $goal[0],
) ?>" required>
<span class="choice-icon"><?= $goal[2] ?></span>
<b><?= e(
    $goal[0],
) ?></b>
<small><?= e(
    $goal[1],
) ?></small>
</label><?php endforeach; ?></fieldset>
<div class="field">
<label>What is your biggest challenge?</label>
<div class="compact-choices"><?php foreach (
    [
        'Staying consistent',
        'Understanding concepts',
        'Managing deadlines',
        'Finding quality resources',
    ]
    as $challenge
): ?><label>
<input type="radio" name="biggest_challenge" value="<?= e(
    $challenge,
) ?>" required>
<span><?= e(
    $challenge,
) ?></span>
</label><?php endforeach; ?></div>
</div>
<div class="onboarding-actions">
<button class="btn btn-secondary" type="button" data-onboarding-back>← Back</button>
<button class="btn btn-primary" type="button" data-onboarding-next>Continue →</button>
</div>
</section>
<section class="onboarding-step" data-onboarding-step hidden>
<div class="onboarding-visual">▤</div>
<span class="eyebrow">How you learn</span>
<h1>Make recommendations feel like you.</h1>
<p>Tell us how you prefer to learn and how close your next exams are.</p>
<div class="field">
<label>Preferred learning style</label>
<div class="time-choices"><?php foreach (
    [
        ['Short notes', '▤'],
        ['Video lessons', '▶'],
        ['Practice questions', '✓'],
        ['Mixed approach', '✦'],
    ]
    as $style
): ?><label class="choice-card">
<input type="radio" name="learning_style" value="<?= e(
    $style[0],
) ?>" required>
<span class="choice-icon"><?= $style[1] ?></span>
<b><?= e(
    $style[0],
) ?></b>
</label><?php endforeach; ?></div>
</div>
<div class="field">
<label>When is your next major exam?</label>
<div class="compact-choices"><?php foreach (
    ['Within 2 weeks', 'Within 1 month', 'Within 3 months', 'No date yet']
    as $timeline
): ?><label>
<input type="radio" name="exam_timeline" value="<?= e(
    $timeline,
) ?>" required>
<span><?= e(
    $timeline,
) ?></span>
</label><?php endforeach; ?></div>
</div>
<div class="onboarding-actions">
<button class="btn btn-secondary" type="button" data-onboarding-back>← Back</button>
<button class="btn btn-primary" type="button" data-onboarding-next>Continue →</button>
</div>
</section>
<section class="onboarding-step" data-onboarding-step hidden>
<div class="onboarding-visual">◷</div>
<span class="eyebrow">Your rhythm</span>
<h1>Build a plan that feels realistic.</h1>
<p>Set a weekly target and your preferred study time. You can change these later.</p>
<div class="onboarding-range">
<label for="weekly_study_hours">Weekly study target <strong>
<span data-hours-output>10</span> hours</strong>
</label>
<input id="weekly_study_hours" name="weekly_study_hours" type="range" min="1" max="40" value="10" data-hours-range>
</div>
<div class="field">
<label>When do you study best?</label>
<div class="time-choices"><?php foreach (
    [['Early morning', '☀'], ['Daytime', '◐'], ['Evening', '◒'], ['Late night', '☾']]
    as $time
): ?><label class="choice-card">
<input type="radio" name="preferred_study_time" value="<?= e(
    $time[0],
) ?>" required>
<span class="choice-icon"><?= $time[1] ?></span>
<b><?= e(
    $time[0],
) ?></b>
</label><?php endforeach; ?></div>
</div>
<div class="field">
<label for="target_cgpa">Target CGPA</label>
<input class="input onboarding-input" id="target_cgpa" name="target_cgpa" type="number" min="4" max="10" step="0.1" value="8.0" required>
</div>
<div class="onboarding-actions">
<button class="btn btn-secondary" type="button" data-onboarding-back>← Back</button>
<button class="btn btn-primary" type="submit">Build my dashboard ✦</button>
</div>
</section>
</form>
</section>
<p class="onboarding-foot">Your answers are used only to personalize EnoughEdu.</p>
</main>
<script src="<?= e(
    asset_url('/assets/js/app.js'),
) ?>">
</script>
</body>
</html><?php
}

function handle_student_onboarding(): never
{
    $u = require_auth();
    if (($u['role'] ?? 'student') === 'admin') {
        redirect('/admin');
    }
    verify_csrf();
    $pdo = db();
    if (!$pdo) {
        flash('error', 'The database is unavailable.');
        redirect('/onboarding');
    }
    $institutionId = trim((string) ($_POST['university_id'] ?? ''));
    $choice = $_SESSION['institution_choices'][$institutionId] ?? null;
    $university = trim((string) ($_POST['university'] ?? ''));
    $branchId = (int) ($_POST['branch_id'] ?? 0);
    $semester = (int) ($_POST['semester'] ?? 0);
    $graduationYear = (int) ($_POST['graduation_year'] ?? 0);
    $goal = trim((string) ($_POST['study_goal'] ?? ''));
    $challenge = trim((string) ($_POST['biggest_challenge'] ?? ''));
    $learningStyle = trim((string) ($_POST['learning_style'] ?? ''));
    $examTimeline = trim((string) ($_POST['exam_timeline'] ?? ''));
    $hours = (int) ($_POST['weekly_study_hours'] ?? 0);
    $studyTime = trim((string) ($_POST['preferred_study_time'] ?? ''));
    $target = (float) ($_POST['target_cgpa'] ?? 0);
    $goals = ['Improve CGPA', 'Clear backlogs', 'Exam preparation', 'Build career skills'];
    $challenges = [
        'Staying consistent',
        'Understanding concepts',
        'Managing deadlines',
        'Finding quality resources',
    ];
    $styles = ['Short notes', 'Video lessons', 'Practice questions', 'Mixed approach'];
    $timelines = ['Within 2 weeks', 'Within 1 month', 'Within 3 months', 'No date yet'];
    $times = ['Early morning', 'Daytime', 'Evening', 'Late night'];
    $currentYear = (int) date('Y');
    $q = $pdo->prepare('SELECT COUNT(*) FROM branches WHERE id=? AND status="active"');
    $q->execute([$branchId]);
    $validBranch = (bool) $q->fetchColumn();
    if (
        !is_array($choice) ||
        !hash_equals((string) $choice['name'], $university) ||
        !$validBranch ||
        $semester < 1 ||
        $semester > 8 ||
        $graduationYear < $currentYear ||
        $graduationYear > $currentYear + 8 ||
        !in_array($goal, $goals, true) ||
        !in_array($challenge, $challenges, true) ||
        !in_array($learningStyle, $styles, true) ||
        !in_array($examTimeline, $timelines, true) ||
        $hours < 1 ||
        $hours > 40 ||
        !in_array($studyTime, $times, true) ||
        $target < 4 ||
        $target > 10
    ) {
        flash(
            'error',
            'Please select your institution from the suggestions and complete every question.',
        );
        redirect('/onboarding');
    }
    $pdo->beginTransaction();
    try {
        $save = $pdo->prepare(
            'UPDATE users SET university=?,university_id=?,university_city=?,university_region=?,branch_id=?,semester=?,graduation_year=?,study_goal=?,biggest_challenge=?,learning_style=?,exam_timeline=?,weekly_study_hours=?,preferred_study_time=?,target_cgpa=?,onboarding_completed_at=NOW() WHERE id=? AND onboarding_completed_at IS NULL',
        );
        $save->execute([
            $choice['name'],
            $choice['id'],
            $choice['city'],
            $choice['region'],
            $branchId,
            $semester,
            $graduationYear,
            $goal,
            $challenge,
            $learningStyle,
            $examTimeline,
            $hours,
            $studyTime,
            $target,
            $u['id'],
        ]);
        if ($save->rowCount()) {
            $pdo->prepare(
                'INSERT INTO activity_history(user_id,action,entity_type) VALUES(?,"onboarding_completed","profile")',
            )->execute([$u['id']]);
        }
        $pdo->commit();
        unset($_SESSION['institution_choices']);
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        if (config('app.debug')) {
            error_log($e->getMessage());
        }
        flash('error', 'Your answers could not be saved. Please try again.');
        redirect('/onboarding');
    }
    flash('success', 'Your workspace is ready. Welcome to EnoughEdu!');
    redirect('/dashboard');
}

function student_counts(PDO $pdo, int $userId): array
{
    $queries = [
        'tasks' => 'SELECT COUNT(*) FROM planner_tasks WHERE user_id=?',
        'done' => 'SELECT COUNT(*) FROM planner_tasks WHERE user_id=? AND status="done"',
        'purchases' => 'SELECT COUNT(*) FROM orders WHERE user_id=? AND status="paid"',
        'downloads' => 'SELECT COUNT(*) FROM downloads WHERE user_id=?',
    ];
    $out = [];
    foreach ($queries as $key => $sql) {
        $q = $pdo->prepare($sql);
        $q->execute([$userId]);
        $out[$key] = (int) $q->fetchColumn();
    }
    return $out;
}

function student_dashboard_page(string $section = 'dashboard'): void
{
    $u = require_auth();
    if (($u['role'] ?? 'student') === 'admin') {
        if ($section === 'profile') {
            admin_profile_page();
            return;
        }
        redirect('/admin');
    }
    if (empty($u['onboarding_completed_at'])) {
        redirect('/onboarding');
    }
    $allowed = [
        'dashboard',
        'degree',
        'planner',
        'resources',
        'tools',
        'purchases',
        'activity',
        'profile',
    ];
    if (!in_array($section, $allowed, true)) {
        $section = 'dashboard';
    }
    $pdo = db();
    $title =
        $section === 'dashboard'
            ? 'Welcome, ' . explode(' ', $u['name'])[0]
            : ucfirst(str_replace('-', ' ', $section));
    app_start($title, $section);
    if (
        !$pdo
    ) { ?><div class="alert alert-error">The database is unavailable. Check the private database settings.</div><?php
app_end();
return;
}

    if ($section === 'dashboard') {

        $counts = student_counts($pdo, (int) $u['id']);
        $q = $pdo->prepare('SELECT name,short_name FROM branches WHERE id=?');
        $q->execute([(int) $u['branch_id']]);
        $studentBranch = $q->fetch();
        $q = $pdo->prepare(
            'SELECT sgpa,semester FROM academic_records WHERE user_id=? ORDER BY semester DESC LIMIT 1',
        );
        $q->execute([$u['id']]);
        $latest = $q->fetch();
        $q = $pdo->prepare(
            'SELECT title,due_at,priority,status FROM planner_tasks WHERE user_id=? AND status<>"done" ORDER BY due_at IS NULL,due_at ASC LIMIT 5',
        );
        $q->execute([$u['id']]);
        $next = $q->fetchAll();
        $hasAccess = has_any_paid_access($pdo, (int) $u['id']);
        $recommendations = match ($u['study_goal'] ?? '') {
            'Improve CGPA' => [
                [
                    'Record your latest SGPA',
                    'Track your baseline and see what to improve.',
                    '/dashboard/degree',
                    '↗',
                ],
                [
                    'Plan focused study blocks',
                    'Turn your weekly target into real tasks.',
                    '/dashboard/planner',
                    '◷',
                ],
                [
                    'Open academic calculators',
                    'Preview the CGPA and semester planning tools.',
                    '/tools',
                    '∑',
                ],
            ],
            'Clear backlogs' => [
                [
                    'Map every pending subject',
                    'Add backlog subjects and deadlines to your planner.',
                    '/dashboard/planner',
                    '✓',
                ],
                [
                    'Find branch resources',
                    'Focus on notes and previous papers for your semester.',
                    '/resources',
                    '▤',
                ],
                [
                    'Measure the impact',
                    'Preview the Backlog Impact Calculator.',
                    '/tools/backlog-impact-calculator',
                    '⌁',
                ],
            ],
            'Exam preparation' => [
                [
                    'Build your exam plan',
                    'Create revision tasks around your exam timeline.',
                    '/dashboard/planner',
                    '◷',
                ],
                [
                    'Find revision material',
                    'Browse notes and papers for your branch and semester.',
                    '/resources',
                    '▤',
                ],
                [
                    'Practice your way',
                    'Use resources suited to ' .
                    ($u['learning_style'] ?? 'your learning style') .
                    '.',
                    '/tools',
                    '✓',
                ],
            ],
            'Build career skills' => [
                [
                    'Strengthen your profile',
                    'Preview career and resume tools.',
                    '/tools/resume-builder',
                    '◇',
                ],
                [
                    'Plan a skill project',
                    'Add one focused project milestone this week.',
                    '/dashboard/planner',
                    '✓',
                ],
                [
                    'Explore placement tools',
                    'See the ATS and career preparation features.',
                    '/tools',
                    '↗',
                ],
            ],
            default => [
                [
                    'Create your first plan',
                    'Add a focused study task for this week.',
                    '/dashboard/planner',
                    '◷',
                ],
                [
                    'Explore your resources',
                    'Browse materials for your semester.',
                    '/resources',
                    '▤',
                ],
                ['See student tools', 'Preview tools designed for your degree.', '/tools', '✦'],
            ],
        };
        ?>
        <section class="student-profile-banner">
<div>
<span class="eyebrow">Your academic workspace</span>
<h2><?= e(
            $u['university'] ?: 'Your university',
        ) ?> · Semester <?= e($u['semester']) ?></h2>
<p><?= e(
    $studentBranch['name'] ?? 'Engineering',
) ?> · <?= e(
     implode(', ', array_filter([$u['university_city'] ?? '', $u['university_region'] ?? ''])),
 ) ?> · Graduating <?= e($u['graduation_year'] ?: '—') ?></p>
<div class="profile-tags">
<span><?= e(
    $u['learning_style'] ?: 'Mixed approach',
) ?></span>
<span><?= e($u['exam_timeline'] ?: 'No exam date') ?></span>
<span>Challenge: <?= e(
    $u['biggest_challenge'] ?: 'Getting started',
) ?></span>
</div>
</div>
<div class="profile-focus">
<small>Current focus</small>
<strong><?= e(
    $u['study_goal'] ?: 'Build a stronger semester',
) ?></strong>
<span><?= e($u['weekly_study_hours'] ?: 0) ?> hrs/week · <?= e(
     $u['preferred_study_time'] ?: 'Flexible schedule',
 ) ?> · Target <?= e(
     number_format((float) ($u['target_cgpa'] ?: 0), 1),
 ) ?> CGPA</span>
</div>
</section>
<div class="kpi-grid">
<div class="kpi">
<span>Tasks created</span>
<strong><?= $counts[
     'tasks'
 ] ?></strong>
<small class="muted">Your records only</small>
</div>
<div class="kpi">
<span>Tasks completed</span>
<strong><?= $counts[
    'done'
] ?></strong>
<small class="muted">Start at zero, grow at your pace</small>
</div>
<div class="kpi">
<span>Paid purchases</span>
<strong><?= $counts[
    'purchases'
] ?></strong>
<small class="muted">Successful orders</small>
</div>
<div class="kpi">
<span>Latest SGPA</span>
<strong><?= $latest
    ? e(number_format((float) $latest['sgpa'], 2))
    : '—' ?></strong>
<small class="muted"><?= $latest
    ? 'Semester ' . (int) $latest['semester']
    : 'No academic record yet' ?></small>
</div>
</div>
        <?php if (
            !$counts['tasks'] &&
            !$latest &&
            !$counts['purchases']
        ): ?><section class="panel onboarding-panel">
<span class="eyebrow">Personalized starting point</span>
<h2>Built around <?= e(
    $u['study_goal'] ?: 'your semester',
) ?></h2>
<p class="muted">Your workspace is empty and private. These next steps are based on the answers you selected.</p>
<div class="recommendation-grid"><?php foreach (
    $recommendations
    as $item
): ?><a class="recommendation-card" href="<?= e(
    $item[2],
) ?>">
<span><?= $item[3] ?></span>
<div>
<b><?= e($item[0]) ?></b>
<small><?= e(
    $item[1],
) ?></small>
</div>
<i>→</i>
</a><?php endforeach; ?></div>
</section><?php endif; ?>
        <div class="dash-layout">
<section class="panel">
<div class="panel-head">
<h3>Upcoming tasks</h3>
<a class="card-link" href="/dashboard/planner">Manage planner</a>
</div><?php
        foreach ($next as $task): ?><div class="list-item">
<span>
<b><?= e(
    $task['title'],
) ?></b>
<small class="muted" style="display:block"><?= e(
    $task['due_at'] ? date('d M Y, g:i A', strtotime($task['due_at'])) : 'No due date',
) ?></small>
</span>
<span class="badge <?= ($task['priority'] ?? 'medium') === 'high'
    ? 'danger'
    : '' ?>"><?= e($task['priority']) ?></span>
</div><?php endforeach;
        if (
            !$next
        ): ?><div class="empty">No upcoming tasks. Your planner is ready for the first one.</div><?php endif;
        ?></section>
<aside class="panel">
<h3>Tool access</h3>
<p class="muted"><?= $hasAccess
    ? 'Your active purchase unlocks the tools included in your account.'
    : 'No tool access is active yet. You can browse features, but tools stay locked until payment succeeds.' ?></p>
<a class="btn <?= $hasAccess
    ? 'btn-secondary'
    : 'btn-primary' ?> btn-block" href="/dashboard/tools"><?= $hasAccess
     ? 'Open my tools'
     : 'View plans' ?></a>
</aside>
</div>
        <?php
    } elseif ($section === 'planner') {

        $q = $pdo->prepare(
            'SELECT * FROM planner_tasks WHERE user_id=? ORDER BY status="done",due_at IS NULL,due_at ASC,id DESC',
        );
        $q->execute([$u['id']]);
        $tasks = $q->fetchAll();
        ?>
        <div class="dash-layout">
<section class="panel">
<h3>Add a task</h3>
<form method="post" action="/dashboard/planner/add"><?= csrf_field() ?><div class="field">
<label>Task</label>
<input class="input" name="title" maxlength="190" required placeholder="Example: Revise Unit 1">
</div>
<div class="form-row">
<div class="field">
<label>Subject</label>
<input class="input" name="subject" maxlength="160">
</div>
<div class="field">
<label>Due date</label>
<input class="input" type="datetime-local" name="due_at">
</div>
</div>
<div class="field">
<label>Priority</label>
<select class="input" name="priority">
<option value="medium">Medium</option>
<option value="high">High</option>
<option value="low">Low</option>
</select>
</div>
<button class="btn btn-primary">Add task</button>
</form>
</section>
<section class="panel">
<div class="panel-head">
<h3>My tasks</h3>
<span class="tag"><?= count(
    $tasks,
) ?> total</span>
</div><?php
 foreach ($tasks as $task): ?><div class="list-item">
<span>
<b style="<?= $task['status'] === 'done'
    ? 'text-decoration:line-through'
    : '' ?>"><?= e($task['title']) ?></b>
<small class="muted" style="display:block"><?= e(
    $task['subject'] ?: 'General',
) ?> · <?= e(
     $task['due_at'] ? date('d M Y', strtotime($task['due_at'])) : 'No deadline',
 ) ?></small>
</span>
<div style="display:flex;gap:6px">
<form method="post" action="/dashboard/planner/toggle"><?= csrf_field() ?><input type="hidden" name="id" value="<?= $task[
    'id'
] ?>">
<button class="btn btn-secondary btn-sm"><?= $task['status'] === 'done'
    ? 'Reopen'
    : 'Complete' ?></button>
</form>
<form method="post" action="/dashboard/planner/delete" onsubmit="return confirm('Delete this task?')"><?= csrf_field() ?><input type="hidden" name="id" value="<?= $task[
    'id'
] ?>">
<button class="btn btn-secondary btn-sm">Delete</button>
</form>
</div>
</div><?php endforeach;
 if (!$tasks): ?><div class="empty">No tasks yet.</div><?php endif;
 ?></section>
</div>
        <?php
    } elseif ($section === 'degree') {

        $q = $pdo->prepare('SELECT * FROM academic_records WHERE user_id=? ORDER BY semester');
        $q->execute([$u['id']]);
        $records = $q->fetchAll();
        ?>
        <div class="dash-layout">
<section class="panel">
<h3>Add or update semester result</h3>
<form method="post" action="/dashboard/degree/save"><?= csrf_field() ?><div class="form-row">
<div class="field">
<label>Semester</label>
<select class="input" name="semester"><?php for (
    $i = 1;
    $i <= 8;
    $i++
): ?><option value="<?= $i ?>">Semester <?= $i ?></option><?php endfor; ?></select>
</div>
<div class="field">
<label>SGPA</label>
<input class="input" type="number" name="sgpa" min="0" max="10" step=".01" required>
</div>
</div>
<div class="form-row">
<div class="field">
<label>Credits earned</label>
<input class="input" type="number" name="credits_earned" min="0" value="0">
</div>
<div class="field">
<label>Backlogs</label>
<input class="input" type="number" name="backlogs" min="0" value="0">
</div>
</div>
<button class="btn btn-primary">Save result</button>
</form>
</section>
<section class="panel">
<h3>My academic records</h3>
<div class="table-wrap">
<table class="table">
<thead>
<tr>
<th>Semester</th>
<th>SGPA</th>
<th>Credits</th>
<th>Backlogs</th>
</tr>
</thead>
<tbody><?php
foreach ($records as $r): ?><tr>
<td><?= $r['semester'] ?></td>
<td><?= e(
    number_format((float) $r['sgpa'], 2),
) ?></td>
<td><?= $r['credits_earned'] ?></td>
<td><?= $r['backlogs'] ?></td>
</tr><?php endforeach;
if (!$records): ?><tr>
<td colspan="4" class="empty">No semester results yet.</td>
</tr><?php endif;
?></tbody>
</table>
</div>
</section>
</div>
        <?php
    } elseif ($section === 'purchases') {

        $q = $pdo->prepare(
            'SELECT order_number,product_name,amount,status,created_at FROM orders WHERE user_id=? ORDER BY id DESC',
        );
        $q->execute([$u['id']]);
        $orders = $q->fetchAll();
        ?>
        <section class="panel">
<div class="panel-head">
<h3>My purchases</h3>
<a class="btn btn-primary btn-sm" href="/dashboard/tools">View plans</a>
</div>
<div class="table-wrap">
<table class="table">
<thead>
<tr>
<th>Order</th>
<th>Product</th>
<th>Date</th>
<th>Amount</th>
<th>Status</th>
</tr>
</thead>
<tbody><?php
        foreach ($orders as $o): ?><tr>
<td><?= e($o['order_number']) ?></td>
<td><?= e(
    $o['product_name'],
) ?></td>
<td><?= e(date('d M Y', strtotime($o['created_at']))) ?></td>
<td>₹<?= e(
    number_format((float) $o['amount'], 2),
) ?></td>
<td>
<span class="badge <?= $o['status'] === 'failed'
    ? 'danger'
    : ($o['status'] === 'pending'
        ? 'warn'
        : '') ?>"><?= e($o['status']) ?></span>
</td>
</tr><?php endforeach;
        if (!$orders): ?><tr>
<td colspan="5" class="empty">No purchases yet.</td>
</tr><?php endif;
        ?></tbody>
</table>
</div>
</section>
        <?php
    } elseif ($section === 'tools') {

        $q = $pdo->prepare(
            "SELECT s.plan_id,s.scope_type,s.scope_id,s.starts_at,s.expires_at,COALESCE(p.name,CONCAT(UCASE(LEFT(s.scope_type,1)),SUBSTRING(s.scope_type,2),' access')) plan_name,p.slug,p.plan_type FROM subscriptions s LEFT JOIN plans p ON p.id=s.plan_id WHERE s.user_id=? AND s.status='active' AND s.starts_at<=NOW() AND (s.expires_at IS NULL OR s.expires_at>NOW()) ORDER BY s.expires_at IS NULL DESC,s.expires_at",
        );
        $q->execute([$u['id']]);
        $activePlans = $q->fetchAll();
        $q = $pdo->prepare(
            "SELECT t.name,t.slug,t.category,ut.granted_at,ut.expires_at FROM user_tools ut JOIN tools t ON t.id=ut.tool_id WHERE ut.user_id=? AND t.status='active' AND (ut.expires_at IS NULL OR ut.expires_at>NOW()) ORDER BY t.name",
        );
        $q->execute([$u['id']]);
        $activeTools = $q->fetchAll();
        $plans = $pdo
            ->query(
                'SELECT id,name,slug,plan_type,price,billing_period,features FROM plans WHERE status="active" AND plan_type<>"tool" ORDER BY FIELD(plan_type,"full","branch","semester","category"),price DESC',
            )
            ->fetchAll();
        $activePlanIds = [];
        foreach ($activePlans as $activePlan) {
            if (!empty($activePlan['plan_id'])) {
                $activePlanIds[(int) $activePlan['plan_id']] = true;
            }
        }
        ?>
        <section class="panel">
<div class="panel-head">
<div>
<h3>Your active access</h3>
<p class="muted">Purchased plans and individual tools appear here after payment is confirmed.</p>
</div>
<a class="btn btn-primary btn-sm" href="/tools">See all tools</a>
</div>
          <?php if ($activePlans || $activeTools): ?><div class="access-summary-grid">
            <?php foreach (
                $activePlans
                as $access
            ): ?><article class="access-card">
<span class="badge">Active plan</span>
<h3><?= e(
    $access['plan_name'],
) ?></h3>
<p><?= e(ucfirst($access['scope_type'])) ?> access<?= $access['scope_id'] &&
 $access['scope_id'] !== 'all'
     ? ' · ' . e($access['scope_id'])
     : '' ?></p>
<p><?= $access['expires_at']
    ? 'Available until ' . e(date('d M Y', strtotime($access['expires_at'])))
    : 'No expiry date' ?></p>
<a class="btn btn-secondary btn-sm" href="/tools">Open included tools</a>
</article><?php endforeach; ?>
            <?php foreach (
                $activeTools
                as $tool
            ): ?><article class="access-card">
<span class="badge">Purchased tool</span>
<h3><?= e(
    $tool['name'],
) ?></h3>
<p><?= e(ucfirst($tool['category'])) ?> · <?= $tool['expires_at']
     ? 'until ' . e(date('d M Y', strtotime($tool['expires_at'])))
     : 'lifetime access' ?></p>
<a class="btn btn-secondary btn-sm" href="/tools/<?= e(
    $tool['slug'],
) ?>">Open tool</a>
</article><?php endforeach; ?>
          </div><?php else: ?><div class="empty">
<h3>No active plan yet</h3>
<p>Choose a plan below or purchase one tool from the complete catalogue.</p>
<a class="btn btn-secondary" href="/tools">Browse individual tools</a>
</div><?php endif; ?>
        </section>
        <section class="panel" style="margin-top:18px">
<div class="panel-head">
<div>
<h3>Choose a plan</h3>
<p class="muted">Prices and features are kept current by EnoughEdu.</p>
</div>
<a class="card-link" href="/dashboard/purchases">Purchase history</a>
</div>
          <div class="dashboard-plan-grid"><?php foreach ($plans as $plan):

              $features = json_decode((string) ($plan['features'] ?? '[]'), true);
              $features = is_array($features) ? $features : [];
              $period =
                  [
                      'one_time' => 'one-time',
                      'semester' => '/ semester',
                      'monthly' => '/ month',
                      'yearly' => '/ year',
                  ][$plan['billing_period']] ?? '';
              $isActive = isset($activePlanIds[(int) $plan['id']]);
              ?><article class="dashboard-plan-card">
<span class="tag"><?= e(
    ucfirst($plan['plan_type']),
) ?> plan</span>
<h3><?= e($plan['name']) ?></h3>
<div class="price">₹<?= e(
    number_format(
        (float) $plan['price'],
        (float) $plan['price'] === (float) (int) $plan['price'] ? 0 : 2,
    ),
) ?> <small><?= e($period) ?></small>
</div>
<ul class="features"><?php
foreach ($features as $feature): ?><li><?= e(
    ai_public_text((string) $feature),
) ?></li><?php endforeach;
if (!$features): ?><li>EnoughEdu member access</li><?php endif;
?></ul><?php if (
    $isActive
): ?><span class="badge">Currently active</span><?php else: ?><a class="btn btn-primary btn-block" href="/checkout?plan=<?= e(
    $plan['slug'],
) ?>">Purchase <?= e($plan['name']) ?></a><?php endif; ?></article><?php
          endforeach; ?></div>
          <?php if (
              !$plans
          ): ?><div class="empty">No subscription plans are currently available.</div><?php endif; ?>
          <p style="text-align:center;margin:24px 0 0">
<a class="btn btn-secondary" href="/tools">See all tools and individual prices</a>
</p>
        </section><?php
    } elseif ($section === 'resources') {

        $q = $pdo->prepare('SELECT COUNT(*) FROM bookmarks WHERE user_id=?');
        $q->execute([$u['id']]);
        $bookmarks = (int) $q->fetchColumn();
        $q = $pdo->prepare('SELECT COUNT(*) FROM downloads WHERE user_id=?');
        $q->execute([$u['id']]);
        $downloads = (int) $q->fetchColumn();
        ?><div class="kpi-grid">
<div class="kpi">
<span>Saved resources</span>
<strong><?= $bookmarks ?></strong>
</div>
<div class="kpi">
<span>Downloads</span>
<strong><?= $downloads ?></strong>
</div>
</div>
<section class="panel" style="margin-top:18px">
<h3>Your resource library starts empty</h3>
<p class="muted">Browse materials published by EnoughEdu for your branch and semester.</p>
<a class="btn btn-primary" href="/resources">Browse resources</a>
</section><?php
    } elseif ($section === 'activity') {

        $q = $pdo->prepare(
            'SELECT action,entity_type,created_at FROM activity_history WHERE user_id=? ORDER BY id DESC LIMIT 100',
        );
        $q->execute([$u['id']]);
        $activity = $q->fetchAll();
        ?><section class="panel">
<h3>My activity</h3><?php
foreach ($activity as $a): ?><div class="list-item">
<span>
<b><?= e(
    ucwords(str_replace('_', ' ', $a['action'])),
) ?></b>
<small class="muted" style="display:block"><?= e($a['entity_type'] ?: 'Account') ?> · <?= e(
     date('d M Y, g:i A', strtotime($a['created_at'])),
 ) ?></small>
</span>
</div><?php endforeach;
if (!$activity): ?><div class="empty">No activity recorded yet.</div><?php endif;
?></section><?php
    } elseif ($section === 'profile') {

        $profileBranches = $pdo
            ->query('SELECT id,name FROM branches WHERE status="active" ORDER BY sort_order,name')
            ->fetchAll();
        if (!empty($u['university_id'])) {
            $_SESSION['institution_choices'][$u['university_id']] = [
                'id' => $u['university_id'],
                'name' => $u['university'],
                'city' => $u['university_city'] ?? '',
                'region' => $u['university_region'] ?? '',
                'location' => implode(
                    ', ',
                    array_filter([$u['university_city'] ?? '', $u['university_region'] ?? '']),
                ),
            ];
        }
        ?><section class="panel" style="max-width:850px">
<div class="panel-head">
<div>
<h3>Account and study profile</h3>
<p class="muted">Update the details used to personalize your dashboard.</p>
</div>
</div>
<form method="post" action="/dashboard/profile" enctype="multipart/form-data"><?= csrf_field() ?><div class="form-row">
<div class="field">
<label>Full name</label>
<input class="input" name="name" value="<?= e(
    $u['name'],
) ?>" required>
</div>
<div class="field">
<label>Email</label>
<input class="input" value="<?= e(
    $u['email'],
) ?>" disabled>
</div>
</div>
<div class="form-row">
<div class="field institution-field">
<label>College or university</label>
<input type="hidden" name="university_id" value="<?= e(
    $u['university_id'] ?? '',
) ?>" data-institution-id required>
<input class="input" name="university" value="<?= e(
    $u['university'] ?? '',
) ?>" required maxlength="160" autocomplete="off" data-institution-search aria-autocomplete="list" aria-controls="institution-results">
<div class="institution-results" id="institution-results" data-institution-results role="listbox" hidden>
</div>
<small class="institution-help selected" data-institution-help>Select a directory suggestion to change your institution.</small>
</div>
<div class="field">
<label>Graduation year</label>
<input class="input" name="graduation_year" type="number" min="<?= date(
    'Y',
) ?>" max="<?= date('Y') + 8 ?>" value="<?= e(
    $u['graduation_year'] ?? date('Y'),
) ?>" required>
</div>
</div>
<div class="form-row">
<div class="field">
<label>Branch</label>
<select class="input" name="branch_id" required><?php foreach (
    $profileBranches
    as $branch
): ?><option value="<?= $branch['id'] ?>" <?= $branch['id'] == ($u['branch_id'] ?? 0)
    ? 'selected'
    : '' ?>><?= e(
    $branch['name'],
) ?></option><?php endforeach; ?></select>
</div>
<div class="field">
<label>Semester</label>
<select class="input" name="semester"><?php for (
    $i = 1;
    $i <= 8;
    $i++
): ?><option value="<?= $i ?>" <?= $i == (int) ($u['semester'] ?? 1)
    ? 'selected'
    : '' ?>><?= $i ?></option><?php endfor; ?></select>
</div>
</div>
<div class="form-row">
<div class="field">
<label>Current focus</label>
<select class="input" name="study_goal"><?php foreach (
    ['Improve CGPA', 'Clear backlogs', 'Exam preparation', 'Build career skills']
    as $goal
): ?><option <?= $goal === ($u['study_goal'] ?? '') ? 'selected' : '' ?>><?= e(
    $goal,
) ?></option><?php endforeach; ?></select>
</div>
<div class="field">
<label>Biggest challenge</label>
<select class="input" name="biggest_challenge"><?php foreach (
    [
        'Staying consistent',
        'Understanding concepts',
        'Managing deadlines',
        'Finding quality resources',
    ]
    as $challenge
): ?><option <?= $challenge === ($u['biggest_challenge'] ?? '') ? 'selected' : '' ?>><?= e(
    $challenge,
) ?></option><?php endforeach; ?></select>
</div>
</div>
<div class="form-row">
<div class="field">
<label>Learning style</label>
<select class="input" name="learning_style"><?php foreach (
    ['Short notes', 'Video lessons', 'Practice questions', 'Mixed approach']
    as $style
): ?><option <?= $style === ($u['learning_style'] ?? '') ? 'selected' : '' ?>><?= e(
    $style,
) ?></option><?php endforeach; ?></select>
</div>
<div class="field">
<label>Next major exam</label>
<select class="input" name="exam_timeline"><?php foreach (
    ['Within 2 weeks', 'Within 1 month', 'Within 3 months', 'No date yet']
    as $timeline
): ?><option <?= $timeline === ($u['exam_timeline'] ?? '') ? 'selected' : '' ?>><?= e(
    $timeline,
) ?></option><?php endforeach; ?></select>
</div>
</div>
<div class="form-row">
<div class="field">
<label>Preferred study time</label>
<select class="input" name="preferred_study_time"><?php foreach (
    ['Early morning', 'Daytime', 'Evening', 'Late night']
    as $time
): ?><option <?= $time === ($u['preferred_study_time'] ?? '') ? 'selected' : '' ?>><?= e(
    $time,
) ?></option><?php endforeach; ?></select>
</div>
<div class="field">
<label>Weekly study hours</label>
<input class="input" name="weekly_study_hours" type="number" min="1" max="40" value="<?= e(
    $u['weekly_study_hours'] ?? 10,
) ?>" required>
</div>
</div>
<div class="form-row">
<div class="field">
<label>Target CGPA</label>
<input class="input" name="target_cgpa" type="number" min="4" max="10" step="0.1" value="<?= e(
    $u['target_cgpa'] ?? 8,
) ?>" required>
</div>
<div class="field">
<label>Profile picture</label>
<input class="input" type="file" name="avatar" accept="image/png,image/jpeg,image/webp">
</div>
</div>
<button class="btn btn-primary">Save changes</button>
</form>
<hr style="border-color:var(--line);margin:30px 0">
<h3>Change password</h3>
<form method="post" action="/dashboard/password"><?= csrf_field() ?><div class="form-row">
<input class="input" type="password" name="current_password" placeholder="Current password" required>
<input class="input" type="password" name="password" minlength="8" placeholder="New password" required>
</div>
<button class="btn btn-secondary" style="margin-top:15px">Update password</button>
</form>
</section><?php
    }
    app_end();
}

function handle_student_portal(string $action): never
{
    $u = require_auth();
    if (($u['role'] ?? 'student') === 'admin') {
        redirect('/admin');
    }
    verify_csrf();
    $pdo = db();
    if (!$pdo) {
        flash('error', 'Database unavailable.');
        redirect('/dashboard');
    }
    if ($action === 'planner-add') {
        $title = trim($_POST['title'] ?? '');
        if ($title === '') {
            flash('error', 'Enter a task title.');
            redirect('/dashboard/planner');
        }
        $due = trim($_POST['due_at'] ?? '') ?: null;
        $priority = in_array($_POST['priority'] ?? '', ['low', 'medium', 'high'], true)
            ? $_POST['priority']
            : 'medium';
        $pdo->prepare(
            'INSERT INTO planner_tasks(user_id,title,subject,due_at,priority) VALUES(?,?,?,?,?)',
        )->execute([$u['id'], $title, trim($_POST['subject'] ?? ''), $due, $priority]);
        $pdo->prepare(
            'INSERT INTO activity_history(user_id,action,entity_type) VALUES(?,"task_created","planner_task")',
        )->execute([$u['id']]);
        flash('success', 'Task added.');
        redirect('/dashboard/planner');
    }
    if (in_array($action, ['planner-toggle', 'planner-delete'], true)) {
        $id = (int) ($_POST['id'] ?? 0);
        if ($action === 'planner-delete') {
            $q = $pdo->prepare('DELETE FROM planner_tasks WHERE id=? AND user_id=?');
            $q->execute([$id, $u['id']]);
            flash('success', 'Task deleted.');
        } else {
            $q = $pdo->prepare(
                'UPDATE planner_tasks SET completed_at=IF(status="done",NULL,NOW()),status=IF(status="done","todo","done") WHERE id=? AND user_id=?',
            );
            $q->execute([$id, $u['id']]);
            flash('success', 'Task updated.');
        }
        redirect('/dashboard/planner');
    }
    if ($action === 'degree-save') {
        $semester = max(1, min(8, (int) ($_POST['semester'] ?? 1)));
        $sgpa = (float) ($_POST['sgpa'] ?? -1);
        if ($sgpa < 0 || $sgpa > 10) {
            flash('error', 'SGPA must be between 0 and 10.');
            redirect('/dashboard/degree');
        }
        $credits = max(0, (int) ($_POST['credits_earned'] ?? 0));
        $backlogs = max(0, (int) ($_POST['backlogs'] ?? 0));
        $q = $pdo->prepare(
            'INSERT INTO academic_records(user_id,semester,sgpa,credits_earned,backlogs) VALUES(?,?,?,?,?) ON DUPLICATE KEY UPDATE sgpa=VALUES(sgpa),credits_earned=VALUES(credits_earned),backlogs=VALUES(backlogs)',
        );
        $q->execute([$u['id'], $semester, $sgpa, $credits, $backlogs]);
        flash('success', 'Semester result saved.');
        redirect('/dashboard/degree');
    }
    redirect('/dashboard');
}

function admin_stat(PDO $pdo, string $sql): string
{
    try {
        return (string) $pdo->query($sql)->fetchColumn();
    } catch (Throwable) {
        return '0';
    }
}

function admin_profile_page(): void
{
    $u = app_start('Admin · My profile', 'settings', true);
    $challenge = $_SESSION['admin_password_otp'] ?? null;
    $otpPending =
        is_array($challenge) &&
        (int) ($challenge['user_id'] ?? 0) === (int) $u['id'] &&
        time() <= (int) ($challenge['expires_at'] ?? 0);
    $securityEmail = masked_email((string) config('admin_security.otp_email'));
    ?>
    <section class="panel" style="max-width:760px">
<div class="panel-head">
<div>
<h3>Administrator profile</h3>
<p class="muted">Change the administrator name, avatar or password.</p>
</div>
<a class="btn btn-secondary btn-sm" href="/admin">Back to admin</a>
</div>
<form method="post" action="/dashboard/profile" enctype="multipart/form-data"><?= csrf_field() ?><div class="form-row">
<div class="field">
<label>Full name</label>
<input class="input" name="name" value="<?= e(
    $u['name'],
) ?>" required>
</div>
<div class="field">
<label>Email</label>
<input class="input" value="<?= e(
    $u['email'],
) ?>" disabled>
</div>
</div>
<input type="hidden" name="semester" value="<?= e(
    $u['semester'] ?? 1,
) ?>">
<div class="field">
<label>Profile picture</label>
<input class="input" type="file" name="avatar" accept="image/png,image/jpeg,image/webp">
</div>
<button class="btn btn-primary">Save profile</button>
</form>
<hr style="border-color:var(--line);margin:30px 0">
<div class="panel-head">
<div>
<h3>Change administrator password</h3>
<p class="muted">For security, the current password and a one-time code sent to <?= e(
    $securityEmail,
) ?> are required.</p>
</div>
<span class="badge">OTP protected</span>
</div>
    <?php if (
        !$otpPending
    ): ?><form method="post" action="/admin/password/request-otp"><?= csrf_field() ?><div class="field">
<label for="admin-current-password">Current password</label>
<input class="input" id="admin-current-password" type="password" name="current_password" autocomplete="current-password" required>
</div>
<div class="form-row">
<div class="field">
<label for="admin-new-password">New password</label>
<input class="input" id="admin-new-password" type="password" name="password" minlength="8" autocomplete="new-password" required>
</div>
<div class="field">
<label for="admin-confirm-password">Confirm new password</label>
<input class="input" id="admin-confirm-password" type="password" name="password_confirmation" minlength="8" autocomplete="new-password" required>
</div>
</div>
<button class="btn btn-secondary">Email verification code</button>
</form>
    <?php else: ?><div class="alert alert-success">Code sent to <?= e(
    $securityEmail,
) ?>. It is single-use and expires shortly.</div>
<form method="post" action="/admin/password/confirm"><?= csrf_field() ?><div class="field">
<label for="admin-password-otp">6-digit verification code</label>
<input class="input" id="admin-password-otp" name="otp" inputmode="numeric" autocomplete="one-time-code" pattern="[0-9]{6}" minlength="6" maxlength="6" required placeholder="000000">
</div>
<button class="btn btn-primary">Verify code and change password</button>
</form><?php endif; ?></section><?php app_end();
}

function admin_coupon_rows(PDO $pdo): array
{
    $sql =
        'SELECT c.*,COALESCE(s.order_attempts,0) order_attempts,COALESCE(s.successful_payments,0) successful_payments,COALESCE(s.failed_payments,0) failed_payments,COALESCE(s.cancelled_payments,0) cancelled_payments,COALESCE(s.pending_payments,0) pending_payments,COALESCE(s.discount_given,0) discount_given,s.last_attempt_at FROM coupons c LEFT JOIN (SELECT coupon_id,COUNT(*) order_attempts,SUM(status="paid") successful_payments,SUM(status="failed") failed_payments,SUM(status="cancelled") cancelled_payments,SUM(status="pending") pending_payments,SUM(CASE WHEN status="paid" THEN discount_amount ELSE 0 END) discount_given,MAX(created_at) last_attempt_at FROM orders WHERE coupon_id IS NOT NULL GROUP BY coupon_id) s ON s.coupon_id=c.id ORDER BY c.id DESC';
    return $pdo->query($sql)->fetchAll();
}

function save_public_setting(PDO $pdo, string $key, string $value, string $group = 'site'): void
{
    $q = $pdo->prepare(
        'INSERT INTO settings(setting_key,setting_value,setting_group,is_public) VALUES(?,?,?,1) ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value),setting_group=VALUES(setting_group),is_public=1',
    );
    $q->execute([$key, $value, $group]);
}

function admin_content_controls(): void
{
    $statLabels = [
        'students' => 'Registered students',
        'notes' => 'Published notes',
        'tools' => 'Available features',
        'materials' => 'Published materials',
    ];
    $pages = [
        'about' => 'About page',
        'privacy' => 'Privacy Policy',
        'terms' => 'Terms of Service',
        'refund-policy' => 'Refund Policy',
    ];
    ?>
    <section class="panel">
<div class="panel-head">
<div>
<h3>Homepage text</h3>
<p class="muted">These changes appear on the public homepage immediately.</p>
</div>
<a class="btn btn-secondary btn-sm" href="/" target="_blank" rel="noopener">Open homepage ↗</a>
</div>
<form method="post" action="/admin/action"><?= csrf_field() ?><input type="hidden" name="action" value="settings-save">
<input type="hidden" name="return_section" value="content">
<div class="field">
<label>Announcement bar</label>
<input class="input" name="settings[announcement_text]" maxlength="190" value="<?= e(
    setting('announcement_text', 'Built for ambitious engineering students'),
) ?>" required>
</div>
<div class="field">
<label>Homepage headline</label>
<textarea class="input" name="settings[homepage_headline]" rows="2" maxlength="220" required><?= e(
    setting('homepage_headline', 'Everything an Engineering Student Needs for the Entire Degree'),
) ?></textarea>
</div>
<div class="field">
<label>Homepage description</label>
<textarea class="input" name="settings[homepage_subtitle]" rows="3" maxlength="500" required><?= e(
    setting(
        'homepage_subtitle',
        'Tools, notes, planners, AI-assisted career utilities and academic resources in one organised platform.',
    ),
) ?></textarea>
</div>
<button class="btn btn-primary">Save homepage text</button>
</form>
</section>
    <section class="panel" style="margin-top:18px">
<div class="panel-head">
<div>
<h3>Every homepage number</h3>
<p class="muted">Leave a statistic value blank to use its real live database total. Add “+” as a suffix when appropriate.</p>
</div>
</div>
<form method="post" action="/admin/action"><?= csrf_field() ?><input type="hidden" name="action" value="homepage-stats-save">
<h4>Product preview card</h4>
<div class="form-row">
<div class="field">
<label>Progress percentage</label>
<input class="input" type="number" name="preview[progress]" min="0" max="100" value="<?= e(
    setting('homepage_preview_progress', '0'),
) ?>" required>
</div>
<div class="field">
<label>Tasks shown</label>
<input class="input" type="number" name="preview[tasks]" min="0" max="999999999" value="<?= e(
    setting('homepage_preview_tasks', '0'),
) ?>" required>
</div>
<div class="field">
<label>Purchases shown</label>
<input class="input" type="number" name="preview[purchases]" min="0" max="999999999" value="<?= e(
    setting('homepage_preview_purchases', '0'),
) ?>" required>
</div>
</div>
<h4 style="margin-top:24px">Homepage structure</h4>
<div class="form-row">
<div class="field">
<label>Engineering branch count</label>
<input class="input" type="number" name="structure[branches]" min="0" max="999" value="<?= e(
    setting('homepage_branch_count', '11'),
) ?>" required>
</div>
<div class="field">
<label>Semester count</label>
<input class="input" type="number" name="structure[semesters]" min="0" max="99" value="<?= e(
    setting('homepage_semester_count', '8'),
) ?>" required>
</div>
</div>
<h4 style="margin-top:24px">Public statistic cards</h4>
<div class="content-editor-grid"><?php foreach (
    $statLabels
    as $key => $defaultLabel
): ?><div class="content-stat-editor">
<div class="field">
<label>Card label</label>
<input class="input" name="stats[<?= $key ?>][label]" maxlength="80" value="<?= e(
    setting('homepage_stat_' . $key . '_label', $defaultLabel),
) ?>" required>
</div>
<div class="form-row">
<div class="field">
<label>Display number</label>
<input class="input" type="number" name="stats[<?= $key ?>][value]" min="0" max="999999999" value="<?= e(
    setting('homepage_stat_' . $key . '_value', ''),
) ?>" placeholder="Live total">
</div>
<div class="field">
<label>Suffix</label>
<input class="input" name="stats[<?= $key ?>][suffix]" maxlength="8" value="<?= e(
    setting('homepage_stat_' . $key . '_suffix', ''),
) ?>" placeholder="+">
</div>
</div>
<small class="muted">Blank number = automatic live total.</small>
</div><?php endforeach; ?></div>
<button class="btn btn-primary" style="margin-top:20px">Save every homepage number</button>
</form>
</section>
    <section class="panel" style="margin-top:18px">
<div class="panel-head">
<div>
<h3>Public page content</h3>
<p class="muted">Edit the title, introduction, update date, headings and text. Empty section slots are ignored; bullet lines can start with “- ”.</p>
</div>
</div><?php foreach (
        $pages
        as $page => $label
    ):
        $data = managed_page_data($page); ?><details class="admin-page-editor" <?= $page === 'about'
    ? 'open'
    : '' ?>>
<summary>
<span>
<b><?= e($label) ?></b>
<small><?= count(
    $data['sections'],
) ?> published sections</small>
</span>
<span>Edit page ↓</span>
</summary>
<form method="post" action="/admin/action"><?= csrf_field() ?><input type="hidden" name="action" value="page-content-save">
<input type="hidden" name="page" value="<?= e(
    $page,
) ?>">
<div class="field">
<label>Page title</label>
<input class="input" name="title" maxlength="160" value="<?= e(
    $data['title'],
) ?>" required>
</div>
<div class="field">
<label>Introduction / SEO description</label>
<textarea class="input" name="intro" rows="3" maxlength="500" required><?= e(
    $data['intro'],
) ?></textarea>
</div><?php if (
    $page !== 'about'
): ?><div class="field">
<label>Last updated date</label>
<input class="input" type="date" name="updated" value="<?= e(
    $data['updated'],
) ?>" required>
</div><?php endif; ?><div class="page-section-editors"><?php for (
    $index = 0;
    $index < 8;
    $index++
):
    $section = $data['sections'][$index] ?? [
        'heading' => '',
        'body' => '',
    ]; ?><fieldset class="content-section-editor">
<legend>Section <?= $index +
    1 ?></legend>
<div class="field">
<label>Heading</label>
<input class="input" name="sections[<?= $index ?>][heading]" maxlength="180" value="<?= e(
    $section['heading'],
) ?>">
</div>
<div class="field">
<label>Content</label>
<textarea class="input" name="sections[<?= $index ?>][body]" rows="5" maxlength="8000"><?= e(
    $section['body'],
) ?></textarea>
</div>
</fieldset><?php
endfor; ?></div>
<div class="plan-admin-actions">
<button class="btn btn-primary">Publish <?= e(
    $label,
) ?></button>
<a class="btn btn-secondary" href="/<?= e(
    $page,
) ?>" target="_blank" rel="noopener">Open public page ↗</a>
</div>
</form>
</details><?php
    endforeach; ?></section>
    <?php
}

function admin_console_page(string $section = 'dashboard'): void
{
    require_admin();
    $allowed = [
        'dashboard',
        'users',
        'subscriptions',
        'plans',
        'payments',
        'tools',
        'resources',
        'content',
        'coupons',
        'integrations',
        'settings',
    ];
    if (!in_array($section, $allowed, true)) {
        $section = 'dashboard';
    }
    if ($section === 'resources') {
        admin_materials_page();
        return;
    }
    if ($section === 'integrations') {
        admin_integration_settings_page();
        return;
    }
    $pdo = db();
    app_start('Admin · ' . ucfirst($section), $section, true);
    if (
        !$pdo
    ) { ?><div class="alert alert-error">Database unavailable. Check the private configuration.</div><?php
app_end();
return;
}
    if ($section === 'dashboard') {

        $students = admin_stat($pdo, 'SELECT COUNT(*) FROM users WHERE role="student"');
        $subs = admin_stat(
            $pdo,
            'SELECT COUNT(*) FROM subscriptions WHERE status="active" AND (expires_at IS NULL OR expires_at>NOW())',
        );
        $revenue = admin_stat(
            $pdo,
            'SELECT COALESCE(SUM(amount),0) FROM payments WHERE status="success"',
        );
        $materials = admin_stat(
            $pdo,
            'SELECT (SELECT COUNT(*) FROM notes)+(SELECT COUNT(*) FROM resources)',
        );
        ?>
      <div class="kpi-grid">
<div class="kpi">
<span>Students</span>
<strong><?= e(
          $students,
      ) ?></strong>
<small class="muted">Live database total</small>
</div>
<div class="kpi">
<span>Active subscriptions</span>
<strong><?= e(
    $subs,
) ?></strong>
</div>
<div class="kpi">
<span>Successful revenue</span>
<strong>₹<?= e(
    number_format((float) $revenue, 2),
) ?></strong>
</div>
<div class="kpi">
<span>Study materials</span>
<strong><?= e(
    $materials,
) ?></strong>
</div>
</div>
<section class="panel" style="margin-top:18px">
<div class="panel-head">
<div>
<h3>Admin control centre</h3>
<p class="muted">All numbers and tables on this side come from the live database.</p>
</div>
</div>
<div class="resource-grid">
<a class="resource-card" href="/admin/users">
<h3>Manage users</h3>
<p class="muted">Roles and account status</p>
</a>
<a class="resource-card" href="/admin/resources">
<h3>Add materials</h3>
<p class="muted">Branch and semester links</p>
</a>
<a class="resource-card" href="/admin/plans">
<h3>Manage plans</h3>
<p class="muted">Prices, features and availability</p>
</a>
<a class="resource-card" href="/admin/settings">
<h3>Site settings</h3>
<p class="muted">Live public values</p>
</a>
</div>
</section><?php  ?><p style="margin-top:18px">
<a class="btn btn-secondary" href="/dashboard/profile">Change administrator profile or password</a>
</p><?php
    } elseif ($section === 'users') {
        $rows = $pdo
            ->query(
                'SELECT id,name,email,role,status,semester,created_at,last_login_at FROM users ORDER BY id DESC LIMIT 250',
            )
            ->fetchAll(); ?>
      <section class="panel">
<div class="panel-head">
<div>
<h3>User management</h3>
<p class="muted">Change roles or suspend access. Passwords are never displayed.</p>
</div>
</div>
<div class="table-wrap">
<table class="table">
<thead>
<tr>
<th>User</th>
<th>Role</th>
<th>Status</th>
<th>Joined</th>
<th>Save</th>
</tr>
</thead>
<tbody><?php foreach (
          $rows
          as $r
      ): ?><tr>
<td>
<b><?= e($r['name']) ?></b>
<small class="muted" style="display:block"><?= e(
    $r['email'],
) ?></small>
</td>
<td colspan="4">
<form class="inline-admin-form" method="post" action="/admin/action"><?= csrf_field() ?><input type="hidden" name="action" value="user-save">
<input type="hidden" name="id" value="<?= $r[
    'id'
] ?>">
<select class="input" name="role">
<option value="student" <?= $r['role'] === 'student'
    ? 'selected'
    : '' ?>>Student</option>
<option value="editor" <?= $r['role'] === 'editor'
    ? 'selected'
    : '' ?>>Editor</option>
<option value="admin" <?= $r['role'] === 'admin'
    ? 'selected'
    : '' ?>>Admin</option>
</select>
<select class="input" name="status">
<option value="active" <?= $r[
    'status'
] === 'active'
    ? 'selected'
    : '' ?>>Active</option>
<option value="suspended" <?= $r['status'] === 'suspended'
    ? 'selected'
    : '' ?>>Suspended</option>
</select>
<span><?= e(
    date('d M Y', strtotime($r['created_at'])),
) ?></span>
<button class="btn btn-secondary btn-sm">Save</button>
</form>
</td>
</tr><?php endforeach; ?></tbody>
</table>
</div>
</section><?php
    } elseif ($section === 'subscriptions') {

        te_admin_memberships();
        $rows = $pdo
            ->query(
                'SELECT s.id,s.auto_renew,u.name,u.email,p.name plan_name,s.scope_type,s.starts_at,s.expires_at,s.status FROM subscriptions s JOIN users u ON u.id=s.user_id LEFT JOIN plans p ON p.id=s.plan_id ORDER BY s.id DESC LIMIT 250',
            )
            ->fetchAll();
        $users = $pdo
            ->query(
                'SELECT id,name,email FROM users WHERE role="student" AND status="active" ORDER BY name',
            )
            ->fetchAll();
        $plans = $pdo
            ->query(
                'SELECT id,name FROM plans WHERE status="active" AND plan_type<>"tool" ORDER BY price',
            )
            ->fetchAll();
        ?>
      <div class="dash-layout">
<section class="panel">
<h3>Grant a subscription</h3>
<form method="post" action="/admin/action"><?= csrf_field() ?><input type="hidden" name="action" value="subscription-save">
<div class="field">
<label>Student</label>
<select class="input" name="user_id" required>
<option value="">Choose student</option><?php foreach (
    $users
    as $r
): ?><option value="<?= $r['id'] ?>"><?= e(
    $r['name'] . ' · ' . $r['email'],
) ?></option><?php endforeach; ?></select>
</div>
<div class="field">
<label>Plan</label>
<select class="input" name="plan_id" required><?php foreach (
    $plans
    as $p
): ?><option value="<?= $p['id'] ?>"><?= e(
    $p['name'],
) ?></option><?php endforeach; ?></select>
</div>
<div class="field">
<label>Access until</label>
<input class="input" type="date" name="expires_at" required value="<?= date(
    'Y-m-d',
    strtotime('+1 year'),
) ?>">
</div>
<button class="btn btn-primary">Grant access</button>
</form>
</section>
<section class="panel">
<h3>Live subscriptions</h3><?php
foreach ($rows as $r): ?><div class="list-item">
<span>
<b><?= e(
    $r['name'],
) ?></b>
<small class="muted" style="display:block"><?= e(
    $r['plan_name'] ?: $r['scope_type'],
) ?> · until <?= e(
     $r['expires_at'] ? date('d M Y', strtotime($r['expires_at'])) : 'No expiry',
 ) ?></small>
</span>
<span class="badge <?= $r['status'] !== 'active' ? 'warn' : '' ?>"><?= e(
    $r['status'],
) ?></span><?php if ($r['status'] === 'active') {
    te_admin_cancel_form((int) $r['id'], 'plan', $r['name']);
} ?></div><?php endforeach;
if (!$rows): ?><div class="empty">No subscriptions yet.</div><?php endif;
?></section>
</div><?php
    } elseif ($section === 'plans') {
        $rows = $pdo
            ->query('SELECT * FROM plans ORDER BY status="active" DESC,price,name')
            ->fetchAll(); ?>
      <section class="panel">
<div class="panel-head">
<div>
<h3>Add subscription plan</h3>
<p class="muted">Active plans appear automatically on the public pricing page and at checkout.</p>
</div>
</div>
<form method="post" action="/admin/action"><?= csrf_field() ?><input type="hidden" name="action" value="plan-save">
<div class="form-row">
<div class="field">
<label>Plan name</label>
<input class="input" name="name" required maxlength="120" placeholder="Campus Pro">
</div>
<div class="field">
<label>URL slug</label>
<input class="input" name="slug" required maxlength="120" placeholder="campus-pro">
</div>
</div>
<div class="form-row">
<div class="field">
<label>Plan type</label>
<select class="input" name="plan_type">
<option value="full">Full platform</option>
<option value="branch">Branch</option>
<option value="semester">Semester</option>
<option value="category">Tool category</option>
<option value="tool">Single tool</option>
</select>
</div>
<div class="field">
<label>Billing period</label>
<select class="input" name="billing_period">
<option value="yearly">Yearly</option>
<option value="monthly">Monthly</option>
<option value="semester">Per semester</option>
<option value="one_time">One-time</option>
</select>
</div>
</div>
<div class="form-row">
<div class="field">
<label>Price (₹)</label>
<input class="input" type="number" name="price" min="1" step="0.01" required>
</div>
<div class="field">
<label>Status</label>
<select class="input" name="status">
<option value="active">Active — public</option>
<option value="inactive">Inactive — hidden</option>
</select>
</div>
</div><?php plan_contents_fields(
    $pdo,
); ?><div class="field">
<label>Other benefits / features (one per line)</label>
<textarea class="input" name="features" rows="5" placeholder="Priority support&#10;Monthly mentoring session">
</textarea>
</div>
<input type="hidden" name="plan_form_complete" value="1">
<button class="btn btn-primary">Add plan</button>
</form>
</section>
<section class="panel" style="margin-top:18px">
<div class="panel-head">
<div>
<h3>Subscription plans</h3>
<p class="muted">Edit any plan below, or delete one that is no longer needed.</p>
</div>
<span class="tag"><?= count(
    $rows,
) ?> total</span>
</div>
<div class="plan-admin-grid"><?php foreach ($rows as $r):

     $features = json_decode((string) ($r['features'] ?? '[]'), true);
     $features = is_array($features) ? $features : [];
     $contents = plan_contents($r['limits_json'] ?? null);
     if ($contents !== null) {
         $features = $contents['other_features'] ?? [];
     }
     ?><article class="plan-admin-card">
<form method="post" action="/admin/action"><?= csrf_field() ?><input type="hidden" name="action" value="plan-save">
<input type="hidden" name="id" value="<?= $r[
    'id'
] ?>">
<div class="panel-head">
<div>
<h3><?= e($r['name']) ?></h3>
<small class="muted">/<?= e(
    $r['slug'],
) ?></small>
</div>
<span class="badge <?= $r['status'] === 'inactive' ? 'warn' : '' ?>"><?= e(
    $r['status'],
) ?></span>
</div>
<div class="form-row">
<div class="field">
<label>Name</label>
<input class="input" name="name" value="<?= e(
    $r['name'],
) ?>" required>
</div>
<div class="field">
<label>Slug</label>
<input class="input" name="slug" value="<?= e(
    $r['slug'],
) ?>" required>
</div>
</div>
<div class="form-row">
<div class="field">
<label>Type</label>
<select class="input" name="plan_type"><?php foreach (
    [
        'full' => 'Full platform',
        'branch' => 'Branch',
        'semester' => 'Semester',
        'category' => 'Tool category',
        'tool' => 'Single tool',
    ]
    as $value => $label
): ?><option value="<?= $value ?>" <?= $r['plan_type'] === $value
    ? 'selected'
    : '' ?>><?= $label ?></option><?php endforeach; ?></select>
</div>
<div class="field">
<label>Billing</label>
<select class="input" name="billing_period"><?php foreach (
    [
        'yearly' => 'Yearly',
        'monthly' => 'Monthly',
        'semester' => 'Per semester',
        'one_time' => 'One-time',
    ]
    as $value => $label
): ?><option value="<?= $value ?>" <?= $r['billing_period'] === $value
    ? 'selected'
    : '' ?>><?= $label ?></option><?php endforeach; ?></select>
</div>
</div>
<div class="form-row">
<div class="field">
<label>Price (₹)</label>
<input class="input" type="number" name="price" min="1" step="0.01" value="<?= e(
    $r['price'],
) ?>" required>
</div>
<div class="field">
<label>Status</label>
<select class="input" name="status">
<option value="active" <?= $r[
    'status'
] === 'active'
    ? 'selected'
    : '' ?>>Active</option>
<option value="inactive" <?= $r['status'] === 'inactive'
    ? 'selected'
    : '' ?>>Inactive</option>
</select>
</div>
</div><?php plan_contents_fields(
    $pdo,
    $r,
); ?><div class="field">
<label>Other benefits / features (one per line)</label>
<textarea class="input" name="features" rows="5"><?= e(
    implode("\n", array_map('strval', $features)),
) ?></textarea>
</div>
<input type="hidden" name="plan_form_complete" value="1">
<div class="plan-admin-actions">
<button class="btn btn-secondary btn-sm">Save changes</button>
</div>
</form>
<details class="plan-delete">
<summary class="btn btn-danger btn-sm">Delete</summary>
<form method="post" action="/admin/action"><?= csrf_field() ?><input type="hidden" name="action" value="plan-delete">
<input type="hidden" name="id" value="<?= $r[
    'id'
] ?>">
<p class="muted">Existing subscription history will be kept.</p>
<button class="btn btn-danger btn-sm">Confirm delete</button>
</form>
</details>
</article><?php
 endforeach; ?></div><?php if (
    !$rows
): ?><div class="empty">No plans configured yet. Add the first plan above.</div><?php endif; ?></section><?php
    } elseif ($section === 'payments') {
        $rows = $pdo
            ->query(
                'SELECT p.transaction_id,p.amount,p.status,p.gateway,p.created_at,u.name,u.email FROM payments p JOIN users u ON u.id=p.user_id ORDER BY p.id DESC LIMIT 250',
            )
            ->fetchAll(); ?>
      <section class="panel">
<h3>Payment records</h3>
<div class="table-wrap">
<table class="table">
<thead>
<tr>
<th>Transaction</th>
<th>Student</th>
<th>Amount</th>
<th>Gateway</th>
<th>Status</th>
<th>Date</th>
</tr>
</thead>
<tbody><?php
      foreach ($rows as $r): ?><tr>
<td><?= e($r['transaction_id']) ?></td>
<td>
<b><?= e(
    $r['name'],
) ?></b>
<small class="muted" style="display:block"><?= e($r['email']) ?></small>
</td>
<td>₹<?= e(
    number_format((float) $r['amount'], 2),
) ?></td>
<td><?= e($r['gateway']) ?></td>
<td>
<span class="badge <?= $r['status'] === 'failed'
    ? 'danger'
    : '' ?>"><?= e($r['status']) ?></span>
</td>
<td><?= e(
    date('d M Y', strtotime($r['created_at'])),
) ?></td>
</tr><?php endforeach;
      if (!$rows): ?><tr>
<td colspan="6" class="empty">No payment records yet.</td>
</tr><?php endif;
      ?></tbody>
</table>
</div>
</section><?php
    } elseif ($section === 'tools') {
        $rows = $pdo
            ->query('SELECT * FROM tools WHERE category<>"ai" ORDER BY category,name')
            ->fetchAll(); ?>
      <section class="panel">
<div class="panel-head">
<div>
<h3>Individual tool pricing</h3>
<p class="muted">Set ₹0 to make an existing tool free. A positive price places it in Premium Tools.</p>
</div>
<span class="tag"><?= count(
          $rows,
      ) ?> tools</span>
</div>
<div class="admin-tool-price-grid"><?php
 foreach (
     $rows
     as $r
 ): ?><form class="admin-tool-price-row" method="post" action="/admin/action"><?= csrf_field() ?><input type="hidden" name="action" value="tool-price-save">
<input type="hidden" name="id" value="<?= $r[
    'id'
] ?>">
<div>
<b><?= e($r['name']) ?></b>
<small><?= e($r['slug']) ?> · <?= e(
     $r['category'],
 ) ?></small>
</div>
<div class="field">
<label for="tool-price-<?= $r[
    'id'
] ?>">Price (₹)</label>
<input class="input" id="tool-price-<?= $r[
    'id'
] ?>" type="number" name="price" min="0" max="99999999" step=".01" value="<?= e(
    number_format((float) $r['price'], 2, '.', ''),
) ?>" required>
</div>
<button class="btn btn-primary btn-sm">Save price</button>
</form><?php endforeach;
 if (!$rows): ?><div class="empty">No tools configured.</div><?php endif;
 ?></div>
</section>
      <details class="admin-page-editor" style="margin-top:18px">
<summary>
<span>
<b>Edit a tool's details</b>
<small>Update its name, category and description.</small>
</span>
<span>Open editor ↓</span>
</summary>
<form method="post" action="/admin/action"><?= csrf_field() ?><input type="hidden" name="action" value="tool-save">
<div class="form-row">
<div class="field">
<label for="admin-tool-name">Name</label>
<input class="input" id="admin-tool-name" name="name" required>
</div>
<div class="field">
<label for="admin-tool-slug">Existing tool slug</label>
<input class="input" id="admin-tool-slug" name="slug" list="admin-tool-slugs" required placeholder="cgpa-calculator">
<datalist id="admin-tool-slugs"><?php foreach (
    $rows
    as $toolRow
): ?><option value="<?= e($toolRow['slug']) ?>"><?= e(
    $toolRow['name'],
) ?></option><?php endforeach; ?></datalist>
</div>
</div>
<div class="form-row">
<div class="field">
<label for="admin-tool-category">Category</label>
<select class="input" id="admin-tool-category" name="category">
<option>academic</option>
<option>career</option>
<option>writing</option>
<option>productivity</option>
<option>engineering</option>
<option>media</option>
</select>
</div>
<div class="field">
<label for="admin-tool-price-detail">Price (₹)</label>
<input class="input" id="admin-tool-price-detail" type="number" name="price" min="0" step=".01" required>
</div>
</div>
<div class="field">
<label for="admin-tool-description">Description</label>
<textarea class="input" id="admin-tool-description" name="description" rows="3">
</textarea>
</div>
<button class="btn btn-primary">Save tool details</button>
</form>
</details><?php
    } elseif ($section === 'coupons') {
        $rows = admin_coupon_rows($pdo); ?>
      <div class="dash-layout">
        <section class="panel">
          <h3>Create or update coupon</h3>
<p class="muted">Enter an existing code to update its rules without resetting successful-use history.</p>
          <form method="post" action="/admin/action"><?= csrf_field() ?><input type="hidden" name="action" value="coupon-save">
            <div class="field">
<label>Code</label>
<input class="input" name="code" required maxlength="50" placeholder="CAMPUS20">
</div>
            <div class="form-row">
<div class="field">
<label>Discount type</label>
<select class="input" name="discount_type">
<option value="percent">Percentage</option>
<option value="fixed">Fixed rupees</option>
</select>
</div>
<div class="field">
<label>Discount value</label>
<input class="input" type="number" name="discount_value" min="1" step=".01" required>
</div>
</div>
            <div class="form-row">
<div class="field">
<label>Minimum order (₹)</label>
<input class="input" type="number" name="min_order_value" min="0" step=".01" value="0">
</div>
<div class="field">
<label>Maximum successful uses</label>
<input class="input" type="number" name="max_uses" min="1" placeholder="Unlimited">
</div>
</div>
            <div class="form-row">
<div class="field">
<label>Starts</label>
<input class="input" type="datetime-local" name="starts_at">
</div>
<div class="field">
<label>Expires</label>
<input class="input" type="datetime-local" name="expires_at">
</div>
</div>
            <div class="field">
<label>Status</label>
<select class="input" name="status">
<option value="active">Active</option>
<option value="inactive">Inactive</option>
</select>
</div>
<button class="btn btn-primary">Save coupon</button>
          </form>
        </section>
        <section class="panel">
          <div class="panel-head">
<div>
<h3>Coupon usage report</h3>
<p class="muted">Payment outcomes update automatically from Razorpay orders.</p>
</div>
<span class="tag"><?= count(
              $rows,
          ) ?> total</span>
</div>
          <?php foreach ($rows as $r): ?>
            <div class="list-item coupon-admin-item">
              <div class="coupon-admin-details">
<div class="panel-head" style="margin-bottom:0">
<span>
<b><?= e(
                  $r['code'],
              ) ?></b>
<small class="muted" style="display:block"><?= e(
    $r['discount_type'],
) ?> <?= e(number_format((float) $r['discount_value'], 2)) ?> · minimum ₹<?= e(
     number_format((float) $r['min_order_value'], 2),
 ) ?></small>
</span>
<span class="badge <?= $r['status'] === 'inactive' ? 'warn' : '' ?>"><?= e(
    $r['status'],
) ?></span>
</div>
                <div class="coupon-stats">
<span class="coupon-stat">
<b><?= e(
                    $r['order_attempts'],
                ) ?></b>
<small>Attempts</small>
</span>
<span class="coupon-stat">
<b><?= e(
    $r['successful_payments'],
) ?></b>
<small>Successful</small>
</span>
<span class="coupon-stat">
<b><?= e(
    $r['failed_payments'],
) ?></b>
<small>Failed</small>
</span>
<span class="coupon-stat">
<b><?= e(
    $r['cancelled_payments'],
) ?></b>
<small>Cancelled</small>
</span>
</div>
                <small class="muted" style="display:block;margin-top:10px">Successful uses: <?= e(
                    $r['used_count'],
                ) ?> <?= isset($r['max_uses']) && $r['max_uses'] !== null
     ? ' / ' . e($r['max_uses'])
     : ' / unlimited' ?> · Pending: <?= e($r['pending_payments']) ?> · Discount delivered: ₹<?= e(
     number_format((float) $r['discount_given'], 2),
 ) ?> <?= $r['last_attempt_at']
     ? ' · Last attempt ' . e(date('d M Y, g:i A', strtotime($r['last_attempt_at'])))
     : '' ?></small>
              </div>
              <form class="coupon-admin-actions" method="post" action="/admin/action"><?= csrf_field() ?><input type="hidden" name="action" value="coupon-status">
<input type="hidden" name="id" value="<?= $r[
    'id'
] ?>">
<select class="input" name="status">
<option value="active" <?= $r['status'] === 'active'
    ? 'selected'
    : '' ?>>Active</option>
<option value="inactive" <?= $r['status'] === 'inactive'
    ? 'selected'
    : '' ?>>Inactive</option>
</select>
<button class="btn btn-secondary btn-sm" style="margin-top:8px;width:100%">Save status</button>
</form>
            </div>
          <?php endforeach; ?>
          <?php if (
              !$rows
          ): ?><div class="empty">No coupons yet. Create the first coupon to begin tracking usage.</div><?php endif; ?>
        </section>
      </div><?php
    } elseif ($section === 'content') {
        admin_content_controls();
    } elseif ($section === 'settings') {

        maintenance_admin_form();
        admin_app_download_form();
        $keys = ['site_name', 'support_email'];
        $labels = ['site_name' => 'Website name', 'support_email' => 'Support email'];
        ?>
      <section class="panel" style="max-width:900px">
<div class="panel-head">
<div>
<h3>Website settings</h3>
<p class="muted">Saved values are read from the live database.</p>
</div>
</div>
<form method="post" action="/admin/action"><?= csrf_field() ?><input type="hidden" name="action" value="settings-save">
<input type="hidden" name="return_section" value="settings"><?php foreach (
    $keys
    as $key
): ?><div class="field">
<label><?= e(
    $labels[$key],
) ?></label>
<input class="input" name="settings[<?= e($key) ?>]" value="<?= e(
    setting($key, ''),
) ?>" required>
</div><?php endforeach; ?><button class="btn btn-primary">Save live changes</button>
</form>
<hr style="border-color:var(--line);margin:28px 0">
<h3>Integration status</h3>
<div class="card-meta">
<span class="tag">Google <?= google_oauth_ready()
    ? 'ready'
    : 'needs keys' ?></span>
<span class="tag">Gemini <?= trim((string) config('ai.api_key')) !== ''
    ? 'ready'
    : 'needs key' ?></span>
<span class="tag">Razorpay <?= razorpay_ready() &&
trim((string) config('razorpay.webhook_secret')) !== '' &&
!str_starts_with((string) config('razorpay.webhook_secret'), 'YOUR_')
    ? 'ready'
    : 'needs keys/webhook' ?></span>
</div>
</section><?php
    }
    app_end();
}

function handle_admin_portal(): never
{
    $admin = require_admin();
    verify_csrf();
    $pdo = db();
    if (!$pdo) {
        flash('error', 'Database unavailable.');
        redirect('/admin');
    }
    $action = $_POST['action'] ?? '';
    try {
        if ($action === 'subscription-cancel') {
            te_cancel_subscription($pdo);
            redirect('/admin/subscriptions');
        }
        if ($action === 'user-save') {
            $id = (int) ($_POST['id'] ?? 0);
            $role = in_array($_POST['role'] ?? '', ['student', 'editor', 'admin'], true)
                ? $_POST['role']
                : 'student';
            $status = in_array($_POST['status'] ?? '', ['active', 'suspended'], true)
                ? $_POST['status']
                : 'active';
            if ($id === (int) $admin['id'] && ($role !== 'admin' || $status !== 'active')) {
                throw new DomainException(
                    'You cannot remove or suspend your own administrator access.',
                );
            }
            $q = $pdo->prepare('UPDATE users SET role=?,status=? WHERE id=?');
            $q->execute([$role, $status, $id]);
            flash('success', 'User updated.');
            redirect('/admin/users');
        }
        if ($action === 'subscription-save') {
            $userId = (int) ($_POST['user_id'] ?? 0);
            $planId = (int) ($_POST['plan_id'] ?? 0);
            $expires = $_POST['expires_at'] ?? '';
            $q = $pdo->prepare('SELECT plan_type,slug FROM plans WHERE id=? AND status="active"');
            $q->execute([$planId]);
            $plan = $q->fetch();
            if (!$userId || !$plan || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $expires)) {
                throw new DomainException('Choose a valid student, plan and expiry date.');
            }
            if ($plan['plan_type'] === 'tool') {
                throw new DomainException(
                    'Grant individual tools through a tool purchase or choose a bundle plan.',
                );
            }
            $scope =
                [
                    'full' => 'degree',
                    'branch' => 'branch',
                    'semester' => 'semester',
                    'category' => 'category',
                ][$plan['plan_type']] ?? 'degree';
            $scopeId = in_array($scope, ['branch', 'semester'], true)
                ? 'current'
                : ($scope === 'category'
                    ? $plan['slug']
                    : 'all');
            $pdo->prepare(
                'INSERT INTO subscriptions(user_id,plan_id,scope_type,scope_id,starts_at,expires_at,status) VALUES(?,?,?,?,NOW(),?,"active")',
            )->execute([$userId, $planId, $scope, $scopeId, $expires . ' 23:59:59']);
            flash('success', 'Subscription granted.');
            redirect('/admin/subscriptions');
        }
        if ($action === 'plan-save') {
            if (($_POST['plan_form_complete'] ?? '') !== '1') {
                throw new DomainException(
                    'The plan form was incomplete. Reload and try again. If many items are selected, increase PHP max_input_vars in cPanel.',
                );
            }
            $id = (int) ($_POST['id'] ?? 0);
            $name = trim((string) ($_POST['name'] ?? ''));
            $slugValue = slug((string) ($_POST['slug'] ?? ''));
            $type = in_array(
                $_POST['plan_type'] ?? '',
                ['tool', 'category', 'semester', 'branch', 'full'],
                true,
            )
                ? $_POST['plan_type']
                : 'full';
            $billing = in_array(
                $_POST['billing_period'] ?? '',
                ['one_time', 'semester', 'monthly', 'yearly'],
                true,
            )
                ? $_POST['billing_period']
                : 'yearly';
            $status = in_array($_POST['status'] ?? '', ['active', 'inactive'], true)
                ? $_POST['status']
                : 'active';
            $price = (float) ($_POST['price'] ?? 0);
            $features = array_values(
                array_filter(
                    array_map(
                        'trim',
                        preg_split('/\R/', (string) ($_POST['features'] ?? '')) ?: [],
                    ),
                    fn($value) => $value !== '',
                ),
            );
            $limitsJson = save_plan_contents($pdo, $id, $features, $type);
            if ($name === '' || $slugValue === '' || $price <= 0 || !$features) {
                throw new DomainException(
                    'Enter a plan name, slug, positive price, and at least one feature.',
                );
            }
            $json = json_encode($features, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
            if ($id) {
                $q = $pdo->prepare(
                    'UPDATE plans SET name=?,slug=?,plan_type=?,price=?,billing_period=?,features=?,limits_json=?,status=? WHERE id=?',
                );
                $q->execute([
                    $name,
                    $slugValue,
                    $type,
                    $price,
                    $billing,
                    $json,
                    $limitsJson,
                    $status,
                    $id,
                ]);
                if (
                    !$pdo->query('SELECT COUNT(*) FROM plans WHERE id=' . (int) $id)->fetchColumn()
                ) {
                    throw new DomainException('That plan no longer exists.');
                }
                $message = 'Plan updated.';
            } else {
                $q = $pdo->prepare(
                    'INSERT INTO plans(name,slug,plan_type,price,billing_period,features,limits_json,status) VALUES(?,?,?,?,?,?,?,?)',
                );
                $q->execute([
                    $name,
                    $slugValue,
                    $type,
                    $price,
                    $billing,
                    $json,
                    $limitsJson,
                    $status,
                ]);
                $message = 'Plan added and pricing updated.';
            }
            flash('success', $message);
            redirect('/admin/plans');
        }
        if ($action === 'plan-delete') {
            $id = (int) ($_POST['id'] ?? 0);
            if ($id <= 0) {
                throw new DomainException('Choose a valid plan to delete.');
            }
            $q = $pdo->prepare('DELETE FROM plans WHERE id=?');
            $q->execute([$id]);
            if (!$q->rowCount()) {
                throw new DomainException('That plan was already removed.');
            }
            flash('success', 'Plan deleted. Existing subscription history was kept.');
            redirect('/admin/plans');
        }
        if ($action === 'tool-price-save') {
            $id = (int) ($_POST['id'] ?? 0);
            $price = te_price($_POST['price'] ?? null);
            if ($id <= 0 || !is_finite($price) || $price < 0 || $price > 99999999) {
                throw new DomainException(
                    'Enter a valid tool price between ₹0 and ₹99,999,999. Use ₹0 for free access.',
                );
            }
            $exists = $pdo->prepare(
                'SELECT id FROM tools WHERE id=? AND category<>"ai" AND status<>"archived" LIMIT 1',
            );
            $exists->execute([$id]);
            if (!$exists->fetchColumn()) {
                throw new DomainException('That active tool could not be found.');
            }
            $pdo->prepare('UPDATE tools SET price=?,is_free=? WHERE id=?')->execute([
                $price,
                $price == 0 ? 1 : 0,
                $id,
            ]);
            flash('success', 'Tool price updated everywhere.');
            redirect('/admin/tools');
        }
        if ($action === 'tool-save') {
            $name = trim($_POST['name'] ?? '');
            $slugValue = slug($_POST['slug'] ?? '');
            $category = in_array(
                $_POST['category'] ?? '',
                ['academic', 'career', 'writing', 'productivity', 'engineering', 'media'],
                true,
            )
                ? $_POST['category']
                : 'academic';
            $price = te_price($_POST['price'] ?? null);
            if (!is_finite($price) || $price < 0 || $price > 99999999) {
                throw new DomainException('Enter a price of zero or more.');
            }
            if (!$name || !$slugValue) {
                throw new DomainException('Name and slug are required.');
            }
            if (!tool_definition($slugValue)) {
                throw new DomainException(
                    'Choose one of the working EnoughEdu tool slugs. New tools require an implemented tool page before publication.',
                );
            }
            $exists = $pdo->prepare('SELECT id FROM tools WHERE slug=? LIMIT 1');
            $exists->execute([$slugValue]);
            if (!$exists->fetchColumn()) {
                throw new DomainException('That tool is not present in the catalogue.');
            }
            $q = $pdo->prepare(
                'UPDATE tools SET name=?,category=?,description=?,price=?,is_free=?,status="active" WHERE slug=?',
            );
            $q->execute([
                $name,
                $category,
                trim($_POST['description'] ?? ''),
                $price,
                $price == 0 ? 1 : 0,
                $slugValue,
            ]);
            flash('success', 'Tool catalogue updated.');
            redirect('/admin/tools');
        }
        if ($action === 'coupon-save') {
            $code = strtoupper(trim($_POST['code'] ?? ''));
            $type = in_array($_POST['discount_type'] ?? '', ['percent', 'fixed'], true)
                ? $_POST['discount_type']
                : 'percent';
            $value = (float) ($_POST['discount_value'] ?? 0);
            $minimum = max(0, (float) ($_POST['min_order_value'] ?? 0));
            $maxUses = trim((string) ($_POST['max_uses'] ?? ''));
            $maxUses = $maxUses === '' ? null : (int) $maxUses;
            $starts = trim((string) ($_POST['starts_at'] ?? ''));
            $starts =
                $starts === ''
                    ? null
                    : str_replace('T', ' ', $starts) . (strlen($starts) === 16 ? ':00' : '');
            $expires = trim((string) ($_POST['expires_at'] ?? ''));
            $expires =
                $expires === ''
                    ? null
                    : str_replace('T', ' ', $expires) . (strlen($expires) === 16 ? ':00' : '');
            $status = in_array($_POST['status'] ?? '', ['active', 'inactive'], true)
                ? $_POST['status']
                : 'active';
            if (
                !preg_match('/^[A-Z0-9_-]{3,50}$/', $code) ||
                $value <= 0 ||
                ($type === 'percent' && $value > 100) ||
                ($maxUses !== null && $maxUses < 1) ||
                ($starts && $expires && strtotime($starts) >= strtotime($expires))
            ) {
                throw new DomainException(
                    'Check the coupon code, discount, usage limit, and date range. Percentage discounts cannot exceed 100%.',
                );
            }
            $pdo->prepare(
                'INSERT INTO coupons(code,discount_type,discount_value,min_order_value,max_uses,starts_at,expires_at,status) VALUES(?,?,?,?,?,?,?,?) ON DUPLICATE KEY UPDATE discount_type=VALUES(discount_type),discount_value=VALUES(discount_value),min_order_value=VALUES(min_order_value),max_uses=VALUES(max_uses),starts_at=VALUES(starts_at),expires_at=VALUES(expires_at),status=VALUES(status)',
            )->execute([$code, $type, $value, $minimum, $maxUses, $starts, $expires, $status]);
            flash('success', 'Coupon rules saved.');
            redirect('/admin/coupons');
        }
        if ($action === 'coupon-status') {
            $id = (int) ($_POST['id'] ?? 0);
            $status = in_array($_POST['status'] ?? '', ['active', 'inactive'], true)
                ? $_POST['status']
                : 'inactive';
            if ($id <= 0) {
                throw new DomainException('Choose a valid coupon.');
            }
            $q = $pdo->prepare('UPDATE coupons SET status=? WHERE id=?');
            $q->execute([$status, $id]);
            flash('success', 'Coupon status updated.');
            redirect('/admin/coupons');
        }
        if ($action === 'homepage-stats-save') {
            $preview = (array) ($_POST['preview'] ?? []);
            $structure = (array) ($_POST['structure'] ?? []);
            $progress = trim((string) ($preview['progress'] ?? ''));
            $tasks = trim((string) ($preview['tasks'] ?? ''));
            $purchases = trim((string) ($preview['purchases'] ?? ''));
            $branches = trim((string) ($structure['branches'] ?? ''));
            $semesters = trim((string) ($structure['semesters'] ?? ''));
            if (
                !ctype_digit($progress) ||
                !ctype_digit($tasks) ||
                !ctype_digit($purchases) ||
                !ctype_digit($branches) ||
                !ctype_digit($semesters) ||
                (int) $progress > 100 ||
                (int) $tasks > 999999999 ||
                (int) $purchases > 999999999 ||
                (int) $branches > 999 ||
                (int) $semesters > 99
            ) {
                throw new DomainException(
                    'Homepage numbers must be valid non-negative values, and progress must be between 0 and 100.',
                );
            }
            $statKeys = ['students', 'notes', 'tools', 'materials'];
            $prepared = [];
            foreach ($statKeys as $key) {
                $row = (array) (($_POST['stats'] ?? [])[$key] ?? []);
                $label = text_limit(trim((string) ($row['label'] ?? '')), 80);
                $value = trim((string) ($row['value'] ?? ''));
                $suffix = text_limit(trim((string) ($row['suffix'] ?? '')), 8);
                if (
                    $label === '' ||
                    ($value !== '' && (!ctype_digit($value) || (int) $value > 999999999))
                ) {
                    throw new DomainException(
                        'Every statistic needs a label. Display numbers must be blank or a non-negative whole number.',
                    );
                }
                $prepared[$key] = [$label, $value, $suffix];
            }
            $pdo->beginTransaction();
            try {
                save_public_setting(
                    $pdo,
                    'homepage_preview_progress',
                    (string) (int) $progress,
                    'homepage',
                );
                save_public_setting(
                    $pdo,
                    'homepage_preview_tasks',
                    (string) (int) $tasks,
                    'homepage',
                );
                save_public_setting(
                    $pdo,
                    'homepage_preview_purchases',
                    (string) (int) $purchases,
                    'homepage',
                );
                save_public_setting(
                    $pdo,
                    'homepage_branch_count',
                    (string) (int) $branches,
                    'homepage',
                );
                save_public_setting(
                    $pdo,
                    'homepage_semester_count',
                    (string) (int) $semesters,
                    'homepage',
                );
                foreach ($prepared as $key => [$label, $value, $suffix]) {
                    save_public_setting(
                        $pdo,
                        'homepage_stat_' . $key . '_label',
                        $label,
                        'homepage',
                    );
                    save_public_setting(
                        $pdo,
                        'homepage_stat_' . $key . '_value',
                        $value,
                        'homepage',
                    );
                    save_public_setting(
                        $pdo,
                        'homepage_stat_' . $key . '_suffix',
                        $suffix,
                        'homepage',
                    );
                }
                $pdo->commit();
            } catch (Throwable $e) {
                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }
                throw $e;
            }
            flash('success', 'Every homepage number was updated.');
            redirect('/admin/content');
        }
        if ($action === 'page-content-save') {
            $page = (string) ($_POST['page'] ?? '');
            if (!in_array($page, ['about', 'privacy', 'terms', 'refund-policy'], true)) {
                throw new DomainException('Choose a valid public page.');
            }
            $title = text_limit(trim((string) ($_POST['title'] ?? '')), 160);
            $intro = text_limit(trim((string) ($_POST['intro'] ?? '')), 500);
            $updated = $page === 'about' ? '' : trim((string) ($_POST['updated'] ?? ''));
            if (
                $title === '' ||
                $intro === '' ||
                ($page !== 'about' &&
                    (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $updated) ||
                        date('Y-m-d', strtotime($updated)) !== $updated))
            ) {
                throw new DomainException(
                    'Enter the page title, introduction and a valid update date.',
                );
            }
            $sections = [];
            foreach (array_slice((array) ($_POST['sections'] ?? []), 0, 8) as $section) {
                if (!is_array($section)) {
                    continue;
                }
                $heading = text_limit(trim((string) ($section['heading'] ?? '')), 180);
                $body = text_limit(trim((string) ($section['body'] ?? '')), 8000);
                if ($heading === '' && $body === '') {
                    continue;
                }
                if ($heading === '' || $body === '') {
                    throw new DomainException(
                        'Each used page section needs both a heading and content.',
                    );
                }
                $sections[] = ['heading' => $heading, 'body' => $body];
            }
            if (!$sections) {
                throw new DomainException('Add at least one complete page section.');
            }
            $payload = json_encode(
                [
                    'title' => $title,
                    'intro' => $intro,
                    'updated' => $updated,
                    'sections' => $sections,
                ],
                JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR,
            );
            save_public_setting($pdo, 'page_content_' . $page, $payload, 'public_pages');
            flash('success', ucwords(str_replace('-', ' ', $page)) . ' content published.');
            redirect('/admin/content');
        }
        if ($action === 'app-download-save') {
            save_android_app_download($pdo);
        }
        if ($action === 'settings-save') {
            $allowed = [
                'announcement_text',
                'homepage_headline',
                'homepage_subtitle',
                'site_name',
                'support_email',
            ];
            foreach ((array) ($_POST['settings'] ?? []) as $key => $value) {
                if (!in_array($key, $allowed, true)) {
                    continue;
                }
                $value = trim((string) $value);
                if ($value === '') {
                    throw new DomainException('Website text fields cannot be empty.');
                }
                if ($key === 'support_email' && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    throw new DomainException('Enter a valid support email.');
                }
                $limits = [
                    'announcement_text' => 190,
                    'homepage_headline' => 220,
                    'homepage_subtitle' => 500,
                    'site_name' => 120,
                    'support_email' => 190,
                ];
                save_public_setting(
                    $pdo,
                    $key,
                    text_limit($value, $limits[$key]),
                    $key === 'site_name' || $key === 'support_email' ? 'site' : 'homepage',
                );
            }
            flash('success', 'Live website settings saved.');
            redirect(
                '/admin/' .
                    (($_POST['return_section'] ?? 'settings') === 'content'
                        ? 'content'
                        : 'settings'),
            );
        }
    } catch (DomainException $e) {
        flash('error', $e->getMessage());
    } catch (Throwable $e) {
        if (config('app.debug')) {
            error_log($e->getMessage());
        }
        flash(
            'error',
            $action === 'subscription-cancel'
                ? 'Cancellation could not be confirmed. Check Razorpay and retry to reconcile the subscription status.'
                : 'The change could not be saved. Check the submitted values.',
        );
    }
    redirect('/admin');
}
