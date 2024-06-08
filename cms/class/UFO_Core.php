<?php

/**
 * Copyright (c) 2022-2024 UFOCMS
 *
 * This software is licensed under the GPLv3 license.
 * See the LICENSE file for more information.
 */

final class UFO_Core {

    protected $ajax, $autorun;

    /**
     * @throws Exception
     */
    public function __construct ($admin = true, $ajax = false, $autorun = true) {
        $this->beforeProcess();

        $this->ajax    = $ajax;
        $this->autorun = $autorun;

        $this->add_works();
        $this->set_timezone();
        $this->add_language();
        $this->add_data();
        $this->run_class();
        $this->explorer();

        if ($admin) {
            $this->def_pages();

            $this->add_menu();
            $this->add_default_style();
            $this->add_default_script();
            $this->run_plugins();
            $this->after_plugins();

            if (!$ajax && $autorun) {
                $this->add_localize_script();
                $this->add_login_inputs();
            }
        } else {
            $this->add_style_front();
            $this->run_plugins();
        }

        $this->run_theme();

        $this->run_class(true);

        $this->do_all_works();

        if (!$ajax && $autorun) {
            if ($admin) {
                $this->save_saver();
            } else {
                $this->save_saver_front();
            }
        }
    }

    /**
     * @throws Exception
     * @return void
     */
    private function beforeProcess () {
        global $ufo, $db;

        /**
         * Automatic protocol redirect to HTTPS or HTTP
         */
        if ($ufo->isset_key($_SERVER, "HTTPS")) {
            if ($_SERVER["HTTPS"] === "off") {
                if ($ufo->equal($db->meta("protocol"), "https")) {
                    header("HTTP/1.1 301 Moved Permanently");
                    $ufo->redirect("https://" . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"]);
                    $ufo->die();
                }
            }
        }

        /**
         * Delete the last saved page
         */
        if ($ufo->is_admin()) {
            if (!$ufo->reloadedHere() && $_SERVER["REQUEST_METHOD"] == "GET")
                $ufo->unset_session("ufo_last_page");
        }

        /**
         * Set all queries in the link sent from
         * the frontend to $_GET.
         */
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $url = $ufo->this_url_info();
            foreach ($url["queries"] as $query => $value)
                $_GET[$query] = $value;
        }
    }

    /**
     * @return void
     */
    private function add_works () {
        global $ufo;

        $ufo->add_work("ufo-admin-header-options", function () {
            global $ufo; return implode("", $ufo->get_array("ufo-admin-header-options"));
        });

        $ufo->add_work("ufo_body_class", function () {
            global $ufo;
            $implode = implode(" ", $ufo->get_array("ufo_body_class"));
            return ltrim($implode, " ");
        });
    }

    /**
     * @return void
     */
    private function add_data () {
        global $ufo, $db, $_;

        /** Ajax data */
        $ajax_guest = ["login", "check_admin_login", "language"];
        foreach ($ajax_guest as $item)
            $ufo->add_array("ufo_ajax_guest", $item);

        /** Search engine optimization (SEO) */
        $ufo->add_meta([
            "name"    => "robots",
            "content" => $ufo->success($db->seo) && !$ufo->is_admin() ? "index, follow" : "noindex, nofollow"
        ]);

        /** Customize logo management login page */
        if ($ufo->match_page("login.php")) {
            $_["ufo_admin_login_logo"] = $ufo->tag("a", $ufo->tag("i", null, [
                "class"  => "ufo-icon-ufocms"
            ]), [
                "href"   => "https://ufocms.org",
                "class"  => "login-icon",
                "target" => "_blank"
            ]);
        }

        /** Add ajax key in session */
        $ufo->set_session("ufo_ajax_key", $ufo->do_work("ufo_ajax_key") ?? "");

        /** Add menu positions */
        $UFO_Menu = new UFO_Menu();
        $UFO_Menu->add_position("every-where", $ufo->lng("Every where"));
        $UFO_Menu->add_position("main-menu", $ufo->lng("Main menu"));
        $UFO_Menu->add_position("footer", $ufo->lng("Footer"));
        $UFO_Menu->add_position("user-account", $ufo->lng("User Account"));
        $UFO_Menu->add_position("quick-access", $ufo->lng("Quick access"));

        /** Start admin data */
        if (!$ufo->is_admin()) return;

        $ufo->add_array("ufo_body_class", "admin-has-header");
        $ufo->add_array("ufo_body_attrs", [
            "data-theme" => THEME
        ]);
    }

    /**
     * @throws Exception
     * @return void
     */
    private function run_class (bool $end = false) {
        if (!$end) {
            (new UFO_Pages())->init();
            (new UFO_Menu())->init();
            (new UFO_Account())->init();
        }

        if ($end) {
            (new UFO_Editor())->init();
        }
    }

    /**
     * @return void
     */
    private function def_pages () {
        $this->widgets();
        $this->setting_list();
    }

    /**
     * @throws Exception
     * @return void
     */
    private function add_menu () {
        global $ufo;

        $ufo->add_menu([
            "title"  => $ufo->lng("Dashboard"),
            "icon"   => "ufo-icon-apps",
            "page"   => "dashboard"
        ]);
        $ufo->add_menu([
            "title"  => $ufo->lng("File management"),
            "icon"   => "ufo-icon-folder",
            "page"   => "files"
        ]);
        $ufo->add_menu([
            "title"  => $ufo->lng("Pages"),
            "icon"   => "ufo-icon-file",
            "page"   => "pages"
        ]);
        $ufo->add_menu([
            "title"  => $ufo->lng("Comments"),
            "icon"   => "ufo-icon-message-circle",
            "page"   => "comments"
        ]);
        $ufo->add_menu([
            "title"  => $ufo->lng("Users"),
            "icon"   => "ufo-icon-users",
            "page"   => "members"
        ]);

        $ufo->add_array("ufo-admin-header-options", $ufo->tag("i", null, [
            "class" => "ufo-icon-power font-size-25px exit-cms"
        ]));
        $ufo->add_array("ufo-admin-header-options", $ufo->tag("a", null, [
            "class" => "ufo-icon-globe font-size-25px",
            "href"  => $ufo->web_link(),
            "target"=> "_blank"
        ]));
        $ufo->add_array("ufo-admin-header-options", $ufo->tag("i", null, [
            "class" => "ufo-icon-maximize font-size-23px fullscreen-page"
        ]));
    }

    /**
     * @return void
     */
    private function add_language () {
        global $ufo, $admin_folder;
        $ufo->add_lng($ufo->slash_folder(
            $admin_folder . "content/language/" . LANG . ".json"
        ));
    }

    /**
     * @return void
     */
    private function do_all_works () {
        global $ufo, $_;

        foreach ($_["works"] ?? [] as $k => $v) {
            $ufo->do_work($k, $ufo->is_function($v) ? $v() : $v);
        }

        if ($ufo->is_admin()) {
            $ufo->do_work("ufo_do_all_setup");
        }
    }

    /**
     * @throws Exception
     * @return void
     */
    private function add_default_style () {
        global $ufo;

        $ufo->add_style(ASSETS . "css/theme/" . THEME . ".css");
        $ufo->add_style(ASSETS . "css/ufo.css");
    }

    /**
     * @return void
     */
    private function add_default_script () {
        global $ufo;

        // Jquery
        $ufo->add_script("jquery", ASSETS . "script/jquery.min.js", null, "top");

        // UFO Options
        $ufo->add_script("options", ASSETS . "script/options.js");

        // UFO Api
        $ufo->add_script("ufo_api", ASSETS . "script/api.js");

        // Codemirror
        $ufo->add_script("duDialog", ASSETS . "libs/codemirror/codemirror.js", "", "top");
    }

    /**
     * @throws Exception
     * @return void
     */
    private function after_plugins () {
        global $ufo;

        (new UFO_Template())->admin_init();

        $ufo->add_script("ufo", ASSETS . "script/ufo.js", "options");

        $ufo->add_menu([
            "title" => $ufo->lng("Settings"),
            "icon"  => "ufo-icon-settings",
            "page"  => "setting"
        ]);

        $ufo->add_array("settings", [
            "name" => $ufo->lng("Advanced settings"),
            "more" => $ufo->lng("Setup and customization"),
            "icon" => "ufo-icon-zap",
            "icon-color" => "orange",
            "page"   => "advance-setting",
            "layout" => LAYOUT . "pages/snippets/setting"
        ]);
        $ufo->add_array("settings", [
            "name" => $ufo->lng("Security"),
            "more" => $ufo->lng("Security settings"),
            "icon" => "ufo-icon-shield",
            "icon-color" => "green",
            "page"   => "security",
            "layout" => LAYOUT . "pages/snippets/security"
        ]);
        $ufo->add_array("settings", [
            "name" => $ufo->lng("Update"),
            "more" => $ufo->lng("Download the new version of UFO"),
            "icon" => "ufo-icon-upgrade",
            "icon-color" => "#413F42",
            "page"   => "update",
            "layout" => LAYOUT . "pages/update"
        ]);
    }

    /**
     * @return void
     */
    private function add_localize_script () {
        global $ufo;
        $ufo->do_work("ufo_set_default_localize");
    }

    /**
     * @return void
     */
    private function add_login_inputs () {
        global $ufo;

        $ufo->add_input("admin_login", [
            "class" => "login-input",
            "name"  => "username",
            "type"  => "text",
            "required"    => true,
            "placeholder" => $ufo->lng("Username")
        ]);
        $ufo->add_input("admin_login", [
            "class" => "login-input",
            "name"  => "password",
            "type"  => "password",
            "required"    => true,
            "placeholder" => $ufo->lng("Password")
        ]);
    }

    /**
     * @return void
     * @throws Exception
     */
    private function set_timezone () {
        global $db;
        try {
            date_default_timezone_set($db->meta("timezone"));
        } catch (Exception $e) {
            print_r("Set Timezone Error : " . $e);
        }
    }

    /**
     * @return void
     */
    private function run_plugins () {
        new UFO_Plugins();
    }

    /**
     * @return void
     * @throws Exception
     */
    private function run_theme () {
        if (defined("AJAX_FRONT")) {
            (new UFO_Template())->ajax_front();
        }
    }

    /**
     * @return void
     */
    private function save_saver () {
        global $ufo;

        if (!$ufo->isset_post())
            file_put_contents(_CACHE_ . "saver.json", json_encode($ufo->get_saver()));
    }

    /**
     * @return void
     */
    private function save_saver_front () {
        global $ufo, $admin_folder;

        if (!$ufo->isset_post())
            file_put_contents($admin_folder . _CACHE_ . "saver-front.json", json_encode($ufo->get_saver()));
    }

    /**
     * @return void
     */
    private function add_style_front () {
        global $ufo;

        $ufo->add_style(ASSETS . "font/all.css");
        $ufo->add_style(ASSETS . "css/ui.css");
        $ufo->add_style(ASSETS . "css/front.css");
    }

    /**
     * @return void
     */
    private function setting_list () {
        global $ufo;

        $ufo->add_input("setting-search", [
            "placeholder"  => $ufo->lng("Search"),
            "class"        => "setting-search",
            "data-nothing" => $ufo->lng("Nothing Found :(")
        ]);

        $ufo->add_array("settings", [
            "name" => $ufo->lng("Menus"),
            "more" => $ufo->lng("Edit and add menu"),
            "icon" => "ufo-icon-list",
            "icon-color" => "blue",
            "page"   => "menu",
            "layout" => LAYOUT . "pages/snippets/menu-editor"
        ]);
        $ufo->add_array("settings", [
            "name" => $ufo->lng("Plugins"),
            "more" => $ufo->lng("Manage plugins"),
            "icon" => "ufo-icon-plugin font-size-30px",
            "icon-color" => "red",
            "page"   => "plugins",
            "layout" => LAYOUT . "pages/plugins"
        ]);
        $ufo->add_array("settings", [
            "name" => $ufo->lng("Templates"),
            "more" => $ufo->lng("Manage templates"),
            "icon" => "ufo-icon-layout font-size-30px",
            "icon-color" => "purple",
            "page"   => "templates",
            "layout" => LAYOUT . "pages/templates"
        ]);
        $ufo->add_array("settings", [
            "name" => $ufo->lng("Managers"),
            "more" => $ufo->lng("Manage managers"),
            "icon" => "ufo-icon-admins font-size-30px",
            "icon-color" => "#69585f",
            "page"   => "managers",
            "layout" => LAYOUT . "pages/managers"
        ]);
        $ufo->add_array("settings", [
            "name" => $ufo->lng("Market"),
            "more" => $ufo->lng("Download template and plugin"),
            "icon" => "ufo-icon-shopping-bag font-size-30px",
            "icon-color" => "#17b7c7",
            "page"   => "market",
            "layout" => LAYOUT . "pages/market"
        ]);
    }

    /**
     * @return void
     */
    private function widgets () {
        global $ufo;

        $ufo->add_admin_widget([
            "title"   => $ufo->lng("Note"),
            "column"  => 1,
            "include" => "layout/pages/snippets/notes.php",
            "script"  => "ufo_widget_notes"
        ]);
        $ufo->add_admin_widget([
            "title"   => $ufo->lng("Comments"),
            "column"  => 2,
            "include" => "layout/pages/snippets/comments.php",
            "script"  => "ufo_widget_comments"
        ]);
    }

    /**
     * @return void
     */
    private function explorer () {
        global $ufo;

        $ufo->exert("ufo-explorer-comments", function ($explorer) {
            global $ufo;

            $sender = array_merge([
                "from" => "unknown"
            ], $ufo->isset_key($explorer->hunted, "unknown") ? $explorer->unknown : []);

            if ($ufo->isset_key($explorer->hunted, "member")) {
                $sender = array_merge([
                    "from" => "member"
                ], $explorer->member);
            }
            if ($ufo->isset_key($explorer->hunted, "admin")) {
                $sender = array_merge([
                    "from" => "admin"
                ], $explorer->admin);
            }
            if ($ufo->isset_key($explorer->hunted, "guest")) {
                $sender = array_merge([
                    "from" => "guest"
                ], $explorer->guest);
            }

            $explorer->sender = $sender;
        });
        $ufo->add_array("ufo-explorer", [
            "name"   => "comments",
            "hunter" => function ($explorer) {
                global $ufo;

                extract($explorer->query);

                return $ufo->get_comments(
                    $type, $limit != 0, $page,
                    $limit, $where, $paging_action, $sort == "DESC" ? $sort : (
                        $sort == "ASC" ? $sort : "DESC"
                    )
                );
            }
        ]);
    }

}