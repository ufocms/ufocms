<div class="row-plugins user-select-none">

    <div class="plugins-column flex flex-center align-center flex-direction-column cursor-pointer add-plugin ufo-add-new-template">
        <i class="ufo-icon-plus"></i><br>
        <h3><?=$ufo->lng("Add template")?></h3>
    </div>

    <?php
        foreach ($ufo->do_work("ufo_templates") as $item)
            $ufo->load_layout("pages/snippets/template-items",true,".php", $item);
    ?>

</div>