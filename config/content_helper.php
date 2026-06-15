<?php
/**
 * Helpers para cargar contenido editable del sitio.
 *
 * A diferencia de config/lang.php (strings cortos de UI fijos en código),
 * aquí vive el contenido largo y editable desde el panel admin:
 *   - Textos de las secciones del home, "Quiénes somos", "Eventos", contacto…
 *   - Orden personalizado de imágenes en las secciones que lo permiten.
 *
 * Patrón general: definimos defaults en código (tras importar el sitio
 * funciona aunque la BD esté vacía o caída) y los sobrescribimos con los
 * valores guardados en la tabla site_content cuando hay conexión.
 */

/**
 * Carga todos los textos editables del sitio en un array clave → valor.
 *
 * Funcionamiento:
 *   1. Parte de un array de defaults en español, definido en este archivo.
 *      Esto garantiza que el sitio se renderiza aunque no haya BD.
 *   2. Si hay conexión, consulta la tabla `site_content` (clave/valor) y
 *      sobrescribe los defaults con los textos guardados desde el admin.
 *   3. Si el idioma activo es 'en', vuelve a sobrescribir buscando claves
 *      con sufijo '_en' (p. ej. 'hero_h1' → 'hero_h1_en'). Si no existe la
 *      versión inglesa, se mantiene el español como fallback.
 *
 * @param  mixed   $conexion  Recurso mysqli o false/null si la BD no está disponible.
 * @param  string  $lang      'es' o 'en'.
 * @return array              Array clave → texto, listo para usar en plantillas.
 */
function load_site_content($conexion, string $lang = 'es') {
    // Valores por defecto en español. Sirven como:
    //   - Lista canónica de claves disponibles (lo que NO esté aquí no se renderiza).
    //   - Plantilla inicial al desplegar el sitio sin contenido en BD.
    //   - Fallback si la conexión a BD falla.
    $defaults = [
        // Homepage — Hero
        'hero_label'         => 'Cafetería · Valencia',
        'hero_h1'            => 'Functional coffee<br>&amp; smart food',
        'hero_subtitle'      => 'Come como piensas.<br>Comida adaptada a las necesidades de tu día.',
        // Homepage — Quiénes somos (preview)
        'qs_label'           => '¿Quiénes somos?',
        'qs_h2'              => 'Del alto rendimiento<br>a tu mesa.',
        'qs_p1'              => 'TUOI es mucho más que una cafetería: es tu lugar para disfrutar, cuidarte y sentirte bien. Un espacio donde puedes hacer una pausa, empezar el día o recargar energía mientras disfrutas de un buen café y comida saludable, rica y pensada para tu día a día.',
        'qs_p2'              => 'Aquí cuidarte no es complicado. Es natural, accesible… y apetecible. Detrás de TUOI está el conocimiento de <strong><a href="https://miobiosport.com/" target="_blank">MIOBIO</a></strong>, especialistas en alimentación funcional aplicada al deporte de élite. Toda esa experiencia se traduce en algo muy simple: ofrecerte opciones que no solo te gustan, sino que te ayudan a tener más energía, sentirte mejor y mantener tu ritmo.',
        // Homepage — Filosofía
        'fil_label'          => 'Nuestra filosofía',
        'fil_h2'             => 'Todo bajo una misma filosofía:<br>alimentación funcional, equilibrada y con sabor.',
        'card_balance_title' => 'Nutrición en equilibrio',
        'card_balance_desc'  => 'Cada plato diseñado para darte lo que necesitas, sin excesos ni carencias. Nutrición real en cada bocado.',
        'card_energy_title'  => 'Activa tu mañana',
        'card_energy_desc'   => 'Desayunos pensados para despertar tu rendimiento desde la primera hora del día. Sin estimulantes artificiales.',
        'card_focus_title'   => 'Concentración sostenida',
        'card_focus_desc'    => 'Sin picos de azúcar, sin bajones a media tarde. Comida que mantiene tu mente activa cuando más lo necesitas.',
        'card_power_title'   => 'Rinde al máximo',
        'card_power_desc'    => 'Proteínas, carbohidratos y grasas en su justa medida para que tu cuerpo funcione a pleno rendimiento, siempre.',
        'value1'             => 'Desayunos enfocados en activar la energía',
        'value2'             => 'Almuerzos diseñados para sostener el rendimiento',
        'value3'             => 'Comidas orientadas a la recuperación',
        'value4'             => 'Opciones adaptadas a diferentes necesidades nutricionales',
        // Quiénes somos page — Hero
        'qs_page_hero_label' => 'Nuestra historia',
        'qs_page_hero_h1'    => '¿Quiénes somos?',
        'qs_page_hero_sub'   => 'Del deporte de élite a tu mesa de trabajo.',
        // Quiénes somos page — Bloque 1
        'qs_page_b1_label'   => 'Quiénes somos',
        'qs_page_b1_h2'      => 'Tu lugar para cuidarte sin complicaciones',
        'qs_page_b1_p1'      => 'TUOI es mucho más que una cafetería: es tu lugar para disfrutar, cuidarte y sentirte bien.',
        'qs_page_b1_p2'      => 'Un espacio donde puedes hacer una pausa, empezar el día o recargar energía mientras disfrutas de café y comida saludable, rica y pensada para tu día a día.',
        'qs_page_b1_p3'      => 'Aquí cuidarte no es complicado. Es natural, accesible… y apetecible.',
        // Quiénes somos page — Bloque 2
        'qs_page_b2_label'   => 'Nuestro origen',
        'qs_page_b2_h2'      => 'El conocimiento del deporte de élite, en tu mesa',
        'qs_page_b2_p1'      => 'Detrás de TUOI está el conocimiento de <strong>MIOBIO</strong>, especialistas en alimentación funcional aplicada al deporte de élite. Toda esa experiencia se traduce en algo muy simple: ofrecerte opciones que no solo te gustan, sino que te ayudan a tener más energía, sentirte mejor y mantener tu ritmo.',
        'qs_page_b2_p2'      => 'Porque lo que comes influye en cómo te sientes.',
        // Quiénes somos page — Bloque 3
        'qs_page_b3_label'   => 'Nuestra propuesta',
        'qs_page_b3_h2'      => 'En TUOI puedes',
        'qs_page_b3_intro'   => 'En TUOI puedes:',
        'qs_page_b3_li1'     => 'Empezar el día con desayunos que activan tu energía',
        'qs_page_b3_li2'     => 'Disfrutar de café y opciones saludables en cualquier momento',
        'qs_page_b3_li3'     => 'Hacer una pausa con comida equilibrada que realmente apetece',
        'qs_page_b3_p'       => 'Todo bajo una misma idea: comer bien sin complicarte.',
        // Quiénes somos page — Cierre
        'qs_page_close_p'    => 'TUOI es el punto de encuentro entre el conocimiento del alto rendimiento y tu día a día. <strong>Un lugar donde lo saludable se convierte en parte natural de tu rutina.</strong>',
        'qs_page_close_btn'  => 'Explorar la carta',
        // Eventos — Hero de página
        'ev_hero_label'      => 'Eventos · TUOI',
        'ev_hero_h1'         => 'Eventos con sentido, energía y propósito',
        'ev_hero_sub'        => 'Experiencias gastronómicas que potencian cada encuentro.',
        'ev_hero_cta_primary'   => 'Hablemos de tu evento',
        'ev_hero_cta_secondary' => 'Ver menús',
        // Eventos — Manifiesto (intro narrativa entre carrusel y "Por qué TUOI")
        'ev_intro_label'     => 'Nuestra filosofía',
        'ev_intro_p1'        => 'En TUOI llevamos nuestra filosofía de functional coffee & smart food también al mundo de los eventos. Diseñamos experiencias gastronómicas que no solo acompañan, sino que potencian lo que ocurre en cada encuentro: más claridad, mejor energía y una sensación real de bienestar.',
        'ev_intro_p2'        => 'Trabajamos con ingredientes de proximidad y propuestas equilibradas que se adaptan al ritmo y objetivo de cada encuentro. El resultado: comida ligera, sabrosa y funcional, que evita bajones y acompaña el ritmo natural de cada momento.',
        // Eventos — Por qué TUOI (4 viñetas)
        'ev_why_label'       => 'Por qué TUOI',
        'ev_why_h2'          => '¿Por qué TUOI?',
        'ev_why_b1_title'    => 'Cerca y con sentido',
        'ev_why_b1_desc'     => 'Trabajamos con ingredientes de proximidad, materiales responsables y procesos cuidados. Porque un buen evento no debería pasarle factura al planeta.',
        'ev_why_b2_title'    => 'Ligero y de verdad',
        'ev_why_b2_desc'     => 'Comida real, sin ultraprocesados ni excesos. Sabrosa, equilibrada y fácil de disfrutar — sin pesadez.',
        'ev_why_b3_title'    => 'Energía que acompaña',
        'ev_why_b3_desc'     => 'Pensamos los menús para que el evento fluya: energía constante, mente despierta y cero bajones.',
        'ev_why_b4_title'    => 'Hecho para tu evento',
        'ev_why_b4_desc'     => 'Cada propuesta se adapta a lo que necesitas: el formato, las personas y lo que quieres transmitir.',
        // Eventos — Prueba social (testimonio + logos)
        'ev_social_label'    => 'Confían en nosotros',
        'ev_social_quote'    => 'Organizamos un afterwork para 40 personas y la diferencia se notó: la gente conectó, comió bien y nadie sufrió el bajón de media tarde. Volveremos.',
        'ev_social_author'   => 'Marta Soler',
        'ev_social_role'     => 'People & Culture · Innovae',
        // Eventos — Propuesta de menús (intro de las 2 opciones)
        'ev_menus_label'     => 'Nuestros menús',
        'ev_menus_h2'        => 'Menús que se adaptan a tu evento',
        'ev_menus_intro'     => 'Ofrecemos diferentes formatos que se ajustan al tipo de encuentro y a la experiencia que quieres crear.',
        // Eventos — Opción 1: Experiencias TUOI (catálogo de menús cerrados)
        'ev_opt1_label'      => 'Experiencias TUOI',
        'ev_opt1_title'      => 'Experiencias TUOI',
        'ev_opt1_desc'       => 'Tres formatos diseñados para acompañar cada tipo de encuentro. Elige y nosotros nos encargamos del resto.',
        'ev_opt1_cta'        => 'Ver experiencias',
        // Eventos — Opción 2: Evento a tu medida
        'ev_opt2_label'      => 'A tu medida',
        'ev_opt2_title'      => 'Diseñado a tu medida',
        'ev_opt2_desc'       => 'Diseñamos el menú contigo según el formato, las personas y lo que quieres transmitir.',
        'ev_opt2_cta_primary'   => 'Descubre tus posibilidades',
        'ev_opt2_cta_secondary' => 'Contáctanos',
        // Eventos · Página hija "Experiencias listas para disfrutar"
        'ev_el_h1'           => 'Listos para disfrutar',
        'ev_el_intro'        => 'Tres formatos pensados para acompañar cada tipo de encuentro: desde una pausa que activa, hasta una experiencia gastronómica completa.',
        'ev_el_back_text'    => 'Volver a Eventos',
        // Niveles de tarjeta (compartido por todas las experiencias)
        'ev_el_level_essential' => 'Essential',
        'ev_el_level_signature' => 'Signature',
        // Categoría 1 — Coffee Break
        'ev_el_cat1_label'    => 'Catering corporativo',
        'ev_el_cat1_title'    => 'Coffee Break',
        'ev_el_cat1_audience' => 'Reuniones · Workshops · Eventos corporativos · Presentaciones · Jornadas deportivas',
        // Categoría 2 — Social Cocktail
        'ev_el_cat2_label'    => 'Cóctel',
        'ev_el_cat2_title'    => 'Social Cocktail',
        'ev_el_cat2_audience' => '',
        // Categoría 3 — Table Experience
        'ev_el_cat3_label'    => 'Brunch & Comida',
        'ev_el_cat3_title'    => 'Table Experience',
        'ev_el_cat3_audience' => '',
        // Nombres de las 6 tarjetas
        'ev_el_e1_name'       => 'Flow Coffee Essential',
        'ev_el_e2_name'       => 'Flow Coffee Signature',
        'ev_el_e3_name'       => 'Social Cocktail Essential',
        'ev_el_e4_name'       => 'Social Cocktail Signature',
        'ev_el_e5_name'       => 'Table Experience Essential',
        'ev_el_e6_name'       => 'Table Experience Signature',
        // Servicio (encabezado)
        'ev_el_service_label' => 'Servicio',
        'ev_el_service_h2'    => 'Lo que incluye el servicio',
        // Condiciones (label del colapsable)
        'ev_el_conditions_label' => 'Condiciones de contratación y pago',
        // Flow Coffee Essential
        'ev_el_e1_tagline'   => 'La pausa perfecta para mantener la energía y el ritmo del evento.',
        'ev_el_e1_body'      => "<p><strong>Concepto:</strong> coffee break saludable, elegante y funcional.</p>\n<p><strong>Ideal para:</strong></p>\n<ul>\n<li>Reuniones</li>\n<li>Workshops</li>\n<li>Eventos corporativos</li>\n<li>Presentaciones</li>\n<li>Jornadas deportivas</li>\n</ul>\n<p><strong>Incluye:</strong></p>\n<ul>\n<li>Café de especialidad, infusiones, zumo de naranja natural, leche semi, sin lactosa y de avena, botellas de agua.</li>\n<li>Mini bakery (croissant y pops de chocolate).</li>\n<li>Mini salados: pulguitas de brie, jamón y mermelada de tomate; pavo con cremoso de aguacate; mini croissant de tomate, 4 quesos y espinacas.</li>\n<li>Fruta.</li>\n<li>Opciones veganas y sin gluten, bajo petición.</li>\n</ul>",
        // Flow Coffee Signature
        'ev_el_e2_tagline'   => 'Mucho más que un coffee break: una experiencia diseñada para activar cuerpo y mente.',
        'ev_el_e2_body'      => "<p><strong>Concepto:</strong> experiencia coffee break premium con enfoque wellness y funcional.</p>\n<p><strong>Incluye:</strong></p>\n<ul>\n<li>Café de especialidad, infusiones, zumo de naranja natural, leche semi, sin lactosa y de avena, botellas de agua.</li>\n<li>Mini croissant.</li>\n<li>Mini cookies.</li>\n<li>Mini muffins.</li>\n<li>Mini salados: pulguitas de brie, jamón y mermelada de tomate; pavo con cremoso de aguacate; mini croissant de tomate, 4 quesos y espinacas.</li>\n<li>Mini empanadillas de pisto.</li>\n<li>Vasito de yogurt con granola casera.</li>\n<li>Fruta.</li>\n<li>Opciones veganas y sin gluten, bajo petición.</li>\n</ul>",
        // Social Cocktail Essential
        'ev_el_e3_tagline'   => 'Una propuesta fresca y cuidada para eventos donde conectar también forma parte de la experiencia.',
        'ev_el_e3_body'      => "<p><strong>Concepto:</strong> cóctel dinámico, elegante y social.</p>\n<p><strong>Incluye:</strong></p>\n<ul>\n<li>Bebida: cerveza, refresco o agua.</li>\n<li>Hojaldres de cremoso de aguacate con jamón ibérico.</li>\n<li>Tortilla de patata.</li>\n</ul>",
        // Social Cocktail Signature
        'ev_el_e4_tagline'   => 'Una experiencia gastronómica diseñada para sorprender, emocionar y crear recuerdo.',
        'ev_el_e4_body'      => "<p><strong>Añadir:</strong></p>\n<ul>\n<li>Showcooking.</li>\n<li>Estaciones en vivo.</li>\n<li>Maridajes funcionales.</li>\n<li>Mixología saludable.</li>\n<li>Coctelería de autor.</li>\n<li>Experiencias temáticas.</li>\n<li>Menú diseñado según el tipo de evento.</li>\n<li>Puesta en escena premium.</li>\n</ul>",
        // Table Experience Essential
        'ev_el_e5_tagline'   => 'Comida real y equilibrada para encuentros donde compartir es parte del momento.',
        'ev_el_e5_body'      => "<p><strong>Concepto:</strong> brunch o comida informal saludable y moderna.</p>\n<p><strong>Incluye:</strong></p>\n<ul>\n<li>Bowls.</li>\n<li>Focaccias.</li>\n<li>Ensaladas premium.</li>\n<li>Platos para compartir.</li>\n<li>Opciones funcionales.</li>\n<li>Postres saludables.</li>\n</ul>",
        // Table Experience Signature
        'ev_el_e6_tagline'   => 'Una experiencia gastronómica premium donde bienestar, estética y sabor se unen.',
        'ev_el_e6_body'      => "<p><strong>Añadir:</strong></p>\n<ul>\n<li>Brunch experiencial.</li>\n<li>Estaciones gastronómicas.</li>\n<li>Menú personalizado.</li>\n<li>Platos inspirados en nutrición deportiva.</li>\n<li>Showcooking.</li>\n<li>Menú wellness.</li>\n<li>Experiencia sensorial.</li>\n<li>Maridaje funcional.</li>\n<li>Diseño visual personalizado.</li>\n</ul>",
        // Servicio incluido (común a todas las experiencias)
        'ev_el_service_body' => "<ul>\n<li>Opciones alimentarias adaptadas: sin gluten, sin lactosa y vegetarianas.</li>\n<li>Uso preferente de materiales de servicio reciclables o reutilizables, reduciendo plásticos de un solo uso. Envases reutilizables o reciclables (tapers) para minimizar el desperdicio alimentario.</li>\n<li>Transporte y logística del servicio.</li>\n<li>Montaje y preparación del espacio.</li>\n<li>Material de servicio: menaje, mesas auxiliares y mantelería si procede.</li>\n<li>Personal para atención durante el servicio.</li>\n</ul>",
        // Condiciones de contratación y pago
        'ev_el_conditions_body' => "<h4>Condiciones de contratación</h4>\n<p>El número final de comensales y el desglose de menús (vegetarianos, intolerancias alimentarias) deberá confirmarse como máximo 8 días antes de la fecha del evento. Cualquier modificación posterior estará sujeta a disponibilidad y posible ajuste presupuestario.</p>\n<h4>Anulaciones</h4>\n<p>Las anulaciones deberán realizarse exclusivamente por teléfono o correo electrónico, siendo imprescindible la confirmación expresa por parte de la empresa. No se aceptarán anulaciones en el buzón de voz. Con menos de 24 horas laborables de antelación, se cobrará el importe íntegro del pedido.</p>\n<p>Toda la vajilla, menaje o material atribuibles al evento será de materiales desechables y biodegradables si no se indica lo contrario. La aceptación de esta propuesta implica la plena conformidad con todas las condiciones aquí expuestas.</p>\n<h4>Condiciones de pago</h4>\n<p>Para confirmar la fecha y el servicio, será necesario realizar un anticipo del 50% del importe total, que servirá como garantía de reserva. El 50% restante se deberá abonar 48 horas antes del inicio del servicio.</p>\n<p>En caso de contratación con menos de 7 días de antelación, se requerirá el 100% del importe total como pago único y anticipado.</p>",
        // Eventos — CTA
        'ev_cta_h2'          => '¿Tienes un evento en mente?',
        'ev_cta_text'        => 'Cuéntanos cómo lo imaginas y diseñamos el menú a tu medida.',
        'ev_cta_btn'         => 'Hablamos →',
        // Eventos — Marquee de categorías (separador " – ")
        'ev_marquee_text'    => 'Eventos de networking – Afterworks – Team buildings – Presentaciones – Encuentros corporativos o creativos',
        // Información de contacto
        'contact_phone'      => '+34 604 39 43 47',
        'contact_email'      => 'hola@miobiosport.com',
        'contact_address'    => 'C. de la Travesía, 15B, 46024 València',
        // Instagram CTA (aparece en home y en menú lunch)
        'ig_cta_text'        => '¿Qué hay hoy en TUOI? El menú del día se publica cada mañana en nuestro Instagram.',
        'ig_cta_url'         => 'https://www.instagram.com/tuoi.coffee/',
    ];

    // Traducciones en inglés definidas en código. La BD tiene prioridad (claves
    // '<clave>_en'), pero si un texto aún no está traducido en BD este array
    // sirve de fallback para que la página no muestre español en modo EN.
    $defaults_en = [
        // Homepage
        'hero_subtitle'         => 'Eat like you think.<br>Food adapted to the needs of your day.',
        'qs_label'              => 'Who are we?',
        'qs_h2'                 => 'From high performance<br>to your table.',
        'qs_p1'                 => 'TUOI is much more than a café: it\'s your place to enjoy, take care of yourself and feel good. A space where you can take a break, start your day or recharge while enjoying great coffee and healthy, flavourful food designed for your daily routine.',
        'qs_p2'                 => 'Taking care of yourself here is not complicated. It\'s natural, accessible… and appealing. Behind TUOI is the knowledge of <strong><a href="https://miobiosport.com/" target="_blank">MIOBIO</a></strong>, specialists in functional nutrition applied to elite sport. All that expertise translates into something very simple: giving you options that not only taste great, but help you have more energy, feel better and keep your pace.',
        'fil_label'             => 'Our philosophy',
        'fil_h2'                => 'Everything under one philosophy:<br>functional, balanced and flavourful nutrition.',
        'card_balance_title'    => 'Balanced nutrition',
        'card_balance_desc'     => 'Every dish designed to give you what you need — no excesses, no gaps. Real nutrition in every bite.',
        'card_energy_title'     => 'Power up your morning',
        'card_energy_desc'      => 'Breakfasts designed to wake up your performance from the very first hour of the day. No artificial stimulants.',
        'card_focus_title'      => 'Sustained focus',
        'card_focus_desc'       => 'No sugar spikes, no mid-afternoon slumps. Food that keeps your mind active when you need it most.',
        'card_power_title'      => 'Perform at your best',
        'card_power_desc'       => 'Proteins, carbohydrates and fats in the right measure so your body performs at full capacity, always.',
        'value1'                => 'Breakfasts focused on activating energy',
        'value2'                => 'Lunches designed to sustain performance',
        'value3'                => 'Meals aimed at recovery',
        'value4'                => 'Options adapted to different nutritional needs',
        // Quiénes somos page
        'qs_page_hero_label'    => 'Our story',
        'qs_page_hero_h1'       => 'Who are we?',
        'qs_page_hero_sub'      => 'From elite sport to your work desk.',
        'qs_page_b1_label'      => 'Who we are',
        'qs_page_b1_h2'         => 'Your place to take care of yourself without the hassle',
        'qs_page_b1_p1'         => 'TUOI is much more than a café: it\'s your place to enjoy, take care of yourself and feel good.',
        'qs_page_b1_p2'         => 'A space where you can take a break, start your day or recharge while enjoying coffee and healthy, flavourful food designed for your daily routine.',
        'qs_page_b1_p3'         => 'Taking care of yourself here is not complicated. It\'s natural, accessible… and appealing.',
        'qs_page_b2_label'      => 'Our origin',
        'qs_page_b2_h2'         => 'The knowledge of elite sport, at your table',
        'qs_page_b2_p1'         => 'Behind TUOI is the knowledge of <strong>MIOBIO</strong>, specialists in functional nutrition applied to elite sport. All that expertise translates into something very simple: giving you options that not only taste great, but help you have more energy, feel better and keep your pace.',
        'qs_page_b2_p2'         => 'Because what you eat influences how you feel.',
        'qs_page_b3_label'      => 'Our offering',
        'qs_page_b3_h2'         => 'At TUOI you can',
        'qs_page_b3_intro'      => 'At TUOI you can:',
        'qs_page_b3_li1'        => 'Start the day with breakfasts that activate your energy',
        'qs_page_b3_li2'        => 'Enjoy coffee and healthy options at any time',
        'qs_page_b3_li3'        => 'Take a break with balanced food that truly appeals',
        'qs_page_b3_p'          => 'All under one idea: eat well without the fuss.',
        'qs_page_close_p'       => 'TUOI is the meeting point between high-performance knowledge and your everyday life. <strong>A place where healthy eating becomes a natural part of your routine.</strong>',
        'qs_page_close_btn'     => 'Explore the menu',
        // Eventos — página principal
        'ev_hero_label'         => 'Events · TUOI',
        'ev_hero_h1'            => 'Events with purpose, energy and meaning',
        'ev_hero_sub'           => 'Gastronomic experiences that enhance every gathering.',
        'ev_hero_cta_primary'   => "Let's talk about your event",
        'ev_hero_cta_secondary' => 'See menus',
        'ev_intro_label'        => 'Our philosophy',
        'ev_intro_p1'           => 'At TUOI we bring our functional coffee & smart food philosophy to the world of events. We design gastronomic experiences that do not just accompany, but enhance what happens at every gathering: sharper focus, better energy and a genuine sense of wellbeing.',
        'ev_intro_p2'           => 'We work with local ingredients and balanced proposals that adapt to the pace and goal of each gathering. The result: light, tasty and functional food that avoids energy dips and accompanies the natural rhythm of each moment.',
        'ev_why_label'          => 'Why TUOI',
        'ev_why_h2'             => 'Why TUOI?',
        'ev_why_b1_title'       => 'Local and meaningful',
        'ev_why_b1_desc'        => 'We work with local ingredients, responsible materials and careful processes. Because a great event should not cost the planet.',
        'ev_why_b2_title'       => 'Light and real',
        'ev_why_b2_desc'        => 'Real food, no ultra-processed ingredients or excess. Tasty, balanced and easy to enjoy — no heaviness.',
        'ev_why_b3_title'       => 'Energy that keeps up',
        'ev_why_b3_desc'        => 'We design menus so the event flows: steady energy, an alert mind and zero slumps.',
        'ev_why_b4_title'       => 'Made for your event',
        'ev_why_b4_desc'        => 'Every proposal adapts to your needs: the format, the people and what you want to convey.',
        'ev_social_label'       => 'They trust us',
        'ev_social_quote'       => 'We organised an afterwork for 40 people and the difference was clear: people connected, ate well and no one suffered the mid-afternoon slump. We\'ll be back.',
        'ev_social_author'      => 'Marta Soler',
        'ev_social_role'        => 'People & Culture · Innovae',
        'ev_menus_label'        => 'Our menus',
        'ev_menus_h2'           => 'Menus that adapt to your event',
        'ev_menus_intro'        => 'We offer different formats that fit the type of gathering and the experience you want to create.',
        'ev_opt1_label'         => 'TUOI Experiences',
        'ev_opt1_title'         => 'TUOI Experiences',
        'ev_opt1_desc'          => 'Three formats designed to suit every type of gathering. Choose and we take care of the rest.',
        'ev_opt1_cta'           => 'See experiences',
        'ev_opt2_label'         => 'Tailored for you',
        'ev_opt2_title'         => 'Designed for you',
        'ev_opt2_desc'          => 'We design the menu with you according to the format, the people and what you want to convey.',
        'ev_opt2_cta_primary'   => 'Discover your options',
        'ev_opt2_cta_secondary' => 'Contact us',
        'ev_marquee_text'       => 'Networking events – Afterworks – Team buildings – Presentations – Corporate or creative gatherings',
        // Eventos — Listos para disfrutar
        'ev_el_h1'              => 'Ready to enjoy',
        'ev_el_intro'           => 'Three formats designed to accompany every type of gathering: from an energising break to a complete gastronomic experience.',
        'ev_el_back_text'       => 'Back to Events',
        'ev_el_cat1_label'      => 'Corporate catering',
        'ev_el_cat1_title'      => 'Coffee Break',
        'ev_el_cat1_audience'   => 'Meetings · Workshops · Corporate events · Presentations · Sports days',
        'ev_el_cat2_label'      => 'Cocktail',
        'ev_el_cat2_title'      => 'Social Cocktail',
        'ev_el_cat3_label'      => 'Brunch & Lunch',
        'ev_el_cat3_title'      => 'Table Experience',
        'ev_el_service_label'   => 'Service',
        'ev_el_service_h2'      => 'What the service includes',
        'ev_el_conditions_label'=> 'Contracting and payment terms',
        'ev_el_e1_tagline'      => 'The perfect break to keep energy and momentum flowing throughout the event.',
        'ev_el_e1_body'         => "<p><strong>Concept:</strong> healthy, elegant and functional coffee break.</p>\n<p><strong>Ideal for:</strong></p>\n<ul>\n<li>Meetings</li>\n<li>Workshops</li>\n<li>Corporate events</li>\n<li>Presentations</li>\n<li>Sports days</li>\n</ul>\n<p><strong>Includes:</strong></p>\n<ul>\n<li>Specialty coffee, herbal teas, fresh orange juice, semi-skimmed, lactose-free and oat milk, water bottles.</li>\n<li>Mini bakery (croissants and chocolate pops).</li>\n<li>Mini savouries: brie, ham and tomato jam rolls; turkey with avocado cream; mini croissants with tomato, four cheeses and spinach.</li>\n<li>Fresh fruit.</li>\n<li>Vegan and gluten-free options available on request.</li>\n</ul>",
        'ev_el_e2_tagline'      => 'Much more than a coffee break: an experience designed to activate body and mind.',
        'ev_el_e2_body'         => "<p><strong>Concept:</strong> premium coffee break experience with a wellness and functional focus.</p>\n<p><strong>Includes:</strong></p>\n<ul>\n<li>Specialty coffee, herbal teas, fresh orange juice, semi-skimmed, lactose-free and oat milk, water bottles.</li>\n<li>Mini croissant.</li>\n<li>Mini cookies.</li>\n<li>Mini muffins.</li>\n<li>Mini savouries: brie, ham and tomato jam rolls; turkey with avocado cream; mini croissants with tomato, four cheeses and spinach.</li>\n<li>Mini pisto pastries.</li>\n<li>Yoghurt pot with homemade granola.</li>\n<li>Fresh fruit.</li>\n<li>Vegan and gluten-free options available on request.</li>\n</ul>",
        'ev_el_e3_tagline'      => 'A fresh, curated proposal for events where connecting is part of the experience.',
        'ev_el_e3_body'         => "<p><strong>Concept:</strong> dynamic, elegant and social cocktail.</p>\n<p><strong>Includes:</strong></p>\n<ul>\n<li>Drink: beer, soft drink or water.</li>\n<li>Iberian ham and avocado cream pastries.</li>\n<li>Spanish omelette.</li>\n</ul>",
        'ev_el_e4_tagline'      => 'A gastronomic experience designed to surprise, delight and create lasting memories.',
        'ev_el_e4_body'         => "<p><strong>Add:</strong></p>\n<ul>\n<li>Live cooking.</li>\n<li>Live stations.</li>\n<li>Functional pairings.</li>\n<li>Healthy mixology.</li>\n<li>Signature cocktails.</li>\n<li>Themed experiences.</li>\n<li>Menu designed around the event type.</li>\n<li>Premium presentation.</li>\n</ul>",
        'ev_el_e5_tagline'      => 'Real, balanced food for gatherings where sharing is part of the moment.',
        'ev_el_e5_body'         => "<p><strong>Concept:</strong> healthy and modern informal brunch or lunch.</p>\n<p><strong>Includes:</strong></p>\n<ul>\n<li>Bowls.</li>\n<li>Focaccias.</li>\n<li>Premium salads.</li>\n<li>Sharing dishes.</li>\n<li>Functional options.</li>\n<li>Healthy desserts.</li>\n</ul>",
        'ev_el_e6_tagline'      => 'A premium gastronomic experience where wellbeing, aesthetics and flavour come together.',
        'ev_el_e6_body'         => "<p><strong>Add:</strong></p>\n<ul>\n<li>Experiential brunch.</li>\n<li>Gastronomic stations.</li>\n<li>Personalised menu.</li>\n<li>Dishes inspired by sports nutrition.</li>\n<li>Live cooking.</li>\n<li>Wellness menu.</li>\n<li>Sensory experience.</li>\n<li>Functional pairing.</li>\n<li>Custom visual design.</li>\n</ul>",
        'ev_el_service_body'    => "<ul>\n<li>Dietary options available: gluten-free, lactose-free and vegetarian.</li>\n<li>Preferential use of recyclable or reusable service materials, reducing single-use plastics. Reusable or recyclable containers to minimise food waste.</li>\n<li>Transport and service logistics.</li>\n<li>Set-up and preparation of the space.</li>\n<li>Service equipment: tableware, auxiliary tables and linen where applicable.</li>\n<li>Service staff for on-site attendance.</li>\n</ul>",
        'ev_el_conditions_body' => "<h4>Contracting conditions</h4>\n<p>The final number of guests and the menu breakdown (vegetarian, dietary intolerances) must be confirmed no later than 8 days before the event date. Any subsequent changes are subject to availability and possible budget adjustment.</p>\n<h4>Cancellations</h4>\n<p>Cancellations must be made exclusively by phone or email, with express written confirmation from the company being required. Cancellations left as voicemails will not be accepted. With less than 24 working hours' notice, the full order amount will be charged.</p>\n<p>All tableware, equipment or materials attributable to the event will be made of disposable and biodegradable materials unless otherwise stated. Acceptance of this proposal implies full agreement with all the conditions set out herein.</p>\n<h4>Payment conditions</h4>\n<p>To confirm the date and the service, a 50% advance payment of the total amount is required as a booking deposit. The remaining 50% must be paid 48 hours before the start of the service.</p>\n<p>If the booking is made less than 7 days in advance, 100% of the total amount will be required as a single advance payment.</p>",
        // CTA
        'ev_cta_h2'             => 'Have an event in mind?',
        'ev_cta_text'           => 'Tell us how you picture it and we will design the menu for you.',
        'ev_cta_btn'            => "Let's talk →",
        // Contacto
        'contact_phone'         => '+34 604 39 43 47',
        'contact_email'         => 'hola@miobiosport.com',
        'contact_address'       => 'C. de la Travesía, 15B, 46024 València',
        // Instagram CTA
        'ig_cta_text'           => "What's on at TUOI today? The daily menu is posted every morning on our Instagram.",
    ];

    // Sin conexión: devolvemos los defaults (español) o los en-defaults según idioma.
    if (!$conexion) {
        if ($lang === 'en') {
            foreach ($defaults_en as $key => $val) {
                $defaults[$key] = $val;
            }
        }
        return $defaults;
    }

    // Cargamos TODA la tabla en memoria de una sola consulta (es pequeña, decenas
    // de filas) en vez de hacer un SELECT por clave. El @ silencia el warning si
    // la tabla aún no existe en una instalación nueva.
    $all = [];
    $result = @mysqli_query($conexion, "SELECT content_key, content_value FROM site_content");
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $all[$row['content_key']] = $row['content_value'];
        }
    }

    // Paso 1: sobrescribir los defaults con los valores en español guardados en BD.
    // Solo sobrescribimos claves conocidas; valores extra en BD se ignoran a propósito.
    foreach (array_keys($defaults) as $key) {
        if (isset($all[$key])) $defaults[$key] = $all[$key];
    }

    // Paso 2: si el idioma activo es inglés, buscar la versión '<clave>_en'.
    // Prioridad: BD > defaults_en > español (ya aplicado en paso 1).
    // Usamos !empty (no isset) para que un campo EN vacío en BD caiga al siguiente nivel.
    if ($lang === 'en') {
        foreach (array_keys($defaults) as $key) {
            $en_key = $key . '_en';
            if (!empty($all[$en_key])) {
                $defaults[$key] = $all[$en_key];
            } elseif (isset($defaults_en[$key])) {
                $defaults[$key] = $defaults_en[$key];
            }
        }
    }

    return $defaults;
}

/**
 * Returns files from $dir sorted by the admin-defined order stored in image_order.
 * Falls back to modification date (newest first) if no order has been saved.
 *
 * @param  mixed  $conexion  mysqli connection (or null/false)
 * @param  string $section   section key matching image_order.section
 * @param  string $dir       absolute path to the image directory
 * @param  string $glob      glob pattern, e.g. '*.{webp,jpg,jpeg,png}'
 * @return array  absolute file paths in display order
 */
function load_ordered_images($conexion, $section, $dir, $glob = '*.{webp,jpg,jpeg,png,pdf}') {
    // Si el directorio no existe, devolvemos array vacío en lugar de error
    // (las plantillas ya muestran un placeholder "próximamente" en ese caso).
    if (!is_dir($dir)) return [];

    // Listamos los ficheros en disco. GLOB_BRACE permite la sintaxis {webp,jpg,...}.
    $found = glob(rtrim($dir, '/') . '/' . $glob, GLOB_BRACE) ?: [];
    if (empty($found)) return [];

    // Intentamos respetar el orden guardado por el admin en la tabla image_order.
    if ($conexion) {
        try {
            $s   = mysqli_real_escape_string($conexion, $section);
            $res = mysqli_query($conexion,
                "SELECT filename, sort_order FROM image_order WHERE section = '$s' ORDER BY sort_order ASC"
            );
            if ($res && mysqli_num_rows($res) > 0) {
                // Construimos un mapa filename → sort_order para ordenación rápida.
                $order = [];
                while ($row = mysqli_fetch_assoc($res)) {
                    $order[$row['filename']] = (int) $row['sort_order'];
                }
                // Reordenamos los archivos encontrados:
                //  - Los que tienen orden guardado se sitúan según sort_order.
                //  - Los que no estén en BD (subidos pero aún no ordenados) caen
                //    al final (PHP_INT_MAX) y se desempatan alfabéticamente.
                usort($found, function ($a, $b) use ($order) {
                    $oa = $order[basename($a)] ?? PHP_INT_MAX;
                    $ob = $order[basename($b)] ?? PHP_INT_MAX;
                    return $oa !== $ob ? $oa - $ob : strcmp(basename($a), basename($b));
                });
                return $found;
            }
        } catch (\Exception $e) {
            // La tabla image_order aún no existe (instalación previa a la migración).
            // Caemos al ordenamiento por defecto sin romper la página.
        }
    }

    // Fallback sin BD o sin orden guardado: más recientes primero, para que las
    // imágenes nuevas aparezcan arriba sin necesidad de tocar el admin.
    usort($found, fn($a, $b) => filemtime($b) - filemtime($a));
    return $found;
}
