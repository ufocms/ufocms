<div class="comments">
    <?php $comments = $db->query("SELECT * FROM `%prefix%comments` ORDER BY `id` DESC LIMIT 10");
    if (count($comments) == 0) {
        echo $ufo->tag("div", $ufo->lng("No comment posted"), ["class" => "flex flex-center align-center width-100-cent font-size-14px"]);
    }
    foreach ($comments as $item) {
        if ($item["aid"] != 0) continue; ?>

        <div class="comment">
            <div class="comment-header">
                <img src="<?= $item["mid"] != 0 ? ($ufo->get_member($item["mid"])["photo"] ?? "#") : "#" ?>" data-error="<?= $db->meta("unknown_photo") ?>">
                <h2><?= $item["mid"] != 0 ? ($ufo->get_member($item["mid"])["username"] ?? "NaN") : ($ufo->is_json($item["guest"]) ? (json_decode($item["guest"], true)["name"] ?? "NaN") : "") ?></h2>
            </div>
            <p><?= $item["comment"] ?></p>
            <span><?= $ufo->structureDateTime($item["dateTime"], true) ?></span>
        </div>

    <?php } ?>
</div>