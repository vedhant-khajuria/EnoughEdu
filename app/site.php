<?php
declare(strict_types=1);
require_once __DIR__ . '/maintenance.php';
maintenance_gate();
require_once __DIR__ . '/app-download.php';

function ai_public_text(mixed $value): string
{
    return preg_replace('/\b(?:Google\s+)?Gemini\b/i', 'AI', (string) $value) ?? (string) $value;
}

const BRANCHES = [
    ['Computer Science Engineering', 'cse', '</>'],
    ['Artificial Intelligence & Machine Learning', 'ai-ml', '✦'],
    ['Information Technology', 'it', '⌘'],
    ['Electronics & Communication', 'ece', '⌁'],
    ['Mechanical Engineering', 'mechanical', '⚙'],
    ['Civil Engineering', 'civil', '▦'],
    ['Electrical Engineering', 'electrical', 'ϟ'],
    ['Aerospace Engineering', 'aerospace', '↗'],
    ['Data Science', 'data-science', '∿'],
    ['Robotics', 'robotics', '◉'],
    ['Mechatronics', 'mechatronics', '◇'],
];
const TOOLS = [
    'Academic Calculators' => [
        ['CGPA Calculator', 'cgpa-calculator', '₹99', '∑'],
        ['SGPA Calculator', 'sgpa-calculator', '₹99', '∑'],
        ['Percentage Calculator', 'percentage-calculator', '₹49', '%'],
        ['Attendance Calculator', 'attendance-calculator', '₹49', '◷'],
        ['GPA Converter', 'gpa-converter', '₹49', '⇄'],
        ['Marks Predictor', 'marks-predictor', '₹79', '⌁'],
        ['Backlog Impact Calculator', 'backlog-impact-calculator', '₹79', '↘'],
    ],
    'Career Tools' => [
        ['Resume Builder', 'resume-builder', '₹199', '▤'],
        ['ATS Resume Checker', 'ats-resume-checker', '₹99', '◎'],
        ['LinkedIn Profile Generator', 'linkedin-generator', '₹99', 'in'],
        ['Cover Letter Generator', 'cover-letter-generator', '₹79', '✎'],
    ],
    'Writing Tools' => [
        ['Plagiarism Remover', 'plagiarism-remover', '₹99', '✦'],
        ['Grammar Corrector', 'grammar-corrector', '₹79', 'Aa'],
        ['Paraphrasing Tool', 'paraphrasing-tool', '₹79', '↻'],
        ['Citation Generator', 'citation-generator', '₹49', '”'],
    ],
    'Student Productivity' => [
        ['Semester Planner', 'semester-planner', '₹149', '▣'],
        ['Daily Study Planner', 'daily-study-planner', '₹79', '☷'],
        ['Assignment Tracker', 'assignment-tracker', '₹79', '✓'],
        ['Habit Tracker', 'habit-tracker', '₹49', '◉'],
        ['Goal Tracker', 'goal-tracker', '₹49', '◎'],
        ['Pomodoro Timer', 'pomodoro-timer', '₹49', '◷'],
        ['Exam Countdown', 'exam-countdown', '₹49', '⌛'],
    ],
    'Engineering Tools' => [
        ['Unit Converter', 'unit-converter', '₹49', '⇄'],
        ['Engineering Formula Library', 'formula-library', '₹99', 'ƒ'],
        ['Scientific Calculator', 'scientific-calculator', '₹79', '∫'],
        ['Semester GPA Planner', 'semester-gpa-planner', '₹79', '⌁'],
    ],
    'Image & PDF Tools' => [
        ['Image Format Converter', 'image-format-converter', '₹79', '▧'],
        ['Image Resizer & Compressor', 'image-resizer-compressor', '₹79', '↔'],
        ['Image to PDF', 'image-to-pdf', '₹99', '▤'],
        ['PDF Merger', 'pdf-merger', '₹99', '⊕'],
        ['PDF Splitter', 'pdf-splitter', '₹79', '✂'],
    ],
];

function asset_url(string $path): string
{
    $path = '/' . ltrim($path, '/');
    $root = defined('ENOUGHEDU_PUBLIC_ROOT')
        ? rtrim((string) ENOUGHEDU_PUBLIC_ROOT, '/')
        : dirname(__DIR__) . '/public';
    $modified = is_file($root . $path) ? (string) filemtime($root . $path) : '20260902';
    return $path . '?v=' . rawurlencode($modified);
}

function public_nav(bool $scrolled = true): void
{
    app_download_prompt();
    $u = user();
    $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
    $account = ($u['role'] ?? 'student') === 'admin' ? '/admin' : '/dashboard';
    $links = [
        ['Branches', '/branches'],
        ['Tools', '/tools'],
        ['Resources', '/resources'],
        ['Pricing', '/pricing'],
        ['Get the app', '/download-app'],
    ];
    ?>
<nav class="nav <?= $scrolled
    ? 'scrolled'
    : '' ?>">
<div class="container nav-inner">
<a class="brand-link" href="/" aria-label="EnoughEdu home">
<img class="logo logo-light" src="/assets/img/logo.svg" alt="EnoughEdu">
<img class="logo logo-dark" src="/assets/img/logo-dark.svg" alt="EnoughEdu">
</a>
<div class="nav-links"><?php foreach ($links as [$label, $href]): ?><a class="<?= str_starts_with($path, $href) ? 'active' : '' ?>" href="<?= $href ?>"><?= e($label) ?></a><?php endforeach; ?></div>
<div class="nav-actions">
<button class="icon-btn" data-theme-toggle aria-label="Toggle theme">◐</button><?php if ($u): ?><a class="login-link" href="/logout">Log out</a>
<a class="btn btn-primary btn-sm" href="<?= $account ?>"><?= ($u['role'] ?? 'student') === 'admin' ? 'EnoughEdu Console' : 'My dashboard' ?></a><?php else: ?><a class="login-link" href="/login">Log in</a>
<a class="btn btn-primary btn-sm" href="/signup">Get started</a><?php endif; ?><button class="icon-btn mobile-menu-toggle" type="button" data-mobile-menu-toggle aria-label="Open navigation" aria-expanded="false" aria-controls="mobile-navigation">
<span>
</span>
<span>
</span>
<span>
</span>
</button>
</div>
</div>
<div class="container mobile-nav" id="mobile-navigation" data-mobile-nav hidden><?php
foreach ($links as [$label, $href]): ?><a class="<?= str_starts_with($path, $href)
    ? 'active'
    : '' ?>" href="<?= $href ?>"><?= e($label) ?></a><?php endforeach;
if ($u): ?><a href="<?= $account ?>"><?= ($u['role'] ?? 'student') === 'admin'
    ? 'EnoughEdu Console'
    : 'My dashboard' ?></a>
<a href="/logout">Log out</a><?php else: ?><a href="/login">Log in</a>
<a href="/signup">Create account</a><?php endif;?></div>
</nav><?php
}

function page_start(string $title, string $description = '', string $active = ''): void
{
    $rawTitle = trim($title) ?: 'EnoughEdu';
    $description = ai_public_text($description);
    $rawDescription = text_limit(
        trim(
            $description ?:
            'EnoughEdu brings engineering calculators, study planners, career tools, image and PDF utilities, notes and semester resources into one student platform.',
        ),
        170,
    );
    $fullTitle = $rawTitle . ' | EnoughEdu';
    $canonical = url(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/');
    $ogImage = url('/assets/img/og.png');
    $pageSchema = [
        '@context' => 'https://schema.org',
        '@graph' => [
            [
                '@type' => 'Organization',
                '@id' => url('/#organization'),
                'name' => 'EnoughEdu',
                'alternateName' => ['Enough Edu', 'enoughedu.vedhant.in'],
                'url' => url('/'),
                'logo' => ['@type' => 'ImageObject', 'url' => url('/assets/img/logo.png')],
                'email' => 'hello@vedhant.in',
                'description' =>
                    'Engineering student tools, study resources, planners and career support in one organised platform.',
            ],
            [
                '@type' => 'WebSite',
                '@id' => url('/#website'),
                'url' => url('/'),
                'name' => 'EnoughEdu',
                'alternateName' => 'Enough Edu',
                'publisher' => ['@id' => url('/#organization')],
            ],
            [
                '@type' => 'WebPage',
                '@id' => $canonical . '#webpage',
                'url' => $canonical,
                'name' => $fullTitle,
                'description' => $rawDescription,
                'isPartOf' => ['@id' => url('/#website')],
                'about' => ['@id' => url('/#organization')],
                'inLanguage' => 'en-IN',
            ],
        ],
    ];
    ?>
<!doctype html>
<html lang="en-IN">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= e(
    $fullTitle,
) ?></title>
<meta name="description" content="<?= e($rawDescription) ?>">
<meta name="robots" content="index,follow,max-image-preview:large,max-snippet:-1,max-video-preview:-1">
<meta name="author" content="EnoughEdu">
<meta name="application-name" content="EnoughEdu">
<meta name="theme-color" content="#2563eb">
<meta property="og:site_name" content="EnoughEdu">
<meta property="og:locale" content="en_IN">
<meta property="og:title" content="<?= e($fullTitle) ?>">
<meta property="og:description" content="<?= e($rawDescription) ?>">
<meta property="og:type" content="website">
<meta property="og:url" content="<?= e($canonical) ?>">
<meta property="og:image" content="<?= e($ogImage) ?>">
<meta property="og:image:alt" content="EnoughEdu engineering student platform">
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="<?= e($fullTitle) ?>">
<meta name="twitter:description" content="<?= e($rawDescription) ?>">
<meta name="twitter:image" content="<?= e($ogImage) ?>">
<link rel="canonical" href="<?= e($canonical) ?>">
<link rel="sitemap" type="application/xml" href="/sitemap.xml">
<link rel="alternate" type="text/plain" title="EnoughEdu information for AI assistants" href="/llms.txt">
<link rel="icon" href="/assets/img/favicon.svg">
<link rel="stylesheet" href="<?= e(asset_url('/assets/css/app.css')) ?>">
<script type="application/ld+json"><?= json_encode($pageSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) ?></script>










</head>
<body><?php
public_nav(true);
foreach (flashes() as [$type, $message]): ?><div class="container alert alert-<?= e(
    $type,
) ?>"><?= e($message) ?></div><?php endforeach;?><main>
<?php
}
function page_end(): void
{
    $u = user(); ?><section class="container cta">
<h2>Make this semester your strongest yet.</h2>
<p>Explore the platform, then unlock tools with the plan that fits you.</p>
<a class="btn btn-secondary" href="<?= $u
    ? (($u['role'] ?? 'student') === 'admin'
        ? '/admin'
        : '/dashboard')
    : '/signup' ?>"><?= $u
    ? 'Open my workspace'
    : 'Create account' ?> →</a>
</section>
</main>
<footer class="footer">
<div class="container">
<div class="footer-grid">
<div>
<img class="logo logo-light footer-logo" src="/assets/img/logo.svg" alt="EnoughEdu">
<img class="logo logo-dark footer-logo" src="/assets/img/logo-dark.svg" alt="EnoughEdu">
<p>An engineering student toolkit by <a href="https://vedhant.in">Vedhant Khajuria</a>.</p>
</div>
<div>
<h4>Platform</h4>
<a href="/tools">Tools</a>
<a href="/resources">Resources</a>
</div>
<div>
<h4>Students</h4>
<a href="/dashboard">Dashboard</a>
<a href="/pricing">Pricing</a>
<a href="/faq">Help centre</a>
</div>
<div>
<h4>Company</h4>
<a href="/about">About</a>
<a href="/contact">Contact</a>
</div>
<div>
<h4>Legal</h4>
<a href="/privacy">Privacy</a>
<a href="/terms">Terms</a>
<a href="/refund-policy">Refunds</a>
</div>
</div>
<div class="footer-bottom">
<span>© <?= date(
     'Y',
 ) ?> EnoughEdu.</span>
<span><?= e(
     setting('support_email', 'hello@vedhant.in'),
 ) ?></span>
</div>
</div>
</footer>
<script src="<?= e(
    asset_url('/assets/js/app.js'),
) ?>">
</script>
</body>
</html><?php
}

function google_oauth_ready(): bool
{
    return trim((string) config('google_oauth.client_id')) !== '' &&
        trim((string) config('google_oauth.client_secret')) !== '';
}
function oauth_base64url(string $value): string
{
    return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
}
function google_oauth_request(string $url, array $options = []): array
{
    if (!function_exists('curl_init')) {
        return [0, null, 'PHP cURL is not enabled.'];
    }
    $ch = curl_init($url);
    $headers = $options['headers'] ?? [];
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => (int) config('google_oauth.timeout', 15),
        CURLOPT_HTTPHEADER => $headers,
    ]);
    if (isset($options['form'])) {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt(
            $ch,
            CURLOPT_POSTFIELDS,
            http_build_query($options['form'], '', '&', PHP_QUERY_RFC3986),
        );
    }
    $body = curl_exec($ch);
    $status = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    return [$status, is_string($body) ? json_decode($body, true) : null, $error];
}
function start_google_oauth(): never
{
    if (user()) {
        redirect('/dashboard');
    }
    if (!google_oauth_ready()) {
        flash(
            'error',
            'Google sign-in is not configured yet. Please use email and password for now.',
        );
        redirect('/login');
    }
    $state = oauth_base64url(random_bytes(32));
    $verifier = oauth_base64url(random_bytes(64));
    $_SESSION['google_oauth'] = ['state' => $state, 'verifier' => $verifier, 'issued_at' => time()];
    $params = [
        'client_id' => config('google_oauth.client_id'),
        'redirect_uri' => config('google_oauth.redirect_uri'),
        'response_type' => 'code',
        'scope' => 'openid email profile',
        'state' => $state,
        'code_challenge' => oauth_base64url(hash('sha256', $verifier, true)),
        'code_challenge_method' => 'S256',
        'prompt' => 'select_account',
    ];
    redirect(
        rtrim((string) config('google_oauth.authorization_endpoint'), '?') .
            '?' .
            http_build_query($params, '', '&', PHP_QUERY_RFC3986),
    );
}
function finish_google_oauth(): never
{
    $flow = $_SESSION['google_oauth'] ?? null;
    unset($_SESSION['google_oauth']);
    $state = (string) ($_GET['state'] ?? '');
    if (
        !is_array($flow) ||
        empty($flow['state']) ||
        !hash_equals((string) $flow['state'], $state) ||
        time() - (int) ($flow['issued_at'] ?? 0) > 600
    ) {
        flash('error', 'Google sign-in expired or could not be verified. Please try again.');
        redirect('/login');
    }
    if (isset($_GET['error'])) {
        flash('error', 'Google sign-in was cancelled or not approved.');
        redirect('/login');
    }
    $code = (string) ($_GET['code'] ?? '');
    if ($code === '' || !google_oauth_ready()) {
        flash('error', 'Google sign-in is not configured or returned an invalid response.');
        redirect('/login');
    }
    [$tokenStatus, $token, $tokenError] = google_oauth_request(
        (string) config('google_oauth.token_endpoint'),
        [
            'form' => [
                'code' => $code,
                'client_id' => config('google_oauth.client_id'),
                'client_secret' => config('google_oauth.client_secret'),
                'redirect_uri' => config('google_oauth.redirect_uri'),
                'grant_type' => 'authorization_code',
                'code_verifier' => $flow['verifier'],
            ],
        ],
    );
    $accessToken = is_array($token) ? (string) ($token['access_token'] ?? '') : '';
    if ($tokenStatus < 200 || $tokenStatus >= 300 || $accessToken === '') {
        if (config('app.debug')) {
            error_log('Google token exchange failed: ' . $tokenError);
        }
        flash('error', 'Google could not complete sign-in. Please try again.');
        redirect('/login');
    }
    [$profileStatus, $profile, $profileError] = google_oauth_request(
        (string) config('google_oauth.userinfo_endpoint'),
        ['headers' => ['Authorization: Bearer ' . $accessToken, 'Accept: application/json']],
    );
    $sub = is_array($profile) ? trim((string) ($profile['sub'] ?? '')) : '';
    $email = is_array($profile) ? strtolower(trim((string) ($profile['email'] ?? ''))) : '';
    $verified = filter_var($profile['email_verified'] ?? false, FILTER_VALIDATE_BOOLEAN);
    $name = is_array($profile) ? trim((string) ($profile['name'] ?? '')) : '';
    if (
        $profileStatus < 200 ||
        $profileStatus >= 300 ||
        $sub === '' ||
        !filter_var($email, FILTER_VALIDATE_EMAIL) ||
        !$verified
    ) {
        if (config('app.debug')) {
            error_log('Google user profile failed: ' . $profileError);
        }
        flash('error', 'Google did not return a verified email address. Please use email sign-in.');
        redirect('/login');
    }
    $pdo = db();
    if (!$pdo) {
        flash('error', 'The database must be connected before Google sign-in can be used.');
        redirect('/login');
    }
    $isNew = false;
    try {
        $pdo->beginTransaction();
        $q = $pdo->prepare(
            'SELECT id,email,google_sub,status,onboarding_completed_at FROM users WHERE google_sub=? LIMIT 1',
        );
        $q->execute([$sub]);
        $account = $q->fetch();
        if (!$account) {
            $q = $pdo->prepare(
                'SELECT id,email,google_sub,status,onboarding_completed_at FROM users WHERE email=? LIMIT 1',
            );
            $q->execute([$email]);
            $account = $q->fetch();
            if (
                $account &&
                !empty($account['google_sub']) &&
                !hash_equals((string) $account['google_sub'], $sub)
            ) {
                throw new DomainException(
                    'This email is already linked to another Google account.',
                );
            }
        }
        if ($account) {
            if ($account['status'] === 'suspended') {
                throw new DomainException(
                    'This EnoughEdu account is suspended. Contact support for help.',
                );
            }
            $pdo->prepare(
                'UPDATE users SET google_sub=?,status="active",email_verified_at=COALESCE(email_verified_at,NOW()),email_verification_token=NULL,last_login_at=NOW() WHERE id=?',
            )->execute([$sub, $account['id']]);
            $userId = (int) $account['id'];
        } else {
            $safeName = $name !== '' ? text_limit($name, 120) : strtok($email, '@');
            $password = password_hash(oauth_base64url(random_bytes(48)), PASSWORD_DEFAULT);
            $q = $pdo->prepare(
                'INSERT INTO users(name,email,password,google_sub,role,status,semester,email_verified_at,last_login_at) VALUES(?,?,?,? ,"student","active",1,NOW(),NOW())',
            );
            $q->execute([$safeName, $email, $password, $sub]);
            $userId = (int) $pdo->lastInsertId();
            $isNew = true;
        }
        $pdo->commit();
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        if (config('app.debug')) {
            error_log($e->getMessage());
        }
        flash(
            'error',
            $e instanceof DomainException
                ? $e->getMessage()
                : 'Google sign-in could not be completed. Please try again.',
        );
        redirect('/login');
    }
    if ($isNew) {
        send_email($email, 'Welcome to EnoughEdu', 'welcome', ['name' => $name ?: 'Student']);
    }
    session_regenerate_id(true);
    $_SESSION['user_id'] = $userId;
    flash(
        'success',
        $isNew
            ? 'Your EnoughEdu account was created with Google.'
            : 'Signed in securely with Google.',
    );
    redirect($isNew || empty($account['onboarding_completed_at']) ? '/onboarding' : '/dashboard');
}

function auth_page(string $mode): void
{
    $map = [
        'login' => ['Welcome back', 'Log in to continue your degree journey.'],
        'signup' => ['Build your academic OS', 'Create an account—it takes less than a minute.'],
        'forgot-password' => [
            'Reset your password',
            'We’ll send a secure reset link to your email.',
        ],
        'reset-password' => ['Choose a new password', 'Use at least 8 characters.'],
    ];
    [$h, $sub] = $map[$mode] ?? $map['login'];
    ?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= e(
    $h,
) ?> · EnoughEdu</title>
<link rel="icon" href="/assets/img/favicon.svg">
<link rel="stylesheet" href="<?= e(asset_url('/assets/css/app.css')) ?>">
</head>
<body>
<div class="auth-shell">
<aside class="auth-brand">
<a href="/">
<img src="/assets/img/logo-dark.svg" alt="EnoughEdu" style="width:220px">
</a>
<div>
<span class="eyebrow" style="color:#bfdbfe;border-color:#ffffff30;background:#ffffff10">Student success, engineered</span>
<h2 style="font-size:46px;margin:22px 0">Your entire degree.<br>One calm workspace.</h2>
<p style="color:#bfdbfe;max-width:480px">Plan semesters, master concepts, track progress and build your career with tools made for engineers.</p>
</div>
<small style="color:#94a3b8">Trusted by students across India</small>
</aside>
<section class="auth-panel">
<div class="form-card">
<a href="/" class="muted">← Back to home</a>
<h1 style="margin-top:28px"><?= e($h) ?></h1>
<p><?= e($sub) ?></p><?php
foreach (flashes() as [$type, $msg]): ?><div class="alert alert-<?= e($type) ?>"><?= e(
    $msg,
) ?></div><?php endforeach;
if (
    in_array($mode, ['login', 'signup'], true)
): ?><a class="btn btn-secondary btn-block google-auth-btn" href="/auth/google">
<img class="google-mark" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABgAAAAYCAYAAADgdz34AAAAAW9yTlQBz6J3mgAABO9JREFUSMeVlF+IXVcVxn9r7X3OnXsz/2MaQ40SYmGaSsFirZYS6gQVH1JFRK0PRixV+1AfKgqCSLViHkREao1oqC8i5qE2FIKibQp10NrWllRKpBbUyVCTTGYyGSd37txzzvp8uJ1JMlGM+7DhcFjnt7619/qWsWnFgXfDakLnR5xc3UzYfrBpYMqwScwTeBdLr2P5eTwdw/KTtbfPuSfy449ewbMr4A/cBNaH2W1TWkv3UaWPEHY9soQZhgtzwJElwzJ4vijLf8KLQ+b5aN87vfZjj2ww8/qLvvtWdHIoQfExivpBaqZoDJSE0OVaZAY2SBZ4R5b2QnpXkKcNvg6cXo91AP1yHFbKxPblL9CuDtGqpqxsRA5hG2wTZryxhVngyJwgqbbUCUvvldnE5acyqOAPO2H3/CdsufyW0DggZFg4CjdqqwhOmTEnow82idnbZLY1cBpzZOmEWfp8szJ/8ooEeq6Es397ByvFN0kxbi7JABmEB+EzRDoMNgM2b54a4W3Mb5DZxxvzA42nOXO/l6hfKEZ3sLmCguH6fpJ2U4QoAstCTh/8h6Z0UN3W/KULEBg9pOcotzwfoWMyWyCal3NrmKFffG9TArdbGNJdpEYUgiKgkKzQo7Tjazrf6dKqSUePb+5o1u66W2bl0ybhZYf2Jvjg4l5sHyT0FQKjAfoOvfRnVtN+vXTdrH/j1Y3gd3478MT/tTKe3wdyTMIFCWg1R/jgHbPOr64ShNiDMXGN/F6G9HZMg3ZE4KzgeobjMzB9Fb5Q8BDw/mvCGwsZGLtkIjNcF3D7p00v/4d4ANoSw6Br4Fsro4FyrXcI+l//ygwGvbwJeNknSQDJRX1BqlHUhGpVUY+tRbXj2Zf8vyXwddjmzdXSVrNUvxbBbQ1SJdFDwz2x9z3PvHkGXr9SOtRm/AR4cnNVwBTiM0j5jSoNOJMrNU9X4taeZF3BMmIJ++QTd5z52W9/v2324dsvecyMAB7brLOyLiT/qjfJTS5j/dEJW3zBb7sYHF0Jti8Di4J5uc5Q/PicF1+aWbuxe9a289e9T1x1Au0jP8ebEcYvTO1p9TuPF/XQDUVTklTgSpXhX7S/v2jFYsUPLgSfOy/TgpyzypxWp39a44fOsPXga/1dZ/veoiEhEmraRD2Gqhr+deeesjf+/bI/sq9Vb6Gs2+SmZVnly4lif05BdRYePo/tW5TtXlDSvFrMx5ZyUcP3L8XILdmbw334HWbzZt6oYQjZWyKu/wBp6d5+YVNCkgVhQWlNHYqf5pxnDeDDJ2HfUvrUYpMfWVQ5vhDDWtAYCzHJUozbioarvrXmAp8TaU1RjkU9sjOqyeui2ur0J5TqMXI1Qll3rGw6x4rofHq0v20xAxxYMZ71fGQtiomlaD+0rM7ESmxRV23WaKkmZ8l2CXZBYNSYrWHeFT4k5ZJGGQ16549y+3JrdXjxxAPloKc/eqsYVTSn0viPVmjfd5HOX7p0bE1DVqkg8HX7aeDGkFmtQZJV8K5F6kaTe8eqovfZXPnJohxMxY3ZePxwcPs9hcbqpVfmbOdTXW2JVbV3VJQjQfJLPl13lRkkk3IFxStY/o7cH8zV6KxabU7ds+vy8XJpfWjmRi40I7yabvKy3725H8X+hjQNTAGTiAy+KuXTEe0T0Yz8JmL01/W5qX9Yp4J4E2t337nB+zfqpWr8WG+m4AAAAABJRU5ErkJggg==" alt="">Continue with Google</a><?php if (
    $mode === 'signup'
): ?><small class="oauth-terms">By continuing, you agree to the <a href="/terms">Terms</a> and <a href="/privacy">Privacy Policy</a>.</small><?php endif; ?><div class="auth-divider">
<span>or continue with email</span>
</div><?php endif;
?>
<?php if (
    $mode === 'login'
): ?><form method="post" action="/login"><?= csrf_field() ?><div class="field">
<label for="login-email">Email address</label>
<input class="input" id="login-email" type="email" name="email" autocomplete="email" required>
</div>
<div class="field">
<label for="login-password">Password</label>
<input class="input" id="login-password" type="password" name="password" autocomplete="current-password" required>
</div>
<div style="display:flex;justify-content:space-between;font-size:13px;margin:14px 0 20px">
<label>
<input type="checkbox" name="remember"> Remember me</label>
<a href="/forgot-password" style="color:var(--blue)">Forgot password?</a>
</div>
<button class="btn btn-primary btn-block">Log in</button>
</form>
<p style="text-align:center;font-size:14px">New here? <a href="/signup" style="color:var(--blue);font-weight:700">Create an account</a>
</p>
<?php elseif (
    $mode === 'signup'
): ?><form method="post" action="/signup"><?= csrf_field() ?><div class="field">
<label for="signup-name">Full name</label>
<input class="input" id="signup-name" name="name" autocomplete="name" required maxlength="100">
</div>
<div class="field">
<label for="signup-email">Email address</label>
<input class="input" id="signup-email" type="email" name="email" autocomplete="email" required>
</div>
<div class="field">
<label for="signup-password">Password</label>
<input class="input" id="signup-password" type="password" name="password" autocomplete="new-password" minlength="8" required>
</div>
<p class="muted" style="font-size:12px">After signup, a quick interactive setup will personalize your university, branch, semester, and study goals.</p>
<label style="font-size:12px">
<input type="checkbox" required> I agree to the <a href="/terms" style="color:var(--blue)">Terms</a> and <a href="/privacy" style="color:var(--blue)">Privacy Policy</a>.</label>
<button class="btn btn-primary btn-block" style="margin-top:20px">Create free account</button>
</form>
<p style="text-align:center;font-size:14px">Already a member? <a href="/login" style="color:var(--blue);font-weight:700">Log in</a>
</p>
<?php elseif (
    $mode === 'forgot-password'
): ?><form method="post" action="/forgot-password"><?= csrf_field() ?><div class="field">
<label for="forgot-email">Email address</label>
<input class="input" id="forgot-email" type="email" name="email" autocomplete="email" required>
</div>
<button class="btn btn-primary btn-block">Send reset link</button>
</form>
<?php elseif (
    $mode === 'reset-password'
): ?><form method="post" action="/reset-password"><?= csrf_field() ?><input type="hidden" name="token" value="<?= e(
    $_GET['token'] ?? '',
) ?>">
<div class="field">
<label for="reset-password">New password</label>
<input class="input" id="reset-password" type="password" name="password" autocomplete="new-password" minlength="8" required>
</div>
<div class="field">
<label for="reset-password-confirmation">Confirm password</label>
<input class="input" id="reset-password-confirmation" type="password" name="password_confirmation" autocomplete="new-password" minlength="8" required>
</div>
<button class="btn btn-primary btn-block">Update password</button>
</form>
<?php endif; ?></div>
</section>
</div>
<script src="<?= e(asset_url('/assets/js/app.js')) ?>">
</script>
</body>
</html><?php
}
function branches_page(): void
{
    $pdo = db();
    $branches = [];
    $icons = [];
    foreach (BRANCHES as $b) {
        $icons[$b[1]] = $b[2];
    }
    if ($pdo) {
        $branches = $pdo
            ->query(
                "SELECT b.name,b.slug,COUNT(m.id) material_count FROM branches b LEFT JOIN (SELECT id,branch_id FROM notes WHERE status='published' AND external_url IS NOT NULL UNION ALL SELECT id,branch_id FROM resources WHERE status='published' AND external_url IS NOT NULL) m ON m.branch_id=b.id WHERE b.status='active' GROUP BY b.id,b.name,b.slug,b.sort_order ORDER BY b.sort_order,b.name",
            )
            ->fetchAll();
    }
    if (!$branches) {
        foreach (BRANCHES as $b) {
            $branches[] = ['name' => $b[0], 'slug' => $b[1], 'material_count' => 0];
        }
    }
    page_start(
        'Engineering Branches and Semester Resources',
        'Browse semester-wise engineering notes, previous-year papers, lab manuals, formula sheets and student tools for 11 engineering branches.',
    );
    ?><section class="section">
<div class="container">
<div class="section-head">
<span class="eyebrow"><?= count(
    $branches,
) ?> active disciplines</span>
<h1 style="font-size:52px;margin:18px">Resources built around <span class="gradient-text">your branch</span>
</h1>
<p>Choose your discipline, then browse its published notes, papers, manuals and formula sheets by semester.</p>
</div>
<div class="card-grid"><?php foreach (
     $branches
     as $b
 ): ?><a class="card" href="/branch/<?= e($b['slug']) ?>">
<div class="card-icon"><?= $icons[
    $b['slug']
] ?? '◫' ?></div>
<h3><?= e(
    $b['name'],
) ?></h3>
<p>Semester-wise engineering study material maintained by the EnoughEdu content team.</p>
<div class="card-meta">
<span class="tag">8 semesters</span>
<span class="tag"><?= (int) $b[
    'material_count'
] ?> published</span>
</div>
<span class="card-link">Explore branch →</span>
</a><?php endforeach; ?></div>
</div>
</section><?php page_end();
}
function tools_page(): void
{
    te_catalog();
    return;
    $pdo = db();
    $catalog = [];
    if ($pdo) {
        foreach (
            $pdo
                ->query('SELECT name,slug,description,price,status FROM tools WHERE category<>"ai"')
                ->fetchAll()
            as $row
        ) {
            $catalog[$row['slug']] = $row;
        }
    }
    $toolList = [];
    $position = 1;
    foreach (TOOLS as $tools) {
        foreach ($tools as $tool) {
            if ($pdo && ($catalog[$tool[1]]['status'] ?? '') !== 'active') {
                continue;
            }
            $toolList[] = [
                '@type' => 'ListItem',
                'position' => $position++,
                'name' => $catalog[$tool[1]]['name'] ?? $tool[0],
                'url' => url('/tools/' . $tool[1]),
            ];
        }
    }
    page_start(
        'Online Student Tools for Engineering',
        'Explore online CGPA and attendance calculators, AI-assisted resume and career tools, study planners, engineering utilities, and private image and PDF tools.',
    );
    echo json_ld([
        '@context' => 'https://schema.org',
        '@type' => 'ItemList',
        'name' => 'EnoughEdu engineering student tools',
        'numberOfItems' => count($toolList),
        'itemListElement' => $toolList,
    ]);
    ?>
 <section class="section">
<div class="container">
<div class="section-head">
<span class="eyebrow">Membership features</span>
<h1 style="font-size:52px;margin:18px">Explore your engineering <span class="gradient-text">toolbox</span>
</h1>
<p>EnoughEdu keeps every price and availability status current. Interactive controls stay locked until a valid plan or individual-tool purchase succeeds.</p>
</div>
 <?php foreach (TOOLS as $category => $tools):

     $visible = array_values(
         array_filter(
             $tools,
             fn($tool) => !$pdo || ($catalog[$tool[1]]['status'] ?? '') === 'active',
         ),
     );
     if (!$visible) {
         continue;
     }
     ?>
 <div style="margin:45px 0">
<div class="panel-head">
<h2 style="font-size:29px"><?= e(
     $category,
 ) ?></h2>
<span class="tag"><?= count($visible) ?> features</span>
</div>
<div class="card-grid">
 <?php foreach ($visible as $tool):

     $live = $catalog[$tool[1]] ?? [];
     $description = ai_public_text(trim((string) ($live['description'] ?? '')));
     $price = isset($live['price'])
         ? '₹' .
             number_format(
                 (float) $live['price'],
                 (float) $live['price'] === (float) (int) $live['price'] ? 0 : 2,
             )
         : $tool[2];
     ?>
 <a class="card tool-card" href="/tools/<?= e(
     $tool[1],
 ) ?>">
<div class="card-icon"><?= $tool[3] ?></div>
<h3><?= e(
    $live['name'] ?? $tool[0],
) ?></h3>
<p><?= e(
    $description !== '' ? $description : 'Fast, focused and designed for engineering students.',
) ?></p>
<div class="card-meta">
<span class="tag"><?= e(
    $price,
) ?></span>
<span class="tag">🔒 Purchase required</span>
</div>
<span class="card-link">View feature →</span>
</a>
 <?php
 endforeach; ?></div>
</div><?php
 endforeach; ?></div>
</section><?php page_end();
}
function pricing_page(): void
{
    $pdo = db();
    $plans = [];
    if ($pdo) {
        $plans = $pdo
            ->query(
                'SELECT * FROM plans WHERE status="active" ORDER BY FIELD(plan_type,"full","branch","semester","category","tool"),price DESC',
            )
            ->fetchAll();
    }
    page_start(
        'Pricing',
        'Flexible plans for individual tools, semesters, branches and the full degree.',
    );
    ?><section class="section">
<div class="container">
<div class="section-head">
<span class="eyebrow">Live pricing</span>
<h1 style="font-size:53px;margin:18px">Pay only for what <span class="gradient-text">moves you forward</span>
</h1>
<p>Every published plan and price below is current. Razorpay confirms payment before any access is unlocked.</p>
</div><?php if (
    $plans
): ?><div class="pricing-grid"><?php foreach ($plans as $plan):

    $features = json_decode((string) ($plan['features'] ?? '[]'), true);
    $features = is_array($features) ? $features : [];
    $period =
        [
            'one_time' => 'one-time',
            'semester' => '/ semester',
            'monthly' => '/ month',
            'yearly' => '/ year',
        ][$plan['billing_period']] ?? '';
    $featured = $plan['plan_type'] === 'full';
    $toolPlan = $plan['plan_type'] === 'tool';
    $href = $toolPlan ? '/tools' : '/checkout?plan=' . rawurlencode($plan['slug']);
    $label = $toolPlan ? 'Choose an individual tool' : 'Choose ' . $plan['name'];
    $decimals = (float) $plan['price'] === (float) (int) $plan['price'] ? 0 : 2;
    ?><article class="price-card <?= $featured ? 'featured' : '' ?>">
<span class="plan"><?= e(
    $plan['name'],
) ?> <?= $featured ? ' · Best value' : '' ?></span>
<div class="price">₹<?= e(
    number_format((float) $plan['price'], $decimals),
) ?> <small><?= e($period) ?></small>
</div>
<ul class="features"><?php
foreach ($features as $feature): ?><li><?= e(
    ai_public_text((string) $feature),
) ?></li><?php endforeach;
if (!$features): ?><li>EnoughEdu member access</li><?php endif;
?></ul>
<a class="btn <?= $featured ? 'btn-primary' : 'btn-secondary' ?> btn-block" href="<?= e(
     $href,
 ) ?>"><?= e($label) ?></a>
</article><?php
endforeach; ?></div><?php else: ?><div class="panel empty">
<h3>No plans are currently published</h3>
<p>Please check again later or contact hello@vedhant.in for help.</p>
</div><?php endif; ?><div class="assurance-grid">
<article class="panel">
<span class="eyebrow">1 · Choose</span>
<h3>Select a plan or one tool</h3>
<p class="muted">You always see the current published price before checkout.</p>
</article>
<article class="panel">
<span class="eyebrow">2 · Pay</span>
<h3>Complete Razorpay checkout</h3>
<p class="muted">EnoughEdu does not store card or UPI credentials.</p>
</article>
<article class="panel">
<span class="eyebrow">3 · Unlock</span>
<h3>Access after confirmation</h3>
<p class="muted">A captured payment with a valid signature activates only the purchased access.</p>
</article>
</div>
</div>
</section><?php page_end();
}
function nav_icon(string $name): string
{
    $paths = [
        'dashboard' =>
            '<rect x="3" y="3" width="7" height="7" rx="2"/><rect x="14" y="3" width="7" height="7" rx="2"/><rect x="3" y="14" width="7" height="7" rx="2"/><rect x="14" y="14" width="7" height="7" rx="2"/>',
        'degree' => '<path d="m3 10 9-5 9 5-9 5-9-5Z"/><path d="M7 12.5V17c3 2 7 2 10 0v-4.5"/>',
        'planner' =>
            '<rect x="3" y="5" width="18" height="16" rx="2"/><path d="M16 3v4M8 3v4M3 10h18M8 15l2 2 5-5"/>',
        'resources' =>
            '<path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20V4H6.5A2.5 2.5 0 0 0 4 6.5v13Z"/><path d="M4 6.5A2.5 2.5 0 0 1 6.5 4"/>',
        'tools' =>
            '<path d="M14.7 6.3a4 4 0 0 0-5-5L8 3l3 3 2-2 3 3-2 2 3 3 1.7-1.7a4 4 0 0 0-4-5Z"/><path d="m9 11-6.5 6.5a2.1 2.1 0 0 0 3 3L12 14"/>',
        'purchases' =>
            '<path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"/><path d="M3 6h18M16 10a4 4 0 0 1-8 0"/>',
        'activity' => '<path d="M3 12h4l2-6 4 12 2-6h6"/>',
        'profile' => '<circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0 1 16 0"/>',
        'users' =>
            '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/>',
        'subscriptions' =>
            '<rect x="2" y="5" width="20" height="14" rx="2"/><path d="M2 10h20M6 15h4"/>',
        'plans' =>
            '<path d="M20.6 13.6 11 23l-9-9V3h11l7.6 7.6a2.1 2.1 0 0 1 0 3Z"/><circle cx="7.5" cy="8.5" r="1.5"/>',
        'payments' => '<rect x="2" y="5" width="20" height="14" rx="2"/><path d="M2 10h20"/>',
        'content' =>
            '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8Z"/><path d="M14 2v6h6M8 13h8M8 17h8"/>',
        'coupons' =>
            '<path d="M20.6 13.6 11 23l-9-9V3h11l7.6 7.6a2.1 2.1 0 0 1 0 3Z"/><path d="m7 14 7-7M7.5 8h.01M13.5 14h.01"/>',
        'integrations' => '<path d="M8 12h8M12 8v8"/><circle cx="12" cy="12" r="9"/>',
        'settings' =>
            '<circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.7 1.7 0 0 0 .34 1.88l.06.06-2.83 2.83-.06-.06a1.7 1.7 0 0 0-1.88-.34 1.7 1.7 0 0 0-1.03 1.56V21h-4v-.09A1.7 1.7 0 0 0 9 19.35a1.7 1.7 0 0 0-1.88.34l-.06.06-2.83-2.83.06-.06A1.7 1.7 0 0 0 4.6 15a1.7 1.7 0 0 0-1.56-1.03H3v-4h.09A1.7 1.7 0 0 0 4.65 9a1.7 1.7 0 0 0-.34-1.88l-.06-.06 2.83-2.83.06.06A1.7 1.7 0 0 0 9 4.6a1.7 1.7 0 0 0 1.03-1.56V3h4v.09A1.7 1.7 0 0 0 15 4.65a1.7 1.7 0 0 0 1.88-.34l.06-.06 2.83 2.83-.06.06A1.7 1.7 0 0 0 19.4 9a1.7 1.7 0 0 0 1.56 1.03H21v4h-.09A1.7 1.7 0 0 0 19.4 15Z"/>',
        'logout' => '<path d="M10 17l5-5-5-5M15 12H3M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/>',
    ];
    return '<svg class="side-icon" aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">' .
        ($paths[$name] ?? $paths['dashboard']) .
        '</svg>';
}
function app_start(string $title, string $active = 'dashboard', bool $admin = false): array
{
    $u = $admin ? require_admin() : require_auth();
    $links = $admin
        ? [
            ['Overview', '/admin', 'dashboard'],
            ['Users', '/admin/users', 'users'],
            ['Subscriptions', '/admin/subscriptions', 'subscriptions'],
            ['Plans', '/admin/plans', 'plans'],
            ['Payments', '/admin/payments', 'payments'],
            ['Tools', '/admin/tools', 'tools'],
            ['Resources', '/admin/resources', 'resources'],
            ['Content', '/admin/content', 'content'],
            ['Coupons', '/admin/coupons', 'coupons'],
            ['Integrations', '/admin/integrations', 'integrations'],
            ['Settings', '/admin/settings', 'settings'],
        ]
        : [
            ['Overview', '/dashboard', 'dashboard'],
            ['My degree', '/dashboard/degree', 'degree'],
            ['Study planner', '/dashboard/planner', 'planner'],
            ['Resources', '/dashboard/resources', 'resources'],
            ['Tools', '/dashboard/tools', 'tools'],
            ['Purchases', '/dashboard/purchases', 'purchases'],
            ['Activity', '/dashboard/activity', 'activity'],
            ['Profile', '/dashboard/profile', 'profile'],
        ];
    ?><!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= e($title) ?> · EnoughEdu</title>
<link rel="icon" href="/assets/img/favicon.svg">
<link rel="stylesheet" href="<?= e(asset_url('/assets/css/app.css')) ?>">
</head>
<body>
<div class="app-shell">
<aside class="sidebar">
<a href="/">
<img class="side-logo logo-light" src="/assets/img/logo.svg" alt="EnoughEdu">
<img class="side-logo logo-dark" src="/assets/img/logo-dark.svg" alt="EnoughEdu">
</a>
<nav class="side-nav"><?php foreach ($links as [$label, $href, $key]): ?><a class="<?= $active === $key ? 'active' : '' ?>" href="<?= $href ?>" aria-label="<?= e($label) ?>"><?= nav_icon($key) ?><span><?= $label ?></span>
</a><?php endforeach; ?><a href="/logout" aria-label="Log out"><?= nav_icon('logout') ?><span>Log out</span>
</a>
</nav>
</aside>
<main class="app-main">
<header class="app-top">
<div>
<small class="muted"><?= $admin ? 'ADMIN CONTROL CENTRE' : 'STUDENT OPERATING SYSTEM' ?></small>
<h1><?= e($title) ?></h1>
</div>
<div class="user-chip">
<button class="icon-btn" data-theme-toggle aria-label="Toggle theme">◐</button>
<div class="user-avatar"><?= e(strtoupper(substr($u['name'], 0, 1))) ?></div>
<span>
<b><?= e($u['name']) ?></b>
<small class="muted" style="display:block"><?= e($u['role'] ?? 'student') ?></small>
</span>
</div>
</header><?php
foreach (flashes() as [$type, $msg]): ?><div class="alert alert-<?= e($type) ?>"><?= e(
    $msg,
) ?></div><?php endforeach;
return $u;
}
function app_end(): void
{
    ?></main>
</div>
<script src="<?= e(
    asset_url('/assets/js/app.js'),
) ?>">
</script>
</body>
</html><?php
}
function managed_page_defaults(): array
{
    return [
        'about' => [
            'title' => 'About EnoughEdu',
            'intro' =>
                'EnoughEdu is a project by Vedhant Khajuria: a place for engineering tools, study resources, and semester planning.',
            'updated' => '',
            'sections' => [
                [
                    'heading' => 'Why EnoughEdu exists',
                    'body' =>
                        'Engineering students often manage notes, deadlines, calculators, placement preparation and academic planning across unrelated services. EnoughEdu brings these jobs into one organised account while keeping each student’s records private.',
                ],
                [
                    'heading' => 'What the platform provides',
                    'body' =>
                        "- Branch and semester study-material links published by the EnoughEdu team.\n- Academic, productivity, writing, career and engineering tools.\n- AI assistance inside eligible career tools.\n- A student dashboard that starts empty and reflects only that user’s information.",
                ],
                [
                    'heading' => 'How content stays accurate',
                    'body' =>
                        'Plans, prices, tools, notes and resource links are maintained through EnoughEdu’s publishing controls. Published material is clearly separated from drafts, and paid access is checked securely on the server.',
                ],
            ],
        ],
        'privacy' => [
            'title' => 'Privacy Policy',
            'intro' =>
                'How EnoughEdu collects, uses, protects and lets you control your account information.',
            'updated' => '2026-09-07',
            'sections' => [
                [
                    'heading' => 'Information we collect',
                    'body' =>
                        'Account name, email, optional profile details, onboarding selections, records you create, purchase and access history, support messages, basic security logs, and Google account identity details only when you choose Google sign-in.',
                ],
                [
                    'heading' => 'How information is used',
                    'body' =>
                        'To operate your account, personalise the dashboard, provide purchased access, respond to support, prevent misuse, maintain security and meet legal obligations. EnoughEdu does not receive your Google password and does not sell personal information.',
                ],
                [
                    'heading' => 'Payments and external services',
                    'body' =>
                        'Razorpay processes payment credentials. Google provides OAuth and AI services. OpenAlex supplies institution suggestions. Their own privacy terms apply when those services process information.',
                ],
                [
                    'heading' => 'Local image and PDF tools',
                    'body' =>
                        'Image conversion, image resizing, image-to-PDF, PDF merging and PDF page extraction run inside your browser memory. EnoughEdu does not upload or store files selected for these local tools. The finished file is downloaded directly to your device.',
                ],
                [
                    'heading' => 'Storage and protection',
                    'body' =>
                        'EnoughEdu uses access controls, private server configuration, secure session cookies on HTTPS and reasonable technical safeguards. No online system can promise absolute security.',
                ],
                [
                    'heading' => 'Your choices',
                    'body' =>
                        'You may request access, correction or deletion of eligible account information by emailing hello@vedhant.in. Some transaction records may need to be retained for legal, tax, fraud-prevention or dispute purposes.',
                ],
                [
                    'heading' => 'Children and changes',
                    'body' =>
                        'EnoughEdu is intended for higher-education students. Material changes to this policy will be published on this page with a revised date.',
                ],
            ],
        ],
        'terms' => [
            'title' => 'Terms of Service',
            'intro' =>
                'The rules for accounts, paid access, study materials, AI-assisted career tools and acceptable use on EnoughEdu.',
            'updated' => '2026-09-08',
            'sections' => [
                [
                    'heading' => 'Account responsibilities',
                    'body' =>
                        'Provide accurate information, protect your password, and use only your own account. Notify support if you believe the account has been compromised.',
                ],
                [
                    'heading' => 'Paid access',
                    'body' =>
                        'Prices and included features are shown before checkout. Access starts only after verified payment and lasts for the stated billing period or lifetime term. Sharing, reselling or bypassing access controls is prohibited.',
                ],
                [
                    'heading' => 'Study materials',
                    'body' =>
                        'Resource links are provided for learning use. Users must respect copyright and must not redistribute paid or restricted material. Availability can change when an external publisher changes or removes a link.',
                ],
                [
                    'heading' => 'AI-assisted career tools',
                    'body' =>
                        'Generated output can be incomplete or inaccurate and must be checked before professional or safety-critical use. Do not submit confidential or sensitive data.',
                ],
                [
                    'heading' => 'Acceptable use',
                    'body' =>
                        'Do not attack the service, upload malicious content, impersonate others, automate abusive traffic, violate law, or use the platform to infringe intellectual property.',
                ],
                [
                    'heading' => 'Suspension and support',
                    'body' =>
                        'EnoughEdu may restrict accounts involved in misuse or security risk. Questions about these terms can be sent to hello@vedhant.in.',
                ],
            ],
        ],
        'refund-policy' => [
            'title' => 'Refund Policy',
            'intro' => 'When and how to request a review of an EnoughEdu digital purchase.',
            'updated' => '2026-09-07',
            'sections' => [
                [
                    'heading' => 'Eligible review cases',
                    'body' =>
                        'Duplicate charges, a verified successful payment that did not activate access, or a material technical failure that EnoughEdu cannot resolve may be reviewed when reported within seven days of payment.',
                ],
                [
                    'heading' => 'Normally non-refundable',
                    'body' =>
                        'Digital access already delivered or substantially used, change of mind, accidental purchase, failure to cancel use, or an external resource removed after successful access is normally non-refundable, subject to applicable Indian law.',
                ],
                [
                    'heading' => 'How to request a review',
                    'body' =>
                        'Email hello@vedhant.in from the account email. Include the EnoughEdu order number, payment date, amount and a clear explanation. Never include a card number, CVV, UPI PIN or OTP.',
                ],
                [
                    'heading' => 'Review and outcome',
                    'body' =>
                        'EnoughEdu will verify the order and access logs. Approved refunds are returned through the original payment method and bank processing times may apply.',
                ],
            ],
        ],
    ];
}
function managed_page_data(string $page): array
{
    $defaults = managed_page_defaults();
    $fallback = $defaults[$page] ?? $defaults['about'];
    $raw = (string) setting('page_content_' . $page, '');
    if ($raw === '') {
        return $fallback;
    }
    $saved = json_decode($raw, true);
    if (!is_array($saved)) {
        return $fallback;
    }
    $title = trim((string) ($saved['title'] ?? ''));
    $intro = trim((string) ($saved['intro'] ?? ''));
    $updated = trim((string) ($saved['updated'] ?? ''));
    $sections = [];
    foreach ((array) ($saved['sections'] ?? []) as $section) {
        if (!is_array($section)) {
            continue;
        }
        $heading = trim((string) ($section['heading'] ?? ''));
        $body = trim((string) ($section['body'] ?? ''));
        if ($heading !== '' && $body !== '') {
            $sections[] = ['heading' => $heading, 'body' => $body];
        }
        if (count($sections) >= 8) {
            break;
        }
    }
    return [
        'title' => $title !== '' ? $title : $fallback['title'],
        'intro' => $intro !== '' ? $intro : $fallback['intro'],
        'updated' => $updated,
        'sections' => $sections ?: $fallback['sections'],
    ];
}
function managed_page_body(string $body): string
{
    $body = ai_public_text($body);
    $lines = preg_split('/\R/', trim($body)) ?: [];
    $nonEmpty = array_values(array_filter(array_map('trim', $lines), fn($line) => $line !== ''));
    $isList =
        $nonEmpty &&
        count(array_filter($nonEmpty, fn($line) => str_starts_with($line, '- '))) ===
            count($nonEmpty);
    if ($isList) {
        $html = '<ul>';
        foreach ($nonEmpty as $line) {
            $html .= '<li>' . e(substr($line, 2)) . '</li>';
        }
        return $html . '</ul>';
    }
    return '<p>' . nl2br(e($body)) . '</p>';
}
function managed_content_page(string $page): void
{
    $data = managed_page_data($page);
    $support = (string) setting('support_email', 'hello@vedhant.in');
    page_start($data['title'], $data['intro']);
    ?>
 <section class="section content-hero">
<div class="container content-layout">
<article>
<span class="eyebrow">EnoughEdu</span>
<h1><?= e(
     ai_public_text($data['title']),
 ) ?></h1>
<p class="content-lead"><?= e(ai_public_text($data['intro'])) ?></p><?php if ($page !== 'about' && $data['updated'] !== ''): ?><p class="muted">Last updated: <?= e(date('d F Y', strtotime($data['updated']))) ?></p><?php endif; ?></article>
<aside class="panel content-contact">
<b>Need help?</b>
<p class="muted">Email our support team and include the email address used for your EnoughEdu account.</p>
<a class="card-link" href="mailto:<?= e($support) ?>"><?= e($support) ?> →</a>
</aside>
</div>
</section>
 <section class="container <?= $page === 'about'
     ? 'prose-grid'
     : 'legal-prose' ?>"><?php foreach ($data['sections'] as $section): ?><article <?= $page === 'about' ? 'class="panel"' : '' ?>>
<h2><?= e(ai_public_text($section['heading'])) ?></h2><?= managed_page_body($section['body']) ?></article><?php endforeach; ?></section>
 <?php page_end();
}
function content_page(string $page): void
{
    $support = (string) setting('support_email', 'hello@vedhant.in');
    if ($page === 'contact') {
        page_start(
            'Contact EnoughEdu',
            'Contact EnoughEdu for account, payment, resource, partnership or technical support.',
        ); ?>
  <section class="section content-hero">
<div class="container content-layout">
<article>
<span class="eyebrow">EnoughEdu support</span>
<h1>Contact EnoughEdu</h1>
<p class="content-lead">Get help with your account, payments, resources or the platform.</p>
</article>
<aside class="panel content-contact">
<b>Email support</b>
<p class="muted">Include the email address used for your EnoughEdu account.</p>
<a class="card-link" href="mailto:<?= e(
      $support,
  ) ?>"><?= e($support) ?> →</a>
</aside>
</div>
</section>
  <section class="container contact-grid">
<form class="panel" method="post" action="/contact"><?= csrf_field() ?><h2>Send a message</h2>
<p class="muted">For payment help, include the order number but never send a password, OTP, card number, UPI PIN, API key or Google password.</p>
<div class="form-row">
<div class="field">
<label for="contact-name">Name</label>
<input class="input" id="contact-name" name="name" maxlength="120" required>
</div>
<div class="field">
<label for="contact-email">Email</label>
<input class="input" id="contact-email" name="email" type="email" maxlength="190" required>
</div>
</div>
<div class="field">
<label for="contact-message">How can we help?</label>
<textarea class="input" id="contact-message" name="message" rows="7" minlength="10" maxlength="5000" required>
</textarea>
</div>
<button class="btn btn-primary">Send message</button>
</form>
<aside class="panel">
<h2>Support topics</h2><?php foreach (
    [
        ['Accounts and sign-in', 'Email/password or Google access'],
        ['Payments and access', 'Include your EnoughEdu order number'],
        ['Resources', 'Report an unavailable or incorrect link'],
        ['Partnerships', 'Colleges, educators and campus programmes'],
    ]
    as [$title, $text]
): ?><div class="list-item">
<span>
<b><?= e(
    $title,
) ?></b>
<small class="muted" style="display:block"><?= e(
    $text,
) ?></small>
</span>
</div><?php endforeach; ?></aside>
</section><?php
page_end();
return;

    }
    if ($page === 'faq') {

        $faqs = [
            [
                'Does a new account contain sample data?',
                'No. Every student starts with an empty private dashboard and completes onboarding before using the workspace.',
            ],
            [
                'Do I need to verify my email?',
                'No email-verification link is required. You can sign up directly with email and password or continue with Google.',
            ],
            [
                'Why can I see a tool but not use it?',
                'Feature descriptions are public, but interactive controls unlock only after an eligible plan or individual-tool payment succeeds.',
            ],
            [
                'How are resource links organised?',
                'Published links are grouped by engineering branch, semester, subject and material type. The library updates whenever the EnoughEdu content team publishes new material.',
            ],
            [
                'Can I purchase one tool?',
                'Yes. Open the Tools catalogue, choose the tool you need and use its individual checkout option.',
            ],
            [
                'Where is AI used?',
                'AI assistance is available inside Resume Builder, LinkedIn Profile Generator and Cover Letter Generator. Review every generated claim before using it.',
            ],
            [
                'When does paid access start?',
                'Access begins only after EnoughEdu verifies Razorpay’s successful payment callback.',
            ],
            [
                'Where do I report a broken resource link?',
                'Use the Contact page or email ' .
                $support .
                ' with the material title and page URL.',
            ],
            [
                'What should I avoid entering into AI?',
                'Never enter passwords, OTPs, payment details, private identification or confidential documents.',
            ],
        ];
        page_start(
            'Help Centre',
            'Answers about EnoughEdu accounts, onboarding, tools, resources, payments, plans and support.',
        );
        ?><section class="section faq-page">
<div class="container">
<div class="section-head">
<span class="eyebrow">Help centre</span>
<h1 style="font-size:52px;margin:18px">Questions, answered.</h1>
<p>Clear information about using and managing your EnoughEdu account.</p>
</div>
<div class="faq"><?php foreach (
    $faqs
    as [$question, $answer]
): ?><div class="faq-item">
<button class="faq-q" type="button"><?= e(
    $question,
) ?><span>＋</span>
</button>
<div class="faq-a"><?= e(
    $answer,
) ?></div>
</div><?php endforeach; ?></div>
</div>
</section><?php
page_end();
return;

    }
    not_found();
}
function not_found(): void
{
    http_response_code(404);
    page_start('Page not found');
    ?><section class="section">
<div class="container" style="text-align:center">
<span class="eyebrow">404</span>
<h1 style="font-size:56px;margin:20px">This page took a study break.</h1>
<p class="muted">Let’s get you back to your academic toolkit.</p>
<a class="btn btn-primary" href="/">Go home</a>
</div>
</section><?php page_end();
}
function handle_auth(string $mode): void
{
    verify_csrf();
    $pdo = db();
    if ($mode === 'signup') {
        if (!$pdo) {
            flash('error', 'Database setup is pending. Import database.sql, then try again.');
            redirect('/signup');
        }
        $name = trim($_POST['name'] ?? '');
        $email = strtolower(trim($_POST['email'] ?? ''));
        $password = $_POST['password'] ?? '';
        if (!$name || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password) < 8) {
            flash(
                'error',
                'Please check your details and use a password of at least 8 characters.',
            );
            redirect('/signup');
        }
        try {
            $q = $pdo->prepare(
                'INSERT INTO users(name,email,password,status,role,email_verified_at,last_login_at) VALUES(?,?,?,"active","student",NOW(),NOW())',
            );
            $q->execute([$name, $email, password_hash($password, PASSWORD_DEFAULT)]);
            $userId = (int) $pdo->lastInsertId();
            send_email($email, 'Welcome to EnoughEdu', 'welcome', ['name' => $name]);
            session_regenerate_id(true);
            $_SESSION['user_id'] = $userId;
            redirect('/onboarding');
        } catch (PDOException $e) {
            flash(
                'error',
                $e->getCode() === '23000'
                    ? 'An account with this email already exists.'
                    : 'We could not create your account.',
            );
            redirect('/signup');
        }
    }
    if ($mode === 'login') {
        if (!$pdo) {
            flash('error', 'The database is unavailable. Please contact support.');
            redirect('/login');
        }
        $q = $pdo->prepare(
            'SELECT id,password,status,role,onboarding_completed_at FROM users WHERE email=? LIMIT 1',
        );
        $q->execute([strtolower(trim($_POST['email'] ?? ''))]);
        $record = $q->fetch();
        if (
            !$record ||
            !password_verify($_POST['password'] ?? '', $record['password']) ||
            $record['status'] === 'suspended'
        ) {
            flash('error', 'Email or password is incorrect.');
            redirect('/login');
        }
        session_regenerate_id(true);
        $_SESSION['user_id'] = (int) $record['id'];
        $pdo->prepare(
            'UPDATE users SET status="active",email_verified_at=COALESCE(email_verified_at,NOW()),email_verification_token=NULL,last_login_at=NOW() WHERE id=?',
        )->execute([$record['id']]);
        redirect(
            $record['role'] === 'admin'
                ? '/admin'
                : (empty($record['onboarding_completed_at'])
                    ? '/onboarding'
                    : '/dashboard'),
        );
    }
    if ($mode === 'forgot') {
        if ($pdo && filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL)) {
            $email = strtolower(trim($_POST['email']));
            $q = $pdo->prepare('SELECT id,name FROM users WHERE email=? AND role="student"');
            $q->execute([$email]);
            if ($r = $q->fetch()) {
                $token = bin2hex(random_bytes(32));
                $pdo->prepare(
                    'INSERT INTO password_resets(user_id,token,expires_at) VALUES(?,?,DATE_ADD(NOW(),INTERVAL 1 HOUR))',
                )->execute([$r['id'], hash('sha256', $token)]);
                send_email($email, 'Reset your EnoughEdu password', 'password-reset', [
                    'resetUrl' => url('/reset-password?token=' . $token),
                ]);
            }
        }
        flash('success', 'If that email is registered, a reset link is on its way.');
        redirect('/forgot-password');
    }
    if ($mode === 'reset') {
        if (
            ($_POST['password'] ?? '') !== ($_POST['password_confirmation'] ?? '') ||
            strlen($_POST['password'] ?? '') < 8
        ) {
            flash('error', 'Passwords must match and contain at least 8 characters.');
            redirect('/reset-password?token=' . urlencode($_POST['token'] ?? ''));
        }
        $token = hash('sha256', $_POST['token'] ?? '');
        if ($pdo) {
            $q = $pdo->prepare(
                'SELECT pr.* FROM password_resets pr JOIN users u ON u.id=pr.user_id AND u.role="student" WHERE pr.token=? AND pr.used_at IS NULL AND pr.expires_at>NOW() ORDER BY pr.id DESC LIMIT 1',
            );
            $q->execute([$token]);
            if ($r = $q->fetch()) {
                $pdo->prepare('UPDATE users SET password=? WHERE id=?')->execute([
                    password_hash($_POST['password'], PASSWORD_DEFAULT),
                    $r['user_id'],
                ]);
                $pdo->prepare('UPDATE password_resets SET used_at=NOW() WHERE id=?')->execute([
                    $r['id'],
                ]);
                flash('success', 'Password updated. You can now log in.');
                redirect('/login');
            }
        }
        flash('error', 'This reset link is invalid or expired.');
        redirect('/forgot-password');
    }
}
function checkout_product(
    PDO $pdo,
    string $planSlug,
    string $toolSlug,
    string $materialKind = '',
    int $materialId = 0,
): ?array {
    $planSlug = slug($planSlug);
    $toolSlug = slug($toolSlug);
    if ($planSlug === '' && $toolSlug !== '') {
        $freeTool = te_tool($toolSlug);
        if ($freeTool && te_free($freeTool)) {
            return null;
        }
    }
    if ($planSlug !== '') {
        $q = $pdo->prepare(
            'SELECT id,name,slug,price,plan_type FROM plans WHERE slug=? AND status="active" LIMIT 1',
        );
        $q->execute([$planSlug]);
        if ($plan = $q->fetch()) {
            if ($plan['plan_type'] === 'tool') {
                return null;
            }
            return [
                'type' => 'plan',
                'id' => (int) $plan['id'],
                'name' => $plan['name'],
                'slug' => $plan['slug'],
                'amount' => (float) $plan['price'],
            ];
        }
    }
    if ($toolSlug !== '') {
        $q = $pdo->prepare(
            'SELECT id,name,slug,price FROM tools WHERE slug=? AND status="active" LIMIT 1',
        );
        $q->execute([$toolSlug]);
        if ($tool = $q->fetch()) {
            return [
                'type' => 'tool',
                'id' => (int) $tool['id'],
                'name' => $tool['name'],
                'slug' => $tool['slug'],
                'amount' => (float) $tool['price'],
            ];
        }
    }
    if (in_array($materialKind, ['note', 'resource'], true) && $materialId > 0) {
        $table = $materialKind === 'note' ? 'notes' : 'resources';
        $q = $pdo->prepare(
            "SELECT id,title,price FROM {$table} WHERE id=? AND status='published' AND is_premium=1 AND price>=1 LIMIT 1",
        );
        $q->execute([$materialId]);
        if ($material = $q->fetch()) {
            return [
                'type' => 'material',
                'kind' => $materialKind,
                'id' => (int) $material['id'],
                'name' => $material['title'],
                'slug' => '',
                'amount' => (float) $material['price'],
            ];
        }
    }
    return null;
}
function coupon_quote(PDO $pdo, float $subtotal, string $couponCode): array
{
    $code = strtoupper(trim($couponCode));
    $subtotal = round($subtotal, 2);
    $base = [
        'valid' => true,
        'code' => '',
        'coupon_id' => null,
        'subtotal' => $subtotal,
        'discount' => 0.0,
        'total' => $subtotal,
        'message' => 'No coupon applied.',
    ];
    if ($code === '') {
        return $base;
    }
    if (!preg_match('/^[A-Z0-9_-]{3,50}$/', $code)) {
        return array_merge($base, ['valid' => false, 'message' => 'Enter a valid coupon code.']);
    }
    $q = $pdo->prepare(
        'SELECT id,code,discount_type,discount_value,min_order_value,max_uses,used_count FROM coupons WHERE code=? AND status="active" AND (starts_at IS NULL OR starts_at<=NOW()) AND (expires_at IS NULL OR expires_at>NOW()) LIMIT 1',
    );
    $q->execute([$code]);
    $coupon = $q->fetch();
    if (!$coupon) {
        return array_merge($base, [
            'valid' => false,
            'message' => 'This coupon is invalid or expired.',
        ]);
    }
    if ($coupon['max_uses'] !== null && (int) $coupon['used_count'] >= (int) $coupon['max_uses']) {
        return array_merge($base, [
            'valid' => false,
            'message' => 'This coupon has reached its usage limit.',
        ]);
    }
    if ($subtotal < (float) $coupon['min_order_value']) {
        return array_merge($base, [
            'valid' => false,
            'message' =>
                'This coupon requires a minimum order of ₹' .
                number_format((float) $coupon['min_order_value'], 2) .
                '.',
        ]);
    }
    $discount =
        $coupon['discount_type'] === 'percent'
            ? ($subtotal * min(100, (float) $coupon['discount_value'])) / 100
            : min($subtotal, (float) $coupon['discount_value']);
    $discount = round($discount, 2);
    $total = max(1.0, round($subtotal - $discount, 2));
    return [
        'valid' => true,
        'code' => $coupon['code'],
        'coupon_id' => (int) $coupon['id'],
        'subtotal' => $subtotal,
        'discount' => $discount,
        'total' => $total,
        'message' => $coupon['code'] . ' applied. You save ₹' . number_format($discount, 2) . '.',
    ];
}
function coupon_validation_api(): never
{
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store');
    $u = require_auth();
    if (($u['role'] ?? 'student') === 'admin') {
        http_response_code(403);
        echo json_encode([
            'valid' => false,
            'message' => 'Administrator accounts cannot make purchases.',
        ]);
        exit();
    }
    if (!is_post()) {
        http_response_code(405);
        echo json_encode(['valid' => false, 'message' => 'Method not allowed.']);
        exit();
    }
    verify_csrf();
    $pdo = db();
    if (!$pdo) {
        http_response_code(503);
        echo json_encode(['valid' => false, 'message' => 'The database is unavailable.']);
        exit();
    }
    $product = checkout_product(
        $pdo,
        (string) ($_POST['plan_slug'] ?? ''),
        (string) ($_POST['tool_slug'] ?? ''),
        (string) ($_POST['material_kind'] ?? ''),
        (int) ($_POST['material_id'] ?? 0),
    );
    if (!$product) {
        http_response_code(404);
        echo json_encode(['valid' => false, 'message' => 'That purchase option is unavailable.']);
        exit();
    }
    $quote = coupon_quote($pdo, (float) $product['amount'], (string) ($_POST['coupon'] ?? ''));
    if (!$quote['valid']) {
        http_response_code(422);
    }
    echo json_encode(
        $quote + [
            'product' => $product['name'],
            'subtotal_formatted' => '₹' . number_format((float) $quote['subtotal'], 2),
            'discount_formatted' => '−₹' . number_format((float) $quote['discount'], 2),
            'total_formatted' => '₹' . number_format((float) $quote['total'], 2),
        ],
        JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES,
    );
    exit();
}
function create_payment(): void
{
    $u = require_auth();
    if (($u['role'] ?? 'student') === 'admin') {
        redirect('/admin');
    }
    verify_csrf();
    $pdo = db();
    $toolId = null;
    $planId = null;
    $materialKind = in_array($_POST['material_kind'] ?? '', ['note', 'resource'], true)
        ? (string) $_POST['material_kind']
        : '';
    $materialId = (int) ($_POST['material_id'] ?? 0);
    if (!$pdo) {
        http_response_code(503);
        exit('The database is unavailable.');
    }
    $planSlug = slug((string) ($_POST['plan_slug'] ?? ''));
    $toolSlug = slug((string) ($_POST['tool_slug'] ?? ''));
    $selected = checkout_product($pdo, $planSlug, $toolSlug, $materialKind, $materialId);
    if (!$selected) {
        flash('error', 'That purchase option is unavailable.');
        redirect('/pricing');
    }
    $product = (string) $selected['name'];
    $amount = (float) $selected['amount'];
    if ($selected['type'] === 'tool') {
        $toolId = (int) $selected['id'];
    } elseif ($selected['type'] === 'plan') {
        $planId = (int) $selected['id'];
    } else {
        $materialKind = (string) $selected['kind'];
        $materialId = (int) $selected['id'];
        $owned = $pdo->prepare(
            'SELECT COUNT(*) FROM user_materials WHERE user_id=? AND material_type=? AND material_id=?',
        );
        $owned->execute([$u['id'], $materialKind, $materialId]);
        if ($owned->fetchColumn()) {
            flash('success', 'You already own this material.');
            redirect('/material/open?kind=' . rawurlencode($materialKind) . '&id=' . $materialId);
        }
    }
    $returnQuery = $toolId
        ? 'tool=' . rawurlencode((string) $selected['slug'])
        : ($planId
            ? 'plan=' . rawurlencode((string) $selected['slug'])
            : 'material_kind=' . rawurlencode($materialKind) . '&material_id=' . $materialId);
    if (!razorpay_ready()) {
        flash('error', 'Razorpay is ready, but its API credentials have not been added yet.');
        redirect('/checkout?' . $returnQuery);
    }
    $subtotal = $amount;
    $quote = coupon_quote($pdo, $subtotal, (string) ($_POST['coupon'] ?? ''));
    if (!$quote['valid']) {
        flash('error', (string) $quote['message']);
        redirect('/checkout?' . $returnQuery);
    }
    $couponId = $quote['coupon_id'];
    $discount = (float) $quote['discount'];
    $amount = (float) $quote['total'];
    $amount = number_format($amount, 2, '.', '');
    $subtotalFormatted = number_format($subtotal, 2, '.', '');
    $discountFormatted = number_format($discount, 2, '.', '');
    $amountPaise = (int) round((float) $amount * 100);
    $phone = preg_replace('/\D/', '', (string) ($_POST['phone'] ?? ''));
    if (!preg_match('/^[6-9]\d{9}$/', $phone)) {
        flash('error', 'Enter a valid 10-digit Indian mobile number.');
        redirect('/checkout?' . $returnQuery);
    }
    $name = text_limit(trim((string) ($_POST['name'] ?? $u['name'])), 120);
    if ($name === '') {
        $name = $u['name'];
    }
    $orderNo = 'EDU' . date('ymdHis') . random_int(1000, 9999);
    $productType = $toolId ? 'tool' : ($planId ? 'plan' : 'material_' . $materialKind);
    $productId = $toolId ?: ($planId ?: $materialId);
    $insert = $pdo->prepare(
        'INSERT INTO orders(user_id,order_number,product_type,product_id,product_name,subtotal,discount_amount,amount,coupon_id,status,payment_gateway) VALUES(?,?,?,?,?,?,?,?,?,"pending","razorpay")',
    );
    $insert->execute([
        $u['id'],
        $orderNo,
        $productType,
        $productId,
        $product,
        $subtotalFormatted,
        $discountFormatted,
        $amount,
        $couponId,
    ]);
    $localOrderId = (int) $pdo->lastInsertId();
    [$apiStatus, $remoteOrder, $apiError] = razorpay_api('POST', 'orders', [
        'amount' => $amountPaise,
        'currency' => (string) config('razorpay.currency', 'INR'),
        'receipt' => $orderNo,
        'notes' => [
            'enoughedu_order' => $orderNo,
            'user_id' => (string) $u['id'],
            'product_type' => $productType,
        ],
    ]);
    $remoteId = is_array($remoteOrder) ? (string) ($remoteOrder['id'] ?? '') : '';
    $remoteValid =
        $apiStatus >= 200 &&
        $apiStatus < 300 &&
        str_starts_with($remoteId, 'order_') &&
        (int) ($remoteOrder['amount'] ?? 0) === $amountPaise &&
        strtoupper((string) ($remoteOrder['currency'] ?? '')) === 'INR' &&
        hash_equals($orderNo, (string) ($remoteOrder['receipt'] ?? ''));
    if (!$remoteValid) {
        $pdo->prepare('UPDATE orders SET status="failed",updated_at=NOW() WHERE id=?')->execute([
            $localOrderId,
        ]);
        if (config('app.debug')) {
            error_log('Razorpay order creation failed: ' . $apiError);
        }
        flash('error', 'Razorpay could not start checkout. Please try again.');
        redirect('/checkout?' . $returnQuery);
    }
    $pdo->prepare('UPDATE orders SET gateway_reference=?,updated_at=NOW() WHERE id=?')->execute([
        $remoteId,
        $localOrderId,
    ]);
    $cancelToken = hash_hmac('sha256', $orderNo . '|' . $u['id'], csrf_token());
    $cancelUrl =
        '/payment/razorpay/cancel?' .
        http_build_query(
            ['order' => $orderNo, 'token' => $cancelToken],
            '',
            '&',
            PHP_QUERY_RFC3986,
        );
    $checkout = [
        'key' => (string) config('razorpay.key_id'),
        'amount' => $amountPaise,
        'currency' => 'INR',
        'name' => 'EnoughEdu',
        'description' => $product,
        'image' => url('/assets/img/favicon.png'),
        'order_id' => $remoteId,
        'callback_url' => url('/payment/razorpay/callback'),
        'redirect' => true,
        'prefill' => ['name' => $name, 'email' => $u['email'], 'contact' => $phone],
        'notes' => ['enoughedu_order' => $orderNo],
        'theme' => ['color' => '#2563eb'],
        'retry' => ['enabled' => true, 'max_count' => 2],
    ];
    ?><!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<link rel="icon" href="/assets/img/favicon.svg">
<link rel="stylesheet" href="<?= e(
    asset_url('/assets/css/app.css'),
) ?>">
<title>Secure Razorpay checkout · EnoughEdu</title>
</head>
<body>
<main style="min-height:100vh;display:grid;place-items:center;padding:24px;text-align:center">
<section class="panel" style="width:min(520px,100%);padding:42px">
<div class="card-icon" style="margin:auto">₹</div>
<span class="eyebrow" style="margin-top:20px">Secure Razorpay checkout</span>
<h1 style="margin:20px 0 10px">Complete your payment</h1>
<p class="muted"><?= e(
    $product,
) ?> · ₹<?= e(
     $amount,
 ) ?></p>
<div id="razorpay-message" class="alert" style="display:none;margin:20px 0">
</div>
<button class="btn btn-primary btn-block" id="razorpay-open" type="button">Open Razorpay checkout</button>
<a class="btn btn-secondary btn-block" style="margin-top:10px" href="<?= e(
    $cancelUrl,
) ?>">Cancel and return</a>
<p class="muted" style="font-size:11px;margin-top:18px">EnoughEdu never receives or stores your card, UPI PIN, CVV or OTP.</p>
</section>
</main>
<script src="<?= e(
    (string) config('razorpay.checkout_script'),
) ?>">
</script>
<script>
 const options=<?= json_encode(
     $checkout,
     JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT,
 ) ?>;
 const button=document.getElementById('razorpay-open'),message=document.getElementById('razorpay-message');let checkout=null;
 const openCheckout=()=>{if(typeof Razorpay==='undefined'){message.style.display='block';message.className='alert alert-error';message.textContent='Razorpay could not load. Check your connection and try again.';return}if(!checkout){checkout=new Razorpay(options);checkout.on('payment.failed',response=>{message.style.display='block';message.className='alert alert-error';message.textContent=response.error&&response.error.description?response.error.description:'Payment was not completed. You can try again.'})}checkout.open()};
 button.addEventListener('click',openCheckout);window.addEventListener('load',openCheckout);
 </script>
</body>
</html><?php
}
function send_purchase_receipt(PDO $pdo, array $order): void
{
    $q = $pdo->prepare('SELECT name,email FROM users WHERE id=?');
    $q->execute([$order['user_id']]);
    if ($buyer = $q->fetch()) {
        send_email($buyer['email'], 'Your EnoughEdu purchase is confirmed', 'purchase', [
            'name' => $buyer['name'],
            'product' => $order['product_name'],
            'amount' => $order['amount'],
            'orderNo' => $order['order_number'],
        ]);
    }
}
function finalize_razorpay_payment(PDO $pdo, array $payment, array $audit = []): bool
{
    $paymentId = trim((string) ($payment['id'] ?? ''));
    $gatewayOrderId = trim((string) ($payment['order_id'] ?? ''));
    $status = (string) ($payment['status'] ?? '');
    $currency = strtoupper((string) ($payment['currency'] ?? ''));
    $amountPaise = (int) ($payment['amount'] ?? 0);
    if (
        !str_starts_with($paymentId, 'pay_') ||
        !str_starts_with($gatewayOrderId, 'order_') ||
        $status !== 'captured' ||
        $currency !== 'INR' ||
        $amountPaise < 100
    ) {
        return false;
    }
    $sendReceipt = false;
    $order = null;
    try {
        $pdo->beginTransaction();
        $q = $pdo->prepare('SELECT * FROM orders WHERE gateway_reference=? FOR UPDATE');
        $q->execute([$gatewayOrderId]);
        $order = $q->fetch();
        if (!$order || $order['payment_gateway'] !== 'razorpay') {
            throw new DomainException('Unknown Razorpay order.');
        }
        if ((int) round((float) $order['amount'] * 100) !== $amountPaise) {
            throw new DomainException('Razorpay amount mismatch.');
        }
        if ($order['status'] === 'paid') {
            $pdo->commit();
            return true;
        }
        $q = $pdo->prepare('SELECT order_id FROM payments WHERE transaction_id=? FOR UPDATE');
        $q->execute([$paymentId]);
        $existingPayment = $q->fetchColumn();
        if ($existingPayment !== false && (int) $existingPayment !== (int) $order['id']) {
            throw new DomainException('Razorpay payment is already attached to another order.');
        }
        $pdo->prepare(
            'UPDATE orders SET status="paid",gateway_reference=?,updated_at=NOW() WHERE id=?',
        )->execute([$gatewayOrderId, $order['id']]);
        $response = json_encode(
            ['payment' => $payment, 'verification' => $audit],
            JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR,
        );
        $pdo->prepare(
            'INSERT INTO payments(user_id,order_id,transaction_id,gateway_reference,amount,currency,status,gateway,payment_method,response_json,paid_at) VALUES(?,?,?,?,?,"INR","success","razorpay",?,?,NOW()) ON DUPLICATE KEY UPDATE gateway_reference=VALUES(gateway_reference),status="success",payment_method=VALUES(payment_method),response_json=VALUES(response_json),paid_at=COALESCE(paid_at,NOW())',
        )->execute([
            $order['user_id'],
            $order['id'],
            $paymentId,
            $gatewayOrderId,
            $order['amount'],
            $payment['method'] ?? null,
            $response,
        ]);
        $pdo->prepare(
            'INSERT INTO notifications(user_id,title,message,type) VALUES(?,"Purchase successful",?,"purchase")',
        )->execute([
            $order['user_id'],
            $order['product_name'] . ' is now available in your account.',
        ]);
        if ($order['product_type'] === 'plan') {
            $q = $pdo->prepare('SELECT id,slug,plan_type,billing_period FROM plans WHERE id=?');
            $q->execute([(int) $order['product_id']]);
            $plan = $q->fetch();
            if (!$plan) {
                throw new DomainException('Purchased plan no longer exists.');
            }
            $scope =
                [
                    'full' => 'degree',
                    'branch' => 'branch',
                    'semester' => 'semester',
                    'category' => 'category',
                ][$plan['plan_type']] ?? 'degree';
            $scopeId = match ($scope) {
                'branch', 'semester' => 'current',
                'category' => (string) $plan['slug'],
                default => 'all',
            };
            $expiry = match ($plan['billing_period']) {
                'monthly' => 'DATE_ADD(NOW(),INTERVAL 1 MONTH)',
                'semester' => 'DATE_ADD(NOW(),INTERVAL 6 MONTH)',
                'yearly' => 'DATE_ADD(NOW(),INTERVAL 1 YEAR)',
                default => 'NULL',
            };
            $pdo->prepare(
                'INSERT INTO subscriptions(user_id,plan_id,order_id,scope_type,scope_id,starts_at,expires_at,status) VALUES(?,?,?,' .
                    $pdo->quote($scope) .
                    ',' .
                    $pdo->quote($scopeId) .
                    ',NOW(),' .
                    $expiry .
                    ',"active")',
            )->execute([$order['user_id'], $plan['id'], $order['id']]);
        } elseif ($order['product_type'] === 'tool') {
            $toolId = (int) ($order['product_id'] ?? 0);
            if ($toolId) {
                $pdo->prepare(
                    'INSERT IGNORE INTO user_tools(user_id,tool_id,order_id) VALUES(?,?,?)',
                )->execute([$order['user_id'], $toolId, $order['id']]);
            }
        } elseif (in_array($order['product_type'], ['material_note', 'material_resource'], true)) {
            $materialKind = substr((string) $order['product_type'], 9);
            $materialId = (int) ($order['product_id'] ?? 0);
            if (!$materialId) {
                throw new DomainException('Purchased material is invalid.');
            }
            $table = $materialKind === 'note' ? 'notes' : 'resources';
            $check = $pdo->prepare(
                "SELECT COUNT(*) FROM {$table} WHERE id=? AND status='published'",
            );
            $check->execute([$materialId]);
            if (!$check->fetchColumn()) {
                throw new DomainException('Purchased material no longer exists.');
            }
            $pdo->prepare(
                'INSERT IGNORE INTO user_materials(user_id,material_type,material_id,order_id) VALUES(?,?,?,?)',
            )->execute([$order['user_id'], $materialKind, $materialId, $order['id']]);
        } else {
            throw new DomainException('Unsupported product type.');
        }
        if (!empty($order['coupon_id'])) {
            $pdo->prepare('UPDATE coupons SET used_count=used_count+1 WHERE id=?')->execute([
                $order['coupon_id'],
            ]);
        }
        $pdo->commit();
        $sendReceipt = true;
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        if (config('app.debug')) {
            error_log('Razorpay fulfilment failed: ' . $e->getMessage());
        }
        return false;
    }
    if ($sendReceipt && $order) {
        send_purchase_receipt($pdo, $order);
    }
    return true;
}
function razorpay_payment_callback(): void
{
    $pdo = db();
    $gatewayOrderId = trim((string) ($_POST['razorpay_order_id'] ?? ''));
    $paymentId = trim((string) ($_POST['razorpay_payment_id'] ?? ''));
    $signature = trim((string) ($_POST['razorpay_signature'] ?? ''));
    $success = false;
    $pending = false;
    $successUrl = '/dashboard/tools';
    if ($pdo && str_starts_with($gatewayOrderId, 'order_')) {
        $q = $pdo->prepare(
            'SELECT id,status,gateway_reference,product_type,product_id FROM orders WHERE gateway_reference=? LIMIT 1',
        );
        $q->execute([$gatewayOrderId]);
        $order = $q->fetch();
        if (
            $order &&
            in_array($order['product_type'] ?? '', ['material_note', 'material_resource'], true)
        ) {
            $successUrl =
                '/material/open?kind=' .
                rawurlencode(substr((string) $order['product_type'], 9)) .
                '&id=' .
                (int) $order['product_id'];
        }
        if (
            $order &&
            razorpay_verify_payment_signature(
                (string) $order['gateway_reference'],
                $paymentId,
                $signature,
            )
        ) {
            if ($order['status'] === 'paid') {
                $success = true;
            } else {
                [$apiStatus, $payment, $apiError] = razorpay_api(
                    'GET',
                    'payments/' . rawurlencode($paymentId),
                );
                if ($apiStatus >= 200 && $apiStatus < 300 && is_array($payment)) {
                    $success = finalize_razorpay_payment($pdo, $payment, [
                        'source' => 'checkout_callback',
                        'signature_verified' => true,
                    ]);
                    $pending =
                        !$success &&
                        in_array($payment['status'] ?? '', ['authorized', 'created'], true);
                } else {
                    $pending = true;
                    if (config('app.debug')) {
                        error_log('Razorpay payment fetch failed: ' . $apiError);
                    }
                }
            }
        }
    }
    if ($success) {
        flash('success', 'Payment successful. Your purchase is ready.');
        redirect($successUrl);
    }
    flash(
        $pending ? 'success' : 'error',
        $pending
            ? 'Your payment is awaiting Razorpay capture. Access will activate automatically after confirmation.'
            : 'Payment could not be verified. No access was activated.',
    );
    redirect($pending ? '/dashboard/purchases' : '/pricing');
}
function razorpay_payment_cancel(): void
{
    $u = require_auth();
    $orderNo = trim((string) ($_GET['order'] ?? ''));
    $token = trim((string) ($_GET['token'] ?? ''));
    $expected = hash_hmac('sha256', $orderNo . '|' . $u['id'], csrf_token());
    if ($orderNo === '' || !hash_equals($expected, $token)) {
        http_response_code(403);
        exit('Invalid cancellation request.');
    }
    $pdo = db();
    if ($pdo) {
        $q = $pdo->prepare(
            'UPDATE orders SET status="cancelled",updated_at=NOW() WHERE order_number=? AND user_id=? AND status="pending"',
        );
        $q->execute([$orderNo, $u['id']]);
    }
    flash('error', 'Payment was cancelled. No access was activated.');
    redirect('/pricing');
}
function razorpay_webhook(): never
{
    header('Content-Type: application/json; charset=utf-8');
    $raw = (string) file_get_contents('php://input');
    $signature = trim((string) ($_SERVER['HTTP_X_RAZORPAY_SIGNATURE'] ?? ''));
    if (!razorpay_verify_webhook_signature($raw, $signature)) {
        http_response_code(401);
        echo json_encode(['error' => 'Invalid webhook signature']);
        exit();
    }
    $event = json_decode($raw, true);
    if (!is_array($event)) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid webhook payload']);
        exit();
    }
    $name = (string) ($event['event'] ?? '');
    $payment = (array) ($event['payload']['payment']['entity'] ?? []);
    $pdo = db();
    if (!$pdo) {
        http_response_code(503);
        echo json_encode(['error' => 'Database unavailable']);
        exit();
    }
    if ($name === 'payment.captured') {
        if (!finalize_razorpay_payment($pdo, $payment, ['source' => 'webhook', 'event' => $name])) {
            http_response_code(500);
            echo json_encode(['error' => 'Payment could not be applied']);
            exit();
        }
    } elseif ($name === 'payment.failed') {
        $paymentId = trim((string) ($payment['id'] ?? ''));
        $gatewayOrderId = trim((string) ($payment['order_id'] ?? ''));
        if (str_starts_with($paymentId, 'pay_') && str_starts_with($gatewayOrderId, 'order_')) {
            try {
                $pdo->beginTransaction();
                $q = $pdo->prepare('SELECT * FROM orders WHERE gateway_reference=? FOR UPDATE');
                $q->execute([$gatewayOrderId]);
                if ($order = $q->fetch()) {
                    if ($order['status'] !== 'paid') {
                        $pdo->prepare(
                            'UPDATE orders SET status="failed",updated_at=NOW() WHERE id=?',
                        )->execute([$order['id']]);
                    }
                    $response = json_encode(
                        [
                            'payment' => $payment,
                            'verification' => ['source' => 'webhook', 'event' => $name],
                        ],
                        JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE,
                    );
                    $pdo->prepare(
                        'INSERT INTO payments(user_id,order_id,transaction_id,gateway_reference,amount,currency,status,gateway,payment_method,response_json) VALUES(?,?,?,?,?,"INR","failed","razorpay",?,?) ON DUPLICATE KEY UPDATE status=IF(status="success","success","failed"),response_json=VALUES(response_json)',
                    )->execute([
                        $order['user_id'],
                        $order['id'],
                        $paymentId,
                        $gatewayOrderId,
                        $order['amount'],
                        $payment['method'] ?? null,
                        $response,
                    ]);
                }
                $pdo->commit();
            } catch (Throwable $e) {
                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }
                http_response_code(500);
                echo json_encode(['error' => 'Failure event could not be stored']);
                exit();
            }
        }
    }
    echo json_encode(['ok' => true]);
    exit();
}
function masked_email(string $email): string
{
    $parts = explode('@', $email, 2);
    if (count($parts) !== 2) {
        return 'configured security email';
    }
    $name = $parts[0];
    $visible = substr($name, 0, min(2, strlen($name)));
    return $visible .
        str_repeat('•', max(3, min(8, strlen($name) - strlen($visible)))) .
        '@' .
        $parts[1];
}
function admin_password_otp_is_valid(array $challenge, int $userId, string $otp): bool
{
    return (int) ($challenge['user_id'] ?? 0) === $userId &&
        time() <= (int) ($challenge['expires_at'] ?? 0) &&
        preg_match('/^\d{6}$/', $otp) === 1 &&
        password_verify($otp, (string) ($challenge['otp_hash'] ?? ''));
}
function request_admin_password_otp(): never
{
    $u = require_admin();
    verify_csrf();
    $pdo = db();
    if (!$pdo) {
        flash('error', 'The database is unavailable.');
        redirect('/dashboard/profile');
    }
    $existing = $_SESSION['admin_password_otp'] ?? null;
    $cooldown = (int) config('admin_security.otp_resend_cooldown', 60);
    if (is_array($existing) && time() - (int) ($existing['sent_at'] ?? 0) < $cooldown) {
        flash('error', 'Please wait one minute before requesting another code.');
        redirect('/dashboard/profile');
    }
    $current = (string) ($_POST['current_password'] ?? '');
    $password = (string) ($_POST['password'] ?? '');
    $confirmation = (string) ($_POST['password_confirmation'] ?? '');
    $q = $pdo->prepare('SELECT password FROM users WHERE id=? AND role="admin" LIMIT 1');
    $q->execute([$u['id']]);
    $hash = $q->fetchColumn();
    if (
        !$hash ||
        !password_verify($current, (string) $hash) ||
        strlen($password) < 8 ||
        !hash_equals($password, $confirmation)
    ) {
        flash(
            'error',
            'Check the current password. The new passwords must match and contain at least 8 characters.',
        );
        redirect('/dashboard/profile');
    }
    $destination = trim((string) config('admin_security.otp_email'));
    if (!filter_var($destination, FILTER_VALIDATE_EMAIL)) {
        flash('error', 'The administrator security email is not configured correctly.');
        redirect('/dashboard/profile');
    }
    $otp = (string) random_int(100000, 999999);
    $ttl = max(300, min(900, (int) config('admin_security.otp_ttl_seconds', 600)));
    $challenge = [
        'user_id' => (int) $u['id'],
        'otp_hash' => password_hash($otp, PASSWORD_DEFAULT),
        'password_hash' => password_hash($password, PASSWORD_DEFAULT),
        'expires_at' => time() + $ttl,
        'attempts' => 0,
        'sent_at' => time(),
    ];
    if (
        !send_email(
            $destination,
            'EnoughEdu admin password verification code',
            'admin-password-otp',
            ['otp' => $otp, 'minutes' => (string) ceil($ttl / 60)],
        )
    ) {
        unset($_SESSION['admin_password_otp']);
        flash(
            'error',
            'The code could not be sent. Check cPanel email delivery and PHP mail before trying again.',
        );
        redirect('/dashboard/profile');
    }
    $_SESSION['admin_password_otp'] = $challenge;
    flash(
        'success',
        'A verification code was sent to ' .
            masked_email($destination) .
            '. It expires in ' .
            ceil($ttl / 60) .
            ' minutes.',
    );
    redirect('/dashboard/profile');
}
function confirm_admin_password_otp(): never
{
    $u = require_admin();
    verify_csrf();
    $pdo = db();
    $challenge = $_SESSION['admin_password_otp'] ?? null;
    if (!$pdo || !is_array($challenge) || (int) ($challenge['user_id'] ?? 0) !== (int) $u['id']) {
        unset($_SESSION['admin_password_otp']);
        flash('error', 'Request a new verification code first.');
        redirect('/dashboard/profile');
    }
    if (time() > (int) ($challenge['expires_at'] ?? 0)) {
        unset($_SESSION['admin_password_otp']);
        flash('error', 'That verification code expired. Request a new one.');
        redirect('/dashboard/profile');
    }
    $max = max(3, min(10, (int) config('admin_security.otp_max_attempts', 5)));
    $otp = preg_replace('/\D/', '', (string) ($_POST['otp'] ?? ''));
    if (!admin_password_otp_is_valid($challenge, (int) $u['id'], $otp)) {
        $challenge['attempts'] = (int) ($challenge['attempts'] ?? 0) + 1;
        if ($challenge['attempts'] >= $max) {
            unset($_SESSION['admin_password_otp']);
            flash('error', 'Too many incorrect attempts. Request a new code.');
        } else {
            $_SESSION['admin_password_otp'] = $challenge;
            flash(
                'error',
                'The verification code is incorrect. ' .
                    ($max - $challenge['attempts']) .
                    ' attempts remain.',
            );
        }
        redirect('/dashboard/profile');
    }
    $q = $pdo->prepare('UPDATE users SET password=? WHERE id=? AND role="admin"');
    $q->execute([(string) $challenge['password_hash'], $u['id']]);
    unset($_SESSION['admin_password_otp']);
    session_regenerate_id(true);
    flash('success', 'Administrator password changed successfully.');
    redirect('/dashboard/profile');
}
function handle_account(string $action): void
{
    $u = require_auth();
    verify_csrf();
    $pdo = db();
    if (!$pdo) {
        flash('error', 'Connect the database to save account changes.');
        redirect('/dashboard/profile');
    }
    if ($action === 'profile') {
        $name = trim($_POST['name'] ?? '');
        $semester = max(1, min(8, (int) ($_POST['semester'] ?? 1)));
        $avatar = $u['avatar'] ?? null;
        if (
            !empty($_FILES['avatar']['tmp_name']) &&
            is_uploaded_file($_FILES['avatar']['tmp_name'])
        ) {
            $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
            $mime = (new finfo(FILEINFO_MIME_TYPE))->file($_FILES['avatar']['tmp_name']);
            if (isset($allowed[$mime]) && $_FILES['avatar']['size'] <= 2_000_000) {
                $filename =
                    'avatar-' . $u['id'] . '-' . bin2hex(random_bytes(6)) . '.' . $allowed[$mime];
                $target = public_path('uploads/avatars/' . $filename);
                if (move_uploaded_file($_FILES['avatar']['tmp_name'], $target)) {
                    $avatar = '/uploads/avatars/' . $filename;
                }
            } else {
                flash('error', 'Use a JPG, PNG or WebP image under 2 MB.');
                redirect('/dashboard/profile');
            }
        }
        if (($u['role'] ?? 'student') === 'admin') {
            $pdo->prepare('UPDATE users SET name=?,semester=?,avatar=? WHERE id=?')->execute([
                $name,
                $semester,
                $avatar,
                $u['id'],
            ]);
        } else {
            $institutionId = trim((string) ($_POST['university_id'] ?? ''));
            $choice = $_SESSION['institution_choices'][$institutionId] ?? null;
            $university = trim((string) ($_POST['university'] ?? ''));
            $branchId = (int) ($_POST['branch_id'] ?? 0);
            $graduationYear = (int) ($_POST['graduation_year'] ?? 0);
            $goal = (string) ($_POST['study_goal'] ?? '');
            $challenge = (string) ($_POST['biggest_challenge'] ?? '');
            $learningStyle = (string) ($_POST['learning_style'] ?? '');
            $examTimeline = (string) ($_POST['exam_timeline'] ?? '');
            $hours = (int) ($_POST['weekly_study_hours'] ?? 0);
            $studyTime = (string) ($_POST['preferred_study_time'] ?? '');
            $target = (float) ($_POST['target_cgpa'] ?? 0);
            $q = $pdo->prepare('SELECT COUNT(*) FROM branches WHERE id=? AND status="active"');
            $q->execute([$branchId]);
            if (
                $name === '' ||
                !is_array($choice) ||
                !hash_equals((string) $choice['name'], $university) ||
                !$q->fetchColumn() ||
                $graduationYear < (int) date('Y') ||
                $graduationYear > (int) date('Y') + 8 ||
                !in_array(
                    $goal,
                    ['Improve CGPA', 'Clear backlogs', 'Exam preparation', 'Build career skills'],
                    true,
                ) ||
                !in_array(
                    $challenge,
                    [
                        'Staying consistent',
                        'Understanding concepts',
                        'Managing deadlines',
                        'Finding quality resources',
                    ],
                    true,
                ) ||
                !in_array(
                    $learningStyle,
                    ['Short notes', 'Video lessons', 'Practice questions', 'Mixed approach'],
                    true,
                ) ||
                !in_array(
                    $examTimeline,
                    ['Within 2 weeks', 'Within 1 month', 'Within 3 months', 'No date yet'],
                    true,
                ) ||
                !in_array(
                    $studyTime,
                    ['Early morning', 'Daytime', 'Evening', 'Late night'],
                    true,
                ) ||
                $hours < 1 ||
                $hours > 40 ||
                $target < 4 ||
                $target > 10
            ) {
                flash(
                    'error',
                    'Check your academic profile details and select your institution from the suggestions.',
                );
                redirect('/dashboard/profile');
            }
            $pdo->prepare(
                'UPDATE users SET name=?,semester=?,avatar=?,university=?,university_id=?,university_city=?,university_region=?,branch_id=?,graduation_year=?,study_goal=?,biggest_challenge=?,learning_style=?,exam_timeline=?,weekly_study_hours=?,preferred_study_time=?,target_cgpa=? WHERE id=?',
            )->execute([
                $name,
                $semester,
                $avatar,
                $choice['name'],
                $choice['id'],
                $choice['city'],
                $choice['region'],
                $branchId,
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
        }
        flash('success', 'Profile updated.');
        redirect('/dashboard/profile');
    }
    if ($action === 'password') {
        if (($u['role'] ?? 'student') === 'admin') {
            flash('error', 'Administrator password changes require the emailed verification code.');
            redirect('/dashboard/profile');
        }
        $q = $pdo->prepare('SELECT password FROM users WHERE id=?');
        $q->execute([$u['id']]);
        $hash = $q->fetchColumn();
        if (
            !$hash ||
            !password_verify($_POST['current_password'] ?? '', $hash) ||
            strlen($_POST['password'] ?? '') < 8
        ) {
            flash('error', 'Check your current password and use at least 8 characters.');
            redirect('/dashboard/profile');
        }
        $pdo->prepare('UPDATE users SET password=? WHERE id=?')->execute([
            password_hash($_POST['password'], PASSWORD_DEFAULT),
            $u['id'],
        ]);
        flash('success', 'Password changed successfully.');
        redirect('/dashboard/profile');
    }
}
function handle_contact(): void
{
    verify_csrf();
    $name = trim($_POST['name'] ?? '');
    $email = strtolower(trim($_POST['email'] ?? ''));
    $message = trim($_POST['message'] ?? '');
    if (!$name || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($message) < 10) {
        flash('error', 'Please complete every field.');
        redirect('/contact');
    }
    $pdo = db();
    if ($pdo) {
        $pdo->prepare('INSERT INTO contact_messages(name,email,message) VALUES(?,?,?)')->execute([
            $name,
            $email,
            $message,
        ]);
    }
    flash('success', 'Thanks—your message has been received.');
    redirect('/contact');
}
function route_dispatch(string $path): void
{
    if ($path === '/api/tool-access') {
        te_access_api();
    }
    if (function_exists('rl_route') && rl_route($path)) {
        return;
    }
    if ($path === '/download-app' || $path === '/download-app/') {
        app_download_page();
        return;
    }
    if (
        str_starts_with($path, '/admin') ||
        str_starts_with($path, '/dashboard') ||
        str_starts_with($path, '/auth/google') ||
        str_starts_with($path, '/payment/') ||
        str_ends_with($path, '/pdf') ||
        in_array(
            $path,
            [
                '/login',
                '/signup',
                '/forgot-password',
                '/reset-password',
                '/checkout',
                '/onboarding',
            ],
            true,
        )
    ) {
        header('X-Robots-Tag: noindex, nofollow');
    }
    if ($path === '/auth/google') {
        start_google_oauth();
    }
    if ($path === '/auth/google/callback') {
        finish_google_oauth();
    }
    if ($path === '/verify-email') {
        flash(
            'success',
            'Email verification is no longer required. Log in directly with your email and password.',
        );
        redirect('/login');
    }
    if (in_array($path, ['/login', '/signup', '/forgot-password', '/reset-password'], true)) {
        if (is_post()) {
            handle_auth(
                match ($path) {
                    '/login' => 'login',
                    '/signup' => 'signup',
                    '/forgot-password' => 'forgot',
                    default => 'reset',
                },
            );
        }
        auth_page(ltrim($path, '/'));
        return;
    }
    if ($path === '/logout') {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $p['path'],
                $p['domain'],
                $p['secure'],
                $p['httponly'],
            );
        }
        session_destroy();
        redirect('/');
    }
    if ($path === '/onboarding') {
        if (is_post()) {
            handle_student_onboarding();
        }
        student_onboarding_page();
        return;
    }
    if ($path === '/material/open') {
        open_material();
        return;
    }
    if ($path === '/branches') {
        branches_page();
        return;
    }
    if (preg_match('#^/branch/([a-z0-9-]+)$#', $path, $m)) {
        branch_materials_page($m[1]);
        return;
    }
    if ($path === '/tools') {
        tools_page();
        return;
    }
    if ($path === '/tools/resume-builder/pdf') {
        resume_pdf_download();
    }
    if ($path === '/tools/linkedin-generator/pdf') {
        career_pdf_download('linkedin');
    }
    if ($path === '/tools/cover-letter-generator/pdf') {
        career_pdf_download('cover');
    }
    if (preg_match('#^/tools/([a-z0-9-]+)$#', $path, $m)) {
        functional_tool_page($m[1]);
        return;
    }
    if ($path === '/resources') {
        dynamic_resources_page();
        return;
    }
    if ($path === '/ai-hub' || str_starts_with($path, '/ai-hub/')) {
        header('Location: /tools', true, 301);
        exit();
    }
    if ($path === '/pricing') {
        pricing_page();
        return;
    }
    if ($path === '/checkout') {
        live_checkout_page();
        return;
    }
    if ($path === '/api/institutions') {
        institution_search_api();
    }
    if ($path === '/api/career-ai') {
        career_ai_api();
    }
    if ($path === '/api/ai') {
        header('Content-Type: application/json; charset=utf-8');
        http_response_code(410);
        echo json_encode(['error' => 'AI Hub was replaced by AI-assisted career tools.']);
        exit();
    }
    if ($path === '/api/coupon/validate') {
        coupon_validation_api();
    }
    if ($path === '/payment/create' && is_post()) {
        create_payment();
        return;
    }
    if ($path === '/payment/razorpay/callback' && is_post()) {
        razorpay_payment_callback();
    }
    if ($path === '/payment/razorpay/webhook' && is_post()) {
        razorpay_webhook();
    }
    if ($path === '/payment/razorpay/cancel' && !is_post()) {
        razorpay_payment_cancel();
    }
    if ($path === '/admin/password/request-otp' && is_post()) {
        request_admin_password_otp();
    }
    if ($path === '/admin/password/confirm' && is_post()) {
        confirm_admin_password_otp();
    }
    if ($path === '/dashboard/profile' && is_post()) {
        handle_account('profile');
        return;
    }
    if ($path === '/dashboard/password' && is_post()) {
        handle_account('password');
        return;
    }
    if ($path === '/dashboard/planner/add' && is_post()) {
        handle_student_portal('planner-add');
    }
    if ($path === '/dashboard/planner/toggle' && is_post()) {
        handle_student_portal('planner-toggle');
    }
    if ($path === '/dashboard/planner/delete' && is_post()) {
        handle_student_portal('planner-delete');
    }
    if ($path === '/dashboard/degree/save' && is_post()) {
        handle_student_portal('degree-save');
    }
    if ($path === '/dashboard' || preg_match('#^/dashboard/([a-z-]+)$#', $path, $m)) {
        student_dashboard_page($m[1] ?? 'dashboard');
        return;
    }
    if ($path === '/admin/resources/save' && is_post()) {
        save_material();
        return;
    }
    if ($path === '/admin/resources/delete' && is_post()) {
        delete_material();
        return;
    }
    if ($path === '/admin/action' && is_post()) {
        handle_admin_portal();
    }
    if ($path === '/admin' || preg_match('#^/admin/([a-z-]+)$#', $path, $m)) {
        admin_console_page($m[1] ?? 'dashboard');
        return;
    }
    if (
        in_array(
            ltrim($path, '/'),
            ['about', 'contact', 'privacy', 'terms', 'refund-policy', 'faq'],
            true,
        )
    ) {
        if ($path === '/contact' && is_post()) {
            handle_contact();
            return;
        }
        $page = ltrim($path, '/');
        if (in_array($page, ['about', 'privacy', 'terms', 'refund-policy'], true)) {
            managed_content_page($page);
        } else {
            content_page($page);
        }
        return;
    }
    not_found();
}
