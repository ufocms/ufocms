<?php

/**
 * Copyright (c) 2022-2024 UFOCMS
 *
 * This software is licensed under the GPLv3 license.
 * See the LICENSE file for more information.
 */

final class UFO_Template {

    protected $mode = "run";
    protected $template = false;

    public bool $important = true;

    protected $jsonFILE   = "";
    protected $baseFolder = "";
    protected $jsonTheme  = [];
    protected array $theme_running = [];
    protected string $themeFOLDER  = "";
    protected $FloatDir = "";

    /**
     * @param string $mode
     * @param bool|string $template
     * @throws Exception
     */
    public function __construct (string $mode = "run", $template = false) {
        global $ufo, $db, $admin_folder;

        $this->mode = $mode;
        $this->template = $template;

        $this->jsonFILE   = $ufo->slash_folder($admin_folder . "content/cache/templates.json");
        $this->baseFolder = THEMES;
        $this->FloatDir   = $ufo->slash_folder($this->baseFolder . "../private/ufo_process_extract_template");

        /**
         * Json Config
         */
        $this->jsonTheme  = new UFO_Json($this->jsonFILE);

        /**
         * If the template folder does not exist,
         * remove the template from the templates JSON file
         */
        foreach ($this->jsonTheme->get() as $value)
            if (!is_dir("$this->baseFolder$value[path]")) {
                /**
                 * Remove this template from templates.json
                 */
                $this->jsonTheme->where("id", $value["id"])->remove();

                /**
                 * Update the meta theme to the active theme
                 */
                if ($ufo->equal($value["id"], $db->theme)) {
                    $activeTheme = $ufo->find_by_kv([
                        "set", true, "id"
                    ], $this->jsonTheme->get());

                    $db->update_meta("theme", $activeTheme === false ? "default" : $activeTheme);
                }
            }

        /**
         * Add default works
         */
        $this->add_works();

        $this->limit_run();
    }

    /**
     * @return void
     * @throws Exception
     */
    public function front_init ( ) {
        global $_;
        if (!isset($_["ufo_prevent_process_front"]))
            $this->set_theme_run();
    }

    /**
     * @return void
     * @throws Exception
     */
    public function admin_init ( ) {
        global $ufo, $db, $_, $admin_folder;

        $this->set_theme_run(false);

        $permissions = $this->manifest("permissions");

        if ($permissions) {
            if (in_array("admin", $permissions) && (
                !$ufo->isset_key($ufo->prevent_ajax(), "theme") ||
                in_array("prevent_ajax", $permissions)
            )) {
                $admin_file = $ufo->slash_folder($this->theme_running["path"] . "admin.php");
                if (file_exists($admin_file))
                    require $admin_file;
            }
        }
    }

    /**
     * @return void
     * @throws Exception
     */
    public function ajax_front ( ) {
        global $ufo, $db, $admin_folder, $_;

        if (!defined("AJAX_FRONT"))
            return;

        /**
         * Theme init
         */
        $this->set_theme_run(false);

        /**
         * Add default ajax
         */
        $this->default_ajax();

        if ($this->manifest("ajax")) {
            $ajax_file = $this->themeFOLDER . $this->manifest("ajax") . ".php";
            if (is_file($ajax_file))
                require $ajax_file;
        }

        $ufo->fire("ufo_theme_ajax_setup");
    }

    /**
     * @return void
     */
    protected function limit_run ( ) {
        global $ufo, $_;

        if ($ufo->isset_post("callback")) {
            if (
                $ufo->equal($_POST["callback"], "ufo-install-wizard-plugin") ||
                $ufo->equal($_POST["callback"], "ufo-update-wizard-plugin")
            ) $_["ufo_prevent_autorun_template"] = true;

            if (
                $ufo->equal($_POST["callback"], "ufo-install-wizard-template") ||
                $ufo->equal($_POST["callback"], "ufo-update-wizard-template")
            ) $_["ufo_prevent_autorun_template"] = true;

            if ($ufo->equal($_POST["callback"], "ufo-update-system"))
                $_["ufo_prevent_autorun_template"] = true;
        }
    }

    /**
     * @return void
     */
    protected function add_works ( ) {
        global $ufo;

        $ufo->add_work("ufo_templates", function () {
            return $this->get_themes();
        });

        $ufo->add_work("ufo_upload_template", function ($template) {
            return $this->upload($template);
        });

        $ufo->add_work("ufo_get_template", function ( $id ) {
            return $this->get_theme($id);
        });

        $ufo->add_work("ufo_active_template", function ( $args ) {
            extract($args); return $this->set($template, $mode);
        });

        $ufo->add_work("ufo_shutdown_template", function ( $template ) {
            return $this->unset($template);
        });

        $ufo->add_work("ufo_delete_template", function ( $id ) {
            return $this->delete($id);
        });
    }

    /**
     * @return array|mixed
     * @throws Exception
     */
    protected function get_themes ( ) {
        global $ufo;

        /**
         * JSON Config
         */
        $this->jsonTheme->reset();
        $templates = $this->jsonTheme->get();

        /**
         * Loop for add data
         */
        foreach ($templates as $k => $item) {
            /**
             * Address
             */
            $templates[$k]["path"] = $ufo->slash_folder($this->baseFolder . $item["path"] . "/");
            $templates[$k]["link"] = $ufo->sanitize_link($ufo->web_link() . "content/theme/$item[path]/");
            $templates[$k]["id"]   = $item["id"];

            /**
             * Check exists manifest
             */
            if (!file_exists($templates[$k]["path"] . "manifest.json"))
                unset($templates[$k]);
            else {
                /**
                 * Manifest
                 */
                $templates[$k]["manifest"] = json_decode(file_get_contents($templates[$k]["path"] . "manifest.json"), true);

                $important_manifest = ["name", "icon", "version"];
                if (!$ufo->has_in_array($important_manifest, $templates[$k]["manifest"]))
                    unset($templates[$k]);
            }
        }

        return $templates;
    }

    /**
     * @param $id
     * @return array|mixed
     * @throws Exception
     */
    protected function get_theme ($id) {
        $result = [];
        foreach ($this->get_themes() as $item) {
            if ($item["name"] == $id || $item["id"] == $id)
                $result = $item;
        }
        return $result;
    }

    /**
     * @param bool $run
     * @param bool $recursive (Safe recursive call)
     * @return void
     * @throws Exception
     */
    protected function set_theme_run (bool $run = true, bool $recursive = false) {
        global $ufo, $db;

        $debug = $db->debug;

        try {
            if (isset($_SESSION["ufo_theme"]) || isset($_COOKIE["ufo_theme"]) || $this->template) {
                /**
                 * Run the custom theme
                 */

                $theme  = !$this->template ? ($_COOKIE["ufo_theme"] ?? $_SESSION["ufo_theme"]) : $this->template;
                $exists = $this->jsonTheme->where("id", $theme)->get();

                if (isset($exists[0])) {
                    $exists = $exists[0];
                    if (!is_dir($this->baseFolder . $exists["path"]))
                        $exists = false;
                }

                if ((isset($exists["multi"]) && $exists["multi"]) || $this->template) {
                    /**
                     * Set template
                     */
                    $this->theme_running = $this->get_theme($theme);

                    if (isset($this->theme_running["path"]))
                        $this->themeFOLDER = $this->theme_running["path"];

                    if ($run) $this->run();
                } else {
                    /**
                     * Prevent run in multi
                     */
                    $ufo->unset_session("ufo_theme");
                    $ufo->unset_cookie("ufo_theme");

                    if (!$recursive)
                        $this->set_theme_run($run, true);
                }

            } else {
                /**
                 * Set Template
                 */
                $this->theme_running = $this->get_theme($db->meta("theme"));
                if (isset($this->theme_running["path"])) {
                    $this->themeFOLDER = $this->theme_running["path"];
                    $this->run($run);
                } else {
                    if ($run) {
                        $this->important = false;
                        $this->run();
                    }
                }
            }
        } catch (Exception $e) {
            if ($ufo->success($debug))
                (new UFO_Logs())->exception_handler($e, $debug);
            else $ufo->error($ufo->lng(
                "Error executing template"
            ));
        }
    }

    /**
     * @return false
     * @throws Exception
     */
    protected function run ($run = true): bool {
        global $ufo, $db, $_;

        $_["this_template"] = $this->theme_running;

        $this->set_language();

        /**
         * Autorun
         */
        $autorun = ($this->manifest("autorun") ? $this->manifest("autorun") . ".php" : "autoload.php");
        $pathAutorun = ($this->theme_running["path"] ?? "") . $autorun;

        if ($run) {
            $OS = (new UFO_OS());
            $OS->set_start_memory();
        }

        if (is_file($pathAutorun)) {
            /**
             * Run autorun
             */
            $check_mode = in_array($this->mode, ["debug", "tasks"]);
            if (defined("FRONT") || $check_mode) {
                if ($this->theme_running["set"] || $this->theme_running["multi"] || isset($_SESSION["ufo_theme_admin_preview"]) || $check_mode) {
                    if (!isset($_["ufo_prevent_autorun_template"])) {
                        require $pathAutorun;
                        ob_clean();
                    }
                }
            }
        } else if ($this->important)
            $this->error("$autorun " . $ufo->lng("Not found in your template!"));

        if (!$run)
            return false;

        if ($this->mode == "run") {
            /**
             * Prevent default script front
             */
            if (!isset($_["ufo_prevent_front_script"])) {
                $this->add_script();
                $this->localize_script();
            }

            /**
             * Run float page
             */
            $ufo->do_work("ufo_do_float_page");

            /**
             * Render all pages
             */
            $this->rules();
            $this->render_pages();
        }

        if (!empty($this->theme_running)) {
            $OS->set_mem_usage();

            $OS->set_end_memory('End run template (' . $this->theme_running["manifest"]["name"] . ')');

            $this->theme_running["memory_usage"] = $OS->get_memory_info();

            if ($this->theme_running["memory_usage"]["percent"] > $ufo->get_package()["config"]["themes"]["limit_memory_usage"]) {
                $ufo->add_log(
                    str_replace("%n", $this->theme_running["manifest"]["name"], $ufo->lng("Template %n")),
                    str_replace("%n", $this->theme_running["manifest"]["name"], $ufo->lng("Template %n has been turned off due to overuse of RAM")),
                    "danger"
                );
            }

            return true;
        }

        return false;
    }

    /**
     * @return void
     * @throws Exception
     */
    protected function rules ( ) {
        global $ufo, $db;

        try {
            $ufo->add_rule($this->page("home"), "/");

            if ($ufo->this_page())
                $ufo->add_rule($this->page("page"), "/" . $ufo->this_page()["link"]);

            if ($ufo->this_article())
                $ufo->add_rule($this->page("article"), "/" . $db->slug("blog") . "/" . $ufo->this_article()["link"]);

            $this->forms();
            $this->wizard();
        } catch (Exception $e) {
            $this->error($e);
        }
    }

    /**
     * @return void
     * @throws Exception
     */
    protected function render_pages ($fn = null) {
        global $ufo, $db, $_, $admin_folder;

        ob_start([$this, "renderHTML"]);

        if (!isset($_["ufo_clear_body"])) {
            $exit = false;
            $urn  = explode("?", $ufo->this_urn())[0];

            foreach ($ufo->get_full_rules() as $rule) {
                /**
                 * Search and check compliance with this link
                 */
                if (preg_match('~^' . $rule["rule"] . '$~i', $urn, $params)) {
                    if (!empty($rule["title"]))
                        $_["title"] = $rule["title"];

                    /**
                     * Full file Address
                     */
                    $file = "$rule[path].php";

                    /**
                     * Check exists File
                     */
                    if (file_exists($file)) {
                        /**
                         * Run this callback rule
                         */
                        $ufo->call($rule["callback"]);

                        /**
                         * Run file
                         */
                        require $file;
                    } else {
                        if ($ufo->is_function($fn)) $fn();
                        require $this->page(404) . ".php";
                    }

                    $exit = $rule;
                    break;
                }
            }

            if (!$exit)
                require $this->page(404) . ".php";
        } else {
            $ufo->header();

            /**
             * Replace the body
             */
            if (is_string($_["ufo_clear_body"]))
                echo $_["ufo_clear_body"];

            $ufo->footer();
        }

        ob_get_flush();
    }

    /**
     * @param $buffer
     * @return string
     */
    protected function renderHTML ($buffer): string {
        global $ufo, $db;

        if ($db->minify_html == "true")
            $buffer = $ufo->minify_html($buffer);

        /**
         * Customize the buffer with another script
         */
        foreach ($ufo->fire("ufo_front_render_html", $buffer) as $item)
            if (is_string($item))
                $buffer = $item;

        return $ufo->run_shortcodes($buffer);
    }

    /**
     * @param $file
     * @return string
     */
    protected function page ($file): string {
        global $ufo, $admin_folder;
        if (is_file($this->themeFOLDER . "$file.php") && defined("FRONT") && (($this->theme_running["set"] || $this->theme_running["multi"]) || isset($_SESSION["ufo_theme_admin_preview"])))
            return $ufo->slash_folder($this->themeFOLDER . $file);
        return $ufo->slash_folder($admin_folder . "layout/front/$file");
    }

    /**
     * Manifest {
     *   ...,
     *   "config": {
     *      ...,
     *      "forms": {...}
     *   }
     * }
     *
     * @throws Exception
     * @return void
     */
    protected function forms () {
        global $ufo, $admin_folder;

        $forms = array_values($ufo->get_array("ufo_forms"));
        $merged_forms = [];

        foreach ($forms as $info) {
            foreach ($info as $key => $form)
                $merged_forms[$key] = $form;
        }

        $forms = array_merge($merged_forms,
            $this->manifest("config")["forms"] ?? []
        );
        $file  = $admin_folder . "layout/front/forms";

        if ($ufo->file_exists_theme("forms"))
            $file = $ufo->theme_path() . "forms";

        foreach ($forms as $form => $info) {
            $ufo->add_rule($ufo->slash_folder($file), "/$info[slug]", $info["title"] . " - " . $ufo->this_title(), $ufo->fn(function ($form) {
                global $_; $_["ufo_this_form"] = $form;
            }, $form));
        }
    }
    
    /**
     * @return void
     */
    private function add_script ( ) {
        global $ufo;

        $ufo->add_script("jquery", ASSETS . "script/jquery.min.js", null, "top");
        $ufo->add_script("options", ASSETS . "script/options.js", "jquery", "top");
        $ufo->add_script("front", ASSETS . "script/front.js");
    }

    /**
     * @return void
     * @throws Exception
     */
    private function localize_script ( ) {
        global $ufo;

        $ufo->add_localize_script("ufo_data", "web_url", URL_WEBSITE);

        if ( $ufo->check_login_admin() && (isset($_COOKIE["ufo_theme"]) || isset($_SESSION["ufo_theme"])))
            $ufo->add_localize_script("ufo_data", "preview", true);

        if (!isset($ufo->manifest_theme()["config"]["alert"]))
            $ufo->add_source('window.alert = text => $.ufo_dialog({title: text,options:{okText:"' . $ufo->lng("Ok") . '"}})');
    }

    /**
     * @param $prop
     * @return false|mixed
     */
    protected function manifest ($prop) {
        return $this->theme_running["manifest"][$prop] ?? false;
    }

    /**
     * @param string $template
     * @return bool
     */
    protected function upload (string $template): bool {
        if ($this->themeFOLDER . $template)
            return setcookie("ufo-install-wizard-template", $template, time() + (60 * 15), "/");
        return false;
    }

    /**
     * @return bool|array
     */
    protected function install_process ( ) {
        /**
         * @var bool|array $result
         * @var string $theme
         */

        $result = false;

        if (isset($_COOKIE["ufo-install-wizard-template"]) || isset($_COOKIE["ufo-update-wizard-template"])) {

            /**
             * Check the parameters
             *
             * @Cookie
             * @POST
             * @ZipFile
             */
            if (!isset($_POST["step"])) return false;

            /**
             * Template file
             */
            $theme = $this->baseFolder . ($_COOKIE["ufo-install-wizard-template"] ?? $_COOKIE["ufo-update-wizard-template"]);

            /**
             * Check
             */
            if (!file_exists($theme)) return false;

            /**
             * Actions
             */
            switch ($_POST["step"]) {
                case "unzip":
                    $result = $this->unzip_process($theme);
                    break;
                case "install":
                    $result = $this->installing($this->FloatDir, $theme);
                    break;
            }

        }

        return $result;
    }

    /**
     * @param $template
     * @return bool
     */
    protected function unzip_process ($template): bool {
        global $ufo;

        if (is_dir($this->FloatDir))
            $ufo->delete_folder($this->FloatDir);

        if (mkdir($this->FloatDir))
            return $ufo->unzip($template, $this->FloatDir);

        return false;
    }

    /**
     * Install and Update process
     *
     * @param string $templates
     * @param string $themeZip
     * @return array
     */
    protected function installing (string $templates, string $themeZip): array {
        global $ufo;

        /**
         * Set variables
         *
         * @var array $folders
         * @var array $templates
         */
        $folders   = $ufo->all_folders($templates);
        $templates = [];

        /**
         * In-depth review of template information to install or update
         */
        foreach ($folders as $items) {
            $mode = "install";

            /**
             * Check exists manifest
             */
            if (file_exists($ufo->slash_folder($items . "/manifest.json"))) {

                /**
                 * Json manager
                 */
                $manifest = new UFO_Json($ufo->slash_folder($items . "/manifest.json"));
                $manifest = $manifest->get();

                /**
                 * Check parameters in manifest
                 */
                $important_manifest = ["name", "icon", "version"];
                if ($ufo->has_in_array($important_manifest, $manifest)) {

                    if (isset($manifest["id"])) {
                        $oldVersion = $this->jsonTheme->where("id", $manifest["id"])->get();
                        if (isset($oldVersion[0])) {
                            if ($manifest["version"] != $oldVersion[0]["version"])
                                $mode = "update";
                        }
                    }

                    /**
                     * Check if the template is installed and IF (not update) not reinstall
                     */
                    if (empty($this->jsonTheme->where("name", $manifest["name"])->get()) || $mode == "update") {

                        $pathName = pathinfo($items)["filename"];

                        /**
                         * Transform extract
                         */
                        if ($ufo->file_copy($items . "/", $this->baseFolder . $pathName)) {

                            $templates[$manifest["name"]] = [
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
                             * Add error for this theme
                             */
                            $templates[$manifest["name"]] = [
                                "error" => $ufo->lng("Installation error")
                            ];
                        }

                    } else {
                        /**
                         * Add error for this theme
                         */
                        $templates[$manifest["name"]] = [
                            "error" => $ufo->lng($ufo->equal($mode, "install") ?
                                "This template is already installed" : "This template does not have the necessary items to update"
                            )
                        ];
                    }

                } else {
                    /**
                     * Add error for this template
                     */
                    $templates[pathinfo($items)["filename"]] = [
                        "error" => $ufo->lng($ufo->equal($mode, "install") ?
                            "This template does not have the necessary items to install" : "This template does not have the necessary items to update"
                        )
                    ];
                }

            } else {
                /**
                 * Add error for this template
                 */
                $templates[pathinfo($items)["filename"]] = [
                    "error" => $ufo->lng($ufo->equal($mode, "install") ?
                        "This template does not have the necessary items to install" : "This template does not have the necessary items to update"
                    )
                ];
            }
        }

        /**
         * IF - there is no error, the template will be installed
         */
        foreach ($templates as $k => $items) {
            if (!isset($items["error"])) {
                $this->add_template($items["id"], $items["name"], $items["version"], $items["path"], $items["mode"]);
                unset($templates[$k]["path"]);
            }
        }

        /**
         * Delete float dir & template file
         */
        $ufo->delete_folder($this->FloatDir);
        $ufo->delete_file($themeZip);

        /**
         * Return Result
         */
        return $templates;
    }

    /**
     * @param string $id
     * @param string $name
     * @param string $version
     * @param string $path
     * @param string $mode
     * @return void
     */
    protected function add_template ( string $id, string $name, string $version, string $path = "", string $mode = "install" ) {
        if ($mode == "install") {
            $this->jsonTheme->where("name", $name)->push([
                "id"      => $id,
                "name"    => $name,
                "version" => $version,
                "path"  => $path,
                "set"   => false,
                "multi" => false
            ], true);
        } elseif ($mode == "update") {
            $this->jsonTheme->where("id", $id)->update([
                "id" => $id,
                "name" => $name,
                "version" => $version,
                "path" => $path
            ]);
        }
    }

    /**
     * @return void
     */
    protected function wizard ( ) {
        global $ufo;

        $ufo->add_rule(LAYOUT . "external/template-wizard", "/ufo-template-wizard");

        if ( $ufo->match_page("ufo-template-wizard") ) {
            $ufo->add_array("ufo_body_class", "ufo-wizard");
            $this->src();
        }
    }

    /**
     * @param $template
     * @return false|int|string
     * @throws Exception
     */
    protected function unset ( $template ) {
        global $db;
        if ($this->jsonTheme->where("id", $template)->exists()) {

            $template = $this->jsonTheme->where("id", $template)->get()[0] ?? [];

            if (!empty($template)) {
                if ($template["id"] == $db->meta("theme"))
                    $db->update_meta("theme", "");
            }

            /**
             * Delete task template
             */
            $this->remove_task($template["id"]);

            return $this->jsonTheme
                ->where("id", $template)
                ->update([
                    "set" => false,
                    "multi" => false
                ]);

        }

        return false;
    }

    /**
     * @param $template
     * @param string $mode
     * @return bool|int|string
     * @throws Exception
     */
    protected function set ( $template, string $mode = "set" ) {
        global $db;

        $template = $this->get_theme($template);

        if (!empty($template) && ($mode == "set" || $mode == "multi")) {
            $result = $this->jsonTheme
                ->where("id", $template["id"])
                ->update([
                    "set"   => true,
                    "multi" => ($mode == "multi")
                ]);
            if ($mode == "set")
                $result = $db->update_meta("theme", $template["id"]);
        } else
            $result = false;

        return $result;
    }

    /**
     * @param string $id
     * @return bool|string
     * @throws Exception
     */
    protected function delete ( string $id ) {
        global $ufo, $db, $_;

        /**
         * Get template
         */
        $template = $this->get_theme($id);

        /**
         * Hook variable
         */
        $_["ufo_process_uninstall"] = false;

        /**
         * Check exists
         */
        if ( !empty($template) ) {

            /**
             * Get uninstall file template
             */
            if ( isset($template["manifest"]["actions"]["uninstall"]) ) {

                /**
                 * Rewrite address
                 */
                $uninstall = $template["path"] . $template["manifest"]["actions"]["uninstall"] . ".php";

                /**
                 * Check
                 */
                if ( file_exists($uninstall) ) {
                    /**
                     * Run process uninstall template
                     */
                    require $uninstall;
                } else
                    $_["ufo_process_uninstall"] = true;

            } else
                $_["ufo_process_uninstall"] = true;

            if ( $_["ufo_process_uninstall"] ) {

                /**
                 * Update theme meta
                 */
                if ( $template["id"] == $db->meta("theme") )
                    $db->update_meta("theme", "");

                /**
                 * Delete template directory
                 */
                $_["ufo_process_uninstall"] = $ufo->delete_folder($template["path"]);

                if ( $_["ufo_process_uninstall"] ) {

                    /**
                     * Delete template item
                     */
                    $_["ufo_process_uninstall"] = $this->jsonTheme->where("id", $id)->remove();

                    /**
                     * Delete task
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
    protected function remove_task ( string $id ) {
        global $admin_folder;
        return (new UFO_Json($admin_folder . "content/cache/tasks.json"))->where("template", $id)->remove();
    }

    /**
     * @return void
     */
    protected function set_language ( ): void {
        global $ufo;

        if (empty($this->theme_running["path"]))
            return;

        $language = $ufo->slash_folder($this->theme_running["path"] . "/language/");

        /** Set default language */
        if (file_exists($language . LANG . ".json"))
            $ufo->add_lng($language . LANG . ".json");

        /** Set custom language */
        else if (file_exists($language . $this->manifest("lang") . ".json"))
            $ufo->add_lng($language . $this->manifest("lang") . ".json");
    }

    /**
     * @return void
     */
    protected function src ( ) {
        global $ufo;

        $ufo->add_script("wizard", ASSETS . "script/wizard.js", "jquery", "top");

        $ufo->add_localize_script("ufo_data", "admin_url", URL_ADMIN);
    }

    /**
     * @return void
     */
    protected function default_ajax ( ) {
        global $ufo;

        /**
         * Wizard installer
         */
        $ufo->add_ajax("ufo-install-wizard-template", function () {
            global $ufo;

            if (!$ufo->check_login_admin())
                $ufo->die($ufo->status(403, $ufo->lng("Access denied")));

            $install = $this->install_process();

            return $install ? $ufo->status(200, $ufo->lng("Done successfully"), [
                "info" => $install
            ]) : $ufo->status(503, $ufo->lng("System error"));
        }, true);

        /**
         * Exit preview
         */
        $ufo->add_ajax("ufo-exit-preview", function () {
            global $ufo;
            $ufo->unset_session("ufo_theme");
            $ufo->unset_session("ufo_theme_admin_preview");
            $ufo->unset_cookie("ufo_theme");
        }, true);

        /**
         * Comment ajax
         */
        $ufo->add_ajax("ufo_front_comment", function () {
            global $ufo, $db;

            $status = $ufo->status(403, "Access denied");
            if (!isset($_POST["action"])) $ufo->die($status);

            switch ( $_POST["action"] ) {
                case "submit_comment":
                    $required = ["comment", "rate", "page", "for"];

                    if (!$ufo->check_login_member()) {
                        $required[] = "name";
                        $required[] = "email";
                    }

                    if (!$ufo->has_in_array($required, $_POST))
                        $ufo->die($status);

                    $insert = $db->insert("comments", [
                        "mid" => $ufo->check_login_member() ? $ufo->get_member()["uid"] : 0,
                        "pid" => (int) ($ufo->is_bas64($_POST["page"]) ? base64_decode($_POST["page"]) : $_POST["page"]),
                        "guest"    => !$ufo->check_login_member() ? json_encode(["name" => $_POST["name"], "email" => $_POST["email"]], JSON_UNESCAPED_UNICODE) : "",
                        "comment"  => $db->sanitize_string($_POST["comment"]),
                        "dateTime" => $ufo->dateTime(),
                        "rate"     => (int) $_POST["rate"],
                        "_for"     => $_POST["for"],
                        "_reply"   => (int) (isset($_POST["reply"]) ? ($ufo->is_bas64($_POST["reply"]) ? base64_decode($_POST["reply"]) : $_POST["reply"]) : 0),
                        "accept"   => $db->meta("accept_comment") == "true" ? 1 : 0
                    ]);

                    if ( $insert ) {
                        if ( $db->meta("accept_comment") == "false" ) {
                            $status = $ufo->status(100, $ufo->lng("Your comment will be displayed after approval"));
                        } else {
                            $status = $ufo->status(200, $ufo->lng("Your comment has been posted"));
                        }
                    } else {
                        $status = $ufo->status(503, $ufo->lng("System error"));
                    }
                    break;
                case "load_comments":
                    if (
                        !$ufo->isset_key($_POST, "p") ||
                        !$ufo->isset_key($_POST, "ppage")
                    ) $ufo->die($status);

                    $ufo->load_layout("front/comments", true, ".php", [
                        "pid"   => (int) ($ufo->is_bas64($_POST["p"]) ? base64_decode($_POST["p"]) : $_POST["p"]),
                        "page"  => (int) $_POST["ppage"]
                    ]);

                    return false;
            }

            $ufo->die($status);
        }, true);

        /**
         * Forms
         */
        $ufo->add_ajax("ufo_form", function () {
            global $ufo;

            $status = $ufo->status(403, $ufo->lng("Access denied"));

            if (!$ufo->isset_post("fields"))
                $ufo->die($status);

            if (!is_array($_POST["fields"]))
                $ufo->die($status);

            $form = $ufo->this_url_info()["slashes"][0];
            $fire = $ufo->fire("ufo_submit_" . $form . "_form", $form);
            $fire = end($fire);

            if ($ufo->is_json($fire))
                $ufo->die($fire);

            $ufo->die($ufo->status(503, $ufo->lng("System error")));
        }, true);
    }

    /**
     * @param $content
     * @return void
     * @throws Exception
     */
    protected function error ( $content ) {
        global $ufo;
        $ufo->die($ufo->error($content, "", false, true));
    }

}
