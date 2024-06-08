<header class="admin-header grid grid-2">

    <div class="header-title flex flex-start align-center <?= $ufo->reverse_float() ?>">
        <i class="ufo-icon-menu open-menu-side"></i>
        <h3><?= $db->meta("web_name") ?></h3>
    </div>

    <div class="header-left">
        <?= $ufo->do_work("ufo-admin-header-options") ?>
    </div>

</header>