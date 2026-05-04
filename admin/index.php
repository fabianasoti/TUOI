<?php
require_once 'config.php';
require_once dirname(__DIR__) . '/config/content_helper.php';

// Count content rows
$content_count = 0;
$res = mysqli_query($conexion, "SELECT COUNT(*) AS c FROM site_content");
if ($res) $content_count = (int)mysqli_fetch_assoc($res)['c'];

// Count images per section (quick tally)
$base = dirname(__DIR__) . '/assets/img/';
$img_sections = [
    'carteles'               => $base . 'carteles',
    'quienes_somos'          => $base . 'quienes_somos',
    'carta/desayunos'        => $base . 'carta/desayunos',
    'carta/toque-salado'     => $base . 'carta/toque-salado',
    'carta/momento-dulce'    => $base . 'carta/momento-dulce',
    'carta/bebidas'          => $base . 'carta/bebidas',
    'carta/superalimentos'   => $base . 'carta/superalimentos',
    'carta/desayunos-en'     => $base . 'carta/desayunos-en',
    'carta/toque-salado-en'  => $base . 'carta/toque-salado-en',
    'carta/momento-dulce-en' => $base . 'carta/momento-dulce-en',
    'carta/bebidas-en'       => $base . 'carta/bebidas-en',
    'carta/superalimentos-en'=> $base . 'carta/superalimentos-en',
];

$total_images = 0;
foreach ($img_sections as $dir) {
    if (is_dir($dir)) {
        $files = glob($dir . '/*.{jpg,jpeg,png,webp,pdf}', GLOB_BRACE);
        $total_images += count($files ?: []);
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>TUOI Admin — Dashboard</title>
    <link rel="stylesheet" href="../assets/fonts/inter.css">
    <link rel="stylesheet" href="assets/css/admin.css">
</head>
<body>
<div class="admin-layout">

    <?php include 'partials/sidebar.php'; ?>

    <div class="main-content">
        <div class="topbar">
            <div>
                <div class="topbar-title">Dashboard</div>
                <div class="topbar-sub">Bienvenido, <?= htmlspecialchars($_SESSION['admin_user'] ?? 'admin') ?></div>
            </div>
            <div class="topbar-actions">
                <a href="../index.php" target="_blank" class="btn btn-secondary btn-sm">🌐 Ver sitio</a>
            </div>
        </div>

        <div class="content-area">

            <!-- Stats -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon morado">📝</div>
                    <div>
                        <div class="stat-value"><?= $content_count ?></div>
                        <div class="stat-label">Bloques de texto</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon verde">🖼️</div>
                    <div>
                        <div class="stat-value"><?= $total_images ?></div>
                        <div class="stat-label">Imágenes totales</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon naranja">📁</div>
                    <div>
                        <div class="stat-value"><?= count($img_sections) ?></div>
                        <div class="stat-label">Secciones de imágenes</div>
                    </div>
                </div>
            </div>

            <!-- Quick actions -->
            <div class="card">
                <div class="card-header">
                    <div class="card-title">Accesos rápidos</div>
                </div>
                <div class="quick-grid">
                    <a href="contenido.php" class="quick-card">
                        <span class="quick-icon">✏️</span>
                        <div>
                            <div class="quick-label">Editar contenido</div>
                            <div class="quick-desc">Textos y títulos del sitio</div>
                        </div>
                    </a>
                    <a href="imagenes.php" class="quick-card">
                        <span class="quick-icon">🖼️</span>
                        <div>
                            <div class="quick-label">Gestionar imágenes</div>
                            <div class="quick-desc">Subir, ver y eliminar imágenes</div>
                        </div>
                    </a>
                    <a href="../index.php" target="_blank" class="quick-card">
                        <span class="quick-icon">🌐</span>
                        <div>
                            <div class="quick-label">Ver el sitio</div>
                            <div class="quick-desc">Abre el sitio público</div>
                        </div>
                    </a>
                    <a href="../pages/carta/" target="_blank" class="quick-card">
                        <span class="quick-icon">🍽️</span>
                        <div>
                            <div class="quick-label">Ver la carta</div>
                            <div class="quick-desc">Menú de productos</div>
                        </div>
                    </a>
                </div>
            </div>

            <!-- Image summary by section -->
            <div class="card">
                <div class="card-header">
                    <div class="card-title">Imágenes por sección</div>
                    <a href="imagenes.php" class="btn btn-primary btn-sm">Gestionar →</a>
                </div>
                <table style="width:100%;border-collapse:collapse;font-size:14px;">
                    <thead>
                        <tr style="border-bottom:2px solid var(--border);">
                            <th style="text-align:left;padding:8px 0;color:var(--muted);font-weight:600;">Sección</th>
                            <th style="text-align:right;padding:8px 0;color:var(--muted);font-weight:600;">Imágenes</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($img_sections as $label => $dir): ?>
                        <?php
                        $files = is_dir($dir) ? (glob($dir . '/*.{jpg,jpeg,png,webp,pdf}', GLOB_BRACE) ?: []) : [];
                        $count = count($files);
                        ?>
                        <tr style="border-bottom:1px solid var(--border);">
                            <td style="padding:10px 0;"><?= htmlspecialchars($label) ?></td>
                            <td style="padding:10px 0;text-align:right;">
                                <span class="badge-count"><?= $count ?></span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

        </div>
    </div>
</div>
</body>
</html>
