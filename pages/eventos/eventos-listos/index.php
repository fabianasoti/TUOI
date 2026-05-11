<?php
$base         = '../../../';
$current_page = 'eventos';
require $base . 'config/conexion.php';
require $base . 'config/content_helper.php';
require_once $base . 'config/lang.php';
$page_title = $lang === 'en' ? 'Ready to enjoy | TUOI' : 'Listos para disfrutar | TUOI';

require $base . 'includes/header.php';
$c = load_site_content($conexion, $lang);

// Estructura fija de categorías y experiencias. Los textos se editan desde
// admin/contenido.php (keys ev_el_*).
$el_categories = [
    [
        'anchor'   => 'flow-coffee',
        'label'    => 'Catering corporativo',
        'title'    => 'Coffee Break',
        'audience' => 'Reuniones · Workshops · Eventos corporativos · Presentaciones · Jornadas deportivas',
        'cards'  => [
            ['key' => 'e1', 'level' => 'Essential', 'name' => 'Flow Coffee Essential'],
            ['key' => 'e2', 'level' => 'Signature', 'name' => 'Flow Coffee Signature'],
        ],
    ],
    [
        'anchor'   => 'social-cocktail',
        'label'    => 'Cóctel',
        'title'    => 'Social Cocktail',
        'audience' => '',
        'cards'  => [
            ['key' => 'e3', 'level' => 'Essential', 'name' => 'Social Cocktail Essential'],
            ['key' => 'e4', 'level' => 'Signature', 'name' => 'Social Cocktail Signature'],
        ],
    ],
    [
        'anchor'   => 'table-experience',
        'label'    => 'Brunch & Comida',
        'title'    => 'Table Experience',
        'audience' => '',
        'cards'  => [
            ['key' => 'e5', 'level' => 'Essential', 'name' => 'Table Experience Essential'],
            ['key' => 'e6', 'level' => 'Signature', 'name' => 'Table Experience Signature'],
        ],
    ],
];
?>

<main class="el-page">

<!-- ── HERO ──────────────────────────────────────────────────────────────── -->
<section class="page-hero ev-hero el-hero">
    <a href="<?= $base ?>pages/eventos/" class="el-hero__back">
        <span class="el-hero__back-arrow" aria-hidden="true">←</span>
        <span class="el-hero__back-text"><?= $lang === 'en' ? 'Back to Events' : 'Volver a Eventos' ?></span>
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
        <a href="#<?= $cat['anchor'] ?>" class="el-subnav__link"><?= htmlspecialchars($cat['title']) ?></a>
        <?php endforeach; ?>
    </div>
</nav>

<!-- ── CATEGORÍAS ────────────────────────────────────────────────────────── -->
<?php foreach ($el_categories as $cat): ?>
<section class="el-section" id="<?= $cat['anchor'] ?>">
    <div class="el-section__inner">
        <header class="el-section__head">
            <?php if (!empty($cat['label'])): ?>
            <span class="section-label"><?= htmlspecialchars($cat['label']) ?></span>
            <?php endif; ?>
            <h2><?= htmlspecialchars($cat['title']) ?></h2>
            <?php if (!empty($cat['audience'])): ?>
            <p class="el-section__audience"><?= htmlspecialchars($cat['audience']) ?></p>
            <?php endif; ?>
        </header>
        <div class="el-cards">
            <?php foreach ($cat['cards'] as $card):
                $tk = "ev_el_{$card['key']}_tagline";
                $bk = "ev_el_{$card['key']}_body";
            ?>
            <article class="el-card el-card--<?= strtolower($card['level']) ?>">
                <span class="el-card__level"><?= htmlspecialchars($card['level']) ?></span>
                <h3 class="el-card__title"><?= htmlspecialchars($card['name']) ?></h3>
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
        <span class="section-label">Servicio</span>
        <h2><?= $lang === 'en' ? 'What the service includes' : 'Lo que incluye el servicio' ?></h2>
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
                <span class="el-conditions__label"><?= $lang === 'en' ? 'Booking & payment terms' : 'Condiciones de contratación y pago' ?></span>
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
        <h2><?= htmlspecialchars($c['ev_cta_h2'] ?? '¿Tienes un evento en mente?') ?></h2>
        <p><?= htmlspecialchars($c['ev_cta_text'] ?? 'Cuéntanos cómo lo imaginas y diseñamos el menú a tu medida.') ?></p>
        <a href="<?= $base ?>pages/eventos/#contacto" class="btn-primary ev-cta__btn">
            <?= htmlspecialchars($c['ev_cta_btn'] ?? 'Hablamos →') ?>
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
