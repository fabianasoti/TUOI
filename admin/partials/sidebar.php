<?php
$current_admin_page = basename($_SERVER['PHP_SELF']);

// Cuenta de mensajes sin leer (para mostrar badge en la sidebar).
// Aseguramos columna is_read antes de consultar (idempotente).
$unread_msgs = 0;
if (isset($conexion) && $conexion) {
    try {
        @mysqli_query($conexion, "ALTER TABLE contact_submissions ADD COLUMN is_read TINYINT(1) NOT NULL DEFAULT 0 AFTER message");
    } catch (\Throwable $e) { /* tabla no existe o columna ya está */ }
    try {
        $r = mysqli_query($conexion, "SELECT COUNT(*) AS c FROM contact_submissions WHERE is_read = 0");
        if ($r) $unread_msgs = (int) (mysqli_fetch_assoc($r)['c'] ?? 0);
    } catch (\Throwable $e) { /* tabla no existe — sin badge */ }
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

        <a href="eventos.php" class="nav-item <?= $current_admin_page === 'eventos.php' ? 'active' : '' ?>">
            <span class="nav-icon">🎉</span> Eventos
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
