<?php
require_once 'config.php';
require_once dirname(__DIR__) . '/config/content_helper.php';

// Active editing language (ES default, EN via ?edit_lang=en)
$edit_lang  = ($_GET['edit_lang'] ?? 'es') === 'en' ? 'en' : 'es';
$key_suffix = $edit_lang === 'en' ? '_en' : '';

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
    // Eventos — secciones
    'ev_ev_label', 'ev_ev_h2', 'ev_ev_desc',
    'ev_nw_label', 'ev_nw_h2', 'ev_nw_desc',
    'ev_tb_label', 'ev_tb_h2', 'ev_tb_desc',
    'ev_cat_label', 'ev_cat_h2', 'ev_cat_desc',
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
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>TUOI Admin — Contenido</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/admin.css">
</head>
<body>
<div class="admin-layout">
    <?php include 'partials/sidebar.php'; ?>

    <div class="main-content">
        <div class="topbar">
            <div>
                <div class="topbar-title">Editar contenido</div>
                <div class="topbar-sub">Textos visibles en el sitio web</div>
            </div>
            <div class="topbar-actions">
                <a href="../index.php" target="_blank" class="btn btn-secondary btn-sm">🌐 Ver sitio</a>
            </div>
        </div>

        <div class="content-area">

            <?php include 'partials/toast.php'; ?>

            <!-- Language tabs -->
            <div class="lang-tabs">
                <a href="?edit_lang=es" class="lang-tab <?= $edit_lang === 'es' ? 'active' : '' ?>">
                    <span class="flag">🇪🇸</span> Español
                </a>
                <a href="?edit_lang=en" class="lang-tab <?= $edit_lang === 'en' ? 'active' : '' ?>">
                    <span class="flag">🇬🇧</span> English
                    <?php if ($edit_lang === 'en'): ?>
                        <span style="font-size:11px;color:var(--muted);margin-left:6px;">
                            (vacío = usa el texto en español)
                        </span>
                    <?php endif; ?>
                </a>
            </div>

            <!-- ── HERO ──────────────────────────────── -->
            <div class="card">
                <div class="card-header">
                    <div class="card-title">
                        <span>🏠</span> Hero — Sección principal
                        <span class="section-badge">Portada</span>
                    </div>
                </div>
                <form method="post" action="?edit_lang=<?= $edit_lang ?>">
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
                <form method="post" action="?edit_lang=<?= $edit_lang ?>">
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
                <form method="post" action="?edit_lang=<?= $edit_lang ?>">
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

            <!-- ── EVENTOS ──────────────────────────────── -->
            <div class="card">
                <div class="card-header">
                    <div class="card-title">
                        <span>🎉</span> Eventos — Hero de página
                        <span class="section-badge">Eventos</span>
                    </div>
                </div>
                <form method="post" action="?edit_lang=<?= $edit_lang ?>">
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

            <!-- ── EVENTOS — 4 SECCIONES ─────────────────── -->
            <?php
            $ev_sections_admin = [
                ['prefix' => 'ev_ev',  'icon' => '🎉', 'name' => 'Sección Eventos'],
                ['prefix' => 'ev_nw',  'icon' => '🤝', 'name' => 'Sección Networking'],
                ['prefix' => 'ev_tb',  'icon' => '👥', 'name' => 'Sección Team Building'],
                ['prefix' => 'ev_cat', 'icon' => '🍽️', 'name' => 'Sección Catering'],
            ];
            foreach ($ev_sections_admin as $sec):
            ?>
            <div class="card">
                <div class="card-header">
                    <div class="card-title">
                        <span><?= $sec['icon'] ?></span> <?= $sec['name'] ?>
                        <span class="section-badge">Eventos</span>
                    </div>
                </div>
                <form method="post" action="?edit_lang=<?= $edit_lang ?>">
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
                    <button type="submit" class="btn btn-primary">💾 Guardar <?= $sec['name'] ?></button>
                </form>
            </div>
            <?php endforeach; ?>

            <!-- ── CONTACTO ──────────────────────────────── -->
            <div class="card">
                <div class="card-header">
                    <div class="card-title">
                        <span>📞</span> Información de contacto
                        <span class="section-badge">Eventos</span>
                    </div>
                </div>
                <form method="post" action="?edit_lang=<?= $edit_lang ?>">
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

            <!-- ── QUIÉNES SOMOS (PÁGINA) ────────────────── -->
            <div class="card">
                <div class="card-header">
                    <div class="card-title">
                        <span>👥</span> Quiénes somos — Página completa
                        <span class="section-badge">Quiénes somos</span>
                    </div>
                </div>
                <form method="post" action="?edit_lang=<?= $edit_lang ?>">

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

        </div>
    </div>
</div>
</body>
</html>
