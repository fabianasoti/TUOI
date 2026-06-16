<?php
$base         = '../../';
$current_page = 'contacto';
$extra_css    = 'contacto';
require $base . 'config/conexion.php';
require $base . 'config/content_helper.php';
require_once $base . 'config/lang.php';
$page_title = $lang === 'en' ? 'Contact | TUOI' : 'Contacto | TUOI';
$page_url         = 'https://tuoi.es/pages/contacto/';
$page_description = $lang === 'en'
    ? 'Contact TUOI or visit us at C. de la Travesía, 15B, 46024 València. Reservations, events and enquiries for Valencia\'s functional food café.'
    : 'Contacta con TUOI o visítanos en C. de la Travesía, 15B, 46024 València. Reservas, eventos y consultas en tu cafetería de comida funcional en Valencia.';

// Handler del formulario (procesa POST y AJAX exit antes de emitir HTML).
require $base . 'includes/contact-handler.php';

require $base . 'includes/header.php';
$c = load_site_content($conexion, $lang);
?>

<main>

    <section class="page-hero">
        <span class="section-label"><?= t('contacto_hero_label') ?></span>
        <h1><?= t('contacto_hero_h1') ?></h1>
        <p><?= t('contacto_hero_sub') ?></p>
    </section>

    <?php $contact_compact = true; require $base . 'includes/contact-form.php'; ?>

</main>

<?php require $base . 'includes/footer.php'; ?>
