<?php

/**
 * Display all comments in the table
 */

$Page = (int) ($_GET["page"] ?? 1);
$Comments = $ufo->get_comments("article", true, $Page, $db->table_rows, [
    "mid" => $_["this_member"]["uid"]
]);

if ($Page > $Comments["total"] + 1 || $Page < 0) $ufo->die($ufo->urlAddParam(
    "page", 1
));

$TableComments = [
    "rows" => [],
    "ids"  => []
];

foreach ($Comments["rows"] as $comment) {
    $link = $comment["page"]["link"];

    if (!$ufo->is_url($link)) {
        switch ($comment["page"]["type"]) {
            case "page":
                $link = $ufo->web_link() . $link;
                break;
            case "article":
                $link = $ufo->web_link() . $db->slug("blog") . "/$link";
                break;
            default:
                $link = $ufo->do_work($comment["page"]["type"] . "_pages_get_full_url", $comment["page"]);
        }
    }

    $TableComments["rows"][] = [
        $ufo->tag("span", $comment["comment"]),
        $ufo->tag("span", $ufo->structureDateTime($comment["dateTime"], true)),
        $comment["accept"] == 1 ? $ufo->tag("span", $ufo->lng("Confirmed"), [
            "class" => "ufo-badge success font-size-13px"
        ]) : $ufo->tag("span", $ufo->lng("Not approved"), [
            "class" => "ufo-badge danger font-size-13px"
        ]),
        $ufo->tag("a", null, [
            "href"   => $link,
            "target" => "_blank",
            "class"  => "ufo-icon-external-link cl-info font-size-19px " . $ufo->space_lr_by_dir()
        ]) .
        $ufo->tag("i", null, [
            "class"   => "ufo-icon-file-text cl-success font-size-19px cursor-pointer",
            "onclick" => "alert('" . $comment["comment"] . "')"
        ])
    ];

    $TableComments["ids"][] = $comment["id"];
}

$ufo->modern_table("comments", [
    $ufo->lng("Text"),
    $ufo->lng("Date"),
    $ufo->lng("Status"),
    $ufo->lng("Options")
], $TableComments["rows"], $TableComments["ids"]);

echo $ufo->get_modern_table("comments") . $Comments["paging"];