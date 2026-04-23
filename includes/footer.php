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
            <p>&copy; <?= date('Y') ?> TUOI. <?= t('footer_rights') ?></p>
        </div>
    </footer>

    <script src="<?= $base ?>assets/js/main.js"></script>
</body>
</html>
