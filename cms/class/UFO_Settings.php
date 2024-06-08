<?php

/**
 * Copyright (c) 2022-2024 UFOCMS
 *
 * This software is licensed under the GPLv3 license.
 * See the LICENSE file for more information.
 */

final class UFO_Settings {

    protected array $list = [];

    /**
     * @return array
     * @throws Exception
     */
    public function init ( ): array {
        global $ufo;

        $this->web_info();
        $this->controlPanel();
        $this->general();
        $this->links();
        $this->email();
        $this->hosting();

        foreach ($ufo->fire("ufo_advance_settings_list") as $item) {
            if (!$ufo->has_in_array(["title", "html"], $item))
                continue;

            $this->list[] = [
                "order" => $item["order"] ?? (count($this->list) + 1),
                "title" => $item["title"],
                "html"  => $item["html"]
            ];
        }

        $ufo->order_array($this->list);

        return $this->list;
    }

    /**
     * @return void
     * @throws Exception
     */
    public function ajax ( ) {
        global $ufo;

        if (isset($_POST["action"])) {
            switch ($_POST["action"]) {
                case "languages":
                    $ufo->die(json_encode($ufo->do_work("ufo_all_languages")));
                    break;
                case "timezones":
                    $ufo->die(json_encode($ufo->get_package()["timezones"]));
                    break;
                case "ctime":
                    $ufo->die(json_encode($ufo->get_package()["ctime"]));
                    break;
                case "save":
                    $ufo->die($this->save());
                    break;
            }
        }
    }

    /**
     * @return void
     * @throws Exception
     */
    protected function web_info ( ) {
        global $ufo, $db;

        $this->list[] = [
            "order" => 1,
            "title" => $ufo->lng("Website info"),
            "html"  => [
                $ufo->tag("div",
                    $ufo->tag("div", $ufo->tag("img", null, [
                        "src" => $db->meta("web_icon"),
                        "data-error" => $db->meta("error_photo")
                    ]), [
                        "class" => "ufo-setting-web-logo"
                    ]) .
                    $ufo->tag("label", $ufo->tag("div", $ufo->tag("img", null, [
                        "src" => $db->meta("banner"),
                        "data-error" => $db->meta("error_photo")
                    ]), [
                        "class" => "ufo-setting-web-banner"
                    ]), ["class" => "p-5px"]), [
                        "class" => "ufo-setting-logo-banner"
                    ]
                ),
                $ufo->tag("label", $ufo->lng("Website name") . $ufo->single_input([
                    "placeholder" => $ufo->lng("Website name"),
                    "value"       => $db->meta("web_name"),
                    "class"       => "ufo-setting-web-name mt-5 text-center",
                    "end"         => ""
                ]), ["class" => "p-5px mb-20 db"]),
                $ufo->tag("label", $ufo->lng("Copyright text") . $ufo->single_input([
                    "placeholder" => $ufo->lng("Copyright text"),
                    "value"       => $db->meta("copyright"),
                    "class"       => "ufo-setting-footer-copyright mt-5 text-center",
                    "end"         => ""
                ]), ["class" => "p-5px mb-20 db"]),
                $ufo->tag("div", $ufo->tag("span", $ufo->lng("Protocol")), ["style" => "padding:0 5px"]),
                $ufo->tag("div", $ufo->tag("div", function ( ) {
                    global $ufo, $db;
                    $HTML = ""; $LIST = ["HTTPS", "HTTP"];
                    foreach ($LIST as $item) {
                        $attrs = ["data-rows" => $item, "data-setting" => "ufo-protocol", "data-val" => strtolower($item)];
                        if ( strtolower($item) == $db->meta("protocol") ) {
                            $attrs["class"] = "active";
                        }
                        $HTML .= $ufo->tag("button", $item, $attrs);
                    }
                    return $HTML;
                }, ["class" => "ufo-setting-group-btn ufo-setting-ufo-protocol"]), ["class" => "p-5px"])
            ]
        ];
    }

    /**
     * @return void
     * @throws Exception
     */
    protected function controlPanel ( ) {
        global $ufo, $db;

        $this->list[] = [
            "order" => 2,
            "title" => $ufo->lng("Control panel"),
            "html"  => [
                $ufo->tag("label", $ufo->lng("Theme") . $ufo->single_input([
                    "value"    => strtoupper($ufo->lng(THEME)),
                    "readonly" => true,
                    "class"    => "form-control mt-5 ufo-select-theme text-center"
                ]), ["class" => "p-5px db"]),
                $ufo->tag("span", $ufo->lng("Table rows")),
                $ufo->tag("div", function ( ) {
                    global $ufo, $db;
                    $HTML = ""; $LIST = [25, 50, 100];
                    foreach ($LIST as $item) {
                        $attrs = ["data-rows" => $item, "data-setting" => "ufo-table-rows", "data-val" => $item];
                        if ( $item == $db->meta("table_rows") ) {
                            $attrs["class"] = "active";
                        }
                        $HTML .= $ufo->tag("button", $item, $attrs);
                    }
                    return $HTML;
                }, ["class" => "ufo-setting-group-btn ufo-setting-table-rows"])
            ]
        ];
    }

    /**
     * @return void
     * @throws Exception
     */
    protected function general ( ) {
        global $ufo, $db;

        $this->list[] = [
            "order" => 3,
            "title" => $ufo->lng("General"),
            "html"  => [
                $ufo->tag("label", $ufo->tag("span", "Charset", [
                    "class" => "db width-100-cent text-left"
                ]) . $ufo->single_input([
                    "value" => $db->meta("charset"),
                    "class" => "form-control mt-5 ufo-select-charset text-center"
                ]), ["class" => "p-5px db"]),
                $ufo->tag("label", $ufo->lng("Language") . $ufo->single_input([
                    "value"    => strtoupper($db->meta("lang")),
                    "readonly" => true,
                    "class"    => "form-control mt-5 ufo-select-language text-center"
                ]), ["class" => "p-5px db"]),
                $ufo->tag("label", $ufo->lng("Direction") . $ufo->single_input([
                    "value"    => strtoupper($db->meta("dir")),
                    "readonly" => true,
                    "class"    => "form-control mt-5 ufo-select-direction text-center"
                ]), ["class" => "p-5px db"]),
                $ufo->tag("label", $ufo->lng("Timezone") . $ufo->single_input([
                    "value"    => strtoupper($db->meta("timezone")),
                    "readonly" => true,
                    "class"    => "form-control mt-5 ufo-select-timezone text-center"
                ]), ["class" => "p-5px db"]),
                $ufo->tag("label", $ufo->lng("Time conversion") . $ufo->single_input([
                    "value"    => $ufo->lng(ucfirst($db->meta("type_time"))),
                    "readonly" => true,
                    "class"    => "form-control mt-5 ufo-select-ctime text-center"
                ]), ["class" => "p-5px db"]),

                $ufo->tag("label", $ufo->lng("The structure of date and time") . $ufo->single_input([
                    "value"  => $db->meta("structure_datetime"),
                    "class"  => "form-control mt-5 ufo-select-structure-datetime text-center"
                ]), ["class" => "p-5px db"]),

                $ufo->tag("div", $ufo->tag("span", $ufo->lng("Automatic acceptance of new members")), ["style" => "padding:0 5px"]),
                $ufo->tag("div", $ufo->tag("div", function ( ) {
                    global $ufo, $db;
                    $HTML = ""; $LIST = ["ON" => "true", "OFF" => "false"];
                    foreach ($LIST as $k => $v) {
                        $k = $ufo->lng($k);
                        $attrs = ["data-rows" => $k, "data-setting" => "ufo-accept-member", "data-val" => strtolower($v)];
                        if ( strtolower($v) == $db->meta("accept-member") ) {
                            $attrs["class"] = "active";
                        }
                        $HTML .= $ufo->tag("button", $k, $attrs);
                    }
                    return $HTML;
                }, ["class" => "ufo-setting-group-btn ufo-setting-ufo-accept-member"]), ["class" => "p-5px"]),

                $ufo->tag("div", $ufo->tag("span", $ufo->lng("Automatic acceptance of new comments")), ["style" => "padding:0 5px"]),
                $ufo->tag("div", $ufo->tag("div", function ( ) {
                    global $ufo, $db;

                    $HTML = "";
                    $BUTTONS = ["ON" => "true", "OFF" => "false"];

                    foreach ($BUTTONS as $k => $v) {
                        $k = $ufo->lng($k);
                        $attrs = ["data-rows" => $k, "data-setting" => "ufo-accept-comment", "data-val" => strtolower($v)];

                        if (strtolower($v) == $db->meta("accept_comment"))
                            $attrs["class"] = "active";

                        $HTML .= $ufo->tag("button", $k, $attrs);
                    }

                    return $HTML;
                }, ["class" => "ufo-setting-group-btn ufo-setting-ufo-accept-comment"]), ["class" => "p-5px"]),

                $ufo->tag("div", $ufo->tag("span", $ufo->lng("Search engine optimization (SEO)")), ["style" => "padding:0 5px"]),
                $ufo->tag("div", $ufo->tag("div", function ( ) {
                    global $ufo, $db;

                    $HTML = "";
                    $BUTTONS = ["ON" => "true", "OFF" => "false"];

                    foreach ($BUTTONS as $k => $v) {
                        $k = $ufo->lng($k);
                        $attrs = ["data-rows" => $k, "data-setting" => "ufo-seo", "data-val" => strtolower($v)];

                        if (strtolower($v) == $db->seo)
                            $attrs["class"] = "active";

                        $HTML .= $ufo->tag("button", $k, $attrs);
                    }

                    return $HTML;
                }, ["class" => "ufo-setting-group-btn ufo-setting-ufo-seo"]), ["class" => "p-5px"]),
            ]
        ];
    }

    /**
     * @return void
     * @throws Exception
     */
    protected function links ( ) {
        global $ufo, $db;

        $Slugs = [
            "Blog"     => "blog",
            "Category" => "category",
            "Signup"   => "signup",
            "Login"    => "login"
        ];
        $Html = [];

        foreach ($ufo->fire("ufo_advance_settings_slugs") as $slug)
            $Slugs = $Slugs + $slug;

        foreach ($Slugs as $ks => $slug) {
            $Html[] = $ufo->tag("label", $ufo->lng($ks) . $ufo->single_input([
                "value" => $db->slug($slug),
                "name"  => $slug,
                "class" => "form-control mt-5 ufo-slug text-center"
            ]), ["class" => "p-5px db"]);
        }

        $this->list[] = [
            "order" => 4,
            "title" => $ufo->lng("Links"),
            "html"  => $Html
        ];
    }

    /**
     * @return void
     * @throws Exception
     */
    protected function email ( ) {
        global $ufo, $db;

        $SMTP  = unserialize($db->meta("smtp"));
        $EMAIL = unserialize($db->meta("mail"));

        $this->list[] = [
            "order" => 5,
            "title" => $ufo->lng("Email settings"),
            "html"  => [

                $ufo->tag("div",

                    $ufo->tag("div", $ufo->lng("Host") . $ufo->single_input([
                        "placeholder" => $ufo->lng("Host"),
                        "value" => $SMTP["host"],
                        "class" => "mt-5 rtl-to-ltr-placeholder ufo-advance-mail-host"
                    ]), ["class" => "p-5px"]) .

                    $ufo->tag("div", $ufo->lng("Authentication") .
                        $ufo->tag("select",
                            $ufo->tag("option", $ufo->lng("Yes"), [ "value" => "true", ($ufo->equal($SMTP["auth"], "true") ? "selected" : "f") => "true" ]) .
                            $ufo->tag("option", $ufo->lng("No"), [ "value" => "false", ($ufo->equal($SMTP["auth"], "false") ? "selected" : "f") => "true" ]),
                            ["class" => "form-control text-center mt-5 cursor-pointer ufo-advance-mail-auth"]
                        ), ["class"  => "p-5px"]) .

                    $ufo->tag("div", $ufo->lng("Security") .
                        $ufo->tag("select",
                            $ufo->tag("option", "SSL", ["value" => "ssl", ($ufo->equal($SMTP["secure"], "ssl") ? "selected" : "f") => "true"]) .
                            $ufo->tag("option", "TLS", ["value" => "tls", ($ufo->equal($SMTP["secure"], "tls") ? "selected" : "f") => "true"]),
                            ["class" => "form-control text-center mt-5 cursor-pointer ufo-advance-mail-secure"]
                        ), ["class"  => "p-5px"]) .

                    $ufo->tag("div", $ufo->lng("Port") . $ufo->single_input([
                            "placeholder" => $ufo->lng("Port"),
                            "value" => $SMTP["port"],
                            "class" => "mt-5 rtl-to-ltr-placeholder ufo-advance-mail-port"
                        ]), ["class" => "p-5px"]) .

                    $ufo->tag("div", $ufo->lng("Email") . $ufo->single_input([
                            "placeholder" => $ufo->lng("Email"),
                            "value" => $EMAIL["username"],
                            "class" => "mt-5 rtl-to-ltr-placeholder ufo-advance-mail-email"
                        ]), ["class" => "p-5px"]) .

                    $ufo->tag("div", $ufo->lng("Password") . $ufo->single_input([
                            "placeholder" => $ufo->lng("Password"),
                            "value" => $EMAIL["password"],
                            "class" => "mt-5 rtl-to-ltr-placeholder ufo-advance-mail-password"
                        ]), ["class" => "p-5px"]) .

                    $ufo->tag("div", $ufo->lng("Sender email") . $ufo->single_input([
                            "placeholder" => $ufo->lng("Sender email"),
                            "value" => $EMAIL["from_mail"],
                            "class" => "mt-5 rtl-to-ltr-placeholder ufo-advance-from-email"
                        ]), ["class" => "p-5px"]) .

                    $ufo->tag("div", $ufo->lng("Sender name") . $ufo->single_input([
                            "placeholder" => $ufo->lng("Sender name"),
                            "value" => $EMAIL["from_name"],
                            "class" => "mt-5 rtl-to-ltr-placeholder ufo-advance-from-name"
                        ]), ["class" => "p-5px"]),
                    ["class" => "grid-2"]
                )
            ]
        ];
    }

    /**
     * @return void
     */
    protected function hosting ( ) {
        global $ufo;

        $this->list[] = [
            "order" => 6,
            "title" => $ufo->lng("Hosting setting"),
            "html"  => [
                $ufo->tag("label", $ufo->lng("RAM usage limit") . " ( " . $ufo->lng("Megabyte") . " )" . $ufo->single_input([
                    "value" => (int) (ini_get("memory_limit") ?? 0),
                    "class" => "form-control mt-5 ufo-memory-limit text-center"
                ]), ["class" => "p-5px db"]),
                $ufo->tag("label", $ufo->lng("Max upload size") . " ( " . $ufo->lng("Megabyte") . " )" . $ufo->single_input([
                    "value" => (int) $ufo->get_upload_max_size(),
                    "class" => "form-control mt-5 ufo-max-upload-size text-center"
                ]), ["class" => "p-5px db"]),

                $ufo->tag("div", $ufo->tag("span", $ufo->lng("Minify HTML")), ["style" => "padding:0 5px"]),
                $ufo->tag("div", $ufo->tag("div", function () {
                    global $ufo, $db;

                    $HTML = "";
                    $BUTTONS = ["ON" => "true", "OFF" => "false"];

                    foreach ($BUTTONS as $k => $v) {
                        $k = $ufo->lng($k);
                        $attrs = ["data-rows" => $k, "data-setting" => "ufo-minify-html", "data-val" => strtolower($v)];

                        if (strtolower($v) == $db->meta("minify_html"))
                            $attrs["class"] = "active";

                        $HTML .= $ufo->tag("button", $k, $attrs);
                    }

                    return $HTML;
                }, ["class" => "ufo-setting-group-btn ufo-setting-ufo-minify-html"]), ["class" => "p-5px"]),
            ]
        ];
    }

    /**
     * @return string
     * @throws Exception
     */
    protected function save ( ): string {
        global $ufo, $db;

        if (!$ufo->isset_post("setting") || !is_array($_POST["setting"]))
            return $ufo->status(403, $ufo->lng("Access denied"));

        $setting = $_POST["setting"];
        $result  = [];

        if (isset($setting["ufo-web-logo"]))
            $result[] = $db->update_meta("web_icon", $setting["ufo-web-logo"]);

        if (isset($setting["ufo-web-banner"]))
            $result[] = $db->update_meta("banner", $setting["ufo-web-banner"]);

        if (isset($setting["ufo-web-name"]))
            $result[] = $db->update_meta("web_name", $setting["ufo-web-name"]);

        if (isset($setting["ufo-copyright"]))
            $result[] = $db->update_meta("copyright", $setting["ufo-copyright"]);

        if (isset($setting["ufo-accept-member"]))
            $result[] = $db->update_meta("accept-member", $setting["ufo-accept-member"]);

        if (isset($setting["ufo-accept-comment"]))
            $result[] = $db->update_meta("accept_comment", $setting["ufo-accept-comment"]);

        if (isset($setting["ufo-protocol"])) {
            function url_protocol ($protocol, $url): string {
                return $protocol . str_replace("http", "", str_replace("https", "", $url));
            }
            $protocol = strtolower($setting["ufo-protocol"]);
            $result[] = $db->update_meta("protocol", $protocol);
            $result[] = $db->update_meta("web_url", url_protocol($protocol, URL_WEBSITE));
            $result[] = $db->update_meta("web_admin_url", url_protocol($protocol, URL_ADMIN));
        }

        if (isset($setting["ufo-charset"]))
            $result[] = $db->update_meta("charset", $setting["ufo-charset"]);

        if (isset($setting["ufo-language"]))
            $result[] = $db->update_meta("lang", $setting["ufo-language"]);

        if (isset($setting["ufo-direction"]))
            $result[] = $db->update_meta("dir", $setting["ufo-direction"]);

        if (isset($setting["ufo-table-rows"]))
            $result[] = $db->update_meta("table_rows", $setting["ufo-table-rows"]);

        if (isset($setting["ufo-timezone"])) {
            $timezone = explode("/", strtolower($setting["ufo-timezone"]));
            $timezone = ucfirst($timezone[0]) . "/" . ucfirst($timezone[1]);
            $result[] = $db->update_meta("timezone", $timezone);
        }

        if (isset($setting["ufo-ctime"])) {
            $ctime = $ufo->lng($setting["ufo-ctime"]) == $ufo->lng("Solar") ? "solar" : "gregorian";
            $result[] = $db->update_meta("type_time", $ctime);
        }

        if (isset($setting["ufo-structure-datetime"]))
            $result[] = $db->update_meta("structure_datetime", $setting["ufo-structure-datetime"]);

        // Slugs
        foreach ($setting as $k => $item) {
            $is_slug = strpos($k, "ufo-slug-");
            if ($is_slug == 0 && $is_slug !== FALSE)
                $result[] = $db->update_meta("slug_" . str_replace("ufo-slug-", "", $k), $item);
        }

        if (isset($setting["ufo-minify-html"]))
            $result[] = $db->update_meta("minify_html", $setting["ufo-minify-html"]);

        if (isset($setting["ufo-theme"]))
            $result[] = $db->update("admins", [
                "theme" => strtolower($setting["ufo-theme"])
            ], "id", $ufo->get_admin()["id"]);

        if (isset($setting["ufo-seo"]))
            $result[] = $db->update_meta("seo", $setting["ufo-seo"]);

        $result = $this->save_mail($setting, $result);
        $result = $this->save_htaccess($setting, $result);

        $result = in_array(false, $result);

        $ufo->fire("ufo_settings_save", $setting);

        return $ufo->status(
            $result ? 503 : 200, $result ? $ufo->lng("System error") : $ufo->lng("Done successfully")
        );
    }

    /**
     * @param $setting
     * @param $result
     * @return mixed
     * @throws Exception
     */
    protected function save_mail ( $setting, $result ) {
        global $db;

        $SMTP  = unserialize($db->meta("smtp"));
        $EMAIL = unserialize($db->meta("mail"));

        /**
         * Change SMTP
         */
        $SMTP["host"]   = $setting["ufo-mail-host"]   ?? $SMTP["host"];
        $SMTP["auth"]   = $setting["ufo-mail-auth"]   ?? $SMTP["auth"];
        $SMTP["secure"] = $setting["ufo-mail-secure"] ?? $SMTP["secure"];
        $SMTP["port"]   = $setting["ufo-mail-port"]   ?? $SMTP["port"];

        /**
         * Change EMAIL
         */
        $EMAIL["username"]  = $setting["ufo-mail-email"]      ?? $EMAIL["username"];
        $EMAIL["password"]  = $setting["ufo-mail-password"]   ?? $EMAIL["password"];
        $EMAIL["from_mail"] = $setting["ufo-mail-from-email"] ?? $EMAIL["from_mail"];
        $EMAIL["from_name"] = $setting["ufo-mail-from-name"]  ?? $EMAIL["from_name"];

        /**
         * Update meta
         */
        $result[] = $db->update_meta("smtp", serialize($SMTP));
        $result[] = $db->update_meta("mail", serialize($EMAIL));

        return $result;
    }

    /**
     * @param $setting
     * @param $result
     * @return mixed
     */
    protected function save_htaccess ( $setting, $result ) {
        global $ufo;

        /**
         * Upload maximum size
         */
        if ( isset($setting["ufo-max-upload-size"]) ) {
            $result[] = $ufo->rewrite_htaccess("php_value upload_max_filesize [0-9]{0,9}M", "php_value upload_max_filesize " . ((int) $setting["ufo-max-upload-size"]) . "M");
            $result[] = $ufo->rewrite_htaccess("php_value post_max_size [0-9]{0,9}M", "php_value post_max_size " . ((int) $setting["ufo-max-upload-size"]) . "M");
        }

        /**
         * Limit memory usage
         */
        if ( isset($setting["ufo-memory-limit"]) ) {
            $result[] = $ufo->rewrite_htaccess("php_value memory_limit [0-9]{0,9}M", "php_value memory_limit " . ((int) $setting["ufo-memory-limit"]) . "M");
        }

        return $result;
    }

}