<script src="./js/<?php echo $filename . ".js?r=" . rand(1, 999); ?>"></script>
<!-- GA -->
<?php if ($_SERVER["SERVER_NAME"] == "amping.io") : ?>
    <!-- Google tag (gtag.js) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-H7HE7SK2EP"></script>
    <script>
        window.dataLayer = window.dataLayer || [];

        function gtag() {
            dataLayer.push(arguments);
        }
        gtag('js', new Date());

        gtag('config', 'G-H7HE7SK2EP');
    </script>
<?php endif; ?>