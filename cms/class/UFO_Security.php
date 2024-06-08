<?php

/**
 * Copyright (c) 2022-2024 UFOCMS
 *
 * This software is licensed under the GPLv3 license.
 * See the LICENSE file for more information.
 */

final class UFO_Security {

    /**
     * @return void
     */
    public function init_items ( ) {

        try {
            $this->manage_cookies();
            $this->ajax();
            $this->tasks();
            $this->hosting();
            $this->developers();
        } catch ( Exception $e ) {
            print_r($e);
        }

    }

    /**
     * @param $title
     * @param array $items
     * @return void
     */
    public function add_security ( $title, array $items ) {
        global $_;

        if ( !isset($_["ufo_security"]) ) {
            $_["ufo_security"] = [];
        }

        $_["ufo_security"][$title] = $items;
    }

    /**
     * @return void
     */
    public function ajax_callback ( ) {
        global $ufo, $db;

        if ( isset($_POST["result"]) ) {

            $security = $_POST["result"];

            try {
                $result = [];

                if ( isset($security["admin_cookie"]) ) {
                    $result[] = $db->update_meta("admin_cookie", $security["admin_cookie"]);
                }

                if ( isset($security["member_cookie"]) ) {
                    $result[] = $db->update_meta("member_cookie", $security["member_cookie"]);
                }

                if ( isset($security["debug"]) ) {
                    $result[] = $db->update_meta("debug", $security["debug"]);
                }

                if ( isset($security["ajax_key"]) ) {
                    $result[] = $db->update_meta("ajax_key", serialize([
                        "last_change" => $ufo->dateTime(), "key" => $security["ajax_key"]
                    ]));
                }

                if ( isset($security["task_key"]) ) {
                    $result[] = $db->update_meta("task_key", $security["task_key"]);
                }

                if ( isset($security["ufo-security-set-tasks"]) ) {
                    $result[] = $db->update_meta("tasks", $security["ufo-security-set-tasks"]);
                }

                $result = $this->save_htaccess($security, $result);

                die($ufo->status(in_array(false, $result) ? 503 : 200, in_array(false, $result) ? $ufo->lng("System error") : $ufo->lng("Done successfully")));
            } catch ( Exception $e ) {
                die($ufo->status(403, $ufo->lng("System error")));
            }

        } else {
            die($ufo->status(403, $ufo->lng("Parameters not isset")));
        }
    }

    /**
     * @return void
     * @throws Exception
     */
    protected function manage_cookies ( ) {
        global $ufo, $db;

        $this->add_security($ufo->lng("Manage cookies"), [

            $ufo->tag("label", "" .
                $ufo->tag("div", "" .
                    $ufo->tag("div", $ufo->lng("Admin cookie"), ["class" => "mt-5"]) .
                    $ufo->tag("div", $ufo->btn($ufo->tag("i", null, [
                        "class" => "ufo-icon-refresh"
                    ]), $ufo->reverse_float()), [
                        "class"         => "ufo-security-new-key",
                        "data-input"    => "ufo-security-admin-cookie",
                        "data-security" => "admin_cookie"
                    ]),
                    ["class" => "grid-2 mb-10"]
                ) . $ufo->single_input([
                    "placeholder"   => $ufo->lng("Admin cookie"),
                    "value"         => $db->meta("admin_cookie"),
                    "readonly"      => true,
                    "class"         => "text-center ufo-security-admin-cookie"
                ])
            ),

            $ufo->tag("label", "" .
                $ufo->tag("div", "" .
                    $ufo->tag("div", $ufo->lng("Members cookie"), ["class" => "mt-5"]) .
                    $ufo->tag("div", $ufo->btn($ufo->tag("i", null, [
                        "class" => "ufo-icon-refresh"
                    ]), $ufo->reverse_float()), [
                        "class"         => "ufo-security-new-key",
                        "data-input"    => "ufo-security-member-cookie",
                        "data-security" => "member_cookie"
                    ]),
                    ["class" => "grid-2 mb-10"]
                ) . $ufo->single_input([
                    "placeholder"   => $ufo->lng("Members cookie"),
                    "value"         => $db->meta("member_cookie"),
                    "readonly"      => true,
                    "class"         => "text-center ufo-security-member-cookie"
                ])
            )

        ]);
    }

    /**
     * @return void
     * @throws Exception
     */
    protected function tasks ( ) {
        global $ufo, $db;

        $this->add_security($ufo->lng("Auto tasks"), [

            $ufo->error($ufo->lng("Please see the help page before changing the information") . " ( " . $ufo->tag("a", $ufo->lng("Click"), ["href"=>"https://ufocms.org/doc/tasks","target"=>"_blank"]) . " ) ", "warning", false),

            $ufo->tag("label", "" .
                $ufo->tag("div", "" .
                    $ufo->tag("div", $ufo->lng("Key"), ["class" => "mt-5"]) .
                    $ufo->tag("div", $ufo->btn($ufo->tag("i", null, [
                        "class" => "ufo-icon-refresh"
                    ]), $ufo->reverse_float()), [
                        "class"         => "ufo-security-new-key",
                        "data-input"    => "ufo-security-tasks-key",
                        "data-security" => "task_key"
                    ]),
                    ["class" => "grid-2 mb-10"]
                ) . $ufo->single_input([
                    "placeholder"   => $ufo->lng("Key"),
                    "value"         => $db->meta("task_key"),
                    "readonly"      => true,
                    "class"         => "text-center ufo-security-tasks-key"
                ]),
                ["class" => "mt-20 db"]
            ),

            $ufo->tag("div", function ( ) {
                global $ufo, $db;
                $HTML = ""; $LIST = ["ON", "OFF"];
                foreach ($LIST as $item) {
                    $attrs = ["data-rows" => $item, "data-security" => "ufo-security-set-tasks", "data-val" => strtolower($item)];
                    if ( strtolower($item) == $db->meta("tasks") ) {
                        $attrs["class"] = "active";
                    }
                    $HTML .= $ufo->tag("button", $ufo->lng($item), $attrs);
                }
                return $HTML;
            }, ["class" => "ufo-security-group-btn ufo-security-set-tasks"])

        ]);
    }

    /**
     * @return void
     * @throws Exception
     */
    protected function hosting ( ) {
        global $ufo;

        $this->add_security($ufo->lng("Hosting setting"), [

            $ufo->tag("span", $ufo->lng("Prevent files from being listed")),
            $ufo->tag("div", function ( ) {
                global $ufo, $db;
                $HTML = ""; $LIST = ["ON", "OFF"];
                foreach ($LIST as $item) {
                    $attrs = ["data-rows" => $item, "data-security" => "ufo-listing-files", "data-val" => strtolower($item)];
                    if ( strtolower($item) == $db->meta("listing_files") ) {
                        $attrs["class"] = "active";
                    }
                    $HTML .= $ufo->tag("button", $ufo->lng($item), $attrs);
                }
                return $HTML;
            }, ["class" => "ufo-security-group-btn ufo-security-listing-files"])

        ]);
    }

    /**
     * @return void
     * @throws Exception
     */
    protected function ajax ( ) {
        global $ufo, $db;

        $this->add_security($ufo->lng("Ajax setting"), [

            $ufo->tag("label", "" .
                $ufo->tag("div", "" .
                    $ufo->tag("div", $ufo->lng("Key"), ["class" => "mt-5"]) .
                    $ufo->tag("div", $ufo->btn($ufo->tag("i", null, [
                        "class" => "ufo-icon-refresh"
                    ]), $ufo->reverse_float()), [
                        "class"         => "ufo-security-new-key",
                        "data-input"    => "ufo-security-ajax-key",
                        "data-security" => "ajax_key"
                    ]),
                    ["class" => "grid-2 mb-10"]
                ) . $ufo->single_input([
                    "placeholder"   => $ufo->lng("Key"),
                    "value"         => $ufo->do_work("ufo_ajax_key"),
                    "readonly"      => true,
                    "class"         => "text-center ufo-security-ajax-key"
                ])
            ),

        ]);
    }

    /**
     * @return void
     * @throws Exception
     */
    protected function developers ( ) {
        global $ufo, $db;

        $this->add_security($ufo->lng("Developers"), [

            // Debug
            $ufo->tag("label", $ufo->lng("Debug") . $ufo->tag("div", "" .
                 $ufo->tag("button", $ufo->lng("ON"), ["class" => ($db->meta("debug") == "true" ? "active" : ""), "data-security" => "debug", "data-val" => "true", "data-warning" => $ufo->lng("Turn off this option when you are done")]) .
                 $ufo->tag("button", $ufo->lng("OFF"), ["class" => ($db->meta("debug") == "false" ? "active" : ""), "data-security" => "debug", "data-val" => "false"]),
                 ["class" => "ufo-security-group-btn"]
            ))

        ]);
    }

    /**
     * @param $security
     * @param $result
     * @return mixed
     * @throws Exception
     */
    protected function save_htaccess ( $security, $result ) {
        global $ufo, $db;
        if ( isset($security["ufo-listing-files"]) ) {
            $result[] = $ufo->rewrite_htaccess("Options -Indexes", strtolower($security["ufo-listing-files"]) == "on" ? "Options -Indexes" : "");
            $db->update_meta("listing_files", $security["ufo-listing-files"]);
        }
        return $result;
    }

}