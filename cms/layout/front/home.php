<?php $ufo->header();

$articles = new UFO_Explorer([
    "hunter" => "pages",
    "type"   => "article",
    "limit"  => (int) $db->table_rows,
    "page"   => $_GET["page"] ?? 1,
    "search" => $_GET["s"] ?? false,
    "status" => 1,
    "paging_action" => "home_articles"
]);
?>

<div class="ufo-default-container">
    <div class="p-10px">
        <a href="<?= $ufo->web_link() ?>">
            <h2 class="text-center"><?= $ufo->this_title() ?></h2>
        </a>
        <a class="text-center db copyright" href="https://ufocms.org" target="_blank">
            <?= $ufo->copyright() ?>
        </a>
    </div>

    <?php $ufo->load_layout("front/menu") ?>

    <form method="get">
        <label class="input-field">
            <button class="ufo-icon-search" type="submit"></button>
            <input name="s" type="text" placeholder="<?=$ufo->lng("Search")?>" autocomplete="off" minlength="3" required>
        </label>
    </form>

    <div class="ufo-article-cards ufo-list-articles">
        <?php while ($articles->hunt()) { ?>

            <div class="item ufo-card-bg-<?= rand(1, 10) ?>">
                <div class="ufo-article-img">
                    <img src="<?= $articles->photo(1) ?>" alt="<?= $articles->title() ?>">
                </div>
                <h3><?= $articles->title() ?></h3>
                <p><?= $articles->short_desc() ?></p>
                <a href="<?= $articles->link() ?>" class="link">
                    <?= $ufo->lng("Read") ?>
                </a>
            </div>

        <?php }
            if ($articles->empty()) {
                echo $ufo->tag("h4", $ufo->lng("Nothing Found :("), [
                    "class" => "width-100-cent text-center font-size-20px"
                ]);
            }
        ?>
    </div>

    <?php $ufo->echo($articles->paging("paging") . $ufo->space(), $articles->ready()) ?>
</div>

<?php $ufo->footer(); ?>