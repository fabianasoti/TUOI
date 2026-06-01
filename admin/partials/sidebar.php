<?php
/**
 * Sidebar de navegación del panel admin.
 *
 * Se incluye desde cada página del admin después de abrir el wrapper del
 * layout. Resalta el ítem activo según el archivo PHP actual y muestra
 * un badge rojo con el número de mensajes de contacto sin leer.
 *
 * Variable opcional disponible en la página padre:
 *   $conexion → recurso mysqli (si está, calculamos el badge de mensajes).
 */

// Nombre del archivo PHP que estamos sirviendo. Se usa para marcar el
// enlace activo en la nav (comparando con 'index.php', 'eventos.php', etc.).
$current_admin_page = basename($_SERVER['PHP_SELF']);

// Cuenta de mensajes de contacto sin leer (badge rojo en el ítem "Mensajes").
$unread_msgs = 0;
if (isset($conexion) && $conexion) {
    // Auto-migración defensiva: si la columna is_read aún no existe en
    // contact_submissions (instalaciones antiguas), la creamos al vuelo.
    // El @ silencia el warning de "Duplicate column" cuando ya existe,
    // que es el caso normal en una BD ya migrada.
    try {
        @mysqli_query($conexion, "ALTER TABLE contact_submissions ADD COLUMN is_read TINYINT(1) NOT NULL DEFAULT 0 AFTER message");
    } catch (\Throwable $e) { /* tabla aún no creada o columna ya existe — ignoramos */ }
    try {
        $r = mysqli_query($conexion, "SELECT COUNT(*) AS c FROM contact_submissions WHERE is_read = 0");
        if ($r) $unread_msgs = (int) (mysqli_fetch_assoc($r)['c'] ?? 0);
    } catch (\Throwable $e) { /* tabla no existe — sidebar sin badge */ }
}
?>
<aside class="sidebar">
    <div class="sidebar-brand">
        <div class="brand-logo">TUOI</div>
        <div class="brand-sub">Panel de administración</div>
    </div>

    <nav class="sidebar-nav">
        <div class="nav-section">General</div>
        <a href="index.php" class="nav-item <?= $current_admin_page === 'index.php' ? 'active' : '' ?>">
            <span class="nav-icon">📊</span> Dashboard
        </a>

        <div class="nav-section" style="margin-top:12px;">Contenido</div>
        <a href="contenido.php" class="nav-item <?= $current_admin_page === 'contenido.php' ? 'active' : '' ?>">
            <span class="nav-icon">✏️</span> Editar textos
        </a>
        <a href="imagenes.php" class="nav-item <?= $current_admin_page === 'imagenes.php' ? 'active' : '' ?>">
            <span class="nav-icon">🖼️</span> Imágenes
        </a>
        <a href="carta.php" class="nav-item <?= $current_admin_page === 'carta.php' ? 'active' : '' ?>">
            <span class="nav-icon">🍽️</span> Carta
        </a>

        <a href="testimonios.php" class="nav-item <?= $current_admin_page === 'testimonios.php' ? 'active' : '' ?>">
            <span class="nav-icon">💬</span> Testimonios
        </a>
        <a href="mensajes.php" class="nav-item <?= $current_admin_page === 'mensajes.php' ? 'active' : '' ?>" style="position:relative;">
            <span class="nav-icon">📬</span> Mensajes
            <?php if ($unread_msgs > 0): ?>
            <span style="margin-left:auto;background:#dc2626;color:#fff;font-size:10px;font-weight:700;padding:2px 7px;border-radius:10px;min-width:18px;text-align:center;"><?= $unread_msgs ?></span>
            <?php endif; ?>
        </a>

        <a href="usuarios.php" class="nav-item <?= $current_admin_page === 'usuarios.php' ? 'active' : '' ?>">
            <span class="nav-icon">👤</span> Usuarios
        </a>

        <div class="nav-section" style="margin-top:12px;">Sitio</div>
        <a href="../index.php" target="_blank" class="nav-item">
            <span class="nav-icon">🌐</span> Ver sitio
        </a>
        <a href="../pages/carta/" target="_blank" class="nav-item">
            <span class="nav-icon">🍽️</span> Ver carta
        </a>
        <a href="../pages/eventos/" target="_blank" class="nav-item">
            <span class="nav-icon">🎉</span> Ver Eventos
        </a>
    </nav>

    <div class="sidebar-footer">
        <a href="logout.php" class="logout-link">
            <span>⎋</span> Cerrar sesión
        </a>
    </div>
</aside>
