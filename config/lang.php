<?php
/**
 * Sistema de internacionalización (i18n) del sitio TUOI.
 *
 * Define:
 *   - $lang:        idioma activo de la sesión ('es' o 'en'), persistido en cookie.
 *   - $_ui:         diccionario de strings cortos de interfaz (menús, botones, formularios…).
 *   - t() / t_raw(): helpers para obtener un string traducido por su clave.
 *   - $carta_info:  metadatos (título + subtítulo) de cada categoría de la carta.
 *
 * Convención: el español es el idioma por defecto y siempre debe estar
 * definido. El inglés es opcional; si una clave no tiene traducción 'en',
 * se cae al valor 'es' para evitar mostrar la clave cruda.
 *
 * Para textos largos editables desde el panel admin se usa otro mecanismo
 * (ver config/content_helper.php → load_site_content). Este archivo es solo
 * para strings de UI fijos en código.
 */

// Detección de idioma: la cookie 'tuoi_lang' la fija set-lang.php cuando el
// usuario pulsa el selector ES/EN del header. Por defecto, español.
$lang = (isset($_COOKIE['tuoi_lang']) && $_COOKIE['tuoi_lang'] === 'en') ? 'en' : 'es';

// ── UI string translations ──────────────────────────────
// Mapa clave → [es, en]. Mantén las claves agrupadas por sección para
// que sea fácil localizar y traducir nuevos textos.
$_ui = [
    // Navigation
    'nav_home'          => ['es' => 'Inicio',            'en' => 'Home'],
    'nav_menu'          => ['es' => 'Carta',             'en' => 'Menu'],
    'nav_about'         => ['es' => 'Quiénes somos',     'en' => 'About us'],
    'nav_contacto'      => ['es' => 'Contacto',          'en' => 'Contact'],
    // Contacto — página
    'contacto_hero_label' => ['es' => 'Contacto',                            'en' => 'Contact'],
    'contacto_hero_h1'    => ['es' => 'Hablemos',                            'en' => "Let's talk"],
    'contacto_hero_sub'   => ['es' => 'Cuéntanos qué necesitas y te respondemos lo antes posible.', 'en' => 'Tell us what you need and we will reply as soon as possible.'],
    // Header dropdown category names
    'cat_desayunos'     => ['es' => 'Desayunos',         'en' => 'Breakfasts'],
    'cat_brunch'        => ['es' => 'Menú brunch',       'en' => 'Brunch menu'],
    'cat_toque'         => ['es' => 'Opciones saladas',  'en' => 'Savory options'],
    'cat_lunch'         => ['es' => 'Menú lunch',        'en' => 'Lunch menu'],
    'cat_dulce'         => ['es' => 'Momento Dulce',     'en' => 'Sweet Moment'],
    'cat_bebidas'       => ['es' => 'Bebidas',           'en' => 'Drinks'],
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
    'footer_legal'          => ['es' => 'Legal',                'en' => 'Legal'],
    'footer_legal_privacy'  => ['es' => 'Política de Privacidad','en' => 'Privacy Policy'],
    'footer_legal_terms'    => ['es' => 'Aviso Legal',           'en' => 'Legal Notice'],
    'footer_legal_cookies'  => ['es' => 'Política de Cookies',   'en' => 'Cookies Policy'],
    // Quienes somos (link)
    'qs_link'           => ['es' => 'Conoce nuestra historia', 'en' => 'Our story'],
    // Eventos
    'nav_eventos'       => ['es' => 'Eventos',                 'en' => 'Events'],
    'nav_ev_all'        => ['es' => 'Ver todos los eventos',    'en' => 'All events'],
    'nav_ev_listos'     => ['es' => 'Listos para disfrutar',   'en' => 'Ready to enjoy'],
    'nav_ev_medida'     => ['es' => 'Diseñado a tu medida',    'en' => 'Designed for you'],
    'nav_ev_contacto'   => ['es' => 'Contacto',                'en' => 'Contact'],
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
    'ev_contact_consent_err' => ['es' => 'Debes aceptar la política de privacidad para enviar el formulario.', 'en' => 'You must accept the privacy policy to submit the form.'],
    'ev_contact_consent'     => [
        'es' => 'He leído y acepto la <a href="../legal/privacidad.php" target="_blank" rel="noopener">Política de Privacidad</a>. Mis datos serán tratados por Alimentación y Vida SL (MIOBIO) para responder a esta consulta.',
        'en' => 'I have read and accept the <a href="../legal/privacidad.php" target="_blank" rel="noopener">Privacy Policy</a>. My data will be processed by Alimentación y Vida SL (MIOBIO) to reply to this enquiry.'
    ],
    'ev_read_more'      => ['es' => 'Leer más',                'en' => 'Read more'],
    'ev_back'           => ['es' => '← Volver a Eventos',      'en' => '← Back to Events'],
    // Eventos — secciones extra (estado vacío, contacto, formulario)
    'ev_empty_text'     => ['es' => 'Próximamente publicaremos más sobre esta modalidad.<br>¡Escríbenos para conocer todas las opciones!', 'en' => 'We will publish more about this option soon.<br>Get in touch to learn about all the options!'],
    'ev_empty_btn'      => ['es' => 'Contactar →',             'en' => 'Contact us →'],
    'ev_contact_h2'     => ['es' => '¿Hablamos?',              'en' => "Let's talk?"],
    'ev_contact_lead'   => ['es' => 'Cuéntanos tu proyecto y diseñamos juntos el evento perfecto.', 'en' => 'Tell us about your project and together we will design the perfect event.'],
    'ev_form_ph_name'   => ['es' => 'Tu nombre',               'en' => 'Your name'],
    'ev_form_ph_email'  => ['es' => 'tu@email.com',            'en' => 'you@email.com'],
    'ev_form_ph_phone'  => ['es' => '+34 600 000 000',         'en' => '+34 600 000 000'],
    'ev_form_ph_msg'    => ['es' => 'Cuéntanos en qué podemos ayudarte...', 'en' => 'Tell us how we can help you...'],
    // Validación inline (mostrada en rojo encima del campo)
    'ev_form_required'  => ['es' => 'Este campo es obligatorio.',                'en' => 'This field is required.'],
    'ev_form_email_bad' => ['es' => 'Introduce una dirección de email válida.',  'en' => 'Please enter a valid email address.'],
    'ev_contact_send_another' => ['es' => 'Enviar otro mensaje →',               'en' => 'Send another message →'],
    // Eventos — Listos para disfrutar (fallbacks)
    'ev_el_back_text'        => ['es' => 'Volver a Eventos',                          'en' => 'Back to Events'],
    'ev_el_service_h2'       => ['es' => 'Lo que incluye el servicio',                'en' => 'What the service includes'],
    'ev_el_conditions_label' => ['es' => 'Condiciones de contratación y pago',        'en' => 'Contracting and payment terms'],
    'ev_cta_h2'              => ['es' => '¿Tienes un evento en mente?',               'en' => 'Have an event in mind?'],
    'ev_cta_text'            => ['es' => 'Cuéntanos cómo lo imaginas y diseñamos el menú a tu medida.', 'en' => 'Tell us how you picture it and we will design the menu for you.'],
    'ev_cta_btn'             => ['es' => 'Hablamos →',                                'en' => "Let's talk →"],
    // Eventos — A tu medida (provisional)
    'atm_intro_label'   => ['es' => 'A tu medida',                                 'en' => 'Designed for you'],
    'atm_intro_h2'      => ['es' => 'Adaptamos tu evento a lo que necesites',     'en' => 'We adapt your event to what you need'],
    'atm_intro_p'       => ['es' => 'Sin formatos rígidos ni paquetes cerrados: partimos de tu idea, tu espacio y tu gente para diseñar contigo el menú, la propuesta y el ritmo del evento. Networking, presentaciones, celebraciones íntimas o brunches corporativos — lo construimos contigo, paso a paso, hasta que encaje.', 'en' => 'No rigid formats, no fixed packages: we start from your idea, your venue and your guests to design the menu, the proposal and the pace of the event together with you. Networking sessions, launches, intimate celebrations or corporate brunches — we build it with you, step by step, until it fits.'],
    'atm_gallery_h2'    => ['es' => 'Algunos momentos',                            'en' => 'A few moments'],
    'atm_cta_btn'       => ['es' => 'Cuéntanos tu evento →',                       'en' => 'Tell us about your event →'],
    // Instagram CTA
    'btn_instagram'     => ['es' => 'Ver menú del día',                             'en' => 'View today\'s menu'],
];

/**
 * Devuelve la traducción de $key escapada para HTML.
 *
 * Es la opción segura por defecto: úsala siempre que el string vaya a
 * imprimirse directamente en una plantilla (echo t('...')).
 *
 * Estrategia de fallback:
 *   1. Traducción en el idioma activo ($lang).
 *   2. Traducción en español ('es') si no existe la del idioma activo.
 *   3. La propia clave, para que un texto que falte sea visible y fácil de detectar.
 */
function t(string $key): string {
    global $_ui, $lang;
    $str = $_ui[$key][$lang] ?? $_ui[$key]['es'] ?? $key;
    return htmlspecialchars($str, ENT_QUOTES);
}

/**
 * Igual que t() pero SIN escapado HTML.
 *
 * Solo para strings que contienen marcado HTML controlado por nosotros
 * (p. ej. enlaces dentro del consentimiento de privacidad). Nunca uses
 * t_raw() con datos provenientes de usuarios.
 */
function t_raw(string $key): string {
    global $_ui, $lang;
    return $_ui[$key][$lang] ?? $_ui[$key]['es'] ?? $key;
}

// ── Carta category info (title + description) ──────────
// Cada categoría tiene un par [título, subtítulo] por idioma. Lo consume
// includes/carta-page.php para renderizar la cabecera de cada subpágina
// de la carta (desayunos, bebidas, etc.).
$carta_info = [
    'desayunos' => [
        'es' => ['Desayunos',      'Activa tu mañana con energía real.'],
        'en' => ['Breakfasts',     'Power up your morning with real energy.'],
    ],
    'menu-brunch' => [
        'es' => ['Menú brunch',    'Incluye plato principal, bebida, café y postre.'],
        'en' => ['Brunch menu',    'Includes main course, drink, coffee, and dessert.'],
    ],
    'toque-salado' => [
        'es' => ['Opciones saladas', 'Bocados sabrosos para cualquier momento del día.'],
        'en' => ['Savory options',   'Savory bites for any moment of the day.'],
    ],
    'menu-lunch' => [
        'es' => ['Menú lunch',     'De Lunes a Viernes de 13:00 a 16:00'],
        'en' => ['Lunch menu',     'Monday to Friday from 1:00 PM to 4:00 PM'],
    ],
    'momento-dulce' => [
        'es' => ['Momento Dulce',  'Bollería artesanal para darte ese gusto.'],
        'en' => ['Sweet Moment',   'Artisan pastries to treat yourself.'],
    ],
    'bebidas' => [
        'es' => ['Bebidas',        'Café de especialidad y bebidas funcionales.'],
        'en' => ['Drinks',         'Specialty coffee and functional beverages.'],
    ],
];
