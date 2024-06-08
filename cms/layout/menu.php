<nav class="menu">
    <div class="profile-admin">
        <img src="<?= $ufo->get_admin()["photo"] ?>" data-error="<?= $db->meta("unknown_photo") ?>">
        <div>
            <span><?= $ufo->get_admin()["login_name"] ?></span>
            <i class="ufo-icon-log-out ufo-logout-admin"></i>
        </div>
    </div>
    <ul class="menu-items">
        <?= $ufo->loop_menu() ?>
    </ul>
</nav>