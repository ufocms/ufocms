<?php

/**
 * Copyright (c) 2022-2024 UFOCMS
 *
 * This software is licensed under the GPLv3 license.
 * See the LICENSE file for more information.
 */

/**
 * Autoload all default widgets
 */
foreach ($ufo->minifyArray($ufo->get_file_list(
    realpath(__DIR__)
), "file") as $item) {
    $item = explode(SLASH, $item);
    $item = end($item);

    if (preg_match("/UFO_/i", $item)) {
        require $item;

        $class = explode(".", $item)[0];

        if (!empty($class))
            new $class();
    }
}