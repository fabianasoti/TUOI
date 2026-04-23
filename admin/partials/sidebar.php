<?php
$current_admin_page = basename($_SERVER['PHP_SELF']);
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
