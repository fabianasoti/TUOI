<?php
require_once 'config.php';

$success = '';
$error   = '';

// Active editing language (ES default, EN via ?lang=en)
$edit_lang = ($_GET['lang'] ?? 'es') === 'en' ? 'en' : 'es';

// ── Ensure table exists ──────────────────────────────────
@mysqli_query($conexion,
    "CREATE TABLE IF NOT EXISTS testimonios (
        id          INT AUTO_INCREMENT PRIMARY KEY,
        quote       TEXT         NOT NULL,
        quote_en    TEXT         NULL,
        author      VARCHAR(255) NOT NULL DEFAULT '',
        role        VARCHAR(255) DEFAULT '',
        role_en     VARCHAR(255) NULL,
        sort_order  INT          DEFAULT 0,
        active      TINYINT(1)   NOT NULL DEFAULT 1,
        created_at  TIMESTAMP    DEFAULT CURRENT_TIMESTAMP
    )"
);
// Migración idempotente para instalaciones previas (MYSQLI_REPORT_STRICT lanza excepciones, así que envolvemos).
try { mysqli_query($conexion, "ALTER TABLE testimonios ADD COLUMN quote_en TEXT NULL AFTER quote"); } catch (\Throwable $e) {}
try { mysqli_query($conexion, "ALTER TABLE testimonios ADD COLUMN role_en  VARCHAR(255) NULL AFTER role"); } catch (\Throwable $e) {}

// Helper: redirect preservando lang y edit si procede
function redirect_back($lang, $edit_id = null) {
    $qs = [];
    if ($lang === 'en') $qs['lang'] = 'en';
    if ($edit_id)       $qs['edit'] = (int) $edit_id;
    $qs['ok'] = 1;
    header('Location: testimonios.php?' . http_build_query($qs));
    exit;
}

// ── Add (solo desde pestaña ES) ──────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'add') {
    $quote  = trim($_POST['quote']  ?? '');
    $author = trim($_POST['author'] ?? '');
    $role   = trim($_POST['role']   ?? '');
    if ($quote === '' || $author === '') {
        $error = 'Cita y autor son obligatorios.';
    } else {
        $q = mysqli_real_escape_string($conexion, $quote);
        $a = mysqli_real_escape_string($conexion, $author);
        $r = mysqli_real_escape_string($conexion, $role);
        mysqli_query($conexion,
            "INSERT INTO testimonios (quote, author, role, sort_order, active)
             VALUES ('$q', '$a', '$r', 0, 1)"
        );
        redirect_back('es');
    }
}

// ── Edit ES (cita / autor / cargo / activo) ──────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'edit_es') {
    $id     = (int) ($_POST['id'] ?? 0);
    $quote  = trim($_POST['quote']  ?? '');
    $author = trim($_POST['author'] ?? '');
    $role   = trim($_POST['role']   ?? '');
    $active = isset($_POST['active']) ? 1 : 0;
    if ($id > 0 && $quote !== '' && $author !== '') {
        $q = mysqli_real_escape_string($conexion, $quote);
        $a = mysqli_real_escape_string($conexion, $author);
        $r = mysqli_real_escape_string($conexion, $role);
        mysqli_query($conexion,
            "UPDATE testimonios SET quote='$q', author='$a', role='$r', active=$active WHERE id=$id"
        );
        redirect_back('es');
    } else {
        $error = 'Cita y autor son obligatorios.';
    }
}

// ── Edit EN (solo traducción) ────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'edit_en') {
    $id       = (int) ($_POST['id'] ?? 0);
    $quote_en = trim($_POST['quote_en'] ?? '');
    $role_en  = trim($_POST['role_en']  ?? '');
    if ($id > 0) {
        $qe = mysqli_real_escape_string($conexion, $quote_en);
        $re = mysqli_real_escape_string($conexion, $role_en);
        mysqli_query($conexion,
            "UPDATE testimonios SET quote_en='$qe', role_en='$re' WHERE id=$id"
        );
        redirect_back('en');
    }
}

// ── Delete ───────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
    $id = (int) ($_POST['id'] ?? 0);
    if ($id > 0) {
        mysqli_query($conexion, "DELETE FROM testimonios WHERE id=$id");
        redirect_back($edit_lang);
    }
}

// ── Toggle active ────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'toggle') {
    $id = (int) ($_POST['id'] ?? 0);
    if ($id > 0) {
        mysqli_query($conexion, "UPDATE testimonios SET active = 1 - active WHERE id=$id");
        redirect_back($edit_lang);
    }
}

// ── Save order (drag) ────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_order'])) {
    $ids = json_decode($_POST['save_order'], true);
    if (is_array($ids)) {
        foreach ($ids as $order => $id) {
            $id    = (int) $id;
            $order = (int) $order;
            mysqli_query($conexion, "UPDATE testimonios SET sort_order=$order WHERE id=$id");
        }
        $success = 'Orden guardado.';
    }
}

if (isset($_GET['ok'])) $success = 'Cambios guardados correctamente.';

// ── Load list ────────────────────────────────────────────
$testimonios = [];
$res = mysqli_query($conexion, "SELECT * FROM testimonios ORDER BY sort_order ASC, id DESC");
if ($res) while ($row = mysqli_fetch_assoc($res)) $testimonios[] = $row;

$edit_t = null;
if (isset($_GET['edit'])) {
    $eid = (int) $_GET['edit'];
    $res = mysqli_query($conexion, "SELECT * FROM testimonios WHERE id=$eid");
    if ($res) $edit_t = mysqli_fetch_assoc($res);
}

// URL helper que preserva lang
function tab_url($lang, $extra = []) {
    $qs = [];
    if ($lang === 'en') $qs['lang'] = 'en';
    return 'testimonios.php' . ((!empty($qs) || !empty($extra)) ? '?' . http_build_query(array_merge($qs, $extra)) : '');
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>TUOI Admin — Testimonios</title>
    <link rel="stylesheet" href="../assets/fonts/inter.css">
    <link rel="stylesheet" href="assets/css/admin.css">
    <style>
        .t-list { display:flex; flex-direction:column; gap:12px; }
        .t-item { position:relative; display:flex; align-items:flex-start; gap:14px; padding:16px 18px; background:var(--surface); border:1px solid var(--border); border-radius:10px; cursor:grab; }
        .t-item:active { cursor:grabbing; }
        .t-item.dragging { opacity:.4; }
        .t-item.drag-over { border-color:var(--primary); background:rgba(124,58,237,.06); }
        .t-item.inactive { opacity:.55; }
        .t-grip { color:var(--muted); font-size:18px; line-height:1; padding-top:2px; }
        .t-body { flex:1; min-width:0; }
        .t-quote { font-size:14px; line-height:1.5; color:var(--text); margin-bottom:6px; font-style:italic; }
        .t-meta { font-size:12px; color:var(--muted); }
        .t-meta strong { color:var(--text); font-weight:600; }
        .t-actions { display:flex; gap:6px; flex-shrink:0; align-items:center; }
        .t-pill { font-size:11px; padding:2px 8px; border-radius:20px; font-weight:600; }
        .t-pill--on  { background:#dcfce7; color:#166534; }
        .t-pill--off { background:#fee2e2; color:#991b1b; }
        .t-pill--ok  { background:#dbeafe; color:#1e40af; }
        .t-pill--no  { background:#f3f4f6; color:#6b7280; }
        .t-edit-form { background:var(--surface-2,#f9f9f9); border:1px solid var(--border); border-radius:12px; padding:20px; margin-top:16px; }
        .t-empty { text-align:center; padding:32px; color:var(--muted); font-style:italic; background:var(--surface); border:1px dashed var(--border); border-radius:10px; }
        .t-ref { background:#f9fafb; border:1px solid var(--border); border-left:3px solid var(--muted); border-radius:8px; padding:12px 14px; margin-bottom:14px; }
        .t-ref-label { font-size:11px; text-transform:uppercase; letter-spacing:.04em; color:var(--muted); font-weight:600; margin-bottom:6px; }
        .t-ref-text { font-size:14px; color:var(--text); font-style:italic; line-height:1.5; }
        .t-ref-author { font-size:12px; color:var(--muted); margin-top:6px; }
    </style>
</head>
<body>
<div class="admin-layout">
    <?php include 'partials/sidebar.php'; ?>

    <div class="main-content">
        <div class="topbar">
            <div>
                <div class="topbar-title">Testimonios</div>
                <div class="topbar-sub">Aparecen en la sección "Confían en nosotros" de Eventos. Si hay más de uno se rotan automáticamente.</div>
            </div>
            <div class="topbar-actions">
                <a href="../pages/eventos/#confian" target="_blank" class="btn btn-secondary btn-sm">🌐 Ver en la web</a>
            </div>
        </div>

        <div class="content-area">
            <?php include 'partials/toast.php'; ?>

            <!-- Language tabs -->
            <div class="lang-tabs">
                <a href="<?= tab_url('es') ?>" class="lang-tab <?= $edit_lang === 'es' ? 'active' : '' ?>">
                    <span class="flag">🇪🇸</span> Español
                </a>
                <a href="<?= tab_url('en') ?>" class="lang-tab <?= $edit_lang === 'en' ? 'active' : '' ?>">
                    <span class="flag">🇬🇧</span> English
                    <?php if ($edit_lang === 'en'): ?>
                        <span style="font-size:11px;color:var(--muted);margin-left:6px;">
                            (solo traducciones · vacío = se usa el español)
                        </span>
                    <?php endif; ?>
                </a>
            </div>

            <?php if ($edit_lang === 'es'): ?>
            <!-- ─────────── PESTAÑA ESPAÑOL ─────────── -->

            <!-- Add new -->
            <div class="card" style="margin-bottom:20px;">
                <div class="card-header">
                    <div class="card-title"><span>➕</span> Añadir testimonio</div>
                </div>
                <form method="post">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="add">
                    <div class="form-group">
                        <label class="form-label">Cita *</label>
                        <textarea name="quote" rows="3" class="form-control" required placeholder="“Lo mejor del evento fue…”"></textarea>
                    </div>
                    <div class="form-grid-2">
                        <div class="form-group">
                            <label class="form-label">Autor *</label>
                            <input type="text" name="author" class="form-control" required placeholder="Nombre y apellido">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Cargo / empresa</label>
                            <input type="text" name="role" class="form-control" placeholder="Ej. People & Culture · Innovae">
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">+ Añadir</button>
                </form>
            </div>

            <!-- Edit form ES -->
            <?php if ($edit_t): ?>
            <div class="card" style="margin-bottom:20px;">
                <div class="card-header">
                    <div class="card-title"><span>✏️</span> Editar testimonio #<?= (int)$edit_t['id'] ?></div>
                </div>
                <form method="post">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="edit_es">
                    <input type="hidden" name="id" value="<?= (int)$edit_t['id'] ?>">
                    <div class="form-group">
                        <label class="form-label">Cita *</label>
                        <textarea name="quote" rows="3" class="form-control" required><?= htmlspecialchars($edit_t['quote']) ?></textarea>
                    </div>
                    <div class="form-grid-2">
                        <div class="form-group">
                            <label class="form-label">Autor *</label>
                            <input type="text" name="author" class="form-control" required value="<?= htmlspecialchars($edit_t['author']) ?>">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Cargo / empresa</label>
                            <input type="text" name="role" class="form-control" value="<?= htmlspecialchars($edit_t['role']) ?>">
                        </div>
                    </div>
                    <div class="form-group">
                        <label style="display:flex;align-items:center;gap:8px;font-weight:500;">
                            <input type="checkbox" name="active" value="1" <?= $edit_t['active'] ? 'checked' : '' ?>>
                            Activo (visible en la web)
                        </label>
                    </div>
                    <div style="display:flex;gap:8px;">
                        <button type="submit" class="btn btn-primary">💾 Guardar</button>
                        <a href="<?= tab_url('es') ?>" class="btn btn-secondary">Cancelar</a>
                    </div>
                </form>
            </div>
            <?php endif; ?>

            <?php else: ?>
            <!-- ─────────── PESTAÑA ENGLISH ─────────── -->

            <?php if ($edit_t): ?>
            <div class="card" style="margin-bottom:20px;">
                <div class="card-header">
                    <div class="card-title"><span>🇬🇧</span> Traducir testimonio #<?= (int)$edit_t['id'] ?></div>
                </div>

                <!-- Referencia: original en español -->
                <div class="t-ref">
                    <div class="t-ref-label">Original en español (referencia)</div>
                    <div class="t-ref-text">"<?= htmlspecialchars($edit_t['quote']) ?>"</div>
                    <div class="t-ref-author">
                        <strong><?= htmlspecialchars($edit_t['author']) ?></strong>
                        <?php if ($edit_t['role']): ?> · <?= htmlspecialchars($edit_t['role']) ?><?php endif; ?>
                    </div>
                </div>

                <form method="post">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="edit_en">
                    <input type="hidden" name="id" value="<?= (int)$edit_t['id'] ?>">
                    <div class="form-group">
                        <label class="form-label">Quote (English)</label>
                        <textarea name="quote_en" rows="3" class="form-control" placeholder="“The best part of the event was…”"><?= htmlspecialchars($edit_t['quote_en'] ?? '') ?></textarea>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Role / company (English)</label>
                        <input type="text" name="role_en" class="form-control" value="<?= htmlspecialchars($edit_t['role_en'] ?? '') ?>" placeholder="e.g. People & Culture · Innovae">
                        <small style="color:var(--muted);font-size:12px;">El nombre del autor no se traduce.</small>
                    </div>
                    <div style="display:flex;gap:8px;">
                        <button type="submit" class="btn btn-primary">💾 Guardar traducción</button>
                        <a href="<?= tab_url('en') ?>" class="btn btn-secondary">Cancelar</a>
                    </div>
                </form>
            </div>
            <?php else: ?>
            <div class="card" style="margin-bottom:20px;background:#f9fafb;">
                <p style="margin:0;color:var(--muted);font-size:14px;">
                    💡 Selecciona un testimonio de la lista para añadir o editar su traducción al inglés.
                    Para crear un testimonio nuevo, cambia a la pestaña <a href="<?= tab_url('es') ?>" style="color:var(--primary);font-weight:600;">🇪🇸 Español</a>.
                </p>
            </div>
            <?php endif; ?>

            <?php endif; ?>

            <!-- ── List (común a ambas pestañas) ── -->
            <div class="card">
                <div class="card-header">
                    <div class="card-title">
                        <span>💬</span> Testimonios (<?= count($testimonios) ?>)
                    </div>
                    <span class="topbar-sub" style="font-size:12px;">Arrastra para reordenar</span>
                </div>
                <?php if (empty($testimonios)): ?>
                <div class="t-empty">Aún no hay testimonios. Añade el primero desde la pestaña Español.</div>
                <?php else: ?>
                <div class="t-list" id="t-list">
                    <?php foreach ($testimonios as $t):
                        $has_en   = !empty($t['quote_en']);
                        // Mostrar la cita en el idioma activo (con fallback a ES si no hay EN).
                        $display_quote = ($edit_lang === 'en' && $has_en) ? $t['quote_en'] : $t['quote'];
                        $display_role  = ($edit_lang === 'en' && !empty($t['role_en'])) ? $t['role_en'] : $t['role'];
                    ?>
                    <div class="t-item <?= $t['active'] ? '' : 'inactive' ?>" draggable="true" data-id="<?= (int)$t['id'] ?>">
                        <span class="t-grip" title="Arrastra para reordenar">⋮⋮</span>
                        <div class="t-body">
                            <div class="t-quote">"<?= htmlspecialchars(mb_strimwidth($display_quote, 0, 220, '…')) ?>"</div>
                            <div class="t-meta">
                                <strong><?= htmlspecialchars($t['author']) ?></strong>
                                <?php if ($display_role): ?> · <?= htmlspecialchars($display_role) ?><?php endif; ?>
                            </div>
                        </div>
                        <div class="t-actions">
                            <?php if ($edit_lang === 'en'): ?>
                                <span class="t-pill <?= $has_en ? 't-pill--ok' : 't-pill--no' ?>" title="<?= $has_en ? 'Traducción guardada' : 'Sin traducir' ?>">
                                    <?= $has_en ? 'Traducido ✓' : 'Sin traducir' ?>
                                </span>
                                <a href="<?= tab_url('en', ['edit' => (int)$t['id']]) ?>" class="btn btn-secondary btn-sm" title="<?= $has_en ? 'Editar traducción' : 'Añadir traducción' ?>">
                                    🇬🇧 <?= $has_en ? 'Editar' : 'Traducir' ?>
                                </a>
                            <?php else: ?>
                                <span class="t-pill <?= $has_en ? 't-pill--ok' : 't-pill--no' ?>" title="<?= $has_en ? 'Tiene traducción al inglés' : 'Sin traducción al inglés' ?>">
                                    EN <?= $has_en ? '✓' : '—' ?>
                                </span>
                                <span class="t-pill <?= $t['active'] ? 't-pill--on' : 't-pill--off' ?>">
                                    <?= $t['active'] ? 'Activo' : 'Inactivo' ?>
                                </span>
                                <form method="post" style="display:inline;">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="action" value="toggle">
                                    <input type="hidden" name="id" value="<?= (int)$t['id'] ?>">
                                    <button type="submit" class="btn btn-secondary btn-sm" title="Alternar visibilidad">
                                        <?= $t['active'] ? '🙈' : '👁️' ?>
                                    </button>
                                </form>
                                <a href="<?= tab_url('es', ['edit' => (int)$t['id']]) ?>" class="btn btn-secondary btn-sm" title="Editar">✏️</a>
                                <form method="post" style="display:inline;" onsubmit="return confirm('¿Eliminar este testimonio?');">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?= (int)$t['id'] ?>">
                                    <button type="submit" class="btn btn-secondary btn-sm" style="color:#c0392b;" title="Eliminar">🗑️</button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>

        </div>
    </div>
</div>

<script>
// Drag-and-drop reorder
(function () {
    const list = document.getElementById('t-list');
    if (!list) return;
    let dragEl = null;

    list.addEventListener('dragstart', e => {
        const it = e.target.closest('.t-item');
        if (!it) return;
        dragEl = it;
        it.classList.add('dragging');
    });
    list.addEventListener('dragend', e => {
        document.querySelectorAll('.t-item').forEach(el => el.classList.remove('dragging', 'drag-over'));
        saveOrder();
    });
    list.addEventListener('dragover', e => {
        e.preventDefault();
        const tgt = e.target.closest('.t-item');
        if (!tgt || tgt === dragEl) return;
        const rect = tgt.getBoundingClientRect();
        const after = (e.clientY - rect.top) > rect.height / 2;
        list.insertBefore(dragEl, after ? tgt.nextSibling : tgt);
    });

    function saveOrder() {
        const ids = [...list.querySelectorAll('.t-item')].map(el => el.dataset.id);
        const fd = new FormData();
        fd.append('save_order', JSON.stringify(ids));
        fd.append('csrf_token', <?= json_encode(csrf_token()) ?>);
        fetch('testimonios.php', { method: 'POST', body: fd });
    }
})();
</script>
</body>
</html>
