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
        'ev_why_b1_icon'     => '🌱',
        'ev_why_b1_title'    => 'Cerca y con sentido',
        'ev_why_b1_desc'     => 'Trabajamos con ingredientes de proximidad, materiales responsables y procesos cuidados. Porque un buen evento no debería pasarle factura al planeta.',
        'ev_why_b2_icon'     => '💚',
        'ev_why_b2_title'    => 'Ligero y de verdad',
        'ev_why_b2_desc'     => 'Comida real, sin ultraprocesados ni excesos. Sabrosa, equilibrada y fácil de disfrutar — sin pesadez.',
        'ev_why_b3_icon'     => '⚡',
        'ev_why_b3_title'    => 'Energía que acompaña',
        'ev_why_b3_desc'     => 'Pensamos los menús para que el evento fluya: energía constante, mente despierta y cero bajones.',
        'ev_why_b4_icon'     => '🎯',
        'ev_why_b4_title'    => 'Hecho para tu evento',
        'ev_why_b4_desc'     => 'Cada propuesta se adapta a lo que necesitas: el formato, las personas y lo que quieres transmitir.',
        // Eventos — Prueba social (testimonio + logos)
        'ev_social_label'    => 'Confían en nosotros',
        'ev_social_quote'    => 'Organizamos un afterwork para 40 personas y la diferencia se notó: la gente conectó, comió bien y nadie sufrió el bajón de media tarde. Volveremos.',
        'ev_social_author'   => 'Marta Soler',
        'ev_social_role'     => 'People & Culture · Innovae',
        // Eventos — Propuesta de menús (3 categorías)
        'ev_menus_label'     => 'Nuestros menús',
        'ev_menus_h2'        => 'Menús que se adaptan a tu evento',
        'ev_menus_intro'     => 'Ofrecemos diferentes formatos que se ajustan al tipo de encuentro y a la experiencia que quieres crear.',
        'ev_cb_label'        => 'Coffee Break',
        'ev_cb_h2'           => 'Coffee break',
        'ev_cb_desc'         => 'Opciones ágiles y equilibradas para pausas que reactivan, favorecen la concentración y mantienen la energía estable.',
        'ev_br_label'        => 'Brunch',
        'ev_br_h2'           => 'Brunch',
        'ev_br_desc'         => 'Una propuesta más completa y versátil, ideal para encuentros distendidos que combinan trabajo y socialización.',
        'ev_td_label'        => 'Tardeo',
        'ev_td_h2'           => 'Tardeo',
        'ev_td_desc'         => 'El formato perfecto para cerrar el día con un ambiente más relajado, sin renunciar a una alimentación cuidada.',
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
    ];

    // Sin conexión: devolvemos los defaults en español tal cual.
    if (!$conexion) return $defaults;

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
    // Usamos !empty (no isset) para que un campo EN vacío en el admin caiga
    // automáticamente al texto en español, en vez de mostrar un hueco.
    if ($lang === 'en') {
        foreach (array_keys($defaults) as $key) {
            $en_key = $key . '_en';
            if (!empty($all[$en_key])) {
                $defaults[$key] = $all[$en_key];
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
