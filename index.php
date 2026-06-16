<?php
$base         = '';
$current_page = 'inicio';
$extra_css    = 'home';
$page_title   = 'TUOI | Functional Coffee & Smart Food';
require 'config/conexion.php';
require 'config/content_helper.php';
require_once 'config/lang.php';
$page_url         = 'https://tuoi.es/';
$page_description = $lang === 'en'
    ? 'Functional coffee & smart food café in Valencia. Healthy breakfasts, brunch, lunch and drinks adapted to your day.'
    : 'Cafetería de comida funcional y smart food en Valencia. Desayunos saludables, brunch, lunch y bebidas adaptadas a las necesidades de tu día.';
require 'includes/header.php';
// $lang is set by header.php via config/lang.php

$c = load_site_content($conexion, $lang);
?>

<main>

    <!-- =========================================================
         HERO — Imagen destacada + texto de bienvenida + CTA a carta
    ========================================================== -->
    <section class="hero">
        <div class="hero-content">
            <span class="hero-label"><?= $c['hero_label'] ?></span>
            <h1><?= $c['hero_h1'] ?></h1>
            <p><?= $c['hero_subtitle'] ?></p>
            <a href="<?= $base ?>pages/carta/" class="btn-primary"><?= t('btn_see_menu') ?></a>
        </div>
    </section>

    <!-- =========================================================
         INSTAGRAM CTA — Menú del día en Instagram
    ========================================================== -->
    <section class="section-ig-cta">
        <div class="section-ig-cta__inner">
            <svg class="section-ig-cta__icon" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                <rect x="2" y="2" width="20" height="20" rx="5" ry="5" stroke="currentColor" stroke-width="1.8"/>
                <circle cx="12" cy="12" r="4.5" stroke="currentColor" stroke-width="1.8"/>
                <circle cx="17.5" cy="6.5" r="1" fill="currentColor"/>
            </svg>
            <p class="section-ig-cta__text"><?= htmlspecialchars($c['ig_cta_text']) ?></p>
            <a href="<?= htmlspecialchars($c['ig_cta_url']) ?>"
               target="_blank" rel="noopener noreferrer"
               class="btn-primary">
                <?= t('btn_instagram') ?>
            </a>
        </div>
    </section>

    <!-- =========================================================
         ¿QUIÉNES SOMOS? — Preview con enlace a página completa
    ========================================================== -->
    <section class="section-quienes">
        <div class="section-quienes-inner">

            <div class="quienes-text">
                <span class="section-label"><?= $c['qs_label'] ?></span>
                <h2><?= $c['qs_h2'] ?></h2>
                <p><?= $c['qs_p1'] ?></p>
                <p><?= $c['qs_p2'] ?></p>
                <a href="<?= $base ?>pages/quienes-somos.php" class="link-arrow">
                    <?= t('btn_our_story') ?> <span aria-hidden="true">→</span>
                </a>
            </div>

            <div class="quienes-visual">
                <div class="img-placeholder" aria-hidden="true">
                    <img src="assets/img/tuoi_quienes_somos.jpg" alt="Imagen representativa del equipo de TUOI">
                </div>
            </div>

        </div>
    </section>

    <!-- =========================================================
         NUESTRA FILOSOFÍA — 4 valores + lista de oferta
    ========================================================== -->
    <section class="section-filosofia">
        <div class="section-filosofia-inner">

            <div class="section-header">
                <span class="section-label"><?= $c['fil_label'] ?></span>
                <h2><?= $c['fil_h2'] ?></h2>
            </div>

            <div class="features-grid">

                <div class="feature-card">
                    <img src="assets/img/carteles/balance.png" alt="Logo Balance" class="feature-logo feature-logo--balance">
                    <span class="badge badge-verde">Balance</span>
                    <h3><?= htmlspecialchars($c['card_balance_title']) ?></h3>
                    <p><?= htmlspecialchars($c['card_balance_desc']) ?></p>
                </div>

                <div class="feature-card">
                    <img src="assets/img/carteles/energy.png" alt="Logo Energy" class="feature-logo feature-logo--energy">
                    <span class="badge badge-naranja">Energy</span>
                    <h3><?= htmlspecialchars($c['card_energy_title']) ?></h3>
                    <p><?= htmlspecialchars($c['card_energy_desc']) ?></p>
                </div>

                <div class="feature-card">
                    <img src="assets/img/carteles/focus.png" alt="Logo Focus" class="feature-logo feature-logo--focus">
                    <span class="badge badge-morado">Focus</span>
                    <h3><?= htmlspecialchars($c['card_focus_title']) ?></h3>
                    <p><?= htmlspecialchars($c['card_focus_desc']) ?></p>
                </div>

                <div class="feature-card">
                    <img src="assets/img/carteles/power.png" alt="Logo Power" class="feature-logo feature-logo--power">
                    <span class="badge badge-amarillo">Power</span>
                    <h3><?= htmlspecialchars($c['card_power_title']) ?></h3>
                    <p><?= htmlspecialchars($c['card_power_desc']) ?></p>
                </div>

            </div>

            <!-- Lista de oferta -->
            <div class="values-list">
                <div class="value-item">
                    <span class="value-dot" aria-hidden="true"></span>
                    <p><?= htmlspecialchars($c['value1']) ?></p>
                </div>
                <div class="value-item">
                    <span class="value-dot" aria-hidden="true"></span>
                    <p><?= htmlspecialchars($c['value2']) ?></p>
                </div>
                <div class="value-item">
                    <span class="value-dot" aria-hidden="true"></span>
                    <p><?= htmlspecialchars($c['value3']) ?></p>
                </div>
                <div class="value-item">
                    <span class="value-dot" aria-hidden="true"></span>
                    <p><?= htmlspecialchars($c['value4']) ?></p>
                </div>
            </div>

        </div>
    </section>


</main>

<?php require 'includes/footer.php'; ?>
