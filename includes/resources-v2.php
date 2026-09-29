<?php
declare(strict_types=1);
if (!defined('ENOUGHEDU_PUBLIC_ROOT')) {
    http_response_code(404);
    exit();
}
require_once (isset($privateRoot) ? $privateRoot : dirname(__DIR__)) . '/app/resource-library.php';
if (!rl_ready()) {
    // Keep the existing library available until the additive migration is imported.
    require __DIR__ . '/resources-v2-legacy.php';
} else {
    function material_branch(string $slug): ?array
    {
        if ($slug === 'ai-and-machine-learning') {
            $slug = 'ai-ml';
        }
        $q = db()->prepare(
            'SELECT id,name,slug FROM branches WHERE slug=? AND status="active" LIMIT 1',
        );
        $q->execute([$slug]);
        return $q->fetch() ?: null;
    }
    function material_access_label(array $item): string
    {
        return 'FREE PREVIEW';
    }
    function admin_materials_page(): void
    {
        rl_admin();
    }
    function save_material(): never
    {
        rl_save();
    }
    function delete_material(): never
    {
        require_admin();
        verify_csrf();
        flash(
            'error',
            'Use Edit and set visibility to Archived to preserve resource purchase history.',
        );
        redirect('/admin/resources');
    }
    function branch_materials_page(string $slug): void
    {
        $branch = material_branch($slug);
        if (!$branch) {
            not_found();
            return;
        }
        rl_list($branch);
    }
    function dynamic_resources_page(): void
    {
        rl_list();
    }
    function open_material(): never
    {
        rl_view();
    }
}
