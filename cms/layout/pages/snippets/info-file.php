<div class="ufo-popup-modal-layer">
    <div class="ufo-popup-modal">
        <div class="header">
            <h4 class="title"><?= $arg["name"] ?></h4>
            <div class="close">
                <i class="ufo-icon-x"></i>
                <span><?= $ufo->lng("close") ?></span>
            </div>
        </div>
        <div class="container">
            <div class="side">
                <div class="top">
                    <?= $ufo->single_input([
                        "placeholder" => $ufo->lng("link"),
                        "value" => $arg["link"],
                        "class" => "fm-info-link-file",
                        "readonly" => true,
                        "end" => $ufo->tag("div", null, ["class" => "p-5px"]),
                        "dir" => "ltr"
                    ]) . $ufo->single_input([
                        "placeholder" => $ufo->lng("name"),
                        "value" => $arg["name"],
                        "class" => "fm-info-name-file",
                        "end" => $ufo->tag("div", null, ["class" => "p-5px"])
                    ]) ?>

                    <ul class="p-5px">
                        <li class="flex space-between align-center mb-10">
                            <span><?= $ufo->lng("size") ?> : </span><span><?= $arg["size"]["size"] . $arg["size"]["unit"] ?></span>
                        </li>
                        <li class="flex space-between align-center mb-10">
                            <span><?= $ufo->lng("format") ?> : </span><span><?= $arg["type"] ?></span>
                        </li>
                    </ul>
                </div>

                <div class="footer">
                    <?= $ufo->btn($ufo->lng("download"), "width-100-cent fm-btn-dl", "btn btn-success") . $ufo->space("<div class='p-5px'></div>") ?>
                    <div class="flex flex-start">
                        <?= $ufo->btn($ufo->lng("delete"), "width-50-cent fm-delete-file", "btn btn-danger") ?>
                        <?= $ufo->btn($ufo->lng("save"), "width-50-cent fm-save-changed-info " . ($ufo->dir() == "ltr" ? "ml-5" : "mr-5")) ?>
                    </div>
                </div>

                <?= $ufo->space(null, 2) ?>
            </div>
            <div class="content overflow-auto">
                <?= $arg["content"] ?>
            </div>
        </div>
    </div>
</div>