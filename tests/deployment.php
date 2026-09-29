<?php
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit();
}

require dirname(__DIR__) . '/app/bootstrap.php';
require dirname(__DIR__) . '/app/resource-library.php';

function check(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

check(realpath(public_path()) === dirname(__DIR__), 'Public root must be the unified directory.');
check(url('/contact') === 'https://enoughedu.vedhant.in/contact', 'Incorrect canonical domain.');
check(config('mail.reply_to') === 'hello@vedhant.in', 'Incorrect support address.');

foreach (['../config/config.php', '../../outside.pdf', '/absolute.pdf', 'missing.pdf'] as $name) {
    $rejected = false;
    try {
        rl_pdf($name);
    } catch (DomainException) {
        $rejected = true;
    }
    check($rejected, 'Resource path was not rejected: ' . $name);
}

$files = glob(rl_root() . '/*.pdf') ?: [];
foreach ($files as $file) {
    check(rl_pdf(basename($file)) === realpath($file), 'A valid private PDF was rejected.');
    [$key, $pages] = rl_preview($file);
    check($key === hash_file('sha256', $file) && $pages > 0, 'Invalid preview manifest.');
}

echo 'Path confinement and configuration checks passed. Verified ' . count($files) . " PDFs.\n";
