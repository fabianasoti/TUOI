<?php
require_once 'config.php';
require_once __DIR__ . '/partials/image_utils.php';

$base_img    = dirname(__DIR__) . '/assets/img/';
$base_url    = '../assets/img/';
$max_size    = 20 * 1024 * 1024;
$allowed_ext = ['jpg', 'jpeg', 'png', 'webp', 'gif'];

$sections = [
    // General
    'carteles'               => ['label' => 'Carteles / Logos',             'path' => $base_img . 'carteles/'],
    'quienes_somos'          => ['label' => 'Quiénes somos',                'path' => $base_img . 'quienes_somos/'],
    'inicio'                 => ['label' => 'Inicio (imagen principal)',     'path' => $base_img],
    // Carta — Español
    'carta/desayunos'        => ['label' => 'Carta — Desayunos',            'path' => $base_img . 'carta/desayunos/'],
    'carta/toque-salado'     => ['label' => 'Carta — Toque Salado',         'path' => $base_img . 'carta/toque-salado/'],
    'carta/momento-dulce'    => ['label' => 'Carta — Momento Dulce',        'path' => $base_img . 'carta/momento-dulce/'],
    'carta/bebidas'          => ['label' => 'Carta — Bebidas',              'path' => $base_img . 'carta/bebidas/'],
    'carta/superalimentos'   => ['label' => 'Carta — Superalimentos',       'path' => $base_img . 'carta/superalimentos/'],
    // Eventos — carrusel y por qué TUOI
    'eventos/carrusel'       => ['label' => 'Eventos — Carrusel',           'path' => $base_img . 'eventos/carrusel/'],
    'eventos/por-que-tuoi'   => ['label' => 'Eventos — Por qué TUOI',       'path' => $base_img . 'eventos/por-que-tuoi/'],
    'eventos/logos'          => ['label' => 'Eventos — Logos clientes',     'path' => $base_img . 'eventos/logos/'],
    // Eventos — imágenes de sub-menús (se suben desde "Gestión de Eventos")
    'eventos/coffee-break'   => ['label' => 'Eventos — Coffee Break',       'path' => $base_img . 'eventos/coffee-break/'],
    'eventos/brunch'         => ['label' => 'Eventos — Brunch',             'path' => $base_img . 'eventos/brunch/'],
    'eventos/tardeo'         => ['label' => 'Eventos — Tardeo',             'path' => $base_img . 'eventos/tardeo/'],
    // Carta — English
    'carta/desayunos-en'     => ['label' => 'Carta — Desayunos (EN)',       'path' => $base_img . 'carta/desayunos-en/'],
    'carta/toque-salado-en'  => ['label' => 'Carta — Toque Salado (EN)',    'path' => $base_img . 'carta/toque-salado-en/'],
    'carta/momento-dulce-en' => ['label' => 'Carta — Momento Dulce (EN)',   'path' => $base_img . 'carta/momento-dulce-en/'],
    'carta/bebidas-en'       => ['label' => 'Carta — Bebidas (EN)',         'path' => $base_img . 'carta/bebidas-en/'],
    'carta/superalimentos-en'=> ['label' => 'Carta — Superalimentos (EN)',  'path' => $base_img . 'carta/superalimentos-en/'],
];

$active_section = $_GET['s'] ?? 'carteles';
if (!array_key_exists($active_section, $sections)) $active_section = 'carteles';

$dir_path = $sections[$active_section]['path'];
$success  = '';
$error    = '';

// ── Handle upload ────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['imagen'])) {
    if (!is_dir($dir_path)) {
        $error = 'El directorio de destino no existe.';
    } elseif (strpos(realpath($dir_path), realpath($base_img)) !== 0) {
        $error = 'Destino no permitido.';
    } else {
        $files = $_FILES['imagen'];
        if (!is_array($files['name'])) {
            foreach ($files as $k => $v) $files[$k] = [$v];
        }

        $uploaded = 0;
        $skipped  = [];

        for ($i = 0; $i < count($files['name']); $i++) {
            if ($files['error'][$i] !== UPLOAD_ERR_OK) {
                $skipped[] = $files['name'][$i] . ' (error PHP ' . $files['error'][$i] . ')';
                continue;
            }
            $orig_name = $files['name'][$i];
            $ext       = strtolower(pathinfo($orig_name, PATHINFO_EXTENSION));
            $base_name = preg_replace('/[^a-zA-Z0-9._-]/', '_', pathinfo($orig_name, PATHINFO_FILENAME));

            if (!in_array($ext, $allowed_ext, true)) {
                $skipped[] = $orig_name . ' (formato no permitido)';
                continue;
            }
            if ($files['size'][$i] > $max_size) {
                $skipped[] = $orig_name . ' (supera 20 MB)';
                continue;
            }

            $webp_name = $base_name . '.webp';
            $tmp       = $files['tmp_name'][$i];
            $tmp_dest  = $dir_path . uniqid('_tmp_');

            if (!move_uploaded_file($tmp, $tmp_dest)) {
                $skipped[] = $orig_name . ' (error al mover al servidor)';
                continue;
            }

            if (convert_to_webp($tmp_dest, $dir_path . $webp_name)) {
                @unlink($tmp_dest);
                $uploaded++;
            } else {
                // fallback: keep original
                $fallback = $dir_path . $base_name . '.' . $ext;
                rename($tmp_dest, $fallback);
                $uploaded++;
                $webp_name = $base_name . '.' . $ext;
            }
        }

        if ($uploaded > 0) $success = "$uploaded imagen(es) subida(s), optimizada(s) (máx. 2000 px) y convertida(s) a WebP.";
        if (!empty($skipped)) $error = 'No se pudo subir: ' . implode(', ', $skipped);
    }
}

// ── Handle delete ────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_file'])) {
    $target    = $dir_path . basename($_POST['delete_file']);
    $real_base = realpath($base_img);
    $real_tgt  = realpath($target);

    if ($real_tgt && $real_base && strpos($real_tgt, $real_base) === 0 && is_file($real_tgt)) {
        if (unlink($real_tgt)) {
            $fn = mysqli_real_escape_string($conexion, basename($real_tgt));
            $s  = mysqli_real_escape_string($conexion, $active_section);
            @mysqli_query($conexion, "DELETE FROM image_order WHERE section='$s' AND filename='$fn'");
            $success = 'Imagen eliminada.';
        } else {
            $error = 'No se pudo eliminar. Comprueba permisos.';
        }
    } else {
        $error = 'Archivo no válido o no encontrado.';
    }
}

// ── Handle save order ────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_order'])) {
    $filenames = json_decode($_POST['save_order'], true);
    if (is_array($filenames)) {
        $s = mysqli_real_escape_string($conexion, $active_section);
        @mysqli_query($conexion, "DELETE FROM image_order WHERE section = '$s'");
        foreach ($filenames as $i => $filename) {
            $fn    = mysqli_real_escape_string($conexion, basename($filename));
            $order = (int) $i;
            @mysqli_query($conexion,
                "INSERT INTO image_order (section, filename, sort_order)
                 VALUES ('$s', '$fn', $order)
                 ON DUPLICATE KEY UPDATE sort_order = $order"
            );
        }
        $success = 'Orden guardado.';
    } else {
        $error = 'Error al leer el orden enviado.';
    }
}

// ── List images (respecting saved order) ─────────────────
require_once dirname(__DIR__) . '/config/content_helper.php';
$ordered_paths = load_ordered_images($conexion, $active_section, $dir_path, '*.{jpg,jpeg,png,webp,gif}');
$images        = array_map('basename', $ordered_paths);
$url_path      = $base_url . ltrim(str_replace($base_img, '', $dir_path), '/');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>TUOI Admin — Imágenes</title>
    <link rel="stylesheet" href="../assets/fonts/inter.css">
    <link rel="stylesheet" href="assets/css/admin.css">
    <style>
        /* ── Upload drop zone ──────────────────────────────── */
        .upload-zone {
            display: flex; flex-direction: column; align-items: center; justify-content: center;
            gap: 8px; padding: 2rem; border: 2px dashed var(--border);
            border-radius: 12px; cursor: pointer; transition: border-color .2s, background .2s;
            text-align: center; color: var(--muted);
        }
        .upload-zone:hover, .upload-zone.drag-over {
            border-color: var(--primary, #7c3aed);
            background: rgba(124,58,237,.04);
            color: var(--primary, #7c3aed);
        }
        .upload-zone .upload-icon { font-size: 2rem; }
        .upload-zone p { margin: 0; font-size: 14px; }
        .upload-zone .hint { font-size: 12px; opacity: .7; }

        /* ── Image card grid ───────────────────────────────── */
        .img-card-grid {
            display: flex; flex-wrap: wrap; gap: 12px;
        }
        .img-card {
            position: relative; width: 140px; border-radius: 10px; overflow: hidden;
            border: 2px solid var(--border); background: var(--surface);
            cursor: grab; user-select: none;
            transition: border-color .15s, box-shadow .15s, opacity .15s;
            box-shadow: 0 2px 8px rgba(0,0,0,.06);
        }
        .img-card:hover { border-color: var(--primary,#7c3aed); box-shadow: 0 4px 16px rgba(0,0,0,.12); }
        .img-card:active { cursor: grabbing; }
        .img-card.dragging { opacity: .3; border-color: var(--primary,#7c3aed); }
        .img-card.drag-over {
            border-color: var(--primary,#7c3aed);
            background: rgba(124,58,237,.07);
            box-shadow: 0 0 0 3px rgba(124,58,237,.2);
        }
        .img-card__thumb {
            width: 140px; height: 100px; object-fit: cover; display: block; pointer-events: none;
        }
        .img-card__thumb-placeholder {
            width: 140px; height: 100px; display: flex; align-items: center; justify-content: center;
            background: var(--surface-2, #f5f5f5); font-size: 28px;
        }
        .img-card__del {
            position: absolute; top: 5px; right: 5px;
            width: 24px; height: 24px; border-radius: 50%;
            background: rgba(0,0,0,.6); color: #fff; border: none;
            font-size: 15px; cursor: pointer;
            display: flex; align-items: center; justify-content: center;
            transition: background .15s; z-index: 2;
        }
        .img-card__del:hover { background: #c0392b; }
        .img-card__name {
            padding: 5px 6px 6px;
            font-size: 10px; color: var(--muted);
            white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
            border-top: 1px solid var(--border);
            background: var(--surface);
        }
        .img-card__drag {
            position: absolute; top: 5px; left: 5px;
            background: rgba(0,0,0,.45); color: #fff;
            font-size: 12px; border-radius: 4px; padding: 1px 4px;
            pointer-events: none; line-height: 1;
        }

        /* new-images preview strip (before upload) */
        .new-preview { display: flex; flex-wrap: wrap; gap: 10px; margin-top: 12px; }
        .new-preview-card {
            position: relative; width: 100px; border-radius: 8px; overflow: hidden;
            border: 2px dashed var(--border);
        }
        .new-preview-card img { width: 100px; height: 70px; object-fit: cover; display: block; }
        .new-preview-card__del {
            position: absolute; top: 3px; right: 3px; width: 20px; height: 20px;
            border-radius: 50%; background: rgba(0,0,0,.6); color: #fff; border: none;
            font-size: 13px; cursor: pointer;
            display: flex; align-items: center; justify-content: center;
        }
        .new-preview-card__del:hover { background: #c0392b; }
    </style>
</head>
<body>
<div class="admin-layout">
    <?php include 'partials/sidebar.php'; ?>

    <div class="main-content">
        <div class="topbar">
            <div>
                <div class="topbar-title">Gestión de imágenes</div>
                <div class="topbar-sub">Se convierten a WebP automáticamente · Arrastra para reordenar</div>
            </div>
        </div>

        <div class="content-area">

            <?php include 'partials/toast.php'; ?>

            <!-- Section tabs -->
            <div class="section-tabs">
                <?php foreach ($sections as $key => $info): ?>
                <a href="?s=<?= urlencode($key) ?>"
                   class="section-tab <?= $key === $active_section ? 'active' : '' ?>">
                    <?= htmlspecialchars($info['label']) ?>
                </a>
                <?php endforeach; ?>
            </div>

            <!-- Upload card -->
            <div class="card">
                <div class="card-header">
                    <div class="card-title">
                        ⬆️ Subir imágenes
                        <span class="section-badge"><?= htmlspecialchars($sections[$active_section]['label']) ?></span>
                    </div>
                </div>
                <form method="post" enctype="multipart/form-data" id="upload-form">
                    <input type="hidden" name="section" value="<?= htmlspecialchars($active_section) ?>">
                    <input id="file-input" name="imagen[]" type="file"
                           accept=".jpg,.jpeg,.png,.webp,.gif" multiple style="display:none">
                    <label class="upload-zone" id="drop-zone" for="file-input">
                        <div class="upload-icon">📁</div>
                        <p>Haz clic o arrastra imágenes aquí</p>
                        <p class="hint">JPG · PNG · GIF · WEBP · Máx. 20 MB · Se guardan como WebP</p>
                    </label>
                    <div class="new-preview" id="new-preview"></div>
                    <button type="submit" class="btn btn-primary" style="margin-top:12px;" id="submit-btn" disabled>
                        ⬆️ Subir
                    </button>
                </form>
            </div>

            <!-- Images grid -->
            <div class="card">
                <div class="card-header">
                    <div class="card-title">
                        🖼️ Imágenes actuales
                        <span class="badge-count"><?= count($images) ?></span>
                    </div>
                    <?php if (count($images) > 1): ?>
                    <div style="display:flex;align-items:center;gap:10px;">
                        <span style="font-size:12px;color:var(--muted);">Arrastra para reordenar</span>
                        <form method="post" id="order-form">
                            <input type="hidden" name="section" value="<?= htmlspecialchars($active_section) ?>">
                            <input type="hidden" name="save_order" id="order-input" value="">
                            <button type="submit" id="save-order-btn" class="btn btn-verde btn-sm" disabled>
                                💾 Guardar orden
                            </button>
                        </form>
                    </div>
                    <?php endif; ?>
                </div>

                <?php if (empty($images)): ?>
                <div class="empty-state">
                    <span class="empty-icon">📭</span>
                    No hay imágenes en esta sección todavía.
                </div>
                <?php else: ?>
                <div class="img-card-grid" id="sortable-grid">
                    <?php foreach ($images as $img):
                        $rel_url = $base_url . ltrim(str_replace($base_img, '', $dir_path), '/') . $img;
                    ?>
                    <div class="img-card" draggable="true" data-filename="<?= htmlspecialchars($img) ?>">
                        <div class="img-card__drag">⠿</div>
                        <img class="img-card__thumb"
                             src="<?= htmlspecialchars($rel_url) ?>"
                             alt="<?= htmlspecialchars($img) ?>" loading="lazy"
                             onerror="this.replaceWith(Object.assign(document.createElement('div'),{className:'img-card__thumb-placeholder',textContent:'🖼'}))">
                        <!-- Delete button submits its own mini-form -->
                        <button type="button" class="img-card__del"
                                title="Eliminar"
                                onclick="deleteImg(this,'<?= htmlspecialchars(addslashes($img)) ?>')">×</button>
                        <div class="img-card__name" title="<?= htmlspecialchars($img) ?>"><?= htmlspecialchars($img) ?></div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <!-- Hidden delete form -->
                <form method="post" id="delete-form" style="display:none">
                    <input type="hidden" name="section"     value="<?= htmlspecialchars($active_section) ?>">
                    <input type="hidden" name="delete_file" id="delete-filename" value="">
                </form>
                <?php endif; ?>
            </div>

        </div>
    </div>
</div>

<script>
/* ── Drop zone + preview before upload ───────────────────── */
(function () {
    const zone      = document.getElementById('drop-zone');
    const input     = document.getElementById('file-input');
    const preview   = document.getElementById('new-preview');
    const submitBtn = document.getElementById('submit-btn');
    let fileList    = [];

    function syncInput() {
        const dt = new DataTransfer();
        fileList.forEach(f => dt.items.add(f));
        input.files    = dt.files;
        submitBtn.disabled = fileList.length === 0;
    }

    function renderPreview() {
        preview.innerHTML = '';
        fileList.forEach((file, idx) => {
            const reader = new FileReader();
            reader.onload = e => {
                const card = document.createElement('div');
                card.className = 'new-preview-card';
                card.innerHTML = `<img src="${e.target.result}" alt=""><button type="button" class="new-preview-card__del" data-idx="${idx}">×</button>`;
                card.querySelector('button').addEventListener('click', function () {
                    fileList.splice(+this.dataset.idx, 1);
                    syncInput(); renderPreview();
                });
                preview.appendChild(card);
            };
            reader.readAsDataURL(file);
        });
    }

    input.addEventListener('change', () => {
        fileList = [...input.files]; syncInput(); renderPreview();
    });

    zone.addEventListener('dragover',  e => { e.preventDefault(); zone.classList.add('drag-over'); });
    zone.addEventListener('dragleave', () => zone.classList.remove('drag-over'));
    zone.addEventListener('drop', e => {
        e.preventDefault(); zone.classList.remove('drag-over');
        const dropped = [...e.dataTransfer.files].filter(f => f.type.startsWith('image/'));
        fileList = [...fileList, ...dropped];
        syncInput(); renderPreview();
    });

    document.getElementById('upload-form').addEventListener('submit', e => {
        if (fileList.length === 0) { e.preventDefault(); alert('Selecciona al menos una imagen.'); }
    });
})();

/* ── Delete via hidden form ──────────────────────────────── */
function deleteImg(btn, filename) {
    if (!confirm('¿Eliminar ' + filename + '?')) return;
    document.getElementById('delete-filename').value = filename;
    document.getElementById('delete-form').submit();
}

/* ── Drag-to-reorder existing images ─────────────────────── */
(function () {
    const grid    = document.getElementById('sortable-grid');
    const saveBtn = document.getElementById('save-order-btn');
    const orderIn = document.getElementById('order-input');
    if (!grid || !saveBtn) return;

    let dragSrc = null;

    function getOrder() {
        return [...grid.querySelectorAll('.img-card')].map(el => el.dataset.filename);
    }
    function markChanged() {
        orderIn.value      = JSON.stringify(getOrder());
        saveBtn.disabled   = false;
    }

    grid.querySelectorAll('.img-card').forEach(card => {
        card.addEventListener('dragstart', function (e) {
            dragSrc = this; e.dataTransfer.effectAllowed = 'move';
            setTimeout(() => this.classList.add('dragging'), 0);
        });
        card.addEventListener('dragend', function () {
            this.classList.remove('dragging');
            grid.querySelectorAll('.img-card').forEach(c => c.classList.remove('drag-over'));
        });
        card.addEventListener('dragover', function (e) {
            e.preventDefault(); if (this === dragSrc) return;
            grid.querySelectorAll('.img-card').forEach(c => c.classList.remove('drag-over'));
            this.classList.add('drag-over');
        });
        card.addEventListener('dragleave', function () { this.classList.remove('drag-over'); });
        card.addEventListener('drop', function (e) {
            e.preventDefault(); this.classList.remove('drag-over');
            if (dragSrc && dragSrc !== this) {
                const cards = [...grid.querySelectorAll('.img-card')];
                const si = cards.indexOf(dragSrc), ti = cards.indexOf(this);
                si < ti ? grid.insertBefore(dragSrc, this.nextSibling)
                        : grid.insertBefore(dragSrc, this);
                markChanged();
            }
        });
    });
})();
</script>
</body>
</html>
