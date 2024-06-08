<?php @ob_start(); ob_clean();

$admin_page = true;

require "include" . DIRECTORY_SEPARATOR . "load.php";

$ufo->check_login_admin() || $ufo->redirect(URL_WEBSITE);

$_["title"] = $ufo->lng("Logging out") . " - $db->web_name";

try {

    setcookie($db->admin_cookie, "", time() - 3600, "/");

    $ufo->redirect(URL_ADMIN . "login.php");

} catch (Exception $e) {

    $ufo->redirect(URL_ADMIN);

}

ob_end_flush(); ?>