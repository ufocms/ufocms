<?php extract($arg) ?>
<li class="setting-items flex <?=$ufo->dir() == "ltr" ? "flex-left" : "flex-right"?>" data-setting="<?= $page ?? "NaN" ?>">
    <div class="icon-wrp" style="--setting-this-icon-cl: <?= $arg["icon-color"] ?? "blue" ?>">
        <div class="icon">
            <i class="<?= $icon ?? "NaN" ?>"></i>
        </div>
    </div>
    <div class="content">
        <h3 class="title"><?= $name ?? "NaN" ?></h3>
        <span class="sub-title"><?= $more ?? "NaN" ?></span>
    </div>
</li>