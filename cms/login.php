<?php @ob_start(); ob_clean();

$admin_page = true;

require "include" . DIRECTORY_SEPARATOR . "load.php";

!$ufo->check_login_admin() || $ufo->redirect(URL_ADMIN);

$_["title"] = $ufo->lng("Login") . " - $db->web_name";

?>

<html dir="<?= $ufo->dir() ?>" lang="<?= $db->meta("lang") ?>">
<head>
    <meta charset="<?= $ufo->charset() ?>">
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="robots" content="noindex, nofollow">

    <title><?= $_["title"] ?></title>

    <link rel="icon" href="<?= $ufo->web_logo() ?>">
    <link rel="stylesheet" href="<?= ASSETS . "css/ufo.css" ?>">
    <link rel="stylesheet" href="<?= ASSETS . "css/ui.css" ?>">
    <link rel="stylesheet" href="<?= ASSETS . "css/front.css" ?>">

    <script>
        const ufo_info = {
            admin_url: "<?= $ufo->admin_url() ?>",
            ajax_url : "<?= $ufo->admin_ajax() ?>",
            panel: false
        }
    </script>
</head>
<body>

<?php require LAYOUT . "login.php" ?>

<script src="<?= ASSETS . "script/jquery.min.js" ?>"></script>
<script src="<?= ASSETS . "script/options.js" ?>"></script>
<script src="<?= ASSETS . "script/ufo.js" ?>"></script>
</body>
</html>

<?php ob_end_flush() ?>