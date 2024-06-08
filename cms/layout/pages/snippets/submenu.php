<div class="p-10px">
    <?php extract($arg) ?>

    <?php $submenu = $ufo->object_to_array($submenu) ?>

    <?= $ufo->btn($ufo->lng("add submenu"), "font-size-14px add-new-submenu width-100-cent", "btn btn-primary", [
        "data-prev-menu" => $prev_menu
    ]) ?>

    <?= $ufo->space() ?>

    <ul class="menu-e-row submenu-list mt-10" data-prev-menu="<?= $prev_menu ?>">
        <?php foreach ($submenu as $item) { ?>
            <li class="item" data-menu="<?= $item["id"] ?>">
                <span class="title font-size-14px"><?= $item["title"] ?></span>
                <div class="<?= $ufo->reverse_float() ?> position-relative">
                    <i class="ufo-icon-more-vertical menu-show-context"></i>
                    <div class="context-e-menu">
                        <ul>
                            <li class="submenu-context-action" data-action="edit" data-menu="<?= $item["id"] ?>" data-prev-menu="<?= $prev_menu ?>"><?= $ufo->lng("edit") ?></li>
                            <li class="submenu-context-action" data-action="remove" data-menu="<?= $item["id"] ?>" data-prev-menu="<?= $prev_menu ?>"><?= $ufo->lng("delete") ?></li>
                        </ul>
                    </div>
                </div>
            </li>
        <?php } ?>
    </ul>

    <?=$ufo->space(null, 2)?>
</div>