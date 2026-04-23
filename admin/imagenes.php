<?php
require_once 'config.php';

$base_img   = dirname(__DIR__) . '/assets/img/';
$base_url   = '../assets/img/';
$max_size   = 10 * 1024 * 1024; // 10 MB
$allowed_ext = ['jpg', 'jpeg', 'png', 'webp'];

$sections = [
    // General
    'carteles'                  => ['label' => 'Carteles / Logos',              'path' => $base_img . 'carteles/'],
    'quienes_somos'             => ['label' => 'Quiénes somos',                 'path' => $base_img . 'quienes_somos/'],
    'inicio'                    => ['label' => 'Inicio (imagen principal)',      'path' => $base_img],
    // Carta — Español
    'carta/desayunos'           => ['label' => 'Carta — Desayunos',             'path' => $base_img . 'carta/desayunos/'],
    'carta/toque-salado'        => ['label' => 'Carta — Toque Salado',          'path' => $base_img . 'carta/toque-salado/'],
    'carta/momento-dulce'       => ['label' => 'Carta — Momento Dulce',         'path' => $base_img . 'carta/momento-dulce/'],
    'carta/bebidas'             => ['label' => 'Carta — Bebidas',               'path' => $base_img . 'carta/bebidas/'],
    'carta/superalimentos'      => ['label' => 'Carta — Superalimentos',        'path' => $base_img . 'carta/superalimentos/'],
    // Eventos
    'eventos/eventos'           => ['label' => 'Eventos — Eventos',             'path' => $base_img . 'eventos/eventos/'],
    'eventos/networking'        => ['label' => 'Eventos — Networking',          'path' => $base_img . 'eventos/networking/'],
    'eventos/team-building'     => ['label' => 'Eventos — Team Building',       'path' => $base_img . 'eventos/team-building/'],
    'eventos/catering'          => ['label' => 'Eventos — Catering',            'path' => $base_img . 'eventos/catering/'],
    // Carta — English
    'carta/desayunos-en'        => ['label' => 'Carta — Desayunos (EN)',        'path' => $base_img . 'carta/desayunos-en/'],
    'carta/toque-salado-en'     => ['label' => 'Carta — Toque Salado (EN)',     'path' => $base_img . 'carta/toque-salado-en/'],
    'carta/momento-dulce-en'    => ['label' => 'Carta — Momento Dulce (EN)',    'path' => $base_img . 'carta/momento-dulce-en/'],
    'carta/bebidas-en'          => ['label' => 'Carta — Bebidas (EN)',          'path' => $base_img . 'carta/bebidas-en/'],
    'carta/superalimentos-en'   => ['label' => 'Carta — Superalimentos (EN)',   'path' => $base_img . 'carta/superalimentos-en/'],
];

$active_section = $_GET['s'] ?? 'carteles';
if (!array_key_exists($active_section, $sections)) {
    $active_section = 'carteles';
}

$dir_path = $sections[$active_section]['path'];
$success  = '';
$error    = '';

// ── Handle upload (multi-file) ───────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['imagen'])) {
    if (!is_dir($dir_path)) {
        $error = 'El directorio de destino no existe.';
    } elseif (strpos(realpath($dir_path), realpath($base_img)) !== 0) {
        $error = 'Destino no permitido.';
    } else {
        // Normalize to array whether one or multiple files were sent
        $files = $_FILES['imagen'];
        if (!is_array($files['name'])) {
            foreach ($files as $k => $v) $files[$k] = [$v];
        }

        $uploaded = 0;
        $skipped  = [];

        for ($i = 0; $i < count($files['name']); $i++) {
            if ($files['error'][$i] !== UPLOAD_ERR_OK) {
                $skipped[] = $files['name'][$i] . ' (error ' . $files['error'][$i] . ')';
                continue;
            }
            $ext       = strtolower(pathinfo($files['name'][$i], PATHINFO_EXTENSION));
            $safe_name = preg_replace('/[^a-zA-Z0-9._-]/', '_', basename($files['name'][$i]));

            if (!in_array($ext, $allowed_ext, true)) {
                $skipped[] = $safe_name . ' (formato no permitido)';
                continue;
            }
            if ($files['size'][$i] > $max_size) {
                $skipped[] = $safe_name . ' (supera 10 MB)';
                continue;
            }
            if (move_uploaded_file($files['tmp_name'][$i], $dir_path . $safe_name)) {
                $uploaded++;
            } else {
                $skipped[] = $safe_name . ' (error al mover)';
            }
        }

        if ($uploaded > 0) {
            $success = "$uploaded imagen(es) subida(s) correctamente.";
        }
        if (!empty($skipped)) {
            $error = 'No se pudo subir: ' . implode(', ', $skipped);
        }
    }
}

// ── Handle delete ────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_file'])) {
    $target    = $dir_path . basename($_POST['delete_file']);
    $real_base = realpath($base_img);
    $real_tgt  = realpath($target);

    if ($real_tgt && $real_base && strpos($real_tgt, $real_base) === 0 && is_file($real_tgt)) {
        if (unlink($real_tgt)) {
            // Remove from order table too
            $fn = mysqli_real_escape_string($conexion, basename($real_tgt));
            $s  = mysqli_real_escape_string($conexion, $active_section);
            @mysqli_query($conexion, "DELETE FROM image_order WHERE section='$s' AND filename='$fn'");
            $success = 'Imagen eliminada correctamente.';
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
        $success = 'Orden guardado correctamente.';
    } else {
        $error = 'Error al leer el orden enviado.';
    }
}

// ── List current images (respecting saved order) ─────────
require_once dirname(__DIR__) . '/config/content_helper.php';
$ordered_paths = load_ordered_images($conexion, $active_section, $dir_path, '*.{jpg,jpeg,png,webp}');
$images = array_map('basename', $ordered_paths);

// Build URL for current section
$url_path = $base_url . str_replace($base_img, '', $dir_path);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>TUOI Admin — Imágenes</title>
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
                <div class="topbar-title">Gestión de imágenes</div>
                <div class="topbar-sub">Sube, visualiza y elimina imágenes por sección</div>
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
                        ⬆️ Subir imagen
                        <span class="section-badge"><?= htmlspecialchars($sections[$active_section]['label']) ?></span>
                    </div>
                </div>
                <form method="post" enctype="multipart/form-data" id="upload-form">
                    <input type="hidden" name="section" value="<?= htmlspecialchars($active_section) ?>">
                    <input id="file-input" name="imagen[]" type="file"
                           accept=".jpg,.jpeg,.png,.webp"
                           multiple
                           style="display:none"
                           onchange="updateLabel(this)">
                    <label class="upload-zone" id="drop-zone" for="file-input">
                        <div class="upload-icon">📁</div>
                        <p>Haz clic o arrastra una imagen aquí</p>
                        <p style="font-size:12px;margin-top:4px;">JPG, JPEG, PNG, WEBP · Máx. 10 MB · Puedes seleccionar varias</p>
                    </label>
                    <p id="file-label" style="font-size:13px;color:var(--muted);margin:10px 0;"></p>
                    <button type="submit" class="btn btn-primary" style="margin-top:4px;">
                        ⬆️ Subir imagen
                    </button>
                </form>
            </div>

            <!-- Images grid + order -->
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
                    <div class="img-grid" id="sortable-grid">
                    <?php foreach ($images as $img): ?>
                        <?php
                        $rel_url = '../assets/img/' . ltrim(str_replace($base_img, '', $dir_path), '/') . $img;
                        ?>
                        <div class="img-item" draggable="true" data-filename="<?= htmlspecialchars($img) ?>">
                            <div class="img-drag-handle" title="Arrastrar para ordenar">⠿</div>
                            <img src="<?= htmlspecialchars($rel_url) ?>"
                                 alt="<?= htmlspecialchars($img) ?>"
                                 loading="lazy"
                                 onerror="this.style.display='none'">
                            <div class="img-info">
                                <span class="img-filename" title="<?= htmlspecialchars($img) ?>">
                                    <?= htmlspecialchars($img) ?>
                                </span>
                                <form method="post" class="img-delete-form"
                                      onsubmit="return confirm('¿Eliminar <?= htmlspecialchars(addslashes($img)) ?>?')">
                                    <input type="hidden" name="delete_file" value="<?= htmlspecialchars($img) ?>">
                                    <input type="hidden" name="section" value="<?= htmlspecialchars($active_section) ?>">
                                    <button type="submit" class="img-delete-btn" title="Eliminar">🗑</button>
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

<script>
function updateLabel(input) {
    const label = document.getElementById('file-label');
    if (!input.files.length) { label.textContent = ''; return; }
    label.textContent = input.files.length === 1
        ? '📎 ' + input.files[0].name
        : '📎 ' + input.files.length + ' archivos seleccionados';
}

const zone  = document.getElementById('drop-zone');
const input = document.getElementById('file-input');

zone.addEventListener('dragover',  e => { e.preventDefault(); zone.classList.add('drag-over'); });
zone.addEventListener('dragleave', () => zone.classList.remove('drag-over'));
zone.addEventListener('drop', e => {
    e.preventDefault();
    zone.classList.remove('drag-over');
    // Assign dropped files to the hidden input
    const dt = e.dataTransfer;
    if (dt.files.length) {
        // DataTransfer trick to set files on input
        const list = new DataTransfer();
        list.items.add(dt.files[0]);
        input.files = list.files;
        updateLabel(input);
    }
});

document.getElementById('upload-form').addEventListener('submit', e => {
    if (!input.files || input.files.length === 0) {
        e.preventDefault();
        alert('Selecciona una imagen antes de subir.');
    }
});

// ── Sortable grid ──────────────────────────────────────────
(function () {
    const grid    = document.getElementById('sortable-grid');
    const saveBtn = document.getElementById('save-order-btn');
    const orderIn = document.getElementById('order-input');
    if (!grid || !saveBtn) return;

    let dragSrc = null;

    function getOrder() {
        return [...grid.querySelectorAll('.img-item')].map(el => el.dataset.filename);
    }

    function markChanged() {
        orderIn.value = JSON.stringify(getOrder());
        saveBtn.disabled = false;
        saveBtn.textContent = '💾 Guardar orden';
    }

    grid.querySelectorAll('.img-item').forEach(item => {
        item.addEventListener('dragstart', function (e) {
            dragSrc = this;
            e.dataTransfer.effectAllowed = 'move';
            setTimeout(() => this.classList.add('dragging'), 0);
        });

        item.addEventListener('dragend', function () {
            this.classList.remove('dragging');
            grid.querySelectorAll('.img-item').forEach(i => i.classList.remove('drag-over'));
        });

        item.addEventListener('dragover', function (e) {
            e.preventDefault();
            if (this === dragSrc) return;
            grid.querySelectorAll('.img-item').forEach(i => i.classList.remove('drag-over'));
            this.classList.add('drag-over');
        });

        item.addEventListener('dragleave', function () {
            this.classList.remove('drag-over');
        });

        item.addEventListener('drop', function (e) {
            e.preventDefault();
            this.classList.remove('drag-over');
            if (dragSrc && dragSrc !== this) {
                const items  = [...grid.children];
                const srcIdx = items.indexOf(dragSrc);
                const tgtIdx = items.indexOf(this);
                srcIdx < tgtIdx
                    ? grid.insertBefore(dragSrc, this.nextSibling)
                    : grid.insertBefore(dragSrc, this);
                markChanged();
            }
        });
    });
})();
</script>
</body>
</html>
