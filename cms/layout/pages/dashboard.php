<div class="ufo-notification-list">
    <?php
        $logs = (new UFO_Json(_CACHE_ . "admin/logs.json"))->get();

        if (count($logs) != 0) {
            echo $ufo->tag("div", $ufo->btn($ufo->tag("i", null, [
                "class" => "ufo-icon-trash"
            ]), $ufo->reverse_float() . " mt-10 mb-10 ufo-btn-empty-logs", "btn btn-danger"));
        }

        foreach (array_reverse($logs) as $item) {
            echo $ufo->tag("div",[
                $ufo->tag("span", $item["title"], [
                    "class" => "width-100-cent db mb-10"
                ]),
                $ufo->tag("hr"),
                $ufo->tag("p", $item["message"], [
                    "class" => "mt-10"
                ])
            ], ["class" => "ufo-error $item[status]"]);
        }
    ?>
</div>

<section class="ufo-dashboard-widgets">

    <div class="ufo-dashboard-widgets-cr">
        <?php foreach ($ufo->get_admin_widgets()["column"] as $item) { ?>
            <div class="ufo-dashboard-widget ufo-widget-<?= $item["script"] ?>">
                <header>
                    <h4 class="ufo-widget-heading"><?= $item["title"] ?></h4>
                </header>
                <?php
                    if (isset($item["include"])) {
                        if (is_file($item["include"]) && file_exists($item["include"])) {
                            include $item["include"];
                        } else {
                            echo $ufo->lng("File not exists");
                        }
                    } else {
                        print_r($item["html"]);
                    }
                ?>
            </div>
        <?php } ?>
    </div>

    <div class="ufo-dashboard-widgets-cr">
        <?php foreach ($ufo->get_admin_widgets()["column2"] as $item) { ?>
            <div class="ufo-dashboard-widget ufo-widget-<?= $item["script"] ?>">
                <header>
                    <h4 class="ufo-widget-heading"><?= $item["title"] ?></h4>
                </header>
                <?php
                    if (isset($item["include"])) {
                        if (is_file($item["include"]) && file_exists($item["include"])) {
                            include $item["include"];
                        } else {
                            echo $ufo->lng("File not exists");
                        }
                    } else {
                        print_r($item["html"]);
                    }
                ?>
            </div>
        <?php } ?>
    </div>

</section>