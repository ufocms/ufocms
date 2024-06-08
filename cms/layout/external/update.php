<?php

/**
 * Redirect IF - Admin no login
 */
if ( !$ufo->check_login_admin() ) $ufo->redirect(URL_WEBSITE);

ob_clean();

$ufo->load_layout("document");

echo $ufo->tag("div", $ufo->tag("div", "", ["class" => "ufo-wizard-step-container"]), ["class" => "ufo-wizard-container"]);

$ufo->load_layout("endDoc");

