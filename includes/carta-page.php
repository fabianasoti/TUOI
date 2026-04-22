<?php
/**
 * Template compartido para todas las páginas de categoría de la carta.
 * Variables requeridas:
 *   $base, $current_page = 'carta', $current_carta (slug), $carta_titulo, $carta_desc
 */
require $base . 'config/conexion.php';
require $base . 'config/content_helper.php';
require $base . 'includes/header.php';
// $lang and $carta_info are set by header.php → lang.php

// Override title/desc with translation if in English
if ($lang === 'en' && isset($carta_info[$current_carta])) {
    $carta_titulo = $carta_info[$current_carta]['en'][0];
    $carta_desc   = $carta_info[$current_carta]['en'][1];
}

// Load images respecting saved order, with EN fallback
$img_dir_slug = ($lang === 'en') ? $current_carta . '-en' : $current_carta;
$img_dir      = __DIR__ . '/../assets/img/carta/' . $img_dir_slug . '/';
$img_section  = 'carta/' . $img_dir_slug;

// If EN dir is empty/missing, fall back to ES
if ($lang === 'en' && (!is_dir($img_dir) || empty(glob($img_dir . '*.{webp,jpg,jpeg,png}', GLOB_BRACE)))) {
    $img_dir_slug = $current_carta;
    $img_dir      = __DIR__ . '/../assets/img/carta/' . $current_carta . '/';
    $img_section  = 'carta/' . $current_carta;
}

$img_base = $base . 'assets/img/carta/' . $img_dir_slug . '/';
$images   = load_ordered_images($conexion, $img_section, $img_dir);
?>

<main>

    <!-- Hero de página interior -->
    <section class="page-hero">
        <span class="section-label"><?= t('carta_breadcrumb') ?></span>
        <h1><?= htmlspecialchars($carta_titulo) ?></h1>
        <p><?= htmlspecialchars($carta_desc) ?></p>
    </section>

    <!-- Subnav de categorías -->
    <?php require $base . 'includes/carta-subnav.php'; ?>

    <!-- Grid de imágenes -->
    <div class="carta-content">
        <?php if (!empty($images)): ?>
            <div class="carta-grid">
                <?php foreach ($images as $img): ?>
                    <div class="carta-img-card">
                        <img
                            src="<?= $img_base . htmlspecialchars(basename($img)) ?>"
                            alt="<?= htmlspecialchars($carta_titulo) ?>"
                            loading="lazy"
                        >
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

</main>

<?php require $base . 'includes/footer.php'; ?>
