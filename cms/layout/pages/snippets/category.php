<?php

    $btn_create = $ufo->btn($ufo->lng("New category"), "font-size-14px ufo-create-category mt-5 width-auto " . $ufo->reverse_float(), "btn btn-success", ["data-img" => $db->meta("error_photo")]);
    $search     = $ufo->tag("div",
        $ufo->tag("input", null, [
            "class" => "form-control ufo-search-category mr-5 ml-5",
            "placeholder" => $ufo->lng("search")
        ]) .
        $ufo->btn($ufo->tag("i", null, ["class" => "ufo-icon-search font-size-25px", "style" => "height: 43px"]), "ufo-btn-search-category"),
        ["class"=>"flex flex-start"]
    );

    echo $ufo->tag("div", $ufo->tag("div", $search) . $ufo->tag("div", $btn_create), [
        "class" => "mt-10 mb-10 ufo-header-category"
    ]);

    if ( isset($_POST["search"]) ) {
        $allCategory = (new UFO_Pages())->all_category(true);
    } else {
        $allCategory = (new UFO_Pages())->all_category(false,true, $_POST["to_page"] ?? 1);
    }
    $category    = []; $categoryID = [];

    foreach ( $allCategory["rows"] as $item ) {
        $category[] = [
            $ufo->tag("div",
                $ufo->tag('img', null, [
                    "class" => "mem-photo dib",
                    "src"   => $item["photo"],
                    "data-error" => $db->meta("error_photo")
                ]) . $ufo->tag('span', $item["title"], ["class"=>"text-table-responsive dib title"])
            ),
            $item["used"]["page"],
            $item["used"]["article"],
            $ufo->tag('div',
                $ufo->tag('i', null, ["class" => "ufo-icon-trash cl-danger cursor-pointer font-size-19px remove-category", "data-cat" => base64_encode($item['id'])]).
                $ufo->tag('i', null, ["class" => "ufo-icon-edit cl-info cursor-pointer font-size-19px edit-category " . ($ufo->dir() == "ltr" ? "ml-10" : "mr-10"), "data-cat" => base64_encode($item['id'])]),
                ["class" => "flex flex-center"]
            )
        ];
        $categoryID[] = $item["id"];
    }

    $ufo->modern_table(
        'category',
        [
            $ufo->lng("title"),
            $ufo->lng("pages"),
            $ufo->lng("articles"),
            $ufo->lng("options")
        ],
        $category, $categoryID
    );

    echo $ufo->get_modern_table("category") . ($allCategory["paging"] ?? "");

?>