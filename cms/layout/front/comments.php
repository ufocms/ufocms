<?php

if (!is_array($arg))
    $arg = [];

$arg["pid"] = (int) ($arg["pid"] ?? $ufo->here()->id);

$pid = $ufo->is_bas64($arg["pid"]) ? base64_decode($arg["pid"]) : $arg["pid"];

$comments = new UFO_Explorer([
    "hunter" => "comments",
    "type"   => $arg["for"] ?? "",
    "page"   => $arg["page"] ?? 1,
    "where"  => [
        "pid" => $pid,
        "accept" => $arg["accept"] ?? 1,
        "_reply" => 0
    ],
    "paging_action" => "comments-table-paging"
]);
$avgComments = $ufo->avgCommentsRate($pid);
?>

<div class="ufo-comment-wrp" data-p="<?= base64_encode($pid) ?>">
    <div class="width-100-cent grid-2">
        <div>
            <div class="ufo-avg-comments-wrp">
                <div class="font-size-22px font-bolder">
                    <?= $ufo->rlng(
                        "%n of %n",
                        $ufo->tag("span", $avgComments["rate"]),
                        $ufo->tag("span", 5)
                    ) ?>
                </div>
                <div class="ufo-avg-comments">
                    <?php for ($i = 5; $i > 0; $i--) {
                        echo $ufo->tag("i", "", [
                            "class" => ($ufo->return("ufo-icon-star-fill", $i - 1 < $avgComments["rate"], "ufo-icon-star")) . " active cl-warn"
                        ]);
                    } ?>
                    <span><?= $ufo->rlng("Out of a total of %n points", 5) ?></span>
                </div>
            </div>
        </div>
        <div class="flex align-center flex-end">
            <?= $ufo->btn($ufo->lng("Send comment"), $ufo->reverse_float() . " ufo-btn-open-comment font-size-13px") ?>
        </div>
    </div>

    <div class="ufo-send-comment-wrp">

        <div class="flex flex-end mb-20">
            <ufo-star-rating id="ufo-comment-star"></ufo-star-rating>
        </div>

        <?php if (!$ufo->check_login_member()) { ?>

            <?= $ufo->single_input([
                "placeholder" => $ufo->lng("name"),
                "class" => "ufo-field-comment",
                "data-name" => "name"
            ]) ?>
            <?= $ufo->single_input([
                "placeholder" => $ufo->lng("email"),
                "data-name" => "email",
                "class" => "ufo-field-comment",
                "end" => ""
            ]) ?>

        <?php } ?>

        <?= $ufo->tag("textarea", null, [
            "placeholder" => $ufo->lng("description"),
            "data-name"   => "content",
            "class" => "form-control p-10px ufo-field-comment"
        ]) ?>

        <?= $ufo->tag("div", $ufo->btn($ufo->lng("Submit"), "font-size-14px ufo-send-comment", "btn btn-primary", [
            "data-p" => base64_encode($pid)
        ]), [
            "class"  => "height-50px flex flex-end align-center mt-10"
        ]) ?>

    </div>

    <ul class="ufo-comments">

        <?php
        while ($comments->hunt()) {
            if ($comments->accept != 1)
                continue;

            $sender = $comments->sender();
            if (!$sender) continue;

            $unknown_photo = $db->meta("unknown_photo");
        ?>
            <li class="ufo-item-comment">

                <div class="ufo-container-comment">
                    <img src="<?= $sender["photo"] ?? $unknown_photo ?>" data-error="<?= $unknown_photo ?>">
                    <div class="ufo-content-comment">

                        <div class="grid-2">
                            <div>
                                <span class="ufo-comment-username">
                                    <?= $sender["name"] ?>
                                    <?= '<span class="ufo-badge ' . (
                                         $sender["from"] == "member" ? "success" : (
                                             $sender["from"] == "guest" ? "secondary" : (
                                                 $sender == "admin" ? "warning" : "danger"
                                             )
                                         )
                                    ) . ' font-size-13px">' . $ufo->lng(ucfirst($sender["from"])) . '</span>' ?>
                                </span>
                            </div>
                            <div>
                                <span class="<?= $ufo->reverse_float() ?>">
                                    <?= $comments->time() ?>
                                    <i class="ufo-icon-undo font-size-18px cursor-pointer"
                                       data-reply-cm="<?= base64_encode($comments->id) ?>"></i>
                                </span>
                            </div>
                        </div>

                        <div class="mt-10 ufo-comment-text">
                            <?= $comments->comment ?>
                        </div>

                    </div>
                </div>

                <ul class="ufo-comments">

                    <?php foreach ($comments->reply as $reply) {
                        if ($reply["accept"] != 1)
                            continue;

                        $sender = $reply["member"] ?? $reply["admin"] ?? $reply["guest"];
                    ?>

                        <li class="ufo-item-comment reply">
                            <div class="ufo-container-comment">
                                <img src="<?= $sender["photo"] ?? $unknown_photo ?>" data-error="<?= $unknown_photo ?>">
                                <div class="ufo-content-comment">
                                    <div class="grid-2">
                                        <div>
                                            <span class="ufo-comment-username"><?= $sender["username"] ?? ($sender["name"] ?? $ufo->lng("Unknown")) ?> <?= isset($reply["member"]) ? '<span class="ufo-badge success font-size-13px">' . $ufo->lng("User") . '</span>' : (isset($reply["guest"]) ? '<span class="ufo-badge secondary font-size-13px">' . $ufo->lng("Guest") . '</span>' : '<span class="ufo-badge warning font-size-13px">' . $ufo->lng("Admin") . '</span>') ?></span>
                                        </div>
                                        <div>
                                            <span class="<?= $ufo->reverse_float() ?>"><?= $ufo->structureDateTime($reply["dateTime"]) ?></span>
                                        </div>
                                    </div>
                                    <div class="mt-10 ufo-comment-text">
                                        <?= $reply["comment"] ?>
                                    </div>
                                </div>
                            </div>
                        </li>
                    <?php } ?>
                </ul>
            </li>
        <?php } ?>
    </ul>

    <?php
    if (!$comments->ready())
        echo $arg["empty-text"] ?? $ufo->lng("Write your comment");

    if ($comments->paging("total") >= 2)
        echo $comments->paging("paging")
    ?>
</div>