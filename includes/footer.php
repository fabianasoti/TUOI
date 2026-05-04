    <footer class="footer">
        <div class="footer-content">
            <div class="footer-brand">
                <img src="<?= $base ?>assets/img/tuoi_blanco.png" alt="TUOI" class="footer-logo">
                <p>Functional coffee &amp; smart food.<br><?= t('footer_tagline') ?></p>
            </div>

            <div class="footer-nav">
                <h3><?= t('footer_explore') ?></h3>
                <a href="<?= $base ?>index.php"><?= t('nav_home') ?></a>
                <a href="<?= $base ?>pages/carta/"><?= t('footer_menu_link') ?></a>
                <a href="<?= $base ?>pages/quienes-somos.php"><?= t('nav_about') ?></a>
                <a href="<?= $base ?>pages/eventos/"><?= t('nav_eventos') ?></a>
            </div>

            <div class="footer-info">
                <h3><?= t('footer_find') ?></h3>
                <a href="https://maps.app.goo.gl/w6a5cGWvKs6CbEWQ6">
                    <p>
                    C. de la Travesía, 15B<br>
                    Poblados Marítimos<br>
                    46024 València
                    </p>
                </a>
            </div>

            <div class="footer-social">
                <h3><?= t('footer_follow') ?></h3>
                <a href="https://www.instagram.com/tuoi.coffee/" target="_blank" rel="noopener">Instagram</a>
                <a href="#" target="_blank" rel="noopener">TikTok</a>
                <a href="#" target="_blank" rel="noopener">X</a>
            </div>
        </div>

        <div class="footer-bottom">
            <p class="footer-copy">&copy; <?= date('Y') ?> TUOI. <?= t('footer_rights') ?></p>
            <nav class="footer-legal-links" aria-label="<?= t('footer_legal') ?>">
                <a href="<?= $base ?>pages/legal/privacidad.php"><?= t('footer_legal_privacy') ?></a>
                <span class="footer-legal-sep" aria-hidden="true">·</span>
                <a href="<?= $base ?>pages/legal/aviso-legal.php"><?= t('footer_legal_terms') ?></a>
                <span class="footer-legal-sep" aria-hidden="true">·</span>
                <a href="<?= $base ?>pages/legal/cookies.php"><?= t('footer_legal_cookies') ?></a>
            </nav>
        </div>
    </footer>

    <?php $js_v = @filemtime(dirname(__DIR__) . '/assets/js/main.js') ?: time(); ?>
    <script src="<?= $base ?>assets/js/main.js?v=<?= $js_v ?>"></script>
</body>
</html>
