<?php

/**
 * Copyright (c) 2022-2024 UFOCMS
 *
 * This software is licensed under the GPLv3 license.
 * See the LICENSE file for more information.
 */

final class UFO_Ajax {

    /**
     * @throws Exception
     */
    public function __construct () {
        ob_clean();
        $this->run_callback();
    }

    /**
     * @throws Exception
     */
    private function run_callback () {
        global $ufo;
        if ($ufo->isset_key($_POST, "callback")) {
            if ($ufo->check_login_admin() || in_array($_POST["callback"], $ufo->get_array("ufo_ajax_guest"))) {
                if (method_exists($this, $_POST["callback"]) && $ufo->is_callable($this, $_POST["callback"])) {
                    call_user_func([$this, $_POST["callback"]]);
                } else {
                    $ufo->die($ufo->status(403, "Access denied"));
                }
            } else {
                $ufo->load_layout("login");
            }
        } else {
            $ufo->die($ufo->status(403, "Parameters are not entered!"));
        }
    }

    public function check_admin_login () {
        global $ufo;
        $ufo->die($ufo->status(
            200, $ufo->check_login_admin()
        ));
    }

    public function load_page () {
        global $ufo;

        if ($ufo->isset_key($_POST, "page")) {
            $_POST["page"] = str_replace([".."], ["", ""], $_POST["page"]);

            if ($ufo->isset_post("save_last_page"))
                $ufo->set_session("ufo_last_page", $_POST);

            if ($ufo->isset_key($_POST, "plugin") && $_POST["plugin"]) {
                $ufo->load_layout($ufo->plugin_dir($_POST["plugin"]) . $_POST["page"], false);
            } else {
                $ufo->load_layout("pages" . SLASH . $_POST["page"]);
            }
        }
    }

    public function snippets () {
        global $ufo;
        if ($ufo->isset_key($_POST, "file")) {
            $ufo->load_layout("pages/snippets/" . $_POST["file"]);
        }
    }

    public function setting () {
        global $ufo, $db, $_, $admin_folder;

        $setting = $ufo->find_by_kv([
            "page", $_POST["page"]
        ], $ufo->get_array("settings"));

        if (isset($ufo->get_array("settings")[$setting])) {
            $setting = $ufo->get_array("settings")[$setting];

            if ($ufo->is_function($setting["layout"]))
                $ufo->die($setting["layout"]());

            $setting["layout"] = "$setting[layout].php";

            if (is_file($setting["layout"]))
                return require $ufo->slash_folder($setting["layout"]);
        }

        $ufo->load_layout("404");
    }

    public function login () {
        global $ufo;

        if (!$ufo->isset_post("inputs"))
            $ufo->die($ufo->status(403, "Access denied!"));

        extract($_POST["inputs"]);

        $login = $ufo->login_admin([
            "login_name" => $username ?? "",
            "password"   => $password ?? ""
        ]);

        $ufo->die($ufo->status(
            $login[0], $login[1]
        ));
    }

    public function statistics () {
        global $ufo;
        $ufo->die(json_encode(
            $ufo->get_array("statistics-dashboard"),
            JSON_UNESCAPED_UNICODE
        ));
    }

    public function front () {
        global $ufo;

        $ufo->isset_key($_POST, "front") || $ufo->die("Access denied!");

        $theme = THEMES . FRONT_THEME;

        if (is_file($theme))
            return require $theme;

        $ufo->die("Access denied!");
    }

    public function media () {
        global $ufo;

        $media  = new UFO_Media();
        $folder = isset($_POST['folder']) ? (is_dir($_POST['folder']) ? $ufo->slash_folder($_POST['folder'] . "/") : $ufo->back_folder() . FILES) : $ufo->back_folder() . FILES;
        $files  = $ufo->get_file_subfolder($folder, "link", $_POST['types'] ?? "*");
        $new_list_files = [];

        $media->set_folder($folder);

        foreach ($files as $item) {
            $info = pathinfo($item);
            $new_list_files[$info["filename"]] = [
                "image" => $ufo->available_type("img", $info["extension"]),
                "type"  => $info["extension"],
                "name"  => $info["filename"],
                "link"  => $item
            ];
        }

        die(json_encode([
            "folder" => $media->html_folders(),
            "files"  => $new_list_files,
            "back"   => $ufo->slash_folder(pathinfo($folder)["dirname"])
        ], JSON_UNESCAPED_UNICODE));
    }

    public function media_action () {
        global $ufo;
        $ACTION = [
            "delete"      => function () {
                global $ufo;
                return $ufo->do_work("ufo_fm_delete", $_POST);
            },
            "create_file" => function () {
                global $ufo;
                $create = $ufo->do_work("ufo_new_file", [
                    "dir"   => $_POST['address'],
                    "file"  => $_POST['filename'],
                    "type"  => $_POST['type']
                ]);
                return $ufo->status($create["status"], $create["message"]);
            },
            "info_file"   => function () {
                global $ufo;
                return $ufo->do_work("ufo_fm_detail_file", $_POST['address']);
            },
            "save_changed"  => function () {
                global $ufo;
                return $ufo->do_work("ufo_change_data_file", [
                    "file" => $_POST['address'],
                    "name" => $_POST['name'],
                    "content" => $_POST['content_file'] ?? "%NULL%"
                ]);
            },
            "open_folder"   => function () {
                global $ufo, $media;

                $address = $ufo->slash_folder($_POST['address']). SLASH;
                $files   = $media->html_files($address);
                $folders = $media->html_folders($address);
                $stats   = $media->html_stats();

                print_r(json_encode([
                    "back"     => $ufo->slash_folder(pathinfo($address)["dirname"]),
                    "location" => $address,
                    "files"    => $files,
                    "folders"  => $folders,
                    "stats"    => $stats
                ], JSON_UNESCAPED_UNICODE));
            },
            "create_folder" => function () {
                global $ufo, $media;
                $create = $media->new_folder($_POST['name'], $_POST['address']);
                return $ufo->status($create["status"], $create["message"]);
            },
            "upload" => function () {
                global $ufo;
                return $ufo->do_work("ufo_uploader", [
                    "folder" => $_POST['folder'],
                    "file"   => $_FILES['FILE']
                ]);
            }
        ];
        print_r($ufo->isset_key($ACTION, $_POST['action']) ? $ACTION[$_POST['action']]() : "");
    }

    public function menu () {
        global $ufo;
        $ACTIONS = [
            "add"    => function () {
                global $ufo;

                $fields = [
                    "title" => $_POST["title"],
                    "icon"  => $_POST["icon"],
                    "link"  => $_POST["link"],
                    "sub"   => $_POST["sub"] ?? 0
                ];
                $add  = (new UFO_Menu())->add($fields);

                if ($add) {
                    ob_start();
                    $ufo->load_layout("pages/snippets/menu-item", true, ".php", array_merge($fields, [
                        "id" => $add
                    ]));
                    $item = ob_get_clean();
                }

                return $ufo->status($add ? 200 : 503, $add ? [
                    "id"   => $add,
                    "item" => $item ?? ""
                ] : $ufo->lng("System error"));
            },
            "edit"   => function () {
                global $ufo;

                if ($ufo->isset_key($_POST, "edit")) {
                    $Edit = (new UFO_Menu())->update((int) $_POST["edit"], [
                        "title" => $_POST["title"],
                        "icon"  => $_POST["icon"],
                        "link"  => $_POST["link"]
                    ]);

                    return $ufo->status($Edit ? 200 : 503, $Edit ? $ufo->lng("Done successfully") : $ufo->lng("System error"));
                }

                return $ufo->status(403, $ufo->lng("Access denied"));
            },
            "get"    => function () {
                global $ufo;

                $Query = [
                    "hunter" => "menu",
                    "where"  => []
                ];

                if ($ufo->isset_post("menu"))
                    $Query["where"]["id"] = $_POST["menu"];

                if ($ufo->isset_post("sub"))
                    $Query["where"]["sub"] = (int) $_POST["sub"];

                $Menu  = new UFO_Explorer($Query);
                $Items = "";

                while ($Menu->hunt()) {
                    ob_start();
                    $ufo->load_layout("pages/snippets/menu-item", true, ".php", $Menu->hunted);
                    $Items .= ob_get_clean();
                }

                return $ufo->status(200, [
                    "items" => $Items
                ]);
            },
            "delete" => function () {
                global $ufo;

                $Delete = (new UFO_Menu())->delete((int) $_POST["menu"]);

                return $ufo->status($Delete ? 200 : 503, $Delete ? $ufo->lng("Done successfully") : $ufo->lng("System error"));
            },
            "sort"   => function () {
                global $ufo, $db;
                foreach ($_POST["items"] as $k => $v)
                    $db->update("menu", [
                        "display_order" => $k
                    ], "id", $v);
                return $ufo->status(200, $ufo->lng("Done successfully"));
            },
            "positions"       => function () {
                global $ufo;
                return $ufo->status(200, (new UFO_Menu())->positions());
            },
            "add_position"    => function () {
                global $ufo;

                if ($ufo->isset_post("name")) {
                    $ID   = "p" . rand(10000, 99999);
                    $Save = (new UFO_Menu())->add_position($ID, $_POST["name"], true);
                    return $ufo->status($Save ? 200 : 503, $Save ? [
                        "id"   => $ID,
                        "name" => $_POST["name"]
                    ] : $ufo->lng(
                        "System error"
                    ));
                }

                return $ufo->status(403, $ufo->lng("Access denied"));
            },
            "change_position" => function () {
                global $ufo;

                if (!$ufo->isset_post("menu"))
                    return $ufo->status(403, $ufo->lng("Access denied"));

                $change = (new UFO_Menu())->change_position(
                    (int) $_POST["menu"], $_POST["position"]
                );

                return $ufo->status($change ? 200 : 503, $change ? $ufo->lng(
                    "Done successfully"
                ) : $ufo->lng("System error"));
            },
            "delete_position" => function ( ) {
                global $ufo;

                $Delete = (new UFO_Menu())->delete_position($_POST["position"]);

                return $ufo->status($Delete === true ? 200 : 503, $Delete === true ? $ufo->lng(
                    "Done successfully"
                ) : (
                    is_string($Delete) ? $Delete : $ufo->lng("System error")
                ));
            }
        ];
        $ufo->die($ufo->isset_key($ACTIONS, $_POST["action"]) ? $ACTIONS[$_POST["action"]]() : $ufo->status(403, $ufo->lng("Access denied")));
    }

    public function members () {
        global $ufo;
        $ACTION = [
            "editor" => function () {
                global $ufo;

                ob_start();
                $ufo->load_layout("pages/snippets/add-member");
                $content = ob_get_flush();
                ob_clean();

                return json_encode([
                    "title"   => $_POST["type"] == "add" ? $ufo->lng("add user") : $ufo->lng("edit user"),
                    "content" => $content,
                    "button"  => $_POST["type"] == "add" ? $ufo->lng("add") : $ufo->lng("edit")
                ]);
            },
            "edit"   => function () {
                global $ufo;

                $status    = $ufo->status(403, "Access denied!");
                $has       = $ufo->has_in_array(["type", "username", "email", "no", "password", "name", "last_name"], $_POST);

                if ($has) {
                    if ($_POST['type'] == "add") {
                        if ($ufo->add_member($_POST)) {
                            $status = $ufo->status(200, $ufo->lng("Done successfully"));
                        } else {
                            $status = $ufo->status(503, $ufo->lng("System error"));
                        }
                    } else if ($_POST['type'] == "edit") {
                        if ($ufo->update_member($_POST, base64_decode($_POST['mem']))) {
                            $status = $ufo->status(200, $ufo->lng("Done successfully"));
                        } else {
                            $status = $ufo->status(503, $ufo->lng("System error"));
                        }
                    }

                }

                $ufo->die($status);
            },
            "remove" => function () {
                global $db, $ufo;

                if ($ufo->isset_key($_POST, "type") && $_POST["type"] == "multiple") {
                    $remove = false;
                    foreach ($_POST['list'] as $item) {
                        $remove = $db->remove("members", "uid", base64_decode($item));
                    }
                } else {
                    $remove = $db->remove("members", "uid", base64_decode($_POST['uid']));
                }

                return $ufo->status(
                    $remove ? 200 : 503,
                    $remove ? $ufo->lng("Done successfully") : $ufo->lng("System error")
                );
            }
        ];
        $ufo->die($ufo->isset_key($ACTION, $_POST["action"]) ? $ACTION[$_POST["action"]]() : "", 200);
    }

    public function managers () {
        global $ufo;
        $ACTION = [
            "add"    => function () {
                global $ufo, $db;

                $status = $ufo->status(403, "Access denied");
                $fields = $_POST["fields"] ?? [];

                if (!$ufo->has_in_array(["photo", "name", "last_name", "login_name", "email", "password"], $fields)) {
                    return $status;
                }

                if (empty($fields["login_name"]) || empty($fields["name"]) || empty($fields["last_name"]) || empty($fields["password"]) || empty($fields["email"])) {
                    return $ufo->status(0, $ufo->lng("Please check the fields"));
                }

                if (!filter_var($fields["email"], FILTER_VALIDATE_EMAIL)) {
                    return $ufo->status(0, $ufo->lng("The email is not valid"));
                }

                $exists = $db->query("SELECT * FROM `%prefix%admins` WHERE `name`='".$fields["name"]."' OR `login_name`='".$fields["login_name"]."' OR `email`='".$fields["email"]."'");

                if (!isset($exists[0])) {
                    $insert = $db->insert("admins", [
                        "name"       => $fields["name"],
                        "last_name"  => $fields["last_name"],
                        "login_name" => $fields["login_name"],
                        "email"      => $fields["email"],
                        "photo"      => $fields["photo"],
                        "password"   => $ufo->create_password($fields["password"]),
                        "hash_login" => "0x" . $ufo->hash_generator(),
                        "theme"      => "light",
                        "last_login" => "",
                        "ajax_key"   => "0x" . $ufo->hash_generator()
                    ]);
                    if ($insert) {
                        $status = $ufo->status(200, $db->insert_id());
                    } else {
                        $status = $ufo->status(503, $ufo->lng("System error"));
                    }
                } else {
                    $status = $ufo->status(100, $ufo->lng("Another administrator with this information is already registered"));
                }

                return $status;
            },
            "remove" => function () {
                global $ufo, $db;

                $status = $ufo->status(403, "Access denied");
                if (!isset($_POST["manager"])) {
                    return $status;
                }

                $transformPages    = (new UFO_Pages())->transformAll((int) $_POST["manager"], "admin", (int) $_POST["transform"], "admin");
                $transformComments = $ufo->transformCommentsTo((int) $_POST["manager"], (int) $_POST["transform"]);

                if ($transformPages && $transformComments) {
                    $remove = $db->remove("admins", "id", (int) $_POST["manager"]);
                    if ($remove) {
                        $status = $ufo->status(200, $ufo->lng("Done successfully"));
                    } else {
                        $status = $ufo->status(503, $ufo->lng("System error"));
                    }
                } else {
                    $status = $ufo->status(200, $ufo->lng("Some information was not transferred"));
                }

                return $status;
            },
            "edit"   => function () {
                global $ufo, $db;

                $status = $ufo->status(403, "Access denied");
                $fields = $_POST["fields"] ?? [];

                if (!$ufo->has_in_array(["photo", "name", "last_name", "login_name", "email", "password"], $fields)) {
                    return $status;
                }

                if (!isset($_POST["admin"]) || empty($fields["login_name"]) || empty($fields["name"]) || empty($fields["last_name"]) || empty($fields["password"]) || empty($fields["email"])) {
                    return $ufo->status(0, $ufo->lng("Please check the fields"));
                }

                if (!filter_var($fields["email"], FILTER_VALIDATE_EMAIL)) {
                    return $ufo->status(0, $ufo->lng("The email is not valid"));
                }

                $admin = $ufo->is_bas64($_POST["admin"]) ? base64_decode($_POST["admin"]) : $_POST["admin"];
                $admin = $db->get("admins", "id", $admin)[0] ?? false;

                $exists = $db->query("SELECT `id` FROM `%prefix%admins` WHERE `name`='".$fields["name"]."' OR `login_name`='".$fields["login_name"]."' OR `email`='".$fields["email"]."'");

                if ($admin) {
                    if (!isset($exists[1])) {
                        $update = $db->update("admins", [
                            "name"       => $fields["name"],
                            "last_name"  => $fields["last_name"],
                            "login_name" => $fields["login_name"],
                            "email"      => $fields["email"],
                            "photo"      => $fields["photo"],
                            "password"   => $fields["password"] == $admin["password"] ? $admin["password"] : $ufo->create_password($fields["password"])
                        ], "id", $admin["id"]);

                        if ($update) {
                            $status = $ufo->status(200, $ufo->lng("Done successfully"));
                        } else {
                            $status = $ufo->status(503, $ufo->lng("System error"));
                        }
                    } else {
                        $status = $ufo->status(100, $ufo->lng("Another administrator with this information is already registered"));
                    }
                } else {
                    $status = $ufo->status(0, $ufo->lng("Manager not found"));
                }

                return $status;
            }
        ];
        $ufo->die(isset($ACTION[$_POST['action']]) ? $ACTION[$_POST['action']]() : "", 200);
    }

    public function plugin () {
        $ACTION = [
            "detail"    => function () {
                global $ufo;
                $ufo->load_layout("pages/snippets/plugin-detail");
            },
            "shutdown"  => function () {
                global $ufo;
                return $ufo->do_work("ufo_shutdown_plugin", $_POST['plugin']) ? $ufo->status(200, $ufo->lng("Plugin successfully shut down")) : $ufo->status(503, $ufo->lng("System error"));
            },
            "active"    => function () {
                global $ufo;
                return $ufo->do_work("ufo_active_plugin", $_POST['plugin']) ? $ufo->status(200, $ufo->lng("Plugin successfully turned on")) : $ufo->status(503, $ufo->lng("System error"));
            },
            "upload"    => function () {
                global $ufo;
                return $ufo->do_work("ufo_upload_plugin", $_POST['plugin']) ? $ufo->status(200, $ufo->lng("Done successfully")) : $ufo->status(503, $ufo->lng("System error"));
            },
            "uninstall" => function () {
                global $ufo;
                return $ufo->do_work("ufo_uninstall_plugin", $_POST['plugin']) ? $ufo->status(200, $ufo->lng("Done successfully")) : $ufo->status(503, $ufo->lng("System error"));
            }
        ];
        die(isset($ACTION[$_POST['action']]) ? $ACTION[$_POST['action']]() : "");
    }

    public function template () {
        $ACTION = [
            "detail"   => function () {
                global $ufo;
                $ufo->load_layout("pages/snippets/template-detail");
            },
            "shutdown" => function () {
                global $ufo;
                return $ufo->do_work("ufo_shutdown_template", $_POST['template'] ?? "") ? $ufo->status(200, $ufo->lng("Template successfully shutdown")) : $ufo->status(503, $ufo->lng("System error"));
            },
            "active"   => function () {
                global $ufo;
                return $ufo->do_work("ufo_active_template", [
                    "template" => $_POST['template'] ?? "",
                    "mode"     => $_POST['mode'] ?? "set"
                ]) ? $ufo->status(200, $ufo->lng("Template successfully turned on")) : $ufo->status(503, $ufo->lng("System error"));
            },
            "upload"   => function () {
                global $ufo;
                return $ufo->do_work("ufo_upload_template", $_POST['template']) ? $ufo->status(200, $ufo->lng("Done successfully")) : $ufo->status(503, $ufo->lng("System error"));
            },
            "delete"   => function () {
                global $ufo;
                return $ufo->do_work("ufo_delete_template", $_POST['template']) ? $ufo->status(200, $ufo->lng("Done successfully")) : $ufo->status(503, $ufo->lng("System error"));
            },
            "preview"  => function () {
                global $ufo;
                if (isset($_POST["template"])) {
                    if (!session_id()) { session_start(); }

                    if (isset($_COOKIE["ufo_theme"])) {
                        setcookie("ufo_theme", "", time() - 3600, "/");
                    }

                    $_SESSION["ufo_theme"] = $_POST["template"];
                    $_SESSION["ufo_theme_admin_preview"] = true;

                    return $ufo->status(200, URL_WEBSITE);
                } else {
                    return $ufo->status(403, $ufo->lng("Access denied"));
                }
            }
        ];
        die(isset($ACTION[$_POST['action']]) ? $ACTION[$_POST['action']]() : "");
    }

    public function page_editor () {
        $ACTION = [
            "save"   => function () {
                global $ufo;
                return json_encode($ufo->do_work(
                    "ufo_page_editor_save", $_POST
                ), JSON_UNESCAPED_UNICODE);
            },
            "update" => function () {
                global $ufo;
                if (isset($_POST["page"]))
                    $_POST["page"] = (int) $_POST["page"];
                return json_encode($ufo->do_work(
                    "ufo_page_editor_update", $_POST
                ), JSON_UNESCAPED_UNICODE);
            },
            "get"    => function () {
                global $ufo;

                if (isset($_POST["page"])) {
                    $get = $ufo->do_work("ufo_page_editor_get", $ufo->is_bas64($_POST["page"]) ? (int) base64_decode($_POST["page"]) : $_POST["page"]);

                    if ($get)
                        return $ufo->status(200, $get);

                    return $ufo->status(404, $ufo->lng("Page not found"));
                }

                return $ufo->status(403, $ufo->lng("Parameters not defined"));
            }
        ];
        die(isset($ACTION[$_POST["action"]]) ? $ACTION[$_POST["action"]]() : "");
    }

    public function pages () {
        $ACTION = [
            "delete" => function () {
                global $ufo;
                if (isset($_POST["pages"])) {
                    if (!(new UFO_Pages())->delete($_POST["pages"])) {
                        return $ufo->status(503, $ufo->lng("System error"));
                    } else {
                        return $ufo->status(200, $ufo->lng("Done successfully"));
                    }
                } else {
                    return $ufo->status(403, $ufo->lng("Access denied"));
                }
            },
            "status" => function () {
                global $ufo;
                if (isset($_POST["pages"]) && isset($_POST["status"])) {
                    if ((new UFO_Pages())->change_status($_POST["pages"], $_POST["status"])) {
                        return $ufo->status(503, $ufo->lng("System error"));
                    } else {
                        return $ufo->status(200, $ufo->lng("Done successfully"));
                    }
                } else {
                    return $ufo->status(403, $ufo->lng("Access denied"));
                }
            }
        ];
        die(isset($ACTION[$_POST['action']]) ? $ACTION[$_POST['action']]() : "");
    }

    public function comment () {
        global $ufo, $db;

        $result = $ufo->status(403, "Access denied");

        if (!isset($_POST["action"]) || !isset($_POST["comment"])) { die($result); }

        if (!isset($db->get("comments", "id", $_POST["comment"])[0])) {
            die($ufo->status(404, $ufo->lng("Not found")));
        }

        switch ($_POST["action"]) {
            case "get":
                $result = $ufo->get_comment($_POST["comment"]) ?? [];
                die($ufo->status($result ? 200 : 503, $result));
            case "remove":
                $result = $ufo->remove_comment($_POST["comment"]);
                break;
            case "accept":
                $result = $ufo->accept_comment($_POST["comment"]);
                break;
            case "reply":
                if (!isset($_POST["text"])) { die($result); }
                $result = $db->insert("comments", [
                    "aid"      => $ufo->get_admin()["id"],
                    "comment"  => $_POST["text"],
                    "dateTime" => $ufo->dateTime(),
                    "_for"     => $db->get("comments", "id", $_POST["comment"])[0]["_for"] ?? "page",
                    "_reply"   => $_POST["comment"],
                    "accept"   => 1
                ]);
                break;
            case "info":
                $result = $ufo->get_comment($_POST["comment"]) ?? [];
                die($ufo->status($result ? 200 : 404, $result));
                break;
            default:$result = $ufo->status(404, "Not found");
        }

        die($ufo->status($result ? 200 : 503, $result ? $ufo->lng("Done successfully") : $ufo->lng("System error")));
    }

    public function get_all_category () {
        global $ufo, $db;

        $category = $db->get("category", [
            "_from" => $_POST["type"] ?? "page"
        ]);

        if (isset($_POST["editor"])) {
            foreach ($ufo->fire("ufo-editor-get-category") as $item)
                foreach ($item as $cat)
                    $category[] = $cat;
        }

        $_category = [];
        foreach ($category as $item)
            $_category[$item["id"]] = $item["title"];

        die(json_encode($_category, JSON_UNESCAPED_UNICODE));
    }

    public function create_category () {
        global $ufo;
        $create = (new UFO_Pages())->create_category($_POST);
        die($ufo->status(is_numeric($create) ? $create : 0, $create));
    }

    public function delete_category () {
        global $ufo;
        if (isset($_POST["category"])) {
            $delete = (new UFO_Pages())->delete_category($ufo->is_bas64($_POST["category"]) ? base64_decode($_POST["category"]) : $_POST["category"]);
            die($ufo->status($delete ? 200 : 503, $delete ? $ufo->lng("Done successfully") : $ufo->lng("System error")));
        } else {
            die($ufo->status(403, "Access denied!"));
        }
    }

    public function update_category () {
        global $ufo;
        if (isset($_POST["category"])) {
            $update = (new UFO_Pages())->update_category($ufo->is_bas64($_POST["category"]) ? base64_decode($_POST["category"]) : $_POST["category"], $_POST);
            die($ufo->status($update ? 200 : 503, $update ? $ufo->lng("Done successfully") : $ufo->lng("System error")));
        } else {
            die($ufo->status(403, "Access denied!"));
        }
    }

    public function get_category () {
        global $ufo;
        if (isset($_POST["category"])) {
            die(json_encode((new UFO_Pages())->get_category($ufo->is_bas64($_POST["category"]) ? base64_decode($_POST["category"]) : $_POST["category"]), JSON_UNESCAPED_UNICODE));
        } else {
            die($ufo->status(403, "Access denied!"));
        }
    }

    public function advance_setting () {
        (new UFO_Settings())->ajax();
    }

    public function save_security () {
        (new UFO_Security())->ajax_callback();
    }

    public function add_note () {
        global $ufo;

        $status = $ufo->status(403, $ufo->lng("Access denied"));
        if (!isset($_POST["note"])) { die($status); }

        $notes  = new UFO_Json(_CACHE_ . "admin/notes.json");
        $ID     = rand(0, 999999);
        $insert = $notes->push([
            "id"    => $ID,
            "admin" => $ufo->get_admin()["id"],
            "note"  => $_POST["note"]
        ]);

        if ($insert) {
            $status = $ufo->status(200, $ID);
        } else {
            $status = $ufo->status(503, $ufo->lng("System error"));
        }

        die($status);
    }

    public function remove_note () {
        global $ufo;

        $status = $ufo->status(403, $ufo->lng("Access denied"));
        if (!isset($_POST["id"])) { die($status); }

        $notes  = new UFO_Json(_CACHE_ . "admin/notes.json");
        $remove = $notes->where("id", $_POST["id"])->remove();

        if ($remove) {
            $status = $ufo->status(200, $ufo->lng("Done successfully"));
        } else {
            $status = $ufo->status(503, $ufo->lng("System error"));
        }

        die($status);
    }

    public function empty_logs () {
        global $ufo;
        $empty = (new UFO_Json(_CACHE_ . "admin/logs.json"))->empty();
        die($ufo->status($empty ? 200 : 503, $empty ? $ufo->lng("Done successfully") : $ufo->lng("System error")));
    }

    public function market () {
        $ACTION = [
            "dl" => function () {
                global $ufo;
                return $ufo->do_work("ufo_market_dl", $_POST);
            }
        ];
        die(isset($ACTION[$_POST['action']]) ? $ACTION[$_POST['action']]() : "");
    }

    public function system_update () {
        global $ufo;
        $ACTION = [
            "dnv" => function () {
                global $ufo;
                return $ufo->do_work("ufo_dnv_system", $_POST);
            }
        ];
        die($ufo->isset_key($ACTION, $_POST["action"]) ? $ACTION[$_POST["action"]]() : "");
    }

    public function selectable_table () {
        global $ufo, $db;

        extract($_POST);

        if ($db->table_exists($table)) {
            $pagination = [
                "page" => $paging ?? 1,
                "paging_action" => "ufo-selectable-table-paging",
                "limit" => $limit ?? $db->meta("table_rows")
            ];

            if (!empty($search) && isset($search["fields"]))
                if (!empty($search["value"]) && strlen($search["value"]) >= 3)
                    if (is_array($search["fields"]))
                        foreach ($search["fields"] as $field)
                            $db->helper->where($field, "%" . $search["value"] . "%", "LIKE", "OR");
                    else
                        $db->helper->where($search["fields"], "%" . $search["value"] . "%", "LIKE");

            if (isset($join)) {
                if (is_array($join))
                    foreach ($join as $k => $v) {
                        $db->helper->join($k, "%prefix%" . $k . "." . $v[0] . "=" . "%prefix%" . $table . "." . $v[1]);
                    }
            }

            $db->helper->orderBy($id);

            $rows = $db->pagination($table, $pagination, $where ?? []);
            $new_rows = [];
            $id_rows  = [];

            foreach ($rows["rows"] as $item) {
                $columns = [];

                if (isset($fields))
                    foreach ($fields as $field)
                        $columns[] = $ufo->tag(
                            "span",
                            $item[$field],
                            ["class" => "text-table-responsive"]
                       );

                $new_rows[] = $columns;
                $id_rows[]  = $item[$id ?? "id"];
            }

            $ufo->modern_table(
                "ufo-selectable-table",
                $titles ?? [],
                $new_rows,
                $id_rows,
                true
           );

            $ufo->die($ufo->get_modern_table("ufo-selectable-table") . (
                !isset($search["value"]) ? $rows["paging"] : ""
           ));
        } else $ufo->die($ufo->status(404, "Table not found"));
    }

}