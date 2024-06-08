<?php ob_start();

/**
 * Copyright (c) 2022-2024 UFOCMS
 *
 * This software is licensed under the GPLv3 license.
 * See the LICENSE file for more information.
 */

require "config.php";

$ajax_front_access = true;

require "$package[admin_path]ajax_front.php";

ob_end_flush() ?>