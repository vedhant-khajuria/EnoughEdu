<?php
declare(strict_types=1);
if (!defined('ENOUGHEDU_PUBLIC_ROOT')) {
    http_response_code(404);
    exit();
}

function material_branch(string $slug): ?array
{
    if ($slug === 'ai-and-machine-learning') {
        $slug = 'ai-ml';
    }
    $pdo = db();
    if ($pdo) {
        $q = $pdo->prepare(
            'SELECT id,name,slug FROM branches WHERE slug=? AND status="active" LIMIT 1',
        );
        $q->execute([$slug]);
        if ($branch = $q->fetch()) {
            return [
                'id' => (int) $branch['id'],
                'name' => $branch['name'],
                'slug' => $branch['slug'],
                'icon' => '◫',
            ];
        }
    }
    foreach (BRANCHES as $index => $branch) {
        $legacy = strtolower(str_replace([' ', '&'], ['-', 'and'], $branch[0]));
        if ($branch[1] === $slug || $legacy === $slug) {
            return [
                'id' => $index + 1,
                'name' => $branch[0],
                'slug' => $branch[1],
                'icon' => $branch[2],
            ];
        }
    }
    return null;
}

function material_access_label(array $item): string
{
    if (empty($item['is_premium'])) {
        return 'FREE';
    }
    $price = (float) ($item['price'] ?? 0);
    return $price > 0
        ? '₹' . number_format($price, $price === (float) (int) $price ? 0 : 2)
        : 'PRO';
}

function admin_materials_page(): void
{
    header('Cache-Control: no-store, max-age=0');
    header('X-EnoughEdu-Resources-Version: 2');
    require_admin();
    $pdo = db();
    $editing = null;
    $rows = [];
    $branches = [];
    if (
        $pdo &&
        !empty($_GET['edit']) &&
        in_array($_GET['kind'] ?? '', ['note', 'resource'], true)
    ) {
        $table = $_GET['kind'] === 'note' ? 'notes' : 'resources';
        $q = $pdo->prepare("SELECT * FROM {$table} WHERE id=?");
        $q->execute([(int) $_GET['edit']]);
        $editing = $q->fetch() ?: null;
        if ($editing) {
            $editing['_kind'] = $_GET['kind'];
        }
    }
    if ($pdo) {
        $rows = $pdo
            ->query(
                "SELECT 'note' kind,n.id,n.title,n.subject,n.semester,n.note_type material_type,n.external_url,n.is_premium,n.price,n.status,b.name branch_name FROM notes n LEFT JOIN branches b ON b.id=n.branch_id UNION ALL SELECT 'resource',r.id,r.title,r.subject,r.semester,r.resource_type,r.external_url,r.is_premium,r.price,r.status,b.name FROM resources r LEFT JOIN branches b ON b.id=r.branch_id ORDER BY id DESC LIMIT 100",
            )
            ->fetchAll();
        $branches = $pdo
            ->query('SELECT id,name FROM branches WHERE status="active" ORDER BY sort_order,name')
            ->fetchAll();
    }
    app_start('Admin · Study materials', 'resources', true);
    ?>
    <section class="panel">
      <div class="panel-head">
<div>
<h3><?= $editing
          ? 'Edit study material'
          : 'Add branch study material' ?></h3>
<p class="muted">Choose its branch, then paste the exact link students should open.</p>
</div><?php if (
    $editing
): ?><a class="btn btn-secondary btn-sm" href="/admin/resources">Cancel edit</a><?php endif; ?></div>
      <?php if (
          !$pdo
      ): ?><div class="alert alert-error">Connect and import the MySQL database before adding materials.</div><?php endif; ?>
      <form method="post" action="/admin/resources/save" class="admin-material-form">
        <?= csrf_field() ?><input type="hidden" name="id" value="<?= e($editing['id'] ?? '') ?>">
        <div class="form-row">
<div class="field">
<label>Content type</label>
<select class="input" name="kind">
<option value="note" <?= ($editing[
            '_kind'
        ] ??
            '') ===
        'note'
            ? 'selected'
            : '' ?>>Note / formula / cheat sheet</option>
<option value="resource" <?= ($editing[
    '_kind'
] ??
    '') ===
'resource'
    ? 'selected'
    : '' ?>>Paper / manual / viva / project / other</option>
</select>
</div>
<div class="field">
<label>Title</label>
<input class="input" name="title" maxlength="220" value="<?= e(
    $editing['title'] ?? '',
) ?>" required placeholder="Compiler Design Unit 2 Notes">
</div>
</div>
        <div class="form-row">
<div class="field">
<label>Engineering branch</label>
<select class="input" name="branch_id" required>
<option value="">Choose branch</option><?php foreach (
            $branches
            as $branch
        ): ?><option value="<?= $branch['id'] ?>" <?= $branch['id'] ==
(int) ($editing['branch_id'] ?? 0)
    ? 'selected'
    : '' ?>><?= e($branch['name']) ?></option><?php endforeach; ?></select>
</div>
</div>
        <div class="form-row">
<div class="field">
<label>Subject</label>
<input class="input" name="subject" maxlength="160" value="<?= e(
            $editing['subject'] ?? '',
        ) ?>" required placeholder="Compiler Design">
</div>
<div class="field">
<label>Material category</label>
<select class="input" name="material_type">
<option value="semester">Study notes</option>
<option value="handwritten">Handwritten notes</option>
<option value="formula">Formula sheet</option>
<option value="cheatsheet">Cheat sheet</option>
<option value="paper">Previous-year paper</option>
<option value="lab_manual">Lab manual</option>
<option value="viva">Viva questions</option>
<option value="mini_project">Mini project</option>
<option value="final_project">Final-year project</option>
<option value="coding">Coding resource</option>
<option value="interview">Interview questions</option>
<option value="internship">Internship opportunity</option>
<option value="other">Other</option>
</select>
</div>
</div>
        <div class="field">
<label>Study material link</label>
<input class="input" type="url" name="external_url" value="<?= e(
            $editing['external_url'] ?? '',
        ) ?>" required placeholder="https://drive.google.com/... or https://your-source.com/...">
<small class="muted">HTTPS links only. Google Drive, Dropbox, PDFs, Docs and other learning URLs are supported.</small>
</div>
        <div class="field">
<label>Description</label>
<textarea class="input" name="description" rows="3" maxlength="1000" placeholder="What this material covers and why it is useful."><?= e(
            $editing['description'] ?? '',
        ) ?></textarea>
</div>
        <div class="form-row">
<div class="field">
<label>Visibility</label>
<select class="input" name="status">
<option value="published" <?= ($editing[
            'status'
        ] ??
            'published') ===
        'published'
            ? 'selected'
            : '' ?>>Published</option>
<option value="draft" <?= ($editing['status'] ?? '') ===
'draft'
    ? 'selected'
    : '' ?>>Draft</option>
<option value="archived" <?= ($editing['status'] ?? '') === 'archived'
    ? 'selected'
    : '' ?>>Archived</option>
</select>
</div>
<div class="field">
<label>Student access</label>
<select class="input" name="is_premium" data-material-access>
<option value="0" <?= empty(
    $editing['is_premium']
)
    ? 'selected'
    : '' ?>>Free — open without payment</option>
<option value="1" <?= !empty($editing['is_premium'])
    ? 'selected'
    : '' ?>>Paid — individual purchase or included plan</option>
</select>
</div>
</div>
        <div class="field" data-material-price-wrap>
<label>Individual price (₹)</label>
<input class="input" type="number" name="price" min="1" max="99999999" step="0.01" value="<?= e(
            (float) ($editing['price'] ?? 0) > 0
                ? number_format((float) $editing['price'], 2, '.', '')
                : '',
        ) ?>" placeholder="Example: 49">
<small class="muted">Required for Paid access. Set access to Free to publish this material at no cost.</small>
</div>
        <button class="btn btn-primary" <?= $pdo ? '' : 'disabled' ?>><?= $editing
    ? 'Update material'
    : 'Publish material' ?></button>
      </form>
      <script>document.addEventListener('DOMContentLoaded',()=>{const access=document.querySelector('[data-material-access]'),wrap=document.querySelector('[data-material-price-wrap]'),price=wrap?.querySelector('input');if(!access||!price)return;const sync=()=>{const paid=access.value==='1';wrap.hidden=!paid;price.required=paid;price.disabled=!paid;if(!paid)price.value=''};access.addEventListener('change',sync);sync()});</script>
    </section>
    <section class="panel" style="margin-top:18px">
<div class="panel-head">
<div>
<h3>Published library</h3>
<p class="muted">Every item is connected to its chosen branch.</p>
</div>
</div>
      <div class="table-wrap">
<table class="table">
<thead>
<tr>
<th>Material</th>
<th>Branch</th>
<th>Type</th>
<th>Access</th>
<th>Status</th>
<th>Actions</th>
</tr>
</thead>
<tbody>
      <?php foreach ($rows as $row): ?><tr>
<td>
<b><?= e(
    $row['title'],
) ?></b>
<small class="muted" style="display:block"><?= e($row['subject']) ?></small>
</td>
<td><?= e(
    $row['branch_name'] ?? 'All',
) ?></td>
<td><?= e(
    ucwords(str_replace('_', ' ', $row['material_type'])),
) ?></td>
<td>
<span class="badge <?= empty($row['is_premium']) ? '' : 'warn' ?>"><?= e(
    material_access_label($row),
) ?></span>
</td>
<td>
<span class="badge <?= $row['status'] === 'draft' ? 'warn' : '' ?>"><?= e(
    $row['status'],
) ?></span>
</td>
<td>
<div style="display:flex;gap:6px">
<a class="btn btn-secondary btn-sm" href="/admin/resources?edit=<?= $row[
    'id'
] ?>&kind=<?= $row[
    'kind'
] ?>">Edit</a>
<form method="post" action="/admin/resources/delete" onsubmit="return confirm('Delete this material?')"><?= csrf_field() ?><input type="hidden" name="id" value="<?= $row[
    'id'
] ?>">
<input type="hidden" name="kind" value="<?= $row[
    'kind'
] ?>">
<button class="btn btn-secondary btn-sm">Delete</button>
</form>
</div>
</td>
</tr><?php endforeach; ?>
      <?php if (
          !$rows
      ): ?><tr>
<td colspan="6" class="empty">No materials yet. Add your first link above.</td>
</tr><?php endif; ?></tbody>
</table>
</div>
    </section>
    <?php app_end();
}

function save_material(): never
{
    require_admin();
    verify_csrf();
    $pdo = db();
    if (!$pdo) {
        flash('error', 'Database unavailable.');
        redirect('/admin/resources');
    }
    $kind = in_array($_POST['kind'] ?? '', ['note', 'resource'], true) ? $_POST['kind'] : 'note';
    $id = (int) ($_POST['id'] ?? 0);
    $title = trim($_POST['title'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $branch = (int) ($_POST['branch_id'] ?? 0);
    $semester = null;
    $external = trim($_POST['external_url'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $premium = (int) !empty($_POST['is_premium']);
    $price = $premium ? round((float) ($_POST['price'] ?? 0), 2) : 0.0;
    $status = in_array($_POST['status'] ?? '', ['draft', 'published', 'archived'], true)
        ? $_POST['status']
        : 'draft';
    $branchValid = false;
    $branchCheck = $pdo->prepare('SELECT COUNT(*) FROM branches WHERE id=? AND status="active"');
    $branchCheck->execute([$branch]);
    $branchValid = (bool) $branchCheck->fetchColumn();
    if (
        !$title ||
        !$subject ||
        !$branchValid ||
        !filter_var($external, FILTER_VALIDATE_URL) ||
        parse_url($external, PHP_URL_SCHEME) !== 'https' ||
        ($premium && ($price < 1 || $price > 99999999))
    ) {
        flash(
            'error',
            'Complete every required field, choose a valid branch, use a valid HTTPS link, and enter a valid paid price.',
        );
        redirect('/admin/resources');
    }
    $slugValue =
        slug($title) . '-' . substr(hash('sha256', $kind . $branch . $semester . $external), 0, 8);
    if ($kind === 'note') {
        $allowed = ['semester', 'handwritten', 'formula', 'cheatsheet'];
        $type = in_array($_POST['material_type'] ?? '', $allowed, true)
            ? $_POST['material_type']
            : 'semester';
        if ($id) {
            $q = $pdo->prepare(
                'UPDATE notes SET title=?,slug=?,branch_id=?,subject=?,note_type=?,description=?,external_url=?,is_premium=?,price=?,status=? WHERE id=?',
            );
            $q->execute([
                $title,
                $slugValue,
                $branch,
                $subject,
                $type,
                $description,
                $external,
                $premium,
                $price,
                $status,
                $id,
            ]);
        } else {
            $q = $pdo->prepare(
                'INSERT INTO notes(title,slug,branch_id,semester,subject,note_type,description,external_url,is_premium,price,status,uploaded_by) VALUES(?,?,?,?,?,?,?,?,?,?,?,?)',
            );
            $q->execute([
                $title,
                $slugValue,
                $branch,
                $semester,
                $subject,
                $type,
                $description,
                $external,
                $premium,
                $price,
                $status,
                user()['id'],
            ]);
        }
    } else {
        $allowed = [
            'paper',
            'lab_manual',
            'viva',
            'mini_project',
            'final_project',
            'coding',
            'interview',
            'internship',
            'other',
        ];
        $type = in_array($_POST['material_type'] ?? '', $allowed, true)
            ? $_POST['material_type']
            : 'other';
        if ($id) {
            $q = $pdo->prepare(
                'UPDATE resources SET title=?,slug=?,branch_id=?,subject=?,resource_type=?,description=?,external_url=?,is_premium=?,price=?,status=? WHERE id=?',
            );
            $q->execute([
                $title,
                $slugValue,
                $branch,
                $subject,
                $type,
                $description,
                $external,
                $premium,
                $price,
                $status,
                $id,
            ]);
        } else {
            $q = $pdo->prepare(
                'INSERT INTO resources(title,slug,branch_id,semester,subject,resource_type,description,external_url,is_premium,price,status) VALUES(?,?,?,?,?,?,?,?,?,?,?)',
            );
            $q->execute([
                $title,
                $slugValue,
                $branch,
                $semester,
                $subject,
                $type,
                $description,
                $external,
                $premium,
                $price,
                $status,
            ]);
        }
    }
    flash('success', $id ? 'Material updated.' : 'Material published to the selected branch.');
    redirect('/admin/resources');
}

function delete_material(): never
{
    require_admin();
    verify_csrf();
    $pdo = db();
    $kind = $_POST['kind'] ?? '';
    $id = (int) ($_POST['id'] ?? 0);
    if ($pdo && $id && in_array($kind, ['note', 'resource'], true)) {
        $owned = $pdo->prepare(
            'SELECT COUNT(*) FROM user_materials WHERE material_type=? AND material_id=?',
        );
        $owned->execute([$kind, $id]);
        if ($owned->fetchColumn()) {
            flash(
                'error',
                'This paid material has purchase history and cannot be deleted. Set it to Archived instead.',
            );
            redirect('/admin/resources');
        }
        $table = $kind === 'note' ? 'notes' : 'resources';
        $q = $pdo->prepare("DELETE FROM {$table} WHERE id=?");
        $q->execute([$id]);
        flash('success', 'Material deleted.');
    }
    redirect('/admin/resources');
}

function branch_materials_page(string $slug): void
{
    $branch = material_branch($slug);
    if (!$branch) {
        not_found();
        return;
    }
    if ($slug !== $branch['slug']) {
        redirect(
            '/branch/' .
                $branch['slug'] .
                (!empty($_GET['semester']) ? '?semester=' . (int) $_GET['semester'] : ''),
        );
    }
    $semester = max(1, min(8, (int) ($_GET['semester'] ?? 1)));
    $pdo = db();
    $materials = [];
    if ($pdo) {
        $sql =
            "SELECT * FROM (SELECT 'note' kind,n.id,n.title,n.slug,n.subject,n.semester,n.note_type material_type,n.description,n.external_url,n.is_premium,n.price,n.created_at FROM notes n WHERE n.status='published' AND n.branch_id=? AND (n.semester=? OR n.semester IS NULL) UNION ALL SELECT 'resource',r.id,r.title,r.slug,r.subject,r.semester,r.resource_type,r.description,r.external_url,r.is_premium,r.price,r.created_at FROM resources r WHERE r.status='published' AND r.branch_id=? AND (r.semester=? OR r.semester IS NULL)) m ORDER BY created_at DESC";
        $q = $pdo->prepare($sql);
        $q->execute([$branch['id'], $semester, $branch['id'], $semester]);
        $materials = $q->fetchAll();
    }
    page_start(
        $branch['name'] . ' Semester ' . $semester . ' Resources',
        $branch['name'] .
            ' semester ' .
            $semester .
            ' notes, papers, lab manuals, formulas and study material.',
    );
    echo json_ld([
        '@context' => 'https://schema.org',
        '@type' => 'CollectionPage',
        'name' => $branch['name'] . ' Semester ' . $semester . ' Resources',
        'url' => url('/branch/' . $branch['slug']),
        'isPartOf' => ['@type' => 'WebSite', 'name' => 'EnoughEdu', 'url' => url('/')],
        'breadcrumb' => [
            '@type' => 'BreadcrumbList',
            'itemListElement' => [
                ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => url('/')],
                [
                    '@type' => 'ListItem',
                    'position' => 2,
                    'name' => 'Branches',
                    'item' => url('/branches'),
                ],
                [
                    '@type' => 'ListItem',
                    'position' => 3,
                    'name' => $branch['name'],
                    'item' => url('/branch/' . $branch['slug']),
                ],
            ],
        ],
    ]);
    ?>
    <section class="section" style="padding-bottom:30px">
<div class="container">
<span class="eyebrow"><?= e(
        $branch['name'],
    ) ?></span>
<h1 style="font-size:52px;margin:18px 0">Semester <?= $semester ?> study library</h1>
<p class="muted">Notes, papers, lab manuals, projects and formula sheets added by EnoughEdu.</p>
<div class="tabs"><?php for ($i = 1; $i <= 8; $i++): ?><a class="tab <?= $i === $semester ? 'active' : '' ?>" href="/branch/<?= e($slug) ?>?semester=<?= $i ?>">Semester <?= $i ?></a><?php endfor; ?></div>
</div>
</section>
    <section class="container">
<div class="panel-head">
<h2><?= count(
        $materials,
    ) ?> published <?= count($materials) === 1 ? 'material' : 'materials' ?></h2>
<a class="btn btn-secondary btn-sm" href="/resources?branch=<?= e($branch['slug']) ?>">Search full library</a>
</div>
<div class="resource-grid"><?php
foreach ($materials as $item): ?><article class="resource-card">
<div class="card-icon"><?= $item[
    'kind'
] === 'note'
    ? '◫'
    : '▤' ?></div>
<div class="card-meta">
<span class="tag"><?= e(
    ucwords(str_replace('_', ' ', $item['material_type'])),
) ?></span>
<span class="tag"><?= e(
    material_access_label($item),
) ?></span>
</div>
<h3 style="margin-top:15px"><?= e($item['title']) ?></h3>
<p class="muted"><?= e(
    $item['subject'],
) ?> · <?= e(
     $item['description'] ?: 'Curated study material for this semester.',
 ) ?></p>
<a class="btn btn-primary btn-sm" href="/material/open?kind=<?= e(
    $item['kind'],
) ?>&id=<?= (int) $item['id'] ?>"><?= !empty($item['is_premium']) && (float) $item['price'] > 0
    ? 'Buy or open'
    : 'Open material' ?> →</a>
</article><?php endforeach;
if (
    !$materials
): ?><div class="panel empty" style="grid-column:1/-1">
<h3>No published material for this semester yet</h3>
<p>This page will update automatically when the EnoughEdu content team publishes a new link.</p>
<a class="btn btn-secondary btn-sm" href="/resources">Browse all published resources</a>
</div><?php endif;
?></div>
</section>
    <?php page_end();
}

function dynamic_resources_page(): void
{
    header('Cache-Control: no-store, max-age=0');
    header('X-EnoughEdu-Resources-Version: 2');
    $pdo = db();
    $branchSlug = trim((string) ($_GET['branch'] ?? ''));
    $query = text_limit(trim((string) ($_GET['q'] ?? '')), 120);
    $materials = [];
    $branches = [];
    if ($pdo) {
        $branches = $pdo
            ->query(
                "SELECT b.id,b.name,b.slug,COUNT(m.id) material_count FROM branches b LEFT JOIN (SELECT id,branch_id FROM notes WHERE status='published' AND external_url IS NOT NULL UNION ALL SELECT id,branch_id FROM resources WHERE status='published' AND external_url IS NOT NULL) m ON m.branch_id=b.id WHERE b.status='active' GROUP BY b.id,b.name,b.slug,b.sort_order ORDER BY b.sort_order,b.name",
            )
            ->fetchAll();
        $branch = null;
        if ($branchSlug !== '') {
            foreach ($branches as $candidate) {
                if (hash_equals((string) $candidate['slug'], $branchSlug)) {
                    $branch = $candidate;
                    break;
                }
            }
        }
        if ($branchSlug !== '' && !$branch) {
            $branchSlug = '';
        }
        $where = ["x.status='published'", 'x.external_url IS NOT NULL', "b.status='active'"];
        $params = [];
        if ($branch) {
            $where[] = 'x.branch_id=?';
            $params[] = $branch['id'];
        }
        if ($query !== '') {
            $where[] = '(x.title LIKE ? OR x.subject LIKE ? OR x.description LIKE ?)';
            $like = '%' . $query . '%';
            array_push($params, $like, $like, $like);
        }
        $w = implode(' AND ', $where);
        $sql = "SELECT x.*,b.name branch_name,b.slug branch_slug FROM (SELECT 'note' kind,n.id,n.title,n.slug,n.branch_id,n.semester,n.subject,n.note_type material_type,n.description,n.external_url,n.is_premium,n.price,n.status,n.created_at FROM notes n UNION ALL SELECT 'resource',r.id,r.title,r.slug,r.branch_id,r.semester,r.subject,r.resource_type,r.description,r.external_url,r.is_premium,r.price,r.status,r.created_at FROM resources r) x JOIN branches b ON b.id=x.branch_id WHERE {$w} ORDER BY x.created_at DESC LIMIT 100";
        $q = $pdo->prepare($sql);
        $q->execute($params);
        $materials = $q->fetchAll();
    }
    if (!$branches) {
        foreach (BRANCHES as $b) {
            $branches[] = ['id' => 0, 'name' => $b[0], 'slug' => $b[1], 'material_count' => 0];
        }
    }
    $filtered = $branchSlug !== '' || $query !== '';
    page_start(
        'Engineering Study Resource Library',
        'Search engineering notes, previous papers, lab manuals, viva questions, projects and formula sheets.',
    );
    echo json_ld([
        '@context' => 'https://schema.org',
        '@type' => 'CollectionPage',
        'name' => 'EnoughEdu Engineering Study Resource Library',
        'url' => url('/resources'),
        'isPartOf' => ['@type' => 'WebSite', 'name' => 'EnoughEdu', 'url' => url('/')],
    ]);
    ?>
    <section class="section">
<div class="container">
<div class="section-head">
<span class="eyebrow">Branch resource library</span>
<h1 style="font-size:52px;margin:18px">Find the right material, <span class="gradient-text">fast</span>
</h1>
<p>Only published links appear here. Search by title, subject or topic, and choose a branch.</p>
</div>
<form class="panel resource-search" method="get" action="/resources">
<label class="sr-only" for="resource-query">Search resources</label>
<input class="input" id="resource-query" name="q" value="<?= e(
        $query,
    ) ?>" placeholder="Search title, subject or topic…">
<label class="sr-only" for="resource-branch">Engineering branch</label>
<select class="input" id="resource-branch" name="branch">
<option value="">All branches</option><?php foreach (
    $branches
    as $b
): ?><option value="<?= e($b['slug']) ?>" <?= $branchSlug === $b['slug'] ? 'selected' : '' ?>><?= e(
    $b['name'],
) ?></option><?php endforeach; ?></select>
<button class="btn btn-primary">Search</button>
</form>
<div class="panel-head resource-results-head">
<h2><?= count(
    $materials,
) ?> <?= count($materials) === 1 ? 'result' : 'results' ?></h2><?php if (
    $filtered
): ?><a class="card-link" href="/resources">Clear filters</a><?php endif; ?></div>
<div class="resource-grid"><?php
foreach (
    $materials
    as $item
): ?><article class="resource-card">
<div class="card-meta">
<span class="tag"><?= e(
    ucwords(str_replace('_', ' ', $item['material_type'])),
) ?></span>
<span class="tag"><?= e(
    material_access_label($item),
) ?></span>
</div>
<h3 style="margin-top:15px"><?= e($item['title']) ?></h3>
<p class="muted"><?= e(
    $item['subject'],
) ?> · <?= e($item['branch_name']) ?></p><?php if ($item['description']): ?><p><?= e(
    $item['description'],
) ?></p><?php endif; ?><a class="card-link" href="/material/open?kind=<?= e(
    $item['kind'],
) ?>&id=<?= (int) $item['id'] ?>"><?= !empty($item['is_premium']) && (float) $item['price'] > 0
    ? 'Buy or open'
    : 'Open material' ?> →</a>
</article><?php endforeach;
if (!$materials): ?><div class="panel empty" style="grid-column:1/-1">
<h3><?= $filtered
    ? 'No material matches these filters'
    : 'The resource library is ready' ?></h3>
<p><?= $filtered
    ? 'Clear one or more filters and search again.'
    : 'Published notes and study links will appear here as they are added by the EnoughEdu team.' ?></p><?php if (
    $filtered
): ?><a class="btn btn-secondary btn-sm" href="/resources">Show all resources</a><?php endif; ?></div><?php endif;
?></div>
<div class="panel-head branch-browser-head">
<div>
<h2>Browse by branch</h2>
<p class="muted">Choose a branch to see its published study materials.</p>
</div>
</div>
<div class="branch-chip-grid"><?php foreach (
    $branches
    as $b
): ?><a class="branch-chip" href="/resources?branch=<?= e($b['slug']) ?>">
<span>
<b><?= e(
    $b['name'],
) ?></b>
<small><?= (int) $b[
    'material_count'
] ?> published</small>
</span>
<i>→</i>
</a><?php endforeach; ?></div>
</div>
</section>
    <?php page_end();
}

function open_material(): never
{
    $pdo = db();
    $kind = $_GET['kind'] ?? '';
    $id = (int) ($_GET['id'] ?? 0);
    if (!$pdo || !in_array($kind, ['note', 'resource'], true) || $id < 1) {
        http_response_code(404);
        exit('Material not found.');
    }
    $table = $kind === 'note' ? 'notes' : 'resources';
    $q = $pdo->prepare(
        "SELECT id,branch_id,semester,external_url,is_premium,price,status FROM {$table} WHERE id=? LIMIT 1",
    );
    $q->execute([$id]);
    $item = $q->fetch();
    if (
        !$item ||
        $item['status'] !== 'published' ||
        !filter_var($item['external_url'], FILTER_VALIDATE_URL)
    ) {
        http_response_code(404);
        exit('Material not found.');
    }
    $u = user();
    if ($item['is_premium']) {
        $u = require_auth();
        $allowed = ($u['role'] ?? 'student') === 'admin';
        if (!$allowed) {
            $owned = $pdo->prepare(
                'SELECT COUNT(*) FROM user_materials WHERE user_id=? AND material_type=? AND material_id=?',
            );
            $owned->execute([$u['id'], $kind, $id]);
            $allowed = (bool) $owned->fetchColumn();
        }
        if (!$allowed) {
            $access = $pdo->prepare(
                'SELECT s.scope_type,p.limits_json FROM subscriptions s LEFT JOIN plans p ON p.id=s.plan_id WHERE s.user_id=? AND s.status="active" AND s.starts_at<=NOW() AND (s.expires_at IS NULL OR s.expires_at>NOW())',
            );
            $access->execute([$u['id']]);
            foreach ($access->fetchAll() as $subscription) {
                $contents = plan_contents($subscription['limits_json'] ?? null);
                if ($contents !== null) {
                    if (plan_includes($contents, $kind === 'note' ? 'notes' : 'resources', $id)) {
                        $allowed = true;
                        break;
                    }
                    continue;
                }
                $scope = $subscription['scope_type'];
                if (
                    $scope === 'degree' ||
                    ($scope === 'branch' &&
                        (int) ($u['branch_id'] ?? 0) === (int) $item['branch_id']) ||
                    ($scope === 'semester' &&
                        (int) ($u['branch_id'] ?? 0) === (int) $item['branch_id'] &&
                        ($item['semester'] === null ||
                            (int) ($u['semester'] ?? 0) === (int) $item['semester']))
                ) {
                    $allowed = true;
                    break;
                }
            }
        }
        if (!$allowed) {
            if ((float) $item['price'] > 0) {
                flash(
                    'error',
                    'Purchase this material once, or choose an included plan, to open it.',
                );
                redirect('/checkout?material_kind=' . rawurlencode($kind) . '&material_id=' . $id);
            }
            flash(
                'error',
                'This material is included with an eligible plan. Compare plans to unlock it.',
            );
            redirect('/pricing');
        }
    }
    if ($u) {
        $pdo->prepare(
            'INSERT INTO downloads(user_id,item_type,item_id,ip_address) VALUES(?,?,?,?)',
        )->execute([$u['id'], $kind, $id, $_SERVER['REMOTE_ADDR'] ?? null]);
        $pdo->prepare("UPDATE {$table} SET download_count=download_count+1 WHERE id=?")->execute([
            $id,
        ]);
    }
    header('Location: ' . $item['external_url'], true, 302);
    exit();
}
