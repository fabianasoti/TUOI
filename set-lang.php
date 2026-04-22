<?php
$lang = in_array($_GET['lang'] ?? '', ['es', 'en']) ? $_GET['lang'] : 'es';
setcookie('tuoi_lang', $lang, time() + 60 * 60 * 24 * 365, '/');

// Redirect back to the referring page (same origin only)
$back   = $_SERVER['HTTP_REFERER'] ?? '/';
$parsed = parse_url($back);
$host   = $_SERVER['HTTP_HOST'] ?? '';
if (!empty($parsed['host']) && $parsed['host'] !== $host) {
    $back = '/';
}
header('Location: ' . $back);
exit;
