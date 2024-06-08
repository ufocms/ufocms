<?php $ufo->header();

$article = $ufo->this_article();

if ($article) { ?>

    <div class="ufo-article-wrp">

        <div class="ufo-article-content">

            <h3><?= $article["title"] ?></h3>

            <hr>

            <div class="ufo-article-row-img">
                <?php
                    $photo = $ufo->is_array($article["photo"]) ? json_decode($article["photo"], true) : [];

                    foreach ($photo as $item) {
                        echo $ufo->tag("img", null, [
                            "src" => $item,
                            "data-error" => $db->meta("error_photo")
                        ]);
                    }
                ?>
            </div>

            <?= $article["content"] ?>

        </div>

        <?php $ufo->load_layout("front/comments", true, ".php", "article") ?>

    </div>

<?php }

$ufo->footer() ?>