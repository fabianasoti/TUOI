<?php
function load_site_content($conexion, string $lang = 'es') {
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
        'ev_hero_h1'         => 'Celebra con nosotros',
        'ev_hero_sub'        => 'Organizamos eventos únicos con comida funcional y saludable.',
        // Eventos — Sección "Eventos"
        'ev_ev_label'        => 'Eventos',
        'ev_ev_h2'           => 'Tu evento, nuestro escenario',
        'ev_ev_desc'         => 'Organizamos todo tipo de celebraciones y eventos especiales con una propuesta culinaria funcional y memorable.',
        // Eventos — Sección "Networking"
        'ev_nw_label'        => 'Networking',
        'ev_nw_h2'           => 'Conecta mientras cuidas de ti',
        'ev_nw_desc'         => 'Espacios y propuestas pensadas para que tus eventos de networking sean tan energizantes como productivos. Comida funcional que activa la conversación.',
        // Eventos — Sección "Team Building"
        'ev_tb_label'        => 'Team Building',
        'ev_tb_h2'           => 'Team building con propósito',
        'ev_tb_desc'         => 'Diseñamos experiencias de team building centradas en el bienestar y la cohesión de equipo. Talleres de cocina saludable, catas y actividades que unen a las personas.',
        // Eventos — Sección "Catering"
        'ev_cat_label'       => 'Catering',
        'ev_cat_h2'          => 'Catering funcional y saludable',
        'ev_cat_desc'        => 'Menús a medida para todo tipo de eventos: reuniones de empresa, inauguraciones, bodas y celebraciones. Basados en alimentación funcional, equilibrada y deliciosa.',
        // Información de contacto
        'contact_phone'      => '+34 000 000 000',
        'contact_email'      => 'hola@tuoi.es',
        'contact_address'    => 'C. de la Travesía, 15B, 46024 València',
    ];

    if (!$conexion) return $defaults;

    $all = [];
    $result = @mysqli_query($conexion, "SELECT content_key, content_value FROM site_content");
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $all[$row['content_key']] = $row['content_value'];
        }
    }

    // Merge ES values first
    foreach (array_keys($defaults) as $key) {
        if (isset($all[$key])) $defaults[$key] = $all[$key];
    }

    // Override with EN values when lang=en
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
    if (!is_dir($dir)) return [];

    $found = glob(rtrim($dir, '/') . '/' . $glob, GLOB_BRACE) ?: [];
    if (empty($found)) return [];

    // Try to load saved order from DB
    if ($conexion) {
        try {
            $s   = mysqli_real_escape_string($conexion, $section);
            $res = mysqli_query($conexion,
                "SELECT filename, sort_order FROM image_order WHERE section = '$s' ORDER BY sort_order ASC"
            );
            if ($res && mysqli_num_rows($res) > 0) {
                $order = [];
                while ($row = mysqli_fetch_assoc($res)) {
                    $order[$row['filename']] = (int) $row['sort_order'];
                }
                usort($found, function ($a, $b) use ($order) {
                    $oa = $order[basename($a)] ?? PHP_INT_MAX;
                    $ob = $order[basename($b)] ?? PHP_INT_MAX;
                    return $oa !== $ob ? $oa - $ob : strcmp(basename($a), basename($b));
                });
                return $found;
            }
        } catch (\Exception $e) {
            // Table doesn't exist yet — fall through to default sort
        }
    }

    // Fallback: newest first
    usort($found, fn($a, $b) => filemtime($b) - filemtime($a));
    return $found;
}
