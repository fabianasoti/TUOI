<?php
$base         = '../../../';
$current_page = 'eventos';
$extra_css    = 'eventos';
require $base . 'config/conexion.php';
require $base . 'config/content_helper.php';
require_once $base . 'config/lang.php';
$page_title = $lang === 'en' ? 'Designed for you | TUOI' : 'Diseñado a tu medida | TUOI';

require $base . 'includes/header.php';
$c = load_site_content($conexion, $lang);

// Galería: reutilizamos las imágenes del carrusel de eventos para una
// versión provisional. Cuando se cree el directorio propio
// assets/img/eventos/a-tu-medida/, este loader las recogerá automáticamente.
$atm_dir = dirname(__DIR__, 3) . '/assets/img/eventos/a-tu-medida/';
$atm_imgs = load_ordered_images($conexion, 'eventos/a-tu-medida', $atm_dir, '*.{webp,jpg,jpeg,png}');
if (empty($atm_imgs)) {
    $fallback_dir = dirname(__DIR__, 3) . '/assets/img/eventos/carrusel/';
    $atm_imgs = load_ordered_images($conexion, 'eventos/carrusel', $fallback_dir, '*.{webp,jpg,jpeg,png}');
}
// El path web depende de qué directorio acabamos usando.
$atm_web_dir = !empty($atm_imgs) && str_contains($atm_imgs[0], '/a-tu-medida/')
    ? 'assets/img/eventos/a-tu-medida/'
    : 'assets/img/eventos/carrusel/';
?>
<main>

<section class="page-hero">
    <span class="section-label"><?= htmlspecialchars($c['ev_opt2_label'] ?? t('atm_intro_label')) ?></span>
    <h1><?= htmlspecialchars($c['ev_opt2_title'] ?? 'Diseñado a tu medida') ?></h1>
    <?php if (!empty($c['ev_opt2_desc'])): ?>
    <p><?= htmlspecialchars($c['ev_opt2_desc']) ?></p>
    <?php endif; ?>
</section>

<!-- ── INTRO / MANIFIESTO ────────────────────────────────────────────────── -->
<section class="ev-intro">
    <div class="ev-intro__inner">
        <div class="ev-intro__head">
            <span class="section-label"><?= t('atm_intro_label') ?></span>
            <span class="ev-intro__rule" aria-hidden="true"></span>
        </div>
        <h2 class="ev-intro__h2"><?= t('atm_intro_h2') ?></h2>
        <p class="ev-intro__body"><?= t('atm_intro_p') ?></p>
    </div>
</section>

<!-- ── GALERÍA (marquee) ─────────────────────────────────────────────────── -->
<?php if (!empty($atm_imgs)): ?>
<div class="ev-img-marquee" aria-hidden="true">
    <div class="ev-img-marquee__track">
        <?php foreach (array_merge($atm_imgs, $atm_imgs) as $img_path): ?>
        <div class="ev-img-marquee__item">
            <img src="<?= $base . $atm_web_dir . htmlspecialchars(basename($img_path)) ?>"
                 alt="" loading="lazy">
        </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<!-- ── CTA ───────────────────────────────────────────────────────────────── -->
<section class="ev-cta">
    <div class="ev-cta__inner">
        <h2><?= htmlspecialchars($c['ev_cta_h2'] ?? '¿Tienes un evento en mente?') ?></h2>
        <p><?= htmlspecialchars($c['ev_cta_text'] ?? 'Cuéntanos cómo lo imaginas y diseñamos el menú a tu medida.') ?></p>
        <a href="<?= $base ?>pages/contacto/?from=a-tu-medida" class="btn-primary ev-cta__btn">
            <?= t('atm_cta_btn') ?>
        </a>
    </div>
</section>

</main>
<?php require $base . 'includes/footer.php'; ?>
