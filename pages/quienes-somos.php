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
    <div class="qs-page">

        <!-- Bloque 1: texto izquierda · imagen derecha -->
        <div class="qs-section">
            <div class="qs-text">
                <span class="section-label">Quiénes somos</span>
                <h2>Tu lugar para cuidarte sin complicaciones</h2>
                <p>
                    TUOI es una cafetería donde comer bien se vuelve fácil, accesible y realmente apetecible.
                    Un espacio pensado para que disfrutes de café, desayunos y comidas saludables que encajan
                    con tu día a día, sin complicaciones y sin renunciar al sabor.
                </p>
                <p>
                    Aquí cuidarte no es complicado. Es natural, accesible… y apetecible.
                </p>
            </div>
            <div class="qs-img">
                <img src="<?= $base ?>assets/img/quienes_somos/tuoi_quienes_somos.jpg"
                     alt="Interior de TUOI, espacio de cafetería saludable">
            </div>
        </div>

        <!-- Bloque 2: texto derecha · imagen izquierda (CSS gestiona el orden) -->
        <div class="qs-section qs-section--reverse">
            <div class="qs-text">
                <span class="section-label">Nuestro origen</span>
                <h2>El conocimiento del deporte de élite, en tu mesa</h2>
                <p>
                    Detrás de TUOI está <strong>MIOBIO</strong>, nuestra empresa matriz especializada
                    en alimentación funcional aplicada al deporte de élite. Durante años hemos trabajado
                    entendiendo cómo la alimentación influye directamente en el rendimiento, la recuperación
                    y la salud.
                </p>
                <p>
                    Esa experiencia es la base de todo lo que hacemos. TUOI nace con una idea clara:
                    acercar ese conocimiento a la vida cotidiana. Porque no hace falta ser deportista
                    profesional para querer sentirte mejor, tener más energía o cuidar lo que comes.
                </p>
            </div>
            <div class="qs-img">
                <img src="<?= $base ?>assets/img/quienes_somos/superalimentos.png"
                     alt="Superalimentos y alimentación funcional">
            </div>
        </div>

        <!-- Bloque 3: texto izquierda · imagen derecha -->
        <div class="qs-section">
            <div class="qs-text">
                <span class="section-label">Nuestra propuesta</span>
                <h2>Adaptado a tu ritmo, pensado para ti</h2>
                <p>
                    Una propuesta basada en alimentación funcional, adaptada a ritmos reales y pensada
                    para acompañarte en cualquier momento del día:
                </p>
                <ul class="qs-list">
                    <li>
                        <span class="qs-dot" aria-hidden="true"></span>
                        <span><strong>Desayunos</strong> que activan tu energía</span>
                    </li>
                    <li>
                        <span class="qs-dot" aria-hidden="true"></span>
                        <span><strong>Opciones equilibradas</strong> para mantenerte activo</span>
                    </li>
                    <li>
                        <span class="qs-dot" aria-hidden="true"></span>
                        <span><strong>Comidas</strong> que te ayudan a recuperarte y seguir</span>
                    </li>
                </ul>
                <p>
                    Todo ello con ingredientes de calidad, combinaciones equilibradas y un enfoque
                    práctico que hace que cuidarte no sea un esfuerzo.
                </p>
            </div>
            <div class="qs-img">
                <img src="<?= $base ?>assets/img/quienes_somos/nourishing_bowls.png"
                     alt="Bowls nutritivos y coloridos de TUOI">
            </div>
        </div>

        <!-- Cierre -->
        <div class="qs-cierre">
            <p>
                Porque cuando comes mejor, te sientes mejor. Y cuando te sientes mejor, todo fluye.<br>
                <strong>TUOI es ese lugar donde lo saludable pasa a formar parte natural de tu rutina.</strong>
            </p>
            <a href="<?= $base ?>pages/carta/" class="btn-primary">Explorar la carta</a>
        </div>

    </div>

</main>

<?php require $base . 'includes/footer.php'; ?>
