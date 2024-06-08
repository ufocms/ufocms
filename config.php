<?php

/**
 * Copyright (c) 2022-2024 UFOCMS
 *
 * This software is licensed under the GPLv3 license.
 * See the LICENSE file for more information.
 */

/**
 * If the current link has a file name that does not exist.
 * The user is redirected to index.php, so UFOCMS
 * should be prevented from running completely
 * so that the server traffic does not increase
 * due to 404 caused by several missing files.
 */
$EXTENSION_URL = pathinfo(parse_url(
    $_SERVER["REQUEST_URI"], PHP_URL_PATH
), PATHINFO_EXTENSION);
if (!empty($EXTENSION_URL) && $EXTENSION_URL != "php") exit(404);

$ufo_slash = DIRECTORY_SEPARATOR;

$package = json_decode(file_get_contents(
    "content" . $ufo_slash . "private" . $ufo_slash . "package.json"
), true);

$package["admin_path"] = str_replace(
    ["/", "\\"],
    $ufo_slash,
    $package["admin_path"]
);

/**
 * Delete installer
 */
if (is_dir("install")) {

    function ufo_unlink_installer (?string $path = null): bool {
        if ($path === null)
            $path = 'install' . DIRECTORY_SEPARATOR;

        $files = array_diff(
            scandir($path), ['.', '..']
        );

        foreach ($files as $file) {
            if (is_dir("$path" . DIRECTORY_SEPARATOR . "$file")) {
                ufo_unlink_installer("$path" . DIRECTORY_SEPARATOR . "$file");
            } else {
                unlink("$path" . DIRECTORY_SEPARATOR . "$file");
            }
        }

        return rmdir($path);
    }
    ufo_unlink_installer();

}

?>