<?php
declare(strict_types=1);

$configFile = dirname(__DIR__) . '/config/config.php';
if (!is_file($configFile)) {
    http_response_code(503);
    exit('Site setup is incomplete. Create config/config.php using the example configuration.');
}
$config = require $configFile;
date_default_timezone_set($config['app']['timezone'] ?? 'Asia/Kolkata');
ini_set('session.use_strict_mode', '1');
ini_set('session.use_only_cookies', '1');
session_name($config['security']['session_name'] ?? 'enoughedu_session');
$sessionLifetime = (int) ($config['security']['session_lifetime'] ?? 2592000);
ini_set('session.gc_maxlifetime', (string) $sessionLifetime);
session_set_cookie_params([
    'lifetime' => $sessionLifetime,
    'path' => '/',
    'secure' => (bool) ($config['security']['cookie_secure'] ?? true),
    'httponly' => true,
    'samesite' => 'Lax',
]);
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

function config(string $key, mixed $default = null): mixed
{
    global $config;
    $v = $config;
    foreach (explode('.', $key) as $part) {
        if (!is_array($v) || !array_key_exists($part, $v)) {
            return $default;
        }
        $v = $v[$part];
    }
    return $v;
}
function public_path(string $path = ''): string
{
    $root = defined('ENOUGHEDU_PUBLIC_ROOT') ? (string) ENOUGHEDU_PUBLIC_ROOT : dirname(__DIR__);
    return rtrim($root, '/\\') . ($path !== '' ? '/' . ltrim($path, '/\\') : '');
}
function db(): ?PDO
{
    static $pdo = false;
    if ($pdo !== false) {
        return $pdo;
    }
    try {
        $d = config('database');
        $dsn = "mysql:host={$d['host']};port={$d['port']};dbname={$d['name']};charset={$d['charset']}";
        $pdo = new PDO($dsn, $d['user'], $d['password'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::ATTR_TIMEOUT => 2,
        ]);
    } catch (Throwable $e) {
        $pdo = null;
        if (config('app.debug')) {
            error_log($e->getMessage());
        }
    }
    return $pdo;
}
function e(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
function url(string $path = ''): string
{
    return rtrim((string) config('app.url'), '\/') . '/' . ltrim($path, '/');
}
function redirect(string $to): never
{
    header('Location: ' . $to);
    exit();
}
function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}
function csrf_field(): string
{
    return '<input type="hidden" name="csrf" value="' . e(csrf_token()) . '">';
}
function verify_csrf(): void
{
    if (!hash_equals($_SESSION['csrf'] ?? '', (string) ($_POST['csrf'] ?? ''))) {
        http_response_code(419);
        exit('Your session expired. Please refresh and try again.');
    }
}
function flash(string $type, string $message): void
{
    $_SESSION['flash'][] = [$type, $message];
}
function flashes(): array
{
    $f = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $f;
}
function user(): ?array
{
    if (empty($_SESSION['user_id'])) {
        return null;
    }
    $pdo = db();
    if (!$pdo) {
        return null;
    }
    $q = $pdo->prepare('SELECT * FROM users WHERE id=? AND status="active" LIMIT 1');
    $q->execute([$_SESSION['user_id']]);
    $record = $q->fetch() ?: null;
    if (!$record) {
        unset($_SESSION['user_id']);
    }
    return $record;
}
function require_auth(): array
{
    $u = user();
    if (!$u) {
        flash('error', 'Please log in to continue.');
        redirect('/login');
    }
    return $u;
}
function require_admin(): array
{
    $u = require_auth();
    if (($u['role'] ?? '') !== 'admin') {
        http_response_code(403);
        exit('Access denied');
    }
    return $u;
}
function is_post(): bool
{
    return ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST';
}
function slug(string $text): string
{
    $text = strtolower(trim($text));
    $text = preg_replace('/[^a-z0-9]+/', '-', $text);
    return trim((string) $text, '-');
}
function text_limit(string $text, int $length): string
{
    return function_exists('mb_substr')
        ? mb_substr($text, 0, $length, 'UTF-8')
        : substr($text, 0, $length);
}
function json_ld(array $data): string
{
    return '<script type="application/ld+json">' .
        json_encode(
            $data,
            JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP,
        ) .
        '</script>';
}
function setting(string $key, mixed $default = null): mixed
{
    static $loaded = false,
        $settings = [];
    if (!$loaded) {
        $loaded = true;
        $pdo = db();
        if ($pdo) {
            try {
                foreach (
                    $pdo->query('SELECT setting_key,setting_value FROM settings')->fetchAll()
                    as $row
                ) {
                    $settings[(string) $row['setting_key']] = $row['setting_value'];
                }
            } catch (Throwable) {
                $settings = [];
            }
        }
    }
    return array_key_exists($key, $settings) ? $settings[$key] : $default;
}

function send_email(string $to, string $subject, string $template, array $data = []): bool
{
    $file = dirname(__DIR__) . '/app/emails/' . $template . '.php';
    if (!is_file($file)) {
        return false;
    }
    extract($data, EXTR_SKIP);
    ob_start();
    include $file;
    $body = ob_get_clean();
    $headers = [
        'MIME-Version: 1.0',
        'Content-Type: text/html; charset=UTF-8',
        'From: ' . config('mail.from_name') . ' <' . config('mail.from_email') . '>',
        'Reply-To: ' . config('mail.reply_to'),
    ];
    return mail($to, $subject, $body, implode("\r\n", $headers));
}

function razorpay_ready(): bool
{
    $keyId = trim((string) config('razorpay.key_id'));
    $secret = trim((string) config('razorpay.key_secret'));
    return $keyId !== '' &&
        !str_starts_with($keyId, 'YOUR_') &&
        $secret !== '' &&
        !str_starts_with($secret, 'YOUR_');
}
function razorpay_api(string $method, string $path, ?array $payload = null): array
{
    if (!razorpay_ready()) {
        return [0, null, 'Razorpay API credentials are not configured.'];
    }
    if (!function_exists('curl_init')) {
        return [0, null, 'PHP cURL is not enabled.'];
    }
    $base = rtrim((string) config('razorpay.api_base', 'https://api.razorpay.com/v1'), '/');
    $ch = curl_init($base . '/' . ltrim($path, '/'));
    $headers = ['Accept: application/json'];
    $options = [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => (int) config('razorpay.timeout', 25),
        CURLOPT_CUSTOMREQUEST => strtoupper($method),
        CURLOPT_USERPWD =>
            (string) config('razorpay.key_id') . ':' . (string) config('razorpay.key_secret'),
        CURLOPT_HTTPAUTH => CURLAUTH_BASIC,
        CURLOPT_HTTPHEADER => $headers,
    ];
    if ($payload !== null) {
        $body = json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
        $options[CURLOPT_POSTFIELDS] = $body;
        $options[CURLOPT_HTTPHEADER] = [
            'Accept: application/json',
            'Content-Type: application/json',
        ];
    }
    curl_setopt_array($ch, $options);
    $body = curl_exec($ch);
    $status = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    $data = is_string($body) ? json_decode($body, true) : null;
    if ($error === '' && $status >= 400 && is_array($data)) {
        $error =
            (string) ($data['error']['description'] ??
                ($data['error']['reason'] ?? 'Razorpay rejected the request.'));
    }
    return [$status, $data, $error];
}
function razorpay_verify_payment_signature(
    string $orderId,
    string $paymentId,
    string $signature,
): bool {
    $secret = trim((string) config('razorpay.key_secret'));
    if (
        $orderId === '' ||
        $paymentId === '' ||
        $signature === '' ||
        $secret === '' ||
        str_starts_with($secret, 'YOUR_')
    ) {
        return false;
    }
    return hash_equals(
        hash_hmac('sha256', $orderId . '|' . $paymentId, $secret),
        strtolower($signature),
    );
}
function razorpay_verify_webhook_signature(string $rawBody, string $signature): bool
{
    $secret = trim((string) config('razorpay.webhook_secret'));
    if (
        $rawBody === '' ||
        $signature === '' ||
        $secret === '' ||
        str_starts_with($secret, 'YOUR_')
    ) {
        return false;
    }
    return hash_equals(hash_hmac('sha256', $rawBody, $secret), strtolower($signature));
}
