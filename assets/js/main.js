/* ============================================================
   TUOI — main.js
   ============================================================ */

document.addEventListener('DOMContentLoaded', () => {

    // --------------------------------------------------------
    // 1. NAVBAR — efecto scroll
    // --------------------------------------------------------
    const navbar = document.getElementById('navbar');

    if (navbar) {
        window.addEventListener('scroll', () => {
            navbar.classList.toggle('scrolled', window.scrollY > 40);
        }, { passive: true });
    }


    // --------------------------------------------------------
    // 2. MENÚ HAMBURGUESA (móvil)
    // --------------------------------------------------------
    const menuToggle = document.querySelector('.menu-toggle');
    const navLinks   = document.getElementById('nav-links');

    function closeMenu() {
        if (!navLinks || !menuToggle) return;
        navLinks.classList.remove('active');
        menuToggle.classList.remove('open');
        menuToggle.setAttribute('aria-expanded', 'false');
        document.body.style.overflow = '';
        // Cerrar todos los dropdowns abiertos
        document.querySelectorAll('.nav-dropdown.open').forEach(d => {
            d.classList.remove('open');
            const t = d.querySelector('.dropdown-trigger');
            if (t) t.setAttribute('aria-expanded', 'false');
        });
    }

    if (menuToggle && navLinks) {
        menuToggle.addEventListener('click', () => {
            const isOpen = navLinks.classList.toggle('active');
            menuToggle.classList.toggle('open', isOpen);
            menuToggle.setAttribute('aria-expanded', String(isOpen));
            document.body.style.overflow = isOpen ? 'hidden' : '';
        });

        // Cerrar menú al hacer clic en cualquier enlace de navegación,
        // EXCEPTO cuando el clic es en el trigger del dropdown
        navLinks.addEventListener('click', (e) => {
            if (e.target.closest('.dropdown-trigger')) return; // lo gestiona el trigger
            if (navLinks.classList.contains('active')) closeMenu();
        });
    }


    // --------------------------------------------------------
    // 3. DROPDOWN CARTA — funciona en desktop Y móvil
    //    Desktop: hover abre (CSS). Click también funciona.
    //    Móvil:   solo click abre (no hay hover en touch).
    // --------------------------------------------------------
    const dropdowns = document.querySelectorAll('.nav-dropdown');

    function closeAllDropdowns() {
        dropdowns.forEach(d => {
            d.classList.remove('open');
            const t = d.querySelector('.dropdown-trigger');
            if (t) t.setAttribute('aria-expanded', 'false');
        });
    }

    dropdowns.forEach(dropdown => {
        const trigger = dropdown.querySelector('.dropdown-trigger');
        if (!trigger) return;

        // CLICK en el trigger:
        // – Móvil (≤768px): previene la navegación y hace toggle del dropdown
        // – Desktop: deja que el enlace navegue normalmente (hover abre el dropdown vía CSS)
        trigger.addEventListener('click', (e) => {
            if (window.innerWidth <= 768) {
                e.preventDefault();
                e.stopPropagation();
                const isOpen = dropdown.classList.toggle('open');
                trigger.setAttribute('aria-expanded', String(isOpen));

                dropdowns.forEach(d => {
                    if (d !== dropdown) {
                        d.classList.remove('open');
                        const t = d.querySelector('.dropdown-trigger');
                        if (t) t.setAttribute('aria-expanded', 'false');
                    }
                });
            }
        });

        // Escape cierra este dropdown
        trigger.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                dropdown.classList.remove('open');
                trigger.setAttribute('aria-expanded', 'false');
                trigger.focus();
            }
        });
    });

    // Click fuera → cerrar todos los dropdowns
    document.addEventListener('click', () => closeAllDropdowns());

    // Resize → cerrar si el menú mobile está cerrado
    window.addEventListener('resize', () => {
        if (!navLinks?.classList.contains('active')) closeAllDropdowns();
    }, { passive: true });


    // --------------------------------------------------------
    // 4. EVENTOS — subnav active state on scroll
    // --------------------------------------------------------
    const evSubnav = document.querySelector('.ev-subnav');

    if (evSubnav) {
        const evLinks    = evSubnav.querySelectorAll('.ev-subnav__link');
        const evSections = [...evLinks].map(a => {
            const id = a.getAttribute('href')?.replace(/^.*#/, '');
            return id ? document.getElementById(id) : null;
        });

        const navbarH  = parseInt(getComputedStyle(document.documentElement).getPropertyValue('--navbar-h')) || 72;
        const subnavH  = evSubnav.offsetHeight;
        const offset   = navbarH + subnavH + 20;

        function updateActiveLink() {
            let current = -1;
            evSections.forEach((sec, i) => {
                if (sec && sec.getBoundingClientRect().top <= offset) current = i;
            });
            evLinks.forEach((a, i) => a.classList.toggle('active', i === current));
        }

        window.addEventListener('scroll', updateActiveLink, { passive: true });
        updateActiveLink();
    }


    // --------------------------------------------------------
    // 5. FADE-IN ON SCROLL (global)
    //    Auto-etiqueta bloques de contenido y los revela al entrar en viewport.
    // --------------------------------------------------------
    if ('IntersectionObserver' in window &&
        !window.matchMedia('(prefers-reduced-motion: reduce)').matches) {

        const fadeSelectors = [
            // Secciones genéricas (hero + bloques top-level)
            'main > section',
            'main .page-hero > *',

            // Eventos
            'main .ev-img-marquee',
            'main .ev-why__bullet',
            'main .ev-why__image',
            'main .ev-section__head',
            'main .ev-menus-intro__inner',
            'main .ev-cta__inner',
            'main .ev-contact__info',
            'main .ev-contact__form-wrap',
            'main .project-row',

            // Home
            'main .section-quienes-inner',
            'main .section-filosofia-inner > .section-header',
            'main .feature-card',
            'main .value-item',

            // Quiénes somos
            'main .qs-section',
            'main .qs-cierre',

            // Carta
            'main .carta-filtros-wrap',
            'main .carta-item'
        ].join(',');

        const targets = document.querySelectorAll(fadeSelectors);
        targets.forEach(el => {
            // No animar elementos que ya pertenecen a un padre fade-up para evitar dobles delays
            el.classList.add('fade-up');
        });

        const io = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                    io.unobserve(entry.target);
                }
            });
        }, { threshold: 0.05, rootMargin: '0px 0px -60px 0px' });

        targets.forEach(el => io.observe(el));
    }


});
// --------------------------------------------------------
// LÓGICA DE CARRUSELES DINÁMICOS
// --------------------------------------------------------
const carouselStates = {};

window.moveSlide = function(trackId, direction) {
    const track = document.getElementById(trackId);
    if (!track) return;

    const images = track.querySelectorAll('img');
    const totalImages = images.length;
    if (totalImages <= 1) return;

    // Inicializamos el contador para este carrusel si no existe
    if (typeof carouselStates[trackId] === 'undefined') {
        carouselStates[trackId] = 0;
    }

    let currentIndex = carouselStates[trackId];
    currentIndex += direction;

    // Lógica de bucle infinito (de la última salta a la primera y viceversa)
    if (currentIndex < 0) {
        currentIndex = totalImages - 1; 
    } else if (currentIndex >= totalImages) {
        currentIndex = 0; 
    }

    carouselStates[trackId] = currentIndex;

    // Desplazar visualmente las fotos
    track.style.transform = `translateX(-${currentIndex * 100}%)`;

    // Actualizar los puntos (dots) indicadores
    const dotsContainer = document.getElementById(`dots-${trackId}`);
    if (dotsContainer) {
        const dots = dotsContainer.querySelectorAll('.dot');
        dots.forEach((dot, index) => {
            if (index === currentIndex) {
                dot.classList.add('active');
            } else {
                dot.classList.remove('active');
            }
        });
    }
};