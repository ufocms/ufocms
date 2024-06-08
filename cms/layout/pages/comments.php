<div class="p-10px">
<?php
    echo $ufo->tag("select", function ( ) { global $ufo;
        $join = "";
        foreach ($ufo->do_work("ufo_mode_comments") as $k => $v) {
            $attrs = ["value" => $v, "data-accept" => $k];
            if ( isset($_POST["mode"]) ) {
                if ( $k === $_POST["mode"] ) {
                    $attrs["selected"] = "";
                }
            }
            $join .= $ufo->tag("option", $ufo->lng($k), $attrs);
        }
        return $join;
    }, ["class" => "ufo-select-mode-comments form-control mt-10 mb-10"]);

    $where = [];

    if ( isset($_POST["accept"]) && $_POST["accept"] != "all" ) {
        $where["accept"] = $_POST["accept"];
    }

    $allComments = $ufo->get_comments("", true, $_POST["to_page"] ?? 1, 0, $where);
    $allComments = array_reverse($allComments);
    $comments    = [];
    $commentID   = [];

    function addComment ( $item, $comments = [] ) {
        global $ufo, $db;

        $accept_btn = $ufo->tag('i', null, [
            "class" => "ufo-icon-check cl-success cursor-pointer font-size-15px accept-comment " . ($ufo->dir() == "ltr" ? "mr-10" : "ml-10"),
            "data-comment" => $item['id']
        ]);

        if ($item["accept"] == 1)
            $accept_btn = "";

        $comments[] = [
            $ufo->tag('span', $item["guest"]["name"] ?? ($item["member"]["username"] ?? ($item["admin"]["name"] ?? $ufo->lng("Unknown"))), ["class" => "text-table-responsive clickable"]),
            isset($item["admin"]) ? $ufo->tag("span", $ufo->lng("Admin"), ["class" => "ufo-badge secondary font-size-13px"]) : (isset($item["guest"]) ?
                $ufo->tag("span", $ufo->lng("Guest"), ["class" => "ufo-badge secondary font-size-13px"]) :
                (isset($item["member"]) ? $ufo->tag("span", $ufo->lng("User"), ["class" => "ufo-badge warning font-size-13px"]) : $ufo->tag("span", $ufo->lng("Unknown"), ["class" => "ufo-badge warning font-size-13px"]))),
            $ufo->tag('span', $item["comment"], ["class" => "text-table-responsive clickable"]),
            $item["accept"] == 1 ? $ufo->tag("span", $ufo->lng("Confirmed"), ["class" => "ufo-badge success font-size-13px"]) : $ufo->tag("span", $ufo->lng("Not approved"), ["class" => "ufo-badge danger font-size-13px"]),
            $item["rate"],
            $ufo->tag("span", $ufo->structureDateTime($item["dateTime"], true), ["class" => "clickable"]),
            $ufo->tag('div', $accept_btn .
                $ufo->tag('i', null, [
                    "class" => "ufo-icon-undo cl-success cursor-pointer font-size-17px reply-comment",
                    "data-comment" => $item['id']
                ]) .
                $ufo->tag('i', null, [
                    "class" => "ufo-icon-trash cl-danger cursor-pointer font-size-17px remove-comment " . ($ufo->dir() == "ltr" ? "ml-10" : "mr-10"),
                    "data-comment" => $item['id'],
                    "style" => "margin: 1px 0 0"
                ]) .
                $ufo->tag('i', null, [
                    "class" => "ufo-icon-info cl-info cursor-pointer font-size-17px info-comment " . ($ufo->dir() == "ltr" ? "ml-10" : "mr-10"),
                    "data-comment"    => $item['id'],
                    "data-title-page" => $item["page"]["title"] ?? $ufo->lng("Not found"),
                    "data-page-link"  => $ufo->web_link() . $db->meta("slug_blog") . "/" . ($item["page"]["link"] ?? "")
                ]), ["class" => "flex flex-center"])
        ];

        return $comments;
    }

    foreach ( $allComments["rows"] as $item ) {
        $comments = addComment($item, $comments);

        foreach ($item["reply"] as $reply) {
            $comments = addComment($reply, $comments);
        }

        $commentID[] = $item["id"];
    }

    $ufo->modern_table(
        "comments", [
            $ufo->lng("name"),
            $ufo->lng("sender"),
            $ufo->lng("text"),
            $ufo->lng("confirm"),
            $ufo->lng("score"),
            $ufo->lng("date"),
            $ufo->lng("options")
        ], $comments, $commentID
    );

    echo $ufo->get_modern_table("comments") . ($allComments["paging"] ?? "");
?></div>