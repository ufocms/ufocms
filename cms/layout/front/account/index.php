<?php ob_start();

$endUrl = urlencode($ufo->end_url());
$page   = LAYOUT . $ufo->slash_folder("front/account/pages/$endUrl.php");

// Security: To prevent going to other files and folders
//           and to prevent other files from being executed
$dir = explode(SLASH, pathinfo($page)["dirname"]);
$dir = $dir[9] == "account" && $dir[10] == "pages";

if (empty($ufo->fire("ufo-account-page-$endUrl", $endUrl))) {
    if ($dir && file_exists($page))
        require $page;
    else if ($endUrl == $db->slug("account"))
        require $ufo->slash_folder("pages/dashboard.php");
    else
        require $ufo->slash_folder("pages/404.php");
}

$content = ob_get_clean() ?>

<?php $ufo->document() ?>

<main class="ufo-account">
    <div class="ufo-account-menu">
        <?php require "menu.php" ?>
    </div>
    <div class="ufo-account-content">
        <header>
            <div class="flex flex-start align-center">
                <i class="ufo-icon-menu font-size-20px" id="open-menu"></i>
                <h4 class="<?= $ufo->space_lr_by_dir(10) ?>">
                    <?= $_["ufo_acc_page_title"] ?? $ufo->lng("User account") ?>
                </h4>
            </div>
            <div></div>
        </header>
        <section class="ufo-account-page">
            <?= $content ?>
        </section>
    </div>
</main>

<?php $ufo->endDoc() ?>
