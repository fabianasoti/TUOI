<?php
$base         = '../../';
$current_page = 'carta';
$page_title   = 'Carta | TUOI';

require $base . 'config/conexion.php';
require $base . 'config/content_helper.php';
require $base . 'includes/header.php';

// $lang is set by header.php → lang.php
global $carta_info;
$categorias = [
    'desayunos'      => t_raw('cat_desayunos'),
    'toque-salado'   => t_raw('cat_toque'),
    'momento-dulce'  => t_raw('cat_dulce'),
    'bebidas'        => t_raw('cat_bebidas'),
    'superalimentos' => t_raw('cat_super'),
];

// Cargar imágenes respetando orden y idioma (EN dirs con fallback a ES)
$all_images = [];
foreach ($categorias as $slug => $label) {
    $img_slug = ($lang === 'en') ? $slug . '-en' : $slug;
    $dir      = __DIR__ . '/../../assets/img/carta/' . $img_slug . '/';
    $section  = 'carta/' . $img_slug;

    // Fall back to ES if EN dir is empty or missing
    if ($lang === 'en' && (!is_dir($dir) || empty(glob($dir . '*.{webp,jpg,jpeg,png,pdf}', GLOB_BRACE)))) {
        $img_slug = $slug;
        $dir      = __DIR__ . '/../../assets/img/carta/' . $slug . '/';
        $section  = 'carta/' . $slug;
    }

    $found = load_ordered_images($conexion, $section, $dir, '*.{webp,jpg,jpeg,png,pdf}');
    foreach ($found as $filepath) {
        $ext = strtolower(pathinfo($filepath, PATHINFO_EXTENSION));
        $all_images[] = [
            'src'       => $base . 'assets/img/carta/' . $img_slug . '/' . basename($filepath),
            'categoria' => $slug,
            'label'     => $label,
            'tipo'      => ($ext === 'pdf') ? 'pdf' : 'img',
        ];
    }
}

// Detectar categoría activa via GET param (?cat=desayunos)
// Sin param → null = mostrar todo, sin ningún tab activo
$cat_activa = (isset($_GET['cat']) && array_key_exists($_GET['cat'], $categorias))
    ? $_GET['cat']
    : null;
?>

<main>

    <!-- Hero de página -->
    <section class="page-hero">
        <span class="section-label"><?= t('carta_page_label') ?></span>
        <h1><?= t('carta_page_title') ?></h1>
        <p><?= t('carta_page_sub') ?></p>
    </section>

    <!-- Subnav / filtros de categoría -->
    <div class="carta-filtros-wrap">
        <div class="carta-filtros" role="tablist" aria-label="Filtrar por categoría">
            <?php foreach ($categorias as $slug => $label): ?>
                <button
                    class="filtro-btn <?= $cat_activa === $slug ? 'active' : '' ?>"
                    data-filtro="<?= $slug ?>"
                    role="tab"
                    aria-selected="<?= $cat_activa === $slug ? 'true' : 'false' ?>">
                    <?= htmlspecialchars($label) ?>
                </button>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Lista de imágenes -->
    <div class="carta-lista-wrap">

        <?php if (!empty($all_images)): ?>
            <div class="carta-lista" id="carta-lista">
                <?php foreach ($all_images as $item): ?>
                    <div class="carta-item"
                         data-categoria="<?= $item['categoria'] ?>"
                         <?= ($cat_activa !== null && $cat_activa !== $item['categoria']) ? 'hidden' : '' ?>>

                        <?php if ($item['tipo'] === 'pdf'): ?>
                            <div class="carta-pdf-wrap">
                                <object
                                    data="<?= htmlspecialchars($item['src']) ?>"
                                    type="application/pdf"
                                    class="carta-pdf">
                                    <div class="carta-pdf-fallback">
                                        <p><?= t('pdf_no_show') ?></p>
                                        <a href="<?= htmlspecialchars($item['src']) ?>"
                                           target="_blank" rel="noopener" class="btn-primary">
                                            <?= t('open_pdf') ?>
                                        </a>
                                    </div>
                                </object>
                            </div>
                        <?php else: ?>
                            <img
                                src="<?= htmlspecialchars($item['src']) ?>"
                                alt="<?= htmlspecialchars($item['label']) ?>"
                                loading="lazy">
                        <?php endif; ?>

                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

    </div>

</main>

<script>
(function () {
    const filtros = document.querySelectorAll('.filtro-btn');
    const items   = document.querySelectorAll('.carta-item');
    if (!filtros.length) return;

    function showAll() {
        items.forEach(item => item.hidden = false);
        filtros.forEach(f => {
            f.classList.remove('active');
            f.setAttribute('aria-selected', 'false');
        });
        const url = new URL(window.location);
        url.searchParams.delete('cat');
        history.replaceState(null, '', url);
    }

    filtros.forEach(btn => {
        btn.addEventListener('click', () => {
            const yaActivo = btn.classList.contains('active');

            // Si el tab ya estaba activo → reset: mostrar todo (como si pulsaras "Carta")
            if (yaActivo) {
                showAll();
                return;
            }

            // Activar este tab
            filtros.forEach(f => {
                f.classList.remove('active');
                f.setAttribute('aria-selected', 'false');
            });
            btn.classList.add('active');
            btn.setAttribute('aria-selected', 'true');

            // Filtrar
            const filtro = btn.dataset.filtro;
            let visible = 0;
            items.forEach(item => {
                const match = item.dataset.categoria === filtro;
                item.hidden = !match;
                if (match) visible++;
            });

            // Actualizar URL sin recargar
            const url = new URL(window.location);
            url.searchParams.set('cat', filtro);
            history.replaceState(null, '', url);
        });
    });
})();
</script>

<?php require $base . 'includes/footer.php'; ?>
