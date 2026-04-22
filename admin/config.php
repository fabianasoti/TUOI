<?php
session_start();

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

require_once dirname(__DIR__) . '/config/conexion.php';

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
