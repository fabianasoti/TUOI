<?php
require_once 'config.php';

$base_img    = dirname(__DIR__) . '/assets/img/eventos/';
$max_size    = 20 * 1024 * 1024;
$allowed_ext = ['jpg', 'jpeg', 'png', 'webp', 'gif'];

$categories = [
    'coffee-break' => 'Coffee Break',
    'brunch'       => 'Brunch',
    'tardeo'       => 'Tardeo',
];

$active_cat = $_GET['cat'] ?? 'coffee-break';
if (!array_key_exists($active_cat, $categories)) $active_cat = 'coffee-break';

$success = '';
$error   = '';

// ── Helper: parse image_filename → array ─────────────────
function parse_images(string $raw): array {
    if ($raw === '') return [];
    $decoded = json_decode($raw, true);
    if (is_array($decoded)) return array_values($decoded);
    return array_values(array_filter(array_map('trim', explode(',', $raw))));
}

require_once __DIR__ . '/partials/image_utils.php';

// ── Helper: upload multiple files, return list or set $error ─
function upload_files(array $files_arr, string $dir, array $allowed, int $max, string &$error): array {
    $saved = [];
    if (!is_dir($dir) && !mkdir($dir, 0775, true)) {
        $error = 'No se pudo crear el directorio: ' . $dir;
        return $saved;
    }
    foreach ($files_arr['tmp_name'] as $i => $tmp) {
        $file_err = $files_arr['error'][$i];
        if ($file_err === UPLOAD_ERR_NO_FILE) continue;
        $php_errors = [
            UPLOAD_ERR_INI_SIZE   => 'Imagen supera upload_max_filesize en php.ini.',
            UPLOAD_ERR_FORM_SIZE  => 'Imagen supera el límite del formulario.',
            UPLOAD_ERR_PARTIAL    => 'Imagen subida parcialmente.',
            UPLOAD_ERR_NO_TMP_DIR => 'Sin directorio temporal PHP (UPLOAD_ERR_NO_TMP_DIR).',
            UPLOAD_ERR_CANT_WRITE => 'PHP no puede escribir en disco.',
            UPLOAD_ERR_EXTENSION  => 'Una extensión PHP bloqueó la subida.',
        ];
        if ($file_err !== UPLOAD_ERR_OK) {
            $error = $php_errors[$file_err] ?? "Error PHP código $file_err.";
            return $saved;
        }
        $ext = strtolower(pathinfo($files_arr['name'][$i], PATHINFO_EXTENSION));
        if (!in_array($ext, $allowed, true)) { $error = 'Formato no permitido (JPG/PNG/WEBP).'; return $saved; }
        if ($files_arr['size'][$i] > $max)   { $error = 'Una imagen supera 20 MB.';             return $saved; }

        $safename = uniqid('img_', true) . '.webp';
        $dest     = $dir . $safename;

        // Move to temp location, then convert to WebP
        $tmp_dest = $dir . uniqid('tmp_', true);
        if (!move_uploaded_file($tmp, $tmp_dest)) {
            $last  = error_get_last();
            $error = 'Error al guardar imagen en ' . $dir . ($last ? ' — ' . $last['message'] : '');
            return $saved;
        }

        if (convert_to_webp($tmp_dest, $dest)) {
            @unlink($tmp_dest);
            $saved[] = $safename;
        } else {
            // Conversion failed — keep original as fallback
            $fallback = $dir . uniqid('img_', true) . '.' . $ext;
            rename($tmp_dest, $fallback);
            $saved[] = basename($fallback);
        }
    }
    return $saved;
}

// ── Ensure tables exist ──────────────────────────────────
@mysqli_query($conexion,
    "CREATE TABLE IF NOT EXISTS eventos_posts (
        id             INT AUTO_INCREMENT PRIMARY KEY,
        category       VARCHAR(50)  NOT NULL DEFAULT 'catering',
        title          VARCHAR(255) NOT NULL DEFAULT '',
        body           TEXT,
        image_filename TEXT         DEFAULT NULL,
        sort_order     INT          DEFAULT 0,
        created_at     TIMESTAMP    DEFAULT CURRENT_TIMESTAMP
    )"
);
// Upgrade VARCHAR(255) → TEXT for existing installs
@mysqli_query($conexion, "ALTER TABLE eventos_posts MODIFY COLUMN image_filename TEXT DEFAULT NULL");
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

    if ($title === '') {
        $error = 'El título es obligatorio.';
    } else {
        $dir    = $base_img . $cat . '/';
        $images = isset($_FILES['post_images']) ? upload_files($_FILES['post_images'], $dir, $allowed_ext, $max_size, $error) : [];

        if ($error === '') {
            $img_val = empty($images) ? 'NULL' : ("'" . mysqli_real_escape_string($conexion, json_encode($images)) . "'");
            $c = mysqli_real_escape_string($conexion, $cat);
            $t = mysqli_real_escape_string($conexion, $title);
            $b = mysqli_real_escape_string($conexion, $body);
            mysqli_query($conexion,
                "INSERT INTO eventos_posts (category, title, body, image_filename, sort_order)
                 VALUES ('$c', '$t', '$b', $img_val, 0)"
            );
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
        $res = mysqli_query($conexion, "SELECT image_filename, category FROM eventos_posts WHERE id = $id");
        $row = $res ? mysqli_fetch_assoc($res) : [];
        $dir = $base_img . ($row['category'] ?? $active_cat) . '/';

        // 1. Images kept in new order (from drag-reorder hidden input)
        $existing_order = json_decode($_POST['existing_order'] ?? '[]', true);
        if (!is_array($existing_order)) $existing_order = [];

        // 2. Delete images removed by user
        $deleted = json_decode($_POST['deleted_images'] ?? '[]', true);
        if (is_array($deleted)) {
            foreach ($deleted as $del) {
                $fp = $dir . basename((string)$del);
                if (is_file($fp)) @unlink($fp);
            }
        }

        // 3. Upload new images
        $new_imgs = isset($_FILES['new_images']) ? upload_files($_FILES['new_images'], $dir, $allowed_ext, $max_size, $error) : [];

        if ($error === '') {
            $final = array_merge($existing_order, $new_imgs);
            $img_val = empty($final) ? 'NULL' : ("'" . mysqli_real_escape_string($conexion, json_encode($final)) . "'");
            $t = mysqli_real_escape_string($conexion, $title);
            $b = mysqli_real_escape_string($conexion, $body);
            mysqli_query($conexion, "UPDATE eventos_posts SET title='$t', body='$b', image_filename=$img_val WHERE id=$id");
            header("Location: eventos.php?cat=$active_cat&ok=1");
            exit;
        }
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
            $dir = $base_img . $row['category'] . '/';
            foreach (parse_images($row['image_filename']) as $img) {
                $fp = $dir . $img;
                if (is_file($fp)) @unlink($fp);
            }
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
        .post-item { position:relative; display:flex; align-items:flex-start; gap:14px; padding:14px 16px; background:var(--surface); border:1px solid var(--border); border-radius:10px; cursor:grab; }
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

        /* ── Image manager ─────────────────────────────────── */
        .img-manager { display:flex; flex-direction:column; gap:12px; }
        .img-grid { display:flex; flex-wrap:wrap; gap:10px; min-height:40px; }
        .img-card {
            position:relative; width:110px; border-radius:8px; overflow:hidden;
            border:2px solid var(--border); background:var(--surface);
            cursor:grab; user-select:none; transition:border-color .15s, opacity .15s;
        }
        .img-card:active { cursor:grabbing; }
        .img-card.dragging { opacity:.35; border-color:var(--primary,#7c3aed); }
        .img-card.drag-over { border-color:var(--primary,#7c3aed); background:rgba(124,58,237,.07); }
        .img-card img { width:110px; height:80px; object-fit:cover; display:block; pointer-events:none; }
        .img-card__del {
            position:absolute; top:4px; right:4px;
            width:22px; height:22px; border-radius:50%;
            background:rgba(0,0,0,.65); color:#fff; border:none;
            font-size:14px; line-height:22px; text-align:center;
            cursor:pointer; display:flex; align-items:center; justify-content:center;
            transition:background .15s;
        }
        .img-card__del:hover { background:#c0392b; }
        .img-card__order {
            position:absolute; bottom:0; left:0; right:0;
            background:rgba(0,0,0,.45); color:#fff;
            font-size:10px; text-align:center; padding:2px 0;
            pointer-events:none;
        }
        .img-add-zone {
            display:flex; align-items:center; gap:8px; flex-wrap:wrap;
        }
        .img-add-label {
            display:inline-flex; align-items:center; gap:6px;
            padding:7px 14px; border-radius:8px; border:1.5px dashed var(--border);
            color:var(--muted); font-size:13px; cursor:pointer;
            transition:border-color .15s, color .15s;
        }
        .img-add-label:hover { border-color:var(--primary,#7c3aed); color:var(--primary,#7c3aed); }
        .img-add-label input[type=file] { display:none; }
        .img-new-preview { display:flex; flex-wrap:wrap; gap:10px; margin-top:4px; }
        .img-new-card {
            position:relative; width:110px; border-radius:8px; overflow:hidden;
            border:2px dashed var(--border); background:var(--surface);
        }
        .img-new-card img { width:110px; height:80px; object-fit:cover; display:block; }
        .img-new-card__del {
            position:absolute; top:4px; right:4px;
            width:22px; height:22px; border-radius:50%;
            background:rgba(0,0,0,.65); color:#fff; border:none;
            font-size:14px; line-height:22px; text-align:center;
            cursor:pointer; display:flex; align-items:center; justify-content:center;
        }
        .img-new-card__del:hover { background:#c0392b; }
        .img-empty-hint { color:var(--muted); font-size:13px; font-style:italic; }
    </style>
</head>
<body>
<div class="admin-layout">
    <?php include 'partials/sidebar.php'; ?>

    <div class="main-content">
        <div class="topbar">
            <div>
                <div class="topbar-title">Gestión de Eventos</div>
                <div class="topbar-sub">Sub-menús para Coffee Break, Brunch y Tardeo</div>
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
                        <label class="form-label">Imágenes
                            <span class="hint">— se convierten a WebP automáticamente · arrastra para reordenar · máx. 20 MB c/u</span>
                        </label>

                        <?php if ($edit_post):
                            $existing_imgs = parse_images($edit_post['image_filename'] ?? '');
                        ?>
                        <!-- ── Gestión de imágenes existentes ── -->
                        <div class="img-manager" id="img-manager">
                            <div class="img-grid" id="existing-grid">
                                <?php if (empty($existing_imgs)): ?>
                                <span class="img-empty-hint">Sin imágenes aún.</span>
                                <?php else: foreach ($existing_imgs as $idx => $fname): ?>
                                <div class="img-card" draggable="true" data-name="<?= htmlspecialchars($fname) ?>">
                                    <img src="../assets/img/eventos/<?= htmlspecialchars($active_cat) ?>/<?= htmlspecialchars($fname) ?>"
                                         alt="" onerror="this.src='data:image/svg+xml,<svg xmlns=\'http://www.w3.org/2000/svg\' width=\'110\' height=\'80\'><rect width=\'110\' height=\'80\' fill=\'%23eee\'/><text x=\'50%\' y=\'50%\' dominant-baseline=\'middle\' text-anchor=\'middle\' fill=\'%23999\' font-size=\'11\'>Sin imagen</text></svg>'">
                                    <button type="button" class="img-card__del" title="Quitar imagen">×</button>
                                    <div class="img-card__order"><?= $idx + 1 ?></div>
                                </div>
                                <?php endforeach; endif; ?>
                            </div>

                            <input type="hidden" name="existing_order" id="existing-order"
                                   value="<?= htmlspecialchars(json_encode($existing_imgs)) ?>">
                            <input type="hidden" name="deleted_images" id="deleted-images" value="[]">

                            <div class="img-add-zone">
                                <label class="img-add-label">
                                    ＋ Añadir imágenes
                                    <input type="file" name="new_images[]" id="new-imgs-input"
                                           multiple accept=".jpg,.jpeg,.png,.webp">
                                </label>
                            </div>
                            <div class="img-new-preview" id="new-imgs-preview"></div>
                        </div>

                        <?php else: ?>
                        <!-- ── Subida inicial (nueva entrada) ── -->
                        <label class="img-add-label" style="width:fit-content;">
                            ＋ Seleccionar imágenes
                            <input type="file" name="post_images[]" id="add-imgs-input"
                                   multiple accept=".jpg,.jpeg,.png,.webp">
                        </label>
                        <div class="img-new-preview" id="add-imgs-preview"></div>
                        <?php endif; ?>
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
                    <?php foreach ($posts as $post):
                        $thumb_imgs = parse_images($post['image_filename'] ?? '');
                        $thumb_src  = !empty($thumb_imgs)
                            ? '../assets/img/eventos/' . htmlspecialchars($active_cat) . '/' . htmlspecialchars($thumb_imgs[0])
                            : '';
                    ?>
                    <div class="post-item" draggable="true" data-id="<?= (int) $post['id'] ?>">
                        <?php if ($thumb_src): ?>
                        <img class="post-thumb" src="<?= $thumb_src ?>" alt="" onerror="this.style.display='none'">
                        <?php else: ?>
                        <div class="post-thumb-placeholder">🖼</div>
                        <?php endif; ?>
                        <?php if (count($thumb_imgs) > 1): ?>
                        <span style="position:absolute;top:6px;left:6px;background:rgba(0,0,0,.55);color:#fff;font-size:10px;border-radius:4px;padding:1px 5px;">
                            <?= count($thumb_imgs) ?> fotos
                        </span>
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
/* ── 1. Drag-reorder of post list ────────────────────────── */
(function () {
    const list    = document.getElementById('sortable-posts');
    const saveBtn = document.getElementById('save-order-btn');
    const orderIn = document.getElementById('order-input');
    if (!list || !saveBtn) return;

    let dragSrc = null;

    function getOrder() { return [...list.querySelectorAll('.post-item')].map(el => el.dataset.id); }
    function markChanged() { orderIn.value = JSON.stringify(getOrder()); saveBtn.disabled = false; }

    function bindPostItem(item) {
        item.addEventListener('dragstart', function (e) {
            dragSrc = this; e.dataTransfer.effectAllowed = 'move';
            setTimeout(() => this.classList.add('dragging'), 0);
        });
        item.addEventListener('dragend', function () {
            this.classList.remove('dragging');
            list.querySelectorAll('.post-item').forEach(i => i.classList.remove('drag-over'));
        });
        item.addEventListener('dragover', function (e) {
            e.preventDefault(); if (this === dragSrc) return;
            list.querySelectorAll('.post-item').forEach(i => i.classList.remove('drag-over'));
            this.classList.add('drag-over');
        });
        item.addEventListener('dragleave', function () { this.classList.remove('drag-over'); });
        item.addEventListener('drop', function (e) {
            e.preventDefault(); this.classList.remove('drag-over');
            if (dragSrc && dragSrc !== this) {
                const items = [...list.children];
                const si = items.indexOf(dragSrc), ti = items.indexOf(this);
                si < ti ? list.insertBefore(dragSrc, this.nextSibling) : list.insertBefore(dragSrc, this);
                markChanged();
            }
        });
    }
    list.querySelectorAll('.post-item').forEach(bindPostItem);
})();

/* ── 2. Image manager (edit form) ────────────────────────── */
(function () {
    const grid        = document.getElementById('existing-grid');
    const orderInput  = document.getElementById('existing-order');
    const deletedInput= document.getElementById('deleted-images');
    const newInput    = document.getElementById('new-imgs-input');
    const newPreview  = document.getElementById('new-imgs-preview');
    if (!grid) return;

    let deletedList = [];
    // DataTransfer object to keep the real File list in sync after removals
    let newFileList  = [];

    /* ── helpers ── */
    function updateOrderInput() {
        const names = [...grid.querySelectorAll('.img-card[data-name]')].map(c => c.dataset.name);
        orderInput.value = JSON.stringify(names);
        // refresh position numbers
        grid.querySelectorAll('.img-card__order').forEach((el, i) => el.textContent = i + 1);
    }

    function updateDeletedInput() {
        deletedInput.value = JSON.stringify(deletedList);
    }

    /* ── drag-reorder of existing images ── */
    let imgDragSrc = null;

    function bindCard(card) {
        card.addEventListener('dragstart', function (e) {
            imgDragSrc = this; e.dataTransfer.effectAllowed = 'move';
            setTimeout(() => this.classList.add('dragging'), 0);
        });
        card.addEventListener('dragend', function () {
            this.classList.remove('dragging');
            grid.querySelectorAll('.img-card').forEach(c => c.classList.remove('drag-over'));
        });
        card.addEventListener('dragover', function (e) {
            e.preventDefault(); if (this === imgDragSrc) return;
            grid.querySelectorAll('.img-card').forEach(c => c.classList.remove('drag-over'));
            this.classList.add('drag-over');
        });
        card.addEventListener('dragleave', function () { this.classList.remove('drag-over'); });
        card.addEventListener('drop', function (e) {
            e.preventDefault(); this.classList.remove('drag-over');
            if (imgDragSrc && imgDragSrc !== this) {
                const cards = [...grid.querySelectorAll('.img-card')];
                const si = cards.indexOf(imgDragSrc), ti = cards.indexOf(this);
                si < ti ? grid.insertBefore(imgDragSrc, this.nextSibling) : grid.insertBefore(imgDragSrc, this);
                updateOrderInput();
            }
        });

        /* delete button */
        card.querySelector('.img-card__del').addEventListener('click', function () {
            const name = card.dataset.name;
            deletedList.push(name);
            updateDeletedInput();
            card.remove();
            updateOrderInput();
            if (!grid.querySelector('.img-card')) {
                grid.innerHTML = '<span class="img-empty-hint">Sin imágenes — añade nuevas abajo.</span>';
            }
        });
    }

    grid.querySelectorAll('.img-card').forEach(bindCard);

    /* ── new images preview ── */
    if (newInput && newPreview) {
        newInput.addEventListener('change', function () {
            newFileList = [...this.files];
            renderNewPreviews();
        });
    }

    function renderNewPreviews() {
        newPreview.innerHTML = '';
        newFileList.forEach((file, idx) => {
            const reader = new FileReader();
            reader.onload = function (e) {
                const card = document.createElement('div');
                card.className = 'img-new-card';
                card.innerHTML = `<img src="${e.target.result}" alt=""><button type="button" class="img-new-card__del" data-idx="${idx}">×</button>`;
                card.querySelector('.img-new-card__del').addEventListener('click', function () {
                    newFileList.splice(parseInt(this.dataset.idx), 1);
                    rebuildFileInput();
                    renderNewPreviews();
                });
                newPreview.appendChild(card);
            };
            reader.readAsDataURL(file);
        });
    }

    function rebuildFileInput() {
        // Re-assign the file list via DataTransfer
        const dt = new DataTransfer();
        newFileList.forEach(f => dt.items.add(f));
        newInput.files = dt.files;
    }
})();

/* ── 3. New-post image preview ───────────────────────────── */
(function () {
    const addInput   = document.getElementById('add-imgs-input');
    const addPreview = document.getElementById('add-imgs-preview');
    if (!addInput || !addPreview) return;

    let fileList = [];

    addInput.addEventListener('change', function () {
        fileList = [...this.files];
        render();
    });

    function render() {
        addPreview.innerHTML = '';
        fileList.forEach((file, idx) => {
            const reader = new FileReader();
            reader.onload = function (e) {
                const card = document.createElement('div');
                card.className = 'img-new-card';
                card.innerHTML = `<img src="${e.target.result}" alt=""><button type="button" class="img-new-card__del" data-idx="${idx}">×</button>`;
                card.querySelector('.img-new-card__del').addEventListener('click', function () {
                    fileList.splice(parseInt(this.dataset.idx), 1);
                    const dt = new DataTransfer();
                    fileList.forEach(f => dt.items.add(f));
                    addInput.files = dt.files;
                    render();
                });
                addPreview.appendChild(card);
            };
            reader.readAsDataURL(file);
        });
    }
})();
</script>
</body>
</html>
