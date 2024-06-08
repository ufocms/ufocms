<?php

/**
 * Copyright (c) 2022-2024 UFOCMS
 *
 * This software is licensed under the GPLv3 license.
 * See the LICENSE file for more information.
 */

/**
 * UFOCMS on the Linux host must change the server's
 * include path so as not to encounter an error.
 */
if (stristr(PHP_OS, "LINUX"))
    set_include_path(DIRECTORY_SEPARATOR);

/** Support UTF-8 */
setlocale(LC_ALL, "en_US.UTF-8");

/**
 * Main database information
 */
$db_host = "%db_host%";
$db_pass = "%db_pass%";
$db_user = "%db_user%";
$db_name = "%db_name%";
$db_port = "%db_port%";
$db_prefix  = "%db_prefix%";
$db_charset = "%db_charset%";
$db_collate = $db_charset . "%db_collate%";
$db_socket  = null;