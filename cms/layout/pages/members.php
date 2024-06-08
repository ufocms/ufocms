<section>

    <?= $ufo->tag("div",
        $ufo->tag("div", $ufo->tag("h3", $ufo->lng("Members"), [
            "class" => "p-5px"
        ])) .
        $ufo->tag("div", $ufo->tag("div",
            $ufo->btn($ufo->lng("new user"),
            "add-new-user font-size-14px " . $ufo->reverse_float()
        )), [
            "class" => "p-5px"
        ]), [
            "class" => "grid grid-2 p-10px"
        ]) ?>

    <div class="width-100-cent p-10px">

        <div class="flex mb-10 head-option-members">
            <div class="p-5px left flex flex-start">
                <?= $ufo->single_input([
                    "placeholder" => $ufo->lng("search"),
                    "class" => "search-member",
                    "end"   => "",
                    "type"  => "search"
                ]) ?>
                <?= $ufo->btn($ufo->tag("i", null, [
                    "class" => "ufo-icon-search font-size-25px"
                ]), "btn-search-member " . ($ufo->dir() == "ltr" ? "ml-10" : "mr-10")) ?>
            </div>
            <div class="p-5px right">
                <select class="form-control cursor-pointer member-action-select">
                    <option value="options" class="d-select" selected><?= $ufo->lng("options") ?></option>
                </select>
            </div>
        </div>

        <?php
            $limit = $db->table_rows;

            if ($ufo->isset_post("search"))
                $db->where("username", "%" . $db->sanitize_string($_POST['search']) . "%", "LIKE");

            $page = (int) isset($_POST["to_page"]) ? $_POST["to_page"] : 1; $db->helper->orderBy("uid");
            $data = $db->pagination("members", [
                "page"  => $page,
                "limit" => $limit,
                "paging_action" => "member-table-paging"
            ]);
            $total_pages = $data["total"];
            $rows = $data["rows"];

            $tablePlus       = $ufo->do_work("ufo_get_member_table");
            $member_to_array = [];
            $member_id_array = [];

            foreach ($rows as $item) {
                $row = [
                    $ufo->tag("img", null, [
                        "class" => "mem-photo",
                        "src"   => $item["photo"],
                        "data-error" => $db->unknown_photo
                    ]) .
                    $ufo->tag("span", $item["username"], [
                        "class" => "text-table-responsive username"]
                    ), $item["email"], $ufo->structureDateTime($item["dateTime"], true)
                ];

                $member_to_array[] = array_merge($row, $tablePlus["rows"]($item), [
                    $ufo->tag("div",
                        $ufo->tag("i", null, [
                            "class" => "ufo-icon-trash cl-danger cursor-pointer font-size-19px remove-member",
                            "data-mem" => base64_encode($item['uid'])
                        ]) .
                        $ufo->tag("i", null, [
                            "class" => "ufo-icon-edit cl-info cursor-pointer font-size-19px edit-member " . ($ufo->dir() == "ltr" ? "ml-10" : "mr-10"),
                            "data-mem" => base64_encode($item['uid'])
                        ]) .
                        $tablePlus["options"]($item),
                        ["class" => "flex flex-center"]
                    )
                ]);

                $member_id_array[] = base64_encode($item["uid"]);
            }

            $ufo->modern_table(
                'members',
                array_merge(array_merge([$ufo->lng("user"), $ufo->lng("email"), $ufo->lng("registry date")], $tablePlus["columns"]()), [$ufo->lng("options")]),
                $member_to_array, $member_id_array, true
            );

            echo $ufo->get_modern_table("members") . $data["paging"];
        ?>
    </div>

</section>