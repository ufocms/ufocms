<?php

    (new UFO_Security())->init_items();

    echo "<div class='ufo-more-setting-head'>";

    echo $ufo->btn($ufo->lng("Save information"), "ufo-save-security-setting");

    echo $ufo->single_input([
        "placeholder" => $ufo->lng("Search"),
        "class"       => "form-control",
        "end"         => ""
    ]);

    echo "</div>";

    echo '<ul class="ufo-more-setting-ul">';

    foreach ( $_["ufo_security"] as $k => $item ) {
        $list = "";

        if ( is_array($item) ) {
            foreach ($item as $html) {$list .= $html;}

            echo $ufo->tag(
                "li",
                $ufo->tag("h3", $k, [
                    "class" => "ufo-advance-setting-title"
                ]) . $ufo->tag("hr") . $list
                , ["class" => "ufo-more-setting-li"]
            );
        }
    }

    echo '</ul>';