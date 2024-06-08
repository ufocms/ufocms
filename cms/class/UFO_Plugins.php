<?php

/**
 * Copyright (c) 2022-2024 UFOCMS
 *
 * This software is licensed under the GPLv3 license.
 * See the LICENSE file for more information.
 */

final class UFO_Plugins {

    protected $baseFile   = "";
    protected $PluginDir  = "";
    protected $FloatDir   = "";
    protected array $plugin     = [];
    protected array $plugins    = [];
    protected array $waiting    = [];
    protected array $rendered   = [];
    protected $JsonPlugin = [];

    /**
     * @throws Exception
     */
    public function __construct ( ) {
        global $ufo, $admin_folder;

        $this->baseFile  = $ufo->slash_folder($admin_folder . "content/cache/plugins.json");
        $this->PluginDir = $ufo->plugin_dir();
        $this->FloatDir  = $ufo->slash_folder($this->PluginDir . "../private/ufo_process_extract_plugin");

        /**
         * JSON Config
         */
        $this->JsonPlugin = new UFO_Json($this->baseFile);

        /**
         * Add Works
         */
        $this->add_works();

        /**
         * Add Rules
         */
        $this->rules();

        /**
         * If the plugins folder does not exist,
         * remove the plugin from the plugins JSON file
         */
        foreach ($this->JsonPlugin->get() as $value)
            if (!is_dir("$this->PluginDir$value[path]")) {
                /**
                 * Remove this plugin from plugins.json
                 */
                $this->JsonPlugin->where("id", $value["id"])->remove();
            }

        /**
         * Check All Plugins
         */
        foreach ($this->plugins() as $item)
            $this->before_run($item);

        /**
         * Run all plugins in the waiting list
         */
        foreach ($this->waiting as $k => $plugins) {
            if ($ufo->isset_key($this->rendered, $k)) {
                if ($this->rendered[$k]) {
                    foreach ($plugins as $plugin) {
                        $plugin["directory"] = $plugin["path"];
                        $this->before_run($plugin);
                    }
                }
            }
        }
    }

    /**
     * @return void
     */
    protected function add_works ( ) {
        global $ufo;

        /**
         * @param $in_process
         * @description :
         *  - process ( true )  : All plugins that are running
         *  - process ( false ) : All plugins installed
         * @return array
         */
        $ufo->add_work("ufo_get_all_plugin", function ($in_process = true) {
            global $ufo;

            if ( $in_process ) {
                /** in process */
                return $this->plugins;
            } else {
                /**
                 * JSON Config
                 */

                $plugins = $this->JsonPlugin->reset()->get();

                /**
                 * Loop For Add Data
                 */
                foreach ($plugins as $k => $item) {
                    /**
                     * Address
                     */
                    $plugins[$k]["path"] = $ufo->slash_folder($this->PluginDir . $item["path"] . "/");
                    $plugins[$k]["link"] = $ufo->sanitize_link($ufo->web_link() . "content/plugins/$item[path]/");
                    $plugins[$k]["id"]   = $item["id"];

                    /**
                     * Check Exists Manifest
                     */
                    if ( !file_exists($plugins[$k]["path"] . "manifest.json") ) {
                        unset($plugins[$k]);
                    } else {
                        /**
                         * Manifest
                         */
                        $plugins[$k]["manifest"] = json_decode(file_get_contents($plugins[$k]["path"] . "manifest.json"), true);

                        $important_manifest = ["name", "icon", "version"];
                        if ( !$ufo->has_in_array($important_manifest, $plugins[$k]["manifest"]) ) {
                            unset($plugins[$k]);
                        }
                    }
                }

                return $plugins;
            }
        });

        /**
         * @param $select
         * @return array
         */
        $ufo->add_work("ufo_get_plugin", function ($select) {
            return $this->get($select);
        });

        /**
         * @param string $plugin
         * @return mixed
         */
        $ufo->add_work("ufo_shutdown_plugin", function (string $plugin) {
            return $this->shutdown($plugin);
        });
        $ufo->add_work("ufo_active_plugin", function (string $plugin) {
            return $this->active($plugin);
        });
        $ufo->add_work("ufo_uninstall_plugin", function (string $plugin) {
            return $this->uninstall($plugin);
        });

        /**
         * @param string $plugin
         * @return bool
         */
        $ufo->add_work("ufo_upload_plugin", function (string $plugin) {
            return $this->upload($plugin);
        });

        /**
         * @return string(JSON)
         */
        $ufo->add_ajax("ufo-install-wizard-plugin", function () {
            global $ufo;

            if (!$ufo->check_login_admin())
                $ufo->die($ufo->status(403, $ufo->lng("Access denied")));

            $install = $this->install_process();

            return $install ? $ufo->status(
                200, $ufo->lng("Done successfully"
            ), [
                "info" => $install
            ]) : $ufo->status(503, $ufo->lng("System error"));
        }, true);
    }

    /**
     * @return void
     * @throws Exception
     */
    protected function rules ( ) {
        global $ufo;

        $ufo->add_rule(LAYOUT . "external/plugin-wizard", "/ufo-plugin-wizard");
        $this->wizard();
    }

    /**
     * @param $plugin
     * @return void
     * @throws Exception
     */
    protected function before_run ($plugin) {
        global $ufo;

        /** Check Manifest */
        if (file_exists($plugin["directory"] . "manifest.json")) {
            ob_start();

            /**
             * Add plugin properties
             */
            $this->plugin["path"] = $plugin["directory"];
            $this->plugin["link"] = $ufo->sanitize_link($ufo->web_link() . "content/plugins/" . pathinfo($plugin["directory"])["filename"] . "/");
            $this->plugin["id"]   = $plugin["id"];
            $this->plugin["manifest"] = json_decode(file_get_contents($plugin["directory"] . "manifest.json"), true);
            $this->plugin["shutdown"] = $plugin["shutdown"];
            $this->get_languages();

            /**
             * Prevent Run
             */
            if ($ufo->isset_key($ufo->prevent_ajax(), "plugins")) {
                if ($ufo->isset_key($this->plugin["manifest"], "permissions")) {
                    if (!in_array("prevent_ajax", $this->plugin["manifest"]["permissions"]))
                        return;
                } else return;
            }

            /**
             * Easy Access To This Plugin
             */
            $_["this_plugin"] = $this->plugin;

            /**
             * Full Check And Run
             */
            $this->run();

            /**
             * Append To Array Plugins
             */
            $this->plugins[] = $this->plugin;

            /**
             * Reset This Plugin
             */
            $this->plugin = [];
            unset($_["this_plugin"]);

            ob_clean();
        } else {
            /**
             * IF the plugin folder does not exist,
             * remove the plugin from the plugins JSON file
             */
            if (!is_dir($plugin["directory"]))
                $this->JsonPlugin->where("id", $plugin["id"])->remove();
        }
    }

    /**
     * @return false|void
     */
    protected function run ( ) {
        global $ufo;

        if ( !$this->plugin["shutdown"] ) {

            /**
             * Check the information for run plugin
             */

            $important_manifest = ["name", "icon", "version"];
            if ( !$ufo->has_in_array($important_manifest, $this->plugin["manifest"]) )
                return false;

            if ( $ufo->isset_key($this->plugin["manifest"], "run") ) {

                $run = $this->plugin["manifest"]["run"];

                /**
                 * If the plugin wants to run after another plugin,
                 * it must wait in the waiting list and run after it
                 */
                if ( $ufo->isset_key($run, "after") ) {
                    if ( is_array($run["after"]) ) {
                        $continue = true;

                        foreach ($run["after"] as $plugin)
                            if ( !$ufo->isset_key($this->rendered, $plugin) ) {
                                $this->add_waiting($plugin);
                                $continue = false;
                            }

                        if ($continue) return false;
                    } else if ( is_string($run["after"]) )
                        if ( !$ufo->isset_key($this->rendered, $run["after"]) ) {
                            $this->add_waiting($run["after"]);
                            return false;
                        }
                }


                /**
                 * IF - there is an autorun in the plugin manifest, run the plugin in the background
                 */
                if ( $ufo->isset_key($run, "auto") ) {

                    /**
                     * Check Exists File
                     */
                    $file = $this->plugin["path"] . $run["auto"] . ".php";

                    if ( is_file($file) ) {

                        /**
                         * Check the information for run plugin
                         */
                        $important_manifest = ["front", "admin"];
                        if ( !$ufo->has_in_array($important_manifest, $run) )
                            return false;

                        if ( !$run["front"] && defined("FRONT") )
                            return false;
                        if ( !$run["admin"] && defined("ADMIN") )
                            return false;

                        if ( defined("AJAX") ) {
                            if ( $ufo->isset_key($run, "ajax") ) {
                                if ( $run["ajax"] ) {
                                    $this->render_plugin($file, [
                                        "ajax" => $run["ajax"]
                                    ]);
                                }
                            }
                        } else {
                            $this->render_plugin($file);
                        }

                        $this->rendered[$this->plugin["manifest"]["id"]] = true;
                    }
                }
            }

        }
    }

    /**
     * @param $plugin
     * @return void
     */
    protected function add_waiting ($plugin) {
        global $ufo;
        if (!$ufo->isset_key($this->waiting, $plugin))
            $this->waiting[$plugin] = [];
        $this->waiting[$plugin][] = $this->plugin;
    }

    /**
     * @param $file
     * @param array $args
     * @return void
     */
    protected function render_plugin ($file, array $args = []) {
        global $ufo, $db, $_;
        try {
            $OS = new UFO_OS();

            /** Language */
            $this->set_language();

            $OS->set_start_memory();

            if (!$ufo->isset_key($_, "this_plugin")) {
                $_["this_plugin"] = $this->plugin;
                $added_to_hooks   = true;
            }

            require $file;

            /** Run the Ajax custom file */
            if (isset($args["ajax"]) && is_string($args["ajax"]))
                require $this->plugin["path"] . "$args[ajax].php";

            $OS->set_mem_usage();

            $OS->set_end_memory('End run plugin (' . $this->plugin["manifest"]["name"] . ')');

            $this->plugin["memory_usage"] = $OS->get_memory_info();

            if ( $this->plugin["memory_usage"]["percent"] > $ufo->get_package()["config"]["plugins"]["limit_memory_usage"] ) {
                if ( $this->shutdown($this->plugin["id"]) ) {
                    $ufo->add_log(
                        str_replace("%n", $this->plugin["manifest"]["name"], $ufo->lng("Plugin %n")),
                        str_replace("%n", $this->plugin["manifest"]["name"], $ufo->lng("Plugin %n has been turned off due to overuse of RAM")),
                        "danger"
                    );
                }
            }

            if (isset($added_to_hooks))
                unset($_["this_plugin"]);
        } catch (Exception $e) {}
    }

    /**
     * @return array
     */
    protected function plugins ( ): array {
        global $ufo;

        /**
         * Decode Plugin Row
         *
         * @var $plugins
         * @var $result
         */
        $plugins = json_decode(file_get_contents($this->baseFile), true);
        $result  = [];

        /**
         * Each plugin is added to the array
         */
        foreach ($plugins as $items) {
            $directory = $ufo->slash_folder($this->PluginDir . $items["path"] . "/");
            $result[] = [
                "directory" => $directory,
                "shutdown"  => $items["shutdown"],
                "id" => $items["id"]
            ];
        }

        return $result;
    }

    /**
     * @return array|string|string[]
     */
    protected function path_url ( ) {
        global $ufo;
        return $ufo->sanitize_link(URL_WEBSITE . str_replace(["../", "..\\"], "", $this->plugin["path"]));
    }

    /**
     * @param $id
     * @return array
     */
    protected function get ($id): array {
        foreach ($this->plugins as $plugin) {
            if (isset($plugin["id"])) {
                if ($plugin["id"] == $id)
                    return $plugin;
            }
        }
        return [];
    }

    /**
     * @return false|void
     */
    protected function set_language ( ) {
        global $ufo;

        $language = $ufo->slash_folder($this->plugin["path"] . "/language/");

        /** Set default language */
        if (file_exists($language . LANG . ".json"))
            $ufo->add_lng($language . LANG . ".json");

        /** Set custom language */
        else if ($ufo->isset_key($this->plugin["manifest"], "lang"))
            if (file_exists($language . $this->plugin["manifest"]["lang"] . ".json"))
                $ufo->add_lng($language . $this->plugin["manifest"]["lang"] . ".json");
    }

    /**
     * @return void
     */
    protected function get_languages ( ) {
        global $ufo;

        $this->plugin["lang"] = [];
        $folder    = $ufo->slash_folder($this->plugin["path"] . "language/");
        $languages = $ufo->get_file_subfolder($folder);

        foreach ($languages as $lng => $v)
            $this->plugin["lang"][] = $lng;
    }

    /**
     * @param string $plugin
     * @return array|false|int|mixed|string
     */
    protected function shutdown (string $plugin) {
        if ( $this->JsonPlugin->where("id", $plugin)->exists() ) {
            $this->remove_task($plugin);
            return $this->JsonPlugin
                ->where("id", $plugin)
                ->update([
                    "shutdown" => true
                ]);
        } else {
            return 404;
        }
    }

    /**
     * @param string $plugin
     * @return false|int|string
     */
    protected function active (string $plugin) {
        if ($this->JsonPlugin->where("id", $plugin)->exists()) {
            return $this->JsonPlugin
                ->where("id", $plugin)
                ->update([
                    "shutdown" => false
                ]);
        }
        return 404;
    }

    /**
     * @param string $plugin
     * @return bool|false
     */
    protected function upload (string $plugin): bool {
        if ( $this->PluginDir . $plugin ) {
            return setcookie("ufo-install-wizard-plugin", $plugin, time() + (60 * 15), "/");
        } else {
            return false;
        }
    }

    /**
     * @return bool|array
     */
    protected function install_process ( ) {
        /**
         * @var bool|array $result
         * @var string $plugin
         */
        $result = false;

        if (isset($_COOKIE["ufo-install-wizard-plugin"]) || isset($_COOKIE["ufo-update-wizard-plugin"])) {

            /**
             * Check the parameters
             *
             * @Cookie
             * @POST
             * @ZipFile
             */
            if (!isset($_POST["step"]))
                return false;

            /**
             * Plugin file
             */
            $plugin = $this->PluginDir . (
                $_COOKIE["ufo-install-wizard-plugin"] ?? $_COOKIE["ufo-update-wizard-plugin"]
            );

            /**
             * Check
             */
            if (!file_exists($plugin))
                return false;

            /**
             * Actions
             */
            switch ($_POST["step"]) {
                case "unzip":
                    $result = $this->unzip_process($plugin);
                    break;
                case "install":
                    $result = $this->installing($this->FloatDir, $plugin);
                    break;
            }

        }

        return $result;
    }

    /**
     * @param $plugin
     * @return bool
     */
    protected function unzip_process ($plugin): bool {
        global $ufo;

        /**
         * Delete old data
         */
        if (is_dir($this->FloatDir))
            $ufo->delete_folder($this->FloatDir);

        if (mkdir($this->FloatDir))
            return $ufo->unzip($plugin, $this->FloatDir);

        return false;
    }

    /**
     * @param string $plugin
     * @param string $pluginZip
     * @return array
     */
    protected function installing (string $plugin, string $pluginZip): array {
        global $ufo;

        /**
         * Set Variables
         *
         * @var array $folders
         * @var array $plugins
         */
        $folders = $ufo->all_folders($plugin);
        $plugins = [];

        /**
         * In-depth review of plugin information to install or update
         */
        foreach ($folders as $items) {
            $mode = "install";

            /**
             * Check Exists Manifest
             */
            if (file_exists($ufo->slash_folder($items . "/manifest.json"))) {

                /**
                 * Json Manager
                 */
                $manifest = new UFO_Json($ufo->slash_folder($items . "/manifest.json"));
                $manifest = $manifest->get();

                /**
                 * Check Parameters In Manifest
                 */
                $important_manifest = ["name", "icon", "version"];
                if ($ufo->has_in_array($important_manifest, $manifest)) {

                    if (isset($manifest["id"])) {
                        $oldVersion = $this->JsonPlugin->where("id", $manifest["id"])->get();
                        if (isset($oldVersion[0])) {
                            if ($manifest["version"] != $oldVersion[0]["version"])
                                $mode = "update";
                        }
                    }

                    /**
                     * Check if the plugin is installed and IF (not update) not reinstall
                     */
                    if (empty($this->JsonPlugin->where("name", $manifest["name"])->get()) || $mode == "update") {

                        $pathName = pathinfo($items)["filename"];

                        /**
                         * Transform extract
                         */
                        if ($ufo->file_copy($items . "/", $this->PluginDir . $pathName)) {
                            $plugins[$manifest["name"]] = [
                                "id"   => $manifest["id"] ?? "0x" . $ufo->hash_generator("md5"),
                                "name" => $manifest["name"],
                                "icon" => $manifest["icon"],
                                "version" => $manifest["version"],
                                "description" => $manifest["description"] ?? "",
                                "mode" => $mode,
                                "path" => $pathName,
                                "document" => $ufo->tag("a", $ufo->lng("view"), [
                                    "href"   => $manifest["document"] ?? "",
                                    "target" => "_blank"
                                ])
                            ];
                        } else {
                            /**
                             * Add error for this plugin
                             */
                            $plugins[$manifest["name"]] = [
                                "error" => $ufo->lng($ufo->equal($mode, "install") ?
                                    "Installation error" : "Update error"
                                )
                            ];
                        }

                    } else {
                        /**
                         * Add error for this plugin
                         */
                        $plugins[$manifest["name"]] = [
                            "error" => $ufo->lng($ufo->equal($mode, "install") ?
                                "This plugin is already installed" : "This plugin does not have the necessary items to update"
                            )
                        ];
                    }

                } else {
                    /**
                     * Add error for this plugin
                     */
                    $plugins[pathinfo($items)["filename"]] = [
                        "error" => $ufo->lng($ufo->equal($mode, "install") ?
                            "This plugin does not have the necessary items to install" : "This plugin does not have the necessary items to update"
                        )
                    ];
                }

            } else {
                /**
                 * Add error for this plugin
                 */
                $plugins[pathinfo($items)["filename"]] = [
                    "error" => $ufo->lng($ufo->equal($mode, "install") ?
                        "This plugin does not have the necessary items to install" : "This plugin does not have the necessary items to update"
                    )
                ];
            }
        }

        /**
         * IF - there is no error, the plugin will be installed
         */
        foreach ($plugins as $k => $items) {
            if (!isset($items["error"])) {
                $this->add_plugin($items["name"], $items["id"], $items["version"], $items["path"], $items["mode"]);
                unset($plugins[$k]["path"]);
            }
        }

        /**
         * Delete float dir & plugin file
         */
        $ufo->delete_folder($this->FloatDir);
        $ufo->delete_file($pluginZip);

        /**
         * Return Result
         */
        return $plugins;
    }

    /**
     * @return void
     */
    protected function src ( ) {
        global $ufo;

        $ufo->add_script("wizard", ASSETS . "script/wizard.js");

        $ufo->add_localize_script("ufo_data", "admin_url", URL_ADMIN);
    }

    /**
     * @return void
     * @throws Exception
     */
    protected function wizard ( ) {
        global $ufo;

        if ($ufo->match_page("ufo-plugin-wizard")) {
            $ufo->add_localize_script("ufo_data", "url_admin", $ufo->admin_url());
            $ufo->add_array("ufo_body_class", "ufo-wizard");
            $this->src();
        }
    }

    /**
     * @param string $name
     * @param string $id
     * @param string $version
     * @param string $path
     * @param string $mode
     * @return void
     */
    protected function add_plugin (string $name, string $id, string $version, string $path, string $mode = "install") {
        if ( $mode == "install" ) {
            $this->JsonPlugin->where("name", $name)->push([
                "id"       => $id,
                "name"     => $name,
                "version"  => $version,
                "path"     => $path,
                "shutdown" => true
            ], true);
        } elseif ( $mode == "update" ) {
            $this->JsonPlugin->where("id", $id)->update([
                "id"       => $id,
                "name"     => $name,
                "version"  => $version,
                "path"     => $path
            ]);
        }
    }

    /**
     * @param string $id
     * @return bool|string
     */
    protected function uninstall (string $id) {
        global $ufo, $_;

        /**
         * Get plugin
         */
        $plugin = $this->get($id);

        /**
         * Hook variable
         */
        $_["ufo_process_uninstall"] = false;

        /**
         * Check exists
         */
        if ( !empty($plugin) ) {

            /**
             * Get uninstall file plugin
             */
            if ( isset($plugin["manifest"]["actions"]["uninstall"]) ) {

                /**
                 * Rewrite address
                 */
                $uninstall = $plugin["path"] . $plugin["manifest"]["actions"]["uninstall"] . ".php";

                /**
                 * Check
                 */
                if ( file_exists($uninstall) ) {

                    /**
                     * Run process uninstall plugin
                     */
                    require $uninstall;

                } else $_["ufo_process_uninstall"] = true;

            } else $_["ufo_process_uninstall"] = true;

            if ( $_["ufo_process_uninstall"] ) {

                /**
                 * Delete plugin directory
                 */

                if ( $ufo->delete_folder($plugin["path"]) ) {

                    /**
                     * Delete plugin item
                     */
                    $_["ufo_process_uninstall"] = $this->JsonPlugin->where("id", $id)->remove();

                    /**
                     * Delete tasks
                     */
                    $_["ufo_process_uninstall"] = $this->remove_task($id);

                }

            }

        }

        return $_["ufo_process_uninstall"];
    }

    /**
     * @param string $id
     * @return bool|string
     */
    protected function remove_task (string $id) {
        global $admin_folder;
        return (new UFO_Json($admin_folder . "content/cache/tasks.json"))->where("plugin", $id)->remove();
    }

}