<?php
/**
 * Subnav de categorías de la carta.
 * Requiere: $base, $current_carta (slug de la categoría activa)
 */
$carta_categorias = [
    'index'               => 'Carta entera',
    'desayunos'           => 'Desayunos',
    'bocadillos'          => 'Bocadillos',
    'ensaladas'           => 'Ensaladas',
    'plant-based'         => 'Plant Based',
    'gluten-free'         => 'Gluten Free',
    'bebidas'             => 'Bebidas',
    'momento-dulce'       => 'Momento Dulce',
    'ingredientes-extras' => 'Ingredientes Extras',
];
?>
<nav class="carta-subnav" aria-label="Categorías de la carta">
    <div class="carta-subnav-inner">
        <?php foreach ($carta_categorias as $slug => $label):
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
