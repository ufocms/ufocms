<section>

    <div class="width-100-cent">

        <div class="flex mb-10 head-option-pages">
            <div class="left flex flex-start">
                <?= $ufo->single_input([
                    "placeholder" => $ufo->lng("search"),
                    "class" => "search-pages",
                    "end"   => ""
                ]) ?>
                <?= $ufo->btn($ufo->tag("i", null, ["class" => "ufo-icon-search font-size-25px"]), "btn-search-pages ml-10 mr-10") ?>
            </div>
            <div class="right">
                <select class="form-control cursor-pointer pages-action-select">
                    <option value="options" class="d-select" selected><?= $ufo->lng("options") ?></option>
                </select>
            </div>
        </div>

        <?= $ufo->btn($ufo->lng("New " . ($_POST["type"] ?? "page")), "font-size-14px create-new-page", "btn btn-primary", ["data-type" => isset($_POST["type"]) ? ($_POST["type"] == "page" ? "page" : "article") : "page"]) ?>
        <?= $ufo->btn($ufo->lng("Show " . (isset($_POST["type"]) ? ($_POST["type"] == "page" ? "articles" : "pages") : "articles")), "font-size-14px change-type-show", "btn btn-success", ["data-type" => isset($_POST["type"]) ? ($_POST["type"] == "page" ? "article" : "page") : "article"]) ?>

        <div class="option-pages-toolbar-wrp">
            <?php foreach ($ufo->get_array("ufo-toolbar-pages") as $item) {
                echo $ufo->tag("span", $item["title"], ["class" => "option-pages-toolbar ufo-badge " . $item["style"] . " cursor-pointer", "data-action" => $item["action"], "data-page" => ""]);
            } ?>
        </div>

        <div class="p-5px mt-5"></div>

        <?php
        $type = $_POST["type"] ?? "page";

        $page = (int) isset($_POST['to_page']) ? $_POST['to_page'] : 1;
        $class_page = (new UFO_Pages());
        $data = $class_page->all($type, 0, $page, true, $_POST['search'] ?? false, $_POST["type_page"] ?? "all");
        $rows = array_reverse($data["rows"]);
        $slug_blog = $db->meta("slug_blog");

        $page_to_array = [];
        $page_id_array = [];

        foreach ($rows as $item) {
            $category = "";
            $author = json_decode($item["author"], true);

            if (!empty($item["category"]) && $ufo->is_array($item["category"])) {
                $category    = json_decode($item["category"], true);
                $newCategory = [];
                foreach ($category as $c) {
                    $get = $class_page->get_category((int) $c);
                    if ( isset($get["title"]) ) {
                        $newCategory[] = $get["title"];
                    }
                }
                $category = join(", ", $newCategory);
            }

            $encodeID = base64_encode($item["id"]);

            $page_to_array[] = [
                $ufo->tag('span', $item["title"], ["class" => "title"]),
                $ufo->get_admin($author["id"])["login_name"] ?? ($ufo->get_member($author["id"])["username"] ?? $ufo->lng("Unknown")),
                $category,
                $class_page->status_to_text($item["status"]),
                $ufo->structureDateTime($item["dateTime"], true),
                $ufo->tag('div',
                    $ufo->tag('i', null, [
                        "class" => "ufo-icon-trash cl-danger cursor-pointer font-size-19px remove-page",
                        "data-page" => $encodeID
                    ]) .
                    $ufo->tag('a', null, [
                        "class"  => "ufo-icon-edit text-decoration-none cl-info cursor-pointer font-size-19px " . $ufo->space_lr_by_dir(10),
                        "href"   => $ufo->urlAddParam("page", $encodeID, $ufo->admin_url() . "ufo-editor", false),
                        "target" => "_blank"
                    ]) .
                    $ufo->tag('a', null, [
                        "href"   => URL_WEBSITE . ($ufo->return($slug_blog . "/", $ufo->equal($item["type"], "article"))) . $item["link"],
                        "target" => "_blank",
                        "class"  => "text-decoration-none ufo-icon-external-link cl-success cursor-pointer font-size-19px " . $ufo->space_lr_by_dir(10)
                    ]), ["class" => "flex flex-center"])
            ];

            $page_id_array[] = $encodeID;
        }

        $ufo->modern_table(
            "pages", [
                $ufo->lng("title"),
                $ufo->lng("author"),
                $ufo->lng("category"),
                $ufo->lng("status"),
                $ufo->lng("date"),
                $ufo->lng("options"),
            ], $page_to_array, $page_id_array, true
        );

        echo $ufo->get_modern_table("pages") . (!isset($_POST['search']) ? $data["paging"] : "");
        ?>

    </div>

</section>