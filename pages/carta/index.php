<?php
$base         = '../../';
$current_page = 'carta';
$page_title   = 'Carta | TUOI';

require $base . 'config/conexion.php';
require $base . 'includes/header.php';

// Categorías disponibles (orden de aparición en los filtros)
$categorias = [
    'desayunos'           => 'Desayunos',
    'bocadillos'          => 'Bocadillos',
    'ensaladas'           => 'Ensaladas',
    'plant-based'         => 'Plant Based',
    'gluten-free'         => 'Gluten Free',
    'bebidas'             => 'Bebidas',
    'momento-dulce'       => 'Momento Dulce',
    'ingredientes-extras' => 'Ingredientes Extras',
];

// Cargar todas las imágenes de todas las categorías
// Soporta: .webp .jpg .jpeg .png .pdf
$all_images = [];
foreach ($categorias as $slug => $label) {
    $dir = __DIR__ . '/../../assets/img/carta/' . $slug . '/';
    if (!is_dir($dir)) continue;

    $found = glob($dir . '*.{webp,jpg,jpeg,png,pdf}', GLOB_BRACE);
    if (!$found) continue;

    // Ordenar por fecha de modificación (más reciente primero)
    usort($found, fn($a, $b) => filemtime($b) - filemtime($a));

    foreach ($found as $filepath) {
        $ext = strtolower(pathinfo($filepath, PATHINFO_EXTENSION));
        $all_images[] = [
            'src'       => $base . 'assets/img/carta/' . $slug . '/' . basename($filepath),
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
        <span class="section-label">Menú</span>
        <h1>Nuestra carta</h1>
        <p>Funcional, equilibrado y con sabor.</p>
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

        <?php if (empty($all_images)): ?>
            <div class="carta-empty">
                <div class="empty-icon" aria-hidden="true">🍽️</div>
                <h3>Próximamente</h3>
                <p>Las imágenes de la carta se cargarán desde el panel de administración.</p>
            </div>

        <?php else: ?>
            <div class="carta-lista" id="carta-lista">
                <?php foreach ($all_images as $item): ?>
                    <div class="carta-item"
                         data-categoria="<?= $item['categoria'] ?>"
                         <?= ($cat_activa !== null && $cat_activa !== $item['categoria']) ? 'hidden' : '' ?>>

                        <?php if ($item['tipo'] === 'pdf'): ?>
                            <!-- PDF: embed con fallback -->
                            <div class="carta-pdf-wrap">
                                <object
                                    data="<?= htmlspecialchars($item['src']) ?>"
                                    type="application/pdf"
                                    class="carta-pdf">
                                    <div class="carta-pdf-fallback">
                                        <p>Tu navegador no puede mostrar el PDF.</p>
                                        <a href="<?= htmlspecialchars($item['src']) ?>"
                                           target="_blank" rel="noopener" class="btn-primary">
                                            Abrir PDF
                                        </a>
                                    </div>
                                </object>
                            </div>
                        <?php else: ?>
                            <!-- Imagen normal -->
                            <img
                                src="<?= htmlspecialchars($item['src']) ?>"
                                alt="<?= htmlspecialchars($item['label']) ?>"
                                loading="lazy">
                        <?php endif; ?>

                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Mensaje si no hay resultados para el filtro activo -->
            <div class="carta-sin-resultados" id="carta-sin-resultados" hidden>
                <div class="empty-icon" aria-hidden="true">🔍</div>
                <h3>Sin imágenes en esta categoría</h3>
                <p>Próximamente se cargarán las imágenes desde el panel de administración.</p>
            </div>
        <?php endif; ?>

    </div>

</main>

<script>
(function () {
    const filtros = document.querySelectorAll('.filtro-btn');
    const items   = document.querySelectorAll('.carta-item');
    const sinRes  = document.getElementById('carta-sin-resultados');

    if (!filtros.length) return;

    function showAll() {
        items.forEach(item => item.hidden = false);
        filtros.forEach(f => {
            f.classList.remove('active');
            f.setAttribute('aria-selected', 'false');
        });
        if (sinRes) sinRes.hidden = true;
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

            if (sinRes) sinRes.hidden = visible > 0;

            // Actualizar URL sin recargar
            const url = new URL(window.location);
            url.searchParams.set('cat', filtro);
            history.replaceState(null, '', url);
        });
    });
})();
</script>

<?php require $base . 'includes/footer.php'; ?>
