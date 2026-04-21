<?php
/**
 * Header compartido — se incluye en todas las páginas.
 * Variables requeridas antes de incluir este archivo:
 *   $base         → ruta relativa hasta la raíz del proyecto (ej: '' / '../' / '../../')
 *   $current_page → 'inicio' | 'carta' | 'quienes-somos'
 *   $page_title   → (opcional) título personalizado de la pestaña
 */
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title ?? 'TUOI | Functional Coffee & Smart Food') ?></title>
    <link rel="stylesheet" href="<?= $base ?>assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;800&display=swap" rel="stylesheet">
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

        <a href="<?= $base ?>index.php"
           class="nav-link <?= ($current_page ?? '') === 'inicio' ? 'active' : '' ?>">
            Inicio
        </a>

        <!-- Dropdown Carta -->
        <div class="nav-dropdown <?= ($current_page ?? '') === 'carta' ? 'active' : '' ?>">
            <button class="nav-link dropdown-trigger" aria-haspopup="true" aria-expanded="false">
                Carta <span class="arrow" aria-hidden="true">▾</span>
            </button>
            <div class="dropdown-menu" role="menu">
                <a href="<?= $base ?>pages/carta/" role="menuitem">Desayunos</a>
                <a href="<?= $base ?>pages/carta/?cat=bocadillos" role="menuitem">Bocadillos</a>
                <a href="<?= $base ?>pages/carta/?cat=ensaladas" role="menuitem">Ensaladas</a>
                <a href="<?= $base ?>pages/carta/?cat=plant-based" role="menuitem">Plant Based</a>
                <a href="<?= $base ?>pages/carta/?cat=gluten-free" role="menuitem">Gluten Free</a>
                <a href="<?= $base ?>pages/carta/?cat=bebidas" role="menuitem">Bebidas</a>
                <a href="<?= $base ?>pages/carta/?cat=momento-dulce" role="menuitem">Momento Dulce</a>
                <a href="<?= $base ?>pages/carta/?cat=ingredientes-extras" role="menuitem">Ingredientes Extras</a>
            </div>
        </div>

        <a href="<?= $base ?>pages/quienes-somos.php"
           class="nav-link <?= ($current_page ?? '') === 'quienes-somos' ? 'active' : '' ?>">
            Quiénes somos
        </a>

        <!-- Toggle de idioma (preparado, sin i18n todavía) -->
        <button class="lang-toggle" aria-label="Cambiar idioma">
            <span class="lang-option lang-active">ES</span>
            <span class="lang-sep">/</span>
            <span class="lang-option">EN</span>
        </button>

    </nav>
</header>
