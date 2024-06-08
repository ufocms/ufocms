<section class="p-5px">

    <div class="width-100-cent">
        <ul class="ufo-tabs ufo-pages-scroll">
            <?php
            foreach ($ufo->get_array("ufo-page-tab-items") as $item) {
                $active = isset($item["active"]) ? ($item["active"] ? " active" : "") : "";
                echo $ufo->tag("li", $item["title"], [
                    "class" => "ufo-tabs-items" . $active,
                    "data-ufo-tab" => $item["tab"]
                ]);
            }
            ?>
        </ul>
    </div>

    <?php
    foreach ($ufo->get_array("ufo-page-tab-items") as $item) {
        $active = isset($item["active"]) ? ($item["active"] ? " active" : "") : "";
    ?>
        <div class="ufo-tabs-pages<?= $active ?>" data-ufo-tab="<?= $item["tab"] ?>">
            <?php
                if (file_exists($item["include"] . ".php"))
                    require $item["include"] . ".php";
            ?>
        </div>
    <?php } ?>

</section>