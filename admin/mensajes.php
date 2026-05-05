<?php
require_once 'config.php';

$success = '';
$error   = '';

// ── Ensure table + is_read column exist (idempotente) ─────────────
try {
    mysqli_query($conexion,
        "CREATE TABLE IF NOT EXISTS contact_submissions (
            id           INT AUTO_INCREMENT PRIMARY KEY,
            name         VARCHAR(255) NOT NULL DEFAULT '',
            email        VARCHAR(255) NOT NULL DEFAULT '',
            phone        VARCHAR(100) DEFAULT '',
            message      TEXT,
            source_page  VARCHAR(100) DEFAULT '',
            consent_at   DATETIME     NULL DEFAULT NULL,
            consent_ip   VARCHAR(45)  NULL DEFAULT NULL,
            submitted_at TIMESTAMP    DEFAULT CURRENT_TIMESTAMP
        )"
    );
} catch (\Throwable $e) { /* ya existe */ }
try {
    mysqli_query($conexion, "ALTER TABLE contact_submissions ADD COLUMN is_read TINYINT(1) NOT NULL DEFAULT 0 AFTER message");
} catch (\Throwable $e) { /* columna ya existe */ }

// ── Toggle read state ──────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'toggle_read') {
    $id = (int) ($_POST['id'] ?? 0);
    if ($id > 0) {
        mysqli_query($conexion, "UPDATE contact_submissions SET is_read = 1 - is_read WHERE id=$id");
        header('Location: mensajes.php' . (isset($_GET['filter']) ? '?filter=' . urlencode($_GET['filter']) : ''));
        exit;
    }
}

// ── Mark all as read ───────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'mark_all_read') {
    mysqli_query($conexion, "UPDATE contact_submissions SET is_read = 1 WHERE is_read = 0");
    header('Location: mensajes.php?ok=1');
    exit;
}

// ── Delete ─────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
    $id = (int) ($_POST['id'] ?? 0);
    if ($id > 0) {
        mysqli_query($conexion, "DELETE FROM contact_submissions WHERE id=$id");
        header('Location: mensajes.php?ok=1');
        exit;
    }
}

if (isset($_GET['ok'])) $success = 'Cambios guardados correctamente.';

// ── Filter ─────────────────────────────────────────────────────────
$filter = $_GET['filter'] ?? 'all';
if (!in_array($filter, ['all', 'unread'], true)) $filter = 'all';

$where = $filter === 'unread' ? 'WHERE is_read = 0' : '';

// ── Counts ─────────────────────────────────────────────────────────
$total_count  = 0;
$unread_count = 0;
$res = mysqli_query($conexion, "SELECT COUNT(*) AS c FROM contact_submissions");
if ($res) $total_count = (int) mysqli_fetch_assoc($res)['c'];
$res = mysqli_query($conexion, "SELECT COUNT(*) AS c FROM contact_submissions WHERE is_read = 0");
if ($res) $unread_count = (int) mysqli_fetch_assoc($res)['c'];

// ── Load messages ──────────────────────────────────────────────────
$messages = [];
$res = mysqli_query($conexion,
    "SELECT * FROM contact_submissions $where ORDER BY submitted_at DESC, id DESC"
);
if ($res) while ($row = mysqli_fetch_assoc($res)) $messages[] = $row;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>TUOI Admin — Mensajes</title>
    <link rel="stylesheet" href="../assets/fonts/inter.css">
    <link rel="stylesheet" href="assets/css/admin.css">
    <style>
        .msg-toolbar { display:flex; gap:12px; align-items:center; margin-bottom:18px; flex-wrap:wrap; }
        .msg-tabs { display:inline-flex; background:var(--card-bg); border:1px solid var(--border); border-radius:10px; padding:3px; }
        .msg-tab { padding:6px 14px; border-radius:7px; font-size:13px; font-weight:500; color:var(--muted); text-decoration:none; transition:.15s; }
        .msg-tab.active { background:var(--accent); color:#fff; }
        .msg-tab .count { display:inline-block; margin-left:5px; font-size:11px; opacity:.85; }

        .msg-list { display:flex; flex-direction:column; gap:10px; }
        .msg-item { background:var(--card-bg); border:1px solid var(--border); border-radius:10px; padding:16px 18px; transition:.15s; }
        .msg-item.unread { border-left:4px solid var(--accent); background:rgba(197,99,210,.05); }
        .msg-head { display:flex; justify-content:space-between; align-items:flex-start; gap:14px; margin-bottom:8px; flex-wrap:wrap; }
        .msg-from { font-size:14px; }
        .msg-from strong { color:var(--text); }
        .msg-from a { color:var(--accent); text-decoration:none; }
        .msg-from a:hover { text-decoration:underline; }
        .msg-meta { font-size:12px; color:var(--muted); display:flex; gap:10px; flex-wrap:wrap; }
        .msg-pill { font-size:11px; padding:2px 8px; border-radius:20px; font-weight:600; background:#e0e7ff; color:#3730a3; }
        .msg-body { font-size:14px; line-height:1.55; color:var(--text); white-space:pre-wrap; word-wrap:break-word; padding:8px 0; border-top:1px solid var(--border); margin-top:6px; }
        .msg-actions { display:flex; gap:8px; margin-top:10px; padding-top:10px; border-top:1px solid var(--border); }
        .msg-rgpd { font-size:11px; color:var(--muted); margin-top:6px; }
        .msg-empty { text-align:center; padding:48px 24px; color:var(--muted); font-style:italic; background:var(--card-bg); border:1px dashed var(--border); border-radius:10px; }
        .btn-link { background:none; border:none; padding:5px 10px; cursor:pointer; color:var(--muted); font-size:12px; font-weight:500; border-radius:6px; }
        .btn-link:hover { background:var(--border); color:var(--text); }
        .btn-link.danger:hover { background:#fee2e2; color:#991b1b; }
    </style>
</head>
<body>
<div class="admin-layout">
    <?php include 'partials/sidebar.php'; ?>

    <div class="main-content">
        <div class="topbar">
            <div>
                <div class="topbar-title">Mensajes recibidos</div>
                <div class="topbar-sub">Formulario de contacto de la página de eventos.</div>
            </div>
        </div>

        <div class="content-area">
            <?php if ($success): ?>
                <div class="alert alert-success" style="margin-bottom:16px;"><?= htmlspecialchars($success) ?></div>
            <?php endif; ?>

            <div class="msg-toolbar">
                <div class="msg-tabs">
                    <a href="?filter=all" class="msg-tab <?= $filter === 'all' ? 'active' : '' ?>">
                        Todos <span class="count">(<?= $total_count ?>)</span>
                    </a>
                    <a href="?filter=unread" class="msg-tab <?= $filter === 'unread' ? 'active' : '' ?>">
                        Sin leer <span class="count">(<?= $unread_count ?>)</span>
                    </a>
                </div>

                <?php if ($unread_count > 0): ?>
                <form method="post" style="margin-left:auto;">
                    <input type="hidden" name="action" value="mark_all_read">
                    <button type="submit" class="btn btn-secondary btn-sm">✓ Marcar todos como leídos</button>
                </form>
                <?php endif; ?>
            </div>

            <?php if (empty($messages)): ?>
                <div class="msg-empty">
                    <?php if ($filter === 'unread'): ?>
                        ¡Estás al día! No hay mensajes sin leer.
                    <?php else: ?>
                        Aún no se han recibido mensajes desde el formulario de contacto.
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <div class="msg-list">
                    <?php foreach ($messages as $m):
                        $is_unread = (int) $m['is_read'] === 0;
                        $when = $m['submitted_at'] ? date('d/m/Y H:i', strtotime($m['submitted_at'])) : '—';
                    ?>
                    <div class="msg-item <?= $is_unread ? 'unread' : '' ?>">
                        <div class="msg-head">
                            <div class="msg-from">
                                <strong><?= htmlspecialchars($m['name'] ?: '(sin nombre)') ?></strong>
                                · <a href="mailto:<?= htmlspecialchars($m['email']) ?>"><?= htmlspecialchars($m['email']) ?></a>
                                <?php if (!empty($m['phone'])): ?>
                                · <a href="tel:<?= htmlspecialchars(preg_replace('/[\s\-()]/', '', $m['phone'])) ?>"><?= htmlspecialchars($m['phone']) ?></a>
                                <?php endif; ?>
                            </div>
                            <div class="msg-meta">
                                <?php if ($is_unread): ?>
                                <span class="msg-pill">Sin leer</span>
                                <?php endif; ?>
                                <?php if (!empty($m['source_page'])): ?>
                                <span>📍 <?= htmlspecialchars($m['source_page']) ?></span>
                                <?php endif; ?>
                                <span>🕒 <?= htmlspecialchars($when) ?></span>
                            </div>
                        </div>

                        <div class="msg-body"><?= htmlspecialchars($m['message'] ?? '') ?></div>

                        <?php if (!empty($m['consent_at'])): ?>
                        <div class="msg-rgpd">
                            ✓ Consentimiento RGPD aceptado el <?= htmlspecialchars(date('d/m/Y H:i', strtotime($m['consent_at']))) ?>
                            <?php if (!empty($m['consent_ip'])): ?>
                            (IP: <?= htmlspecialchars($m['consent_ip']) ?>)
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>

                        <div class="msg-actions">
                            <form method="post" style="display:inline;">
                                <input type="hidden" name="action" value="toggle_read">
                                <input type="hidden" name="id" value="<?= (int) $m['id'] ?>">
                                <button type="submit" class="btn-link">
                                    <?= $is_unread ? '✓ Marcar como leído' : '↺ Marcar como no leído' ?>
                                </button>
                            </form>
                            <a href="mailto:<?= htmlspecialchars($m['email']) ?>?subject=<?= rawurlencode('Re: tu consulta a TUOI') ?>"
                               class="btn-link">↩ Responder</a>
                            <form method="post" style="display:inline; margin-left:auto;"
                                  onsubmit="return confirm('¿Eliminar este mensaje? Esta acción no se puede deshacer.');">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?= (int) $m['id'] ?>">
                                <button type="submit" class="btn-link danger">🗑 Eliminar</button>
                            </form>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
</body>
</html>
