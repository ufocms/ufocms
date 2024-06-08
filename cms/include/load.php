<?php @ob_start(); ob_clean();

/**
 * Copyright (c) 2022-2024 UFOCMS
 *
 * This software is licensed under the GPLv3 license.
 * See the LICENSE file for more information.
 */

if (!session_id())
    session_start();

$_ = [];
$admin_folder = "";

if (isset($admin_page) && $admin_page)
    define("ADMIN", true);

if (isset($front_page) && $front_page) {
    define("FRONT", true);
    $admin_folder = $front_page;
}

if (isset($change_folder_admin))
    $admin_folder = $change_folder_admin;

if (defined("AJAX_FRONT") || isset($ajax))
    define("AJAX", true);

require $admin_folder . "include" . DIRECTORY_SEPARATOR . "config.php";

/**
 * Autoload all classes
 */
spl_autoload_register(function ($class) use ($admin_folder) {
    if (stristr($class, "UFO_"))
        require $admin_folder . "class" . DIRECTORY_SEPARATOR . "$class.php";
});

/**
 * Load PHPMailer
 */
require "PHPMailer" . DIRECTORY_SEPARATOR . "autoload.php";

$db  = new UFO_DB(
    $db_host,
    $db_user,
    $db_pass,
    $db_name,
    $db_prefix,
    $db_charset,
    $db_collate,
    $db_port,
    $db_socket
);
$ufo = new UFO_Options();

if ($ufo->is_admin()) {
    $package = json_decode(file_get_contents(
        $ufo->slash_folder("../content/private/package.json")
    ), true);
}

/** Reliable hosts */
if (isset($package["reliable_hosts"]) && is_array($package["reliable_hosts"])) {
    /** Parse web link */
    $parseWebURL = $ufo->url_info($ufo->web_link());
    $host = !empty($parseWebURL["port"]) ? "$parseWebURL[host]:$parseWebURL[port]" : $parseWebURL["host"];
    $package["reliable_hosts"][$host] = $ufo->this_title();

    if (isset($package["reliable_hosts"][$_SERVER["HTTP_HOST"]])) {
        $_["web_url"] = $ufo->web_link();
        $_["title"] = $package["reliable_hosts"][$_SERVER["HTTP_HOST"]];
    } else $ufo->die($ufo->error(
        "Security error : The provided host name is not valid for this server",
        "justify-content:center;margin: 20 0", false, true
    ));
}

/** Defines */
const SLASH = DIRECTORY_SEPARATOR;

define("BASE_PATH", dirname(__FILE__) . SLASH . $ufo->back_folder());
define("DEEP_PATH", BASE_PATH . $ufo->back_folder());
define("ADMIN_PATH", $ufo->slash_folder(BASE_PATH . $admin_folder));
define("THEME", $ufo->get_admin()["theme"] ?? "light");
define("LANG", $db->meta("lang"));
define("FRONT_THEME", $db->meta("theme"));

define("CONTENT", $ufo->slash_folder("content/"));
define("_PRIVATE_", $ufo->slash_folder("content/private/"));

define("_CLASS_", $ufo->slash_folder(BASE_PATH . "class/"));
define("_CACHE_", $ufo->slash_folder(CONTENT . "cache/"));
define("_INCLUDE_", $ufo->slash_folder(BASE_PATH . "include/"));
define("LAYOUT", $ufo->slash_folder(BASE_PATH . "layout/"));

define("FILES", $ufo->slash_folder(CONTENT . "files/"));

define("URL_ADMIN", $ufo->admin_url());
define("URL_WEBSITE", $ufo->web_link());
define("WEB_TITLE", $db->meta("web_name"));
define("WEB_ICON", $db->meta("web_icon"));

define("PLUGINS", $ufo->slash_folder(
    BASE_PATH . "../content/plugins/"
));
define("THEMES", $ufo->slash_folder(
    BASE_PATH . "../content/theme/"
));

const ASSETS      = URL_WEBSITE . "content/assets/";
const URL_FILES   = URL_WEBSITE . "content/files/";
const URL_PLUGINS = URL_WEBSITE . "content/plugins/";
const URL_THEME   = URL_WEBSITE . "content/theme/";

/** End defines */

/** Logs Handler */
(new UFO_Logs())->init_handler();

/**
 * Prevent run ( IF - The system is updating )
 */
if ($db->meta("status") == 3) $ufo->die($ufo->error(
    "The system is updating",
    "justify-content:center;margin: 20 0",
    true, true
));

/** Care */
(new UFO_Care())->init();

new UFO_Works();

try {
    new UFO_Core(isset($admin_page), isset($ajax), $autorun ?? true);

    if ( defined("ADMIN") )
        $media = new UFO_Media(isset($ajax), $autorun ?? true);
} catch ( Exception $e ) {
    $ufo->error($ufo->lng("An error has occurred"));
    $ufo->die($e, 503);
}

ob_end_flush() ?>