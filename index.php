<?php
$base         = '';
$current_page = 'inicio';
$page_title   = 'TUOI | Functional Coffee & Smart Food';
require 'config/conexion.php';
require 'includes/header.php';
?>

<main>

    <!-- =========================================================
         HERO — Imagen destacada + texto de bienvenida + CTA a carta
    ========================================================== -->
    <section class="hero">
        <div class="hero-content">
            <span class="hero-label">Cafetería · Valencia</span>
            <h1>Functional coffee<br>&amp; smart food</h1>
            <p>Come como piensas.<br>Comida adaptada a las necesidades de tu día.</p>
            <a href="<?= $base ?>pages/carta/" class="btn-primary">Ver la carta</a>
        </div>
    </section>
    <!-- =========================================================
         ¿QUIÉNES SOMOS? — Preview con enlace a página completa
    ========================================================== -->
    <section class="section-quienes">
        <div class="section-quienes-inner">

            <div class="quienes-text">
                <span class="section-label">¿Quiénes somos?</span>
                <h2>Del alto rendimiento<br>a tu mesa.</h2>
                <p>
                    TUOI es mucho más que una cafetería: es tu lugar para disfrutar, cuidarte y sentirte bien.
                    Un espacio donde puedes hacer una pausa, empezar el día o recargar energía
                    mientras disfrutas de un buen café y comida saludable, rica y pensada para tu día a día.
                </p>
                <p>
                    Aquí cuidarte no es complicado. Es natural, accesible… y apetecible.
                    Detrás de TUOI está el conocimiento de <strong><a href="https://miobiosport.com/" target="_blank">MIOBIO</a></strong>, especialistas en alimentación funcional
                    aplicada al deporte de élite.
                    Toda esa experiencia se traduce en algo muy simple:
                    ofrecerte opciones que no solo te gustan, sino que te ayudan a tener más energía,
                    sentirte mejor y mantener tu ritmo.
                </p>
                <a href="<?= $base ?>pages/quienes-somos.php" class="link-arrow">
                    Conoce nuestra historia <span aria-hidden="true">→</span>
                </a>
            </div>

            <div class="quienes-visual">
                <!-- Placeholder: reemplazar con imagen real cuando esté disponible -->
                <div class="img-placeholder" aria-hidden="true">
                    <img src="assets/img/tuoi_quienes_somos.jpg" alt="Imagen representativa del equipo de TUOI">
                </div>
            </div>

        </div>
    </section>

    <!-- =========================================================
         NUESTRA FILOSOFÍA — 4 valores + lista de oferta
    ========================================================== -->
    <section class="section-filosofia">
        <div class="section-filosofia-inner">

            <div class="section-header">
                <span class="section-label">Nuestra filosofía</span>
                <h2>Todo bajo una misma filosofía:<br>alimentación funcional, equilibrada y con sabor.</h2>
            </div>

            <div class="features-grid">

                <div class="feature-card">
                    <img src="assets/img/carteles/balance.png" alt="Logo Balance" class="feature-logo feature-logo--balance">
                    <span class="badge badge-verde">Balance</span>
                    <h3>Nutrición en equilibrio</h3>
                    <p>Cada plato diseñado para darte lo que necesitas, sin excesos ni carencias. Nutrición real en cada bocado.</p>
                </div>

                <div class="feature-card">
                    <img src="assets/img/carteles/energy.png" alt="Logo Energy" class="feature-logo feature-logo--energy">
                    <span class="badge badge-naranja">Energy</span>
                    <h3>Activa tu mañana</h3>
                    <p>Desayunos pensados para despertar tu rendimiento desde la primera hora del día. Sin estimulantes artificiales.</p>
                </div>

                <div class="feature-card">
                    <img src="assets/img/carteles/focus.png" alt="Logo Focus" class="feature-logo feature-logo--focus">
                    <span class="badge badge-morado">Focus</span>
                    <h3>Concentración sostenida</h3>
                    <p>Sin picos de azúcar, sin bajones a media tarde. Comida que mantiene tu mente activa cuando más lo necesitas.</p>
                </div>

                <div class="feature-card">
                    <img src="assets/img/carteles/power.png" alt="Logo Power" class="feature-logo feature-logo--power">
                    <span class="badge badge-amarillo">Power</span>
                    <h3>Rinde al máximo</h3>
                    <p>Proteínas, carbohidratos y grasas en su justa medida para que tu cuerpo funcione a pleno rendimiento, siempre.</p>
                </div>

            </div>

            <!-- Lista de oferta -->
            <div class="values-list">
                <div class="value-item">
                    <span class="value-dot" aria-hidden="true"></span>
                    <p>Desayunos enfocados en activar la energía</p>
                </div>
                <div class="value-item">
                    <span class="value-dot" aria-hidden="true"></span>
                    <p>Almuerzos diseñados para sostener el rendimiento</p>
                </div>
                <div class="value-item">
                    <span class="value-dot" aria-hidden="true"></span>
                    <p>Comidas orientadas a la recuperación</p>
                </div>
                <div class="value-item">
                    <span class="value-dot" aria-hidden="true"></span>
                    <p>Opciones adaptadas a diferentes necesidades nutricionales</p>
                </div>
            </div>

        </div>
    </section>

</main>

<?php require 'includes/footer.php'; ?>
