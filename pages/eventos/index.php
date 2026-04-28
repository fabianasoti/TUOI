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

        $admin_email = $c['contact_email'] ?? 'hola@tuoi.es';
        $mail_subject = '=?UTF-8?B?' . base64_encode('Nuevo contacto desde Eventos · TUOI') . '?=';
        $mail_body  = "Has recibido un nuevo mensaje desde el formulario de Eventos.\n\n";
        $mail_body .= "Nombre:    $name\n";
        $mail_body .= "Email:     $email\n";
        $mail_body .= "Teléfono:  " . ($phone ?: '—') . "\n\n";
        $mail_body .= "Mensaje:\n$message\n";
        $mail_headers  = "From: TUOI Eventos <noreply@tuoi.es>\r\n";
        $mail_headers .= "Reply-To: $email\r\n";
        $mail_headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
        @mail($admin_email, $mail_subject, $mail_body, $mail_headers);

        $contact_success = true;
        $_POST = [];
    } else {
        $contact_error = true;
    }
}

require $base . 'includes/header.php';
$c = load_site_content($conexion, $lang);

// ── Load carousel images ────────────────────────────────────────────────────
$carrusel_dir   = dirname(__DIR__, 2) . '/assets/img/eventos/carrusel/';
$carrusel_files = load_ordered_images($conexion, 'eventos/carrusel', $carrusel_dir);
$carrusel_imgs  = array_map(fn($p) => basename($p), $carrusel_files);

// ── Load "Por qué TUOI" lateral image (first image in folder) ──────────────
$why_dir   = dirname(__DIR__, 2) . '/assets/img/eventos/por-que-tuoi/';
$why_files = load_ordered_images($conexion, 'eventos/por-que-tuoi', $why_dir);
$why_img   = !empty($why_files) ? basename($why_files[0]) : null;

// ── Menu sections (zigzag posts) ───────────────────────────────────────────
$menu_sections = [
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

$posts_by = [];
if ($conexion) {
    foreach ($menu_sections as $sec) {
        $cat = mysqli_real_escape_string($conexion, $sec['id']);
        $r   = @mysqli_query($conexion,
            "SELECT * FROM eventos_posts WHERE category='$cat' ORDER BY sort_order ASC, id DESC"
        );
        $posts_by[$sec['id']] = [];
        if ($r) while ($row = mysqli_fetch_assoc($r)) $posts_by[$sec['id']][] = $row;
    }
}

// ── Marquee categorías: split by " – " or " - " ────────────────────────────
$marquee_raw   = $c['ev_marquee_text'] ?? 'Team Building – Networking – Corporativos – Afterwork – Experiencias';
$marquee_items = array_values(array_filter(array_map('trim', preg_split('/\s[–-]\s/u', $marquee_raw))));
?>

<main>

<!-- ── HERO ──────────────────────────────────────────────────────────────── -->
<section class="page-hero ev-hero">
    <span class="section-label"><?= htmlspecialchars($c['ev_hero_label'] ?? 'Eventos · TUOI') ?></span>
    <h1><?= htmlspecialchars($c['ev_hero_h1'] ?? 'Celebra con nosotros') ?></h1>
    <p><?= htmlspecialchars($c['ev_hero_sub'] ?? 'Organizamos eventos únicos con comida funcional y saludable.') ?></p>
</section>

<!-- ── MARQUEE DE IMÁGENES ───────────────────────────────────────────────── -->
<?php if (!empty($carrusel_imgs)): ?>
<section class="ev-img-marquee" aria-label="Galería de eventos">
    <div class="ev-img-marquee__track">
        <?php for ($pass = 0; $pass < 2; $pass++): ?>
            <?php foreach ($carrusel_imgs as $img): ?>
            <div class="ev-img-marquee__item" <?= $pass === 1 ? 'aria-hidden="true"' : '' ?>>
                <img src="<?= $base ?>assets/img/eventos/carrusel/<?= htmlspecialchars($img) ?>"
                     alt="" loading="lazy">
            </div>
            <?php endforeach; ?>
        <?php endfor; ?>
    </div>
</section>
<?php endif; ?>

<!-- ── POR QUÉ TUOI ──────────────────────────────────────────────────────── -->
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
                    <span class="ev-why__icon" aria-hidden="true"><?= htmlspecialchars($icon) ?></span>
                    <div>
                        <h3><?= htmlspecialchars($title) ?></h3>
                        <p><?= nl2br(htmlspecialchars($desc)) ?></p>
                    </div>
                </li>
                <?php endfor; ?>
            </ul>
        </div>

        <?php if ($why_img): ?>
        <div class="ev-why__image">
            <img src="<?= $base ?>assets/img/eventos/por-que-tuoi/<?= htmlspecialchars($why_img) ?>"
                 alt="" loading="lazy">
        </div>
        <?php endif; ?>
    </div>
</section>

<!-- ── PROPUESTA DE MENÚS (intro + zigzag) ───────────────────────────────── -->
<section class="ev-menus-intro">
    <div class="ev-menus-intro__inner">
        <span class="section-label"><?= htmlspecialchars($c['ev_menus_label'] ?? 'Propuesta de menús') ?></span>
        <h2><?= htmlspecialchars($c['ev_menus_h2'] ?? 'Menús de grupo y catering') ?></h2>
        <p><?= htmlspecialchars($c['ev_menus_intro'] ?? '') ?></p>
    </div>
</section>

<?php foreach ($menu_sections as $idx => $sec):
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
                <p><?= htmlspecialchars($sec['desc']) ?></p>
            </div>
        </div>

        <?php if (!empty($posts)): ?>
        <div class="ev-posts">
            <?php foreach ($posts as $i => $post):
                $isReverse = ($i % 2 !== 0) ? 'row-reverse' : '';
                $images = [];
                $raw_images = $post['images'] ?? ($post['image_filename'] ?? '');
                if (!empty($raw_images)) {
                    $decoded = json_decode($raw_images, true);
                    if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                        $images = $decoded;
                    } else {
                        $images = array_map('trim', explode(',', $raw_images));
                    }
                }
                $trackId = "track-evento-" . ($post['id'] ?? $i);
            ?>
            <div class="project-row fade-up visible <?= $isReverse ?>">
                <div class="project-image">
                    <?php if (count($images) > 1): ?>
                        <div class="carousel-container">
                            <div class="carousel-track" id="<?= $trackId ?>">
                                <?php foreach($images as $img): ?>
                                    <img src="<?= $base ?>assets/img/eventos/<?= htmlspecialchars($sec['id']) ?>/<?= htmlspecialchars($img) ?>"
                                         alt="<?= htmlspecialchars($post['title']) ?>" loading="lazy">
                                <?php endforeach; ?>
                            </div>
                            <button class="carousel-btn btn-prev" onclick="moveSlide('<?= $trackId ?>', -1)">❮</button>
                            <button class="carousel-btn btn-next" onclick="moveSlide('<?= $trackId ?>', 1)">❯</button>
                            <div class="carousel-dots" id="dots-<?= $trackId ?>">
                                <?php foreach($images as $idx2 => $img): ?>
                                    <div class="dot <?= $idx2 === 0 ? 'active' : '' ?>"></div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php elseif (count($images) === 1): ?>
                        <img src="<?= $base ?>assets/img/eventos/<?= htmlspecialchars($sec['id']) ?>/<?= htmlspecialchars($images[0]) ?>"
                             alt="<?= htmlspecialchars($post['title']) ?>"
                             style="width: 100%; border-radius: 12px; object-fit: cover; aspect-ratio: 16/9;" loading="lazy">
                    <?php else: ?>
                        <div class="img-placeholder" style="aspect-ratio: 16/9; display: flex; align-items: center; justify-content: center; background: #e0e0e0; border-radius: 12px;">📸 Sin imagen</div>
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
            <p>Próximamente publicaremos las propuestas de esta modalidad.<br>¡Escríbenos para conocer todas las opciones!</p>
            <a href="#contacto" class="btn-primary" style="margin-top:1.25rem;display:inline-block;">Contactar →</a>
        </div>
        <?php endif; ?>

    </div>
</section>
<?php endforeach; ?>

<!-- ── CTA ───────────────────────────────────────────────────────────────── -->
<section class="ev-cta">
    <div class="ev-cta__inner">
        <h2><?= htmlspecialchars($c['ev_cta_h2'] ?? '¿Tienes un evento en mente?') ?></h2>
        <p><?= htmlspecialchars($c['ev_cta_text'] ?? '') ?></p>
        <a href="#contacto" class="btn-primary ev-cta__btn">
            <?= htmlspecialchars($c['ev_cta_btn'] ?? 'Hablamos →') ?>
        </a>
    </div>
</section>

<!-- ── BANNER MARQUEE DE CATEGORÍAS ──────────────────────────────────────── -->
<?php if (!empty($marquee_items)): ?>
<section class="ev-slogan" aria-label="Tipos de evento">
    <div class="ev-slogan__track">
        <?php for ($pass = 0; $pass < 2; $pass++): ?>
            <span class="ev-slogan__group" <?= $pass === 1 ? 'aria-hidden="true"' : '' ?>>
                <?php foreach ($marquee_items as $item): ?>
                    <span class="ev-slogan__item"><?= htmlspecialchars($item) ?></span>
                    <span class="ev-slogan__sep" aria-hidden="true">–</span>
                <?php endforeach; ?>
            </span>
        <?php endfor; ?>
    </div>
</section>
<?php endif; ?>

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
                        <?= htmlspecialchars($c['contact_phone'] ?? '+34 604 39 43 47') ?>
                    </a>
                </li>
                <li>
                    <span class="ev-contact__icon">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                    </span>
                    <a href="mailto:<?= htmlspecialchars($c['contact_email'] ?? 'hola@miobiosport.com') ?>">
                        <?= htmlspecialchars($c['contact_email'] ?? 'hola@miobiosport.com') ?>
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
