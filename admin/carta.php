<?php
require_once 'config.php';
require_once __DIR__ . '/partials/image_utils.php';
require_once dirname(__DIR__) . '/config/carta_meta.php';

$success = '';
$error   = '';

// Adaptamos los metadatos compartidos al formato que usa este admin (ES label
// directo + icono). Las claves son la fuente de verdad — no añadir aquí.
$CATEGORIAS = [];
foreach ($CARTA_CATEGORIAS as $slug => $labels) {
    $CATEGORIAS[$slug] = $labels['es'];
}
$ALERGENOS = [];
foreach ($CARTA_ALERGENOS as $key => $meta) {
    $ALERGENOS[$key] = ['label' => $meta['es'], 'icon' => $meta['icon']];
}

// Idioma de edición (tab ES/EN).
$edit_lang = ($_GET['lang'] ?? 'es') === 'en' ? 'en' : 'es';

// Categoría activa en el admin (filtra la lista y a qué cat se añaden ítems nuevos).
$active_cat = $_GET['cat'] ?? 'desayunos';
if (!array_key_exists($active_cat, $CATEGORIAS)) $active_cat = 'desayunos';

// Directorio de fotos de ítems (compartido entre categorías).
// El slug `categoria` ya separa los ítems lógicamente; no hace falta una
// subcarpeta por categoría.
$items_dir = dirname(__DIR__) . '/assets/img/carta/items/';
$items_url = '../assets/img/carta/items/';

// Auto-creación: igual que en imagenes.php, forzamos 0775 con chmod para
// que www-data pueda escribir (el umask deja la carpeta en 0755 si no).
if (!is_dir($items_dir)) {
    @mkdir($items_dir, 0775, true);
    @chmod($items_dir, 0775);
}

// ── Crear / migrar tabla ─────────────────────────────────
@mysqli_query($conexion,
    "CREATE TABLE IF NOT EXISTS carta_items (
        id              INT AUTO_INCREMENT PRIMARY KEY,
        categoria       VARCHAR(50)  NOT NULL,
        subcategoria    VARCHAR(100) NULL,
        subcategoria_en VARCHAR(100) NULL,
        nombre          VARCHAR(255) NOT NULL,
        nombre_en       VARCHAR(255) NULL,
        descripcion     TEXT         NULL,
        descripcion_en  TEXT         NULL,
        ingredientes    TEXT         NULL,
        ingredientes_en TEXT         NULL,
        precio          DECIMAL(6,2) NULL,
        es_suplemento   TINYINT(1)   NOT NULL DEFAULT 0,
        alergenos       VARCHAR(255) NULL,
        foto            VARCHAR(255) NULL,
        visible         TINYINT(1)   NOT NULL DEFAULT 1,
        sort_order      INT          NOT NULL DEFAULT 0,
        created_at      TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
        updated_at      TIMESTAMP    DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_cat_order (categoria, sort_order)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
);
// Migraciones idempotentes para bases ya creadas con el schema anterior.
try { mysqli_query($conexion, "ALTER TABLE carta_items ADD COLUMN subcategoria VARCHAR(100) NULL AFTER categoria"); } catch (\Throwable $e) {}
try { mysqli_query($conexion, "ALTER TABLE carta_items ADD COLUMN subcategoria_en VARCHAR(100) NULL AFTER subcategoria"); } catch (\Throwable $e) {}
try { mysqli_query($conexion, "ALTER TABLE carta_items ADD COLUMN es_suplemento TINYINT(1) NOT NULL DEFAULT 0 AFTER precio"); } catch (\Throwable $e) {}

// ── Helpers ──────────────────────────────────────────────

/**
 * Procesa el campo de subida `foto`. Devuelve nombre de archivo guardado
 * o null si no se subió nada. En caso de error, escribe en $error (referencia).
 */
function handle_photo_upload(string $items_dir, ?string $old_filename, ?string &$error): ?string {
    if (empty($_FILES['foto']) || ($_FILES['foto']['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return null; // sin foto nueva
    }
    $f = $_FILES['foto'];
    if ($f['error'] !== UPLOAD_ERR_OK) {
        $error = 'Error al subir la foto (código PHP ' . $f['error'] . ').';
        return null;
    }
    if ($f['size'] > 20 * 1024 * 1024) {
        $error = 'La foto supera los 20 MB.';
        return null;
    }
    $ext = strtolower(pathinfo($f['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, ['jpg','jpeg','png','webp','gif'], true)) {
        $error = 'Formato de foto no permitido (usa jpg, png, webp o gif).';
        return null;
    }

    // Nombre único independiente del id del ítem (así podemos subir foto
    // en el mismo POST que el INSERT sin necesitar el id todavía).
    $name = 'item_' . uniqid('', true) . '.webp';
    $dest = $items_dir . $name;

    $tmp_dest = $items_dir . '_tmp_' . uniqid();
    if (!move_uploaded_file($f['tmp_name'], $tmp_dest)) {
        $error = 'No se pudo mover la foto al servidor (revisa permisos).';
        return null;
    }

    if (convert_to_webp($tmp_dest, $dest, 82, 1400)) {
        @unlink($tmp_dest);
    } else {
        // Fallback: si la conversión falla, guardamos el original tal cual.
        $name = 'item_' . uniqid('', true) . '.' . $ext;
        rename($tmp_dest, $items_dir . $name);
    }

    // Si reemplazamos foto previa, borramos la antigua para no acumular.
    if ($old_filename && is_file($items_dir . $old_filename)) {
        @unlink($items_dir . $old_filename);
    }

    return $name;
}

function redirect_back(string $cat, string $lang, ?int $edit_id = null): void {
    $qs = ['cat' => $cat];
    if ($lang === 'en') $qs['lang'] = 'en';
    if ($edit_id)       $qs['edit'] = $edit_id;
    $qs['ok'] = 1;
    header('Location: carta.php?' . http_build_query($qs));
    exit;
}

function tab_url(string $cat, string $lang, array $extra = []): string {
    $qs = ['cat' => $cat];
    if ($lang === 'en') $qs['lang'] = 'en';
    return 'carta.php?' . http_build_query(array_merge($qs, $extra));
}

// ── Add (solo desde tab ES) ──────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'add') {
    $categoria    = $_POST['categoria'] ?? '';
    $subcategoria = trim($_POST['subcategoria'] ?? '');
    $nombre       = trim($_POST['nombre'] ?? '');
    $descripcion  = trim($_POST['descripcion'] ?? '');
    $ingredientes = trim($_POST['ingredientes'] ?? '');
    $precio_raw   = trim($_POST['precio'] ?? '');
    $precio       = ($precio_raw === '') ? null : (float) str_replace(',', '.', $precio_raw);
    $es_supl      = isset($_POST['es_suplemento']) ? 1 : 0;

    $alergenos_sel = $_POST['alergenos'] ?? [];
    if (!is_array($alergenos_sel)) $alergenos_sel = [];
    $alergenos_sel = array_values(array_intersect($alergenos_sel, array_keys($ALERGENOS)));
    $alergenos_csv = implode(',', $alergenos_sel);

    $visible = isset($_POST['visible']) ? 1 : 0;

    if (!array_key_exists($categoria, $CATEGORIAS)) {
        $error = 'Categoría no válida.';
    } elseif ($nombre === '') {
        $error = 'El nombre es obligatorio.';
    } else {
        $foto = handle_photo_upload($items_dir, null, $error);
        if (!$error) {
            // sort_order: siempre al final de la categoría (global MAX+1).
            // La lógica de "empuje" anterior creaba sort_orders duplicados cuando
            // ítems de otras subcategorías compartían el mismo valor máximo, lo que
            // causaba desorden al renderizar. El usuario puede reordenar con
            // drag-and-drop en el admin; el save normaliza los valores al instante.
            $cat_esc = mysqli_real_escape_string($conexion, $categoria);
            $r = mysqli_query($conexion, "SELECT COALESCE(MAX(sort_order), -1) + 1 AS next FROM carta_items WHERE categoria='$cat_esc'");
            $next_order = $r ? (int) mysqli_fetch_assoc($r)['next'] : 0;

            $cat = mysqli_real_escape_string($conexion, $categoria);
            $sub = mysqli_real_escape_string($conexion, $subcategoria);
            $n   = mysqli_real_escape_string($conexion, $nombre);
            $d   = mysqli_real_escape_string($conexion, $descripcion);
            $ing = mysqli_real_escape_string($conexion, $ingredientes);
            $al  = mysqli_real_escape_string($conexion, $alergenos_csv);
            $ft  = $foto ? "'" . mysqli_real_escape_string($conexion, $foto) . "'" : 'NULL';
            $pr  = $precio === null ? 'NULL' : (float) $precio;
            $sub_sql = $subcategoria === '' ? 'NULL' : "'$sub'";

            mysqli_query($conexion,
                "INSERT INTO carta_items (categoria, subcategoria, nombre, descripcion, ingredientes, precio, es_suplemento, alergenos, foto, visible, sort_order)
                 VALUES ('$cat', $sub_sql, '$n', '$d', '$ing', $pr, $es_supl, '$al', $ft, $visible, $next_order)"
            );
            redirect_back($categoria, 'es');
        }
    }
}

// ── Edit ES (todos los campos no-traducción) ─────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'edit_es') {
    $id           = (int) ($_POST['id'] ?? 0);
    $categoria    = $_POST['categoria'] ?? '';
    $subcategoria = trim($_POST['subcategoria'] ?? '');
    $nombre       = trim($_POST['nombre'] ?? '');
    $descripcion  = trim($_POST['descripcion'] ?? '');
    $ingredientes = trim($_POST['ingredientes'] ?? '');
    $precio_raw   = trim($_POST['precio'] ?? '');
    $precio       = ($precio_raw === '') ? null : (float) str_replace(',', '.', $precio_raw);
    $es_supl      = isset($_POST['es_suplemento']) ? 1 : 0;

    $alergenos_sel = $_POST['alergenos'] ?? [];
    if (!is_array($alergenos_sel)) $alergenos_sel = [];
    $alergenos_sel = array_values(array_intersect($alergenos_sel, array_keys($ALERGENOS)));
    $alergenos_csv = implode(',', $alergenos_sel);

    $visible        = isset($_POST['visible']) ? 1 : 0;
    $delete_photo   = !empty($_POST['delete_photo']);

    if ($id <= 0 || !array_key_exists($categoria, $CATEGORIAS) || $nombre === '') {
        $error = 'Datos inválidos: nombre y categoría son obligatorios.';
    } else {
        $current = mysqli_fetch_assoc(mysqli_query($conexion, "SELECT foto FROM carta_items WHERE id=$id"));
        $old_foto = $current['foto'] ?? null;

        $new_foto = handle_photo_upload($items_dir, $old_foto, $error);

        if (!$error) {
            // Resolver el valor final de foto.
            if ($new_foto !== null) {
                $foto_value = $new_foto;
            } elseif ($delete_photo) {
                if ($old_foto && is_file($items_dir . $old_foto)) @unlink($items_dir . $old_foto);
                $foto_value = null;
            } else {
                $foto_value = $old_foto;
            }

            $cat = mysqli_real_escape_string($conexion, $categoria);
            $sub = mysqli_real_escape_string($conexion, $subcategoria);
            $n   = mysqli_real_escape_string($conexion, $nombre);
            $d   = mysqli_real_escape_string($conexion, $descripcion);
            $ing = mysqli_real_escape_string($conexion, $ingredientes);
            $al  = mysqli_real_escape_string($conexion, $alergenos_csv);
            $ft  = $foto_value === null ? 'NULL' : "'" . mysqli_real_escape_string($conexion, $foto_value) . "'";
            $pr  = $precio === null ? 'NULL' : (float) $precio;
            $sub_sql = $subcategoria === '' ? 'NULL' : "'$sub'";

            mysqli_query($conexion,
                "UPDATE carta_items
                 SET categoria='$cat', subcategoria=$sub_sql, nombre='$n', descripcion='$d', ingredientes='$ing',
                     precio=$pr, es_suplemento=$es_supl, alergenos='$al', foto=$ft, visible=$visible
                 WHERE id=$id"
            );
            redirect_back($categoria, 'es');
        }
    }
}

// ── Edit EN (solo traducciones) ──────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'edit_en') {
    $id              = (int) ($_POST['id'] ?? 0);
    $subcategoria_en = trim($_POST['subcategoria_en'] ?? '');
    $nombre_en       = trim($_POST['nombre_en'] ?? '');
    $descripcion_en  = trim($_POST['descripcion_en'] ?? '');
    $ingredientes_en = trim($_POST['ingredientes_en'] ?? '');

    if ($id > 0) {
        $se = mysqli_real_escape_string($conexion, $subcategoria_en);
        $ne = mysqli_real_escape_string($conexion, $nombre_en);
        $de = mysqli_real_escape_string($conexion, $descripcion_en);
        $ie = mysqli_real_escape_string($conexion, $ingredientes_en);
        mysqli_query($conexion,
            "UPDATE carta_items
             SET subcategoria_en='$se', nombre_en='$ne', descripcion_en='$de', ingredientes_en='$ie'
             WHERE id=$id"
        );

        // Necesitamos la categoría del ítem para volver al tab correcto.
        $row = mysqli_fetch_assoc(mysqli_query($conexion, "SELECT categoria FROM carta_items WHERE id=$id"));
        redirect_back($row['categoria'] ?? $active_cat, 'en');
    }
}

// ── Delete ───────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
    $id = (int) ($_POST['id'] ?? 0);
    if ($id > 0) {
        $row = mysqli_fetch_assoc(mysqli_query($conexion, "SELECT categoria, foto FROM carta_items WHERE id=$id"));
        if ($row) {
            if (!empty($row['foto']) && is_file($items_dir . $row['foto'])) {
                @unlink($items_dir . $row['foto']);
            }
            mysqli_query($conexion, "DELETE FROM carta_items WHERE id=$id");
            redirect_back($row['categoria'], $edit_lang);
        }
    }
}

// ── Normalizar sort_order (elimina duplicados) ───────────
// Reasigna sort_order 0,1,2,... a todos los ítems de la categoría activa,
// respetando el orden actual (sort_order ASC, id ASC) para no alterar la
// secuencia visible. Útil cuando hay sort_orders duplicados por el bug del
// handler de alta anterior.
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'normalize_order') {
    $cat_esc = mysqli_real_escape_string($conexion, $active_cat);
    // Reagrupa por subcategoría (en orden de primera inserción) y dentro de
    // cada grupo ordena por id ASC (orden real de alta), eliminando duplicados.
    $res_ids = mysqli_query($conexion, "
        SELECT ci.id
        FROM carta_items ci
        JOIN (
            SELECT subcategoria, MIN(id) AS min_id
            FROM carta_items
            WHERE categoria = '$cat_esc'
            GROUP BY subcategoria
        ) grp ON ci.subcategoria = grp.subcategoria
        WHERE ci.categoria = '$cat_esc'
        ORDER BY grp.min_id ASC, ci.id ASC
    ");
    if ($res_ids) {
        $i = 0;
        while ($row = mysqli_fetch_assoc($res_ids)) {
            mysqli_query($conexion, "UPDATE carta_items SET sort_order=$i WHERE id=" . (int)$row['id']);
            $i++;
        }
        $success = 'Orden normalizado correctamente (' . $i . ' ítems).';
    }
    redirect_back($active_cat, $edit_lang);
}

// ── Rename sub-grupo en bloque ───────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'rename_sub') {
    $cat     = $_POST['categoria'] ?? '';
    $old_sub = trim($_POST['old_sub'] ?? '');
    $new_sub = trim($_POST['new_sub'] ?? '');
    if (array_key_exists($cat, $CATEGORIAS) && $old_sub !== '' && $new_sub !== '') {
        $c = mysqli_real_escape_string($conexion, $cat);
        $o = mysqli_real_escape_string($conexion, $old_sub);
        $n = mysqli_real_escape_string($conexion, $new_sub);
        mysqli_query($conexion,
            "UPDATE carta_items SET subcategoria='$n' WHERE categoria='$c' AND subcategoria='$o'"
        );
    }
    redirect_back($cat, $edit_lang);
}

// ── Toggle visible ───────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'toggle') {
    $id = (int) ($_POST['id'] ?? 0);
    if ($id > 0) {
        mysqli_query($conexion, "UPDATE carta_items SET visible = 1 - visible WHERE id=$id");
        $row = mysqli_fetch_assoc(mysqli_query($conexion, "SELECT categoria FROM carta_items WHERE id=$id"));
        redirect_back($row['categoria'] ?? $active_cat, $edit_lang);
    }
}

// ── Save order (drag-drop dentro de una categoría) ───────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_order'])) {
    $ids = json_decode($_POST['save_order'], true);
    if (is_array($ids)) {
        foreach ($ids as $order => $id) {
            $id    = (int) $id;
            $order = (int) $order;
            mysqli_query($conexion, "UPDATE carta_items SET sort_order=$order WHERE id=$id");
        }
        $success = 'Orden guardado.';
    }
}

if (isset($_GET['ok'])) $success = 'Cambios guardados correctamente.';

// ── Cargar lista de la categoría activa ──────────────────
$items = [];
$cat_esc = mysqli_real_escape_string($conexion, $active_cat);
$res = mysqli_query($conexion,
    "SELECT * FROM carta_items WHERE categoria='$cat_esc' ORDER BY sort_order ASC, id ASC"
);
if ($res) while ($row = mysqli_fetch_assoc($res)) $items[] = $row;

// ── Cargar ítem en edición ───────────────────────────────
$edit_item = null;
if (isset($_GET['edit'])) {
    $eid = (int) $_GET['edit'];
    $res = mysqli_query($conexion, "SELECT * FROM carta_items WHERE id=$eid");
    if ($res) $edit_item = mysqli_fetch_assoc($res);
}

// Contadores por categoría (para el selector lateral).
$counts = array_fill_keys(array_keys($CATEGORIAS), 0);
$res = mysqli_query($conexion, "SELECT categoria, COUNT(*) AS c FROM carta_items GROUP BY categoria");
if ($res) while ($r = mysqli_fetch_assoc($res)) {
    if (isset($counts[$r['categoria']])) $counts[$r['categoria']] = (int) $r['c'];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>TUOI Admin — Carta</title>
    <link rel="stylesheet" href="../assets/fonts/inter.css">
    <link rel="stylesheet" href="assets/css/admin.css">
    <style>
        .cat-pills { display:flex; flex-wrap:wrap; gap:8px; margin-bottom:18px; }
        .cat-pill { padding:8px 14px; border-radius:999px; background:var(--surface); border:1px solid var(--border); font-size:13px; text-decoration:none; color:var(--text); display:inline-flex; align-items:center; gap:6px; }
        .cat-pill:hover { border-color:var(--primary,#7c3aed); }
        .cat-pill.active { background:var(--primary,#7c3aed); color:#fff; border-color:var(--primary,#7c3aed); }
        .cat-pill .count { font-size:11px; opacity:.75; background:rgba(0,0,0,.08); padding:1px 7px; border-radius:10px; }
        .cat-pill.active .count { background:rgba(255,255,255,.22); }

        .i-list { display:flex; flex-direction:column; gap:12px; }
        .i-item { display:grid; grid-template-columns:60px 1fr auto; align-items:center; gap:14px; padding:14px 16px; background:var(--surface); border:1px solid var(--border); border-radius:10px; cursor:grab; }
        .i-item:active { cursor:grabbing; }
        .i-item.dragging { opacity:.4; }
        .i-item.drag-over { border-color:var(--primary,#7c3aed); }
        .i-item.inactive { opacity:.55; }
        .i-thumb { width:60px; height:60px; border-radius:8px; object-fit:cover; background:#f3f4f6; }
        .i-thumb-empty { width:60px; height:60px; border-radius:8px; background:#f3f4f6; display:flex; align-items:center; justify-content:center; font-size:22px; opacity:.5; }
        .i-body { min-width:0; }
        .i-name { font-size:14px; font-weight:600; color:var(--text); margin-bottom:2px; display:flex; gap:8px; align-items:baseline; flex-wrap:wrap; }
        .i-price { font-size:13px; color:var(--primary,#7c3aed); font-weight:600; }
        .i-desc { font-size:12px; color:var(--muted); white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
        .i-tags { display:flex; gap:5px; flex-wrap:wrap; margin-top:4px; }
        .i-tag { font-size:10px; padding:2px 7px; border-radius:10px; background:#eef; color:#446; }
        .i-actions { display:flex; gap:6px; flex-shrink:0; }
        .pill { font-size:11px; padding:2px 8px; border-radius:20px; font-weight:600; }
        .pill--on { background:#dcfce7; color:#166534; }
        .pill--off { background:#fee2e2; color:#991b1b; }
        .pill--ok { background:#dbeafe; color:#1e40af; }
        .pill--no { background:#f3f4f6; color:#6b7280; }
        .alergenos-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(150px,1fr)); gap:8px; }
        .alergeno-chk { display:flex; align-items:center; gap:6px; padding:8px 10px; background:#f9fafb; border:1px solid var(--border); border-radius:8px; cursor:pointer; font-size:13px; }
        .alergeno-chk input { margin:0; }
        .alergeno-chk:has(input:checked) { background:#eef2ff; border-color:var(--primary,#7c3aed); }
        .empty-state { text-align:center; padding:32px; color:var(--muted); font-style:italic; background:var(--surface); border:1px dashed var(--border); border-radius:10px; }
        .photo-preview { display:flex; align-items:center; gap:12px; margin-top:6px; }
        .photo-preview img { width:80px; height:80px; object-fit:cover; border-radius:8px; border:1px solid var(--border); }
        .ref-block { background:#f9fafb; border:1px solid var(--border); border-left:3px solid var(--muted); border-radius:8px; padding:12px 14px; margin-bottom:14px; }
        .ref-label { font-size:11px; text-transform:uppercase; letter-spacing:.04em; color:var(--muted); font-weight:600; margin-bottom:6px; }

        /* Sub-grupo header dentro de la lista */
        .i-subgroup-header {
            display: flex; align-items: center; justify-content: space-between;
            padding: 8px 12px 6px;
            margin-top: 8px;
            background: #f0f4ff;
            border: 1px solid #c7d2fe;
            border-radius: 8px;
            gap: 10px;
        }
        .i-subgroup-header:first-child { margin-top: 0; }
        .i-subgroup-title {
            font-size: 12px; font-weight: 700; text-transform: uppercase;
            letter-spacing: .06em; color: #3730a3;
        }
        /* Formulario inline de rename (oculto por defecto) */
        .rename-inline-form {
            display: none; align-items: center; gap: 6px; flex: 1;
        }
        .rename-inline-form.visible { display: flex; }
        .rename-inline-form input {
            flex: 1; padding: 4px 8px; font-size: 13px;
            border: 1px solid #c7d2fe; border-radius: 6px;
        }
    </style>
</head>
<body>
<div class="admin-layout">
    <?php include 'partials/sidebar.php'; ?>

    <div class="main-content">
        <div class="topbar">
            <div>
                <div class="topbar-title">Carta</div>
                <div class="topbar-sub">Gestiona los ítems de la carta. Arrastra para reordenar dentro de cada categoría.</div>
            </div>
            <div class="topbar-actions">
                <a href="../pages/carta/" target="_blank" class="btn btn-secondary btn-sm">🌐 Ver carta</a>
            </div>
        </div>

        <div class="content-area">
            <?php include 'partials/toast.php'; ?>

            <!-- Tabs idioma -->
            <div class="lang-tabs">
                <a href="<?= htmlspecialchars(tab_url($active_cat, 'es')) ?>" class="lang-tab <?= $edit_lang === 'es' ? 'active' : '' ?>">
                    <span class="flag">🇪🇸</span> Español
                </a>
                <a href="<?= htmlspecialchars(tab_url($active_cat, 'en')) ?>" class="lang-tab <?= $edit_lang === 'en' ? 'active' : '' ?>">
                    <span class="flag">🇬🇧</span> English
                    <?php if ($edit_lang === 'en'): ?>
                        <span style="font-size:11px;color:var(--muted);margin-left:6px;">
                            (solo traducciones · vacío = se usa el español)
                        </span>
                    <?php endif; ?>
                </a>
            </div>

            <!-- Selector de categoría -->
            <div class="cat-pills">
                <?php foreach ($CATEGORIAS as $slug => $label): ?>
                    <a href="<?= htmlspecialchars(tab_url($slug, $edit_lang)) ?>"
                       class="cat-pill <?= $slug === $active_cat ? 'active' : '' ?>">
                        <?= htmlspecialchars($label) ?>
                        <span class="count"><?= $counts[$slug] ?></span>
                    </a>
                <?php endforeach; ?>
            </div>

            <?php if ($edit_lang === 'es'): ?>
            <!-- ─────────── PESTAÑA ESPAÑOL ─────────── -->

            <?php if (!$edit_item): ?>
            <!-- Formulario de alta -->
            <div class="card" style="margin-bottom:20px;">
                <div class="card-header">
                    <div class="card-title"><span>➕</span> Añadir ítem a "<?= htmlspecialchars($CATEGORIAS[$active_cat]) ?>"</div>
                </div>
                <form method="post" enctype="multipart/form-data">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="add">
                    <input type="hidden" name="categoria" value="<?= htmlspecialchars($active_cat) ?>">

                    <div class="form-group">
                        <label class="form-label">Sub-grupo</label>
                        <?php
                        // Sugerir subcategorías ya existentes en esta categoría para evitar typos.
                        $existing_subs = [];
                        $rs = mysqli_query($conexion, "SELECT DISTINCT subcategoria FROM carta_items WHERE categoria='" . mysqli_real_escape_string($conexion, $active_cat) . "' AND subcategoria IS NOT NULL AND subcategoria <> '' ORDER BY subcategoria");
                        if ($rs) while ($r = mysqli_fetch_assoc($rs)) $existing_subs[] = $r['subcategoria'];
                        ?>
                        <input type="text" name="subcategoria" class="form-control" list="sub-suggest-add" maxlength="100" placeholder="Ej. Tostadas, Extras, Café… (opcional)">
                        <datalist id="sub-suggest-add">
                            <?php foreach ($existing_subs as $s): ?><option value="<?= htmlspecialchars($s) ?>"><?php endforeach; ?>
                        </datalist>
                        <small style="color:var(--muted);font-size:12px;">Agrupa varios ítems bajo la misma etiqueta (aparecerá vertical en el lateral).</small>
                    </div>

                    <div class="form-grid-2">
                        <div class="form-group">
                            <label class="form-label">Nombre *</label>
                            <input type="text" name="nombre" class="form-control" required maxlength="255" placeholder="Ej. Tostada de aguacate">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Precio (€)</label>
                            <input type="text" name="precio" class="form-control" placeholder="Ej. 7,50" inputmode="decimal">
                            <label style="display:flex;align-items:center;gap:6px;font-size:12px;color:var(--muted);margin-top:4px;">
                                <input type="checkbox" name="es_suplemento" value="1">
                                Es suplemento (se muestra como "+2,00 €")
                            </label>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Descripción</label>
                        <textarea name="descripcion" rows="2" class="form-control" placeholder="Frase corta que aparece bajo el nombre"></textarea>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Ingredientes / nota</label>
                        <textarea name="ingredientes" rows="2" class="form-control" placeholder="Lista de ingredientes o nota destacada"></textarea>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Etiquetas / alérgenos</label>
                        <div class="alergenos-grid">
                            <?php foreach ($ALERGENOS as $key => $meta): ?>
                                <label class="alergeno-chk">
                                    <input type="checkbox" name="alergenos[]" value="<?= htmlspecialchars($key) ?>">
                                    <span><?= $meta['icon'] ?></span>
                                    <span><?= htmlspecialchars($meta['label']) ?></span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Foto (opcional)</label>
                        <input type="file" name="foto" accept="image/*" class="form-control">
                        <small style="color:var(--muted);font-size:12px;">Se convertirá a WebP automáticamente. Máx. 20 MB.</small>
                    </div>

                    <div class="form-group">
                        <label style="display:flex;align-items:center;gap:8px;font-weight:500;">
                            <input type="checkbox" name="visible" value="1" checked>
                            Visible en la web
                        </label>
                    </div>

                    <button type="submit" class="btn btn-primary">+ Añadir ítem</button>
                </form>
            </div>
            <?php endif; ?>

            <!-- Formulario de edición ES -->
            <?php if ($edit_item): ?>
            <div class="card" style="margin-bottom:20px;">
                <div class="card-header">
                    <div class="card-title"><span>✏️</span> Editar #<?= (int)$edit_item['id'] ?>: <?= htmlspecialchars($edit_item['nombre']) ?></div>
                </div>
                <form method="post" enctype="multipart/form-data">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="edit_es">
                    <input type="hidden" name="id" value="<?= (int)$edit_item['id'] ?>">

                    <div class="form-grid-2">
                        <div class="form-group">
                            <label class="form-label">Nombre *</label>
                            <input type="text" name="nombre" class="form-control" required maxlength="255" value="<?= htmlspecialchars($edit_item['nombre']) ?>">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Precio (€)</label>
                            <input type="text" name="precio" class="form-control" inputmode="decimal" value="<?= $edit_item['precio'] !== null ? htmlspecialchars(number_format((float)$edit_item['precio'], 2, ',', '')) : '' ?>">
                            <label style="display:flex;align-items:center;gap:6px;font-size:12px;color:var(--muted);margin-top:4px;">
                                <input type="checkbox" name="es_suplemento" value="1" <?= !empty($edit_item['es_suplemento']) ? 'checked' : '' ?>>
                                Es suplemento (se muestra como "+2,00 €")
                            </label>
                        </div>
                    </div>

                    <div class="form-grid-2">
                        <div class="form-group">
                            <label class="form-label">Categoría</label>
                            <select name="categoria" class="form-control" style="border-color:var(--primary,#7c3aed);">
                                <?php foreach ($CATEGORIAS as $slug => $label): ?>
                                    <option value="<?= htmlspecialchars($slug) ?>" <?= $slug === $edit_item['categoria'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($label) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <small style="color:var(--primary,#7c3aed);font-size:12px;font-weight:500;">
                                ↑ Cambia aquí para mover el ítem a otra categoría
                            </small>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Sub-grupo</label>
                            <?php
                            $existing_subs_e = [];
                            $rs = mysqli_query($conexion, "SELECT DISTINCT subcategoria FROM carta_items WHERE categoria='" . mysqli_real_escape_string($conexion, $edit_item['categoria']) . "' AND subcategoria IS NOT NULL AND subcategoria <> '' ORDER BY subcategoria");
                            if ($rs) while ($r = mysqli_fetch_assoc($rs)) $existing_subs_e[] = $r['subcategoria'];
                            ?>
                            <input type="text" name="subcategoria" class="form-control" list="sub-suggest-edit" maxlength="100" value="<?= htmlspecialchars($edit_item['subcategoria'] ?? '') ?>" placeholder="Opcional">
                            <datalist id="sub-suggest-edit">
                                <?php foreach ($existing_subs_e as $s): ?><option value="<?= htmlspecialchars($s) ?>"><?php endforeach; ?>
                            </datalist>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Descripción</label>
                        <textarea name="descripcion" rows="2" class="form-control"><?= htmlspecialchars($edit_item['descripcion'] ?? '') ?></textarea>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Ingredientes / nota</label>
                        <textarea name="ingredientes" rows="2" class="form-control"><?= htmlspecialchars($edit_item['ingredientes'] ?? '') ?></textarea>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Etiquetas / alérgenos</label>
                        <?php $selected = array_filter(explode(',', $edit_item['alergenos'] ?? '')); ?>
                        <div class="alergenos-grid">
                            <?php foreach ($ALERGENOS as $key => $meta): ?>
                                <label class="alergeno-chk">
                                    <input type="checkbox" name="alergenos[]" value="<?= htmlspecialchars($key) ?>" <?= in_array($key, $selected, true) ? 'checked' : '' ?>>
                                    <span><?= $meta['icon'] ?></span>
                                    <span><?= htmlspecialchars($meta['label']) ?></span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Foto</label>
                        <?php if (!empty($edit_item['foto'])): ?>
                            <div class="photo-preview">
                                <img src="<?= htmlspecialchars($items_url . $edit_item['foto']) ?>" alt="">
                                <label style="display:flex;align-items:center;gap:6px;font-size:13px;color:#c0392b;cursor:pointer;">
                                    <input type="checkbox" name="delete_photo" value="1">
                                    Eliminar foto actual
                                </label>
                            </div>
                        <?php endif; ?>
                        <input type="file" name="foto" accept="image/*" class="form-control" style="margin-top:6px;">
                        <small style="color:var(--muted);font-size:12px;">Subir una nueva sustituirá la anterior.</small>
                    </div>

                    <div class="form-group">
                        <label style="display:flex;align-items:center;gap:8px;font-weight:500;">
                            <input type="checkbox" name="visible" value="1" <?= $edit_item['visible'] ? 'checked' : '' ?>>
                            Visible en la web
                        </label>
                    </div>

                    <div style="display:flex;gap:8px;">
                        <button type="submit" class="btn btn-primary">💾 Guardar</button>
                        <a href="<?= htmlspecialchars(tab_url($active_cat, 'es')) ?>" class="btn btn-secondary">Cancelar</a>
                    </div>
                </form>
            </div>
            <?php endif; ?>

            <?php else: ?>
            <!-- ─────────── PESTAÑA INGLÉS ─────────── -->

            <?php if ($edit_item): ?>
            <div class="card" style="margin-bottom:20px;">
                <div class="card-header">
                    <div class="card-title"><span>🇬🇧</span> Traducir #<?= (int)$edit_item['id'] ?></div>
                </div>

                <div class="ref-block">
                    <div class="ref-label">Original en español (referencia)</div>
                    <div style="font-weight:600;"><?= htmlspecialchars($edit_item['nombre']) ?></div>
                    <?php if (!empty($edit_item['descripcion'])): ?>
                        <div style="font-size:13px;color:var(--muted);margin-top:4px;"><?= nl2br(htmlspecialchars($edit_item['descripcion'])) ?></div>
                    <?php endif; ?>
                    <?php if (!empty($edit_item['ingredientes'])): ?>
                        <div style="font-size:12px;color:var(--muted);margin-top:6px;"><em>Ingredientes:</em> <?= nl2br(htmlspecialchars($edit_item['ingredientes'])) ?></div>
                    <?php endif; ?>
                </div>

                <form method="post">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="edit_en">
                    <input type="hidden" name="id" value="<?= (int)$edit_item['id'] ?>">

                    <?php if (!empty($edit_item['subcategoria'])): ?>
                    <div class="form-group">
                        <label class="form-label">Sub-grupo (English)</label>
                        <input type="text" name="subcategoria_en" class="form-control" maxlength="100" value="<?= htmlspecialchars($edit_item['subcategoria_en'] ?? '') ?>" placeholder="e.g. Toasts, Extras">
                        <small style="color:var(--muted);font-size:12px;">ES: <em><?= htmlspecialchars($edit_item['subcategoria']) ?></em></small>
                    </div>
                    <?php endif; ?>

                    <div class="form-group">
                        <label class="form-label">Nombre (English)</label>
                        <input type="text" name="nombre_en" class="form-control" maxlength="255" value="<?= htmlspecialchars($edit_item['nombre_en'] ?? '') ?>" placeholder="e.g. Avocado toast">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Description (English)</label>
                        <textarea name="descripcion_en" rows="2" class="form-control"><?= htmlspecialchars($edit_item['descripcion_en'] ?? '') ?></textarea>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Ingredients / note (English)</label>
                        <textarea name="ingredientes_en" rows="2" class="form-control"><?= htmlspecialchars($edit_item['ingredientes_en'] ?? '') ?></textarea>
                    </div>

                    <div style="display:flex;gap:8px;">
                        <button type="submit" class="btn btn-primary">💾 Guardar traducción</button>
                        <a href="<?= htmlspecialchars(tab_url($active_cat, 'en')) ?>" class="btn btn-secondary">Cancelar</a>
                    </div>
                </form>
            </div>
            <?php else: ?>
            <div class="card" style="margin-bottom:20px;background:#f9fafb;">
                <p style="margin:0;color:var(--muted);font-size:14px;">
                    💡 Selecciona un ítem de la lista para añadir o editar su traducción al inglés.
                    Para crear o editar campos principales, cambia a la pestaña
                    <a href="<?= htmlspecialchars(tab_url($active_cat, 'es')) ?>" style="color:var(--primary);font-weight:600;">🇪🇸 Español</a>.
                </p>
            </div>
            <?php endif; ?>

            <?php endif; ?>

            <!-- ── Lista de ítems de la categoría ── -->
            <?php
            // Agrupar ítems por subcategoría manteniendo el orden de sort_order.
            // La clave especial '' agrupa los ítems sin subcategoría.
            $grupos_list = [];
            foreach ($items as $it) {
                $sub = trim($it['subcategoria'] ?? '');
                $grupos_list[$sub][] = $it;
            }
            ?>
            <div class="card">
                <div class="card-header">
                    <div class="card-title">
                        <span>🍽️</span> Ítems de "<?= htmlspecialchars($CATEGORIAS[$active_cat]) ?>" (<?= count($items) ?>)
                    </div>
                    <div style="display:flex;align-items:center;gap:10px;">
                        <?php
                        // Detectar si hay sort_orders duplicados en esta categoría.
                        $sort_orders = array_column($items, 'sort_order');
                        $has_dupes   = count($sort_orders) !== count(array_unique($sort_orders));
                        ?>
                        <?php if ($has_dupes): ?>
                        <form method="post" style="display:inline;">
                            <?= csrf_field() ?>
                            <input type="hidden" name="action" value="normalize_order">
                            <button type="submit" class="btn btn-sm"
                                    style="background:#fef3c7;color:#92400e;border:1px solid #fcd34d;"
                                    title="Hay ítems con el mismo número de orden, lo que puede causar desorden al mostrarlos. Haz clic para repararlo.">
                                ⚠️ Reparar orden
                            </button>
                        </form>
                        <?php endif; ?>
                        <span class="topbar-sub" style="font-size:12px;">Arrastra para reordenar</span>
                    </div>
                </div>

                <?php if (empty($items)): ?>
                <div class="empty-state">Aún no hay ítems en esta categoría. Añade el primero desde la pestaña Español.</div>
                <?php else: ?>
                <div class="i-list" id="i-list">
                <?php foreach ($grupos_list as $sub_nombre => $sub_items): ?>

                    <?php if ($sub_nombre !== ''): ?>
                    <!-- Cabecera de sub-grupo con rename inline -->
                    <div class="i-subgroup-header" data-sub="<?= htmlspecialchars($sub_nombre) ?>">
                        <span class="i-subgroup-title" id="sub-label-<?= htmlspecialchars(md5($sub_nombre)) ?>">
                            <?= htmlspecialchars($sub_nombre) ?>
                            <span style="font-size:11px;color:var(--muted);font-weight:400;margin-left:4px;">(<?= count($sub_items) ?> ítems)</span>
                        </span>
                        <?php if ($edit_lang === 'es'): ?>
                        <button type="button"
                                class="btn btn-secondary btn-sm rename-sub-btn"
                                title="Renombrar sub-grupo"
                                data-sub="<?= htmlspecialchars($sub_nombre) ?>"
                                data-cat="<?= htmlspecialchars($active_cat) ?>"
                                data-csrf="<?= csrf_token() ?>">
                            ✏️ Renombrar
                        </button>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>

                    <?php foreach ($sub_items as $it):
                        $has_en = !empty($it['nombre_en']);
                        $display_name = ($edit_lang === 'en' && $has_en) ? $it['nombre_en'] : $it['nombre'];
                        $display_desc = ($edit_lang === 'en' && !empty($it['descripcion_en'])) ? $it['descripcion_en'] : ($it['descripcion'] ?? '');
                        $tags = array_filter(explode(',', $it['alergenos'] ?? ''));
                    ?>
                    <div class="i-item <?= $it['visible'] ? '' : 'inactive' ?>" draggable="true" data-id="<?= (int)$it['id'] ?>">
                        <?php if (!empty($it['foto'])): ?>
                            <img class="i-thumb" src="<?= htmlspecialchars($items_url . $it['foto']) ?>" alt="">
                        <?php else: ?>
                            <div class="i-thumb-empty">🍽️</div>
                        <?php endif; ?>

                        <div class="i-body">
                            <div class="i-name">
                                <span><?= htmlspecialchars($display_name) ?></span>
                                <?php if ($it['precio'] !== null): ?>
                                    <span class="i-price"><?= !empty($it['es_suplemento']) ? '+' : '' ?><?= number_format((float)$it['precio'], 2, ',', '') ?> €</span>
                                <?php endif; ?>
                            </div>
                            <?php if ($display_desc): ?>
                                <div class="i-desc"><?= htmlspecialchars($display_desc) ?></div>
                            <?php endif; ?>
                            <?php if (!empty($tags)): ?>
                                <div class="i-tags">
                                    <?php foreach ($tags as $t): if (!isset($ALERGENOS[$t])) continue; ?>
                                        <span class="i-tag"><?= $ALERGENOS[$t]['icon'] ?> <?= htmlspecialchars($ALERGENOS[$t]['label']) ?></span>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="i-actions">
                            <?php if ($edit_lang === 'en'): ?>
                                <span class="pill <?= $has_en ? 'pill--ok' : 'pill--no' ?>">
                                    <?= $has_en ? 'Traducido ✓' : 'Sin traducir' ?>
                                </span>
                                <a href="<?= htmlspecialchars(tab_url($active_cat, 'en', ['edit' => (int)$it['id']])) ?>" class="btn btn-secondary btn-sm">
                                    🇬🇧 <?= $has_en ? 'Editar' : 'Traducir' ?>
                                </a>
                            <?php else: ?>
                                <span class="pill <?= $has_en ? 'pill--ok' : 'pill--no' ?>" title="<?= $has_en ? 'Traducción al inglés disponible' : 'Sin traducción' ?>">
                                    EN <?= $has_en ? '✓' : '—' ?>
                                </span>
                                <span class="pill <?= $it['visible'] ? 'pill--on' : 'pill--off' ?>">
                                    <?= $it['visible'] ? 'Visible' : 'Oculto' ?>
                                </span>
                                <form method="post" style="display:inline;">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="action" value="toggle">
                                    <input type="hidden" name="id" value="<?= (int)$it['id'] ?>">
                                    <button type="submit" class="btn btn-secondary btn-sm" title="Alternar visibilidad">
                                        <?= $it['visible'] ? '🙈' : '👁️' ?>
                                    </button>
                                </form>
                                <a href="<?= htmlspecialchars(tab_url($active_cat, 'es', ['edit' => (int)$it['id']])) ?>" class="btn btn-secondary btn-sm" title="Editar">✏️</a>
                                <form method="post" style="display:inline;" onsubmit="return confirm('¿Eliminar este ítem? Se borrará también su foto.');">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?= (int)$it['id'] ?>">
                                    <button type="submit" class="btn btn-secondary btn-sm" style="color:#c0392b;" title="Eliminar">🗑️</button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>

                <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>

        </div>
    </div>
</div>

<script>
(function () {
    // ── Drag-and-drop reorder ────────────────────────────
    const list = document.getElementById('i-list');
    if (!list) return;
    let dragEl = null;

    list.addEventListener('dragstart', e => {
        const it = e.target.closest('.i-item');
        if (!it) return;
        dragEl = it;
        it.classList.add('dragging');
    });
    list.addEventListener('dragend', () => {
        document.querySelectorAll('.i-item').forEach(el => el.classList.remove('dragging', 'drag-over'));
        saveOrder();
    });
    list.addEventListener('dragover', e => {
        e.preventDefault();
        const tgt = e.target.closest('.i-item');
        if (!tgt || tgt === dragEl) return;
        const rect = tgt.getBoundingClientRect();
        const after = (e.clientY - rect.top) > rect.height / 2;
        list.insertBefore(dragEl, after ? tgt.nextSibling : tgt);
    });

    function saveOrder() {
        const ids = [...list.querySelectorAll('.i-item')].map(el => el.dataset.id);
        const fd = new FormData();
        fd.append('save_order', JSON.stringify(ids));
        fd.append('csrf_token', <?= json_encode(csrf_token()) ?>);
        fetch('carta.php', { method: 'POST', body: fd });
    }

    // ── Rename sub-grupo inline ──────────────────────────
    document.querySelectorAll('.rename-sub-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const header  = btn.closest('.i-subgroup-header');
            const oldSub  = btn.dataset.sub;
            const cat     = btn.dataset.cat;
            const csrf    = btn.dataset.csrf;
            const titleEl = header.querySelector('.i-subgroup-title');

            // Insertar formulario inline si aún no existe
            let form = header.querySelector('.rename-inline-form');
            if (!form) {
                form = document.createElement('form');
                form.method = 'post';
                form.className = 'rename-inline-form';
                form.innerHTML = `
                    <input type="hidden" name="action"   value="rename_sub">
                    <input type="hidden" name="categoria" value="${cat}">
                    <input type="hidden" name="old_sub"   value="${oldSub}">
                    <input type="hidden" name="csrf_token" value="${csrf}">
                    <input type="text"   name="new_sub"   value="${oldSub}" maxlength="100" required>
                    <button type="submit" class="btn btn-primary btn-sm">✔ Guardar</button>
                    <button type="button" class="btn btn-secondary btn-sm cancel-rename">✕</button>
                `;
                header.appendChild(form);
                form.querySelector('.cancel-rename').addEventListener('click', () => {
                    form.classList.remove('visible');
                    titleEl.style.display = '';
                    btn.style.display = '';
                });
            }

            // Mostrar form, ocultar título y botón
            titleEl.style.display = 'none';
            btn.style.display = 'none';
            form.classList.add('visible');
            form.querySelector('input[name="new_sub"]').select();
        });
    });
})();
</script>
</body>
</html>
