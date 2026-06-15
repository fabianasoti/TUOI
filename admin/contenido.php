<?php
require_once 'config.php';
require_once dirname(__DIR__) . '/config/content_helper.php';

// Active editing language (ES default, EN via ?edit_lang=en)
$edit_lang  = ($_GET['edit_lang'] ?? 'es') === 'en' ? 'en' : 'es';
$key_suffix = $edit_lang === 'en' ? '_en' : '';

// Active section (hub when null)
$valid_sections = ['home', 'eventos', 'eventos-listos', 'eventos-medida', 'quienes'];
$section = $_GET['section'] ?? null;
// Compat: redirigir el antiguo "eventos-hijas" a la nueva sección por defecto
if ($section === 'eventos-hijas') $section = 'eventos-listos';
if ($section !== null && !in_array($section, $valid_sections, true)) {
    $section = null;
}

// Base keys (without suffix)
$base_keys = [
    // Homepage
    'hero_label', 'hero_h1', 'hero_subtitle',
    'qs_label', 'qs_h2', 'qs_p1', 'qs_p2',
    'fil_label', 'fil_h2',
    'card_balance_title', 'card_balance_desc',
    'card_energy_title',  'card_energy_desc',
    'card_focus_title',   'card_focus_desc',
    'card_power_title',   'card_power_desc',
    'value1', 'value2', 'value3', 'value4',
    // Eventos page — hero
    'ev_hero_label', 'ev_hero_h1', 'ev_hero_sub',
    'ev_hero_cta_primary', 'ev_hero_cta_secondary',
    // Eventos — Manifiesto / Nuestra filosofía (intro narrativa)
    'ev_intro_label', 'ev_intro_p1', 'ev_intro_p2',
    // Eventos — Por qué TUOI
    'ev_why_label', 'ev_why_h2',
    'ev_why_b1_title', 'ev_why_b1_desc',
    'ev_why_b2_title', 'ev_why_b2_desc',
    'ev_why_b3_title', 'ev_why_b3_desc',
    'ev_why_b4_title', 'ev_why_b4_desc',
    // Eventos — Prueba social (etiqueta de la sección)
    'ev_social_label',
    // Eventos — Propuesta de menús (intro + 3 categorías)
    'ev_menus_label', 'ev_menus_h2', 'ev_menus_intro',
    'ev_opt1_label', 'ev_opt1_title', 'ev_opt1_desc', 'ev_opt1_cta',
    'ev_opt2_label', 'ev_opt2_title', 'ev_opt2_desc',
    'ev_opt2_cta_primary', 'ev_opt2_cta_secondary',
    // Eventos — página "Experiencias listas"
    'ev_el_h1', 'ev_el_intro', 'ev_el_back_text',
    'ev_el_level_essential', 'ev_el_level_signature',
    'ev_el_cat1_label', 'ev_el_cat1_title', 'ev_el_cat1_audience',
    'ev_el_cat2_label', 'ev_el_cat2_title', 'ev_el_cat2_audience',
    'ev_el_cat3_label', 'ev_el_cat3_title', 'ev_el_cat3_audience',
    'ev_el_e1_name', 'ev_el_e1_tagline', 'ev_el_e1_body',
    'ev_el_e2_name', 'ev_el_e2_tagline', 'ev_el_e2_body',
    'ev_el_e3_name', 'ev_el_e3_tagline', 'ev_el_e3_body',
    'ev_el_e4_name', 'ev_el_e4_tagline', 'ev_el_e4_body',
    'ev_el_e5_name', 'ev_el_e5_tagline', 'ev_el_e5_body',
    'ev_el_e6_name', 'ev_el_e6_tagline', 'ev_el_e6_body',
    'ev_el_service_label', 'ev_el_service_h2', 'ev_el_service_body',
    'ev_el_conditions_label', 'ev_el_conditions_body',
    // Eventos — CTA y marquee
    'ev_cta_h2', 'ev_cta_text', 'ev_cta_btn',
    'ev_marquee_text',
    // Contacto
    'contact_phone', 'contact_email', 'contact_address',
    // Instagram CTA (home + menú lunch)
    'ig_cta_text', 'ig_cta_url',
    // Quiénes somos page
    'qs_page_hero_label', 'qs_page_hero_h1', 'qs_page_hero_sub',
    'qs_page_b1_label', 'qs_page_b1_h2', 'qs_page_b1_p1', 'qs_page_b1_p2', 'qs_page_b1_p3',
    'qs_page_b2_label', 'qs_page_b2_h2', 'qs_page_b2_p1', 'qs_page_b2_p2',
    'qs_page_b3_label', 'qs_page_b3_h2', 'qs_page_b3_intro',
    'qs_page_b3_li1', 'qs_page_b3_li2', 'qs_page_b3_li3', 'qs_page_b3_p',
    'qs_page_close_p', 'qs_page_close_btn',
];

// Allowed keys: base + _en variants
$allowed_keys = array_merge(
    $base_keys,
    array_map(fn($k) => $k . '_en', $base_keys)
);

$success = false;
$error   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $saved = 0;
    foreach ($_POST as $key => $value) {
        if (in_array($key, $allowed_keys, true)) {
            if (upsert_content($conexion, $key, $value)) $saved++;
        }
    }
    if ($saved > 0) {
        $lang_label = $edit_lang === 'en' ? ' (EN)' : '';
        $success = "Sección guardada{$lang_label} — $saved campo(s) actualizado(s).";
    } else {
        $error = 'No se pudo guardar. Comprueba la conexión a la base de datos.';
    }
}

// Load current values — seed with ES defaults so fields never appear empty
$content = load_site_content(null, 'es'); // PHP defaults only (no DB)
$res = mysqli_query($conexion, "SELECT content_key, content_value FROM site_content");
if ($res) {
    while ($row = mysqli_fetch_assoc($res)) {
        $content[$row['content_key']] = $row['content_value']; // DB overrides defaults
    }
}

function cv($content, $key, $default = '') {
    return htmlspecialchars($content[$key] ?? $default, ENT_QUOTES);
}

// Helper to build URLs preserving edit_lang
function section_url($section, $edit_lang) {
    $params = ['section' => $section];
    if ($edit_lang === 'en') $params['edit_lang'] = 'en';
    return '?' . http_build_query($params);
}

// Form action preserves both section and edit_lang
$form_qs = [];
if ($section !== null) $form_qs['section'] = $section;
if ($edit_lang === 'en') $form_qs['edit_lang'] = 'en';
$form_action = '?' . http_build_query($form_qs);

// Topbar title — para hijos de Eventos mostramos un breadcrumb "Eventos › Hija"
$section_titles = [
    'home'           => ['title' => 'Página de inicio',                  'sub' => 'Hero, Quiénes somos y Filosofía',                              'parent' => null],
    'eventos'        => ['title' => 'Página de eventos',                 'sub' => 'Hero, intro, por qué TUOI, opciones, CTA y contacto',          'parent' => null],
    'eventos-listos' => ['title' => 'Listos para disfrutar',             'sub' => 'Experiencias prediseñadas (Coffee Break, Cóctel, Table)',      'parent' => 'Eventos'],
    'eventos-medida' => ['title' => 'Evento a tu medida',                'sub' => 'Personalización completa del evento',                          'parent' => 'Eventos'],
    'quienes'        => ['title' => 'Página Quiénes somos',              'sub' => 'Bloques completos de la página',                               'parent' => null],
];
if ($section) {
    $st = $section_titles[$section];
    $tb_title = $st['parent']
        ? '<span class="tb-parent">' . htmlspecialchars($st['parent']) . '</span> <span class="tb-sep">›</span> ' . htmlspecialchars($st['title'])
        : htmlspecialchars($st['title']);
    $tb_sub = $st['sub'];
} else {
    $tb_title = 'Editar contenido';
    $tb_sub   = 'Elige la página que quieres editar';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>TUOI Admin — Contenido</title>
    <link rel="stylesheet" href="../assets/fonts/inter.css">
    <link rel="stylesheet" href="assets/css/admin.css?v=<?= @filemtime(__DIR__ . '/assets/css/admin.css') ?: time() ?>">
    <!-- Quill (editor rico) — usamos 1.3.7 porque emite HTML estándar
         (ul/ol/li) compatible con lo que renderiza el sitio público. -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/quill@1.3.7/dist/quill.snow.css">
    <script src="https://cdn.jsdelivr.net/npm/quill@1.3.7/dist/quill.min.js" defer></script>
</head>
<body>
<div class="admin-layout">
    <?php include 'partials/sidebar.php'; ?>

    <div class="main-content">
        <div class="topbar">
            <div>
                <div class="topbar-title"><?= $tb_title /* ya escapado en la lógica de arriba */ ?></div>
                <div class="topbar-sub"><?= htmlspecialchars($tb_sub) ?></div>
            </div>
            <div class="topbar-actions">
                <?php if ($section !== null): ?>
                    <a href="<?= htmlspecialchars($edit_lang === 'en' ? '?edit_lang=en' : '?') ?>" class="btn btn-secondary btn-sm">← Volver</a>
                <?php endif; ?>
                <a href="../index.php" target="_blank" class="btn btn-secondary btn-sm">🌐 Ver sitio</a>
            </div>
        </div>

        <div class="content-area">

            <?php include 'partials/toast.php'; ?>

            <?php if ($section === null): ?>
                <!-- ── HUB: cards por página, con agrupación visual ─────────────── -->
                <div class="hub-grid">

                    <a href="<?= section_url('home', $edit_lang) ?>" class="hub-card">
                        <div class="hub-card__icon">🏠</div>
                        <div class="hub-card__body">
                            <div class="hub-card__title">Página de inicio</div>
                            <div class="hub-card__desc">Hero, "Quiénes somos" (resumen) y "Nuestra filosofía".</div>
                        </div>
                    </a>

                    <a href="<?= section_url('quienes', $edit_lang) ?>" class="hub-card">
                        <div class="hub-card__icon">👥</div>
                        <div class="hub-card__body">
                            <div class="hub-card__title">Quiénes somos</div>
                            <div class="hub-card__desc">Hero, bloques 1–3, lista de propuesta y cierre.</div>
                        </div>
                    </a>

                    <!-- Grupo: Eventos + sus 2 páginas hijas -->
                    <div class="hub-group">
                        <div class="hub-group__header">
                            <span class="hub-group__icon">🎉</span>
                            <span class="hub-group__label">Eventos</span>
                        </div>
                        <div class="hub-group__children">
                            <a href="<?= section_url('eventos', $edit_lang) ?>" class="hub-card hub-card--parent">
                                <div class="hub-card__icon">🎉</div>
                                <div class="hub-card__body">
                                    <div class="hub-card__title">Página principal de Eventos</div>
                                    <div class="hub-card__desc">Hero, intro, por qué TUOI, las 2 opciones, CTA y contacto.</div>
                                </div>
                            </a>
                            <a href="<?= section_url('eventos-listos', $edit_lang) ?>" class="hub-card hub-card--child">
                                <div class="hub-card__icon">✦</div>
                                <div class="hub-card__body">
                                    <div class="hub-card__title">Listos para disfrutar</div>
                                    <div class="hub-card__desc">6 experiencias prediseñadas + servicio + condiciones.</div>
                                </div>
                            </a>
                            <a href="<?= section_url('eventos-medida', $edit_lang) ?>" class="hub-card hub-card--child">
                                <div class="hub-card__icon">✦</div>
                                <div class="hub-card__body">
                                    <div class="hub-card__title">Evento a tu medida</div>
                                    <div class="hub-card__desc">Personalización completa del evento.</div>
                                </div>
                            </a>
                        </div>
                    </div>

                </div>
            <?php else: ?>

                <!-- Language tabs -->
                <div class="lang-tabs">
                    <a href="<?= section_url($section, 'es') ?>" class="lang-tab <?= $edit_lang === 'es' ? 'active' : '' ?>">
                        <span class="flag">🇪🇸</span> Español
                    </a>
                    <a href="<?= section_url($section, 'en') ?>" class="lang-tab <?= $edit_lang === 'en' ? 'active' : '' ?>">
                        <span class="flag">🇬🇧</span> English
                        <?php if ($edit_lang === 'en'): ?>
                            <span style="font-size:11px;color:var(--muted);margin-left:6px;">
                                (vacío = usa el texto en español)
                            </span>
                        <?php endif; ?>
                    </a>
                </div>

                <!-- Toolbar: buscador + acciones rápidas -->
                <div class="cards-toolbar">
                    <div class="cards-search">
                        <span class="cards-search__icon" aria-hidden="true">🔍</span>
                        <input type="search" id="cardsSearch" class="cards-search__input"
                               placeholder="Buscar campo o sección…" autocomplete="off">
                        <button type="button" id="cardsSearchClear" class="cards-search__clear" aria-label="Limpiar">×</button>
                    </div>
                    <div class="cards-toolbar__actions">
                        <button type="button" id="expandAllCards" class="btn btn-secondary btn-sm">Expandir todo</button>
                        <button type="button" id="collapseAllCards" class="btn btn-secondary btn-sm">Colapsar todo</button>
                    </div>
                </div>

            <?php endif; ?>

            <?php if ($section === 'home'): ?>

            <!-- ── HERO ──────────────────────────────── -->
            <div class="card">
                <div class="card-header">
                    <div class="card-title">
                        <span>🏠</span> Hero — Sección principal
                        <span class="section-badge">Portada</span>
                    </div>
                </div>
                <form method="post" action="<?= htmlspecialchars($form_action) ?>">
                    <?= csrf_field() ?>
                    <div class="form-group">
                        <label class="form-label">
                            Etiqueta superior <span class="hint">ej: "Cafetería · Valencia"</span>
                        </label>
                        <input name="hero_label<?= $key_suffix ?>" type="text" class="form-control"
                               value="<?= cv($content, 'hero_label' . $key_suffix) ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label">
                            Título principal <span class="hint">usa &lt;br&gt; para salto de línea</span>
                        </label>
                        <textarea name="hero_h1<?= $key_suffix ?>" class="form-control" rows="2"><?= cv($content, 'hero_h1' . $key_suffix) ?></textarea>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Subtítulo <span class="hint">debajo del título</span></label>
                        <textarea name="hero_subtitle<?= $key_suffix ?>" class="form-control" rows="2"><?= cv($content, 'hero_subtitle' . $key_suffix) ?></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">💾 Guardar hero</button>
                </form>
            </div>

            <!-- ── QUIÉNES SOMOS ─────────────────────── -->
            <div class="card">
                <div class="card-header">
                    <div class="card-title">
                        <span>👥</span> ¿Quiénes somos?
                        <span class="section-badge">Portada</span>
                    </div>
                </div>
                <form method="post" action="<?= htmlspecialchars($form_action) ?>">
                    <?= csrf_field() ?>
                    <div class="form-grid-2">
                        <div class="form-group">
                            <label class="form-label">Etiqueta</label>
                            <input name="qs_label<?= $key_suffix ?>" type="text" class="form-control"
                                   value="<?= cv($content, 'qs_label' . $key_suffix) ?>">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Título <span class="hint">&lt;br&gt; para salto</span></label>
                            <input name="qs_h2<?= $key_suffix ?>" type="text" class="form-control"
                                   value="<?= cv($content, 'qs_h2' . $key_suffix) ?>">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Párrafo 1</label>
                        <textarea name="qs_p1<?= $key_suffix ?>" class="form-control" rows="4"><?= cv($content, 'qs_p1' . $key_suffix) ?></textarea>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Párrafo 2</label>
                        <textarea name="qs_p2<?= $key_suffix ?>" class="form-control js-richtext" rows="4"><?= cv($content, 'qs_p2' . $key_suffix) ?></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">💾 Guardar sección</button>
                </form>
            </div>

            <!-- ── FILOSOFÍA ─────────────────────────── -->
            <div class="card">
                <div class="card-header">
                    <div class="card-title">
                        <span>💡</span> Nuestra Filosofía
                        <span class="section-badge">Portada</span>
                    </div>
                </div>
                <form method="post" action="<?= htmlspecialchars($form_action) ?>">
                    <?= csrf_field() ?>
                    <div class="form-group">
                        <label class="form-label">Etiqueta</label>
                        <input name="fil_label<?= $key_suffix ?>" type="text" class="form-control"
                               value="<?= cv($content, 'fil_label' . $key_suffix) ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Título <span class="hint">&lt;br&gt; para salto</span></label>
                        <textarea name="fil_h2<?= $key_suffix ?>" class="form-control" rows="2"><?= cv($content, 'fil_h2' . $key_suffix) ?></textarea>
                    </div>

                    <hr class="section-divider">
                    <p style="font-size:13px;color:var(--muted);margin-bottom:16px;">Tarjetas de valores</p>

                    <div class="form-grid-2">
                        <div>
                            <div class="form-group">
                                <label class="form-label">⚖️ Balance — Título</label>
                                <input name="card_balance_title<?= $key_suffix ?>" type="text" class="form-control"
                                       value="<?= cv($content, 'card_balance_title' . $key_suffix) ?>">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Descripción</label>
                                <textarea name="card_balance_desc<?= $key_suffix ?>" class="form-control" rows="3"><?= cv($content, 'card_balance_desc' . $key_suffix) ?></textarea>
                            </div>
                        </div>
                        <div>
                            <div class="form-group">
                                <label class="form-label">⚡ Energy — Título</label>
                                <input name="card_energy_title<?= $key_suffix ?>" type="text" class="form-control"
                                       value="<?= cv($content, 'card_energy_title' . $key_suffix) ?>">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Descripción</label>
                                <textarea name="card_energy_desc<?= $key_suffix ?>" class="form-control" rows="3"><?= cv($content, 'card_energy_desc' . $key_suffix) ?></textarea>
                            </div>
                        </div>
                        <div>
                            <div class="form-group">
                                <label class="form-label">🎯 Focus — Título</label>
                                <input name="card_focus_title<?= $key_suffix ?>" type="text" class="form-control"
                                       value="<?= cv($content, 'card_focus_title' . $key_suffix) ?>">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Descripción</label>
                                <textarea name="card_focus_desc<?= $key_suffix ?>" class="form-control" rows="3"><?= cv($content, 'card_focus_desc' . $key_suffix) ?></textarea>
                            </div>
                        </div>
                        <div>
                            <div class="form-group">
                                <label class="form-label">💪 Power — Título</label>
                                <input name="card_power_title<?= $key_suffix ?>" type="text" class="form-control"
                                       value="<?= cv($content, 'card_power_title' . $key_suffix) ?>">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Descripción</label>
                                <textarea name="card_power_desc<?= $key_suffix ?>" class="form-control" rows="3"><?= cv($content, 'card_power_desc' . $key_suffix) ?></textarea>
                            </div>
                        </div>
                    </div>

                    <hr class="section-divider">
                    <p style="font-size:13px;color:var(--muted);margin-bottom:16px;">Lista de valores</p>

                    <?php for ($i = 1; $i <= 4; $i++): ?>
                    <div class="form-group">
                        <label class="form-label">Valor <?= $i ?></label>
                        <input name="value<?= $i ?><?= $key_suffix ?>" type="text" class="form-control"
                               value="<?= cv($content, "value{$i}{$key_suffix}") ?>">
                    </div>
                    <?php endfor; ?>

                    <button type="submit" class="btn btn-primary">💾 Guardar filosofía</button>
                </form>
            </div>

            <!-- ── INSTAGRAM CTA ────────────────────────── -->
            <div class="card">
                <div class="card-header">
                    <div class="card-title">
                        <span>📸</span> CTA de Instagram
                        <span class="section-badge">Portada · Menú lunch</span>
                    </div>
                </div>
                <form method="post" action="<?= htmlspecialchars($form_action) ?>">
                    <?= csrf_field() ?>
                    <div class="form-group">
                        <label class="form-label">
                            Texto del bloque
                            <span class="hint">aparece en la portada y en la página de Menú lunch</span>
                        </label>
                        <textarea name="ig_cta_text<?= $key_suffix ?>" class="form-control" rows="2"><?= cv($content, 'ig_cta_text' . $key_suffix) ?></textarea>
                    </div>
                    <?php if ($edit_lang === 'es'): ?>
                    <div class="form-group">
                        <label class="form-label">
                            URL de destino
                            <span class="hint">el mismo enlace para ES y EN</span>
                        </label>
                        <input name="ig_cta_url" type="url" class="form-control"
                               value="<?= cv($content, 'ig_cta_url') ?>"
                               placeholder="https://www.instagram.com/tuoi.coffee/">
                    </div>
                    <?php endif; ?>
                    <button type="submit" class="btn btn-primary">💾 Guardar CTA Instagram</button>
                </form>
            </div>

            <?php endif; /* section === home */ ?>

            <?php if ($section === 'eventos'): ?>

            <!-- ── EVENTOS ──────────────────────────────── -->
            <div class="card">
                <div class="card-header">
                    <div class="card-title">
                        <span>🎉</span> Eventos — Hero de página
                        <span class="section-badge">Eventos</span>
                    </div>
                </div>
                <form method="post" action="<?= htmlspecialchars($form_action) ?>">
                    <?= csrf_field() ?>
                    <div class="form-grid-2">
                        <div class="form-group">
                            <label class="form-label">Etiqueta superior</label>
                            <input name="ev_hero_label<?= $key_suffix ?>" type="text" class="form-control"
                                   value="<?= cv($content, 'ev_hero_label' . $key_suffix) ?>">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Subtítulo</label>
                            <input name="ev_hero_sub<?= $key_suffix ?>" type="text" class="form-control"
                                   value="<?= cv($content, 'ev_hero_sub' . $key_suffix) ?>">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Título H1</label>
                        <input name="ev_hero_h1<?= $key_suffix ?>" type="text" class="form-control"
                               value="<?= cv($content, 'ev_hero_h1' . $key_suffix) ?>">
                    </div>
                    <div class="form-grid-2">
                        <div class="form-group">
                            <label class="form-label">CTA principal (botón)</label>
                            <input name="ev_hero_cta_primary<?= $key_suffix ?>" type="text" class="form-control"
                                   value="<?= cv($content, 'ev_hero_cta_primary' . $key_suffix) ?>"
                                   placeholder="Hablemos de tu evento">
                        </div>
                        <div class="form-group">
                            <label class="form-label">CTA secundario (texto-link → menús)</label>
                            <input name="ev_hero_cta_secondary<?= $key_suffix ?>" type="text" class="form-control"
                                   value="<?= cv($content, 'ev_hero_cta_secondary' . $key_suffix) ?>"
                                   placeholder="Ver menús">
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">💾 Guardar hero</button>
                </form>
            </div>

            <!-- ── MARQUEE DE IMÁGENES (info card → enlace a imagenes.php) ─── -->
            <div class="card">
                <div class="card-header">
                    <div class="card-title">
                        <span>🖼️</span> Carrusel de imágenes
                        <span class="section-badge">Eventos</span>
                    </div>
                </div>
                <p style="font-size:14px;color:var(--muted);margin:0 0 14px;">
                    Las imágenes del marquee se gestionan desde la sección de imágenes (subir, ordenar, eliminar).
                </p>
                <a href="imagenes.php?s=eventos/carrusel" class="btn btn-secondary">📁 Gestionar imágenes del carrusel →</a>
            </div>

            <!-- ── NUESTRA FILOSOFÍA (manifiesto / intro) ── -->
            <div class="card">
                <div class="card-header">
                    <div class="card-title">
                        <span>🌿</span> Nuestra filosofía
                        <span class="section-badge">Eventos</span>
                    </div>
                </div>
                <p style="font-size:14px;color:var(--muted);margin:0 0 14px;">
                    Bloque manifiesto entre el carrusel y "Por qué TUOI". Si los dos párrafos quedan vacíos, la sección no se muestra.
                </p>
                <form method="post" action="<?= htmlspecialchars($form_action) ?>">
                    <?= csrf_field() ?>
                    <div class="form-group">
                        <label class="form-label">Etiqueta superior</label>
                        <input name="ev_intro_label<?= $key_suffix ?>" type="text" class="form-control"
                               value="<?= cv($content, 'ev_intro_label' . $key_suffix) ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Párrafo 1</label>
                        <textarea name="ev_intro_p1<?= $key_suffix ?>" class="form-control" rows="4"><?= cv($content, 'ev_intro_p1' . $key_suffix) ?></textarea>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Párrafo 2</label>
                        <textarea name="ev_intro_p2<?= $key_suffix ?>" class="form-control" rows="4"><?= cv($content, 'ev_intro_p2' . $key_suffix) ?></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">💾 Guardar filosofía</button>
                </form>
            </div>

            <!-- ── POR QUÉ TUOI ────────────────────────── -->
            <div class="card">
                <div class="card-header">
                    <div class="card-title">
                        <span>💡</span> Por qué TUOI
                        <span class="section-badge">Eventos</span>
                    </div>
                </div>
                <form method="post" action="<?= htmlspecialchars($form_action) ?>">
                    <?= csrf_field() ?>
                    <div class="form-grid-2">
                        <div class="form-group">
                            <label class="form-label">Etiqueta</label>
                            <input name="ev_why_label<?= $key_suffix ?>" type="text" class="form-control"
                                   value="<?= cv($content, 'ev_why_label' . $key_suffix) ?>">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Título H2</label>
                            <input name="ev_why_h2<?= $key_suffix ?>" type="text" class="form-control"
                                   value="<?= cv($content, 'ev_why_h2' . $key_suffix) ?>">
                        </div>
                    </div>

                    <hr class="section-divider">
                    <p style="font-size:13px;color:var(--muted);margin-bottom:16px;">Las 4 viñetas (título + descripción). Los iconos son fijos en el diseño.</p>

                    <?php for ($i = 1; $i <= 4; $i++): ?>
                    <div class="form-group" style="border-left:3px solid var(--border);padding-left:14px;margin-bottom:18px;">
                        <p style="font-size:12px;font-weight:600;color:var(--muted);margin-bottom:10px;">Viñeta <?= $i ?></p>
                        <div class="form-group">
                            <label class="form-label">Título</label>
                            <input name="ev_why_b<?= $i ?>_title<?= $key_suffix ?>" type="text" class="form-control"
                                   value="<?= cv($content, "ev_why_b{$i}_title" . $key_suffix) ?>">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Descripción</label>
                            <textarea name="ev_why_b<?= $i ?>_desc<?= $key_suffix ?>" class="form-control" rows="2"><?= cv($content, "ev_why_b{$i}_desc" . $key_suffix) ?></textarea>
                        </div>
                    </div>
                    <?php endfor; ?>

                    <hr class="section-divider">
                    <p style="font-size:13px;color:var(--muted);margin-bottom:8px;">
                        La imagen lateral se gestiona en
                        <a href="imagenes.php?s=eventos/por-que-tuoi" style="color:var(--primary);">📁 Imágenes — Por qué TUOI</a>
                        (se usa la primera imagen de la carpeta).
                    </p>

                    <button type="submit" class="btn btn-primary">💾 Guardar Por qué TUOI</button>
                </form>
            </div>

            <!-- ── PRUEBA SOCIAL — etiqueta + accesos ────── -->
            <div class="card">
                <div class="card-header">
                    <div class="card-title">
                        <span>💬</span> Prueba social — Encabezado
                        <span class="section-badge">Eventos</span>
                    </div>
                </div>
                <form method="post" action="<?= htmlspecialchars($form_action) ?>">
                    <?= csrf_field() ?>
                    <div class="form-group">
                        <label class="form-label">Etiqueta de la sección</label>
                        <input name="ev_social_label<?= $key_suffix ?>" type="text" class="form-control"
                               value="<?= cv($content, 'ev_social_label' . $key_suffix) ?>"
                               placeholder="Confían en nosotros">
                    </div>
                    <button type="submit" class="btn btn-primary">💾 Guardar etiqueta</button>
                </form>
                <hr class="section-divider">
                <p style="font-size:13px;color:var(--muted);margin:0 0 8px;">
                    Los testimonios (cita, autor, rol) se gestionan en
                    <a href="testimonios.php" style="color:var(--primary);">💬 Testimonios</a>.
                </p>
                <p style="font-size:13px;color:var(--muted);margin:0;">
                    Los logos de la sección se suben en
                    <a href="imagenes.php?s=eventos/logos" style="color:var(--primary);">📁 Imágenes — Logos</a>.
                </p>
            </div>

            <!-- ── PROPUESTA DE MENÚS — INTRO ──────────── -->
            <div class="card">
                <div class="card-header">
                    <div class="card-title">
                        <span>🍽️</span> Propuesta de menús — Encabezado
                        <span class="section-badge">Eventos</span>
                    </div>
                </div>
                <form method="post" action="<?= htmlspecialchars($form_action) ?>">
                    <?= csrf_field() ?>
                    <div class="form-grid-2">
                        <div class="form-group">
                            <label class="form-label">Etiqueta</label>
                            <input name="ev_menus_label<?= $key_suffix ?>" type="text" class="form-control"
                                   value="<?= cv($content, 'ev_menus_label' . $key_suffix) ?>">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Título H2</label>
                            <input name="ev_menus_h2<?= $key_suffix ?>" type="text" class="form-control"
                                   value="<?= cv($content, 'ev_menus_h2' . $key_suffix) ?>">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Texto introductorio</label>
                        <textarea name="ev_menus_intro<?= $key_suffix ?>" class="form-control" rows="2"><?= cv($content, 'ev_menus_intro' . $key_suffix) ?></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">💾 Guardar encabezado</button>
                </form>
            </div>

            <!-- ── OPCIÓN 1 — Experiencias listas para disfrutar ─ -->
            <div class="card">
                <div class="card-header">
                    <div class="card-title">
                        <span>✦</span> Opción 1 — Experiencias listas
                        <span class="section-badge">Eventos</span>
                    </div>
                </div>
                <form method="post" action="<?= htmlspecialchars($form_action) ?>">
                    <?= csrf_field() ?>
                    <div class="form-grid-2">
                        <div class="form-group">
                            <label class="form-label">Etiqueta</label>
                            <input name="ev_opt1_label<?= $key_suffix ?>" type="text" class="form-control"
                                   value="<?= cv($content, 'ev_opt1_label' . $key_suffix) ?>">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Texto del botón</label>
                            <input name="ev_opt1_cta<?= $key_suffix ?>" type="text" class="form-control"
                                   value="<?= cv($content, 'ev_opt1_cta' . $key_suffix) ?>">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Título</label>
                        <input name="ev_opt1_title<?= $key_suffix ?>" type="text" class="form-control"
                               value="<?= cv($content, 'ev_opt1_title' . $key_suffix) ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Descripción</label>
                        <textarea name="ev_opt1_desc<?= $key_suffix ?>" class="form-control" rows="3"><?= cv($content, 'ev_opt1_desc' . $key_suffix) ?></textarea>
                    </div>
                    <p style="font-size:13px;color:var(--muted);margin:6px 0 14px;">
                        El botón enlaza a la página
                        <a href="../pages/eventos/eventos-listos/" target="_blank" style="color:var(--primary);">/eventos/eventos-listos/</a>.
                    </p>
                    <button type="submit" class="btn btn-primary">💾 Guardar opción 1</button>
                </form>
            </div>

            <!-- ── OPCIÓN 2 — Evento a tu medida ─────────── -->
            <div class="card">
                <div class="card-header">
                    <div class="card-title">
                        <span>✦</span> Opción 2 — Evento a tu medida
                        <span class="section-badge">Eventos</span>
                    </div>
                </div>
                <form method="post" action="<?= htmlspecialchars($form_action) ?>">
                    <?= csrf_field() ?>
                    <div class="form-group">
                        <label class="form-label">Etiqueta</label>
                        <input name="ev_opt2_label<?= $key_suffix ?>" type="text" class="form-control"
                               value="<?= cv($content, 'ev_opt2_label' . $key_suffix) ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Título</label>
                        <input name="ev_opt2_title<?= $key_suffix ?>" type="text" class="form-control"
                               value="<?= cv($content, 'ev_opt2_title' . $key_suffix) ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Descripción</label>
                        <textarea name="ev_opt2_desc<?= $key_suffix ?>" class="form-control" rows="3"><?= cv($content, 'ev_opt2_desc' . $key_suffix) ?></textarea>
                    </div>
                    <div class="form-grid-2">
                        <div class="form-group">
                            <label class="form-label">CTA principal <span class="hint">→ /eventos/a-tu-medida/</span></label>
                            <input name="ev_opt2_cta_primary<?= $key_suffix ?>" type="text" class="form-control"
                                   value="<?= cv($content, 'ev_opt2_cta_primary' . $key_suffix) ?>">
                        </div>
                        <div class="form-group">
                            <label class="form-label">CTA secundario <span class="hint">→ /contacto/</span></label>
                            <input name="ev_opt2_cta_secondary<?= $key_suffix ?>" type="text" class="form-control"
                                   value="<?= cv($content, 'ev_opt2_cta_secondary' . $key_suffix) ?>">
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">💾 Guardar opción 2</button>
                </form>
            </div>

            <!-- ── CTA ─────────────────────────────────── -->
            <div class="card">
                <div class="card-header">
                    <div class="card-title">
                        <span>📣</span> Llamada a la acción (CTA)
                        <span class="section-badge">Eventos</span>
                    </div>
                </div>
                <form method="post" action="<?= htmlspecialchars($form_action) ?>">
                    <?= csrf_field() ?>
                    <div class="form-group">
                        <label class="form-label">Título H2</label>
                        <input name="ev_cta_h2<?= $key_suffix ?>" type="text" class="form-control"
                               value="<?= cv($content, 'ev_cta_h2' . $key_suffix) ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Texto</label>
                        <textarea name="ev_cta_text<?= $key_suffix ?>" class="form-control" rows="2"><?= cv($content, 'ev_cta_text' . $key_suffix) ?></textarea>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Texto del botón <span class="hint">enlaza al formulario de contacto en la misma página</span></label>
                        <input name="ev_cta_btn<?= $key_suffix ?>" type="text" class="form-control"
                               value="<?= cv($content, 'ev_cta_btn' . $key_suffix) ?>">
                    </div>
                    <button type="submit" class="btn btn-primary">💾 Guardar CTA</button>
                </form>
            </div>

            <!-- ── MARQUEE DE CATEGORÍAS ───────────────── -->
            <div class="card">
                <div class="card-header">
                    <div class="card-title">
                        <span>🎞️</span> Banner de categorías (marquee)
                        <span class="section-badge">Eventos</span>
                    </div>
                </div>
                <form method="post" action="<?= htmlspecialchars($form_action) ?>">
                    <?= csrf_field() ?>
                    <div class="form-group">
                        <label class="form-label">
                            Texto del banner
                            <span class="hint">separa las categorías con " – " (espacio · guión largo · espacio)</span>
                        </label>
                        <textarea name="ev_marquee_text<?= $key_suffix ?>" class="form-control" rows="2"><?= cv($content, 'ev_marquee_text' . $key_suffix) ?></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">💾 Guardar banner</button>
                </form>
            </div>

            <!-- ── CONTACTO ──────────────────────────────── -->
            <div class="card">
                <div class="card-header">
                    <div class="card-title">
                        <span>📞</span> Información de contacto
                        <span class="section-badge">Eventos</span>
                    </div>
                </div>
                <form method="post" action="<?= htmlspecialchars($form_action) ?>">
                    <?= csrf_field() ?>
                    <div class="form-group">
                        <label class="form-label">Teléfono</label>
                        <input name="contact_phone<?= $key_suffix ?>" type="text" class="form-control"
                               value="<?= cv($content, 'contact_phone' . $key_suffix) ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Email</label>
                        <input name="contact_email<?= $key_suffix ?>" type="text" class="form-control"
                               value="<?= cv($content, 'contact_email' . $key_suffix) ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Dirección</label>
                        <input name="contact_address<?= $key_suffix ?>" type="text" class="form-control"
                               value="<?= cv($content, 'contact_address' . $key_suffix) ?>">
                    </div>
                    <button type="submit" class="btn btn-primary">💾 Guardar contacto</button>
                </form>
            </div>

            <?php endif; /* section === eventos */ ?>

            <?php if ($section === 'eventos-listos'): ?>

            <!-- ── INTRO PÁGINA EXPERIENCIAS TUOI ───────── -->
            <div class="card" id="card-el-intro">
                <div class="card-header">
                    <div class="card-title">
                        <span>✦</span> Cabecera de la página
                        <span class="section-badge">Eventos › Listos para disfrutar</span>
                    </div>
                </div>
                <form method="post" action="<?= htmlspecialchars($form_action) ?>#card-el-intro">
                    <?= csrf_field() ?>
                    <p style="font-size:13px;color:var(--muted);margin:0 0 14px;">
                        Hero de <a href="../pages/eventos/eventos-listos/" target="_blank" style="color:var(--primary);">/eventos/eventos-listos/</a>. El título grande y la introducción que aparece debajo.
                    </p>
                    <div class="form-group">
                        <label class="form-label">Texto del enlace "volver" <span class="hint">arriba a la izquierda</span></label>
                        <input name="ev_el_back_text<?= $key_suffix ?>" type="text" class="form-control"
                               value="<?= cv($content, 'ev_el_back_text' . $key_suffix) ?>"
                               placeholder="Volver a Eventos">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Título grande (H1)</label>
                        <input name="ev_el_h1<?= $key_suffix ?>" type="text" class="form-control"
                               value="<?= cv($content, 'ev_el_h1' . $key_suffix) ?>"
                               placeholder="Listos para disfrutar">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Introducción</label>
                        <textarea name="ev_el_intro<?= $key_suffix ?>" class="form-control" rows="3"><?= cv($content, 'ev_el_intro' . $key_suffix) ?></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">💾 Guardar cabecera</button>
                </form>
            </div>

            <!-- ── CATEGORÍAS Y NIVELES ──────────────────── -->
            <div class="card" id="card-el-categories">
                <div class="card-header">
                    <div class="card-title">
                        <span>🗂️</span> Categorías y niveles
                        <span class="section-badge">Eventos › Listos para disfrutar</span>
                    </div>
                </div>
                <form method="post" action="<?= htmlspecialchars($form_action) ?>#card-el-categories">
                    <?= csrf_field() ?>
                    <p style="font-size:13px;color:var(--muted);margin:0 0 14px;">
                        Encabezado de cada una de las 3 categorías (Coffee Break, Social Cocktail, Table Experience) y el nombre de los 2 niveles compartidos (Essential / Signature).
                    </p>

                    <?php
                    $el_cats = [
                        [1, '☕', 'Coffee Break',       'Reuniones · Workshops · …'],
                        [2, '🥂', 'Social Cocktail',    ''],
                        [3, '🍽️', 'Table Experience',   ''],
                    ];
                    foreach ($el_cats as [$n, $catIcon, $catTitleHint, $audPlaceholder]): ?>
                    <div class="form-group" style="border-left:3px solid var(--border);padding-left:14px;margin-bottom:18px;">
                        <p style="font-size:12px;font-weight:600;color:var(--muted);margin-bottom:10px;">
                            <?= $catIcon ?> Categoría <?= $n ?> — <?= htmlspecialchars($catTitleHint) ?>
                        </p>
                        <div class="form-grid-2">
                            <div class="form-group">
                                <label class="form-label">Etiqueta superior</label>
                                <input name="ev_el_cat<?= $n ?>_label<?= $key_suffix ?>" type="text" class="form-control"
                                       value="<?= cv($content, "ev_el_cat{$n}_label" . $key_suffix) ?>">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Título</label>
                                <input name="ev_el_cat<?= $n ?>_title<?= $key_suffix ?>" type="text" class="form-control"
                                       value="<?= cv($content, "ev_el_cat{$n}_title" . $key_suffix) ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Audiencia <span class="hint">opcional — frase corta bajo el título</span></label>
                            <input name="ev_el_cat<?= $n ?>_audience<?= $key_suffix ?>" type="text" class="form-control"
                                   value="<?= cv($content, "ev_el_cat{$n}_audience" . $key_suffix) ?>"
                                   placeholder="<?= htmlspecialchars($audPlaceholder) ?>">
                        </div>
                    </div>
                    <?php endforeach; ?>

                    <hr class="section-divider">
                    <p style="font-size:13px;color:var(--muted);margin-bottom:12px;">Niveles (etiqueta de cada tarjeta)</p>
                    <div class="form-grid-2">
                        <div class="form-group">
                            <label class="form-label">Nivel básico</label>
                            <input name="ev_el_level_essential<?= $key_suffix ?>" type="text" class="form-control"
                                   value="<?= cv($content, 'ev_el_level_essential' . $key_suffix) ?>"
                                   placeholder="Essential">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Nivel premium</label>
                            <input name="ev_el_level_signature<?= $key_suffix ?>" type="text" class="form-control"
                                   value="<?= cv($content, 'ev_el_level_signature' . $key_suffix) ?>"
                                   placeholder="Signature">
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary">💾 Guardar categorías y niveles</button>
                </form>
            </div>

            <!-- ── EXPERIENCIAS (1 card por experiencia) ── -->
            <?php
            $el_experiences = [
                ['e1', 'Flow Coffee Essential',          '☕', 'Coffee Break — Essential'],
                ['e2', 'Flow Coffee Signature',          '☕', 'Coffee Break — Signature'],
                ['e3', 'Social Cocktail Essential',      '🥂', 'Cóctel — Essential'],
                ['e4', 'Social Cocktail Signature',      '🥂', 'Cóctel — Signature'],
                ['e5', 'Table Experience Essential',     '🍽️', 'Table — Essential'],
                ['e6', 'Table Experience Signature',     '🍽️', 'Table — Signature'],
            ];
            foreach ($el_experiences as [$exKey, $exName, $exIcon, $exShort]):
                $cardId = 'card-el-' . $exKey;
            ?>
            <div class="card" id="<?= $cardId ?>">
                <div class="card-header">
                    <div class="card-title">
                        <span><?= $exIcon ?></span> <?= htmlspecialchars($exName) ?>
                        <span class="section-badge"><?= htmlspecialchars($exShort) ?></span>
                        <span class="section-badge section-badge--muted">Eventos › Listos</span>
                    </div>
                </div>
                <form method="post" action="<?= htmlspecialchars($form_action) ?>#<?= $cardId ?>">
                    <?= csrf_field() ?>
                    <div class="form-group">
                        <label class="form-label">Nombre de la tarjeta</label>
                        <input name="ev_el_<?= $exKey ?>_name<?= $key_suffix ?>" type="text" class="form-control"
                               value="<?= cv($content, "ev_el_{$exKey}_name" . $key_suffix) ?>"
                               placeholder="<?= htmlspecialchars($exName) ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Tagline <span class="hint">frase corta de promesa</span></label>
                        <input name="ev_el_<?= $exKey ?>_tagline<?= $key_suffix ?>" type="text" class="form-control"
                               value="<?= cv($content, "ev_el_{$exKey}_tagline" . $key_suffix) ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Contenido</label>
                        <textarea name="ev_el_<?= $exKey ?>_body<?= $key_suffix ?>" class="form-control js-richtext" rows="10"><?= cv($content, "ev_el_{$exKey}_body" . $key_suffix) ?></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">💾 Guardar <?= htmlspecialchars($exName) ?></button>
                </form>
            </div>
            <?php endforeach; ?>

            <!-- ── LO QUE INCLUYE EL SERVICIO ──────────── -->
            <div class="card" id="card-el-service">
                <div class="card-header">
                    <div class="card-title">
                        <span>🛎️</span> Lo que incluye el servicio
                        <span class="section-badge">Eventos › Listos para disfrutar</span>
                    </div>
                </div>
                <form method="post" action="<?= htmlspecialchars($form_action) ?>#card-el-service">
                    <?= csrf_field() ?>
                    <div class="form-grid-2">
                        <div class="form-group">
                            <label class="form-label">Etiqueta superior</label>
                            <input name="ev_el_service_label<?= $key_suffix ?>" type="text" class="form-control"
                                   value="<?= cv($content, 'ev_el_service_label' . $key_suffix) ?>"
                                   placeholder="Servicio">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Título de la sección (H2)</label>
                            <input name="ev_el_service_h2<?= $key_suffix ?>" type="text" class="form-control"
                                   value="<?= cv($content, 'ev_el_service_h2' . $key_suffix) ?>"
                                   placeholder="Lo que incluye el servicio">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Lista de servicios <span class="hint">usa una lista con viñetas</span></label>
                        <textarea name="ev_el_service_body<?= $key_suffix ?>" class="form-control js-richtext" rows="8"><?= cv($content, 'ev_el_service_body' . $key_suffix) ?></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">💾 Guardar servicio</button>
                </form>
            </div>

            <!-- ── CONDICIONES DE CONTRATACIÓN ──────────── -->
            <div class="card" id="card-el-conditions">
                <div class="card-header">
                    <div class="card-title">
                        <span>📄</span> Condiciones de contratación y pago
                        <span class="section-badge">Eventos › Listos para disfrutar</span>
                    </div>
                </div>
                <form method="post" action="<?= htmlspecialchars($form_action) ?>#card-el-conditions">
                    <?= csrf_field() ?>
                    <div class="form-group">
                        <label class="form-label">Título del bloque desplegable</label>
                        <input name="ev_el_conditions_label<?= $key_suffix ?>" type="text" class="form-control"
                               value="<?= cv($content, 'ev_el_conditions_label' . $key_suffix) ?>"
                               placeholder="Condiciones de contratación y pago">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Texto completo <span class="hint">usa el botón "H4" para subtítulos</span></label>
                        <textarea name="ev_el_conditions_body<?= $key_suffix ?>" class="form-control js-richtext" data-richtext-headers="true" rows="14"><?= cv($content, 'ev_el_conditions_body' . $key_suffix) ?></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">💾 Guardar condiciones</button>
                </form>
            </div>

            <?php endif; /* section === eventos-listos */ ?>

            <?php if ($section === 'eventos-medida'): ?>

            <!-- ── EVENTO A TU MEDIDA — placeholder ─────── -->
            <div class="card" id="card-el-medida">
                <div class="card-header">
                    <div class="card-title">
                        <span>✦</span> Evento a tu medida
                        <span class="section-badge">Eventos › Evento a tu medida</span>
                    </div>
                </div>
                <p style="font-size:14px;color:var(--muted);margin:0;">
                    El contenido de <a href="../pages/eventos/a-tu-medida/" target="_blank" style="color:var(--primary);">/eventos/a-tu-medida/</a> está pendiente. Cuando lo tengamos definido, los campos editables aparecerán aquí.
                </p>
            </div>

            <?php endif; /* section === eventos-medida */ ?>

            <?php if ($section === 'quienes'): ?>

            <!-- ── QUIÉNES SOMOS (PÁGINA) ────────────────── -->
            <div class="card">
                <div class="card-header">
                    <div class="card-title">
                        <span>👥</span> Quiénes somos — Página completa
                        <span class="section-badge">Quiénes somos</span>
                    </div>
                </div>
                <form method="post" action="<?= htmlspecialchars($form_action) ?>">
                    <?= csrf_field() ?>

                    <p style="font-size:13px;color:var(--muted);margin-bottom:16px;">Hero de página</p>
                    <div class="form-grid-2">
                        <div class="form-group">
                            <label class="form-label">Etiqueta superior</label>
                            <input name="qs_page_hero_label<?= $key_suffix ?>" type="text" class="form-control"
                                   value="<?= cv($content, 'qs_page_hero_label' . $key_suffix) ?>">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Subtítulo</label>
                            <input name="qs_page_hero_sub<?= $key_suffix ?>" type="text" class="form-control"
                                   value="<?= cv($content, 'qs_page_hero_sub' . $key_suffix) ?>">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Título H1</label>
                        <input name="qs_page_hero_h1<?= $key_suffix ?>" type="text" class="form-control"
                               value="<?= cv($content, 'qs_page_hero_h1' . $key_suffix) ?>">
                    </div>

                    <hr class="section-divider">
                    <p style="font-size:13px;color:var(--muted);margin-bottom:16px;">Bloque 1 — Tu lugar para cuidarte</p>
                    <div class="form-grid-2">
                        <div class="form-group">
                            <label class="form-label">Etiqueta</label>
                            <input name="qs_page_b1_label<?= $key_suffix ?>" type="text" class="form-control"
                                   value="<?= cv($content, 'qs_page_b1_label' . $key_suffix) ?>">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Título H2</label>
                            <input name="qs_page_b1_h2<?= $key_suffix ?>" type="text" class="form-control"
                                   value="<?= cv($content, 'qs_page_b1_h2' . $key_suffix) ?>">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Párrafo 1</label>
                        <textarea name="qs_page_b1_p1<?= $key_suffix ?>" class="form-control" rows="3"><?= cv($content, 'qs_page_b1_p1' . $key_suffix) ?></textarea>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Párrafo 2</label>
                        <textarea name="qs_page_b1_p2<?= $key_suffix ?>" class="form-control" rows="2"><?= cv($content, 'qs_page_b1_p2' . $key_suffix) ?></textarea>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Párrafo 3</label>
                        <textarea name="qs_page_b1_p3<?= $key_suffix ?>" class="form-control" rows="2"><?= cv($content, 'qs_page_b1_p3' . $key_suffix) ?></textarea>
                    </div>

                    <hr class="section-divider">
                    <p style="font-size:13px;color:var(--muted);margin-bottom:16px;">Bloque 2 — Nuestro origen</p>
                    <div class="form-grid-2">
                        <div class="form-group">
                            <label class="form-label">Etiqueta</label>
                            <input name="qs_page_b2_label<?= $key_suffix ?>" type="text" class="form-control"
                                   value="<?= cv($content, 'qs_page_b2_label' . $key_suffix) ?>">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Título H2</label>
                            <input name="qs_page_b2_h2<?= $key_suffix ?>" type="text" class="form-control"
                                   value="<?= cv($content, 'qs_page_b2_h2' . $key_suffix) ?>">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Párrafo 1</label>
                        <textarea name="qs_page_b2_p1<?= $key_suffix ?>" class="form-control js-richtext" rows="3"><?= cv($content, 'qs_page_b2_p1' . $key_suffix) ?></textarea>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Párrafo 2</label>
                        <textarea name="qs_page_b2_p2<?= $key_suffix ?>" class="form-control" rows="3"><?= cv($content, 'qs_page_b2_p2' . $key_suffix) ?></textarea>
                    </div>

                    <hr class="section-divider">
                    <p style="font-size:13px;color:var(--muted);margin-bottom:16px;">Bloque 3 — Nuestra propuesta</p>
                    <div class="form-grid-2">
                        <div class="form-group">
                            <label class="form-label">Etiqueta</label>
                            <input name="qs_page_b3_label<?= $key_suffix ?>" type="text" class="form-control"
                                   value="<?= cv($content, 'qs_page_b3_label' . $key_suffix) ?>">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Título H2</label>
                            <input name="qs_page_b3_h2<?= $key_suffix ?>" type="text" class="form-control"
                                   value="<?= cv($content, 'qs_page_b3_h2' . $key_suffix) ?>">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Introducción de lista</label>
                        <textarea name="qs_page_b3_intro<?= $key_suffix ?>" class="form-control" rows="2"><?= cv($content, 'qs_page_b3_intro' . $key_suffix) ?></textarea>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Lista — ítem 1 <span class="hint">puedes usar &lt;strong&gt;</span></label>
                        <input name="qs_page_b3_li1<?= $key_suffix ?>" type="text" class="form-control"
                               value="<?= cv($content, 'qs_page_b3_li1' . $key_suffix) ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Lista — ítem 2</label>
                        <input name="qs_page_b3_li2<?= $key_suffix ?>" type="text" class="form-control"
                               value="<?= cv($content, 'qs_page_b3_li2' . $key_suffix) ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Lista — ítem 3</label>
                        <input name="qs_page_b3_li3<?= $key_suffix ?>" type="text" class="form-control"
                               value="<?= cv($content, 'qs_page_b3_li3' . $key_suffix) ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Párrafo de cierre del bloque</label>
                        <textarea name="qs_page_b3_p<?= $key_suffix ?>" class="form-control" rows="2"><?= cv($content, 'qs_page_b3_p' . $key_suffix) ?></textarea>
                    </div>

                    <hr class="section-divider">
                    <p style="font-size:13px;color:var(--muted);margin-bottom:16px;">Cierre de página</p>
                    <div class="form-group">
                        <label class="form-label">Párrafo final</label>
                        <textarea name="qs_page_close_p<?= $key_suffix ?>" class="form-control js-richtext" rows="3"><?= cv($content, 'qs_page_close_p' . $key_suffix) ?></textarea>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Texto del botón CTA</label>
                        <input name="qs_page_close_btn<?= $key_suffix ?>" type="text" class="form-control"
                               value="<?= cv($content, 'qs_page_close_btn' . $key_suffix) ?>">
                    </div>

                    <button type="submit" class="btn btn-primary">💾 Guardar página quiénes somos</button>
                </form>
            </div>

            <?php endif; /* section === quienes */ ?>

        </div>
    </div>
</div>
<script>
(function () {
    // Mantener la posición de scroll al guardar un formulario. Soporta dos casos:
    // 1. Si el form action incluye un #card-id, el navegador ya hace scroll a esa ancla.
    // 2. Si no, guardamos window.scrollY en sessionStorage y lo restauramos al recargar.
    var KEY = 'admin_scroll_' + location.pathname + location.search;

    document.querySelectorAll('form').forEach(function (form) {
        form.addEventListener('submit', function () {
            // Si el action ya lleva ancla, dejamos que el navegador haga su trabajo.
            if ((form.getAttribute('action') || '').indexOf('#') === -1) {
                try { sessionStorage.setItem(KEY, String(window.scrollY)); } catch (e) {}
            }
        });
    });

    window.addEventListener('load', function () {
        // Si hay ancla en la URL, el navegador ya hace scroll a la card → no tocamos.
        if (location.hash) return;
        var y;
        try { y = sessionStorage.getItem(KEY); } catch (e) {}
        if (y !== null && y !== undefined) {
            sessionStorage.removeItem(KEY);
            // Tras el render para que la altura del DOM esté estable.
            requestAnimationFrame(function () {
                window.scrollTo({ top: parseInt(y, 10), behavior: 'instant' });
            });
        }
    });

    // Auto-ocultar el toast tras unos segundos para que no tape el contenido.
    var toast = document.querySelector('.toast, .alert-success, [data-toast]');
    if (toast) {
        setTimeout(function () {
            toast.style.transition = 'opacity .4s ease';
            toast.style.opacity = '0';
            setTimeout(function () { toast.style.display = 'none'; }, 450);
        }, 3500);
    }

    // ── Tarjetas plegables + buscador ──────────────────────────────────────
    // Convierte cada .card del area en un acordeón: el header siempre visible y
    // el resto del contenido en un wrapper que se oculta. El estado de "qué
    // card está abierta" se persiste por sección en sessionStorage.
    var area = document.querySelector('.content-area');
    if (!area) return;
    var cards = Array.prototype.slice.call(area.querySelectorAll('.card'));
    // Filtramos las "info cards" del hub (las hub-card) y sólo dejamos las que
    // tengan un header (las del editor real).
    cards = cards.filter(function (c) {
        return c.querySelector(':scope > .card-header');
    });
    if (!cards.length) return;

    var SECTION_KEY = 'admin_open_card_' + location.pathname + (location.search || '');

    function ensureBody(card) {
        if (card.__bodyWrapped) return card.querySelector(':scope > .card-body');
        var header = card.querySelector(':scope > .card-header');
        if (!header) return null;
        var body = document.createElement('div');
        body.className = 'card-body';
        // Mover todos los hermanos siguientes al header dentro del body.
        var node = header.nextSibling;
        while (node) {
            var next = node.nextSibling;
            body.appendChild(node);
            node = next;
        }
        card.appendChild(body);
        card.__bodyWrapped = true;
        return body;
    }

    function addChevron(card) {
        // Insertamos el chevron dentro del .card-title (que ya es flex con gap)
        // para que no se rompa el layout si el header tiene además un botón
        // (que queda alineado a la derecha por justify-content: space-between).
        var title = card.querySelector(':scope > .card-header .card-title');
        if (!title || title.querySelector('.card-chevron')) return;
        var chev = document.createElement('span');
        chev.className = 'card-chevron';
        chev.setAttribute('aria-hidden', 'true');
        chev.textContent = '▾';
        title.appendChild(chev);
    }

    function assignId(card, i) {
        if (!card.id) card.id = 'card-auto-' + i;
        return card.id;
    }

    function open(card) {
        card.classList.remove('is-collapsed');
        card.setAttribute('aria-expanded', 'true');
    }
    function close(card) {
        card.classList.add('is-collapsed');
        card.setAttribute('aria-expanded', 'false');
    }

    // Inicializar markup
    cards.forEach(function (card, i) {
        assignId(card, i);
        ensureBody(card);
        addChevron(card);
        card.classList.add('is-collapsible');
        var header = card.querySelector(':scope > .card-header');
        header.setAttribute('role', 'button');
        header.setAttribute('tabindex', '0');
        header.addEventListener('click', function (e) {
            // Evitar interferir con clicks en enlaces o botones dentro del header.
            if (e.target.closest('a, button')) return;
            toggle(card);
        });
        header.addEventListener('keydown', function (e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                toggle(card);
            }
        });
    });

    function toggle(card) {
        if (card.classList.contains('is-collapsed')) {
            open(card);
            try { sessionStorage.setItem(SECTION_KEY, card.id); } catch (e) {}
        } else {
            close(card);
            try {
                if (sessionStorage.getItem(SECTION_KEY) === card.id) {
                    sessionStorage.removeItem(SECTION_KEY);
                }
            } catch (e) {}
        }
    }

    // Estado inicial: todas cerradas, luego abrimos la del hash o la recordada.
    cards.forEach(close);

    var targetId = null;
    if (location.hash && location.hash.length > 1) {
        targetId = location.hash.slice(1);
    } else {
        try { targetId = sessionStorage.getItem(SECTION_KEY); } catch (e) {}
    }
    if (targetId) {
        var target = document.getElementById(targetId);
        if (target && target.classList.contains('card')) open(target);
    }

    // Al enviar un formulario dentro de una card, recordamos su id para que
    // siga abierta tras recargar.
    cards.forEach(function (card) {
        var form = card.querySelector('form');
        if (!form) return;
        form.addEventListener('submit', function () {
            try { sessionStorage.setItem(SECTION_KEY, card.id); } catch (e) {}
        });
    });

    // ── Botones Expandir/Colapsar todo ─────────────────────────────────────
    var btnExpand   = document.getElementById('expandAllCards');
    var btnCollapse = document.getElementById('collapseAllCards');
    if (btnExpand)   btnExpand.addEventListener('click',   function () { cards.forEach(open); });
    if (btnCollapse) btnCollapse.addEventListener('click', function () { cards.forEach(close); });

    // ── Buscador en vivo ───────────────────────────────────────────────────
    var input  = document.getElementById('cardsSearch');
    var clear  = document.getElementById('cardsSearchClear');
    if (!input) return;

    // Cacheamos el texto buscable de cada card (título + labels + placeholders).
    var cardText = cards.map(function (card) {
        var parts = [];
        parts.push((card.querySelector('.card-title') || {}).textContent || '');
        Array.prototype.forEach.call(card.querySelectorAll('label, .form-label, .hint, .section-badge'), function (el) {
            parts.push(el.textContent || '');
        });
        Array.prototype.forEach.call(card.querySelectorAll('input[placeholder], textarea[placeholder]'), function (el) {
            parts.push(el.getAttribute('placeholder') || '');
        });
        return parts.join(' ').toLowerCase();
    });

    function applyFilter(q) {
        q = (q || '').trim().toLowerCase();
        if (!q) {
            // Restauramos la vista inicial: todas cerradas excepto la recordada/hash.
            cards.forEach(function (c) { c.style.display = ''; close(c); });
            var t = location.hash && document.getElementById(location.hash.slice(1));
            if (!t) {
                try { t = document.getElementById(sessionStorage.getItem(SECTION_KEY) || ''); } catch (e) {}
            }
            if (t && t.classList.contains('card')) open(t);
            return;
        }
        cards.forEach(function (card, i) {
            if (cardText[i].indexOf(q) !== -1) {
                card.style.display = '';
                open(card);
            } else {
                card.style.display = 'none';
            }
        });
    }

    var t;
    input.addEventListener('input', function () {
        clearTimeout(t);
        var v = input.value;
        t = setTimeout(function () { applyFilter(v); }, 80);
    });
    if (clear) {
        clear.addEventListener('click', function () {
            input.value = '';
            applyFilter('');
            input.focus();
        });
    }
})();

// ── Editor rico (Quill) sobre textarea.js-richtext ───────────────────────
// Convertimos los textareas marcados con .js-richtext en editores Quill. El
// textarea original queda oculto y sigue siendo el campo que se envía: antes
// de cada submit copiamos el HTML del editor a su value. Así el backend PHP
// no necesita cambios.
window.addEventListener('load', function () {
    if (typeof Quill === 'undefined') return;
    var fields = document.querySelectorAll('textarea.js-richtext');
    if (!fields.length) return;

    Array.prototype.forEach.call(fields, function (textarea) {
        // Toolbar base. Si el campo permite encabezados (condiciones), añadimos H4.
        var allowHeaders = textarea.getAttribute('data-richtext-headers') === 'true';
        var toolbar = [];
        if (allowHeaders) toolbar.push([{ header: [false, 4] }]);
        toolbar.push(['bold', 'italic', 'underline']);
        toolbar.push([{ list: 'bullet' }, { list: 'ordered' }]);
        toolbar.push(['link']);
        toolbar.push(['clean']);

        // Crear contenedor para el editor justo después del textarea.
        var wrapper = document.createElement('div');
        wrapper.className = 'rt-wrapper';
        var editor = document.createElement('div');
        editor.className = 'rt-editor';
        wrapper.appendChild(editor);
        textarea.parentNode.insertBefore(wrapper, textarea.nextSibling);

        // Inicializar Quill con el HTML actual del textarea.
        var quill = new Quill(editor, {
            theme: 'snow',
            modules: { toolbar: toolbar },
            placeholder: textarea.getAttribute('placeholder') || ''
        });
        if (textarea.value) {
            // clipboard.dangerouslyPasteHTML respeta el HTML existente.
            quill.clipboard.dangerouslyPasteHTML(textarea.value);
        }

        // Ocultar el textarea original pero dejarlo en el DOM como campo del form.
        textarea.style.display = 'none';
        textarea.setAttribute('aria-hidden', 'true');
        textarea.setAttribute('tabindex', '-1');

        // Sincronizar al submit del form padre.
        var form = textarea.closest('form');
        if (form) {
            form.addEventListener('submit', function () {
                // Si el editor está vacío Quill devuelve "<p><br></p>"; lo limpiamos.
                var html = quill.root.innerHTML;
                if (html === '<p><br></p>') html = '';
                textarea.value = html;
            });
        }
    });
});
</script>
</body>
</html>
