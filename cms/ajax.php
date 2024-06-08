<?php @ob_start(); ob_clean();

$ajax = true;
$admin_page = !isset($ufo_front_ajax);

require "include" . DIRECTORY_SEPARATOR . "load.php";

/**
 * Check Request Method
 */
if ($_SERVER["REQUEST_METHOD"] == "GET")
    $ufo->die($ufo->redirect(URL_WEBSITE));

/**
 * Check Parameter
 */
if (!isset($_POST["callback"]) || !isset($_GET["ajax_key"]))
    $ufo->die("Access denied!");

/**
 * Do Ajax
 */
$do_ajax = $ufo->do_ajax($_POST["callback"], $_POST["key"] ?? $ufo->do_work("ufo_ajax_key"));

/**
 * Check do_ajax
 */
if (!$do_ajax) {

    /**
     * IF - Callback has in list guest
     */

    if (in_array($_POST["callback"], $ufo->get_array("ufo_ajax_guest"))) {

        /**
         * Run Ajax Class
         */
        (new UFO_Ajax());

    } else {

        /**
         * Check admin key
         */
        $admin = $ufo->get_admin();

        if ($admin) {

            if ($admin["ajax_key"] == $_GET["ajax_key"]) {
                /**
                 * Run Ajax Class
                 */
                new UFO_Ajax();
            }

        } else {
            if (isset($_POST["from_admin"])) {
                $ufo->die("<script>location.reload()</script>");
            } else {
                $ufo->die("Access denied!");
            }
        }

    }
} else {

    /**
     * IF - callback has in do_ajax : print data
     */
    print_r($do_ajax);

}

ob_end_flush(); ?>