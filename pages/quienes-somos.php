<?php
$base         = '../';
$current_page = 'quienes-somos';
require $base . 'config/conexion.php';
require $base . 'config/content_helper.php';
require_once $base . 'config/lang.php'; // sets $lang early so page_title can be translated
$page_title = $lang === 'en' ? 'About us | TUOI' : 'Quiénes somos | TUOI';
require $base . 'includes/header.php';  // uses require_once for lang.php — no double load

$c = load_site_content($conexion, $lang);
?>

<main>

    <!-- Hero interior -->
    <section class="page-hero">
        <span class="section-label"><?= $c['qs_page_hero_label'] ?></span>
        <h1><?= $c['qs_page_hero_h1'] ?></h1>
        <p><?= $c['qs_page_hero_sub'] ?></p>
    </section>

    <!-- Contenido principal -->
    <div class="qs-page">

        <!-- Bloque 1: texto izquierda · imagen derecha -->
        <div class="qs-section">
            <div class="qs-text">
                <span class="section-label"><?= $c['qs_page_b1_label'] ?></span>
                <h2><?= $c['qs_page_b1_h2'] ?></h2>
                <p><?= $c['qs_page_b1_p1'] ?></p>
                <p><?= $c['qs_page_b1_p2'] ?></p>
                <?php if (!empty($c['qs_page_b1_p3'])): ?><p><?= $c['qs_page_b1_p3'] ?></p><?php endif; ?>
            </div>
            <div class="qs-img">
                <img src="<?= $base ?>assets/img/quienes_somos/tuoi_quienes_somos.jpg"
                     alt="Interior de TUOI, espacio de cafetería saludable">
            </div>
        </div>

        <!-- Bloque 2: texto derecha · imagen izquierda -->
        <div class="qs-section qs-section--reverse">
            <div class="qs-text">
                <span class="section-label"><?= $c['qs_page_b2_label'] ?></span>
                <h2><?= $c['qs_page_b2_h2'] ?></h2>
                <p><?= $c['qs_page_b2_p1'] ?></p>
                <p><?= $c['qs_page_b2_p2'] ?></p>
            </div>
            <div class="qs-img">
                <img src="<?= $base ?>assets/img/quienes_somos/superalimentos.png"
                     alt="Superalimentos y alimentación funcional">
            </div>
        </div>

        <!-- Bloque 3: texto izquierda · imagen derecha -->
        <div class="qs-section">
            <div class="qs-text">
                <span class="section-label"><?= $c['qs_page_b3_label'] ?></span>
                <h2><?= $c['qs_page_b3_h2'] ?></h2>
                <p><?= $c['qs_page_b3_intro'] ?></p>
                <ul class="qs-list">
                    <li>
                        <span class="qs-dot" aria-hidden="true"></span>
                        <span><?= $c['qs_page_b3_li1'] ?></span>
                    </li>
                    <li>
                        <span class="qs-dot" aria-hidden="true"></span>
                        <span><?= $c['qs_page_b3_li2'] ?></span>
                    </li>
                    <li>
                        <span class="qs-dot" aria-hidden="true"></span>
                        <span><?= $c['qs_page_b3_li3'] ?></span>
                    </li>
                </ul>
                <p><?= $c['qs_page_b3_p'] ?></p>
            </div>
            <div class="qs-img">
                <img src="<?= $base ?>assets/img/quienes_somos/nourishing_bowls.png"
                     alt="Bowls nutritivos y coloridos de TUOI">
            </div>
        </div>

        <!-- Cierre -->
        <div class="qs-cierre">
            <p><?= $c['qs_page_close_p'] ?></p>
            <a href="<?= $base ?>pages/carta/" class="btn-primary"><?= $c['qs_page_close_btn'] ?></a>
        </div>

    </div>

</main>

<?php require $base . 'includes/footer.php'; ?>
