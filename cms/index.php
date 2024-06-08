<?php ob_start();

$admin_page = true;

require "include" . DIRECTORY_SEPARATOR . "load.php";

$ufo->check_login_admin() || $ufo->redirect(URL_ADMIN . "login.php");

$ufo->load_layout("document");
$ufo->load_layout("panel");
$ufo->load_layout("endDoc");

ob_end_flush(); ?>