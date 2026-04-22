<?php
/**
 * Migración: crea la tabla image_order.
 * Ejecuta UNA VEZ y luego elimina este archivo.
 */
require_once dirname(__DIR__) . '/config/conexion.php';

$sql = "CREATE TABLE IF NOT EXISTS image_order (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    section    VARCHAR(100) NOT NULL,
    filename   VARCHAR(255) NOT NULL,
    sort_order INT NOT NULL DEFAULT 0,
    UNIQUE KEY uk_section_file (section, filename)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

if (mysqli_query($conexion, $sql)) {
    echo '<p style="font-family:sans-serif;color:green;padding:20px">
          ✅ Tabla <strong>image_order</strong> creada correctamente.<br>
          <strong>Elimina este archivo ahora.</strong></p>';
} else {
    echo '<p style="font-family:sans-serif;color:red;padding:20px">
          ❌ Error: ' . htmlspecialchars(mysqli_error($conexion)) . '</p>';
}
