<div class="plugins-column" data-template="<?= $arg["id"] ?>">
    <div class="top">
        <img class="logo" src="<?= $arg["link"] . ($arg["manifest"]["icon"] ?? "logo.png") ?>" data-error="<?= URL_ADMIN ?>content/img/ufo.png">
        <div class="grid grid-2 width-100-cent">
            <div>
                <span class="title"><?= $arg["manifest"]["name"] ?></span>
                <span class="version"><?= $ufo->lng("version") ?> : <?= $arg["version"] ?> <?= $db->meta("theme") == $arg["id"] ? " | " . $ufo->lng("Default") : "" ?></span>
            </div>
            <div class="switch-wrp">
                <input type="checkbox" class="ufo-modern-inputs switch mt-5 shutdown-template" data-template="<?= $arg["id"] ?>" <?= ($arg["set"] ?? false) ? "checked" : "" ?>>
            </div>
        </div>
    </div>
    <p class="description"><?= $arg["manifest"]["description"] ?></p>
    <div class="bottom grid grid-2 ufo-template-item-actions" data-id="<?= $arg["id"] ?>" data-version="<?= $arg["version"] ?>">
        <div class="first"></div>
        <div class="second">
            <span class="show-detail ufo-template-show-info" data-template="<?= $arg["id"] ?>"><?= $ufo->lng("Details") ?></span>
        </div>
    </div>
</div>