<?php
declare(strict_types=1);
// Compatibility with an earlier deployment: retire server file-processing routes.
function oft_route(string $path): void
{
    if (in_array($path, ['/api/file-tools/process', '/api/file-tools/download'], true)) {
        http_response_code(410);
        header('Content-Type: application/json');
        header('Cache-Control: no-store');
        echo json_encode([
            'error' => 'This tool now processes files in your browser. Refresh the tool page.',
        ]);
        exit();
    }
}

function te_description(string $slug, string $description): string
{
    $def = tool_definition($slug);
    return $def &&
        in_array(
            $def[3],
            ['image-converter', 'image-resizer', 'image-pdf', 'pdf-merge', 'pdf-split'],
            true,
        )
        ? $def[2]
        : $description;
}

function te_tool(string $slug): ?array
{
    if (!tool_definition($slug) || !db()) {
        return null;
    }
    $q = db()->prepare('SELECT * FROM tools WHERE slug=? AND status="active" LIMIT 1');
    $q->execute([$slug]);
    return $q->fetch() ?: null;
}
function te_free(array $tool): bool
{
    return (bool) $tool['is_free'] || (float) $tool['price'] === 0.0;
}
function te_price(mixed $value): float
{
    if (!is_scalar($value) || !preg_match('/^\d{1,8}(?:\.\d{1,2})?$/', (string) $value)) {
        throw new DomainException(
            'Enter ₹0 for free, or a price from ₹1 with up to two decimal places.',
        );
    }
    $price = (float) $value;
    if ($price > 99999999 || ($price > 0 && $price < 1)) {
        throw new DomainException('Use ₹0 for free or at least ₹1 for premium access.');
    }
    return $price;
}
function te_art(string $type): string
{
    return in_array($type, ['image-pdf', 'pdf-merge', 'pdf-split'], true)
        ? 'pdf'
        : (str_starts_with($type, 'image-')
            ? 'image'
            : (in_array($type, ['resume', 'linkedin', 'cover', 'ats'], true)
                ? 'career'
                : (in_array($type, ['tracker', 'pomodoro', 'countdown'], true)
                    ? 'planner'
                    : 'study')));
}
function te_guide(string $type): void
{
    $guides = [
        'pdf-merge' => [
            'Combine a complete set',
            'Select at least two PDFs in the order you want them to appear.',
            'Review before joining',
            'Up to 30 files, 40 MB each and 120 MB combined; no more than 1,000 pages. Password-protected files must be unlocked first.',
            'One organised PDF',
            'Create a single document for assignments, lecture notes or applications.',
        ],
        'pdf-split' => [
            'Choose your source PDF',
            'Select the document that contains the pages you need.',
            'Keep precisely what matters',
            'Enter pages such as 1,3,5-8. Pages appear in that order; duplicates are removed.',
            'A smaller, focused document',
            'Download the selected pages without changing your original PDF.',
        ],
        'image-pdf' => [
            'From images to a document',
            'Choose JPG, PNG or WebP images in your preferred order.',
            'Set up your pages',
            'Choose A4, Letter or fit-to-image, then adjust margins. Each image gets its own page.',
            'Ready to submit',
            'Create one PDF from scanned notes, photographs or assignment pages.',
        ],
        'image-converter' => [
            'Choose your image',
            'Start with a JPG, PNG or WebP file, up to 40 MB.',
            'Pick the right format',
            'PNG preserves transparency; JPG suits photographs; WebP balances size and detail.',
            'Download a fresh copy',
            'Your original file stays unchanged. JPEG output uses a white background.',
        ],
        'image-resizer' => [
            'Resize with confidence',
            'Select your image and inspect its original dimensions.',
            'Control size and quality',
            'Keep the aspect ratio locked to avoid stretching. Adjust dimensions, output format and quality.',
            'Ready for your destination',
            'Download the resized image for applications, documents or sharing.',
        ],
        'grades' => [
            'Bring your grade sheet',
            'Enter grade points and credits for each subject.',
            'Weighted, not averaged',
            'Each grade point is multiplied by its credits; the total is divided by all included credits.',
            'Check your institution rules',
            'Use the result for planning. Official rounding and grade scales can vary.',
        ],
        'attendance' => [
            'Start with your class totals',
            'Enter classes held, classes attended and your attendance target.',
            'Plan your next classes',
            'Calculate how many consecutive classes you need or how many you can miss safely.',
            'Keep your figures current',
            'Recalculate after each class. College rules and exemptions may differ.',
        ],
        'resume' => [
            'Tell your actual story',
            'Add your contact details, education, skills, projects and target role.',
            'Shape your application',
            'Generate a structured draft based on the facts you provide. Avoid confidential information.',
            'Review and export',
            'Check every claim and detail, then copy your resume or download its PDF.',
        ],
        'linkedin' => [
            'Define your next role',
            'Add your experience, skills and target position.',
            'Create a coherent profile',
            'Generate a headline, About section and skills list from your details.',
            'Make it sound like you',
            'Review the draft before copying it to LinkedIn or downloading the PDF.',
        ],
        'cover' => [
            'Start with the opportunity',
            'Enter the company, target role and job description alongside your relevant experience.',
            'Draft with purpose',
            'Generate a letter tailored to the details you provide.',
            'Finish with your voice',
            'Verify names and claims, refine the wording and download or copy your letter.',
        ],
        'tracker' => [
            'Make a realistic plan',
            'Add a task or milestone, choose its date and set a priority.',
            'Track small wins',
            'Mark items complete or delete those you no longer need.',
            'Stored on this browser',
            'Your list is saved on this device. Clearing browser storage removes it.',
        ],
        'pomodoro' => [
            'Set a focus interval',
            'Choose a manageable work interval and a recovery break.',
            'Start a focused session',
            'Run, pause or resume the timer while working on one task.',
            'Keep the tab open',
            'Browser background throttling can affect timer accuracy.',
        ],
        'formulas' => [
            'Find the right reference',
            'Choose a discipline or enter a name, symbol or keyword.',
            'Explore matching formulas',
            'Run the tool to display formulas and the meaning of their variables.',
            'Check assumptions',
            'Confirm units and the conditions for the formula before applying it.',
        ],
        'units' => [
            'Choose a physical quantity',
            'Select a measurement type, input unit and destination unit.',
            'Enter your value',
            'The tool applies the appropriate factors and offsets.',
            'Use consistent units',
            'Check the quantity and unit labels before using the converted result.',
        ],
    ];
    $g = $guides[$type] ?? [
        'Prepare your inputs',
        'Enter the values or text requested in the workspace below.',
        'Choose your settings',
        'Adjust the available options to match your task. Use accurate source information.',
        'Review before using',
        'Run the tool, inspect the result and copy or download where available.',
    ];
    ?><section class="container te-guide" aria-label="How this tool works"><?php for ($i = 0; $i < 3; $i++): ?><article>
<span class="eyebrow">Step <?= $i + 1 ?></span>
<h3><?= e($g[$i * 2]) ?></h3>
<p><?= e($g[$i * 2 + 1]) ?></p>
</article><?php endfor; ?></section><?php
}
function te_assets(): void
{
    ?><link rel="stylesheet" href="<?= e(asset_url('/assets/css/tool-experience.css')) ?>"><?php
}
function te_catalog(): void
{
    page_start(
        'Free Essentials & Premium Tools',
        'Find your next study, career or PDF tool. Start with your files or information and unlock premium results when you are ready.',
    );
    te_assets();
    $groups = ['Free Essentials' => [], 'Premium Tools' => []];
    $pdo = db();
    if ($pdo) {
        foreach (
            $pdo
                ->query('SELECT * FROM tools WHERE status="active" ORDER BY sort_order,name')
                ->fetchAll()
            as $tool
        ) {
            if (tool_definition($tool['slug'])) {
                $groups[te_free($tool) ? 'Free Essentials' : 'Premium Tools'][] = $tool;
            }
        }
    }
    ?><section class="section te-catalog">
<div class="container">
<span class="eyebrow">Your everyday workspace</span>
<h1>Small tasks.<br>
<span class="gradient-text">Better tools.</span>
</h1>
<p>Prepare a document, plan your studies or take the next step in your career.</p>
<nav class="te-tabs" aria-label="Tool collections">
<a href="#free-essentials">Free Essentials</a>
<a href="#premium-tools">Premium Tools</a>
</nav><?php foreach (
    $groups
    as $label => $rows
): ?><section id="<?= $label === 'Free Essentials'
    ? 'free-essentials'
    : 'premium-tools' ?>" class="te-collection">
<h2><?= e($label) ?></h2>
<p><?= $label ===
'Free Essentials'
    ? 'Useful tools, ready to use at no cost.'
    : 'Explore the workspace first. Unlock the final result when you are ready.' ?></p>
<div class="card-grid"><?php foreach (
    $rows
    as $row
):
    $def = tool_definition($row['slug']); ?><a class="card te-card" href="/tools/<?= e(
    $row['slug'],
) ?>">
<img src="/assets/img/tools/<?= te_art(
    $def[3],
) ?>.svg" alt="" width="84" height="84">
<span class="tag"><?= te_free($row)
    ? 'Free'
    : 'Premium' ?></span>
<h3><?= e($row['name']) ?></h3>
<p><?= e(
    te_description($row['slug'], trim((string) $row['description']) ?: $def[2]),
) ?></p>
<span class="card-link">Open workspace →</span>
</a><?php
endforeach; ?></div><?php if (
    !$rows
): ?><p class="muted">No tools in this collection yet.</p><?php endif; ?></section><?php endforeach; ?></div>
</section><?php page_end();
}
function te_intro(string $slug, string $name, string $description, string $type): void
{
    $tool = te_tool($slug);
    te_assets();
    ?><section class="container te-hero">
<div>
<a href="/tools">← All tools</a>
<p class="eyebrow"><?= $tool && te_free($tool) ? 'Free Essentials' : 'Premium Tools' ?></p>
<h1><?= e($name) ?></h1>
<p><?= e($description) ?></p>
<a class="btn btn-primary" href="#tool-workspace">Start using this tool</a>
<p class="muted">Prepare your inputs first. <?= $tool && te_free($tool) ? 'No payment required.' : 'Payment is requested only before the final result or download.' ?></p>
</div>
<img src="/assets/img/tools/<?= te_art($type) ?>.svg" alt="<?= e($name) ?> illustration" width="260" height="260">
</section><?php
}
function te_dialog(string $slug): void
{
    ?>
<dialog id="te-payment" class="te-dialog" aria-labelledby="te-dialog-title">
<button type="button" class="te-close" data-te-close aria-label="Close">×</button>
<span class="eyebrow">One last step</span>
<h2 id="te-dialog-title">Unlock your result</h2>
<p id="te-message" role="status">
</p>
<a id="te-checkout" class="btn btn-primary" target="_blank" rel="noopener" href="/checkout?tool=<?= e(
    $slug,
) ?>">Continue to secure checkout</a>
<button type="button" id="te-recheck" class="btn btn-secondary">I have paid — check access</button>
<p class="muted">Checkout opens in another tab. Keep this tab open to preserve your selected files and inputs. An eligible plan or previous purchase also unlocks access.</p>
<button type="button" data-te-close class="btn btn-secondary">Keep editing</button>
</dialog>
<script src="<?= e(asset_url('/assets/js/tool-experience.js')) ?>">
</script><?php
}
function te_access_api(): never
{
    header('Content-Type: application/json');
    header('Cache-Control: private, no-store');
    $slug = (string) ($_GET['tool'] ?? '');
    $tool = te_tool($slug);
    if (!$tool) {
        http_response_code(404);
        echo json_encode(['error' => 'Tool unavailable.']);
        exit();
    }
    echo json_encode([
        'allowed' => paid_access($slug),
        'free' => te_free($tool),
        'login' => (bool) user(),
        'price' => number_format((float) $tool['price'], 2),
        'csrf' => csrf_token(),
    ]);
    exit();
}
function te_admin_cancel_form(int $id, string $kind, string $name): void
{
    ?><details>
<summary class="btn btn-secondary btn-sm"><?= $kind === 'resource'
    ? 'Cancel autopay'
    : 'Cancel access' ?></summary>
<form method="post" action="/admin/action"><?= csrf_field() ?><input type="hidden" name="action" value="subscription-cancel">
<input type="hidden" name="subscription_kind" value="<?= e($kind) ?>">
<input type="hidden" name="id" value="<?= $id ?>">
<p><?= e($name) ?>: <?= $kind === 'resource' ? 'Stop future renewals. Verified paid/trial access remains until expiry. No refund is issued.' : 'End this plan access now. Other purchases remain valid.' ?></p>
<button class="btn btn-danger btn-sm">Confirm cancellation</button>
</form>
</details><?php
}
function te_admin_memberships(): void
{
    if (!function_exists('rl_ready') || !rl_ready()) {
        return;
    }
    $rows = db()
        ->query(
            'SELECT m.*,u.name,u.email FROM resource_memberships m JOIN users u ON u.id=m.user_id ORDER BY m.id DESC LIMIT 250',
        )
        ->fetchAll();
    ?><section class="panel">
<h2>Resource autopay memberships</h2><?php
foreach ($rows as $r): ?><div class="list-item">
<div>
<b><?= e($r['name']) ?></b>
<p><?= e(
    $r['email'],
) ?> · <?= e($r['status']) ?></p>
<small>Membership #<?= (int) $r['id'] ?> · <?= e(
     $r['gateway_id'] ?? 'Awaiting reconciliation',
 ) ?></small>
</div><?php if (
    !empty($r['gateway_id']) &&
    !in_array($r['status'], ['cancelled', 'completed', 'expired'], true)
) {
    te_admin_cancel_form((int) $r['id'], 'resource', $r['name']);
} ?></div><?php endforeach;
if (!$rows): ?><p>No resource memberships yet.</p><?php endif;?></section><?php
}
function te_cancel_subscription(PDO $pdo): void
{
    require_admin();
    verify_csrf();
    $id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);
    $kind = $_POST['subscription_kind'] ?? '';
    if (!$id || $id < 1) {
        throw new DomainException('Choose a subscription.');
    }
    if ($kind === 'plan') {
        $q = $pdo->prepare('SELECT auto_renew FROM subscriptions WHERE id=?');
        $q->execute([$id]);
        $r = $q->fetch();
        if (!$r) {
            throw new DomainException('Subscription not found.');
        }
        if (!empty($r['auto_renew'])) {
            throw new DomainException(
                'This legacy plan has external renewal enabled. Cancel it at its payment provider first.',
            );
        }
        $pdo->prepare(
            'UPDATE subscriptions SET status="cancelled",expires_at=NOW() WHERE id=? AND status="active"',
        )->execute([$id]);
        flash('success', 'Plan access cancelled. Other purchases are unchanged.');
    } elseif ($kind === 'resource') {
        $q = $pdo->prepare('SELECT * FROM resource_memberships WHERE id=?');
        $q->execute([$id]);
        $row = $q->fetch();
        if (!$row || !preg_match('/^sub_[A-Za-z0-9]+$/', (string) $row['gateway_id'])) {
            throw new DomainException('Reconcile this membership with Razorpay first.');
        }
        $remote = rm_api('GET', 'subscriptions/' . rawurlencode($row['gateway_id']));
        if (
            ($remote['id'] ?? '') !== $row['gateway_id'] ||
            ($remote['plan_id'] ?? '') !== $row['plan_id']
        ) {
            throw new RuntimeException('Subscription mismatch.');
        }
        if (!in_array($remote['status'] ?? '', ['cancelled', 'completed', 'expired'], true)) {
            $remote = rm_api(
                'POST',
                'subscriptions/' . rawurlencode($row['gateway_id']) . '/cancel',
                ['cancel_at_cycle_end' => 0],
            );
        }
        if (
            ($remote['id'] ?? '') !== $row['gateway_id'] ||
            !in_array($remote['status'] ?? '', ['cancelled', 'completed', 'expired'], true)
        ) {
            throw new RuntimeException('Razorpay has not confirmed cancellation. Retry to verify.');
        }
        $pdo->prepare('UPDATE resource_memberships SET status=?,updated_at=? WHERE id=?')->execute([
            $remote['status'],
            time(),
            $id,
        ]);
        flash(
            'success',
            'Future autopay cancelled. Existing verified access remains until expiry.',
        );
    } else {
        throw new DomainException('Unknown subscription type.');
    }
}
