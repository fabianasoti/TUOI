<?php
$base         = '../../';
$current_page = 'eventos';
$extra_css    = 'eventos';
require $base . 'config/conexion.php';
require $base . 'config/content_helper.php';
require_once $base . 'config/lang.php';
$page_title = $lang === 'en' ? 'Events | TUOI' : 'Eventos | TUOI';
$page_url         = 'https://tuoi.es/pages/eventos/';
$page_description = $lang === 'en'
    ? 'Host your event at TUOI Valencia. Ready-made celebrations or fully tailored gastronomic experiences for groups and special occasions.'
    : 'Organiza tu evento en TUOI Valencia. Celebraciones listas para disfrutar o diseñadas a tu medida con gastronomía funcional y saludable.';

require $base . 'includes/header.php';
$c = load_site_content($conexion, $lang);

// ── Carrusel marquee images ─────────────────────────────────────────────────
$carrusel_dir  = dirname(__DIR__, 2) . '/assets/img/eventos/carrusel/';
$carrusel_imgs = load_ordered_images($conexion, 'eventos/carrusel', $carrusel_dir, '*.{webp,jpg,jpeg,png}');

// ── Por qué TUOI — lateral image ────────────────────────────────────────────
$why_dir  = dirname(__DIR__, 2) . '/assets/img/eventos/por-que-tuoi/';
$why_imgs = load_ordered_images($conexion, 'eventos/por-que-tuoi', $why_dir, '*.{webp,jpg,jpeg,png}');
$why_img  = !empty($why_imgs) ? basename($why_imgs[0]) : null;

// ── Logos prueba social ─────────────────────────────────────────────────────
$logos_dir  = dirname(__DIR__, 2) . '/assets/img/eventos/logos/';
$logos_imgs = load_ordered_images($conexion, 'eventos/logos', $logos_dir, '*.{webp,jpg,jpeg,png,svg}');

// ── Imagen de fondo del CTA final ───────────────────────────────────────────
$cta_dir  = dirname(__DIR__, 2) . '/assets/img/eventos/cta-fondo/';
$cta_imgs = load_ordered_images($conexion, 'eventos/cta-fondo', $cta_dir, '*.{webp,jpg,jpeg,png}');
$cta_img  = !empty($cta_imgs) ? basename($cta_imgs[0]) : null;

// ── Testimonios (carrusel) ─────────────────────────────────────────────────
$testimonios = [];
if ($conexion) {
    // Migración idempotente: añade columnas EN si no existen.
    try { mysqli_query($conexion, "ALTER TABLE testimonios ADD COLUMN quote_en TEXT NULL AFTER quote"); } catch (\Throwable $e) {}
    try { mysqli_query($conexion, "ALTER TABLE testimonios ADD COLUMN role_en VARCHAR(255) NULL AFTER role"); } catch (\Throwable $e) {}
    // Intentamos leer columnas EN; si la tabla aún no tiene la migración, caemos al SELECT antiguo.
    $res = false;
    try {
        $res = mysqli_query($conexion,
            "SELECT quote, quote_en, author, role, role_en FROM testimonios WHERE active = 1 ORDER BY sort_order ASC, id DESC"
        );
    } catch (\Throwable $e) {
        try {
            $res = mysqli_query($conexion,
                "SELECT quote, author, role FROM testimonios WHERE active = 1 ORDER BY sort_order ASC, id DESC"
            );
        } catch (\Throwable $e2) { $res = false; }
    }
    if ($res) {
        while ($row = mysqli_fetch_assoc($res)) {
            if ($lang === 'en') {
                if (!empty($row['quote_en'])) $row['quote'] = $row['quote_en'];
                if (!empty($row['role_en']))  $row['role']  = $row['role_en'];
            }
            unset($row['quote_en'], $row['role_en']);
            $testimonios[] = $row;
        }
    }
}

// ── Marquee items ───────────────────────────────────────────────────────────
$marquee_raw   = $c['ev_marquee_text'] ?? 'Team Building – Networking – Corporativos – Afterwork – Experiencias';
$marquee_items = array_values(array_filter(array_map('trim', explode('–', $marquee_raw))));
?>

<main>

<!-- ── HERO ──────────────────────────────────────────────────────────────── -->
<section class="page-hero ev-hero">
    <span class="section-label"><?= htmlspecialchars($c['ev_hero_label'] ?? 'Eventos · TUOI') ?></span>
    <h1><?= htmlspecialchars($c['ev_hero_h1'] ?? 'Celebra con nosotros') ?></h1>
    <p><?= htmlspecialchars($c['ev_hero_sub'] ?? '') ?></p>
    <div class="ev-hero__ctas">
        <a href="<?= $base ?>pages/contacto/" class="btn-primary ev-hero__cta-primary">
            <?= htmlspecialchars($c['ev_hero_cta_primary'] ?? 'Hablemos de tu evento') ?>
        </a>
        <a href="#menus" class="ev-hero__cta-secondary">
            <?= htmlspecialchars($c['ev_hero_cta_secondary'] ?? 'Ver menús') ?> <span aria-hidden="true">→</span>
        </a>
    </div>
</section>

<!-- ── MARQUEE IMÁGENES ──────────────────────────────────────────────────── -->
<?php if (!empty($carrusel_imgs)): ?>
<div class="ev-img-marquee" aria-hidden="true">
    <div class="ev-img-marquee__track">
        <?php foreach (array_merge($carrusel_imgs, $carrusel_imgs) as $img_path): ?>
        <div class="ev-img-marquee__item">
            <img src="<?= $base ?>assets/img/eventos/carrusel/<?= htmlspecialchars(basename($img_path)) ?>"
                 alt="Evento TUOI" loading="lazy">
        </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<!-- ── MANIFIESTO / INTRO NARRATIVA ──────────────────────────────────────── -->
<?php if (!empty($c['ev_intro_p1']) || !empty($c['ev_intro_p2'])): ?>
<section class="ev-intro">
    <div class="ev-intro__inner">
        <?php if (!empty($c['ev_intro_label'])): ?>
        <div class="ev-intro__head">
            <span class="section-label"><?= htmlspecialchars($c['ev_intro_label']) ?></span>
            <span class="ev-intro__rule" aria-hidden="true"></span>
        </div>
        <?php endif; ?>
        <div class="ev-intro__cols">
            <?php if (!empty($c['ev_intro_p1'])): ?>
            <p class="ev-intro__col"><?= htmlspecialchars($c['ev_intro_p1']) ?></p>
            <?php endif; ?>
            <?php if (!empty($c['ev_intro_p2'])): ?>
            <p class="ev-intro__col"><?= htmlspecialchars($c['ev_intro_p2']) ?></p>
            <?php endif; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- ── POR QUÉ TUOI ───────────────────────────────────────────────────────── -->
<section class="ev-why">
    <div class="ev-why__inner">
        <div class="ev-why__text">
            <span class="section-label"><?= htmlspecialchars($c['ev_why_label'] ?? 'Por qué TUOI') ?></span>
            <h2><?= htmlspecialchars($c['ev_why_h2'] ?? '¿Por qué TUOI?') ?></h2>
            <ul class="ev-why__list">
                <?php for ($i = 1; $i <= 4; $i++):
                    $title = $c["ev_why_b{$i}_title"] ?? '';
                    $desc  = $c["ev_why_b{$i}_desc"]  ?? '';
                    if ($title === '' && $desc === '') continue;
                ?>
                <li class="ev-why__bullet">
                    <?php if ($title !== ''): ?><h3><?= htmlspecialchars($title) ?></h3><?php endif; ?>
                    <?php if ($desc  !== ''): ?><p><?= nl2br(htmlspecialchars($desc)) ?></p><?php endif; ?>
                </li>
                <?php endfor; ?>
            </ul>
        </div>

        <div class="ev-why__image">
            <?php if ($why_img): ?>
            <img src="<?= $base ?>assets/img/eventos/por-que-tuoi/<?= htmlspecialchars($why_img) ?>"
                 alt="Por qué TUOI" loading="lazy">
            <?php else: ?>
            <div style="width:100%;height:100%;background:var(--fondo-beige);display:flex;align-items:center;justify-content:center;min-height:300px;border-radius:18px;">
                <span style="font-size:5rem;opacity:.2;">🌿</span>
            </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- ── INTRO PROPUESTA DE MENÚS ───────────────────────────────────────────── -->
<div class="ev-menus-intro" id="menus">
    <div class="ev-menus-intro__inner">
        <span class="section-label"><?= htmlspecialchars($c['ev_menus_label'] ?? 'Propuesta de menús') ?></span>
        <h2><?= htmlspecialchars($c['ev_menus_h2'] ?? 'Menús de grupo y catering') ?></h2>
        <?php if (!empty($c['ev_menus_intro'])): ?>
        <p><?= htmlspecialchars($c['ev_menus_intro']) ?></p>
        <?php endif; ?>
    </div>
</div>

<!-- ── OPCIONES (2 tarjetas) ─────────────────────────────────────────────── -->
<section class="ev-options">
    <div class="ev-options__inner">

        <article class="ev-option ev-option--listas">
            <span class="section-label"><?= htmlspecialchars($c['ev_opt1_label'] ?? 'Experiencias TUOI') ?></span>
            <h3 class="ev-option__title"><?= htmlspecialchars($c['ev_opt1_title'] ?? 'Experiencias TUOI') ?></h3>
            <?php if (!empty($c['ev_opt1_desc'])): ?>
            <p class="ev-option__desc"><?= htmlspecialchars($c['ev_opt1_desc']) ?></p>
            <?php endif; ?>
            <div class="ev-option__ctas">
                <a href="<?= $base ?>pages/eventos/eventos-listos/" class="btn-primary ev-option__cta">
                    <?= htmlspecialchars($c['ev_opt1_cta'] ?? 'Ver experiencias') ?> <span aria-hidden="true">→</span>
                </a>
            </div>
        </article>

        <article class="ev-option ev-option--medida">
            <span class="section-label"><?= htmlspecialchars($c['ev_opt2_label'] ?? 'A tu medida') ?></span>
            <h3 class="ev-option__title"><?= htmlspecialchars($c['ev_opt2_title'] ?? 'Diseñado a tu medida') ?></h3>
            <?php if (!empty($c['ev_opt2_desc'])): ?>
            <p class="ev-option__desc"><?= htmlspecialchars($c['ev_opt2_desc']) ?></p>
            <?php endif; ?>
            <div class="ev-option__ctas">
                <a href="<?= $base ?>pages/eventos/a-tu-medida/" class="btn-primary ev-option__cta">
                    <?= htmlspecialchars($c['ev_opt2_cta_primary'] ?? 'Descubre tus posibilidades') ?> <span aria-hidden="true">→</span>
                </a>
                <a href="<?= $base ?>pages/contacto/" class="ev-option__cta ev-option__cta--ghost">
                    <?= htmlspecialchars($c['ev_opt2_cta_secondary'] ?? 'Contáctanos') ?>
                </a>
            </div>
        </article>

    </div>
</section>

<!-- ── PRUEBA SOCIAL (testimonios carrusel + logos) ──────────────────────── -->
<?php if (!empty($testimonios) || !empty($logos_imgs)): ?>
<section class="ev-social" id="confian">
    <div class="ev-social__inner">
        <?php if (!empty($c['ev_social_label'])): ?>
        <span class="section-label ev-social__label"><?= htmlspecialchars($c['ev_social_label']) ?></span>
        <?php endif; ?>

        <?php if (!empty($testimonios)): ?>
        <div class="ev-social__carousel" data-count="<?= count($testimonios) ?>">
            <?php if (count($testimonios) > 1): ?>
            <button type="button" class="ev-social__nav ev-social__nav--prev" data-dir="-1" aria-label="Anterior">‹</button>
            <button type="button" class="ev-social__nav ev-social__nav--next" data-dir="1"  aria-label="Siguiente">›</button>
            <?php endif; ?>

            <div class="ev-social__card">
                <span class="ev-social__mark" aria-hidden="true">“</span>
                <div class="ev-social__slides">
                    <?php foreach ($testimonios as $i => $t): ?>
                    <blockquote class="ev-social__slide <?= $i === 0 ? 'is-active' : '' ?>">
                        <p><?= htmlspecialchars($t['quote']) ?></p>
                        <?php if (!empty($t['author']) || !empty($t['role'])): ?>
                        <footer class="ev-social__cite">
                            <?php if (!empty($t['author'])): ?>
                            <strong><?= htmlspecialchars($t['author']) ?></strong>
                            <?php endif; ?>
                            <?php if (!empty($t['role'])): ?>
                            <span><?= htmlspecialchars($t['role']) ?></span>
                            <?php endif; ?>
                        </footer>
                        <?php endif; ?>
                    </blockquote>
                    <?php endforeach; ?>
                </div>
            </div>

            <?php if (count($testimonios) > 1): ?>
            <div class="ev-social__dots" role="tablist" aria-label="Testimonios">
                <?php foreach ($testimonios as $i => $_): ?>
                <button type="button" class="ev-social__dot <?= $i === 0 ? 'is-active' : '' ?>"
                        role="tab" aria-label="Testimonio <?= $i + 1 ?>"
                        data-idx="<?= $i ?>"></button>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <?php if (!empty($logos_imgs)): ?>
        <div class="ev-social__logos">
            <?php foreach ($logos_imgs as $logo_path): ?>
            <img src="<?= $base ?>assets/img/eventos/logos/<?= htmlspecialchars(basename($logo_path)) ?>"
                 alt="" loading="lazy">
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</section>
<?php endif; ?>

<!-- ── CTA ────────────────────────────────────────────────────────────────── -->
<section class="ev-cta<?= $cta_img ? ' ev-cta--with-image' : '' ?>"
         <?= $cta_img ? 'style="--ev-cta-img: url(\'' . $base . 'assets/img/eventos/cta-fondo/' . htmlspecialchars($cta_img, ENT_QUOTES) . '\');"' : '' ?>>
    <?php if ($cta_img): ?>
    <div class="ev-cta__overlay" aria-hidden="true"></div>
    <?php endif; ?>
    <div class="ev-cta__inner">
        <h2><?= htmlspecialchars($c['ev_cta_h2'] ?? '¿Tienes un evento en mente?') ?></h2>
        <p><?= htmlspecialchars($c['ev_cta_text'] ?? 'Cuéntanos cómo lo imaginas y diseñamos el menú a tu medida.') ?></p>
        <a href="<?= $base ?>pages/contacto/" class="btn-primary ev-cta__btn">
            <?= htmlspecialchars($c['ev_cta_btn'] ?? 'Hablamos →') ?>
        </a>
    </div>
</section>

<!-- ── BANNER MARQUEE CATEGORÍAS ─────────────────────────────────────────── -->
<?php if (!empty($marquee_items)): ?>
<div class="ev-slogan" aria-hidden="true">
    <div class="ev-slogan__track">
        <?php foreach ([1, 2] as $_): ?>
        <span class="ev-slogan__group">
            <?php foreach ($marquee_items as $item): ?>
            <span class="ev-slogan__item"><?= htmlspecialchars($item) ?></span>
            <span class="ev-slogan__sep">–</span>
            <?php endforeach; ?>
        </span>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

</main>

<?php require $base . 'includes/footer.php'; ?>
