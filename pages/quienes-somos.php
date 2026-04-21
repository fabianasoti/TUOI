<?php
$base         = '../';
$current_page = 'quienes-somos';
$page_title   = 'Quiénes somos | TUOI';
require $base . 'config/conexion.php';
require $base . 'includes/header.php';
?>

<main>

    <!-- Hero interior -->
    <section class="page-hero">
        <span class="section-label">Nuestra historia</span>
        <h1>¿Quiénes somos?</h1>
        <p>Del deporte de élite a tu mesa de trabajo.</p>
    </section>

    <!-- Contenido principal -->
    <article class="quienes-page">

        <div class="quienes-intro">
            <h2>TUOI – Functional Coffee &amp; Smart Food</h2>
            <p class="quienes-lead">
                TUOI nace de la experiencia y el conocimiento de <strong>MIOBIO</strong>,
                nuestra empresa matriz, especializada en alimentación funcional aplicada
                al alto rendimiento deportivo.
            </p>
        </div>

        <div class="quienes-body">
            <p>
                Avalados por años de trabajo en el deporte de élite, hemos desarrollado un
                sólido <em>know-how</em> sobre cómo la alimentación impacta directamente en el
                rendimiento, la recuperación y la salud. Esta experiencia nos ha permitido crear
                un modelo propio, centrado en ofrecer soluciones de alimentación adaptadas a las
                necesidades reales de cada persona y cada entorno.
            </p>
            <p>
                Nuestra capacidad de adaptación es uno de nuestros principales diferenciales.
                Trabajamos en el diseño de soluciones específicas para clubes, federaciones,
                deportistas y empresas, ajustándonos a sus objetivos, exigencias y dinámicas
                del día a día.
            </p>
            <p>
                <strong>TUOI representa el siguiente paso:</strong> trasladar todo ese conocimiento
                del alto rendimiento al ámbito cotidiano, acercando la alimentación funcional a
                deportistas amateur y a cualquier persona que busque mejorar su bienestar y
                rendimiento diario.
            </p>
        </div>

        <!-- Oferta -->
        <div class="quienes-oferta">
            <h3>En TUOI ofrecemos soluciones pensadas para acompañar un estilo de vida activo:</h3>
            <ul class="oferta-list">
                <li>
                    <span class="oferta-dot" aria-hidden="true"></span>
                    <span><strong>Desayunos</strong> enfocados en activar la energía</span>
                </li>
                <li>
                    <span class="oferta-dot" aria-hidden="true"></span>
                    <span><strong>Almuerzos</strong> diseñados para sostener el rendimiento</span>
                </li>
                <li>
                    <span class="oferta-dot" aria-hidden="true"></span>
                    <span><strong>Comidas</strong> orientadas a la recuperación</span>
                </li>
                <li>
                    <span class="oferta-dot" aria-hidden="true"></span>
                    <span><strong>Opciones</strong> adaptadas a diferentes necesidades nutricionales</span>
                </li>
            </ul>
            <p class="quienes-cierre">
                Todo ello bajo una misma filosofía: <em>alimentación funcional, equilibrada, práctica y con sabor.</em>
            </p>
        </div>

        <!-- CTA -->
        <div class="quienes-cta">
            <a href="<?= $base ?>pages/carta/" class="btn-primary">Explorar la carta</a>
        </div>

    </article>

</main>

<?php require $base . 'includes/footer.php'; ?>
