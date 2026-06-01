<?php
/**
 * Conexión a la base de datos MySQL del sitio TUOI.
 *
 * Las credenciales se cargan desde /etc/tuoi/db.php, que vive fuera
 * del webroot y del repositorio. Nunca edites credenciales aquí.
 *
 * Para desarrollo local: crea /etc/tuoi/db.php con tus credenciales.
 * Para producción: el sysadmin crea ese archivo en el servidor.
 */

$_db_config_path = '/etc/tuoi/db.php';

if (!file_exists($_db_config_path)) {
    $error_db = "Archivo de configuración de BD no encontrado ({$_db_config_path}). "
              . "Crea ese archivo con las credenciales correctas.";
    return;
}

$_db = require $_db_config_path;

$conexion = mysqli_connect(
    $_db['host'],
    $_db['user'],
    $_db['password'],
    $_db['database']
);

if (!$conexion) {
    $error_db = "Error de conexión: " . mysqli_connect_error();
} else {
    mysqli_set_charset($conexion, "utf8mb4");
}

unset($_db, $_db_config_path);
