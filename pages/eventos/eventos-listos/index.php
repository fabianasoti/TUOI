<?php
$base         = '../../../';
$current_page = 'eventos';
$extra_css    = 'eventos';
require $base . 'config/conexion.php';
require $base . 'config/content_helper.php';
require_once $base . 'config/lang.php';
$page_title = $lang === 'en' ? 'Ready to enjoy | TUOI' : 'Listos para disfrutar | TUOI';

require $base . 'includes/header.php';
$c = load_site_content($conexion, $lang);

// Estructura fija de categorías y experiencias. Los textos vienen de claves
// editables desde admin/contenido.php (keys ev_el_*). Solo el "anchor" y el
// "level_key" (que apunta a la clave compartida de nivel) son fijos.
$el_categories = [
    [
        'anchor'       => 'flow-coffee',
        'img_section'  => 'eventos/coffee-break',
        'label_key'    => 'ev_el_cat1_label',
        'title_key'    => 'ev_el_cat1_title',
        'audience_key' => 'ev_el_cat1_audience',
        'cards'        => [
            ['key' => 'e1', 'level_key' => 'ev_el_level_essential', 'name_key' => 'ev_el_e1_name', 'level_class' => 'essential'],
            ['key' => 'e2', 'level_key' => 'ev_el_level_signature', 'name_key' => 'ev_el_e2_name', 'level_class' => 'signature'],
        ],
    ],
    [
        'anchor'       => 'social-cocktail',
        'img_section'  => 'eventos/social-cocktail',
        'label_key'    => 'ev_el_cat2_label',
        'title_key'    => 'ev_el_cat2_title',
        'audience_key' => 'ev_el_cat2_audience',
        'cards'        => [
            ['key' => 'e3', 'level_key' => 'ev_el_level_essential', 'name_key' => 'ev_el_e3_name', 'level_class' => 'essential'],
            ['key' => 'e4', 'level_key' => 'ev_el_level_signature', 'name_key' => 'ev_el_e4_name', 'level_class' => 'signature'],
        ],
    ],
    [
        'anchor'       => 'table-experience',
        'img_section'  => 'eventos/table-experience',
        'label_key'    => 'ev_el_cat3_label',
        'title_key'    => 'ev_el_cat3_title',
        'audience_key' => 'ev_el_cat3_audience',
        'cards'        => [
            ['key' => 'e5', 'level_key' => 'ev_el_level_essential', 'name_key' => 'ev_el_e5_name', 'level_class' => 'essential'],
            ['key' => 'e6', 'level_key' => 'ev_el_level_signature', 'name_key' => 'ev_el_e6_name', 'level_class' => 'signature'],
        ],
    ],
];
?>

<main class="el-page">

<!-- ── HERO ──────────────────────────────────────────────────────────────── -->
<section class="page-hero ev-hero el-hero">
    <a href="<?= $base ?>pages/eventos/" class="el-hero__back">
        <span class="el-hero__back-arrow" aria-hidden="true">←</span>
        <span class="el-hero__back-text"><?= htmlspecialchars($c['ev_el_back_text'] ?? t('ev_el_back_text')) ?></span>
    </a>
    <span class="section-label"><?= htmlspecialchars($c['ev_opt1_label'] ?? 'Experiencias TUOI') ?></span>
    <h1><?= htmlspecialchars($c['ev_el_h1'] ?? 'Listos para disfrutar') ?></h1>
    <?php if (!empty($c['ev_el_intro'])): ?>
    <p><?= htmlspecialchars($c['ev_el_intro']) ?></p>
    <?php endif; ?>
</section>

<!-- ── SUBNAV INTERNO ────────────────────────────────────────────────────── -->
<nav class="el-subnav" aria-label="Categorías">
    <div class="el-subnav__inner">
        <?php foreach ($el_categories as $cat): ?>
        <a href="#<?= $cat['anchor'] ?>" class="el-subnav__link"><?= htmlspecialchars($c[$cat['title_key']] ?? '') ?></a>
        <?php endforeach; ?>
    </div>
</nav>

<!-- ── CATEGORÍAS ────────────────────────────────────────────────────────── -->
<?php foreach ($el_categories as $cat):
    $catLabel    = $c[$cat['label_key']]    ?? '';
    $catTitle    = $c[$cat['title_key']]    ?? '';
    $catAudience = $c[$cat['audience_key']] ?? '';
    $img_dir     = dirname(__DIR__, 3) . '/assets/img/' . $cat['img_section'] . '/';
    $cat_images  = load_ordered_images($conexion, $cat['img_section'], $img_dir, '*.{jpg,jpeg,png,webp,gif}');
?>
<section class="el-section" id="<?= $cat['anchor'] ?>">
    <div class="el-section__inner">
        <header class="el-section__head">
            <?php if ($catLabel !== ''): ?>
            <span class="section-label"><?= htmlspecialchars($catLabel) ?></span>
            <?php endif; ?>
            <h2><?= htmlspecialchars($catTitle) ?></h2>
            <?php if ($catAudience !== ''): ?>
            <p class="el-section__audience"><?= htmlspecialchars($catAudience) ?></p>
            <?php endif; ?>
        </header>

        <?php if (!empty($cat_images)): ?>
        <div class="el-gallery">
            <?php foreach ($cat_images as $img_path):
                $img_file = basename($img_path);
                $img_url  = $base . 'assets/img/' . $cat['img_section'] . '/' . $img_file;
            ?>
            <img class="el-gallery__img" src="<?= htmlspecialchars($img_url) ?>"
                 alt="<?= htmlspecialchars($catTitle) ?>" loading="lazy">
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <div class="el-cards">
            <?php foreach ($cat['cards'] as $card):
                $tk        = "ev_el_{$card['key']}_tagline";
                $bk        = "ev_el_{$card['key']}_body";
                $cardName  = $c[$card['name_key']]  ?? '';
                $cardLevel = $c[$card['level_key']] ?? '';
            ?>
            <article class="el-card el-card--<?= $card['level_class'] ?>">
                <span class="el-card__level"><?= htmlspecialchars($cardLevel) ?></span>
                <h3 class="el-card__title"><?= htmlspecialchars($cardName) ?></h3>
                <?php if (!empty($c[$tk])): ?>
                <blockquote class="el-card__tagline"><?= htmlspecialchars($c[$tk]) ?></blockquote>
                <?php endif; ?>
                <?php if (!empty($c[$bk])): ?>
                <div class="el-card__body"><?= $c[$bk] ?></div>
                <?php endif; ?>
            </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endforeach; ?>

<!-- ── LO QUE INCLUYE EL SERVICIO ────────────────────────────────────────── -->
<?php if (!empty($c['ev_el_service_body'])): ?>
<section class="el-service">
    <div class="el-service__inner">
        <?php if (!empty($c['ev_el_service_label'])): ?>
        <span class="section-label"><?= htmlspecialchars($c['ev_el_service_label']) ?></span>
        <?php endif; ?>
        <h2><?= htmlspecialchars($c['ev_el_service_h2'] ?? t('ev_el_service_h2')) ?></h2>
        <div class="el-service__body"><?= $c['ev_el_service_body'] ?></div>
    </div>
</section>
<?php endif; ?>

<!-- ── CONDICIONES (colapsable) ──────────────────────────────────────────── -->
<?php if (!empty($c['ev_el_conditions_body'])): ?>
<section class="el-conditions">
    <div class="el-conditions__inner">
        <details class="el-conditions__details">
            <summary>
                <span class="el-conditions__label"><?= htmlspecialchars($c['ev_el_conditions_label'] ?? t('ev_el_conditions_label')) ?></span>
                <span class="el-conditions__icon" aria-hidden="true">+</span>
            </summary>
            <div class="el-conditions__body"><?= $c['ev_el_conditions_body'] ?></div>
        </details>
    </div>
</section>
<?php endif; ?>

<!-- ── CTA FINAL ─────────────────────────────────────────────────────────── -->
<section class="ev-cta">
    <div class="ev-cta__inner">
        <h2><?= htmlspecialchars($c['ev_cta_h2'] ?? t('ev_cta_h2')) ?></h2>
        <p><?= htmlspecialchars($c['ev_cta_text'] ?? t('ev_cta_text')) ?></p>
        <a href="<?= $base ?>pages/contacto/" class="btn-primary ev-cta__btn">
            <?= htmlspecialchars($c['ev_cta_btn'] ?? t('ev_cta_btn')) ?>
        </a>
    </div>
</section>

</main>

<script>
(function () {
    // Scrollspy: marca el link del subnav cuyo bloque está en pantalla.
    var links    = document.querySelectorAll('.el-subnav__link');
    var sections = Array.prototype.map.call(links, function (a) {
        var id = a.getAttribute('href').slice(1);
        return document.getElementById(id);
    }).filter(Boolean);

    if (!sections.length || !('IntersectionObserver' in window)) return;

    function setActive(id) {
        links.forEach(function (l) {
            l.classList.toggle('is-active', l.getAttribute('href') === '#' + id);
        });
    }

    var visible = new Map();
    var io = new IntersectionObserver(function (entries) {
        entries.forEach(function (en) {
            if (en.isIntersecting) visible.set(en.target.id, en.intersectionRatio);
            else visible.delete(en.target.id);
        });
        var best = null, bestRatio = -1;
        visible.forEach(function (r, id) { if (r > bestRatio) { bestRatio = r; best = id; } });
        if (best) setActive(best);
    }, {
        // El subnav sticky ocupa ~50px bajo el navbar; activamos cuando la sección
        // entra en el tercio superior del viewport.
        rootMargin: '-30% 0px -55% 0px',
        threshold: [0, .25, .5, .75, 1]
    });

    sections.forEach(function (s) { io.observe(s); });

    // Estado inicial por si el usuario ya está en mitad de la página al cargar
    setActive(sections[0].id);
})();
</script>

<?php require $base . 'includes/footer.php'; ?>
