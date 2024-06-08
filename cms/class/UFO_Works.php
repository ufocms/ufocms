<?php

/**
 * Copyright (c) 2022-2024 UFOCMS
 *
 * This software is licensed under the GPLv3 license.
 * See the LICENSE file for more information.
 */

final class UFO_Works {

    protected array $DATA = [
        "float_page"  => [],
        "member"      => [
            "columns" => []
        ]
    ];

    public function __construct () {
        global $ufo;

        /**
         * @param $string
         * @return array
         */
        $ufo->add_work("ufo_render_shortcodes", function ($string) {
            return $this->render_shortcode($string);
        });

        /**
         * @param $array
         * @return bool|string
         */
        $ufo->add_work("ufo_send_email", function ($array) {
            return $this->send_mail($array);
        });

        /**
         * @return string
         */
        $ufo->add_work("ufo_generate_nonce", function () {
            return $this->generate_ajax_key();
        });

        /**
         * @return mixed
         */
        $ufo->add_work("ufo_ajax_key", function () {
            return $this->ajax_key();
        });

        /**
         * @param $func
         */
        $ufo->add_work("ufo_setup", function ($func) {
            global $ufo, $_;
            if (!isset($_["ufo_setup"])) $_["ufo_setup"] = [];
            if ($ufo->is_function($func)) $_["ufo_setup"][] = $func;
        });

        /**
         * @param $func
         */
        $ufo->add_work("ufo_theme_setup", function ($func) {
            global $ufo, $_;
            if (!isset($_["ufo_theme_setup"])) $_["ufo_theme_setup"] = [];
            if ($ufo->is_function($func)) $_["ufo_theme_setup"][] = $func;
        });

        /**
         * Setup
         */
        $ufo->add_work("ufo_do_all_setup", function () {
            $this->do_setup();
        });
        $ufo->add_work("ufo_do_all_theme_setup", function () {
            $this->do_theme_setup();
        });

        /**
         * Reset Document
         */
        $ufo->add_work("ufo_reset_document", function ($full = false) {
            global $ufo;
            if (!$ufo->is_admin()) {
                $ufo->clear_script();
                $ufo->clear_style();
                $ufo->clear_localize_script();

                if ($full) {
                    $ufo->add_work("ufo_full_clear_document", function () {
                        return true;
                    });
                }
            }
        });

        /**
         * Get Web Title
         */
        $ufo->add_work("ufo_get_title", function () {
            global $ufo, $db, $_;

            if (isset($_["title"])) {
                return $_["title"];
            } else if (defined("ADMIN")) {
                return $ufo->lng("Management") . " - " . $db->meta("web_name");
            } else if (defined("FRONT")) {
                if ($page = $ufo->this_page(null))
                    return $page["title"];
            }

            return $db->meta("web_name");
        });

        /**
         * Localize Script
         */
        $ufo->add_work("ufo_set_default_localize", function () {
            $this->set_localize_script();
        });

        /**
         * Render Editor Widgets
         */
        $ufo->add_work("ufo_render_editor_widgets", function () {
            return $this->render_editor_widgets();
        });

        /**
         * Float Pages
         */
        $ufo->add_work("ufo_add_float_page", function ($arg) {
            return $this->add_float_page($arg["float"], $arg["content"], isset($arg["clear"]));
        });
        $ufo->add_work("ufo_do_float_page", function () {
            $this->do_float_page();
        });

        /**
         * Body Tag Attributes
         */
        $ufo->add_work("ufo_body_attrs", function () {
            global $ufo;
            $attrs = "";
            foreach ($ufo->get_array("ufo_body_attrs") as $item) {
                foreach ($item as $k => $v) {
                    $attrs .= $k . "='" . $v . "' ";
                }
            }
            return !empty($attrs) ? " " . substr($attrs, 0, -1) : "";
        });

        /**
         * All Languages
         */
        $ufo->add_work("ufo_all_languages", function () {
            return $this->get_languages();
        });
        $ufo->do_work([
            "ufo_setup", "ufo_theme_setup"
        ], function () {
            $this->language();
        });

        /**
         * Mode Comments
         */
        $ufo->add_work("ufo_mode_comments", function () {
            return [
                "Show all" => "all",
                "Confirmed" => 1,
                "Not approved" => 0
            ];
        });

        /**
         * UFO market
         */
        $ufo->add_work("ufo_market_dl", function ($param) {
            return $this->ufo_market_dl($param);
        });

        /**
         * Member table columns
         */
        $ufo->add_work("ufo_member_add_column", function ($array) {
            return $this->add_member_column($array);
        });
        $ufo->add_work("ufo_get_member_table", function () {
            return $this->member_columns();
        });
        $ufo->add_work("ufo_member_add_option", function ($array) {
            return $this->add_member_option($array);
        });

        /**
         * UFO update process
         */
        $ufo->add_work("ufo_dnv_system", function (array $args) {
            return $this->dnv_system($args);
        });

        /**
         * External page update system
         */
        $ufo->do_work("ufo_theme_setup", function () {
            $this->pnv_system();
        });
        $ufo->add_ajax("ufo-update-system", function () {
            global $ufo;
            $install = $this->nv_installer();
            return $install ? $ufo->status(200, $ufo->lng("Done successfully"), ["info" => $install]) : $ufo->status(503, $ufo->lng("System error"));
        });
    }

    /**
     * @return void
     * @throws Exception
     */
    private function language () {
        global $ufo;

        $ufo->do_work("ufo_add_float_page", [
            "float"   => "i18n",
            "content" => json_encode($ufo->all_lng(), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            "clear"   => true
        ]);

        $ufo->add_link([
            "rel"  => "preload",
            "href" => $ufo->web_link() . "float/i18n",
            "type" => "application/json",
            "as"   => "fetch",
            "crossorigin" => "anonymous"
        ]);
    }

    /**
     * @param $string
     * @return array
     */
    private function render_shortcode (string $string) {
        // Adjusted pattern to match shortcodes with or without attributes/content
        $pattern = '/\[(\w+)(.*?)\](?:([^[]*?)\[\/\1\])?/s';

        preg_match_all($pattern, $string, $matches, PREG_SET_ORDER);

        foreach ($matches as $match) {
            $shortcode  = $match[0];
            $name       = $match[1];
            $attributes = trim($match[2]);
            $content    = $match[3] ?? ""; // Content is optional

            $attributes = $attributes ? $this->parse_shortcode($attributes) : [];
            $result = $this->call_shortcode($name, $attributes, $content, $shortcode);

            // Replace the original shortcode in the text with its result
            $string = str_replace($shortcode, $result, $string);
        }

        return $string;
    }

    /**
     * @param string $attrs
     * @return array
     */
    private function parse_shortcode (string $attrs): array {
        $attributes = [];

        // Match different types of attribute values
        preg_match_all(
            '/(\w+)=("[^"]*"|\[[^\]]*\]|{[^}]*}|\d+(?:,\d+)*(?:,"[^"]*")*(?:,\d+)*)/',
            $attrs, $matches, PREG_SET_ORDER
        );

        foreach ($matches as $match) {
            $key   = $match[1];
            $value = trim($match[2], '"');

            $jsonValue = json_decode($match[2], true);

            if (json_last_error() === JSON_ERROR_NONE) {
                $value = $jsonValue;
            } elseif (strpos($value, ',') !== false) {
                $value = array_map(function($item) {
                    $trimmed = trim($item);
                    if (is_numeric($trimmed))
                        return $trimmed + 0;
                    return trim($trimmed, '"');
                }, explode(',', $match[2]));
            }

            $attributes[$key] = $value;
        }

        return $attributes;
    }

    /**
     * @param string $name
     * @param array $attrs
     * @param string $content
     * @param string $full_shortcode
     * @return string
     */
    private function call_shortcode (string $name, array $attrs, string $content, string $full_shortcode): string {
        global $ufo;

        $shortcode = $ufo->get_shortcode($name);

        if (empty($shortcode["content"]))
            return $full_shortcode;

        return $ufo->call($shortcode["content"], $attrs, $content) ?? $shortcode["content"];
    }

    /**
     * @param $array
     * @return bool|string
     */
    private function send_mail ($array) {
        global $PHPMailer;

        extract($array);

        $mail = $PHPMailer;
        $mail->IsSMTP();

        $DEFAULT = [
            "host"      => "smtp.gmail.com",
            "auth"      => true,
            "secure"    => "tls",
            "port"      => 587,
            "username"  => "example@gmail.com",
            "password"  => "",
            "from_mail" => "example@gmail.com",
            "from_name" => "example"
        ];

        try {
            $mail->Host        = $host ?? $DEFAULT["host"];
            $mail->Username    = $username ?? $DEFAULT["username"];
            $mail->Password    = $password ?? $DEFAULT["password"];
            $mail->SMTPSecure  = $secure ?? $DEFAULT["secure"];
            $mail->Port        = (int) $port ?? $DEFAULT["port"];
            $mail->SMTPAuth    = $auth ?? $DEFAULT["auth"];

            $mail->IsHTML(true);

            $mail->AddAddress($to);
            $mail->SetFrom($from_mail ?? $DEFAULT["from_mail"], $from_name ?? $DEFAULT["from_name"]);

            $mail->Subject = $subject;
            $mail->CharSet = "UTF-8";
            $mail->ContentType = "text/html";

            $mail->MsgHTML($content);

            return $mail->Send();
        } catch(\PHPMailer\PHPMailer\Exception $e) {
            $result = $e->errorMessage();
        } catch(Exception $e) {
            $result = $e->getMessage();
        }

        return $result;
    }

    /**
     * @return string
     */
    private function generate_ajax_key (): string {
        global $ufo;
        return "0x" . $ufo->hash_generator();
    }

    /**
     * @return mixed
     * @throws Exception
     */
    private function ajax_key () {
        global $db;
        return unserialize($db->meta("ajax_key"))["key"];
    }

    /**
     * When UFO core full run
     */
    private function do_setup () {
        global $ufo, $_;
        foreach ($_["ufo_setup"] ?? [] as $fn)
            ($ufo->is_function($fn) ? $fn() : "");
        $ufo->fire("ufo_setup");
    }

    /**
     * When theme full run
     */
    private function do_theme_setup () {
        global $ufo, $_;
        foreach ($_["ufo_theme_setup"] ?? [] as $fn)
            ($ufo->is_function($fn) ? $fn() : "");
        $ufo->fire("ufo_theme_setup");
    }

    /**
     * Add javaScript variable
     * @throws Exception
     */
    private function set_localize_script () {
        global $ufo, $db;

        $ufo->add_localize_script("ufo_info", "admin_url", URL_ADMIN);
        $ufo->add_localize_script("ufo_info", "web_url", URL_WEBSITE);
        $ufo->add_localize_script("ufo_info", "ajax_url", $ufo->admin_ajax());
        $ufo->add_localize_script("ufo_info", "panel", defined("ADMIN"));

        $ufo->add_source('window.alert = text => $.ufo_dialog({title: text,options:{okText:"' . $ufo->lng("Ok") . '"}})');

        if ($ufo->check_login_admin()) {
            $ufo->add_localize_script("ufo_info", "debug", $db->meta("debug"));
            $ufo->add_localize_script("ufo_info", "types", $ufo->get_lower_type_file());
            $ufo->add_localize_script("ufo_info", "max_size", $ufo->get_upload_max_size());
            $ufo->add_localize_script("ufo_info", "slash", SLASH);
            $ufo->add_localize_script("ufo_info", "version", $ufo->get_package()["version"] ?? "");
            $ufo->add_localize_script("ufo_info", "step_update", $db->meta("step_update"));
            $ufo->add_localize_script("ufo_info", "error_photo", $db->meta("error_photo"));
            $ufo->add_localize_script("ufo_info", "unknown_img", $db->meta("unknown_photo"));

            if ($ufo->reloadedHere()) {
                $ufo->add_localize_script("ufo_info", "page", $ufo->lastPage());
            }

            $plugins = [];
            foreach ((new UFO_Json(_CACHE_ . "plugins.json"))->get() as $item)
                $plugins[$item["id"]] = $item["version"];
            $ufo->add_localize_script("ufo_info", "plugins", $plugins);

            $templates = [];
            foreach ((new UFO_Json(_CACHE_ . "templates.json"))->get() as $item)
                $templates[$item["id"]] = $item["version"];
            $ufo->add_localize_script("ufo_info", "templates", $templates);
        }
    }

    /**
     * Render Editor Widgets
     */
    private function render_editor_widgets (): string {
        global $ufo;

        $widgets = $ufo->get_array("ufo_editor_widgets");
        $result  = "";

        foreach ($widgets as $item) {
            $result .=
                $ufo->tag("div",
                    $ufo->tag("div",
                        (
                            $ufo->is_url($item["icon"]) ? $ufo->tag("img", null, [
                                "src" => $item["icon"]
                            ]) : (
                                strpos($item["icon"], "</svg>") ? $item["svg"] : $ufo->tag("i", null, ["class" => $item["icon"]])
                           )
                       ) .
                        $ufo->tag("strong", empty($item["title"]) ? "undefined" : $item["title"], ["class"=>"title"])
                    ,["class" => "ufo-widget", "data-widget" => $item["name"]])
                ,["class" => "ufo-widget-column"]);
        }

        return $result;
    }

    /**
     * @param $float
     * @param $content
     * @param bool $clear
     * @return bool
     */
    private function add_float_page ($float, $content, bool $clear = false): bool {
        global $ufo;

        /**
         * Append To Array
         */
        $this->DATA["float_page"][$float] = [
            "content" => $content,
            "clear"   => $clear
        ];

        /**
         * Create Link
         */
        $ufo->add_rule(LAYOUT . "external/float_page", $ufo->sanitize_link("/float/" . $float));

        return true;
    }

    /**
     * @return void
     * @throws Exception
     */
    private function do_float_page () {
        global $ufo;
        if (isset($this->DATA["float_page"][$ufo->end_url()])) {
            $float = $this->DATA["float_page"][$ufo->end_url()];

            if ($float["clear"]) {
                $ufo->do_work("ufo_reset_document");
                $ufo->add_work("ufo_safe_clear_document", function () { return true; });
            }

            $ufo->add_kv("ufo_float_page_content", $ufo->is_function($float["content"]) ? $float["content"]() : $float["content"]);
        }
    }

    /**
     * @return array
     */
    private function get_languages (): array {
        global $ufo;

        $languages = $ufo->get_file_list(CONTENT . "language");
        $languages = $ufo->minifyArray($languages, "file");
        $fix_lang  = [];

        foreach ($languages as $item)
            $fix_lang[] = pathinfo($item)["filename"];

        return $fix_lang;
    }

    /**
     * @param $param
     * @return array|string|void
     */
    private function ufo_market_dl ($param) {
        global $ufo;

        /**
         * Check parameters
         */
        if (isset($param["link"]) && isset($param["type"]) && isset($param["mode"])) {

            /**
             * Check & Limit parameters
             */
            if ($param["type"] == "plugin" || $param["type"] == "template") {

                /**
                 * Check & Limit parameters
                 */
                if ($param["mode"] == "install" || $param["mode"] == "update") {
                    /**
                     * Rewrite directory
                     */
                    $directory = $ufo->return(PLUGINS, $ufo->equal(
                        $param["type"], "plugin"
                    ), THEMES);

                    if (is_dir($directory)) {

                        /**
                         * Custom name file
                         */
                        $file = rand(0, 9999) . ".zip";

                        /**
                         * Extract data to $file
                         */
                        if (copy($param["link"], $directory . $file)) {

                            /**
                             * Set cookie for install or update
                             */
                            setcookie("ufo-" . $param["mode"] . "-wizard-" . $param["type"], $file, time() + (60 * 15), "/");

                            /**
                             * Return data
                             */
                            return $ufo->status(200, [
                                "link" => URL_WEBSITE . ($param["type"] == "plugin" ? "ufo-plugin-wizard" : "ufo-template-wizard")
                            ]);

                        }

                    }

                    return $ufo->status(503, $ufo->lng("System error"));
                }

            }
        }
    }

    /**
     * @param array $array
     * @return bool
     */
    private function add_member_column (array $array): bool {
        global $ufo;
        
        if ($ufo->isset_key($array, "name") && $ufo->isset_key($array, "fn")) {
            if ($ufo->is_function($array["fn"])) {
                $has = false;

                foreach ($this->DATA["member"]["columns"] as $item) {
                    if ($item["name"] == $array["name"]) {
                        $has = true;
                        break;
                    }
                }

                if (!$has) {
                    $this->DATA["member"]["columns"][] = [
                        "name" => $array["name"],
                        "fn"   => $array["fn"]
                    ];
                }

                return true;
            }
        }

        return false;
    }

    /**
     * @param array $array
     * @return bool
     */
    private function add_member_option (array $array): bool {
        if (!isset($this->DATA["member"]["options"]))
            $this->DATA["member"]["options"] = [];
        if (isset($array["title"]) && isset($array["icon"])) {
            $this->DATA["member"]["options"][] = [
                "title" => $array["title"],
                "icon"  => $array["icon"]
            ];
            return true;
        } else { return false; }
    }

    /**
     * @return array|array[]
     */
    private function member_columns (): array {
        return [
            "columns" => function () {
                $columns = [];
                foreach ($this->DATA["member"]["columns"] as $item)
                    $columns[] = $item["name"];
                return $columns;
            },
            "rows"    => function ($member) {
                $rows = [];
                foreach ($this->DATA["member"]["columns"] as $item)
                    $rows[] = $item["fn"]($member);
                return $rows;
            },
            "options" => function ($member) {
                global $ufo; $options = "";
                foreach ($this->DATA["member"]["options"] ?? [] as $item) {
                    $options .= $ufo->tag(
                        'i', null,
                        ["class" => "cl-info cursor-pointer " . ($ufo->dir() == "ltr" ? "ml-10" : "mr-10") . " " . $item["icon"],
                            "data-mem" => base64_encode($member['uid']),
                            "title" => $item["title"]
                        ]);
                }
                return $options;
            }
        ];
    }

    /**
     * Steps update system
     *
     * Meta row :
     *    step_update :
     *        0 -> Nothing
     *        1 -> The new version has been successfully downloaded
     *        2 -> Unzip new version
     *    status :
     *        0 -> Nothing
     *        1 -> Coming soon
     *        2 -> The website is under repair
     *        3 -> Updating
     */

    /**
     * @param array $args
     * @return array|string
     */
    private function dnv_system (array $args) {
        global $ufo, $db;
        if ($ufo->isset_key($args, "link") && $ufo->isset_key($args, "version")) {

            try {
                $nv_setup_folder = $ufo->back_folder() . _PRIVATE_ . "ufo_nv_setup";

                /**
                 * Delete old setup folder
                 */
                if (is_dir($nv_setup_folder))
                    $ufo->delete_folder($nv_setup_folder);

                /**
                 * Add setup folder
                 */
                if (!mkdir($nv_setup_folder))
                    return $ufo->status(503, $ufo->lng("System error"));

                /**
                 * Download new version from UFOCms server
                 */
                $version  = $args["version"];
                $download = copy($args["link"], $nv_setup_folder . SLASH . $version . ".zip");

                /**
                 * Change step update
                 */
                if ($download) {
                    $db->update_meta("new_version", $version);
                    $db->update_meta("step_update", 1);
                }

                return $ufo->status($download ? 200 : 503, $download ? $ufo->lng("Done successfully") : $ufo->lng("System error"));
            } catch (Exception $e) {
                return $ufo->status(503, $ufo->lng("System error"));
            }

        } else return $ufo->status(403, $ufo->lng("Access denied"));
    }

    /**
     * @throws Exception
     */
    private function pnv_system () {
        global $ufo, $db, $_;

        if ($ufo->check_login_admin()) {

            /**
             * Setup page
             */
            if (
                $db->meta("step_update") == 1 &&
                file_exists(_PRIVATE_ . "ufo_nv_setup" . SLASH . $db->meta("new_version") . ".zip")
            ) {
                $ufo->add_rule(LAYOUT . "external/update", $ufo->sanitize_link("/" . $ufo->admin_path() . "update"));
            }

            /**
             * Process
             */
            if ($ufo->match_page($ufo->admin_path() . "update")) {
                $_["title"] = $ufo->lng("System update");

                $ufo->add_array("ufo_body_class", "ufo-wizard");

                $ufo->add_localize_script("ufo_data", "admin_url", URL_ADMIN);

                $ufo->add_script("update", ASSETS . "script/update.js");
            }
        }
    }

    /**
     * New version installer
     *
     * @throws Exception
     */
    private function nv_installer (): bool {
        global $ufo, $db, $admin_folder, $_;

        $result = false;
        $nv_setup_folder  = _PRIVATE_ . "ufo_nv_setup";
        $nv_setup_zipFILE = $nv_setup_folder . SLASH . $db->meta("new_version") . ".zip";
        $nv_setup_file    = $nv_setup_folder . SLASH . "setup.php";

        switch ($_POST['step']) {
            case "unzip":
                if (is_file($nv_setup_zipFILE)) {
                    $result = $ufo->unzip($nv_setup_zipFILE);
                } elseif (is_file($nv_setup_file)) {
                    $result = true;
                }
                if ($result) {
                    $ufo->delete_file($nv_setup_zipFILE);
                    $db->update_meta("step_update", 2);
                }
                break;
            case "install":
                if (is_file($nv_setup_file)) {
                    $change_status = $db->update_meta("status", 3);
                    if ($change_status) {
                        require $nv_setup_file;
                        $result = true;
                    }
                }
                break;
        }

        return $result;
    }

}