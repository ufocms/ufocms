<?php if (!$ufo->do_work("ufo_safe_clear_document")) { ?>

<?= $ufo->loop_load_scripts() ?>
</body>
</html><?php }
if ($ufo->do_work("ufo_full_clear_document")) {
    ob_clean();
} ?>