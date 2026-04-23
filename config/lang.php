<?php
// Language detection — cookie persists user preference
$lang = (isset($_COOKIE['tuoi_lang']) && $_COOKIE['tuoi_lang'] === 'en') ? 'en' : 'es';

// ── UI string translations ──────────────────────────────
$_ui = [
    // Navigation
    'nav_home'          => ['es' => 'Inicio',            'en' => 'Home'],
    'nav_menu'          => ['es' => 'Carta',             'en' => 'Menu'],
    'nav_about'         => ['es' => 'Quiénes somos',     'en' => 'About us'],
    // Header dropdown category names
    'cat_desayunos'     => ['es' => 'Desayunos',         'en' => 'Breakfasts'],
    'cat_toque'         => ['es' => 'Toque Salado',      'en' => 'Savory Touch'],
    'cat_dulce'         => ['es' => 'Momento Dulce',     'en' => 'Sweet Moment'],
    'cat_bebidas'       => ['es' => 'Bebidas',            'en' => 'Drinks'],
    'cat_super'         => ['es' => 'Superalimentos',    'en' => 'Superfoods'],
    // Buttons
    'btn_see_menu'      => ['es' => 'Ver la carta',      'en' => 'See the menu'],
    'btn_our_story'     => ['es' => 'Conoce nuestra historia', 'en' => 'Our story'],
    // Carta index page
    'carta_page_label'  => ['es' => 'Menú',              'en' => 'Menu'],
    'carta_page_title'  => ['es' => 'Nuestra carta',     'en' => 'Our menu'],
    'carta_page_sub'    => ['es' => 'Funcional, equilibrado y con sabor.', 'en' => 'Functional, balanced and flavorful.'],
    'carta_soon'        => ['es' => 'Próximamente',      'en' => 'Coming soon'],
    'carta_soon_desc'   => ['es' => 'Las imágenes de la carta se cargarán desde el panel de administración.', 'en' => 'Menu images will be loaded from the admin panel.'],
    'carta_no_cat'      => ['es' => 'Sin imágenes en esta categoría', 'en' => 'No images in this category'],
    'carta_no_cat_desc' => ['es' => 'Próximamente se cargarán las imágenes desde el panel de administración.', 'en' => 'Images will be loaded from the admin panel soon.'],
    // Carta category pages
    'carta_breadcrumb'  => ['es' => 'Carta',             'en' => 'Menu'],
    'subnav_all'        => ['es' => 'Carta entera',      'en' => 'Full menu'],
    // PDF
    'pdf_no_show'       => ['es' => 'Tu navegador no puede mostrar el PDF.', 'en' => 'Your browser cannot display the PDF.'],
    'open_pdf'          => ['es' => 'Abrir PDF',         'en' => 'Open PDF'],
    // Footer
    'footer_explore'    => ['es' => 'Explora',           'en' => 'Explore'],
    'footer_menu_link'  => ['es' => 'La carta',          'en' => 'The menu'],
    'footer_find'       => ['es' => 'Dónde encontrarnos', 'en' => 'Find us'],
    'footer_follow'     => ['es' => 'Síguenos',          'en' => 'Follow us'],
    'footer_rights'     => ['es' => 'Todos los derechos reservados.', 'en' => 'All rights reserved.'],
    'footer_tagline'    => ['es' => 'Del alto rendimiento a tu mesa.', 'en' => 'From high performance to your table.'],
    // Quienes somos (link)
    'qs_link'           => ['es' => 'Conoce nuestra historia', 'en' => 'Our story'],
    // Eventos
    'nav_eventos'       => ['es' => 'Eventos',                 'en' => 'Events'],
    'nav_networking'    => ['es' => 'Networking',              'en' => 'Networking'],
    'nav_catering'      => ['es' => 'Catering',                'en' => 'Catering'],
    'nav_team_building' => ['es' => 'Team Building',           'en' => 'Team Building'],
    // Eventos — page strings
    'ev_contact_title'  => ['es' => 'Contacto',                'en' => 'Contact'],
    'ev_contact_name'   => ['es' => 'Nombre',                  'en' => 'Name'],
    'ev_contact_email'  => ['es' => 'Email',                   'en' => 'Email'],
    'ev_contact_phone'  => ['es' => 'Teléfono',                'en' => 'Phone'],
    'ev_contact_msg'    => ['es' => 'Mensaje',                 'en' => 'Message'],
    'ev_contact_send'   => ['es' => 'Enviar mensaje',          'en' => 'Send message'],
    'ev_contact_ok'     => ['es' => '¡Mensaje enviado! Te contactaremos pronto.', 'en' => 'Message sent! We will contact you soon.'],
    'ev_contact_err'    => ['es' => 'Por favor completa nombre, email y mensaje.', 'en' => 'Please fill in name, email and message.'],
    'ev_read_more'      => ['es' => 'Leer más',                'en' => 'Read more'],
    'ev_back'           => ['es' => '← Volver a Eventos',      'en' => '← Back to Events'],
];

/** Translated string, HTML-escaped */
function t(string $key): string {
    global $_ui, $lang;
    $str = $_ui[$key][$lang] ?? $_ui[$key]['es'] ?? $key;
    return htmlspecialchars($str, ENT_QUOTES);
}

/** Translated string, raw (use only for trusted values) */
function t_raw(string $key): string {
    global $_ui, $lang;
    return $_ui[$key][$lang] ?? $_ui[$key]['es'] ?? $key;
}

// ── Carta category info (title + description) ──────────
$carta_info = [
    'desayunos' => [
        'es' => ['Desayunos',      'Activa tu mañana con energía real.'],
        'en' => ['Breakfasts',     'Power up your morning with real energy.'],
    ],
    'toque-salado' => [
        'es' => ['Toque Salado',   'Bocados sabrosos para cualquier momento del día.'],
        'en' => ['Savory Touch',   'Savory bites for any moment of the day.'],
    ],
    'momento-dulce' => [
        'es' => ['Momento Dulce',  'Bollería artesanal para darte ese gusto.'],
        'en' => ['Sweet Moment',   'Artisan pastries to treat yourself.'],
    ],
    'bebidas' => [
        'es' => ['Bebidas',        'Café de especialidad y bebidas funcionales.'],
        'en' => ['Drinks',         'Specialty coffee and functional beverages.'],
    ],
    'superalimentos' => [
        'es' => ['Superalimentos', 'Ingredientes que potencian tu rendimiento.'],
        'en' => ['Superfoods',     'Ingredients that boost your performance.'],
    ],
];
