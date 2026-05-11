<?php
/**
 * Subnav de categorías de la carta.
 * Requiere: $base, $current_carta (slug de la categoría activa), $lang (set by header)
 */
global $carta_info;

// Orden visual de las pestañas. La clave 'index' es especial: representa
// la vista "Carta entera" y apunta a pages/carta/ (no a un slug.php).
$carta_categorias = [
    'index'          => t_raw('subnav_all'),
    'desayunos'      => t_raw('cat_desayunos'),
    'toque-salado'   => t_raw('cat_toque'),
    'momento-dulce'  => t_raw('cat_dulce'),
    'bebidas'        => t_raw('cat_bebidas'),
    'superalimentos' => t_raw('cat_super'),
];
?>
<nav class="carta-subnav" aria-label="Categorías de la carta">
    <div class="carta-subnav-inner">
        <?php foreach ($carta_categorias as $slug => $label):
            // 'index' apunta al directorio (resuelto a pages/carta/index.php por el servidor),
            // el resto a una página por categoría: pages/carta/<slug>.php.
            $href = ($slug === 'index')
                ? $base . 'pages/carta/'
                : $base . 'pages/carta/' . $slug . '.php';
            $is_active = ($current_carta === $slug);
        ?>
            <a href="<?= $href ?>"
               class="<?= $is_active ? 'active' : '' ?>"
               <?= $is_active ? 'aria-current="page"' : '' ?>>
                <?= htmlspecialchars($label) ?>
            </a>
        <?php endforeach; ?>
    </div>
</nav>
