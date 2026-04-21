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

        // CLICK en el trigger → toggle .open
        // Usamos stopPropagation para que el listener del documento
        // no lo cierre inmediatamente
        trigger.addEventListener('click', (e) => {
            e.stopPropagation();
            const isOpen = dropdown.classList.toggle('open');
            trigger.setAttribute('aria-expanded', String(isOpen));

            // Cerrar el resto de dropdowns
            dropdowns.forEach(d => {
                if (d !== dropdown) {
                    d.classList.remove('open');
                    const t = d.querySelector('.dropdown-trigger');
                    if (t) t.setAttribute('aria-expanded', 'false');
                }
            });
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
    // 4. TOGGLE IDIOMA (preparado, sin i18n todavía)
    // --------------------------------------------------------
    const langToggle = document.querySelector('.lang-toggle');

    if (langToggle) {
        langToggle.addEventListener('click', () => {
            const opts = langToggle.querySelectorAll('.lang-option');
            opts.forEach(o => o.classList.toggle('lang-active'));
            const newLang = langToggle.querySelector('.lang-active')?.textContent?.trim();
            console.info(`[TUOI] Idioma: ${newLang} — i18n pendiente de implementar`);
            // TODO: redirigir a versión EN cuando esté disponible
        });
    }

});
