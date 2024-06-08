<div class="plugins-column" data-plugin="<?= $arg["id"] ?>">
    <div class="top">
        <img class="logo" src="<?= $arg["link"] . ($arg["manifest"]["icon"] ?? "logo.png") ?>" data-error="<?= URL_ADMIN ?>content/img/ufo.png">
        <div class="grid grid-2 width-100-cent">
            <div>
                <span class="title"><?= $ufo->isset_key($arg["manifest"], "lang") ? $ufo->lng($arg["name"]) : $arg["name"] ?></span>
                <span class="version"><?= $ufo->lng("version") ?> : <?= $arg["version"] ?></span>
            </div>
            <div class="switch-wrp">
                <input type="checkbox" class="ufo-modern-inputs switch mt-5 shutdown-plugin" data-plugin="<?= $arg["id"] ?>" <?= !$arg["shutdown"] ? "checked" : "" ?>>
            </div>
        </div>
    </div>
    <p class="description"><?= $ufo->isset_key($arg["manifest"], "lang") ? $ufo->lng($arg["manifest"]["description"]) : $arg["manifest"]["description"] ?></p>
    <div class="bottom grid grid-2 ufo-plugin-item-actions" data-id="<?= $arg["id"] ?>" data-version="<?= $arg["version"] ?>">
        <div class="first"></div>
        <div class="second">
            <span class="show-detail plugin-show-info" data-plugin="<?= $arg["id"] ?>"><?= $ufo->lng("Details") ?></span>
        </div>
    </div>
</div>