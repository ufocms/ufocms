<?php

/**
 * Get Plugin Data
 */

$plugin = $ufo->do_work("ufo_get_plugin", $_POST["plugin"]);

if (!isset($plugin["manifest"])) return;
$manifest = $plugin["manifest"];

if (isset($plugin["memory_usage"]))
    $memory = $plugin["memory_usage"];

$size = $ufo->convert_size($ufo->folder_size($plugin["path"]));

?>

<div class="ufo-popup-modal-layer plugin-detail-modal">

    <div class="ufo-popup-modal">

        <div class="header">
            <h4 class="title">
                <?= $ufo->lng($manifest["name"]) ?>
            </h4>
            <div class="close">
                <i class="ufo-icon-x"></i>
                <span>
                    <?= $ufo->lng("close") ?>
                </span>
            </div>
        </div>

        <div class="container">

            <div class="side side-plugin-info">
                <div class="top">
                    <div class="plugin-detail-logo">
                        <img src="<?= $plugin["link"] . ($plugin["manifest"]["icon"] ?? "logo.png") ?>" data-error="<?= URL_ADMIN ?>/content/img/ufo.png">
                    </div>
                </div>
                <ul class="plugin-info-list-properties">
                    <li>
                        <div><?= $ufo->lng("name") ?></div>
                        <div dir="<?= $ufo->dir() == "ltr" ? "rtl" : "ltr" ?>"><?= $ufo->lng($manifest["name"]) ?></div>
                    </li>
                    <li>
                        <div><?= $ufo->lng("version") ?></div>
                        <div dir="<?= $ufo->dir() == "ltr" ? "rtl" : "ltr" ?>"><?= $manifest["version"] ?></div>
                    </li>
                    <li>
                        <div><?= $ufo->lng("by") ?></div>
                        <div dir="<?= $ufo->dir() == "ltr" ? "rtl" : "ltr" ?>"><?= ($manifest["developer"]["name"] ?? $ufo->lng("unknown")) ?></div>
                    </li>
                    <?php
                        if (isset($manifest["developer"]["website"])) {
                            echo $ufo->tag("li",
                                $ufo->tag("div", $ufo->lng("website")) .
                                $ufo->tag("div", $ufo->tag("a", " " . $ufo->lng("view"), [
                                    "href" => $manifest["developer"]["website"],
                                    "target" => "_blank",
                                    "style" => "margin: 0 5px"
                                ]), [
                                    "dir" => $ufo->dir() == "ltr" ? "rtl" : "ltr"
                                ])
                            );
                        }
                        if (isset($manifest["document"])) {
                            echo $ufo->tag("li",
                                $ufo->tag("div", $ufo->lng("guide")) .
                                $ufo->tag("div", $ufo->tag("a", " " . $ufo->lng("view"), [
                                    "href" => $manifest["document"],
                                    "target" => "_blank",
                                    "style" => "margin: 0 5px"
                                ]), [
                                    "dir" => $ufo->dir() == "ltr" ? "rtl" : "ltr"
                                ])
                            );
                        }
                    ?>
                </ul>
            </div>

            <div class="content overflow-auto db p-10px">

                <?php if ($plugin["shutdown"]) { ?>
                    <div class="ufo-error warning">
                        <i class="ufo-icon-alert-triangle font-size-25px position-relative top-5"></i> - <?= $ufo->lng("Some information is not displayed due to the plugin being turned off") ?>
                    </div>
                <?php } ?>

                <div class="flex flex-start mb-10">
                    <button class="btn btn-danger mr-5 ml-5 font-size-14px ufo-uninstall-plugin" data-id="<?=$plugin["id"]?>"><i class="ufo-icon-trash mr-5 ml-5" style="position: relative;top: 1px;"></i><?=$ufo->lng("Delete")?></button>
                </div>

                <ul class="plugin-info-list-properties without-auto-width">

                    <?php if (isset($manifest["description"])) { ?>
                        <li style="height: auto;padding: 10px" class="db">
                            <h3><?= $ufo->lng("Description") ?></h3>
                            <hr class="mt-5">
                            <p class="mt-10">
                                <?= $ufo->lng($manifest["description"]) ?>
                            </p>
                        </li>
                    <?php } ?>

                    <?php if (isset($plugin["memory_usage"])) { ?>
                        <li style="height: 60px">
                            <div><?= $ufo->lng("Ram usage") ?></div>
                            <div dir="<?= $ufo->dir() == "ltr" ? "rtl" : "ltr" ?>">
                                <span class="circle-solid <?= $memory["percent"] <= 30 ? "info" : (50 >= $memory["percent"] ? "warn" : "danger") ?>"><?= $memory["percent"] ?>%</span>
                                <span class="ml-10 mr-10 db"><?= $memory["memory_usage"] ?>/<?= $memory["memory_limit"] ?></span>
                            </div>
                        </li>
                    <?php } ?>

                    <li style="height: 60px">
                        <div><?= $ufo->lng("volume") ?></div>
                        <div dir="<?= $ufo->dir() == "ltr" ? "rtl" : "ltr" ?>">
                            <span class="ml-10 mr-10 db">
                                <?= $size["size"] . $size["unit"] ?>
                            </span>
                        </div>
                    </li>
                </ul>

                <div class="ufo-pt-title-permissions">
                    <h3><?= $ufo->lng("More information") ?></h3>
                    <hr class="mb-20 mt-5">
                </div>

                <div class="ufo-pt-permissions">
                    <?php if (isset($ufo->get_all_shortcodes()["plugins"][$manifest["name"]])) { ?>
                        <details class="plugin-info-accordion">
                            <summary><?= $ufo->lng("Shortcodes") ?></summary>
                            <div class="p-5px">
                                <ol class="p-10px" dir="ltr">
                                    <?php
                                        foreach ($ufo->get_all_shortcodes()["plugins"][$manifest["name"]] as $items)
                                            echo $ufo->tag("li", "[" . $items["name"] . "]");
                                    ?>
                                </ol>
                            </div>
                        </details>
                    <?php } ?>

                    <?php if (isset($ufo->get_all_works()["plugins"][$manifest["name"]])) { ?>
                        <details class="plugin-info-accordion mt-10">
                            <summary><?= $ufo->lng("Works") ?></summary>
                            <div class="p-5px">
                                <ol class="p-10px" dir="ltr">
                                    <?php
                                        foreach ($ufo->get_all_works()["plugins"][$manifest["name"]] as $items)
                                            echo $ufo->tag("li", $items);
                                    ?>
                                </ol>
                            </div>
                        </details>
                    <?php } ?>

                    <?php
                        $Tasks   = (new UFO_Task())->getNormalRow();
                        $HasTask = false;
                        foreach ($Tasks as $item) {
                            if ($ufo->isset_key($item, "plugin")) {
                                if ($item["plugin"] == $plugin["id"]) {
                                    $HasTask = true;
                                    break;
                                }
                            }
                        }
                        if ($HasTask) { ?>
                            <details class="plugin-info-accordion mt-10">
                                <summary><?= $ufo->lng("Tasks") ?></summary>
                                <div class="p-5px">
                                    <ul class="p-10px ufo-pt-tasks-list">
                                        <?php foreach ($Tasks as $item) {
                                            if ($ufo->isset_key($item, "plugin")) {
                                                if ($item["plugin"] == $plugin["id"]) { ?>
                                                    <li>
                                                        <span class="db mt-5"><?= $ufo->lng("Name") ?> : <?= $item["name"] ?></span>
                                                        <hr class="mt-10 mb-10">
                                                        <span class="db mb-5"><?= $ufo->lng("Next performance") ?> : <?= $ufo->cTime($item["next"]) ?></span>
                                                        <span class="db"><?= $ufo->lng("Execution status") ?> : <?= $ufo->lng($item["status"]) ?></span>
                                                    </li>
                                                <?php }
                                            }
                                        } ?>
                                    </ul>
                                </div>
                            </details>
                    <?php } ?>
                </div>

            </div>

        </div>

    </div>

</div>