<?php
$base         = '../../';
$current_page = 'eventos';
require $base . 'config/conexion.php';
require $base . 'config/content_helper.php';
require_once $base . 'config/lang.php';
$page_title = $lang === 'en' ? 'Events | TUOI' : 'Eventos | TUOI';

// ── Contact form handler ────────────────────────────────────────────────────
$contact_success = false;
$contact_error   = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['contact_submit'])) {
    $name    = trim($_POST['c_name']    ?? '');
    $email   = trim($_POST['c_email']   ?? '');
    $phone   = trim($_POST['c_phone']   ?? '');
    $message = trim($_POST['c_message'] ?? '');

    if ($name !== '' && $email !== '' && $message !== '' && filter_var($email, FILTER_VALIDATE_EMAIL)) {
        if ($conexion) {
            $n = mysqli_real_escape_string($conexion, $name);
            $e = mysqli_real_escape_string($conexion, $email);
            $p = mysqli_real_escape_string($conexion, $phone);
            $m = mysqli_real_escape_string($conexion, $message);
            @mysqli_query($conexion,
                "INSERT INTO contact_submissions (name, email, phone, message, source_page)
                 VALUES ('$n','$e','$p','$m','eventos')"
            );
        }
        $contact_success = true;
        $_POST = [];
    } else {
        $contact_error = true;
    }
}

require $base . 'includes/header.php';
$c = load_site_content($conexion, $lang);

// ── Load posts by category ──────────────────────────────────────────────────
$cats_order = ['eventos', 'networking', 'team-building', 'catering'];
$posts_by   = [];

if ($conexion) {
    foreach ($cats_order as $cat) {
        $s = mysqli_real_escape_string($conexion, $cat);
        $r = @mysqli_query($conexion,
            "SELECT * FROM eventos_posts WHERE category='$s' ORDER BY sort_order ASC, id DESC"
        );
        $posts_by[$cat] = [];
        if ($r) while ($row = mysqli_fetch_assoc($r)) $posts_by[$cat][] = $row;
    }
}

// ── Section definitions ─────────────────────────────────────────────────────
$sections = [
    [
        'id'      => 'eventos',
        'accent'  => 'naranja',
        'num'     => '01',
        'label'   => $c['ev_ev_label']  ?? 'Eventos',
        'h2'      => $c['ev_ev_h2']     ?? 'Tu evento, nuestro escenario',
        'desc'    => $c['ev_ev_desc']   ?? 'Organizamos todo tipo de celebraciones y eventos especiales con una propuesta culinaria funcional y memorable.',
    ],
    [
        'id'      => 'networking',
        'accent'  => 'morado',
        'num'     => '02',
        'label'   => $c['ev_nw_label']  ?? 'Networking',
        'h2'      => $c['ev_nw_h2']     ?? 'Conecta mientras cuidas de ti',
        'desc'    => $c['ev_nw_desc']   ?? 'Espacios y propuestas pensadas para que tus eventos de networking sean tan energizantes como productivos.',
    ],
    [
        'id'      => 'team-building',
        'accent'  => 'verde',
        'num'     => '03',
        'label'   => $c['ev_tb_label']  ?? 'Team Building',
        'h2'      => $c['ev_tb_h2']     ?? 'Team building con propósito',
        'desc'    => $c['ev_tb_desc']   ?? 'Experiencias de bienestar y cohesión de equipo basadas en alimentación funcional.',
    ],
    [
        'id'      => 'catering',
        'accent'  => 'amarillo',
        'num'     => '04',
        'label'   => $c['ev_cat_label'] ?? 'Catering',
        'h2'      => $c['ev_cat_h2']    ?? 'Catering funcional y saludable',
        'desc'    => $c['ev_cat_desc']  ?? 'Menús a medida para cualquier tipo de evento. Siempre funcional, siempre delicioso.',
    ],
];
?>

<main>

<!-- ── HERO ──────────────────────────────────────────────────────────────── -->
<section class="page-hero ev-hero">
    <span class="section-label"><?= htmlspecialchars($c['ev_hero_label'] ?? 'Eventos · TUOI') ?></span>
    <h1><?= htmlspecialchars($c['ev_hero_h1'] ?? 'Celebra con nosotros') ?></h1>
    <p><?= htmlspecialchars($c['ev_hero_sub'] ?? 'Organizamos eventos únicos con comida funcional y saludable.') ?></p>
    <div class="ev-hero__pills">
        <?php foreach ($sections as $s): ?>
        <a href="#<?= $s['id'] ?>" class="ev-hero__pill ev-hero__pill--<?= $s['accent'] ?>">
            <?= htmlspecialchars($s['label']) ?>
        </a>
        <?php endforeach; ?>
    </div>
</section>

<!-- ── SUBNAV ────────────────────────────────────────────────────────────── -->
<nav class="ev-subnav" aria-label="Secciones">
    <?php foreach ($sections as $s): ?>
    <a href="#<?= $s['id'] ?>" class="ev-subnav__link"><?= htmlspecialchars($s['label']) ?></a>
    <?php endforeach; ?>
    <a href="#contacto" class="ev-subnav__link ev-subnav__link--contact">Contacto</a>
</nav>

<!-- ── SECTIONS ──────────────────────────────────────────────────────────── -->
<?php foreach ($sections as $idx => $sec):
    $posts   = $posts_by[$sec['id']] ?? [];
    $alt_bg  = $idx % 2 === 0 ? '' : ' ev-section--alt';
?>
<section id="<?= $sec['id'] ?>" class="ev-section<?= $alt_bg ?>">
    <div class="ev-section__wrap">

        <!-- Header de sección -->
        <div class="ev-section__head ev-section__head--<?= $sec['accent'] ?>">
            <span class="ev-section__num" aria-hidden="true"><?= $sec['num'] ?></span>
            <div class="ev-section__head-text">
                <span class="section-label"><?= htmlspecialchars($sec['label']) ?></span>
                <h2><?= htmlspecialchars($sec['h2']) ?></h2>
                <p><?= htmlspecialchars($sec['desc']) ?></p>
            </div>
        </div>

        <!-- Posts -->
        <?php if (!empty($posts)): ?>
        <div class="ev-posts">
            <?php foreach ($posts as $i => $post):
                $has_img = !empty($post['image_filename']);
                $reverse = $i % 2 !== 0 ? ' ev-post--reverse' : '';
            ?>
            <article class="ev-post<?= $reverse ?><?= $has_img ? '' : ' ev-post--text-only' ?>">

                <?php if ($has_img): ?>
                <div class="ev-post__img">
                    <img src="<?= $base ?>assets/img/eventos/<?= htmlspecialchars($sec['id']) ?>/<?= htmlspecialchars($post['image_filename']) ?>"
                         alt="<?= htmlspecialchars($post['title']) ?>"
                         loading="lazy">
                </div>
                <?php endif; ?>

                <div class="ev-post__text<?= $has_img ? '' : ' ev-post__text--full' ?>">
                    <h3><?= htmlspecialchars($post['title']) ?></h3>
                    <?php if (!empty($post['body'])): ?>
                    <p><?= nl2br(htmlspecialchars($post['body'])) ?></p>
                    <?php endif; ?>
                </div>

            </article>
            <?php endforeach; ?>
        </div>

        <?php else: ?>
        <div class="ev-empty">
            <p>Próximamente publicaremos más sobre esta modalidad.<br>¡Escríbenos para conocer todas las opciones!</p>
            <a href="#contacto" class="btn-secondary" style="margin-top:1.25rem;display:inline-block;">Contactar →</a>
        </div>
        <?php endif; ?>

    </div>
</section>
<?php endforeach; ?>

<!-- ── CONTACTO ──────────────────────────────────────────────────────────── -->
<section class="ev-contact" id="contacto">
    <div class="ev-contact__inner">

        <div class="ev-contact__info">
            <span class="section-label ev-contact__label"><?= t('ev_contact_title') ?></span>
            <h2>¿Hablamos?</h2>
            <p>Cuéntanos tu proyecto y diseñamos juntos el evento perfecto.</p>

            <ul class="ev-contact__list">
                <li>
                    <span class="ev-contact__icon">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 12a19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 3.6 1.16h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L7.91 8.75a16 16 0 0 0 8.34 8.34l.96-.96a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                    </span>
                    <a href="tel:<?= htmlspecialchars(preg_replace('/[\s\-()]/', '', $c['contact_phone'] ?? '')) ?>">
                        <?= htmlspecialchars($c['contact_phone'] ?? '+34 000 000 000') ?>
                    </a>
                </li>
                <li>
                    <span class="ev-contact__icon">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                    </span>
                    <a href="mailto:<?= htmlspecialchars($c['contact_email'] ?? 'hola@tuoi.es') ?>">
                        <?= htmlspecialchars($c['contact_email'] ?? 'hola@tuoi.es') ?>
                    </a>
                </li>
                <li>
                    <span class="ev-contact__icon">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                    </span>
                    <span><?= htmlspecialchars($c['contact_address'] ?? 'C. de la Travesía, 15B, Valencia') ?></span>
                </li>
            </ul>
        </div>

        <div class="ev-contact__form-wrap">
            <?php if ($contact_success): ?>
            <div class="ev-form-notice ev-form-notice--ok"><?= t('ev_contact_ok') ?></div>
            <?php elseif ($contact_error): ?>
            <div class="ev-form-notice ev-form-notice--err"><?= t('ev_contact_err') ?></div>
            <?php endif; ?>

            <form class="ev-form" method="post" action="#contacto">
                <input type="hidden" name="contact_submit" value="1">
                <div class="ev-form__row ev-form__row--half">
                    <div class="ev-form__group">
                        <label for="c_name"><?= t('ev_contact_name') ?> *</label>
                        <input id="c_name" name="c_name" type="text" required
                               placeholder="Tu nombre"
                               value="<?= htmlspecialchars($_POST['c_name'] ?? '') ?>">
                    </div>
                    <div class="ev-form__group">
                        <label for="c_email"><?= t('ev_contact_email') ?> *</label>
                        <input id="c_email" name="c_email" type="email" required
                               placeholder="tu@email.com"
                               value="<?= htmlspecialchars($_POST['c_email'] ?? '') ?>">
                    </div>
                </div>
                <div class="ev-form__group">
                    <label for="c_phone"><?= t('ev_contact_phone') ?></label>
                    <input id="c_phone" name="c_phone" type="tel"
                           placeholder="+34 600 000 000"
                           value="<?= htmlspecialchars($_POST['c_phone'] ?? '') ?>">
                </div>
                <div class="ev-form__group">
                    <label for="c_message"><?= t('ev_contact_msg') ?> *</label>
                    <textarea id="c_message" name="c_message" rows="5" required
                              placeholder="Cuéntanos en qué podemos ayudarte..."><?= htmlspecialchars($_POST['c_message'] ?? '') ?></textarea>
                </div>
                <button type="submit" class="btn-primary ev-form__btn">
                    <?= t('ev_contact_send') ?> <span aria-hidden="true">→</span>
                </button>
            </form>
        </div>

    </div>
</section>

</main>

<?php require $base . 'includes/footer.php'; ?>
