<?php
// conexion.php
$host = "localhost";
$user = "tuoi_admin2026"; // 
$password = "Tuoi123$"; // 
$database = "tuoi_db";

$conexion = mysqli_connect($host, $user, $password, $database);

if (!$conexion) {
    // Si falla la conexión, mostramos el error pero no matamos la página entera
    $error_db = "Error de conexión: " . mysqli_connect_error();
} else {
    mysqli_set_charset($conexion, "utf8");
}
?>