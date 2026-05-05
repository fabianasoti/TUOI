<?php
$base         = '../../';
$current_page = 'eventos';
require $base . 'config/conexion.php';
require $base . 'config/content_helper.php';
require_once $base . 'config/lang.php';
$page_title = $lang === 'en' ? 'Events | TUOI' : 'Eventos | TUOI';

// ── Contact form handler ────────────────────────────────────────────────────
$contact_success = false;
$contact_errors  = []; // field => translation key

$is_ajax = (
    !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
    strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest'
);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['contact_submit'])) {
    // Need lang loaded early for AJAX response
    require_once $base . 'config/lang.php';

    // ── Honeypot anti-spam ──
    // Campo oculto que los humanos no ven. Si llega con valor, es un bot:
    // simulamos éxito silenciosamente (no guardamos, no enviamos email).
    $is_bot = !empty(trim($_POST['c_website'] ?? ''));

    $name    = trim($_POST['c_name']    ?? '');
    $email   = trim($_POST['c_email']   ?? '');
    $phone   = trim($_POST['c_phone']   ?? '');
    $message = trim($_POST['c_message'] ?? '');
    $consent = isset($_POST['c_consent']);

    if (!$is_bot) {
        if ($name === '')                                  $contact_errors['c_name']    = 'ev_form_required';
        if ($email === '')                                 $contact_errors['c_email']   = 'ev_form_required';
        elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) $contact_errors['c_email']   = 'ev_form_email_bad';
        if ($message === '')                               $contact_errors['c_message'] = 'ev_form_required';
        if (!$consent)                                     $contact_errors['c_consent'] = 'ev_contact_consent_err';
    }

    if ($is_bot) {
        // Fingimos éxito al bot pero no procesamos nada.
        $contact_success = true;
        $_POST = [];
    } elseif (empty($contact_errors)) {
        if ($conexion) {
            // Asegura las columnas RGPD en instalaciones existentes (idempotente).
            try { @mysqli_query($conexion, "ALTER TABLE contact_submissions ADD COLUMN consent_at DATETIME NULL DEFAULT NULL AFTER source_page"); } catch (\Throwable $e) {}
            try { @mysqli_query($conexion, "ALTER TABLE contact_submissions ADD COLUMN consent_ip VARCHAR(45) NULL DEFAULT NULL AFTER consent_at"); } catch (\Throwable $e) {}

            $n  = mysqli_real_escape_string($conexion, $name);
            $e  = mysqli_real_escape_string($conexion, $email);
            $p  = mysqli_real_escape_string($conexion, $phone);
            $m  = mysqli_real_escape_string($conexion, $message);
            $ip = mysqli_real_escape_string($conexion, $_SERVER['REMOTE_ADDR'] ?? '');
            try {
                mysqli_query($conexion,
                    "INSERT INTO contact_submissions (name, email, phone, message, source_page, consent_at, consent_ip)
                     VALUES ('$n','$e','$p','$m','eventos', NOW(), '$ip')"
                );
            } catch (\Throwable $e) {
                // Fallback: instalación antigua sin columnas de consentimiento.
                @mysqli_query($conexion,
                    "INSERT INTO contact_submissions (name, email, phone, message, source_page)
                     VALUES ('$n','$e','$p','$m','eventos')"
                );
            }
        }

        $admin_email  = !empty($c['contact_email']) ? $c['contact_email'] : 'hola@miobiosport.com';
        $mail_subject = '=?UTF-8?B?' . base64_encode('Nuevo contacto desde Eventos · TUOI') . '?=';
        $mail_body    = "Has recibido un nuevo mensaje desde el formulario de Eventos.\n\n";
        $mail_body   .= "Nombre:    $name\n";
        $mail_body   .= "Email:     $email\n";
        $mail_body   .= "Teléfono:  " . ($phone ?: '—') . "\n\n";
        $mail_body   .= "Mensaje:\n$message\n";
        $mail_headers  = "From: TUOI Eventos <noreply@tuoi.es>\r\n";
        $mail_headers .= "Reply-To: $email\r\n";
        $mail_headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

        // En local guardamos el correo en un fichero (no hay MTA). En producción usamos mail().
        $host = $_SERVER['HTTP_HOST'] ?? '';
        $is_local = str_contains($host, 'localhost') || str_starts_with($host, '127.') || str_contains($host, '.local');
        if ($is_local) {
            $log_path = dirname(__DIR__, 2) . '/logs/mail.log';
            @mkdir(dirname($log_path), 0775, true);
            $entry  = "==== " . date('Y-m-d H:i:s') . " ====\n";
            $entry .= "To:      $admin_email\n";
            $entry .= "Subject: Nuevo contacto desde Eventos · TUOI\n";
            $entry .= "Headers:\n$mail_headers\n";
            $entry .= "Body:\n$mail_body\n\n";
            // Intenta escribir en /logs/mail.log; si no hay permisos, manda al error_log de PHP/Apache.
            if (@file_put_contents($log_path, $entry, FILE_APPEND) === false) {
                error_log("[TUOI mail simulado]\n" . $entry);
            }
        } else {
            @mail($admin_email, $mail_subject, $mail_body, $mail_headers);
        }

        $contact_success = true;
        $_POST = [];
    }

    // AJAX: respond with JSON and exit. Non-AJAX: continue to render page.
    if ($is_ajax) {
        header('Content-Type: application/json; charset=utf-8');
        if ($contact_success) {
            echo json_encode(['ok' => true, 'message' => t('ev_contact_ok')]);
        } else {
            $errors_translated = [];
            foreach ($contact_errors as $field => $key) {
                $errors_translated[$field] = t($key);
            }
            echo json_encode(['ok' => false, 'errors' => $errors_translated]);
        }
        exit;
    }
}

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

// ── Testimonios (carrusel) ─────────────────────────────────────────────────
$testimonios = [];
if ($conexion) {
    $res = @mysqli_query($conexion,
        "SELECT quote, author, role FROM testimonios WHERE active = 1 ORDER BY sort_order ASC, id DESC"
    );
    if ($res) while ($row = mysqli_fetch_assoc($res)) $testimonios[] = $row;
}
// Fallback al testimonio en site_content si la tabla está vacía
if (empty($testimonios) && !empty($c['ev_social_quote'])) {
    $testimonios[] = [
        'quote'  => $c['ev_social_quote'],
        'author' => $c['ev_social_author'] ?? '',
        'role'   => $c['ev_social_role']   ?? '',
    ];
}

// ── Posts por categoría ─────────────────────────────────────────────────────
$cats_order = ['coffee-break', 'brunch', 'tardeo'];
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

// ── Definición de secciones ─────────────────────────────────────────────────
$sections = [
    [
        'id'     => 'coffee-break',
        'accent' => 'amarillo',
        'num'    => '01',
        'label'  => $c['ev_cb_label'] ?? 'Coffee Break',
        'h2'     => $c['ev_cb_h2']    ?? 'Coffee Break',
        'desc'   => $c['ev_cb_desc']  ?? '',
    ],
    [
        'id'     => 'brunch',
        'accent' => 'verde',
        'num'    => '02',
        'label'  => $c['ev_br_label'] ?? 'Brunch',
        'h2'     => $c['ev_br_h2']    ?? 'Brunch',
        'desc'   => $c['ev_br_desc']  ?? '',
    ],
    [
        'id'     => 'tardeo',
        'accent' => 'morado',
        'num'    => '03',
        'label'  => $c['ev_td_label'] ?? 'Tardeo',
        'h2'     => $c['ev_td_h2']    ?? 'Tardeo',
        'desc'   => $c['ev_td_desc']  ?? '',
    ],
];

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
                    $icon  = $c["ev_why_b{$i}_icon"]  ?? '';
                    $title = $c["ev_why_b{$i}_title"] ?? '';
                    $desc  = $c["ev_why_b{$i}_desc"]  ?? '';
                    if ($title === '' && $desc === '') continue;
                ?>
                <li class="ev-why__bullet">
                    <?php if ($icon !== ''): ?>
                    <span class="ev-why__icon"><?= htmlspecialchars($icon) ?></span>
                    <?php endif; ?>
                    <div>
                        <?php if ($title !== ''): ?><h3><?= htmlspecialchars($title) ?></h3><?php endif; ?>
                        <?php if ($desc  !== ''): ?><p><?= nl2br(htmlspecialchars($desc)) ?></p><?php endif; ?>
                    </div>
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

<!-- ── INTRO PROPUESTA DE MENÚS ───────────────────────────────────────────── -->
<div class="ev-menus-intro">
    <div class="ev-menus-intro__inner">
        <span class="section-label"><?= htmlspecialchars($c['ev_menus_label'] ?? 'Propuesta de menús') ?></span>
        <h2><?= htmlspecialchars($c['ev_menus_h2'] ?? 'Menús de grupo y catering') ?></h2>
        <?php if (!empty($c['ev_menus_intro'])): ?>
        <p><?= htmlspecialchars($c['ev_menus_intro']) ?></p>
        <?php endif; ?>
    </div>
</div>

<!-- ── SECCIONES (zigzag) ─────────────────────────────────────────────────── -->
<?php foreach ($sections as $idx => $sec):
    $posts  = $posts_by[$sec['id']] ?? [];
    $alt_bg = $idx % 2 === 0 ? '' : ' ev-section--alt';
?>
<section id="<?= $sec['id'] ?>" class="ev-section<?= $alt_bg ?>">
    <div class="ev-section__wrap">

        <div class="ev-section__head ev-section__head--<?= $sec['accent'] ?>">
            <span class="ev-section__num" aria-hidden="true"><?= $sec['num'] ?></span>
            <div class="ev-section__head-text">
                <span class="section-label"><?= htmlspecialchars($sec['label']) ?></span>
                <h2><?= htmlspecialchars($sec['h2']) ?></h2>
                <?php if ($sec['desc'] !== ''): ?><p><?= htmlspecialchars($sec['desc']) ?></p><?php endif; ?>
            </div>
        </div>

        <?php if (!empty($posts)): ?>
        <div class="ev-posts">
            <?php foreach ($posts as $i => $post):
                $isReverse = ($i % 2 !== 0) ? 'row-reverse' : '';
                $raw_imgs  = $post['images'] ?? ($post['image_filename'] ?? '');
                $images    = [];
                if (!empty($raw_imgs)) {
                    $decoded = json_decode($raw_imgs, true);
                    $images  = (json_last_error() === JSON_ERROR_NONE && is_array($decoded))
                        ? $decoded
                        : array_map('trim', explode(',', $raw_imgs));
                }
                $trackId = 'track-' . ($post['id'] ?? $i);
            ?>
            <div class="project-row <?= $isReverse ?>">
                <div class="project-image">
                    <?php if (count($images) > 1): ?>
                        <div class="carousel-container">
                            <div class="carousel-track" id="<?= $trackId ?>">
                                <?php foreach ($images as $img): ?>
                                    <img src="<?= $base ?>assets/img/eventos/<?= htmlspecialchars($sec['id']) ?>/<?= htmlspecialchars($img) ?>"
                                         alt="<?= htmlspecialchars($post['title']) ?>" loading="lazy">
                                <?php endforeach; ?>
                            </div>
                            <button class="carousel-btn btn-prev" onclick="moveSlide('<?= $trackId ?>', -1)">❮</button>
                            <button class="carousel-btn btn-next" onclick="moveSlide('<?= $trackId ?>',  1)">❯</button>
                            <div class="carousel-dots" id="dots-<?= $trackId ?>">
                                <?php foreach ($images as $di => $img): ?>
                                    <div class="dot <?= $di === 0 ? 'active' : '' ?>"></div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php elseif (count($images) === 1): ?>
                        <img src="<?= $base ?>assets/img/eventos/<?= htmlspecialchars($sec['id']) ?>/<?= htmlspecialchars($images[0]) ?>"
                             alt="<?= htmlspecialchars($post['title']) ?>"
                             style="width:100%;border-radius:12px;object-fit:cover;aspect-ratio:16/9;" loading="lazy">
                    <?php else: ?>
                        <div style="aspect-ratio:16/9;display:flex;align-items:center;justify-content:center;background:#e8e6df;border-radius:12px;color:#aaa;font-size:2rem;">📸</div>
                    <?php endif; ?>
                </div>

                <div class="project-text">
                    <h3><?= htmlspecialchars($post['title']) ?></h3>
                    <?php if (!empty($post['body'])): ?>
                        <p><?= nl2br(htmlspecialchars($post['body'])) ?></p>
                    <?php endif; ?>
                    <?php if (!empty($post['tags'] ?? $post['tech'] ?? '')): ?>
                        <div class="tech"><?= htmlspecialchars($post['tags'] ?? $post['tech']) ?></div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <?php else: ?>
        <div class="ev-empty">
            <p><?= t_raw('ev_empty_text') ?></p>
            <a href="#contacto" class="btn-primary" style="margin-top:1.25rem;display:inline-block;"><?= t('ev_empty_btn') ?></a>
        </div>
        <?php endif; ?>

    </div>
</section>
<?php endforeach; ?>

<!-- ── CTA ────────────────────────────────────────────────────────────────── -->
<section class="ev-cta">
    <div class="ev-cta__inner">
        <h2><?= htmlspecialchars($c['ev_cta_h2'] ?? '¿Tienes un evento en mente?') ?></h2>
        <p><?= htmlspecialchars($c['ev_cta_text'] ?? 'Cuéntanos cómo lo imaginas y diseñamos el menú a tu medida.') ?></p>
        <a href="#contacto" class="btn-primary ev-cta__btn">
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

<!-- ── CONTACTO ──────────────────────────────────────────────────────────── -->
<section class="ev-contact" id="contacto">
    <div class="ev-contact__inner">

        <div class="ev-contact__info">
            <span class="section-label ev-contact__label"><?= t('ev_contact_title') ?></span>
            <h2><?= t('ev_contact_h2') ?></h2>
            <p><?= t('ev_contact_lead') ?></p>

            <ul class="ev-contact__list">
                <li>
                    <span class="ev-contact__icon">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 12a19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 3.6 1.16h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L7.91 8.75a16 16 0 0 0 8.34 8.34l.96-.96a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                    </span>
                    <a href="tel:<?= htmlspecialchars(preg_replace('/[\s\-()]/', '', $c['contact_phone'] ?? '')) ?>">
                        <?= htmlspecialchars($c['contact_phone'] ?? '') ?>
                    </a>
                </li>
                <li>
                    <span class="ev-contact__icon">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                    </span>
                    <a href="mailto:<?= htmlspecialchars($c['contact_email'] ?? '') ?>">
                        <?= htmlspecialchars($c['contact_email'] ?? '') ?>
                    </a>
                </li>
                <li>
                    <span class="ev-contact__icon">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                    </span>
                    <span><?= htmlspecialchars($c['contact_address'] ?? '') ?></span>
                </li>
            </ul>
        </div>

        <div class="ev-contact__form-wrap">
            <?php
                $err = function($field) use ($contact_errors) {
                    return isset($contact_errors[$field]) ? t($contact_errors[$field]) : '';
                };
            ?>

            <div class="ev-form-success" role="status" aria-live="polite" <?= $contact_success ? '' : 'hidden' ?>>
                <p class="ev-form-success__msg"><?= t('ev_contact_ok') ?></p>
                <button type="button" class="ev-form-success__again">
                    <?= t('ev_contact_send_another') ?>
                </button>
            </div>

            <form class="ev-form<?= $contact_success ? ' is-hidden' : '' ?>"
                  method="post" action="#contacto" novalidate
                  data-msg-required="<?= t('ev_form_required') ?>"
                  data-msg-email="<?= t('ev_form_email_bad') ?>"
                  data-msg-consent="<?= t('ev_contact_consent_err') ?>">
                <input type="hidden" name="contact_submit" value="1">
                <!-- Honeypot anti-spam: oculto para humanos, los bots lo rellenan -->
                <div class="ev-form__hp" aria-hidden="true">
                    <label for="c_website">Website</label>
                    <input id="c_website" name="c_website" type="text" tabindex="-1" autocomplete="off">
                </div>
                <div class="ev-form__row ev-form__row--half">
                    <div class="ev-form__group">
                        <label for="c_name"><?= t('ev_contact_name') ?> *</label>
                        <p class="ev-form__error" data-error-for="c_name" <?= $err('c_name') ? '' : 'hidden' ?>><?= $err('c_name') ?></p>
                        <input id="c_name" name="c_name" type="text"
                               placeholder="<?= t('ev_form_ph_name') ?>"
                               value="<?= htmlspecialchars($_POST['c_name'] ?? '') ?>"
                               aria-describedby="err-c_name">
                    </div>
                    <div class="ev-form__group">
                        <label for="c_email"><?= t('ev_contact_email') ?> *</label>
                        <p class="ev-form__error" data-error-for="c_email" <?= $err('c_email') ? '' : 'hidden' ?>><?= $err('c_email') ?></p>
                        <input id="c_email" name="c_email" type="email"
                               placeholder="<?= t('ev_form_ph_email') ?>"
                               value="<?= htmlspecialchars($_POST['c_email'] ?? '') ?>">
                    </div>
                </div>
                <div class="ev-form__group">
                    <label for="c_phone"><?= t('ev_contact_phone') ?></label>
                    <input id="c_phone" name="c_phone" type="tel"
                           placeholder="<?= t('ev_form_ph_phone') ?>"
                           value="<?= htmlspecialchars($_POST['c_phone'] ?? '') ?>">
                </div>
                <div class="ev-form__group">
                    <label for="c_message"><?= t('ev_contact_msg') ?> *</label>
                    <p class="ev-form__error" data-error-for="c_message" <?= $err('c_message') ? '' : 'hidden' ?>><?= $err('c_message') ?></p>
                    <textarea id="c_message" name="c_message" rows="5"
                              placeholder="<?= t('ev_form_ph_msg') ?>"><?= htmlspecialchars($_POST['c_message'] ?? '') ?></textarea>
                </div>
                <p class="ev-form__error" data-error-for="c_consent" <?= $err('c_consent') ? '' : 'hidden' ?>><?= $err('c_consent') ?></p>
                <label class="ev-form__consent<?= $err('c_consent') ? ' has-error' : '' ?>">
                    <input type="checkbox" name="c_consent" value="1"
                           <?= !empty($_POST['c_consent']) ? 'checked' : '' ?>>
                    <span><?= t_raw('ev_contact_consent') ?></span>
                </label>
                <button type="submit" class="btn-primary ev-form__btn">
                    <?= t('ev_contact_send') ?> <span aria-hidden="true">→</span>
                </button>
            </form>
            <script>
            (function () {
                var form    = document.querySelector('.ev-form');
                var success = document.querySelector('.ev-form-success');
                if (!form || !success) return;

                var msgRequired = form.dataset.msgRequired || '';
                var msgEmail    = form.dataset.msgEmail    || '';
                var msgConsent  = form.dataset.msgConsent  || '';

                function showError(field, msg) {
                    var p = form.querySelector('[data-error-for="' + field + '"]');
                    if (!p) return;
                    p.textContent = msg;
                    p.hidden = !msg;
                    var input = form.querySelector('[name="' + field + '"]');
                    if (input) input.classList.toggle('is-invalid', !!msg);
                }
                function clearErrors() {
                    form.querySelectorAll('[data-error-for]').forEach(function (p) {
                        p.textContent = '';
                        p.hidden = true;
                    });
                    form.querySelectorAll('.is-invalid').forEach(function (el) {
                        el.classList.remove('is-invalid');
                    });
                }

                function clientValidate() {
                    clearErrors();
                    var ok = true;
                    var name    = form.elements['c_name'];
                    var email   = form.elements['c_email'];
                    var msg     = form.elements['c_message'];
                    var consent = form.elements['c_consent'];

                    if (!name.value.trim())    { showError('c_name', msgRequired); ok = false; }
                    if (!email.value.trim())   { showError('c_email', msgRequired); ok = false; }
                    else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.value.trim())) { showError('c_email', msgEmail); ok = false; }
                    if (!msg.value.trim())     { showError('c_message', msgRequired); ok = false; }
                    if (!consent.checked)      { showError('c_consent', msgConsent); ok = false; }
                    return ok;
                }

                form.addEventListener('submit', function (ev) {
                    ev.preventDefault();
                    if (!clientValidate()) {
                        var firstErr = form.querySelector('.ev-form__error:not([hidden])');
                        if (firstErr) firstErr.scrollIntoView({behavior:'smooth', block:'center'});
                        return;
                    }
                    var btn = form.querySelector('button[type="submit"]');
                    if (btn) btn.disabled = true;

                    fetch(form.action.split('#')[0], {
                        method: 'POST',
                        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
                        body: new FormData(form)
                    })
                    .then(function (r) { return r.json(); })
                    .then(function (data) {
                        if (data && data.ok) {
                            form.reset();
                            form.classList.add('is-hidden');
                            success.hidden = false;
                            // small delay so the fade is visible
                            setTimeout(function () { success.classList.add('is-visible'); }, 20);
                        } else if (data && data.errors) {
                            Object.keys(data.errors).forEach(function (k) {
                                showError(k, data.errors[k]);
                            });
                            var firstErr = form.querySelector('.ev-form__error:not([hidden])');
                            if (firstErr) firstErr.scrollIntoView({behavior:'smooth', block:'center'});
                        }
                    })
                    .catch(function () {
                        // Network error: fall back to native submit so user sees something
                        form.submit();
                    })
                    .finally(function () {
                        if (btn) btn.disabled = false;
                    });
                });

                // Clear field error as user types/changes
                form.querySelectorAll('input, textarea').forEach(function (el) {
                    var clear = function () { showError(el.name, ''); };
                    el.addEventListener('input',  clear);
                    el.addEventListener('change', clear);
                });

                // "Send another" button
                success.querySelector('.ev-form-success__again').addEventListener('click', function () {
                    success.classList.remove('is-visible');
                    setTimeout(function () {
                        success.hidden = true;
                        form.classList.remove('is-hidden');
                        var first = form.querySelector('input[name="c_name"]');
                        if (first) first.focus();
                    }, 250);
                });

                // If page rendered with success state (no-JS fallback), animate it in.
                if (!success.hidden) {
                    setTimeout(function () { success.classList.add('is-visible'); }, 20);
                }
            })();
            </script>
        </div>

    </div>
</section>

</main>

<?php require $base . 'includes/footer.php'; ?>
