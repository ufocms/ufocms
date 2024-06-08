<?php @ob_start(); ob_clean();

if (!isset($ajax_front_access))
    die("Access denied!");

/**
 * Set Variables
 */
$ajax = true;
$front_page = true;
$change_folder_admin = $package["admin_path"];

const AJAX_FRONT = true;

/**
 * Load UFO
 */
require "include" . DIRECTORY_SEPARATOR . "load.php";

ob_clean();

/**
 * Prevent Request Method
 */
if ($_SERVER["REQUEST_METHOD"] == "GET")
    $ufo->redirect(URL_WEBSITE);

/**
 * Check Parameters
 */
if (!$ufo->isset_post("callback"))
    $ufo->die("Access denied!");

print_r($ufo->do_ajax(
    $_POST["callback"], $_POST["key"] ?? ($_SESSION["ufo_ajax_key"] ?? "")
));

ob_end_flush() ?>