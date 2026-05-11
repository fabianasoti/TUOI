<?php
/**
 * Template compartido para todas las páginas de categoría de la carta.
 * Variables requeridas:
 *   $base, $current_page = 'carta', $current_carta (slug), $carta_titulo, $carta_desc
 */
require $base . 'config/conexion.php';
require $base . 'config/content_helper.php';
require $base . 'includes/header.php';
// header.php carga lang.php, que define $lang y $carta_info como globales.

// Si estamos en EN y la categoría tiene traducción, sustituimos título y descripción.
// La página padre nos pasa los textos en ES; aquí los reemplazamos in-place.
if ($lang === 'en' && isset($carta_info[$current_carta])) {
    $carta_titulo = $carta_info[$current_carta]['en'][0];
    $carta_desc   = $carta_info[$current_carta]['en'][1];
}

// ── Resolución del directorio de imágenes ───────────────────────────────
// Las imágenes pueden tener una variante en inglés en una carpeta paralela
// con sufijo "-en" (p. ej. assets/img/carta/desayunos-en/). El admin las
// sube por separado para cada idioma cuando hay texto rotulado dentro de la imagen.
$img_dir_slug = ($lang === 'en') ? $current_carta . '-en' : $current_carta;
$img_dir      = __DIR__ . '/../assets/img/carta/' . $img_dir_slug . '/';
$img_section  = 'carta/' . $img_dir_slug;

// Fallback: si estamos en EN pero la carpeta "-en" no existe o está vacía,
// caemos a las imágenes en español. Evita huecos cuando aún no se ha
// preparado la versión inglesa de una categoría concreta.
if ($lang === 'en' && (!is_dir($img_dir) || empty(glob($img_dir . '*.{webp,jpg,jpeg,png}', GLOB_BRACE)))) {
    $img_dir_slug = $current_carta;
    $img_dir      = __DIR__ . '/../assets/img/carta/' . $current_carta . '/';
    $img_section  = 'carta/' . $current_carta;
}

// $img_base es la ruta relativa que usaremos en el atributo src del <img>.
// $images viene ya ordenado según la preferencia guardada en BD (ver content_helper.php).
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
