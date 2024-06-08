<section>

    <?= $ufo->tag('div',
        $ufo->tag('div', $ufo->tag("h3", $ufo->lng("Managers"), ["class" => "p-5px"])) .
        $ufo->tag('div', $ufo->tag("div", $ufo->btn($ufo->lng("New manager"), "add-new-manager font-size-14px " . $ufo->reverse_float())
        ), ["class" => "p-5px"]), ["class" => "grid grid-2 mt-10 mb-15"]) ?>

    <div class="width-100-cent p-10px">

        <div class="flex mb-10">
            <?= $ufo->single_input([
                "placeholder" => $ufo->lng("search"),
                "class" => "search-managers",
                "end"   => ""
            ]) ?>
            <?= $ufo->btn($ufo->tag("i", null, ["class" => "ufo-icon-search font-size-25px"]), "btn-search-managers " . ($ufo->dir() == "ltr" ? "ml-10" : "mr-10")) ?>
        </div>

        <?php
            if ( !isset($_POST['search']) ) {
                $page = (int)isset($_POST['to_page']) ? $_POST['to_page'] : 1;
                $data = $db->pagination("admins", [
                    "page" => $page,
                    "paging_action" => "managers-table-paging"
                ]);
                $total_pages = $data["total"];
                $rows = $data["rows"];
            } else {
                $search = $db->sanitize_string($_POST['search']);
                $rows = $db->query("SELECT * FROM `%prefix%admins` WHERE `name` LIKE '%$search%'");
            }

            $managers_to_array = [];
            $managers_id_array = [];
            $managers_count    = 0;

            foreach ($rows as $item) {
                $managers_to_array[] = [
                    $ufo->tag("div",
                        $ufo->tag('img', null, [
                            "class" => "mem-photo",
                            "src"   => $item["photo"],
                            "data-error" => $db->meta("unknown_photo")
                        ]) . $ufo->tag('span', $item["name"], ["class" => "text-table-responsive name"]),
                    ["class" => "flex"]),
                    $item["last_name"], $item["email"],
                    $ufo->tag('div',
                        ( $managers_count != 0 ? $ufo->tag('i', null, ["class" => "ufo-icon-trash cl-danger cursor-pointer font-size-19px remove-manager", "data-admin" => $item['id']]) : "" ) .
                        $ufo->tag('i', null, ["class" => "ufo-icon-edit cl-info cursor-pointer font-size-19px edit-manager " . ($ufo->dir() == "ltr" ? "ml-10" : "mr-10"), "data-admin" => $item['id']])
                        , ["class" => "flex flex-center"])
                ];
                $managers_id_array[] = $item["id"];
                $managers_count++;
            }

            $ufo->modern_table(
                'managers',
                [
                    $ufo->lng("name"),
                    $ufo->lng("last name"),
                    $ufo->lng("email"),
                    $ufo->lng("options")
                ],
                array_reverse($managers_to_array), array_reverse($managers_id_array)
            );

            echo $ufo->get_modern_table("managers") . (!isset($_POST['search']) ? $data["paging"] : "");
        ?>
    </div>

</section>