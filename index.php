<?php
declare(strict_types=1);
define('ENOUGHEDU_PUBLIC_ROOT', __DIR__);
// All application paths are relative to this subdomain's document root.
$privateRoot = __DIR__;
require $privateRoot . '/app/bootstrap.php';
require $privateRoot . '/app/site.php';
require __DIR__ . '/includes/resources-v2.php';
require $privateRoot . '/app/tool-suite.php';
require $privateRoot . '/app/portal.php';
$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$host = strtolower(preg_replace('/:\d+$/', '', $_SERVER['HTTP_HOST'] ?? ''));
if ($path === '/' && str_starts_with($host, 'tools.')) {
    $path = '/tools';
}
function icon(string $name): string
{
    $icons = [
        'book' => '◫',
        'chip' => '⌘',
        'calc' => '∑',
        'rocket' => '↗',
        'brain' => '✦',
        'chart' => '⌁',
        'file' => '▤',
        'clock' => '◷',
        'cap' => '◆',
        'bolt' => 'ϟ',
        'code' => '&lt;/&gt;',
    ];
    return $icons[$name] ?? '•';
}
function home(): void
{
    $u = user();
    $accountUrl = $u && ($u['role'] ?? 'student') === 'admin' ? '/admin' : '/dashboard';
    $announcement = setting(
        'announcement_text',
        'An engineering student toolkit by Vedhant Khajuria',
    );
    $headline = setting('homepage_headline', 'Tools and resources for your engineering degree');
    $subtitle = setting(
        'homepage_subtitle',
        'Tools, notes, planners, AI-assisted career utilities and academic resources in one organised platform.',
    );
    $previewProgress = max(0, min(100, (int) setting('homepage_preview_progress', '0')));
    $previewTasks = max(0, (int) setting('homepage_preview_tasks', '0'));
    $previewPurchases = max(0, (int) setting('homepage_preview_purchases', '0'));
    $branchCount = max(0, (int) setting('homepage_branch_count', '11'));
    $semesterCount = max(0, (int) setting('homepage_semester_count', '8'));
    $branches = [
        ['Computer Science Engineering', 'cse'],
        ['AI & Machine Learning', 'ai-ml'],
        ['Information Technology', 'it'],
        ['Electronics & Communication', 'ece'],
        ['Mechanical Engineering', 'mechanical'],
        ['Civil Engineering', 'civil'],
    ];
    $tools = [
        [
            'CGPA Calculator',
            'Calculate your cumulative grade point average instantly.',
            'calc',
            '/tools/cgpa-calculator',
        ],
        [
            'LinkedIn Profile Generator',
            'Create a focused headline, About section and skills list with AI.',
            'brain',
            '/tools/linkedin-generator',
        ],
        [
            'Resume Builder',
            'Build an ATS-ready PDF engineering resume with AI.',
            'file',
            '/tools/resume-builder',
        ],
        [
            'Semester Planner',
            'Plan subjects, deadlines, and exams in one calm view.',
            'clock',
            '/tools/semester-planner',
        ],
        [
            'Formula Library',
            'Search a comprehensive engineering formula reference.',
            'book',
            '/tools/formula-library',
        ],
        [
            'Unit Converter',
            'Convert a broad library of engineering quantities and units.',
            'calc',
            '/tools/unit-converter',
        ],
    ];
    ?>
<!doctype html>
<html lang="en-IN">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>EnoughEdu | Engineering Student Tools, Notes and AI Career Support</title>
<meta name="description" content="EnoughEdu offers CGPA and attendance calculators, engineering notes, study planners, AI-assisted resume tools, and private image and PDF utilities for students.">
<meta name="robots" content="index,follow,max-image-preview:large,max-snippet:-1,max-video-preview:-1">
<meta name="author" content="Vedhant Khajuria">
<meta name="application-name" content="EnoughEdu">
<meta name="theme-color" content="#2563eb">
<meta property="og:site_name" content="EnoughEdu">
<meta property="og:locale" content="en_IN">
<meta property="og:title" content="EnoughEdu | Engineering Student Tools, Notes and AI Career Support">
<meta property="og:description" content="Engineering calculators, semester resources, study planners, AI-assisted career tools, and private image and PDF utilities in one student platform.">
<meta property="og:type" content="website">
<meta property="og:url" content="<?= e(
    url('/'),
) ?>">
<meta property="og:image" content="<?= e(
    url('/assets/img/og.png'),
) ?>">
<meta property="og:image:alt" content="EnoughEdu engineering student platform">
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="EnoughEdu | Engineering Student Tools and Resources">
<meta name="twitter:description" content="Calculators, notes, planners, AI career support, and private file tools for engineering students.">
<meta name="twitter:image" content="<?= e(
    url('/assets/img/og.png'),
) ?>">
<link rel="canonical" href="<?= e(
    url('/'),
) ?>">
<link rel="sitemap" type="application/xml" href="/sitemap.xml">
<link rel="alternate" type="text/plain" title="EnoughEdu information for AI assistants" href="/llms.txt">
<link rel="icon" href="/assets/img/favicon.svg">
<link rel="stylesheet" href="<?= e(
    asset_url('/assets/css/app.css'),
) ?>">







</head>
<body>
<script type="application/ld+json"><?= json_encode(
    [
        '@context' => 'https://schema.org',
        '@graph' => [
            [
                '@type' => 'WebSite',
                '@id' => url('/#website'),
                'url' => url('/'),
                'name' => 'EnoughEdu',
                'alternateName' => ['Enough Edu', 'enoughedu.vedhant.in'],
                'description' =>
                    'Engineering student tools, study resources, planners and career support in one organised platform.',
                'publisher' => ['@id' => url('/#organization')],
            ],
            [
                '@type' => 'Organization',
                '@id' => url('/#organization'),
                'name' => 'EnoughEdu',
                'alternateName' => ['Enough Edu', 'enoughedu.vedhant.in'],
                'url' => url('/'),
                'logo' => ['@type' => 'ImageObject', 'url' => url('/assets/img/logo.png')],
                'email' => 'hello@vedhant.in',
                'description' =>
                    'EnoughEdu is an online academic toolkit for engineering and college students in India.',
            ],
        ],
    ],
    JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP,
) ?></script>
<div class="announcement"><?= e(
    $announcement,
) ?> <a href="/pricing">View membership plans →</a>
</div><?php public_nav(false); ?>
<main>
<section class="hero">
<div class="container hero-grid">
<div class="hero-copy">
<span class="eyebrow">
<i class="eyebrow-dot">
</i> Your degree, finally organised</span>
<h1><?= e(
    $headline,
) ?></h1>
<p><?= e(
    ai_public_text($subtitle),
) ?></p>
<div class="hero-actions">
<a class="btn btn-primary" href="<?= $u
    ? e($accountUrl)
    : '/signup' ?>"><?= $u
    ? 'Open my workspace'
    : 'Create account' ?> <span>→</span>
</a>
<a class="btn btn-secondary" href="#tools">Explore features</a>
</div>
<div class="trust">
<span>Every new student workspace begins empty and private.</span>
</div>
</div>
<div class="hero-visual">
<div class="app-window glass">
<div class="window-top">
<i>
</i>
<i>
</i>
<i>
</i>
</div>
<div class="mini-dashboard">
<div class="dash-header">
<div>
<small>Product preview</small>
<h3>Your private workspace</h3>
</div>
<div class="progress-ring" style="--preview-progress:<?= e(
     $previewProgress,
 ) ?>%">
<strong><?= e(
    $previewProgress,
) ?>%</strong>
</div>
</div>
<div class="dash-grid">
<div class="mini-card">
<small>Your tasks</small>
<strong><?= e(
    $previewTasks,
) ?></strong>
<div class="chart">
</div>
</div>
<div class="mini-card">
<small>Your purchases</small>
<strong><?= e(
    $previewPurchases,
) ?></strong>
<div class="chart" style="background:linear-gradient(180deg,rgba(139,92,246,.16),transparent);transform:scaleX(-1)">
</div>
</div>
</div>
</div>
</div>
<div class="float-pill glass pill-one">Private per-user data</div>
<div class="float-pill glass pill-two">Tools unlock after payment</div>
</div>
</div>
</section>
<?php
$homeDb = db();
$liveStats = [
    'students' => $homeDb
        ? admin_stat($homeDb, 'SELECT COUNT(*) FROM users WHERE role="student"')
        : 0,
    'notes' => $homeDb
        ? admin_stat($homeDb, 'SELECT COUNT(*) FROM notes WHERE status="published"')
        : 0,
    'tools' => $homeDb
        ? admin_stat($homeDb, 'SELECT COUNT(*) FROM tools WHERE status="active"')
        : count(array_merge(...array_values(TOOLS))),
    'materials' => $homeDb
        ? admin_stat(
            $homeDb,
            'SELECT (SELECT COUNT(*) FROM notes WHERE status="published")+(SELECT COUNT(*) FROM resources WHERE status="published")',
        )
        : 0,
];
$defaultLabels = [
    'students' => 'Registered students',
    'notes' => 'Published notes',
    'tools' => 'Available features',
    'materials' => 'Published materials',
];
$homeStats = [];
foreach ($liveStats as $key => $liveValue) {
    $override = trim((string) setting('homepage_stat_' . $key . '_value', ''));
    $homeStats[] = [
        'value' => $override === '' ? $liveValue : max(0, (int) $override),
        'suffix' => text_limit(trim((string) setting('homepage_stat_' . $key . '_suffix', '')), 8),
        'label' => text_limit(
            trim((string) setting('homepage_stat_' . $key . '_label', $defaultLabels[$key])) ?:
            $defaultLabels[$key],
            80,
        ),
    ];
}
?><section class="stats">
<div class="container stat-grid glass"><?php foreach (
    $homeStats
    as $stat
): ?><div class="stat">
<strong data-counter="<?= e($stat['value']) ?>" data-suffix="<?= e(
    $stat['suffix'],
) ?>">0</strong>
<span><?= e($stat['label']) ?></span>
</div><?php endforeach; ?></div>
</section>
<section class="section" id="branches">
<div class="container">
<div class="section-head reveal">
<span class="eyebrow"><?= e(
    $branchCount,
) ?> engineering branches</span>
<h2>Resources shaped around <span class="gradient-text">your curriculum</span>
</h2>
<p>Semester-wise notes, formula sheets, tools and previous papers—organised for the way you actually study.</p>
</div>
<div class="card-grid"><?php foreach (
     $branches
     as $i => $b
 ): ?><a class="card reveal" href="/branch/<?= e($b[1]) ?>">
<div class="card-icon"><?= icon(
    ['code', 'brain', 'chip', 'bolt', 'cap', 'book'][$i],
) ?></div>
<h3><?= e(
    $b[0],
) ?></h3>
<p>Notes, PYQs, lab manuals and dedicated academic tools.</p>
<div class="card-meta">
<span class="tag"><?= e(
    $semesterCount,
) ?> semesters</span>
<span class="tag">Live EnoughEdu library</span>
</div>
<span class="card-link">Explore branch →</span>
</a><?php endforeach; ?></div>
</div>
</section>
<section class="section" id="tools">
<div class="container">
<div class="section-head reveal">
<span class="eyebrow">Student toolkit</span>
<h2>See what you can unlock for your <span class="gradient-text">engineering journey</span>
</h2>
<p>Browse every capability before joining. Tools become usable only after a successful plan or individual-tool purchase.</p>
</div>
<div class="card-grid"><?php foreach (
    $tools
    as $i => $t
): ?><a class="card tool-card reveal" href="<?= e($t[3]) ?>"><?php if (
    $i < 2
): ?><span class="popular-label">POPULAR</span><?php endif; ?><div class="card-icon"><?= icon(
    $t[2],
) ?></div>
<h3><?= $t[0] ?></h3>
<p><?= $t[1] ?></p>
<span class="card-link">View feature →</span>
</a><?php endforeach; ?></div>
<p style="text-align:center;margin-top:30px">
<a class="btn btn-secondary" href="/tools">View all tools</a>
</p>
</div>
</section>
<section class="section">
<div class="container">
<div class="section-head reveal">
<span class="eyebrow">About EnoughEdu</span>
<h2>One academic toolkit for <span class="gradient-text">engineering and college students</span>
</h2>
<p>EnoughEdu is an online student platform in India that combines academic calculators, semester resources, study planners, private image and PDF tools, and AI-assisted career support.</p>
</div>
<div class="card-grid">
<a class="card reveal" href="/tools/resume-builder">
<h3>AI Resume Builder</h3>
<p>Create accurate, ATS-friendly resume text with AI assistance and download it as a PDF.</p>
<span class="card-link">Explore Resume Builder →</span>
</a>
<a class="card reveal" href="/tools/linkedin-generator">
<h3>LinkedIn Profile Generator</h3>
<p>Prepare a focused LinkedIn headline, About section and skills list from your real details.</p>
<span class="card-link">Explore LinkedIn Generator →</span>
</a>
<a class="card reveal" href="/tools/cover-letter-generator">
<h3>Cover Letter Generator</h3>
<p>Draft a tailored engineering internship or job application letter with AI assistance.</p>
<span class="card-link">Explore Cover Letter Generator →</span>
</a>
</div>
</div>
</section>
<section class="section">
<div class="container">
<div class="section-head reveal">
<span class="eyebrow">Four-year roadmap</span>
<h2>One companion for your <span class="gradient-text">whole journey</span>
</h2>
</div>
<div class="roadmap"><?php foreach (
    [
        ['1', 'Planning & Fundamentals', 'Build strong basics and better study routines.'],
        ['2', 'Assignments & Notes', 'Stay ahead with organised resources and trackers.'],
        ['3', 'Internships & Skills', 'Create your resume and prepare for real experience.'],
        ['4', 'Projects & Placements', 'Ship standout projects and ace placement season.'],
    ]
    as $y
): ?><div class="year reveal">
<div class="year-num"><?= $y[0] ?></div>
<h3><?= $y[1] ?></h3>
<p><?= $y[2] ?></p>
</div><?php endforeach; ?></div>
</div>
</section>
<section class="section">
<div class="container">
<div class="section-head reveal">
<span class="eyebrow">Designed for real student work</span>
<h2>One account, clearly separated <span class="gradient-text">by purpose</span>
</h2>
<p>No invented activity is added to a new dashboard. Your plans, tasks, academic records, purchases and downloads appear only when you create or complete them.</p>
</div>
<div class="card-grid">
<article class="card reveal">
<div class="card-icon">◆</div>
<h3>Private starting point</h3>
<p>Every new workspace starts empty and is personalised from onboarding answers.</p>
</article>
<article class="card reveal">
<div class="card-icon">▤</div>
<h3>Published resources only</h3>
<p>Study links appear publicly only after the EnoughEdu content team publishes them.</p>
</article>
<article class="card reveal">
<div class="card-icon">🔒</div>
<h3>Verified paid access</h3>
<p>Interactive tools unlock only after a valid plan or individual-tool purchase.</p>
</article>
</div>
</div>
</section>
<?php $homePlans = $homeDb
    ? $homeDb
        ->query(
            'SELECT name,slug,plan_type,price,billing_period,features FROM plans WHERE status="active" ORDER BY FIELD(plan_type,"full","branch","semester","category","tool"),price DESC LIMIT 4',
        )
        ->fetchAll()
    : []; ?><section class="section" id="pricing">
<div class="container">
<div class="section-head reveal">
<span class="eyebrow">Live pricing</span>
<h2>Choose access when <span class="gradient-text">you’re ready.</span>
</h2>
<p>EnoughEdu keeps these prices and included features current.</p>
</div><?php if (
    $homePlans
): ?><div class="pricing-grid"><?php foreach ($homePlans as $plan):

    $features = json_decode((string) ($plan['features'] ?? '[]'), true);
    $features = is_array($features) ? $features : [];
    $featured = $plan['plan_type'] === 'full';
    $toolPlan = $plan['plan_type'] === 'tool';
    $period =
        [
            'one_time' => 'one-time',
            'semester' => '/ semester',
            'monthly' => '/ month',
            'yearly' => '/ year',
        ][$plan['billing_period']] ?? '';
    ?><article class="price-card <?= $featured
    ? 'featured'
    : '' ?> reveal">
<span class="plan"><?= e($plan['name']) ?> <?= $featured
     ? ' · Best value'
     : '' ?></span>
<div class="price">₹<?= e(
    number_format((float) $plan['price'], 0),
) ?> <small><?= e($period) ?></small>
</div>
<ul class="features"><?php foreach (
    $features
    as $feature
): ?><li><?= e(
    ai_public_text((string) $feature),
) ?></li><?php endforeach; ?></ul>
<a class="btn <?= $featured
    ? 'btn-primary'
    : 'btn-secondary' ?> btn-block" href="<?= $toolPlan
     ? '/tools'
     : '/checkout?plan=' . e($plan['slug']) ?>"><?= $toolPlan
    ? 'Choose a tool'
    : 'Choose ' . e($plan['name']) ?></a>
</article><?php
endforeach; ?></div><?php else: ?><div class="panel empty">
<h3>Plans are being prepared</h3>
<p>New EnoughEdu plans will appear here automatically when available.</p>
</div><?php endif; ?><p style="text-align:center;margin-top:32px">
<a class="btn btn-secondary" href="/pricing">Compare all plans</a>
</p>
</div>
</section>
<section class="section">
<div class="container">
<div class="section-head">
<span class="eyebrow">FAQ</span>
<h2>Questions, answered.</h2>
</div>
<div class="faq"><?php foreach (
    [
        [
            'Can I buy just one tool?',
            'Yes. Choose an individual tool for one-time access, or select a broader plan.',
        ],
        [
            'Are the notes specific to my branch and semester?',
            'Yes. Published resources are organised by branch, semester, subject and material type.',
        ],
        [
            'Where is AI used?',
            'AI assistance is built directly into the Resume Builder, LinkedIn Profile Generator and Cover Letter Generator. Always verify generated content before using it.',
        ],
        [
            'Do plans renew automatically?',
            'No automatic renewal is enabled in this release. Time-limited access ends on the expiry date shown in your account.',
        ],
    ]
    as $f
): ?><div class="faq-item">
<button class="faq-q"><?= $f[0] ?><span>＋</span>
</button>
<div class="faq-a"><?= $f[1] ?></div>
</div><?php endforeach; ?></div>
</div>
</section>
<section class="container cta reveal">
<span class="eyebrow" style="color:#bfdbfe;border-color:#ffffff30;background:#ffffff10">Built for your ambition</span>
<h2>Your next semester starts with a clear workspace.</h2>
<p>Create your private account, complete onboarding and build from zero.</p>
<a class="btn btn-secondary" href="/signup">Create your free account →</a>
</section>
</main>
<footer class="footer">
<div class="container">
<div class="footer-grid">
<div>
<img class="logo logo-light footer-logo" src="/assets/img/logo.svg" alt="EnoughEdu">
<img class="logo logo-dark footer-logo" src="/assets/img/logo-dark.svg" alt="EnoughEdu">
<p>Your academic toolkit for the entire degree.</p>
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
</div>
<div>
<h4>Company</h4>
<a href="/about">About</a>
<a href="/contact">Contact</a>
<a href="/privacy">Privacy</a>
</div>
<div>
<h4>Support</h4>
<a href="/faq">Help centre</a>
<a href="mailto:hello@vedhant.in">hello@vedhant.in</a>
</div>
</div>
<div class="footer-bottom">
<span>© <?= date(
    'Y',
) ?> EnoughEdu. All rights reserved.</span>
<span>A project by <a href="https://vedhant.in">Vedhant Khajuria</a>.</span>
</div>
</div>
</footer>
<script src="<?= e(
     asset_url('/assets/js/app.js'),
 ) ?>" defer>
</script>
</body>
</html><?php
}
if ($path === '/index.php') {
    header('Location: /', true, 301);
    exit();
}
if ($path === '/') {
    home();
    exit();
}
route_dispatch($path);
