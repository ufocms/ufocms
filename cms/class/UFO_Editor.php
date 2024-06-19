<?php

/**
 * Copyright (c) 2022-2024 UFOCMS
 *
 * This software is licensed under the GPLv3 license.
 * See the LICENSE file for more information.
 */

final class UFO_Editor {

    protected array $widget_script = [];
    protected array $widget_style  = [];
    protected array $widgets       = [];
    protected array $shortcodes    = [];

    /**
     * Init
     * @throws Exception
     */
    public function init () {
        global $ufo;

        $ufo->add_work("ufo_page_editor_save", function ($param) {
            return $this->save($param);
        });
        $ufo->add_work("ufo_page_editor_update", function ($param) {
            return $this->update($param);
        });
        $ufo->add_work("ufo_page_editor_get", function ($id) {
            return $this->get($id);
        });

        $ufo->add_work("ufo_editor_run_script_page", function ($pid) {
            return $this->runScript($pid);
        });

        $this->init_page_builder();
    }

    /**
     * Editor run process
     * @throws Exception
     */
    private function init_page_builder () {
        global $ufo, $db, $_;

        if (defined("FRONT")) {
            /**
             * Add Editor Link
             */
            $ufo->add_rule(LAYOUT . "external/ufo-editor", $ufo->sanitize_link("/" . $ufo->admin_path() . "ufo-editor"));
        }

        /**
         * Load All UFO Editor Widgets
         */
        require $ufo->slash_folder('widgets/autoload.php');

        if ($ufo->match_page($ufo->admin_path() . "ufo-editor")) {
            $_["ufo_prevent_autorun_template"] = true;
            $_["ufo_prevent_front_script"] = true;
            $_["title"] = $ufo->lng("Page Builder");

            /**
             * Check Security
             */
            if (!$ufo->check_login_admin())
                $ufo->redirect($db->meta("web_url"));

            /**
             * When Full Run Front
             */
            $ufo->do_work("ufo_theme_setup", function () {
                global $ufo;

                /**
                 * Set Default Localize
                 */
                $ufo->do_work("ufo_set_default_localize");
                $ufo->add_localize_script("ufo_info", "widgets", $ufo->web_link() . "float/ufo_all_editor_widgets");

                /**
                 * Add Resource
                 */
                $this->add_style();
                $this->add_script();

                /**
                 * Add Theme
                 */
                $ufo->add_array("ufo_body_attrs", [
                    "data-theme" => "light",
                ]);
            });
        }

        if ($ufo->end_url() == "ufo_all_editor_widgets") {
            /**
             * Render Widgets
             */
            $this->init_widgets();
            $this->init_shortcodes();
        }

        /**
         * Add Float Data Widgets
         */
        $this->float_data();
    }

    /**
     * Add Style
     */
    private function add_style () {
        global $ufo, $db;

        $ufo->clear_style();

        foreach ($this->widget_style as $v)
            $ufo->add_style($v);

        $ufo->add_style(ASSETS . "libs/popupwindow/popupwindow.min.css");
        $ufo->add_style(ASSETS . "libs/codemirror/addon/all.min.css");

        $ufo->fire("ufo-editor-style");

        $ufo->add_style(ASSETS . "css/editor.css");
    }

    /**
     * Add Scripts
     */
    private function add_script () {
        global $ufo;

        $ufo->clear_script();

        $ufo->add_script("jquery", ASSETS . "script/jquery.min.js", null, "top");

        $ufo->add_script("options", ASSETS . "script/options.js", "jquery", "top");

        $ufo->add_script("popupwindow", ASSETS . "libs/popupwindow/popupwindow.min.js", "jquery", "top");

        $this->codemirror();

        foreach ($this->widget_script as $k => $v)
            $ufo->add_script($k, $v, "jquery");

        $ufo->fire("ufo-editor-script");

        $ufo->add_script("ufo-editor", ASSETS . "script/editor.js");

        $ufo->add_script("ufo", ASSETS . "script/ufo.js");
    }

    /**
     * CodeMirror Library
     */
    private function codemirror () {
        global $ufo;

        $ufo->add_script("codemirror", ASSETS . "libs/codemirror/codemirror.js", null, "top");
        $ufo->add_script("codemirror", ASSETS . "libs/codemirror/mode/all.min.js", "codemirror", "top");
        $ufo->add_script("codemirror", ASSETS . "libs/codemirror/addon/hint.js", "codemirror", "top");
        $ufo->add_script("codemirror", ASSETS . "libs/codemirror/addon/edit.js", "codemirror", "top");
        $ufo->add_script("codemirror", ASSETS . "libs/codemirror/addon/fold.js", "codemirror", "top");
    }

    /**
     * Widgets process
     */
    private function init_widgets () {
        global $ufo;

        /**
         * Get All Widgets
         */
        $widgets = $ufo->get_array("ufo_editor_widgets");

        /**
         * Loop for add to $this->widgets
         */
        foreach ($widgets as $items) {
            /**
             * Add Style
             */
            if (!empty($items["style"]))
                $this->widget_style[] = $items["style"];

            /**
             * Add Script
             */
            if (!empty($items["script"])) {
                if (isset($items["script"]["src"]) && isset($items["script"]["name"]) && !empty($items["script"]["name"])) {
                    $this->widget_script[$items["script"]["name"]] = $items["script"]["src"];
                }
            }

            /**
             * Add Data Widget
             */
            $this->widgets[$items["name"]] = [
                "name"        => $items["name"],
                "title"       => $items["title"],
                "icon"        => $items["icon"],
                "type"        => $items["type"],
                "document"    => $items["document"],
                "controls"    => $items["controls"],
                "template"    => $items["template"],
                "name_script" => $items["script"]["name"] ?? null
            ];
        }
    }

    /**
     * Shortcodes process
     */
    private function init_shortcodes () {
        global $ufo;

        /**
         * Get All Shortcodes
         */
        $shortcodes = $ufo->get_all_shortcodes();

        unset($shortcodes["plugins"]);
        unset($shortcodes["templates"]);

        /**
         * Loop for add to $this->shortcodes
         */
        foreach ($shortcodes as $item) {
            if ($ufo->isset_key($item, "editor"))
                $this->shortcodes[] = $item["editor"];
        }
    }

    /**
     * Insert To Float Data
     */
    private function float_data () {
        global $ufo;
        $ufo->do_work("ufo_add_float_page", [
            "float"   => "ufo_all_editor_widgets",
            "content" => json_encode([
                "shortcodes" => $this->shortcodes,
                "widgets"    => $this->widgets
            ], JSON_UNESCAPED_UNICODE),
            "clear"   => true
        ]);
    }

    /**
     * Save
     * @throws Exception
     */
    private function save ($param = []) {
        global $db, $ufo;

        extract($param);

        $result    = json_decode($ufo->status(403, $ufo->lng("Access denied")), true); // Result return
        $important = ["title", "content", "short_desc", "photo", "link", "category", "tags", "status", "type", "script", "setting"]; // Important parameters
        $continue  = true; // Check to continue

        /**
         * Check the availability of parameters
         */
        foreach ($important as $item) {
            if (!isset($param[$item]))
                $continue = false;
        }

        /**
         * Check to continue
         */
        if (!$continue) {
            $result["status"]  = 404;
            $result["message"] = $ufo->lng("Parameters not found");
            return $result;
        }

        /**
         * Check Script
         */
        if (!is_array($script)) { $continue = false; }

        /**
         * Check page availability
         */
        if ((new UFO_Pages())->exists($title)) {
            $result["status"]  = 100;
            $result["message"] = $ufo->lng("A page with this name has already been created");
            return $result;
        }

        /**
         * Check to continue
         */
        if (!$continue) {
            $result["status"]  = 404;
            $result["message"] = $ufo->lng("Parameters not found");
            return $result;
        }

        /**
         * Insert To DB
         */
        $insert = $db->insert("pages", [
            "title"      => $title,
            "content"    => $content,
            "short_desc" => $short_desc,
            "photo"    => empty($photo) || $photo == "[]" ? "[]" : json_encode(is_array($photo) ? $photo : [$photo], JSON_UNESCAPED_UNICODE),
            "link"     => $link,
            "category" => empty($category) || $category == "[]" ? "" : json_encode($category, JSON_UNESCAPED_UNICODE),
            "tags"     => (string) $tags,
            "status"   => (int) $status,
            "type"     => (string) $type,
            "author"   => json_encode([
                "id"   => isset($from) ? ($from === "admin" ? $ufo->get_admin()["id"] : $ufo->get_member()["uid"]) : $ufo->get_admin()["id"],
                "from" => $from ?? "admin"
            ]),
            "password" => $password ?? "",
            "dateTime" => $ufo->dateTime()
        ]);

        if ($insert) {
            /**
             * Collect and store information
             */
            $Data = [
                "php"     => (string) ($script["php"] ?? ""),
                "js"      => (string) ($script["js"]  ?? ""),
                "css"     => (string) ($script["css"] ?? ""),
                "setting" => $ufo->is_json($setting) ? json_decode($setting, true) : $setting
            ];

            /**
             * Create File Data
             */
            $file = $ufo->slash_folder((defined("FRONT") ? $ufo->admin_path() : "") . _CACHE_ . "editor/" . md5($db->insert_id()) . ".ufo");
            if (file_put_contents($file, json_encode($Data, JSON_UNESCAPED_UNICODE))) {
                $result["status"]  = 200;
                $result["message"] = $ufo->lng("Done successfully");
                $result["id"]      = base64_encode($db->insert_id());
            } else {
                $result["status"]  = 503;
                $result["message"] = $ufo->lng("System error");
            }
        } else {
            $result["status"]  = 503;
            $result["message"] = $ufo->lng("System error");
        }

        return $result;
    }

    /**
     * Update
     * @throws Exception
     */
    private function update ($param = []) {
        global $db, $ufo;

        extract($param);

        $result    = json_decode($ufo->status(403, $ufo->lng("Access denied")), true); // Result return
        $important = ["page"]; // Important parameters
        $continue  = true; // Check to continue

        /**
         * Check the availability of parameters
         */
        foreach ($important as $item) {
            if (!isset($param[$item])) {
                $continue = false;
            }
        }

        /**
         * Check to continue
         */
        if (!$continue) {
            $result["status"]  = 404;
            $result["message"] = $ufo->lng("Parameters not found");
            return $result;
        }

        $oldData = (new UFO_Pages())->get((int) $page, false);

        if ($oldData) {

            /**
             * Fix page id
             */
            $key = "id";
            if (is_string($page))
                $key = "title";

            $authorID = $ufo->equal($from ?? "", "member") ? $ufo->get_member()["uid"] : $ufo->get_admin()["id"];
            $photo = $photo ?? $oldData["photo"];
            $category = $category ?? $oldData["category"];

            /**
             * Update data
             */
            $update = $db->update("pages", [
                "title"      => $title ?? $oldData["title"],
                "content"    => $content ?? $oldData["content"],
                "short_desc" => $short_desc ?? $oldData["short_desc"],
                "photo"      => is_array($photo) ? json_encode($photo) : "[]",
                "link"       => $link ?? $oldData["link"],
                "category"   => is_array($category) ? json_encode($category, JSON_UNESCAPED_UNICODE) : "[]",
                "tags"       => (string) ($tags ?? $oldData["tags"]),
                "status"     => (int) ($status ?? $oldData["status"]),
                "type"       => $type ?? $oldData["type"],
                "author"   => isset($from) ? json_encode([
                    "id"   => $authorID,
                    "from" => $from
                ]) : json_encode($oldData["author"]),
                "password" => $password ?? "",
                "dateTime" => $dateTime ?? $ufo->dateTime()
            ], $key, $page);

            if ($update) {

                $File = $ufo->slash_folder((defined("FRONT") ? $ufo->admin_path() : "") . _CACHE_ . "editor/" . md5($oldData["id"]) . ".ufo");
                $OldD = json_decode(file_get_contents($File), true);

                /**
                 * Collect and store information
                 */
                $Data = [
                    "php"     => (string) ($script["php"] ?? $OldD["php"]),
                    "js"      => (string) ($script["js"]  ?? $OldD["js"]),
                    "css"     => (string) ($script["css"] ?? $OldD["css"]),
                    "setting" => (isset($setting) && $ufo->is_json($setting)) ? json_decode($setting, true) : $OldD["setting"]
                ];

                /**
                 * Create File Data
                 */
                if (file_put_contents($File, json_encode($Data, JSON_UNESCAPED_UNICODE))) {
                    $result["status"]  = 200;
                    $result["message"] = $ufo->lng("Done successfully");
                    $result["id"]      = $page;
                } else {
                    $result["status"]  = 503;
                    $result["message"] = $ufo->lng("System error");
                }
            } else {
                $result["status"]  = 503;
                $result["message"] = $ufo->lng("System error");
            }

        } else {
            $result["status"]  = 404;
            $result["message"] = $ufo->lng("Page or article with this ID could not be found");
        }

        return $result;
    }

    /**
     * Get
     */
    private function get ($id = 0) {
        global $ufo, $admin_folder;
        $page = (new UFO_Pages())->get($id);
        if ($page) {
            $file = $ufo->slash_folder((defined("ADMIN") ? "" : $admin_folder) . _CACHE_ . "editor/" . md5($page["id"]) . ".ufo");
            if (file_exists($file) && is_file($file)) {
                return $page + json_decode(file_get_contents($file), true);
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    /**
     * @param int $pid
     * @return bool
     * @throws Exception
     */
    private function runScript (int $pid): bool {
        global $ufo;

        $page = $this->get($pid);

        if (!$page) return false;

        try {
            if ((
                preg_match("/..<?php/", $page["php"], $matches) ||
                preg_match("/..<?=/", $page["php"], $matches)
            ) && preg_match("/..?>/", $page["php"], $matches)
            ) eval("?> $page[php] <?php");

            echo $ufo->tag("style", $page["css"]);

            echo $ufo->tag("script", $page["js"]);

            return true;
        } catch (Exception $e) {
            (new UFO_Logs())->exception_handler($e, true);
        }

        return false;
    }

}
