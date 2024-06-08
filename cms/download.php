<?php ob_start();
/**
 * Copyright (c) 2024 UFOCMS
 *
 * @Description :
 *  Don't worry, only administrators can access this file
 *  and download the desired files from the content/files
 *  folder.
 *
 * This software is licensed under the GPLv3 license.
 * See the LICENSE file for more information.
 * --------------------------
 */

$autorun = false;
$admin_page = true;

require "include" . DIRECTORY_SEPARATOR . "load.php";

ob_clean();

if (!$ufo->check_login_admin() || !$ufo->isset_get("file"))
    $ufo->die($ufo->redirect(URL_ADMIN));

# Security Download : Please Do not delete or changed
$block_list = ["php", "htaccess", "json"];
$info = pathinfo($_GET["file"]);

if (in_array($info["extension"], $block_list))
    $ufo->die($ufo->redirect(URL_ADMIN));

$ufo->do_work("ufo_dl_file", $_GET["file"]); ?>