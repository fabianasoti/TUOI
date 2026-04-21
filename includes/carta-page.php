<?php
/**
 * Template compartido para todas las páginas de categoría de la carta.
 * Variables requeridas:
 *   $base, $current_page = 'carta', $current_carta (slug), $carta_titulo, $carta_desc
 */
require $base . 'config/conexion.php';
require $base . 'includes/header.php';

// Buscar imágenes en la carpeta de la categoría
$img_dir    = __DIR__ . '/../assets/img/carta/' . $current_carta . '/';
$img_base   = $base . 'assets/img/carta/' . $current_carta . '/';
$images     = [];

if (is_dir($img_dir)) {
    $found = glob($img_dir . '*.{webp,jpg,jpeg,png}', GLOB_BRACE);
    if ($found) {
        // Ordenar por fecha de modificación descendente (más reciente primero)
        usort($found, fn($a, $b) => filemtime($b) - filemtime($a));
        $images = $found;
    }
}
?>

<main>

    <!-- Hero de página interior -->
    <section class="page-hero">
        <span class="section-label">Carta</span>
        <h1><?= htmlspecialchars($carta_titulo) ?></h1>
        <p><?= htmlspecialchars($carta_desc) ?></p>
    </section>

    <!-- Subnav de categorías -->
    <?php require $base . 'includes/carta-subnav.php'; ?>

    <!-- Grid de imágenes -->
    <div class="carta-content">
        <?php if (empty($images)): ?>
            <div class="carta-empty">
                <div class="empty-icon" aria-hidden="true">🍽️</div>
                <h3>Próximamente</h3>
                <p>Las imágenes de esta sección se cargarán desde el panel de administración.</p>
            </div>
        <?php else: ?>
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
