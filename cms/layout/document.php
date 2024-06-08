<?php if (ob_get_length() > 0) ob_clean(); ?>
<?php if ( !$ufo->do_work("ufo_safe_clear_document") ) { ?>
<html dir="<?= $ufo->dir() ?>" lang="<?= $db->meta("lang") ?>">
<head>
    <meta charset="<?= $ufo->charset() ?>">
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
<?= $ufo->loop_load_meta() ?>
    <title><?= $ufo->do_work("ufo_get_title") ?></title>
    <link rel="icon" href="<?= $ufo->web_logo() ?>">
<?= $ufo->loop_load_link() ?>
<?php $ufo->loop_load_styles() ?>
<?php $ufo->loop_load_scripts("top") ?>
<?php $ufo->localize_all_script() ?>
<?php $ufo->loop_source() ?>
</head>
<body class="<?=$ufo->do_work("ufo_body_class")?>"<?=$ufo->do_work("ufo_body_attrs")?>>
<?php }
if ($ufo->do_work("ufo_full_clear_document")) {
    ob_clean();
} ?>