<?php
$base         = '../../';
$current_page = 'carta';
$extra_css    = 'carta';
$page_title   = 'Carta | TUOI';

require $base . 'config/conexion.php';
require $base . 'config/carta_meta.php';
require $base . 'config/content_helper.php';
require $base . 'includes/header.php';
// $lang lo define lang.php (cargado desde header.php).
global $carta_info;

// La carta ahora se ve "una categoría por pantalla". Sin parámetro ?cat
// caemos a la primera categoría definida en $CARTA_CATEGORIAS.
$cat_slugs   = array_keys($CARTA_CATEGORIAS);
$cat_activa  = (isset($_GET['cat']) && array_key_exists($_GET['cat'], $CARTA_CATEGORIAS))
    ? $_GET['cat']
    : $cat_slugs[0];

// Título y subtítulo de la categoría — se sacan de $carta_info (lang.php),
// con fallback a la etiqueta corta si la entrada no existe.
$cat_titulo = $CARTA_CATEGORIAS[$cat_activa][$lang] ?? $CARTA_CATEGORIAS[$cat_activa]['es'];
$cat_sub    = '';
if (isset($carta_info[$cat_activa])) {
    $info        = $carta_info[$cat_activa][$lang] ?? $carta_info[$cat_activa]['es'];
    $cat_titulo  = $info[0] ?? $cat_titulo;
    $cat_sub     = $info[1] ?? '';
}

// Cargar ítems visibles de la categoría activa, ordenados.
$items = [];
$cat_esc = mysqli_real_escape_string($conexion, $cat_activa);
$res = mysqli_query($conexion,
    "SELECT * FROM carta_items WHERE categoria='$cat_esc' AND visible = 1
     ORDER BY sort_order ASC, id ASC"
);
if ($res) while ($row = mysqli_fetch_assoc($res)) $items[] = $row;

// Agrupar ítems consecutivos por sub-categoría preservando el orden definido
// en admin. Un cambio en el campo `subcategoria` corta el grupo.
$grupos = []; // [['sub' => ..., 'sub_en' => ..., 'items' => [...]], ...]
$current = null;
foreach ($items as $it) {
    $sub = trim($it['subcategoria'] ?? '');
    if ($current === null || $current['sub'] !== $sub) {
        if ($current !== null) $grupos[] = $current;
        $current = [
            'sub'    => $sub,
            'sub_en' => trim($it['subcategoria_en'] ?? ''),
            'items'  => [],
        ];
    }
    $current['items'][] = $it;
}
if ($current !== null) $grupos[] = $current;

// Helper: campo con fallback EN→ES.
function carta_field(array $item, string $base_field, string $lang): string {
    $en_key = $base_field . '_en';
    if ($lang === 'en' && !empty($item[$en_key])) return $item[$en_key];
    return (string) ($item[$base_field] ?? '');
}

$items_url = $base . 'assets/img/carta/items/';
$c = load_site_content($conexion, $lang);
?>

<section class="page-hero">
    <span class="section-label"><?= t('carta_page_label') ?></span>
    <h1><?= t('carta_page_title') ?></h1>
    <p><?= t('carta_page_sub') ?></p>
</section>

<section class="section-ig-cta">
    <div class="section-ig-cta__inner">
        <svg class="section-ig-cta__icon" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
            <rect x="2" y="2" width="20" height="20" rx="5" ry="5" stroke="currentColor" stroke-width="1.8"/>
            <circle cx="12" cy="12" r="4.5" stroke="currentColor" stroke-width="1.8"/>
            <circle cx="17.5" cy="6.5" r="1" fill="currentColor"/>
        </svg>
        <p class="section-ig-cta__text"><?= htmlspecialchars($c['ig_cta_text']) ?></p>
        <a href="<?= htmlspecialchars($c['ig_cta_url']) ?>"
           target="_blank" rel="noopener noreferrer"
           class="btn-primary">
            <?= t('btn_instagram') ?>
        </a>
    </div>
</section>

<!-- Filtros sticky — fuera del main para no quedar limitado por su padding -->
<nav class="carta-filtros-wrap" aria-label="Categorías">
    <div class="carta-filtros" role="tablist">
        <?php foreach ($CARTA_CATEGORIAS as $slug => $labels):
            $href = 'index.php' . ($slug === $cat_slugs[0] ? '' : '?cat=' . urlencode($slug));
        ?>
            <a class="filtro-btn <?= $cat_activa === $slug ? 'active' : '' ?>"
               href="<?= htmlspecialchars($href) ?>"
               role="tab"
               aria-selected="<?= $cat_activa === $slug ? 'true' : 'false' ?>">
                <?= htmlspecialchars($labels[$lang] ?? $labels['es']) ?>
            </a>
        <?php endforeach; ?>
    </div>
</nav>

<main class="carta-page">

    <!-- Cabecera con título de la categoría -->
    <section class="carta-cabecera">
        <h1 class="carta-cabecera-titulo"><?= htmlspecialchars($cat_titulo) ?></h1>
        <?php if ($cat_sub !== ''): ?>
            <p class="carta-cabecera-sub"><?= htmlspecialchars($cat_sub) ?></p>
        <?php endif; ?>
    </section>

    <!-- Cuerpo: grupos de ítems en 2 columnas -->
    <div class="carta-cuerpo">

        <?php if (empty($grupos)): ?>
            <div class="carta-empty">
                <p><?= t('carta_soon') ?></p>
                <p><?= t('carta_soon_desc') ?></p>
            </div>
        <?php else: ?>
            <?php foreach ($grupos as $grupo):
                $sub_label = ($lang === 'en' && $grupo['sub_en'] !== '') ? $grupo['sub_en'] : $grupo['sub'];
            ?>
                <section class="carta-grupo<?= $sub_label !== '' ? ' carta-grupo--con-label' : '' ?>">
                    <?php if ($sub_label !== ''): ?>
                        <div class="carta-grupo-label" aria-hidden="true">
                            <span><?= htmlspecialchars($sub_label) ?></span>
                        </div>
                    <?php endif; ?>

                    <ul class="carta-items">
                    <?php foreach ($grupo['items'] as $item):
                        $nombre       = carta_field($item, 'nombre', $lang);
                        $descripcion  = carta_field($item, 'descripcion', $lang);
                        $ingredientes = carta_field($item, 'ingredientes', $lang);
                        $tags         = array_filter(explode(',', $item['alergenos'] ?? ''));
                        $es_supl      = !empty($item['es_suplemento']);
                    ?>
                        <li class="carta-item">
                            <?php if (!empty($item['foto'])): ?>
                                <div class="item-foto">
                                    <img src="<?= htmlspecialchars($items_url . $item['foto']) ?>"
                                         alt="<?= htmlspecialchars($nombre) ?>" loading="lazy">
                                </div>
                            <?php endif; ?>

                            <div class="item-cuerpo">
                                <div class="item-cabecera">
                                    <h3 class="item-nombre"><?= htmlspecialchars($nombre) ?></h3>
                                    <?php if ($item['precio'] !== null): ?>
                                        <span class="item-precio">
                                            <?= $es_supl ? '+' : '' ?><?= number_format((float)$item['precio'], 2, ',', '') ?> €
                                        </span>
                                    <?php endif; ?>
                                </div>
                                <?php if ($descripcion !== ''): ?>
                                    <p class="item-desc"><?= nl2br(htmlspecialchars($descripcion)) ?></p>
                                <?php endif; ?>
                                <?php if ($ingredientes !== ''): ?>
                                    <p class="item-ingredientes"><?= nl2br(htmlspecialchars($ingredientes)) ?></p>
                                <?php endif; ?>
                                <?php if (!empty($tags)): ?>
                                    <ul class="item-alergenos" aria-label="Etiquetas">
                                        <?php foreach ($tags as $tag):
                                            if (!isset($CARTA_ALERGENOS[$tag])) continue;
                                            $meta = $CARTA_ALERGENOS[$tag];
                                            $label = $meta[$lang] ?? $meta['es'];
                                        ?>
                                            <li class="alergeno" title="<?= htmlspecialchars($label) ?>">
                                                <span aria-hidden="true"><?= $meta['icon'] ?></span>
                                                <span class="alergeno-label"><?= htmlspecialchars($label) ?></span>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                <?php endif; ?>
                            </div>
                        </li>
                    <?php endforeach; ?>
                    </ul>
                </section>
            <?php endforeach; ?>
        <?php endif; ?>

    </div>

</main>

<?php require $base . 'includes/footer.php'; ?>
