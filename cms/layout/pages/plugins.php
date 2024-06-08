<div class="row-plugins user-select-none">

    <div class="plugins-column flex flex-center align-center flex-direction-column cursor-pointer add-plugin ufo-add-new-plugin">
        <i class="ufo-icon-plus"></i><br>
        <h3><?=$ufo->lng("add plugin")?></h3>
    </div>

    <?php
        foreach ($ufo->do_work("ufo_get_all_plugin", false) as $item) {
            $ufo->load_layout("pages/snippets/plugin-items",true,".php", $item);
        }
    ?>

</div>