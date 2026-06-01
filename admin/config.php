<?php
// Cookie de sesión endurecida — debe ir antes de session_start().
session_set_cookie_params([
    'lifetime' => 0,
    'path'     => '/',
    'httponly' => true,
    'samesite' => 'Lax',
    'secure'   => !empty($_SERVER['HTTPS']),
]);
session_start();

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

require_once dirname(__DIR__) . '/config/conexion.php';

// ── CSRF ────────────────────────────────────────────────────────────
function csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field() {
    return '<input type="hidden" name="csrf_token" value="'
        . htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') . '">';
}

function csrf_check() {
    $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    if (!is_string($token) || !hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
        http_response_code(403);
        exit('CSRF token inválido. Recarga la página e inténtalo de nuevo.');
    }
}

// Verificación automática en cada POST a una página admin.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
}

// ── Rol del usuario en sesión ────────────────────────────────────────
function is_admin(): bool {
    return ($_SESSION['admin_role'] ?? '') === 'admin';
}

function require_admin(): void {
    if (!is_admin()) {
        http_response_code(403);
        exit('No tienes permisos para realizar esta acción.');
    }
}

function admin_escape($conexion, $value) {
    return mysqli_real_escape_string($conexion, trim($value));
}

function upsert_content($conexion, $key, $value) {
    $k = mysqli_real_escape_string($conexion, $key);
    $v = mysqli_real_escape_string($conexion, $value);
    return mysqli_query($conexion,
        "INSERT INTO site_content (content_key, content_value)
         VALUES ('$k', '$v')
         ON DUPLICATE KEY UPDATE content_value = '$v', updated_at = NOW()"
    );
}
