<div class="ufo-mini-profile">
    <?= $ufo->tag("img", null, [
        "src" => $_["this_member"]["photo"]
    ]) ?>
    <div class="info">
        <div>
            <h5 class="username"><?= $_["this_member"]["username"] ?></h5>
            <a href="<?= $ufo->web_link() . $db->slug("account") ?>/info" class="sub-username"><?= $ufo->lng("Account information") ?></a>
        </div>
        <div>
            <a href="<?= $ufo->web_link() . $db->slug("account") ?>/logout">
                <i class="ufo-icon-log-out"></i>
            </a>
        </div>
    </div>
</div>

<?php
    $Menu = new UFO_Explorer([
        "hunter" => "menu",
        "where"  => [
            "sub" => 0,
            "position" => "user-account"
        ]
    ]);
    $this_page = end($ufo->url_info($_["this_url"])["slashes"]);
?>

<ul class="ufo-account-list-menu">
    <?php while ($Menu->hunt()) { ?>
        <?php foreach ($Menu->submenu() as $key => $menu) { ?>
            <a <?php $ufo->attr("href", $ufo->slug("account") . $menu["link"], empty($menu["submenu"])) ?>>
                <li>
                    <div class="menu<?php $ufo->echo(" active", ($menu["link"] == $this_page && $key != 0) || ($key == 0 && $menu["link"] == $this_page)) ?>">
                        <div <?php $ufo->attr("class", "icon", !empty($menu["icon"])) ?>>
                            <i class="<?= $menu["icon"] ?>"></i>
                        </div>
                        <div class="title flex space-between">
                            <div>
                                <span><?= $menu["title"] ?></span>
                            </div>
                            <?php if (!empty($menu["submenu"])) { ?>
                                <div class="submenu-action"></div>
                            <?php } ?>
                        </div>
                    </div>
                    <?php if (!empty($menu["submenu"])) { ?>
                        <div class="submenu">
                            <?php foreach ($menu["submenu"] as $ksub => $submenu) { ?>
                                <a <?php $ufo->attr("href", $ufo->slug("account") . $submenu["link"], empty($submenu["submenu"])) ?>>
                                    <div class="menu<?php $ufo->echo(" active", ($submenu["link"] == $this_page && $ksub != 0) || ($ksub == 0 && $submenu["link"] == $this_page)) ?>">
                                        <div <?php $ufo->attr("class", "icon", !empty($submenu["icon"])) ?>>
                                            <i class="<?= $submenu["icon"] ?>"></i>
                                        </div>
                                        <div class="title flex space-between">
                                            <div>
                                                <span><?= $submenu["title"] ?></span>
                                            </div>
                                        </div>
                                    </div>
                                </a>
                            <?php } ?>
                        </div>
                    <?php } ?>
                </li>
            </a>
        <?php } ?>
    <?php } ?>
</ul>