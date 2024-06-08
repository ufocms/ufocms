<?php $ufo->header();

$page = $ufo->this_page();

if ($page) { ?>

    <div class="ufo-content-page">
        <?= $page["content"] ?>
    </div>

<?php }

$ufo->footer() ?>