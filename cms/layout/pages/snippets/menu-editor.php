<?php $UFO_Menu = new UFO_Menu() ?>

<div class="menu-editor p-10px">

    <div class="menu-toolbar">
        <div class="left flex flex-start">
            <button class="btn btn-primary ufo-icon-plus menu-add"></button>
            <button class="btn select-menu form-control">
                <?php
                $Current = 0;
                $Menu = (new UFO_Explorer([
                    "hunter"    => "menu",
                    "only_menu" => true,
                    "where"     => [
                        "sub"   => 0
                    ]
                ]));
                while ($Menu->hunt()) { ?>
                    <span <?= $Menu->collected == 1 ? "" : "hidden " ?>data-id="<?= $Menu->id() ?>" data-position="<?= $UFO_Menu->readable_position($Menu->position()) ?>" data-id-position="<?= $Menu->position() ?>"><?= $Menu->title() ?></span>
                    <?php $Current = $Menu->collected == 1 ? $Menu->hunted : $Current; }
                if ($Menu->empty()) {
                    echo $ufo->tag("span", $ufo->lng("Create the first menu"), [
                        "class" => "empty"
                    ]);
                } ?>
            </button>
            <button class="btn btn-light flex flex-center align-center menu-positions">
                <i class="ufo-icon-map-pin"></i>
                <span class="font-size-14px m<?= $ufo->dir() == "ltr" ? "l" : "r" ?>-5"><?= $ufo->lng("Positions") ?></span>
            </button>
        </div>
        <div class="right flex flex-end">
            <button class="btn btn-info solid-icon ufo-icon-list-add menu-toolbar-btns"<?= $Menu->empty() ? " disabled" : "" ?> data-menu="<?= $Current["id"] ?? 0 ?>" data-action="add_submenu"></button>
            <button class="btn btn-success menu-toolbar-btns flex flex-center align-center"<?= $Menu->empty() ? " disabled" : "" ?> data-menu="<?= $Current["id"] ?? 0 ?>" data-action="change_position">
                <span class="font-size-13px<?= ($ufo->dir() == "ltr" ? " mr-5" : " ml-5") ?>"><?= $UFO_Menu->readable_position($Current["position"] ?? "") ?></span>
                <i class="ufo-icon-map-pin"></i>
            </button>
            <button class="btn btn-secondary ufo-icon-edit menu-toolbar-btns"<?= $Menu->empty() ? " disabled" : "" ?> data-menu="<?= $Current["id"] ?? 0 ?>" data-action="edit"></button>
            <button class="btn btn-danger ufo-icon-trash menu-toolbar-btns"<?= $Menu->empty() ? " disabled" : "" ?> data-menu="<?= $Current["id"] ?? 0 ?>" data-action="delete"></button>
        </div>
    </div>

    <ul class="menu-e-row mt-50">
        <?php
        $EmptySubmenu = true;
        if ($Menu->ready()) {
            $Submenu = (new UFO_Explorer([
                "hunter" => "menu",
                "where"  => [
                    "sub" => $Current["id"] ?? 0
                ]
            ]));
            while ($Submenu->hunt()) {
                $ufo->load_layout("pages/snippets/menu-item", true, ".php", [
                    "id"    => $Submenu->id(),
                    "title" => $Submenu->title()
                ]);
                $EmptySubmenu = false;
            }
        }
        ?>
    </ul>

    <h4 class="empty-submenu-list width-100-cent text-center font-size-15px mt-30">
        <?php if ($Menu->empty())
            echo $ufo->lng("Create the first menu");
        else if ($EmptySubmenu)
            echo $ufo->lng("Create your first submenu"); ?>
    </h4>

</div>