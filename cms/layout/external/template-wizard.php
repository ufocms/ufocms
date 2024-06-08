<?php

/**
 * Redirect IF - Admin No Login OR Undefined Cookie
 */
if (!$ufo->check_login_admin() || !(isset($_COOKIE["ufo-install-wizard-template"]) || isset($_COOKIE["ufo-update-wizard-template"])))
    $ufo->die($ufo->redirect(URL_WEBSITE));

$ufo->add_localize_script("ufo_data", "admin_url", $ufo->admin_url());

$ufo->load_layout("document");

echo $ufo->tag("div", $ufo->tag("div", "", [
    "class" => "ufo-wizard-step-container"
]), ["class" => "ufo-wizard-container"]);

$ufo->load_layout("endDoc");

?>