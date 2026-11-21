<?php
/**
 * Carga común: configuración, conexión PDO, sesión, seguridad y utilidades.
 */
declare(strict_types=1);

date_default_timezone_set('America/Lima');
$CONFIG = require __DIR__ . '/../config.php';
// En un hosting, config.local.php (no se sube a GitHub) reemplaza los datos de conexión.
if (is_file(__DIR__ . '/../config.local.php')) {
    $CONFIG = array_replace_recursive($CONFIG, require __DIR__ . '/../config.local.php');
}

if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params(['httponly' => true, 'samesite' => 'Lax']);
    session_start();
}

/** Conexión única a MySQL mediante PDO con consultas preparadas. */
function db(): PDO
{
    static $pdo = null;
    global $CONFIG;
    if ($pdo === null) {
        $c = $CONFIG['db'];
        $dsn = "mysql:host={$c['host']};port={$c['port']};dbname={$c['name']};charset=utf8mb4";
        $pdo = new PDO($dsn, $c['user'], $c['pass'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
        $pdo->exec("SET time_zone = '-05:00'");
    }
    return $pdo;
}

function cfg(string $key)
{
    global $CONFIG;
    $v = $CONFIG;
    foreach (explode('.', $key) as $k) {
        $v = $v[$k] ?? null;
    }
    return $v;
}

/** Ruta de un archivo estático con versión, para que el navegador no use una copia vieja. */
function asset(string $path): string
{
    $file = __DIR__ . '/../' . $path;
    return $path . (is_file($file) ? '?v=' . filemtime($file) : '');
}

/** Escapa texto para HTML. */
function e($v): string
{
    return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
}

function soles($n): string
{
    return 'S/ ' . number_format((float)$n, 2, '.', ',');
}

function redirect(string $url): void
{
    header('Location: ' . $url);
    exit;
}

function flash(?string $msg = null, string $type = 'ok')
{
    if ($msg !== null) {
        $_SESSION['flash'] = ['msg' => $msg, 'type' => $type];
        return null;
    }
    $f = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $f;
}

// ---------------- Autenticación y roles (RNF3) ----------------
function user(): ?array
{
    return $_SESSION['user'] ?? null;
}

function require_login(): array
{
    $u = user();
    if (!$u) {
        redirect('login.php');
    }
    return $u;
}

function is_admin(): bool
{
    return (user()['rol'] ?? '') === 'administrador';
}

function require_admin(): array
{
    $u = require_login();
    if ($u['rol'] !== 'administrador') {
        http_response_code(403);
        flash('Esta sección es solo para el administrador.', 'error');
        redirect('dashboard.php');
    }
    return $u;
}

// ---------------- Protección CSRF ----------------
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

function check_csrf(): void
{
    $sent = $_POST['csrf'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
    if (!is_string($sent) || !hash_equals(csrf_token(), $sent)) {
        http_response_code(400);
        exit('Solicitud no válida. Recargue la página e intente de nuevo.');
    }
}

function post_str(string $k): string
{
    return trim((string)($_POST[$k] ?? ''));
}

function post_int(string $k): int
{
    return (int)($_POST[$k] ?? 0);
}

function json_out($data, int $code = 200): void
{
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

const CANALES = ['Local', 'WhatsApp', 'Facebook', 'TikTok'];
