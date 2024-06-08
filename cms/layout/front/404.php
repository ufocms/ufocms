<?php
    if ( !$ufo->is_admin() ) {
        $ufo->load_layout("document");

        $ufo->from_theme("header");
    } ?>

<?= $ufo->tag("div", $ufo->tag("div", $ufo->tag("h2", 4) .
        $ufo->tag("img", null, [
            "src" => ASSETS . "img/ufo.png"
        ]) . $ufo->tag("h2", 4)
    ) .
    (!isset($arg["prevent_btn"]) ? $ufo->tag("div", $ufo->tag("h3", $ufo->lng("Page not found")) .
        $ufo->tag("a", $ufo->btn($ufo->lng("Back")), [
            "href" => $ufo->web_link()
        ])
    ) : ""),
    ["class" => "ufo-default-404"]
)?>

<?php !$ufo->is_admin() ? $ufo->load_layout("endDoc") : "" ?>