<?php
/**
 * Conexión a la base de datos MySQL del sitio TUOI.
 *
 * Este archivo se incluye desde casi cualquier punto de la aplicación
 * (frontend público, panel admin, helpers de contenido) y deja disponible
 * la variable $conexion como recurso mysqli compartido.
 *
 * Si la conexión falla, NO se interrumpe la ejecución: en su lugar se
 * publica el error en $error_db para que la página que incluye este
 * archivo pueda mostrar un fallback amistoso en vez de una pantalla en blanco.
 *
 * TO-DO seguridad: mover credenciales a variables de entorno / archivo
 * fuera del repositorio antes de pasar a producción.
 */

// Credenciales de acceso a la base de datos.
$host     = "localhost";
$user     = "tuoi_admin2026";
$password = "***CREDENCIAL-ELIMINADA***";
$database = "tuoi_db";

$conexion = mysqli_connect($host, $user, $password, $database);

if (!$conexion) {
    // Fallo de conexión: guardamos el mensaje pero permitimos que la página
    // siga renderizando (los consumidores deben comprobar $conexion antes de usarla).
    $error_db = "Error de conexión: " . mysqli_connect_error();
} else {
    // utf8mb4 garantiza soporte completo de Unicode (emojis, acentos, etc.)
    // tanto en lectura como en escritura — imprescindible para textos en ES/EN.
    mysqli_set_charset($conexion, "utf8mb4");
}
?>