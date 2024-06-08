<?= $ufo->tag("li",
    $ufo->tag("span", $ufo->tag("i", null, ["class" => "ufo-icon-grip-lines cl-black font-size-20px"]), ["class" => "sort-icon"]) .
    $ufo->tag("span", $arg["title"], ["class" => "title font-size-15px"]) .
    $ufo->tag("div",
        $ufo->tag("i", null, ["class" => "ufo-icon-more-vertical menu-show-context"]) .
        $ufo->tag("div",
            $ufo->tag("ul",
                $ufo->tag("li", $ufo->lng("Submenus"), ["data-action" => "submenu", "data-menu" => $arg["id"]]) .
                $ufo->tag("li", $ufo->lng("Edit"), ["data-action" => "edit", "data-menu" => $arg["id"]]) .
                $ufo->tag("li", $ufo->lng("Delete"), ["data-action" => "remove", "data-menu" => $arg["id"]])
            ),
            ["class" => "context-e-menu"]
        ), ["class" => $ufo->reverse_float() . " position-relative"]
    ), ["data-menu" => $arg["id"], "class" => "item"]) ?>