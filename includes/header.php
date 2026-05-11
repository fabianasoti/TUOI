<?php
/**
 * Header compartido — se incluye en todas las páginas públicas.
 * Variables requeridas antes de incluir este archivo:
 *   $base         → ruta relativa hasta la raíz del proyecto
 *   $current_page → 'inicio' | 'carta' | 'quienes-somos'
 *   $page_title   → (opcional) título personalizado de la pestaña
 */
require_once $base . 'config/lang.php';
?>
<!DOCTYPE html>
<html lang="<?= $lang ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title ?? 'TUOI | Functional Coffee & Smart Food') ?></title>
    <?php
        // Cache-busting del CSS: añadimos ?v=<mtime> al src para que cuando
        // se modifique el archivo, el navegador descargue la versión nueva
        // sin que el usuario tenga que limpiar caché. time() es un fallback
        // por si filemtime() falla (devolvería false y romperíamos la URL).
        $css_v = @filemtime(dirname(__DIR__) . '/assets/css/style.css') ?: time();
    ?>
    <link rel="stylesheet" href="<?= $base ?>assets/css/style.css?v=<?= $css_v ?>">
    <link rel="stylesheet" href="<?= $base ?>assets/fonts/inter.css">
</head>
<body>

<header class="navbar" id="navbar">
    <a href="<?= $base ?>index.php" class="logo">
        <img src="<?= $base ?>assets/img/tuoi_logo.png" alt="TUOI Logo" class="logo-img">
    </a>

    <!-- Hamburguesa (móvil) -->
    <button class="menu-toggle" aria-label="Abrir menú" aria-expanded="false">
        <span></span>
        <span></span>
        <span></span>
    </button>

    <nav class="nav-links" id="nav-links">

        <?php
            // Patrón de "enlace activo": cada página define $current_page
            // antes de incluir el header (p. ej. 'inicio', 'carta', 'eventos',
            // 'quienes-somos'). Si coincide con este enlace, le añadimos la
            // clase .active para que el CSS lo destaque.
        ?>
        <a href="<?= $base ?>index.php"
           class="nav-link <?= ($current_page ?? '') === 'inicio' ? 'active' : '' ?>">
            <?= t('nav_home') ?>
        </a>

        <!-- Dropdown Carta -->
        <div class="nav-dropdown <?= ($current_page ?? '') === 'carta' ? 'active' : '' ?>">
            <a href="<?= $base ?>pages/carta/" class="nav-link dropdown-trigger" aria-haspopup="true" aria-expanded="false">
                <?= t('nav_menu') ?> <span class="arrow" aria-hidden="true">▾</span>
            </a>
            <div class="dropdown-menu" role="menu">
                <a href="<?= $base ?>pages/carta/" role="menuitem"><?= t('cat_desayunos') ?></a>
                <a href="<?= $base ?>pages/carta/?cat=toque-salado" role="menuitem"><?= t('cat_toque') ?></a>
                <a href="<?= $base ?>pages/carta/?cat=momento-dulce" role="menuitem"><?= t('cat_dulce') ?></a>
                <a href="<?= $base ?>pages/carta/?cat=bebidas" role="menuitem"><?= t('cat_bebidas') ?></a>
                <a href="<?= $base ?>pages/carta/?cat=superalimentos" role="menuitem"><?= t('cat_super') ?></a>
            </div>
        </div>

        <!-- Dropdown Eventos -->
        <div class="nav-dropdown <?= ($current_page ?? '') === 'eventos' ? 'active' : '' ?>">
            <a href="<?= $base ?>pages/eventos/" class="nav-link dropdown-trigger" aria-haspopup="true" aria-expanded="false">
                <?= t('nav_eventos') ?> <span class="arrow" aria-hidden="true">▾</span>
            </a>
            <div class="dropdown-menu" role="menu">
                <a href="<?= $base ?>pages/eventos/#catering"      role="menuitem"><?= t('nav_catering') ?></a>
                <a href="<?= $base ?>pages/eventos/#team-building" role="menuitem"><?= t('nav_team_building') ?></a>
                <a href="<?= $base ?>pages/eventos/#networking"    role="menuitem"><?= t('nav_networking') ?></a>
            </div>
        </div>

        <a href="<?= $base ?>pages/quienes-somos.php"
           class="nav-link <?= ($current_page ?? '') === 'quienes-somos' ? 'active' : '' ?>">
            <?= t('nav_about') ?>
        </a>

        <!-- Toggle de idioma -->
        <div class="lang-toggle" role="group" aria-label="Cambiar idioma">
            <a href="<?= $base ?>set-lang.php?lang=es"
               class="lang-option <?= $lang === 'es' ? 'lang-active' : '' ?>"
               aria-current="<?= $lang === 'es' ? 'true' : 'false' ?>">ES</a>
            <span class="lang-sep">/</span>
            <a href="<?= $base ?>set-lang.php?lang=en"
               class="lang-option <?= $lang === 'en' ? 'lang-active' : '' ?>"
               aria-current="<?= $lang === 'en' ? 'true' : 'false' ?>">EN</a>
        </div>

    </nav>
</header>
