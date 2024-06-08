<section class="panel">

    <?php $ufo->load_layout("menu") ?>

    <div class="content-pages">

        <?php $this->load_layout("header") ?>

        <div class="content-page">
            <?php if ($ufo->reloadedHere() && $_SERVER["REQUEST_METHOD"] == "GET") {
                // Reload the last opened page from UFO core
                echo $ufo->tag("div", $ufo->tag("div", null, [
                    "class" => "ufo-startup-loading"
                ]), [
                    "class" => "width-100-cent height-100-cent flex flex-center align-center"
                ]);
            } else $ufo->default_page() ?>
        </div>

    </div>

</section>