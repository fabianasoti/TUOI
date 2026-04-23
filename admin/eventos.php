<?php
require_once 'config.php';

$base_img    = dirname(__DIR__) . '/assets/img/eventos/';
$max_size    = 10 * 1024 * 1024;
$allowed_ext = ['jpg', 'jpeg', 'png', 'webp'];

$categories = [
    'eventos'       => 'Eventos',
    'networking'    => 'Networking',
    'team-building' => 'Team Building',
    'catering'      => 'Catering',
];

$active_cat = $_GET['cat'] ?? 'eventos';
if (!array_key_exists($active_cat, $categories)) $active_cat = 'eventos';

$success = '';
$error   = '';

// ── Ensure tables exist ──────────────────────────────────
@mysqli_query($conexion,
    "CREATE TABLE IF NOT EXISTS eventos_posts (
        id             INT AUTO_INCREMENT PRIMARY KEY,
        category       VARCHAR(50)  NOT NULL DEFAULT 'catering',
        title          VARCHAR(255) NOT NULL DEFAULT '',
        body           TEXT,
        image_filename VARCHAR(255) DEFAULT NULL,
        sort_order     INT          DEFAULT 0,
        created_at     TIMESTAMP    DEFAULT CURRENT_TIMESTAMP
    )"
);
@mysqli_query($conexion,
    "CREATE TABLE IF NOT EXISTS contact_submissions (
        id           INT AUTO_INCREMENT PRIMARY KEY,
        name         VARCHAR(255) NOT NULL DEFAULT '',
        email        VARCHAR(255) NOT NULL DEFAULT '',
        phone        VARCHAR(100) DEFAULT '',
        message      TEXT,
        source_page  VARCHAR(100) DEFAULT '',
        submitted_at TIMESTAMP    DEFAULT CURRENT_TIMESTAMP
    )"
);

// ── Handle: add new post ─────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    $cat   = array_key_exists($_POST['category'] ?? '', $categories) ? $_POST['category'] : $active_cat;
    $title = trim($_POST['title'] ?? '');
    $body  = trim($_POST['body']  ?? '');
    $img   = '';

    if ($title === '') {
        $error = 'El título es obligatorio.';
    } else {
        // Handle image upload
        if (isset($_FILES['post_image']) && $_FILES['post_image']['error'] === UPLOAD_ERR_OK) {
            $ext      = strtolower(pathinfo($_FILES['post_image']['name'], PATHINFO_EXTENSION));
            $safename = preg_replace('/[^a-zA-Z0-9._-]/', '_', basename($_FILES['post_image']['name']));
            $dir      = $base_img . $cat . '/';

            if (!in_array($ext, $allowed_ext, true)) {
                $error = 'Formato de imagen no permitido.';
            } elseif ($_FILES['post_image']['size'] > $max_size) {
                $error = 'La imagen supera 10 MB.';
            } elseif (!is_dir($dir) && !mkdir($dir, 0775, true)) {
                $error = 'No se pudo crear el directorio de imágenes.';
            } else {
                $target = $dir . $safename;
                if (move_uploaded_file($_FILES['post_image']['tmp_name'], $target)) {
                    $img = $safename;
                } else {
                    $error = 'Error al subir la imagen.';
                }
            }
        }

        if ($error === '') {
            $c   = mysqli_real_escape_string($conexion, $cat);
            $t   = mysqli_real_escape_string($conexion, $title);
            $b   = mysqli_real_escape_string($conexion, $body);
            $im  = mysqli_real_escape_string($conexion, $img);
            mysqli_query($conexion,
                "INSERT INTO eventos_posts (category, title, body, image_filename, sort_order)
                 VALUES ('$c', '$t', '$b', " . ($im !== '' ? "'$im'" : 'NULL') . ", 0)"
            );
            $success = 'Entrada creada correctamente.';
            header("Location: eventos.php?cat=$active_cat&ok=1");
            exit;
        }
    }
}

// ── Handle: edit post ────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'edit') {
    $id    = (int) ($_POST['post_id'] ?? 0);
    $title = trim($_POST['title'] ?? '');
    $body  = trim($_POST['body']  ?? '');

    if ($id > 0 && $title !== '') {
        // Fetch current image
        $res = mysqli_query($conexion, "SELECT image_filename FROM eventos_posts WHERE id = $id");
        $row = $res ? mysqli_fetch_assoc($res) : null;
        $img = $row['image_filename'] ?? '';

        // New image uploaded?
        if (isset($_FILES['post_image']) && $_FILES['post_image']['error'] === UPLOAD_ERR_OK) {
            $ext      = strtolower(pathinfo($_FILES['post_image']['name'], PATHINFO_EXTENSION));
            $safename = preg_replace('/[^a-zA-Z0-9._-]/', '_', basename($_FILES['post_image']['name']));
            $dir      = $base_img . $active_cat . '/';

            if (in_array($ext, $allowed_ext, true) && $_FILES['post_image']['size'] <= $max_size) {
                if (!is_dir($dir)) mkdir($dir, 0775, true);
                if (move_uploaded_file($_FILES['post_image']['tmp_name'], $dir . $safename)) {
                    // Delete old image
                    if ($img && is_file($dir . $img)) @unlink($dir . $img);
                    $img = $safename;
                }
            }
        }

        $t  = mysqli_real_escape_string($conexion, $title);
        $b  = mysqli_real_escape_string($conexion, $body);
        $im = mysqli_real_escape_string($conexion, $img);
        mysqli_query($conexion,
            "UPDATE eventos_posts SET title='$t', body='$b', image_filename=" . ($im !== '' ? "'$im'" : 'NULL') . " WHERE id=$id"
        );
        $success = 'Entrada actualizada.';
        header("Location: eventos.php?cat=$active_cat&ok=1");
        exit;
    } else {
        $error = 'El título es obligatorio.';
    }
}

// ── Handle: delete post ──────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $id = (int) ($_POST['post_id'] ?? 0);
    if ($id > 0) {
        $res = mysqli_query($conexion, "SELECT image_filename, category FROM eventos_posts WHERE id = $id");
        $row = $res ? mysqli_fetch_assoc($res) : null;
        if ($row && $row['image_filename']) {
            $filepath = $base_img . $row['category'] . '/' . $row['image_filename'];
            if (is_file($filepath)) @unlink($filepath);
        }
        mysqli_query($conexion, "DELETE FROM eventos_posts WHERE id = $id");
        header("Location: eventos.php?cat=$active_cat&ok=1");
        exit;
    }
}

// ── Handle: save order ───────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_order'])) {
    $ids = json_decode($_POST['save_order'], true);
    if (is_array($ids)) {
        foreach ($ids as $order => $id) {
            $id    = (int) $id;
            $order = (int) $order;
            mysqli_query($conexion, "UPDATE eventos_posts SET sort_order=$order WHERE id=$id");
        }
        $success = 'Orden guardado.';
    }
}

if (isset($_GET['ok'])) $success = 'Cambios guardados correctamente.';

// ── Load posts for current category ─────────────────────
$posts = [];
$res = mysqli_query($conexion,
    "SELECT * FROM eventos_posts WHERE category='" . mysqli_real_escape_string($conexion, $active_cat) . "' ORDER BY sort_order ASC, id DESC"
);
if ($res) while ($row = mysqli_fetch_assoc($res)) $posts[] = $row;

// ── Load contact submissions (last 20) ───────────────────
$submissions = [];
$res_sub = @mysqli_query($conexion,
    "SELECT * FROM contact_submissions ORDER BY submitted_at DESC LIMIT 20"
);
if ($res_sub) while ($row = mysqli_fetch_assoc($res_sub)) $submissions[] = $row;

$edit_post = null;
if (isset($_GET['edit'])) {
    $eid = (int) $_GET['edit'];
    $res = mysqli_query($conexion, "SELECT * FROM eventos_posts WHERE id=$eid");
    if ($res) $edit_post = mysqli_fetch_assoc($res);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>TUOI Admin — Eventos</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/admin.css">
    <style>
        .post-list { display:flex; flex-direction:column; gap:12px; }
        .post-item { display:flex; align-items:flex-start; gap:14px; padding:14px 16px; background:var(--surface); border:1px solid var(--border); border-radius:10px; cursor:grab; }
        .post-item:active { cursor:grabbing; }
        .post-item.dragging { opacity:.4; }
        .post-item.drag-over { border-color:var(--primary); background:rgba(var(--primary-rgb),.06); }
        .post-thumb { width:80px; height:60px; object-fit:cover; border-radius:6px; flex-shrink:0; background:var(--border); }
        .post-thumb-placeholder { width:80px; height:60px; border-radius:6px; background:var(--border); flex-shrink:0; display:flex; align-items:center; justify-content:center; font-size:22px; }
        .post-meta { flex:1; min-width:0; }
        .post-meta h4 { font-size:14px; font-weight:600; margin-bottom:4px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
        .post-meta p { font-size:12px; color:var(--muted); white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
        .post-actions { display:flex; gap:6px; flex-shrink:0; }
        .ev-edit-form { background:var(--surface-2,#f9f9f9); border:1px solid var(--border); border-radius:12px; padding:20px; margin-top:16px; }
        .submissions-table { width:100%; border-collapse:collapse; font-size:13px; }
        .submissions-table th { text-align:left; padding:8px 12px; background:var(--surface-2,#f5f5f5); font-weight:600; color:var(--muted); border-bottom:1px solid var(--border); }
        .submissions-table td { padding:8px 12px; border-bottom:1px solid var(--border); vertical-align:top; }
        .submissions-table tr:last-child td { border-bottom:none; }
        .badge-cat { display:inline-block; padding:2px 8px; border-radius:20px; font-size:11px; font-weight:600; background:var(--primary-light,#ede7f6); color:var(--primary,#7c3aed); }
    </style>
</head>
<body>
<div class="admin-layout">
    <?php include 'partials/sidebar.php'; ?>

    <div class="main-content">
        <div class="topbar">
            <div>
                <div class="topbar-title">Gestión de Eventos</div>
                <div class="topbar-sub">Entradas de blog para Catering y Team Building</div>
            </div>
            <div class="topbar-actions">
                <a href="../pages/eventos/" target="_blank" class="btn btn-secondary btn-sm">🌐 Ver Eventos</a>
            </div>
        </div>

        <div class="content-area">

            <?php include 'partials/toast.php'; ?>

            <!-- Category tabs -->
            <div class="section-tabs">
                <?php foreach ($categories as $key => $label): ?>
                <a href="?cat=<?= urlencode($key) ?>"
                   class="section-tab <?= $key === $active_cat ? 'active' : '' ?>">
                    <?= htmlspecialchars($label) ?>
                </a>
                <?php endforeach; ?>
                <a href="?cat=<?= urlencode($active_cat) ?>&show_submissions=1"
                   class="section-tab <?= isset($_GET['show_submissions']) ? 'active' : '' ?>"
                   style="margin-left:auto;">
                    📬 Mensajes de contacto
                </a>
            </div>

            <?php if (isset($_GET['show_submissions'])): ?>

            <!-- Contact submissions -->
            <div class="card">
                <div class="card-header">
                    <div class="card-title">📬 Mensajes de contacto <span class="badge-count"><?= count($submissions) ?></span></div>
                </div>
                <?php if (empty($submissions)): ?>
                <div class="empty-state"><span class="empty-icon">📭</span> No hay mensajes todavía.</div>
                <?php else: ?>
                <div style="overflow-x:auto;">
                <table class="submissions-table">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Nombre</th>
                            <th>Email</th>
                            <th>Teléfono</th>
                            <th>Página</th>
                            <th>Mensaje</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($submissions as $sub): ?>
                    <tr>
                        <td style="white-space:nowrap;"><?= htmlspecialchars(date('d/m/Y H:i', strtotime($sub['submitted_at']))) ?></td>
                        <td><?= htmlspecialchars($sub['name']) ?></td>
                        <td><a href="mailto:<?= htmlspecialchars($sub['email']) ?>"><?= htmlspecialchars($sub['email']) ?></a></td>
                        <td><?= htmlspecialchars($sub['phone']) ?></td>
                        <td><span class="badge-cat"><?= htmlspecialchars($sub['source_page']) ?></span></td>
                        <td style="max-width:300px;"><?= nl2br(htmlspecialchars($sub['message'])) ?></td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                </div>
                <?php endif; ?>
            </div>

            <?php else: ?>

            <!-- Add / Edit post form -->
            <div class="card">
                <div class="card-header">
                    <div class="card-title">
                        <?= $edit_post ? '✏️ Editar entrada' : '➕ Nueva entrada' ?>
                        <span class="section-badge"><?= htmlspecialchars($categories[$active_cat]) ?></span>
                    </div>
                    <?php if ($edit_post): ?>
                    <a href="?cat=<?= urlencode($active_cat) ?>" class="btn btn-secondary btn-sm">✕ Cancelar edición</a>
                    <?php endif; ?>
                </div>
                <form method="post" enctype="multipart/form-data">
                    <input type="hidden" name="action"   value="<?= $edit_post ? 'edit' : 'add' ?>">
                    <input type="hidden" name="category" value="<?= htmlspecialchars($active_cat) ?>">
                    <?php if ($edit_post): ?>
                    <input type="hidden" name="post_id" value="<?= (int) $edit_post['id'] ?>">
                    <?php endif; ?>

                    <div class="form-group">
                        <label class="form-label">Título *</label>
                        <input name="title" type="text" class="form-control" required
                               value="<?= htmlspecialchars($edit_post['title'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Texto <span class="hint">descripción, detalles del evento, etc.</span></label>
                        <textarea name="body" class="form-control" rows="6"><?= htmlspecialchars($edit_post['body'] ?? '') ?></textarea>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Imagen
                            <?php if (!empty($edit_post['image_filename'])): ?>
                            <span class="hint">— imagen actual: <?= htmlspecialchars($edit_post['image_filename']) ?>. Sube una nueva para reemplazarla.</span>
                            <?php endif; ?>
                        </label>
                        <?php if (!empty($edit_post['image_filename'])): ?>
                        <div style="margin-bottom:10px;">
                            <img src="../assets/img/eventos/<?= htmlspecialchars($active_cat) ?>/<?= htmlspecialchars($edit_post['image_filename']) ?>"
                                 style="max-height:120px;border-radius:8px;" alt="">
                        </div>
                        <?php endif; ?>
                        <input name="post_image" type="file" class="form-control" accept=".jpg,.jpeg,.png,.webp">
                        <p style="font-size:12px;color:var(--muted);margin-top:4px;">JPG, PNG, WEBP · Máx. 10 MB</p>
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <?= $edit_post ? '💾 Guardar cambios' : '➕ Crear entrada' ?>
                    </button>
                </form>
            </div>

            <!-- Posts list -->
            <div class="card">
                <div class="card-header">
                    <div class="card-title">
                        📝 Entradas — <?= htmlspecialchars($categories[$active_cat]) ?>
                        <span class="badge-count"><?= count($posts) ?></span>
                    </div>
                    <?php if (count($posts) > 1): ?>
                    <div style="display:flex;align-items:center;gap:10px;">
                        <span style="font-size:12px;color:var(--muted);">Arrastra para reordenar</span>
                        <form method="post" id="order-form">
                            <input type="hidden" name="save_order" id="order-input" value="">
                            <button type="submit" id="save-order-btn" class="btn btn-verde btn-sm" disabled>
                                💾 Guardar orden
                            </button>
                        </form>
                    </div>
                    <?php endif; ?>
                </div>

                <?php if (empty($posts)): ?>
                <div class="empty-state">
                    <span class="empty-icon">📝</span>
                    No hay entradas todavía. Crea la primera arriba.
                </div>
                <?php else: ?>
                <div class="post-list" id="sortable-posts">
                    <?php foreach ($posts as $post): ?>
                    <div class="post-item" draggable="true" data-id="<?= (int) $post['id'] ?>">
                        <?php if (!empty($post['image_filename'])): ?>
                        <img class="post-thumb"
                             src="../assets/img/eventos/<?= htmlspecialchars($active_cat) ?>/<?= htmlspecialchars($post['image_filename']) ?>"
                             alt="" onerror="this.style.display='none'">
                        <?php else: ?>
                        <div class="post-thumb-placeholder">🖼</div>
                        <?php endif; ?>
                        <div class="post-meta">
                            <h4><?= htmlspecialchars($post['title']) ?></h4>
                            <p><?= htmlspecialchars(mb_substr($post['body'] ?? '', 0, 80)) ?><?= mb_strlen($post['body'] ?? '') > 80 ? '…' : '' ?></p>
                        </div>
                        <div class="post-actions">
                            <a href="?cat=<?= urlencode($active_cat) ?>&edit=<?= (int) $post['id'] ?>"
                               class="btn btn-secondary btn-sm">✏️ Editar</a>
                            <form method="post"
                                  onsubmit="return confirm('¿Eliminar esta entrada?')">
                                <input type="hidden" name="action"  value="delete">
                                <input type="hidden" name="post_id" value="<?= (int) $post['id'] ?>">
                                <button type="submit" class="btn btn-danger btn-sm">🗑</button>
                            </form>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>

            <?php endif; ?>

        </div>
    </div>
</div>

<script>
(function () {
    const list    = document.getElementById('sortable-posts');
    const saveBtn = document.getElementById('save-order-btn');
    const orderIn = document.getElementById('order-input');
    if (!list || !saveBtn) return;

    let dragSrc = null;

    function getOrder() {
        return [...list.querySelectorAll('.post-item')].map(el => el.dataset.id);
    }

    function markChanged() {
        orderIn.value    = JSON.stringify(getOrder());
        saveBtn.disabled = false;
    }

    list.querySelectorAll('.post-item').forEach(item => {
        item.addEventListener('dragstart', function (e) {
            dragSrc = this;
            e.dataTransfer.effectAllowed = 'move';
            setTimeout(() => this.classList.add('dragging'), 0);
        });
        item.addEventListener('dragend', function () {
            this.classList.remove('dragging');
            list.querySelectorAll('.post-item').forEach(i => i.classList.remove('drag-over'));
        });
        item.addEventListener('dragover', function (e) {
            e.preventDefault();
            if (this === dragSrc) return;
            list.querySelectorAll('.post-item').forEach(i => i.classList.remove('drag-over'));
            this.classList.add('drag-over');
        });
        item.addEventListener('dragleave', function () { this.classList.remove('drag-over'); });
        item.addEventListener('drop', function (e) {
            e.preventDefault();
            this.classList.remove('drag-over');
            if (dragSrc && dragSrc !== this) {
                const items  = [...list.children];
                const srcIdx = items.indexOf(dragSrc);
                const tgtIdx = items.indexOf(this);
                srcIdx < tgtIdx
                    ? list.insertBefore(dragSrc, this.nextSibling)
                    : list.insertBefore(dragSrc, this);
                markChanged();
            }
        });
    });
})();
</script>
</body>
</html>
