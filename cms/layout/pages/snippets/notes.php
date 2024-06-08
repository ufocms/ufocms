<div class="ufo-note-list">
    <ul class="ufo-list">
        <?php
            $notes = new UFO_Json(_CACHE_ . "admin/notes.json");
            foreach (array_reverse($notes->get()) as $item) {
                if ( $item["admin"] == $ufo->get_admin()["id"] ) {
                    print_r($ufo->tag("li", $ufo->tag("span", $item["note"]) . $ufo->tag("i", null, ["class" => "ufo-icon-x remove", "data-id" => $item["id"]]), ["data-id" => $item["id"]]));
                }
            }
        ?>
    </ul>
</div>
<div class="ufo-add-notes">
    <input type="text" placeholder="<?=$ufo->lng("Write a note")?>" class="form-control ufo-add-note-input">
    <button class="btn btn-info ufo-add-note-btn"><?=$ufo->lng("save")?></button>
</div>