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
        $_meta_title = $page_title ?? 'TUOI | Functional Coffee & Smart Food';
        $_meta_desc  = $page_description ?? ($lang === 'en'
            ? 'Functional coffee & smart food café in Valencia. Healthy breakfasts, brunch, lunch and drinks adapted to your day.'
            : 'Cafetería de comida funcional y smart food en Valencia. Desayunos saludables, brunch, lunch y bebidas adaptadas a las necesidades de tu día.');
    ?>
    <meta name="description" content="<?= htmlspecialchars($_meta_desc) ?>">
    <meta name="robots" content="index, follow">
    <meta property="og:type" content="website">
    <meta property="og:locale" content="<?= $lang === 'en' ? 'en_US' : 'es_ES' ?>">
    <meta property="og:site_name" content="TUOI">
    <meta property="og:title" content="<?= htmlspecialchars($_meta_title) ?>">
    <meta property="og:description" content="<?= htmlspecialchars($_meta_desc) ?>">
    <meta property="og:url" content="<?= htmlspecialchars($page_url ?? 'https://tuoi.es') ?>">
    <meta property="og:image" content="https://tuoi.es/assets/img/og_image.png">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?= htmlspecialchars($_meta_title) ?>">
    <meta name="twitter:description" content="<?= htmlspecialchars($_meta_desc) ?>">
    <meta name="twitter:image" content="https://tuoi.es/assets/img/og_image.png">
    <?php
        // Cache-busting del CSS: añadimos ?v=<mtime> al src para que cuando
        // se modifique el archivo, el navegador descargue la versión nueva
        // sin que el usuario tenga que limpiar caché. time() es un fallback
        // por si filemtime() falla (devolvería false y romperíamos la URL).
        $css_root = dirname(__DIR__) . '/assets/css/';
        $css_v    = @filemtime($css_root . 'style.css') ?: time();
    ?>
    <link rel="stylesheet" href="<?= $base ?>assets/css/style.css?v=<?= $css_v ?>">
    <?php if (!empty($extra_css)):
        $extra_v = @filemtime($css_root . $extra_css . '.css') ?: time();
    ?>
    <link rel="stylesheet" href="<?= $base ?>assets/css/<?= htmlspecialchars($extra_css) ?>.css?v=<?= $extra_v ?>">
    <?php endif; ?>
    <link rel="stylesheet" href="<?= $base ?>assets/fonts/inter.css">
    <link rel="icon" type="image/png" href="<?= $base ?>assets/img/favicon.png">
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
                <a href="<?= $base ?>pages/carta/?cat=menu-brunch" role="menuitem"><?= t('cat_brunch') ?></a>
                <a href="<?= $base ?>pages/carta/?cat=toque-salado" role="menuitem"><?= t('cat_toque') ?></a>
                <a href="<?= $base ?>pages/carta/?cat=menu-lunch" role="menuitem"><?= t('cat_lunch') ?></a>
                <a href="<?= $base ?>pages/carta/?cat=momento-dulce" role="menuitem"><?= t('cat_dulce') ?></a>
                <a href="<?= $base ?>pages/carta/?cat=bebidas" role="menuitem"><?= t('cat_bebidas') ?></a>
            </div>
        </div>

        <!-- Dropdown Eventos -->
        <div class="nav-dropdown <?= ($current_page ?? '') === 'eventos' ? 'active' : '' ?>">
            <a href="<?= $base ?>pages/eventos/" class="nav-link dropdown-trigger" aria-haspopup="true" aria-expanded="false">
                <?= t('nav_eventos') ?> <span class="arrow" aria-hidden="true">▾</span>
            </a>
            <div class="dropdown-menu" role="menu">
                <a href="<?= $base ?>pages/eventos/" role="menuitem" class="dropdown-item-eventos-all"><?= t('nav_ev_all') ?></a>
                <div class="dropdown-divider dropdown-divider--mobile"></div>
                <a href="<?= $base ?>pages/eventos/eventos-listos/" role="menuitem"><?= t('nav_ev_listos') ?></a>
                <a href="<?= $base ?>pages/eventos/a-tu-medida/"    role="menuitem"><?= t('nav_ev_medida') ?></a>
                <a href="<?= $base ?>pages/contacto/"   role="menuitem"><?= t('nav_ev_contacto') ?></a>
            </div>
        </div>

        <a href="<?= $base ?>pages/quienes-somos.php"
           class="nav-link <?= ($current_page ?? '') === 'quienes-somos' ? 'active' : '' ?>">
            <?= t('nav_about') ?>
        </a>

        <a href="<?= $base ?>pages/contacto/"
           class="nav-link <?= ($current_page ?? '') === 'contacto' ? 'active' : '' ?>">
            <?= t('nav_contacto') ?>
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
