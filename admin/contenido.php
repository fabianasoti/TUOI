<?php
require_once 'config.php';
require_once dirname(__DIR__) . '/config/content_helper.php';

// Active editing language (ES default, EN via ?edit_lang=en)
$edit_lang  = ($_GET['edit_lang'] ?? 'es') === 'en' ? 'en' : 'es';
$key_suffix = $edit_lang === 'en' ? '_en' : '';

// Active section (hub when null)
$valid_sections = ['home', 'eventos', 'quienes'];
$section = $_GET['section'] ?? null;
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
    // Eventos — Manifiesto / Nuestra filosofía (intro narrativa)
    'ev_intro_label', 'ev_intro_p1', 'ev_intro_p2',
    // Eventos — Por qué TUOI
    'ev_why_label', 'ev_why_h2',
    'ev_why_b1_icon', 'ev_why_b1_title', 'ev_why_b1_desc',
    'ev_why_b2_icon', 'ev_why_b2_title', 'ev_why_b2_desc',
    'ev_why_b3_icon', 'ev_why_b3_title', 'ev_why_b3_desc',
    'ev_why_b4_icon', 'ev_why_b4_title', 'ev_why_b4_desc',
    // Eventos — Propuesta de menús (intro + 3 categorías)
    'ev_menus_label', 'ev_menus_h2', 'ev_menus_intro',
    'ev_cb_label', 'ev_cb_h2', 'ev_cb_desc',
    'ev_br_label', 'ev_br_h2', 'ev_br_desc',
    'ev_td_label', 'ev_td_h2', 'ev_td_desc',
    // Eventos — CTA y marquee
    'ev_cta_h2', 'ev_cta_text', 'ev_cta_btn',
    'ev_marquee_text',
    // Contacto
    'contact_phone', 'contact_email', 'contact_address',
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

// Topbar title
$section_titles = [
    'home'     => ['title' => 'Editar — Página de inicio',     'sub' => 'Hero, Quiénes somos y Filosofía'],
    'eventos'  => ['title' => 'Editar — Página de eventos',    'sub' => 'Hero, secciones y contacto'],
    'quienes'  => ['title' => 'Editar — Página Quiénes somos', 'sub' => 'Bloques completos de la página'],
];
$tb_title = $section ? $section_titles[$section]['title'] : 'Editar contenido';
$tb_sub   = $section ? $section_titles[$section]['sub']   : 'Elige la página que quieres editar';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>TUOI Admin — Contenido</title>
    <link rel="stylesheet" href="../assets/fonts/inter.css">
    <link rel="stylesheet" href="assets/css/admin.css">
</head>
<body>
<div class="admin-layout">
    <?php include 'partials/sidebar.php'; ?>

    <div class="main-content">
        <div class="topbar">
            <div>
                <div class="topbar-title"><?= htmlspecialchars($tb_title) ?></div>
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
                <!-- ── HUB: cards por página ────────────────── -->
                <div class="form-grid-2">
                    <a href="<?= section_url('home', $edit_lang) ?>" class="card" style="text-decoration:none;color:inherit;display:block;">
                        <div class="card-header">
                            <div class="card-title">
                                <span>🏠</span> Página de inicio
                            </div>
                        </div>
                        <p style="color:var(--muted);font-size:14px;margin:0;">
                            Hero principal, sección "Quiénes somos" (resumen) y "Nuestra filosofía" con tarjetas y valores.
                        </p>
                    </a>

                    <a href="<?= section_url('eventos', $edit_lang) ?>" class="card" style="text-decoration:none;color:inherit;display:block;">
                        <div class="card-header">
                            <div class="card-title">
                                <span>🎉</span> Página de eventos
                            </div>
                        </div>
                        <p style="color:var(--muted);font-size:14px;margin:0;">
                            Hero, carrusel, Por qué TUOI, menús (Coffee Break / Brunch / Tardeo), CTA, banner y contacto.
                        </p>
                    </a>

                    <a href="<?= section_url('quienes', $edit_lang) ?>" class="card" style="text-decoration:none;color:inherit;display:block;">
                        <div class="card-header">
                            <div class="card-title">
                                <span>👥</span> Página Quiénes somos
                            </div>
                        </div>
                        <p style="color:var(--muted);font-size:14px;margin:0;">
                            Hero de página, bloques 1, 2 y 3, lista de propuesta y cierre con CTA.
                        </p>
                    </a>
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
                        <label class="form-label">
                            Párrafo 2 <span class="hint">puedes usar HTML como &lt;strong&gt; o &lt;a href&gt;</span>
                        </label>
                        <textarea name="qs_p2<?= $key_suffix ?>" class="form-control" rows="4"><?= cv($content, 'qs_p2' . $key_suffix) ?></textarea>
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
                    <p style="font-size:13px;color:var(--muted);margin-bottom:16px;">Las 4 viñetas (icono + título + descripción)</p>

                    <?php for ($i = 1; $i <= 4; $i++): ?>
                    <div class="form-group" style="border-left:3px solid var(--border);padding-left:14px;margin-bottom:18px;">
                        <p style="font-size:12px;font-weight:600;color:var(--muted);margin-bottom:10px;">Viñeta <?= $i ?></p>
                        <div class="form-grid-2">
                            <div class="form-group">
                                <label class="form-label">Icono <span class="hint">emoji</span></label>
                                <input name="ev_why_b<?= $i ?>_icon<?= $key_suffix ?>" type="text" class="form-control"
                                       value="<?= cv($content, "ev_why_b{$i}_icon" . $key_suffix) ?>" maxlength="4">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Título</label>
                                <input name="ev_why_b<?= $i ?>_title<?= $key_suffix ?>" type="text" class="form-control"
                                       value="<?= cv($content, "ev_why_b{$i}_title" . $key_suffix) ?>">
                            </div>
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

            <!-- ── PROPUESTA DE MENÚS — INTRO ──────────── -->
            <div class="card">
                <div class="card-header">
                    <div class="card-title">
                        <span>🍽️</span> Propuesta de menús — Encabezado
                        <span class="section-badge">Eventos</span>
                    </div>
                </div>
                <form method="post" action="<?= htmlspecialchars($form_action) ?>">
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

            <!-- ── PROPUESTA DE MENÚS — 3 CATEGORÍAS ───── -->
            <?php
            $ev_menu_admin = [
                ['prefix' => 'ev_cb', 'icon' => '☕', 'name' => 'Coffee Break', 'cat' => 'coffee-break'],
                ['prefix' => 'ev_br', 'icon' => '🥐', 'name' => 'Brunch',       'cat' => 'brunch'],
                ['prefix' => 'ev_td', 'icon' => '🍷', 'name' => 'Tardeo',       'cat' => 'tardeo'],
            ];
            foreach ($ev_menu_admin as $sec):
            ?>
            <div class="card">
                <div class="card-header">
                    <div class="card-title">
                        <span><?= $sec['icon'] ?></span> Menú — <?= $sec['name'] ?>
                        <span class="section-badge">Eventos</span>
                    </div>
                </div>
                <form method="post" action="<?= htmlspecialchars($form_action) ?>">
                    <div class="form-grid-2">
                        <div class="form-group">
                            <label class="form-label">Etiqueta</label>
                            <input name="<?= $sec['prefix'] ?>_label<?= $key_suffix ?>" type="text" class="form-control"
                                   value="<?= cv($content, $sec['prefix'] . '_label' . $key_suffix) ?>">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Título H2</label>
                            <input name="<?= $sec['prefix'] ?>_h2<?= $key_suffix ?>" type="text" class="form-control"
                                   value="<?= cv($content, $sec['prefix'] . '_h2' . $key_suffix) ?>">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Descripción</label>
                        <textarea name="<?= $sec['prefix'] ?>_desc<?= $key_suffix ?>" class="form-control" rows="3"><?= cv($content, $sec['prefix'] . '_desc' . $key_suffix) ?></textarea>
                    </div>
                    <p style="font-size:13px;color:var(--muted);margin:6px 0 14px;">
                        Los sub-menús (entradas con título, texto e imágenes) se gestionan en
                        <a href="eventos.php?cat=<?= $sec['cat'] ?>" style="color:var(--primary);">📝 Eventos — <?= $sec['name'] ?></a>.
                    </p>
                    <button type="submit" class="btn btn-primary">💾 Guardar <?= $sec['name'] ?></button>
                </form>
            </div>
            <?php endforeach; ?>

            <!-- ── CTA ─────────────────────────────────── -->
            <div class="card">
                <div class="card-header">
                    <div class="card-title">
                        <span>📣</span> Llamada a la acción (CTA)
                        <span class="section-badge">Eventos</span>
                    </div>
                </div>
                <form method="post" action="<?= htmlspecialchars($form_action) ?>">
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
                        <label class="form-label">Párrafo 1 <span class="hint">puedes usar &lt;strong&gt;</span></label>
                        <textarea name="qs_page_b2_p1<?= $key_suffix ?>" class="form-control" rows="3"><?= cv($content, 'qs_page_b2_p1' . $key_suffix) ?></textarea>
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
                        <label class="form-label">Párrafo final <span class="hint">puedes usar &lt;strong&gt;</span></label>
                        <textarea name="qs_page_close_p<?= $key_suffix ?>" class="form-control" rows="3"><?= cv($content, 'qs_page_close_p' . $key_suffix) ?></textarea>
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
</body>
</html>
