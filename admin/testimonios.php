<?php
require_once 'config.php';

$success = '';
$error   = '';

// ── Ensure table exists ──────────────────────────────────
@mysqli_query($conexion,
    "CREATE TABLE IF NOT EXISTS testimonios (
        id          INT AUTO_INCREMENT PRIMARY KEY,
        quote       TEXT         NOT NULL,
        author      VARCHAR(255) NOT NULL DEFAULT '',
        role        VARCHAR(255) DEFAULT '',
        sort_order  INT          DEFAULT 0,
        active      TINYINT(1)   NOT NULL DEFAULT 1,
        created_at  TIMESTAMP    DEFAULT CURRENT_TIMESTAMP
    )"
);

// ── Add ──────────────────────────────────────────────────
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
        header('Location: testimonios.php?ok=1');
        exit;
    }
}

// ── Edit ─────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'edit') {
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
        header('Location: testimonios.php?ok=1');
        exit;
    } else {
        $error = 'Cita y autor son obligatorios.';
    }
}

// ── Delete ───────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
    $id = (int) ($_POST['id'] ?? 0);
    if ($id > 0) {
        mysqli_query($conexion, "DELETE FROM testimonios WHERE id=$id");
        header('Location: testimonios.php?ok=1');
        exit;
    }
}

// ── Toggle active (quick action) ─────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'toggle') {
    $id = (int) ($_POST['id'] ?? 0);
    if ($id > 0) {
        mysqli_query($conexion, "UPDATE testimonios SET active = 1 - active WHERE id=$id");
        header('Location: testimonios.php?ok=1');
        exit;
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
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>TUOI Admin — Testimonios</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
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
        .t-edit-form { background:var(--surface-2,#f9f9f9); border:1px solid var(--border); border-radius:12px; padding:20px; margin-top:16px; }
        .t-empty { text-align:center; padding:32px; color:var(--muted); font-style:italic; background:var(--surface); border:1px dashed var(--border); border-radius:10px; }
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

            <!-- Add new -->
            <div class="card" style="margin-bottom:20px;">
                <div class="card-head">
                    <h3>Añadir testimonio</h3>
                </div>
                <div class="card-body">
                    <form method="post">
                        <input type="hidden" name="action" value="add">
                        <div class="form-group">
                            <label>Cita *</label>
                            <textarea name="quote" rows="3" class="form-control" required placeholder="“Lo mejor del evento fue…”"></textarea>
                        </div>
                        <div class="form-row" style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                            <div class="form-group">
                                <label>Autor *</label>
                                <input type="text" name="author" class="form-control" required placeholder="Nombre y apellido">
                            </div>
                            <div class="form-group">
                                <label>Cargo / empresa</label>
                                <input type="text" name="role" class="form-control" placeholder="Ej. People & Culture · Innovae">
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary">+ Añadir</button>
                    </form>
                </div>
            </div>

            <!-- Edit form -->
            <?php if ($edit_t): ?>
            <div class="t-edit-form">
                <h3 style="margin-bottom:12px;">Editar testimonio #<?= (int)$edit_t['id'] ?></h3>
                <form method="post">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="id" value="<?= (int)$edit_t['id'] ?>">
                    <div class="form-group">
                        <label>Cita *</label>
                        <textarea name="quote" rows="3" class="form-control" required><?= htmlspecialchars($edit_t['quote']) ?></textarea>
                    </div>
                    <div class="form-row" style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                        <div class="form-group">
                            <label>Autor *</label>
                            <input type="text" name="author" class="form-control" required value="<?= htmlspecialchars($edit_t['author']) ?>">
                        </div>
                        <div class="form-group">
                            <label>Cargo / empresa</label>
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
                        <button type="submit" class="btn btn-primary">Guardar</button>
                        <a href="testimonios.php" class="btn btn-secondary">Cancelar</a>
                    </div>
                </form>
            </div>
            <?php endif; ?>

            <!-- List -->
            <div class="card">
                <div class="card-head">
                    <h3>Testimonios (<?= count($testimonios) ?>)</h3>
                    <span class="topbar-sub" style="font-size:12px;">Arrastra para reordenar</span>
                </div>
                <div class="card-body">
                    <?php if (empty($testimonios)): ?>
                    <div class="t-empty">Aún no hay testimonios. Añade el primero arriba.</div>
                    <?php else: ?>
                    <div class="t-list" id="t-list">
                        <?php foreach ($testimonios as $t): ?>
                        <div class="t-item <?= $t['active'] ? '' : 'inactive' ?>" draggable="true" data-id="<?= (int)$t['id'] ?>">
                            <span class="t-grip" title="Arrastra para reordenar">⋮⋮</span>
                            <div class="t-body">
                                <div class="t-quote">"<?= htmlspecialchars(mb_strimwidth($t['quote'], 0, 220, '…')) ?>"</div>
                                <div class="t-meta">
                                    <strong><?= htmlspecialchars($t['author']) ?></strong>
                                    <?php if ($t['role']): ?> · <?= htmlspecialchars($t['role']) ?><?php endif; ?>
                                </div>
                            </div>
                            <div class="t-actions">
                                <span class="t-pill <?= $t['active'] ? 't-pill--on' : 't-pill--off' ?>">
                                    <?= $t['active'] ? 'Activo' : 'Inactivo' ?>
                                </span>
                                <form method="post" style="display:inline;">
                                    <input type="hidden" name="action" value="toggle">
                                    <input type="hidden" name="id" value="<?= (int)$t['id'] ?>">
                                    <button type="submit" class="btn btn-secondary btn-sm" title="Alternar visibilidad">
                                        <?= $t['active'] ? '🙈' : '👁️' ?>
                                    </button>
                                </form>
                                <a href="?edit=<?= (int)$t['id'] ?>" class="btn btn-secondary btn-sm">✏️</a>
                                <form method="post" style="display:inline;" onsubmit="return confirm('¿Eliminar este testimonio?');">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?= (int)$t['id'] ?>">
                                    <button type="submit" class="btn btn-secondary btn-sm" style="color:#c0392b;">🗑️</button>
                                </form>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
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
        fetch('testimonios.php', { method: 'POST', body: fd });
    }
})();
</script>
</body>
</html>
