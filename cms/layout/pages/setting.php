<div class="p-10px">
    <h2><?= $ufo->lng("setting") ?></h2>
    <?= $ufo->space() ?>
    <?= $ufo->loop_inputs("setting-search") ?>
</div>
<ul class="settings">
    <?php
        foreach ($ufo->get_array("settings") as $item) $ufo->load_layout(
            "pages/snippets/setting-items",
            true, ".php", $item
        );
    ?>
</ul>