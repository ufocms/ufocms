<?php

$settings = (new UFO_Settings())->init();

echo "<div class='ufo-more-setting-head'>";

echo $ufo->btn($ufo->lng("Save information"), "ufo-save-advance-setting");

echo $ufo->single_input([
    "placeholder" => $ufo->lng("Search"),
    "class" => "form-control",
    "end" => ""
]);

echo "</div>";

echo '<ul class="ufo-more-setting-ul">';

foreach ($settings as $k => $item) {
    if (isset($item["html"]) && is_array($item["html"])) {
        $list = implode("", $item["html"]);

        echo $ufo->tag(
            "li", $ufo->tag("h3", $item["title"], [
                "class" => "ufo-advance-setting-title"
            ]) . $ufo->tag("hr") . $list
            , ["class" => "ufo-more-setting-li"]
        );
    }
}

echo '</ul>';