<?php
declare(strict_types=1);
require_once __DIR__ . '/resource-membership.php';

function rl_ready(): bool
{
    static $ready;
    if ($ready !== null) {
        return $ready;
    }
    try {
        $pdo = db();
        if (!$pdo) {
            return $ready = false;
        }
        foreach (
            [
                'resource_branches',
                'resource_files',
                'resource_memberships',
                'resource_payment_receipts',
            ]
            as $t
        ) {
            $pdo->query("SELECT 1 FROM $t LIMIT 1");
        }
        return $ready = true;
    } catch (Throwable) {
        return $ready = false;
    }
}
function rl_root(): string
{
    return dirname(__DIR__) . '/storage/resource-library';
}
function rl_pdf(string $name): string
{
    if (!preg_match('/\A[a-zA-Z0-9][a-zA-Z0-9 _.-]{0,180}\.pdf\z/i', $name)) {
        throw new DomainException(
            'Use a simple PDF filename, such as maths-unit-1.pdf. Upload it to the private resource-library folder.',
        );
    }
    $root = realpath(rl_root());
    $file = realpath(rl_root() . '/' . $name);
    if (!$root || !$file || dirname($file) !== $root || !is_file($file)) {
        throw new DomainException('PDF not found in the private resource-library folder.');
    }
    // storage/ is denied by Apache; realpath above confines downloads to this directory.
    $handle = fopen($file, 'rb');
    $magic = fread($handle, 5);
    fclose($handle);
    if ($magic !== '%PDF-') {
        throw new DomainException('The selected file is not a PDF.');
    }
    return $file;
}
function rl_preview(string $file): array
{
    $key = hash_file('sha256', $file);
    $dir = rl_root() . '/previews/' . $key;
    $manifest = $dir . '/manifest.json';
    if (is_file($manifest)) {
        $data = json_decode((string) file_get_contents($manifest), true);
        $count = (int) ($data['pages'] ?? 0);
        if ($count > 0 && $count <= 1000) {
            $ok = true;
            for ($p = 1; $p <= $count; $p++) {
                if (!is_file($dir . '/' . $p . '.jpg')) {
                    $ok = false;
                    break;
                }
            }
            if ($ok) {
                return [$key, $count];
            }
        }
    }
    if (!class_exists('Imagick')) {
        throw new DomainException(
            'Previews are not prepared. Ask hosting to enable Imagick and PDF support, or upload prepared page previews.',
        );
    }
    if (filesize($file) > 50 * 1024 * 1024) {
        throw new DomainException(
            'For PDFs above 50 MB, prepare previews with a PDF-to-image tool.',
        );
    }
    if (!is_dir($dir) && !mkdir($dir, 0750, true)) {
        throw new DomainException('Preview folder is not writable.');
    }
    try {
        Imagick::setResourceLimit(Imagick::RESOURCETYPE_MEMORY, 128 * 1024 * 1024);
        Imagick::setResourceLimit(Imagick::RESOURCETYPE_MAP, 256 * 1024 * 1024);
        $info = new Imagick();
        $info->pingImage($file);
        $count = $info->getNumberImages();
        $info->clear();
        if ($count < 1 || $count > 40) {
            throw new RuntimeException('Use the offline converter for PDFs above 40 pages.');
        }
        for ($p = 0; $p < $count; $p++) {
            $im = new Imagick();
            $im->setResolution(100, 100);
            $im->readImage($file . '[' . $p . ']');
            $im->setImageBackgroundColor('white');
            $flat = $im->mergeImageLayers(Imagick::LAYERMETHOD_FLATTEN);
            $flat->setImageFormat('jpeg');
            $flat->setImageCompressionQuality(82);
            $flat->stripImage();
            $flat->writeImage($dir . '/' . ($p + 1) . '.jpg');
            $flat->clear();
            $im->clear();
        }
        file_put_contents($manifest, json_encode(['pages' => $count]), LOCK_EX);
        return [$key, $count];
    } catch (Throwable $e) {
        throw new DomainException(
            'The server could not render this PDF. Prepare previews with a PDF-to-image tool and upload them, then save again.',
        );
    }
}
function rl_item(string $kind, int $id): ?array
{
    if (!in_array($kind, ['note', 'resource'], true) || $id < 1) {
        return null;
    }
    $pdo = db();
    if (!$pdo) {
        return null;
    }
    $table = $kind === 'note' ? 'notes' : 'resources';
    $q = $pdo->prepare(
        "SELECT m.*,f.pdf_name,f.preview_key,f.page_count FROM $table m LEFT JOIN resource_files f ON f.material_kind=? AND f.material_id=m.id WHERE m.id=? AND m.status='published'",
    );
    $q->execute([$kind, $id]);
    $item = $q->fetch();
    if (!$item) {
        return null;
    }
    $item['kind'] = $kind;
    return $item;
}
function rl_assets(): void
{
    ?><link rel="stylesheet" href="<?= e(
    asset_url('/assets/css/resource-library.css'),
) ?>">
<script src="<?= e(asset_url('/assets/js/resource-library.js')) ?>" defer>
</script><?php
}
function rl_public_path(array $item): string
{
    $name = (string) ($item['slug'] ?? '');
    // Older uploads already have an internal random suffix; the numeric ID keeps public URLs unique.
    $name = preg_replace('/-[a-f0-9]{8,12}$/', '', $name) ?? $name;
    $name = trim(preg_replace('/[^a-z0-9-]+/', '-', strtolower($name)) ?? '', '-');
    if ($name === '') {
        $name = 'study-resource';
    }
    return '/resources/' .
        ($item['kind'] === 'note' ? 'note' : 'resource') .
        '/' .
        (int) $item['id'] .
        '/' .
        $name;
}
function rl_meta_description(array $item): string
{
    $description = trim(preg_replace('/\s+/u', ' ', (string) ($item['description'] ?? '')) ?? '');
    return $description !== ''
        ? $description
        : 'Read ' .
                (string) $item['title'] .
                ' for ' .
                (string) $item['subject'] .
                '. Free page previews on EnoughEdu; original PDF downloads with eligible access.';
}
function rl_resource_schema(array $item): array
{
    $canonical = url(rl_public_path($item));
    $resource = [
        '@type' => 'LearningResource',
        '@id' => $canonical . '#resource',
        'url' => $canonical,
        'name' => $item['title'],
        'description' => rl_meta_description($item),
        'learningResourceType' => $item['kind'] === 'note' ? 'Study notes' : 'Study resource',
        'about' => ['@type' => 'Thing', 'name' => $item['subject']],
        'publisher' => ['@type' => 'Organization', 'name' => 'EnoughEdu', 'url' => url('/')],
    ];
    foreach (
        ['created_at' => 'dateCreated', 'updated_at' => 'dateModified']
        as $column => $property
    ) {
        $timestamp = strtotime((string) ($item[$column] ?? ''));
        if ($timestamp !== false) {
            $resource[$property] = date(DATE_ATOM, $timestamp);
        }
    }
    return [
        '@context' => 'https://schema.org',
        '@graph' => [
            $resource,
            [
                '@type' => 'BreadcrumbList',
                'itemListElement' => [
                    ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => url('/')],
                    [
                        '@type' => 'ListItem',
                        'position' => 2,
                        'name' => 'Resources',
                        'item' => url('/resources'),
                    ],
                    [
                        '@type' => 'ListItem',
                        'position' => 3,
                        'name' => $item['title'],
                        'item' => $canonical,
                    ],
                ],
            ],
        ],
    ];
}
function rl_sitemap_xml(array $rows, bool $index = false): string
{
    $escape = static fn(string $value): string => htmlspecialchars(
        $value,
        ENT_XML1 | ENT_QUOTES,
        'UTF-8',
    );
    $tag = $index ? 'sitemapindex' : 'urlset';
    $xml =
        '<?xml version="1.0" encoding="UTF-8"?>' .
        "\n" .
        '<' .
        $tag .
        ' xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' .
        "\n";
    foreach ($rows as $row) {
        $entry = $index ? 'sitemap' : 'url';
        $xml .=
            '  <' .
            $entry .
            '><loc>' .
            $escape($index ? $row['url'] : url(rl_public_path($row))) .
            '</loc>';
        if (!$index && !empty($row['updated_at'])) {
            $timestamp = strtotime($row['updated_at']);
            if ($timestamp !== false) {
                $xml .= '<lastmod>' . $escape(date(DATE_ATOM, $timestamp)) . '</lastmod>';
            }
        }
        $xml .= '</' . $entry . '>' . "\n";
    }
    return $xml . '</' . $tag . '>';
}
function rl_sitemap(?int $page = null): never
{
    $pdo = db();
    $source =
        "(SELECT 'note' kind,id,slug,status,updated_at FROM notes UNION ALL SELECT 'resource',id,slug,status,updated_at FROM resources) x JOIN resource_files f ON f.material_kind=x.kind AND f.material_id=x.id WHERE x.status='published' AND f.page_count>0";
    $count = (int) $pdo->query('SELECT COUNT(*) FROM ' . $source)->fetchColumn();
    $pages = max(1, (int) ceil($count / 1000));
    if ($page !== null && ($page < 1 || $page > $pages)) {
        http_response_code(404);
        exit('Sitemap not found.');
    }
    header('Content-Type: application/xml; charset=utf-8');
    header('Cache-Control: no-cache');
    header('X-Content-Type-Options: nosniff');
    if ($page === null) {
        $rows = [];
        for ($p = 1; $p <= $pages; $p++) {
            $rows[] = ['url' => url('/resources-sitemap-' . $p . '.xml')];
        }
        echo rl_sitemap_xml($rows, true);
    } else {
        $offset = ($page - 1) * 1000;
        $rows = $pdo
            ->query(
                'SELECT x.kind,x.id,x.slug,x.updated_at FROM ' .
                    $source .
                    ' ORDER BY x.kind,x.id LIMIT 1000 OFFSET ' .
                    $offset,
            )
            ->fetchAll();
        echo rl_sitemap_xml($rows);
    }
    exit();
}
function rl_search_terms(string $query): array
{
    $q = strtolower(trim(preg_replace('/[^\p{L}\p{N}\s]+/u', ' ', $query) ?? $query));
    $q = preg_replace('/\s+/', ' ', $q);
    if ($q === '') {
        return [];
    }
    $terms = [$q];
    $stop = [
        'notes',
        'note',
        'pdf',
        'the',
        'for',
        'of',
        'and',
        'with',
        'please',
        'find',
        'me',
        'about',
        'on',
    ];
    foreach (explode(' ', $q) as $word) {
        if (strlen($word) > 1 && !in_array($word, $stop, true)) {
            $terms[] = $word;
        }
    }
    $groups = [
        ['dbms', 'database', 'databases', 'database management', 'sql', 'normalization'],
        ['os', 'operating system', 'operating systems', 'scheduling', 'deadlock'],
        ['dsa', 'data structures', 'data structure', 'algorithms', 'algorithm'],
        ['oop', 'oops', 'object oriented', 'inheritance', 'polymorphism'],
        ['cn', 'computer networks', 'computer network', 'networking', 'tcp'],
        ['math', 'maths', 'mathematics', 'calculus', 'algebra'],
        ['ai', 'artificial intelligence', 'machine learning', 'ml'],
        ['dld', 'digital logic', 'digital electronics', 'boolean algebra'],
        ['de', 'differential equations', 'differential equation'],
        ['toc', 'theory of computation', 'automata', 'formal languages'],
        ['coa', 'computer architecture', 'computer organization', 'computer organisation'],
        ['ds', 'discrete mathematics', 'discrete maths', 'discrete structures'],
        ['som', 'strength of materials', 'mechanics of materials'],
        ['thermo', 'thermodynamics', 'heat transfer'],
        ['bee', 'basic electrical', 'electrical engineering', 'circuits'],
        ['pyq', 'previous year', 'question paper', 'past paper'],
        ['chem', 'chemistry'],
        ['phy', 'physics'],
    ];
    foreach ($groups as $group) {
        $matched = false;
        foreach ($group as $alias) {
            if (preg_match('/(?<![a-z0-9])' . preg_quote($alias, '/') . '(?![a-z0-9])/', $q)) {
                $matched = true;
                break;
            }
        }
        if ($matched) {
            array_push($terms, ...$group);
        }
    }
    return array_slice(array_values(array_unique($terms)), 0, 24);
}
function rl_spelling_suggestion(PDO $pdo, string $query): string
{
    $words = preg_split('/\s+/', strtolower(trim($query))) ?: [];
    if (count($words) > 8) {
        return '';
    }
    $rows = $pdo
        ->query(
            "SELECT title,subject FROM (SELECT title,subject,created_at FROM notes WHERE status='published' UNION ALL SELECT title,subject,created_at FROM resources WHERE status='published') words ORDER BY created_at DESC LIMIT 5000",
        )
        ->fetchAll();
    $vocab = [];
    foreach ($rows as $r) {
        foreach (
            preg_split('/[^a-z0-9]+/', strtolower($r['title'] . ' ' . $r['subject'])) ?: []
            as $w
        ) {
            if (strlen($w) >= 4 && strlen($w) <= 30) {
                $vocab[$w] = true;
            }
        }
    }
    $changed = false;
    foreach ($words as &$word) {
        if (strlen($word) < 4 || strlen($word) > 30 || isset($vocab[$word])) {
            continue;
        }
        $best = $word;
        $distance = strlen($word) > 7 ? 3 : 2;
        foreach ($vocab as $candidate => $_) {
            if (abs(strlen($candidate) - strlen($word)) > 2) {
                continue;
            }
            $d = levenshtein($word, $candidate);
            if ($d < $distance) {
                $distance = $d;
                $best = $candidate;
            }
        }
        if ($best !== $word) {
            $word = $best;
            $changed = true;
        }
    }
    unset($word);
    return $changed ? implode(' ', $words) : '';
}
function rl_search_rows(PDO $pdo, string $slug, string $query, int $offset): array
{
    $params = [];
    $score = '0';
    $terms = rl_search_terms($query);
    if ($terms) {
        $parts = [];
        foreach ($terms as $i => $term) {
            $like = '%' . strtr($term, ['!' => '!!', '%' => '!%', '_' => '!_']) . '%';
            $weight = $i === 0 ? 6 : 1;
            $parts[] =
                "((LOWER(x.title) LIKE ? ESCAPE '!') * " .
                6 * $weight .
                " + (LOWER(x.subject) LIKE ? ESCAPE '!') * " .
                4 * $weight .
                " + (LOWER(x.description) LIKE ? ESCAPE '!') * $weight)";
            array_push($params, $like, $like, $like);
        }
        $score = implode('+', $parts);
    }
    $filter = '';
    if ($slug !== '') {
        $filter =
            ' AND EXISTS(SELECT 1 FROM resource_branches rb JOIN branches b ON b.id=rb.branch_id WHERE rb.material_kind=x.kind AND rb.material_id=x.id AND b.slug=? AND b.status="active")';
        $params[] = $slug;
    }
    $sql =
        "SELECT x.*,($score) relevance FROM (SELECT 'note' kind,id,slug,title,subject,COALESCE(description,'') description,status,created_at FROM notes UNION ALL SELECT 'resource',id,slug,title,subject,COALESCE(description,''),status,created_at FROM resources) x WHERE x.status='published' $filter" .
        ($terms ? ' HAVING relevance>0' : '') .
        ' ORDER BY relevance DESC,x.created_at DESC,x.kind,x.id DESC LIMIT 25 OFFSET ' .
        max(0, $offset);
    $q = $pdo->prepare($sql);
    $q->execute($params);
    return $q->fetchAll();
}
function rl_list(?array $branch = null): void
{
    $pdo = db();
    $qtext = text_limit(trim((string) ($_GET['q'] ?? '')), 120);
    $slug = $branch['slug'] ?? trim((string) ($_GET['branch'] ?? ''));
    $branches = $pdo
        ->query('SELECT id,name,slug FROM branches WHERE status="active" ORDER BY sort_order,name')
        ->fetchAll();
    $page = max(1, (int) ($_GET['page'] ?? 1));
    $offset = ($page - 1) * 24;
    $items = rl_search_rows($pdo, $slug, $qtext, $offset);
    $suggestion = '';
    if (!$items && $qtext !== '' && $page === 1) {
        $suggestion = rl_spelling_suggestion($pdo, $qtext);
        if ($suggestion !== '') {
            $items = rl_search_rows($pdo, $slug, $suggestion, 0);
        }
    }
    $more = count($items) > 24;
    $items = array_slice($items, 0, 24);
    page_start(
        $branch ? $branch['name'] . ' Resources' : 'Resource Library',
        'Preview study resources for free. Download original PDFs with a resource membership.',
    );
    rl_assets();
    ?>
    <section class="section">
<div class="container">
<span class="eyebrow">Read freely. Learn at your pace.</span>
<h1 class="rl-heading"><?= e(
        $branch['name'] ?? 'Your study library',
    ) ?></h1>
<p class="muted">Free page previews for everyone. A resource membership unlocks original PDF downloads.</p>
<a class="card-link" href="/resources/membership">Download membership &rarr;</a>
    <form class="panel resource-search" action="/resources" method="get" style="margin:26px 0">
<label class="sr-only" for="rl-search">Search resources</label>
<input id="rl-search" class="input" name="q" value="<?= e(
        $qtext,
    ) ?>" placeholder="Search title, subject or topic">
<label class="sr-only" for="rl-branch">Branch</label>
<select id="rl-branch" class="input" name="branch">
<option value="">All branches</option><?php foreach ($branches as $b): ?><option value="<?= e($b['slug']) ?>" <?= $slug === $b['slug'] ? 'selected' : '' ?>><?= e($b['name']) ?></option><?php endforeach; ?></select>
<button class="btn btn-primary">Search</button>
</form>
    <?php if (
        $suggestion !== '' &&
        $items
    ): ?><p>Showing related results for <a class="card-link" href="/resources?<?= e(
    http_build_query(['q' => $suggestion, 'branch' => $slug]),
) ?>"><?= e($suggestion) ?></a>.</p><?php elseif (
        $qtext !== ''
    ): ?><p class="muted">Closest matches first, including related topics and abbreviations.</p><?php endif; ?>
    <div class="resource-grid"><?php
    foreach (
        $items
        as $item
    ): ?><article class="resource-card">
<span class="tag">FREE PREVIEW</span>
<h3 style="margin-top:18px"><?= e(
    $item['title'],
) ?></h3>
<p class="muted"><?= e($item['subject']) ?></p>
<p><?= e(
    text_limit((string) $item['description'], 220),
) ?></p>
<a class="btn btn-primary btn-sm" href="<?= e(
    rl_public_path($item),
) ?>">View resource &rarr;</a>
</article><?php endforeach;
    if (!$items): ?><p class="panel">No resources match your search.</p><?php endif;
    ?></div>
<div class="rl-actions"><?php
if ($page > 1): ?><a class="btn btn-secondary" href="/resources?<?= e(
    http_build_query(['q' => $qtext, 'branch' => $slug, 'page' => $page - 1]),
) ?>">Previous</a><?php endif;
if ($more): ?><a class="btn btn-secondary" href="/resources?<?= e(
    http_build_query(['q' => $suggestion ?: $qtext, 'branch' => $slug, 'page' => $page + 1]),
) ?>">Next</a><?php endif;
?></div>
</div>
</section><?php page_end();
}
function rl_view(?array $item = null): never
{
    $item = $item ?? rl_item((string) ($_GET['kind'] ?? ''), (int) ($_GET['id'] ?? 0));
    if (!$item) {
        not_found();
        exit();
    }
    $canonical = rl_public_path($item);
    $requested = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
    if ($requested !== $canonical) {
        header('Location: ' . $canonical, true, 301);
        exit();
    }
    header('Cache-Control: no-store');
    if (empty($item['pdf_name'])) {
        header('X-Robots-Tag: noindex, follow');
    }
    page_start($item['title'], rl_meta_description($item));
    echo json_ld(rl_resource_schema($item));
    rl_assets();
    $base = 'kind=' . rawurlencode($item['kind']) . '&id=' . (int) $item['id'];
    ?>
    <section class="section">
<div class="container">
<a class="card-link" href="/resources">&larr; Resource library</a>
<h1 class="rl-heading"><?= e(
        $item['title'],
    ) ?></h1>
<p class="muted"><?= e($item['subject']) ?></p>
<p><?= e($item['description']) ?></p>
    <?php if (!empty($item['pdf_name'])): ?>
    <div class="rl-toolbar">
<span>Free preview · <?= (int) $item[
        'page_count'
    ] ?> pages</span>
<button class="btn btn-primary" data-rl-download data-download-url="<?= e(
     '/material/download?' . $base,
 ) ?>">Download PDF &darr;</button>
</div>
    <div class="rl-pages"><?php for (
        $p = 1;
        $p <= (int) $item['page_count'];
        $p++
    ): ?><figure>
<img src="<?= e('/material/preview?' . $base . '&page=' . $p) ?>" alt="<?= e(
    $item['title'],
) ?> — page <?= $p ?>" loading="lazy" decoding="async">
<figcaption>Page <?= $p ?></figcaption>
</figure><?php endfor; ?></div>
    <?php else: ?><div class="panel">
<h3>Preview is being prepared</h3>
<p>This resource is waiting for its PDF and preview pages to be added. Please check back soon.</p>
</div><?php endif; ?>
    </div>
</section><?php
    rm_popup();
    page_end();
    exit();
}
function rl_image(): never
{
    $item = rl_item((string) ($_GET['kind'] ?? ''), (int) ($_GET['id'] ?? 0));
    $p = (int) ($_GET['page'] ?? 0);
    if (
        !$item ||
        $p < 1 ||
        $p > (int) $item['page_count'] ||
        !preg_match('/\A[a-f0-9]{64}\z/', (string) $item['preview_key'])
    ) {
        http_response_code(404);
        exit();
    }
    $file = rl_root() . '/previews/' . $item['preview_key'] . '/' . $p . '.jpg';
    if (!is_file($file)) {
        http_response_code(404);
        exit();
    }
    header('Content-Type: image/jpeg');
    header('X-Content-Type-Options: nosniff');
    header('Cache-Control: private, max-age=300');
    session_write_close();
    readfile($file);
    exit();
}
function rl_download(): never
{
    $item = rl_item((string) ($_GET['kind'] ?? ''), (int) ($_GET['id'] ?? 0));
    if (!$item || empty($item['pdf_name'])) {
        http_response_code(404);
        exit('PDF not available.');
    }
    $u = require_auth();
    if (!rm_can_download($u, $item)) {
        flash('error', 'Set up a resource membership to download PDFs.');
        redirect('/resources/membership');
    }
    try {
        $file = rl_pdf($item['pdf_name']);
    } catch (DomainException) {
        http_response_code(404);
        exit('PDF not available.');
    }
    $pdo = db();
    $pdo->prepare(
        'INSERT INTO downloads(user_id,item_type,item_id,ip_address) VALUES(?,?,?,?)',
    )->execute([$u['id'], $item['kind'], $item['id'], $_SERVER['REMOTE_ADDR'] ?? null]);
    $table = $item['kind'] === 'note' ? 'notes' : 'resources';
    $pdo->prepare("UPDATE $table SET download_count=download_count+1 WHERE id=?")->execute([
        $item['id'],
    ]);
    header('Content-Type: application/pdf');
    header(
        'Content-Disposition: attachment; filename="' .
            str_replace('"', '', $item['pdf_name']) .
            '"',
    );
    header('Content-Length: ' . filesize($file));
    header('Cache-Control: private, no-store');
    header('X-Content-Type-Options: nosniff');
    session_write_close();
    readfile($file);
    exit();
}
function rl_admin(): void
{
    require_admin();
    $pdo = db();
    $edit = null;
    $kind = in_array($_GET['kind'] ?? '', ['note', 'resource'], true) ? $_GET['kind'] : 'note';
    $id = (int) ($_GET['edit'] ?? 0);
    $selected = [];
    if ($id) {
        $table = $kind === 'note' ? 'notes' : 'resources';
        $q = $pdo->prepare(
            "SELECT m.*,f.pdf_name FROM $table m LEFT JOIN resource_files f ON f.material_kind=? AND f.material_id=m.id WHERE m.id=?",
        );
        $q->execute([$kind, $id]);
        $edit = $q->fetch() ?: null;
        $q = $pdo->prepare(
            'SELECT branch_id FROM resource_branches WHERE material_kind=? AND material_id=?',
        );
        $q->execute([$kind, $id]);
        $selected = array_map('intval', $q->fetchAll(PDO::FETCH_COLUMN));
    }
    $branches = $pdo
        ->query('SELECT id,name FROM branches WHERE status="active" ORDER BY sort_order,name')
        ->fetchAll();
    $rows = $pdo
        ->query(
            "SELECT x.*,f.pdf_name,(SELECT GROUP_CONCAT(b.name ORDER BY b.name SEPARATOR ', ') FROM resource_branches rb JOIN branches b ON b.id=rb.branch_id WHERE rb.material_kind=x.kind AND rb.material_id=x.id) branch_names FROM (SELECT 'note' kind,id,slug,title,status FROM notes UNION ALL SELECT 'resource',id,slug,title,status FROM resources) x LEFT JOIN resource_files f ON f.material_kind=x.kind AND f.material_id=x.id ORDER BY x.id DESC LIMIT 200",
        )
        ->fetchAll();
    app_start('Admin · Resources', 'resources', true);
    rl_assets();
    rm_admin_form();
    ?>
    <section class="panel">
<h3><?= $edit
        ? 'Edit resource'
        : 'Add resource' ?></h3>
<p class="muted">Upload the original PDF in cPanel to <code>storage/resource-library/</code>, then enter its filename below. Free previews use page images; the original is served only after access checks.</p>
    <form method="post" action="/admin/resources/save"><?= csrf_field() ?><input type="hidden" name="id" value="<?= $id ?>">
    <div class="form-row">
<div class="field">
<label for="rl-kind">Content type</label>
<select class="input" id="rl-kind" name="kind" <?= $edit
        ? 'disabled'
        : '' ?>>
<option value="note" <?= $kind === 'note'
    ? 'selected'
    : '' ?>>Notes</option>
<option value="resource" <?= $kind === 'resource'
    ? 'selected'
    : '' ?>>Other study resource</option>
</select><?php if (
    $edit
): ?><input type="hidden" name="kind" value="<?= e(
    $kind,
) ?>"><?php endif; ?></div>
<div class="field">
<label for="rl-title">Title</label>
<input class="input" id="rl-title" name="title" maxlength="220" required value="<?= e(
    $edit['title'] ?? '',
) ?>">
</div>
</div>
    <fieldset class="rl-branches">
<legend>Engineering branches — select one or more</legend><?php foreach (
        $branches
        as $b
    ): ?><label>
<input type="checkbox" name="branch_ids[]" value="<?= (int) $b[
    'id'
] ?>" <?= in_array((int) $b['id'], $selected, true) ? 'checked' : '' ?>> <?= e(
    $b['name'],
) ?></label><?php endforeach; ?></fieldset>
    <div class="form-row">
<div class="field">
<label for="rl-subject">Subject</label>
<input class="input" id="rl-subject" name="subject" maxlength="160" required value="<?= e(
        $edit['subject'] ?? '',
    ) ?>">
</div>
<div class="field">
<label for="rl-file">Private PDF filename</label>
<input class="input" id="rl-file" name="pdf_name" maxlength="190" required placeholder="engineering-maths.pdf" value="<?= e(
    $edit['pdf_name'] ?? '',
) ?>">
</div>
</div>
    <div class="field">
<label for="rl-description">About this resource / topics covered</label>
<textarea class="input" id="rl-description" name="description" maxlength="5000" rows="6" placeholder="Describe this note in your own words: subject, covered topics, units, and who it helps. This text appears on its public page and helps search engines understand it."><?= e(
        $edit['description'] ?? '',
    ) ?></textarea>
</div>
<div class="field">
<label for="rl-status">Visibility</label>
<select class="input" id="rl-status" name="status"><?php foreach (
    ['published', 'draft', 'archived']
    as $s
): ?><option value="<?= $s ?>" <?= ($edit['status'] ?? 'published') === $s
    ? 'selected'
    : '' ?>><?= ucfirst($s) ?></option><?php endforeach; ?></select>
</div>
    <p class="muted">All previews are free. The resource membership controls downloads; existing purchases retain their access.</p>
<button class="btn btn-primary">Save resource</button> <a class="btn btn-secondary" href="/admin/resources">Clear form</a>
</form>
</section>
    <section class="panel" style="margin-top:24px">
<h3>Resource library</h3>
<div class="table-wrap">
<table class="table">
<thead>
<tr>
<th>Resource</th>
<th>Branches</th>
<th>Preview</th>
<th>Status</th>
<th>Manage</th>
</tr>
</thead>
<tbody><?php foreach (
        $rows
        as $r
    ): ?><tr>
<td><?= e($r['title']) ?></td>
<td><?= e($r['branch_names']) ?></td>
<td><?= $r[
    'pdf_name'
]
    ? 'Ready'
    : 'Upload private PDF' ?></td>
<td><?= e(
    $r['status'],
) ?></td>
<td>
<a class="btn btn-secondary btn-sm" href="/admin/resources?edit=<?= (int) $r[
    'id'
] ?>&amp;kind=<?= e($r['kind']) ?>">Edit</a><?php if (
    $r['status'] === 'published'
): ?> <a class="btn btn-secondary btn-sm" href="<?= e(
     rl_public_path($r),
 ) ?>" target="_blank" rel="noopener">View page</a><?php endif; ?></td>
</tr><?php endforeach; ?></tbody>
</table>
</div>
</section><?php app_end();
}
function rl_save(): never
{
    $u = require_admin();
    verify_csrf();
    $pdo = db();
    try {
        $kind = (string) ($_POST['kind'] ?? '');
        if (!in_array($kind, ['note', 'resource'], true)) {
            throw new DomainException('Choose a content type.');
        }
        $table = $kind === 'note' ? 'notes' : 'resources';
        $id = (int) ($_POST['id'] ?? 0);
        $title = trim((string) ($_POST['title'] ?? ''));
        $subject = trim((string) ($_POST['subject'] ?? ''));
        $description = trim((string) ($_POST['description'] ?? ''));
        $status = (string) ($_POST['status'] ?? 'draft');
        if (
            $title === '' ||
            strlen($title) > 660 ||
            $subject === '' ||
            strlen($subject) > 480 ||
            strlen($description) > 15000 ||
            !in_array($status, ['draft', 'published', 'archived'], true)
        ) {
            throw new DomainException('Check title, subject, description and visibility.');
        }
        $ids = is_array($_POST['branch_ids'] ?? null)
            ? array_values(array_unique(array_map('intval', $_POST['branch_ids'])))
            : [];
        if (!$ids || count($ids) > 50) {
            throw new DomainException('Select at least one branch.');
        }
        $marks = implode(',', array_fill(0, count($ids), '?'));
        $q = $pdo->prepare(
            "SELECT COUNT(*) FROM branches WHERE status='active' AND id IN ($marks)",
        );
        $q->execute($ids);
        if ((int) $q->fetchColumn() !== count($ids)) {
            throw new DomainException('Select valid active branches.');
        }
        $name = trim((string) ($_POST['pdf_name'] ?? ''));
        $file = rl_pdf($name);
        [$key, $pages] = rl_preview($file);
        $pdo->beginTransaction();
        if ($id) {
            $q = $pdo->prepare("SELECT id FROM $table WHERE id=? FOR UPDATE");
            $q->execute([$id]);
            if (!$q->fetchColumn()) {
                throw new DomainException('Resource not found.');
            }
            $q = $pdo->prepare(
                "UPDATE $table SET title=?,subject=?,description=?,branch_id=?,status=?,external_url=NULL WHERE id=?",
            );
            $q->execute([$title, $subject, $description, $ids[0], $status, $id]);
        } else {
            $slug = text_limit(slug($title), 190) . '-' . bin2hex(random_bytes(6));
            $extra = $kind === 'note' ? ',note_type,uploaded_by' : ',resource_type';
            $values = $kind === 'note' ? ",'semester',?" : ",'other'";
            $args = [$title, $slug, $subject, $description, $ids[0], $status];
            if ($kind === 'note') {
                $args[] = $u['id'];
            }
            $pdo->prepare(
                "INSERT INTO $table(title,slug,subject,description,branch_id,status,is_premium,price $extra) VALUES(?,?,?,?,?,?,1,0 $values)",
            )->execute($args);
            $id = (int) $pdo->lastInsertId();
        }
        $pdo->prepare(
            'DELETE FROM resource_branches WHERE material_kind=? AND material_id=?',
        )->execute([$kind, $id]);
        $q = $pdo->prepare('INSERT INTO resource_branches VALUES(?,?,?)');
        foreach ($ids as $b) {
            $q->execute([$kind, $id, $b]);
        }
        $pdo->prepare(
            'INSERT INTO resource_files VALUES(?,?,?,?,?) ON DUPLICATE KEY UPDATE pdf_name=VALUES(pdf_name),preview_key=VALUES(preview_key),page_count=VALUES(page_count)',
        )->execute([$kind, $id, $name, $key, $pages]);
        $pdo->commit();
        flash('success', 'Resource saved for all selected branches.');
    } catch (Throwable $e) {
        if ($pdo && $pdo->inTransaction()) {
            $pdo->rollBack();
        }
        flash(
            'error',
            $e instanceof DomainException
                ? $e->getMessage()
                : 'Could not save this resource. Check the database migration and try again.',
        );
    }
    redirect('/admin/resources');
}
function rl_route(string $path): bool
{
    if (!rl_ready()) {
        return false;
    }
    if ($path === '/resources-sitemap.xml') {
        rl_sitemap();
    }
    if (preg_match('#^/resources-sitemap-([0-9]+)\.xml$#', $path, $match)) {
        rl_sitemap((int) $match[1]);
    }
    if (preg_match('#^/resources/(note|resource)/([0-9]+)/([a-z0-9-]+)/?$#', $path, $match)) {
        $item = rl_item($match[1], (int) $match[2]);
        if (!$item) {
            not_found();
            return true;
        }
        rl_view($item);
    }
    if ($path === '/branches') {
        rl_branches();
        return true;
    }
    if ($path === '/checkout' && !empty($_GET['material_kind'])) {
        redirect('/resources/membership');
    }
    if ($path === '/payment/create' && is_post() && !empty($_POST['material_kind'])) {
        redirect('/resources/membership');
    }
    if ($path === '/material/preview') {
        rl_image();
    }
    if ($path === '/material/download') {
        rl_download();
    }
    if ($path === '/resources/membership') {
        rm_page();
        return true;
    }
    if ($path === '/resources/membership/create' && is_post()) {
        rm_create();
    }
    if ($path === '/resources/membership/verify' && is_post()) {
        rm_verify();
    }
    if ($path === '/resources/membership/cancel' && is_post()) {
        rm_cancel();
    }
    if ($path === '/resources/membership/status') {
        rm_status();
    }
    if ($path === '/resources/membership/webhook' && is_post()) {
        rm_webhook();
    }
    if ($path === '/payment/razorpay/webhook' && is_post()) {
        rm_shared_webhook();
    }
    if ($path === '/admin/resources/membership' && is_post()) {
        rm_admin_save();
    }
    if ($path === '/admin/resources/reconcile' && is_post()) {
        rm_admin_reconcile();
    }
    return false;
}
function rl_branches(): void
{
    $rows = db()
        ->query(
            "SELECT b.name,b.slug,COUNT(x.id) material_count FROM branches b LEFT JOIN resource_branches rb ON rb.branch_id=b.id LEFT JOIN (SELECT 'note' kind,id,status FROM notes UNION ALL SELECT 'resource',id,status FROM resources) x ON x.kind=rb.material_kind AND x.id=rb.material_id AND x.status='published' WHERE b.status='active' GROUP BY b.id,b.name,b.slug,b.sort_order ORDER BY b.sort_order,b.name",
        )
        ->fetchAll();
    page_start('Engineering branches', 'Browse free resource previews by engineering branch.');
    rl_assets();
    ?><section class="section">
<div class="container">
<span class="eyebrow">Your resource library</span>
<h1 class="rl-heading">Browse by branch</h1>
<div class="resource-grid"><?php foreach (
    $rows
    as $r
): ?><a class="resource-card" href="/branch/<?= e($r['slug']) ?>">
<h3><?= e(
    $r['name'],
) ?></h3>
<p class="muted"><?= (int) $r[
    'material_count'
] ?> published resources</p>
<span class="card-link">Explore resources &rarr;</span>
</a><?php endforeach; ?></div>
</div>
</section><?php page_end();
}
