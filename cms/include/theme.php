<?php

/**
 * Copyright (c) 2022-2024 UFOCMS
 *
 * This software is licensed under the GPLv3 license.
 * See the LICENSE file for more information.
 */

/**
 * Admin Path
 */
$front_page = $package["admin_path"];

/**
 * Run UFO
 */
require $front_page . "include" . DIRECTORY_SEPARATOR . "load.php";

/**
 * Run All Setup
 */
$ufo->do_work("ufo_do_all_theme_setup");

/**
 * Template Process
 */
(new UFO_Template())->front_init() ?>