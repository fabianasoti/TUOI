<?php
$base         = '../../../';
$current_page = 'eventos';
require $base . 'config/conexion.php';
require $base . 'config/content_helper.php';
require_once $base . 'config/lang.php';
$page_title = $lang === 'en' ? 'Designed for you | TUOI' : 'Diseñado a tu medida | TUOI';

require $base . 'includes/header.php';
$c = load_site_content($conexion, $lang);
?>
<main>

<section class="page-hero">
    <span class="section-label"><?= htmlspecialchars($c['ev_opt2_label'] ?? 'A tu medida') ?></span>
    <h1><?= htmlspecialchars($c['ev_opt2_title'] ?? 'Diseñado a tu medida') ?></h1>
    <?php if (!empty($c['ev_opt2_desc'])): ?>
    <p><?= htmlspecialchars($c['ev_opt2_desc']) ?></p>
    <?php endif; ?>
</section>

<section class="ev-child-placeholder">
    <div class="ev-child-placeholder__inner">
        <p>Contenido por definir.</p>
        <a href="<?= $base ?>pages/eventos/#contacto" class="btn-primary">Contáctanos</a>
    </div>
</section>

</main>
<?php require $base . 'includes/footer.php'; ?>
