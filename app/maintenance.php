<?php
declare(strict_types=1);

function maintenance_defaults(): array
{
    return [
        'title' => 'A little pause. A better EnoughEdu.',
        'message' =>
            'We are making a few improvements to your study space. Please check back soon. Thank you for your patience.',
    ];
}
function maintenance_exempt_path(string $path): bool
{
    if ($path === '/admin' || str_starts_with($path, '/admin/')) {
        return true;
    }
    return in_array(
        $path,
        [
            '/login',
            '/logout',
            '/forgot-password',
            '/reset-password',
            '/auth/google',
            '/auth/google/callback',
            '/payment/razorpay/webhook',
            '/payment/razorpay/callback',
            '/payment/razorpay/cancel',
            '/resources/membership',
            '/resources/membership/cancel',
            '/resources/membership/verify',
            '/resources/membership/webhook',
        ],
        true,
    );
}
function maintenance_gate(): void
{
    $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
    if ($path === '/admin/maintenance-preview') {
        require_admin();
        maintenance_page(true);
    }
    if ($path === '/admin/maintenance-save' && is_post()) {
        maintenance_save();
    }
    if ((string) setting('maintenance_enabled', '0') !== '1' || maintenance_exempt_path($path)) {
        return;
    }
    $u = user();
    if (($u['role'] ?? '') === 'admin') {
        return;
    }
    maintenance_page();
}
function maintenance_save(): never
{
    require_admin();
    verify_csrf();
    $pdo = db();
    try {
        if (!$pdo) {
            throw new RuntimeException('Database unavailable');
        }
        $defaults = maintenance_defaults();
        $title = $_POST['maintenance_title'] ?? '';
        $message = $_POST['maintenance_message'] ?? '';
        if (!is_string($title) || !is_string($message)) {
            throw new DomainException('Enter plain text for the title and message.');
        }
        $title = text_limit(trim($title) ?: $defaults['title'], 120);
        $message = text_limit(trim($message) ?: $defaults['message'], 1000);
        $values = [
            'maintenance_enabled' => ($_POST['maintenance_enabled'] ?? '') === '1' ? '1' : '0',
            'maintenance_title' => $title,
            'maintenance_message' => $message,
        ];
        $pdo->beginTransaction();
        $q = $pdo->prepare(
            'INSERT INTO settings(setting_key,setting_value,setting_group,is_public) VALUES(?,?,"maintenance",0) ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value),setting_group="maintenance",is_public=0',
        );
        foreach ($values as $key => $value) {
            $q->execute([$key, $value]);
        }
        $pdo->commit();
        flash(
            'success',
            $values['maintenance_enabled'] === '1'
                ? 'Maintenance is on for visitors. You can keep working as an administrator.'
                : 'Maintenance is off. The public website is available.',
        );
    } catch (Throwable $e) {
        if ($pdo && $pdo->inTransaction()) {
            $pdo->rollBack();
        }
        flash(
            'error',
            $e instanceof DomainException
                ? $e->getMessage()
                : 'Could not save maintenance settings. Please try again.',
        );
    }
    redirect('/admin/settings');
}
function maintenance_admin_form(): void
{
    $defaults = maintenance_defaults();
    $enabled = (string) setting('maintenance_enabled', '0') === '1';
    ?>
    <section class="panel" style="max-width:900px;margin-bottom:24px">
<div class="panel-head">
<div>
<h3>Site maintenance</h3>
<p class="muted">Status: <strong><?= $enabled
        ? 'ON — visitors see the maintenance page'
        : 'OFF — the website is public' ?></strong>
</p>
</div>
<a class="btn btn-secondary btn-sm" href="/admin/maintenance-preview" target="_blank" rel="noopener">Preview message &rarr;</a>
</div>
    <form method="post" action="/admin/maintenance-save"><?= csrf_field() ?><label style="display:block;margin:18px 0">
<input type="checkbox" name="maintenance_enabled" value="1" <?= $enabled
    ? 'checked'
    : '' ?>> Enable maintenance mode for visitors</label>
<div class="field">
<label for="maintenance-title">Page heading</label>
<input class="input" id="maintenance-title" name="maintenance_title" maxlength="120" value="<?= e(
    setting('maintenance_title', $defaults['title']),
) ?>" required>
</div>
<div class="field">
<label for="maintenance-message">Message to visitors</label>
<textarea class="input" id="maintenance-message" name="maintenance_message" rows="4" maxlength="1000" required><?= e(
    setting('maintenance_message', $defaults['message']),
) ?></textarea>
</div>
<p class="muted">Administrators can still browse and sign in. Existing payment confirmations and subscription cancellation remain available. Save changes before opening the preview.</p>
<button class="btn btn-primary">Save maintenance settings</button>
</form>
</section>
    <?php
}
function maintenance_page(bool $preview = false): never
{
    $defaults = maintenance_defaults();
    $title = (string) setting('maintenance_title', $defaults['title']);
    $message = (string) setting('maintenance_message', $defaults['message']);
    $support = (string) setting('support_email', 'hello@vedhant.in');
    if (!filter_var($support, FILTER_VALIDATE_EMAIL)) {
        $support = 'hello@vedhant.in';
    }
    http_response_code($preview ? 200 : 503);
    if (!$preview) {
        header('Retry-After: 3600');
    }
    header('Cache-Control: no-store, max-age=0');
    header('X-Robots-Tag: noindex, nofollow');
    header('Content-Type: text/html; charset=utf-8');
    ?>
    <!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="robots" content="noindex,nofollow">
<meta name="theme-color" content="#f5f4ff">
<title>Under maintenance | EnoughEdu</title>
<link rel="icon" href="/assets/img/favicon.svg">
    <style>
    *{box-sizing:border-box}body{margin:0;min-height:100svh;background:#f7f8fc;color:#16233c;font-family:Inter,"Segoe UI",Arial,sans-serif;display:flex;flex-direction:column}body:before{content:"";position:fixed;inset:0;z-index:-1;background:radial-gradient(ellipse at 18% 18%,#e4e5ff 0,transparent 48%),radial-gradient(ellipse at 88% 80%,#dcf6fa 0,transparent 42%)}.mt-wrap{width:min(1120px,calc(100% - 40px));margin:auto}.mt-header{padding:30px 0;display:flex;align-items:center;justify-content:space-between;gap:16px}.mt-logo{width:178px;max-width:45vw}.mt-label{font-size:12px;letter-spacing:.06em;color:#626e86}.mt-main{flex:1;display:grid;place-items:center;padding:32px 0 52px}.mt-card{display:grid;grid-template-columns:1.2fr .8fr;gap:42px;align-items:center;padding:64px;border:1px solid #ffffff;background:#ffffffd9;box-shadow:0 24px 80px #2439750c;border-radius:32px}.mt-pill{display:inline-flex;align-items:center;gap:9px;font-size:12px;letter-spacing:.05em;font-weight:700;padding:9px 13px;border:1px solid #e3dcfd;background:#f5f0ff;color:#7250b5;border-radius:30px}.mt-dot{width:7px;height:7px;border-radius:50%;background:#9572d9}h1{font-size:clamp(32px,4.4vw,56px);line-height:1.12;letter-spacing:-.045em;margin:26px 0 20px;overflow-wrap:anywhere}.mt-message{font-size:17px;line-height:1.8;color:#65718a;margin:0 0 28px;overflow-wrap:anywhere}.mt-actions{display:flex;flex-wrap:wrap;gap:12px}.mt-button{display:inline-flex;align-items:center;justify-content:center;text-decoration:none;border-radius:12px;padding:13px 19px;font-size:14px;font-weight:700;color:white;background:#3159de;box-shadow:0 7px 20px #3159de25}.mt-button.secondary{background:#fff;color:#43516b;border:1px solid #dfe4ef;box-shadow:none}.mt-note{font-size:12px;color:#7d87a0;margin:22px 0 0;line-height:1.6}.mt-art{position:relative;display:grid;place-items:center;min-height:290px}.mt-halo{position:absolute;width:280px;height:280px;border:1px dashed #c9d3ef;border-radius:50%;background:radial-gradient(circle,#eaf0ff,transparent 70%)}.mt-tile{position:relative;transform:rotate(-6deg);width:186px;height:218px;background:#fff;border:1px solid #e5e9f5;border-radius:24px;box-shadow:18px 22px 44px #3f559b19;padding:26px}.mt-tile svg{display:block;width:74px;height:74px;margin:4px auto 22px}.mt-line{height:9px;background:#e7edfa;border-radius:8px;margin:12px 0}.mt-line.short{width:63%}.mt-spark{position:absolute;right:8%;top:12%;font-size:36px;color:#a38ae0}.mt-check{position:absolute;bottom:12%;left:6%;background:#eefcf9;border:1px solid #caece4;color:#317968;border-radius:12px;padding:12px 16px;font-size:13px;box-shadow:0 12px 24px #325c5910}.mt-footer{display:flex;justify-content:space-between;gap:18px;flex-wrap:wrap;padding:22px 0 28px;font-size:12px;color:#7d87a0}.mt-footer a{color:#626f8e;text-decoration:none}.mt-preview{padding:12px 20px;background:#17233e;color:#fff;text-align:center;font-size:13px}.mt-preview a{color:#fff}a:focus-visible{outline:3px solid #8264dd;outline-offset:4px}@media(max-width:760px){.mt-card{grid-template-columns:1fr;padding:32px 26px;gap:18px}.mt-art{min-height:220px;grid-row:1}.mt-halo{width:220px;height:220px}.mt-tile{width:144px;height:170px;padding:18px}.mt-tile svg{width:54px;height:54px;margin-bottom:14px}.mt-line{height:7px;margin:9px 0}.mt-header{padding:22px 0}.mt-main{padding-top:12px}.mt-label{font-size:10px}.mt-check{bottom:6%;left:10%}.mt-message{font-size:16px}}
    </style></head>
<body><?php if (
        $preview
    ): ?><div class="mt-preview">Admin preview · this does not change maintenance status. <a href="/admin/settings">Back to settings</a>
</div><?php endif; ?>
    <header class="mt-wrap mt-header">
<a href="/" aria-label="EnoughEdu home">
<img class="mt-logo" src="/assets/img/logo.svg" alt="EnoughEdu">
</a>
<span class="mt-label">YOUR ACADEMIC COMPANION</span>
</header>
    <main class="mt-wrap mt-main">
<section class="mt-card">
<div>
<span class="mt-pill">
<span class="mt-dot" aria-hidden="true">
</span> SITE UNDER MAINTENANCE</span>
<h1><?= e(
        $title,
    ) ?></h1>
<p class="mt-message"><?= nl2br(e($message)) ?></p>
<div class="mt-actions">
<a class="mt-button" href="/">Try again &rarr;</a>
<a class="mt-button secondary" href="mailto:<?= e($support) ?>">Contact support</a>
</div>
<p class="mt-note">Already a subscriber? <a href="/resources/membership">Manage your membership</a>.</p>
</div>
<div class="mt-art" aria-hidden="true">
<div class="mt-halo">
</div>
<span class="mt-spark">✦</span>
<div class="mt-tile">
<svg viewBox="0 0 80 80" fill="none">
<rect x="5" y="5" width="70" height="70" rx="19" fill="#edf1ff"/>
<path d="M24 23h14c4 0 7 2 7 5v31c0-3-3-5-7-5H24V23Z" fill="#a9b7f8"/>
<path d="M56 23H45v36c0-3 3-5 7-5h4V23Z" fill="#627de7"/>
<path d="m29 35 6 6 16-16" stroke="#fff" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"/>
</svg>
<div class="mt-line">
</div>
<div class="mt-line short">
</div>
</div>
<span class="mt-check">A little care, behind the scenes.</span>
</div>
</section>
</main>
    <footer class="mt-wrap mt-footer">
<span>&copy; <?= date(
        'Y',
    ) ?> EnoughEdu</span>
<a href="/login">Administrator sign-in</a>
</footer>
</body>
</html><?php exit();
}
