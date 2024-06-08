<?php
    extract($_POST);

    $Fields = [];
    if ($ufo->isset_key($_POST, "edit")) {
        $Fields = (new UFO_Menu())->get([
            "where"  => [
                "id" => $_POST["edit"]
            ]
        ])[0] ?? [];
    }

    echo $ufo->space("<br>");

    $ufo->add_input("input-menu", [
        "placeholder"  => $ufo->lng("title"),
        "name"         => "title",
        "class"        => "add-menu-input",
        "autocomplete" => "off",
        "value"        => $Fields["title"] ?? ""
    ]);
    $ufo->add_input("input-menu", [
        "placeholder"  => $ufo->lng("icon"),
        "name"         => "icon",
        "class"        => "add-menu-input rtl-to-ltr-placeholder",
        "autocomplete" => "off",
        "data-empty"   => "true",
        "value"        => $Fields["icon"] ?? ""
    ]);
    $ufo->add_input("input-menu", [
        "placeholder"  => $ufo->lng("link"),
        "name"         => "link",
        "class"        => "add-menu-input rtl-to-ltr-placeholder",
        "autocomplete" => "off",
        "value"        => $Fields["link"] ?? ""
    ]);

    echo $ufo->loop_inputs("input-menu");
?>

<div class="flex flex-start">
    <button class="form-control select-menu-links cursor-pointer ellipsis-text"><?= $ufo->lng("Select link") ?></button>
    <button class="btn btn-danger ufo-icon-trash width-50px <?= $ufo->space_lr_by_dir() ?> delete-menu-link" disabled></button>
</div>