<?php

/**
 * Copyright (c) 2024 UFOCMS
 *
 * This software is licensed under the GPLv3 license.
 * See the LICENSE file for more information.
 */

final class UFO_Account {

    /**
     * Do not execute this method, this method is automatically executed in the core of UFO
     *
     * @throws Exception
     * @return void
     */
    public function init ( ) {
        global $ufo;

        $this->preload();

        if ($ufo->is_admin())
            $this->admin();

        if (defined("FRONT")) {
            $this->rules();
            $this->forms();
        }

        if (defined("AJAX_FRONT"))
            $this->ajax();
    }

    /**
     * @return void
     */
    protected function preload ( ) {
        global $ufo;

        $ufo->add_work("ufo_set_verify_code", fn () => $this->set_verify_code());

        $ufo->add_work("ufo_structure_verify_code", function () {
            global $ufo, $db;
            return $ufo->is_json($db->verify_code) ? json_decode(
                $db->verify_code, true
            ) : [
                "numbers"   => 4,
                "alphabets" => 0
            ];
        });

        $ufo->add_work("ufo_account_get_upload_photo", function (): array {
            global $ufo, $db;

            $upload_photo = $db->account_upload_photo;
            $ufo->is_json($upload_photo, $upload_photo);

            if (!is_array($upload_photo)) {
                $upload_photo = [
                    "active" => true,
                    "types"  => ["png", "jpg", "jpeg"],
                    "size"   => 5,
                    "folder" => "profiles"
                ];
            }

            return $upload_photo;
        });
    }

    /**
     * @return void
     */
    protected function admin ( ) {
        global $ufo;

        $ufo->exert("ufo_advance_settings_list", function () {
            global $ufo, $db;

            $upload_photo = $ufo->do_work("ufo_account_get_upload_photo");
            $verify_code  = $ufo->do_work("ufo_structure_verify_code");

            return [
                "order" => 3,
                "title" => $ufo->lng("User account"),
                "html"  => [
                    $ufo->tag("div", $ufo->tag("span", $ufo->lng(
                        "Upload profile picture"
                    )), ["style" => "padding:0 5px"]),
                    $ufo->tag("div", $ufo->tag("div", function ( ) use ($upload_photo) {
                        global $ufo;

                        $HTML = "";
                        $BUTTONS = [$ufo->lng("Inactive"), $ufo->lng("Active")];

                        foreach ($BUTTONS as $k => $item) {
                            $attrs = ["data-rows" => $item, "data-setting" => "ufo-account-upload-photo", "data-val" => $k];

                            if ($k == (int) $upload_photo["active"])
                                $attrs["class"] = "active";

                            $HTML .= $ufo->tag("button", $item, $attrs);
                        }

                        return $HTML;
                    }, ["class" => "ufo-setting-group-btn ufo-setting-ufo-protocol"]), ["class" => "p-5px"]),

                    $ufo->tag("label", $ufo->lng("Profile picture size (MB)") . $ufo->single_input([
                        "value" => $upload_photo["size"],
                        "class" => "form-control mt-5 ufo-account-photo-size text-center"
                    ]), ["class" => "p-5px db"]),

                    $ufo->tag("label", $ufo->lng("Profile pictures folder") . $ufo->single_input([
                        "value" => $upload_photo["folder"],
                        "class" => "form-control mt-5 ufo-account-folder-profiles text-center"
                    ]), ["class" => "p-5px db"]),

                    $ufo->tag("label", $ufo->lng("Confirmation code expiration (seconds)") . $ufo->single_input([
                        "value" => $db->verify_timeout,
                        "class" => "form-control mt-5 ufo-timeout-verify-code text-center"
                    ]), ["class" => "p-5px db"]),

                    $ufo->tag("label", $ufo->lng("Verification code numbers") . $ufo->single_input([
                        "value" => $verify_code["numbers"],
                        "class" => "form-control mt-5 ufo-verify-code-numbers text-center"
                    ]), ["class" => "p-5px db"]),
                    $ufo->tag("label", $ufo->lng("The number of letters in the verification code") . $ufo->single_input([
                        "value" => $verify_code["alphabets"],
                        "class" => "form-control mt-5 ufo-verify-code-alphabets text-center"
                    ]), ["class" => "p-5px db"])
                ]
            ];
        });

        $ufo->exert("ufo_advance_settings_slugs", function () {
            return [
                "Forgot password" => "forgot_password",
                "User account verification" => "verify",
                "User account" => "account"
            ];
        });

        $ufo->exert("ufo_settings_save", function ($settings) {
            global $ufo, $db;

            $upload_photo = $ufo->do_work("ufo_account_get_upload_photo");

            $upload_photo["active"] = (bool) ($settings["ufo-account-upload-photo"] ?? $upload_photo["active"]);
            $upload_photo["size"]   = (int) ($settings["ufo-account-photo-size"] ?? $upload_photo["size"]);
            $upload_photo["folder"] = (string) ($settings["ufo-account-folder-profiles"] ?? $upload_photo["folder"]);

            $db->update_meta("account_upload_photo", json_encode($upload_photo));

            if (isset($settings["ufo-timeout-verify-code"]))
                $db->update_meta("verify_timeout", (int) $settings["ufo-timeout-verify-code"]);

            $verify_code = $ufo->do_work("ufo_structure_verify_code");

            $verify_code["numbers"]   = $settings["ufo-verify-code-numbers"] ?? $verify_code["numbers"];
            $verify_code["alphabets"] = $settings["ufo-verify-code-alphabets"] ?? $verify_code["alphabets"];

            $db->update_meta("verify_code", json_encode($verify_code));
        });
    }

    /**
     * @return void
     */
    protected function ajax ( ) {
        global $ufo;

        $ufo->exert([
            "ufo_submit_login_form",
            "ufo_submit_signup_form",
            "ufo_submit_verify_form",
            "ufo_submit_forgot_password_form"
        ], function ($form) {
            global $ufo;

            $status = $ufo->status(403, $ufo->lng("Access denied"));
            $fields = $_POST["fields"] ?? [];

            $form = $form . "_submit_form";
            if (method_exists($this, $form))
                $status = call_user_func([$this, $form], $status, $fields);

            return $status;
        });

        $ufo->add_ajax("ufo_account_verify_code", function ( ) {
            global $ufo, $db;

            if (!$ufo->isset_key($_SESSION, "ufo_verify_member"))
                return $ufo->status(0, [
                    "redirect" => $ufo->web_link() . $db->slug("login")
                ]);

            $send = $this->set_verify_code();

            return $ufo->status(
                $send ? 200 : 503,
                $send ? [
                    "text" => $ufo->lng("Verification code sent successfully"),
                    "time" => $ufo->dateInterval($send["expire"])->total_seconds
                ] : $ufo->lng("System error")
            );
        }, true);

        $ufo->add_ajax("ufo_account_save_info", function ( ) {
            global $ufo;

            if (!$ufo->isset_post("fields"))
                return $ufo->status(403, $ufo->lng("Access denied"));

            $fields = $_POST["fields"];
            $member = $ufo->get_member();
            $update_member = $ufo->update_member([
                "name"      => $fields["name"] ?? $member["name"],
                "last_name" => $fields["last_name"] ?? $member["last_name"],
                "email"     => filter_var(
                    $fields["email"] ?? $member["email"], FILTER_VALIDATE_EMAIL
                ),
                "no"        => $fields["no"] ?? $member["no"]
            ], $member["uid"]);

            $ufo->fire("ufo_account_save_info",
                $_POST["fields"], $member["uid"]
            );

            return $ufo->status(
                $update_member ? 200 : 503,
                $update_member ? $ufo->lng("Done successfully") : $ufo->lng("System error")
            );
        });

        $ufo->add_ajax("ufo_account_member_photo", function ( ) {
            global $ufo, $db;

            $meta = $db->account_upload_photo;
            $meta = $ufo->is_json($meta) ? json_decode($meta, true) : [];

            if ($meta["active"]) {
                new UFO_Media();

                $nameFILE = $ufo->hash_generator("md5");
                $typeFILE = explode(".", $_FILES["file"]["name"]);
                $typeFILE = array_pop($typeFILE);

                $upload   = $ufo->do_work("ufo_uploader", [
                    "file"    => $_FILES["file"],
                    "folder"  => FILES . $meta["folder"],
                    "size"    => $meta["size"],
                    "types"   => $meta["types"],
                    "name"    => $nameFILE,
                    "array"   => true
                ]);

                if (is_array($upload)) {
                    if ($upload[0] == 200) {
                        $photo  = $ufo->sanitize_link(
                            URL_FILES . $meta["folder"] . "/" . $nameFILE . "." . $typeFILE
                        );
                        $update = $ufo->update_member([
                            "photo" => $photo
                        ]);

                        return $ufo->status(
                            $update ? 200 : 503,
                            $update ? [
                                "text"  => $ufo->lng("Done successfully"),
                                "photo" => $photo
                            ] : $ufo->lng("System error")
                        );
                    }

                    return $ufo->status($upload[0], $upload[1]);
                } else {
                    return $ufo->status(503, $ufo->lng("System error"));
                }
            }

            return $ufo->status(403, $ufo->lng(
                "Access denied"
            ));
        });

        $ufo->exert("ufo_theme_ajax_setup",
            fn () => $ufo->fire("ufo_account_ajax_setup")
        );
    }

    /**
     * @return void
     * @throws Exception
     */
    protected function rules ( ) {
        global $ufo, $db, $_, $admin_folder;

        $_["ufo_account_path"] = $ufo->slash_folder($admin_folder . "layout/front/account/");

        if ($ufo->file_exists_theme("account")) {
            $page = $ufo->theme_path() . "account";
        } else {
            $page = $_["ufo_account_path"] . "index";
        }

        $ufo->add_rule($page, [
            $db->slug("account"),
            $db->slug("account") . "/(?'page'[^/]+)"
        ], null, fn () => $this->init_account());
    }

    /**
     * @return void
     * @throws Exception
     */
    protected function forms ( ) {
        global $ufo, $db;

        $ufo->add_array("ufo_forms", [
            "login"  => [
                "slug"  => $db->slug("login"),
                "title" => $ufo->lng("Login")
            ],
            "signup" => [
                "slug"  => $db->slug("signup"),
                "title" => $ufo->lng("Signup")
            ],
            "verify" => [
                "slug"  => $db->slug("verify"),
                "title" => $ufo->lng("Authentication")
            ],
            "forgot_password" => [
                "slug"  => $db->slug("forgot_password"),
                "title" => $ufo->lng("Forgot password")
            ]
        ]);

        $ufo->exert("ufo_default_form_fields", function ($form) {
            $form = $form . "_form";
            if (method_exists($this, $form))
                call_user_func([$this, $form]);
        });
    }

    /**
     * @return void
     * @throws Exception
     */
    protected function login_form ( ) {
        global $ufo, $db;

        if ($ufo->check_login_member())
            $ufo->die($ufo->redirect($ufo->web_link() . $db->slug("account")));

        echo $ufo->tag("div", $ufo->tag("img", null, [
            "src"   => $ufo->web_logo()
        ]), [
            "class" => "ufo-form-logo mb-10"
        ]);

        echo $ufo->single_input([
            "placeholder" => $ufo->lng("Username or email"),
            "name"        => "username",
            "type"        => "text",
            "class"       => "text-center",
            "required"    => true
        ]);

        echo $ufo->single_input([
            "placeholder" => $ufo->lng("Password"),
            "name"        => "password",
            "type"        => "password",
            "class"       => "text-center",
            "required"    => true
        ]);

        echo $ufo->tag("div",
            $ufo->tag("div", $ufo->tag("button", $ufo->lng("Login"), [
                "class" => "btn btn-primary font-size-14px",
                "style" => "min-width: 100px",
                "type"  => "submit"
            ]), [
                "class" => "flex align-center"
            ]) .
            $ufo->tag("div",
                $ufo->tag("a", $ufo->lng("Register"), [
                    "class" => "db font-size-14px mb-5",
                    "href"  => $ufo->web_link() . $db->slug("signup")
                ]) .
                $ufo->tag("a", $ufo->lng("Forgot password?"), [
                    "class" => "db font-size-13px",
                    "href"  => $ufo->web_link() . $db->slug("forgot_password")
                ]), [
                    "class" => "flex flex-center align-end flex-direction-column"
                ]
            ), ["class" => "grid-2"]
        );
    }

    /**
     * @param $status
     * @param $fields
     * @return string
     * @throws Exception
     */
    protected function login_submit_form ($status, $fields): string {
        global $ufo, $db;

        if (!$ufo->has_in_array([
            "username", "password"
        ], $fields)) return $status;

        $member = $ufo->login_member([
            "username" => $fields["username"],
            "password" => $fields["password"]
        ]);

        if (!$member)
            return $ufo->status(404, $ufo->lng("Incorrect information"));

        $redirect = $ufo->web_link();

        if ((int) $member["verify"] != 1 && $db->meta("accept-member") == "false") {
            $ufo->set_session("ufo_verify_member", $member["uid"]);
            $redirect .= $db->slug("verify");
        } else
            $redirect .= $db->slug("account");

        return $ufo->status(200, [
            "text"     => $ufo->lng("You entered"),
            "redirect" => $redirect
        ]);
    }

    /**
     * @return void
     * @throws Exception
     */
    protected function signup_form ( ) {
        global $ufo, $db;

        if ($ufo->check_login_member())
            $ufo->die($ufo->redirect($ufo->web_link() . $db->slug("account")));

        echo $ufo->tag("div", $ufo->tag("img", null, [
            "src"   => $ufo->web_logo()
        ]), [
            "class" => "ufo-form-logo mb-10"
        ]);

        echo $ufo->single_input([
            "placeholder" => $ufo->lng("Username"),
            "name"  => "username",
            "type"  => "text",
            "class" => "ufo-field-form-signup text-center"
        ]);

        echo $ufo->single_input([
            "placeholder" => $ufo->lng("Email"),
            "name"  => "email",
            "type"  => "email",
            "class" => "ufo-field-form-signup text-center"
        ]);

        echo $ufo->single_input([
            "placeholder" => $ufo->lng("Password"),
            "name"  => "password",
            "type"  => "password",
            "class" => "ufo-field-form-signup text-center"
        ]);

        echo $ufo->tag("div",
            $ufo->tag("div", $ufo->tag("button", $ufo->lng("Register"), [
                "class" => "btn btn-primary font-size-14px",
                "style" => "min-width: 100px",
                "type"  => "submit"
            ]), [
                "class" => "flex align-center"
            ]) .
            $ufo->tag("div",
                $ufo->tag("a", $ufo->lng("Login to account"), [
                    "class" => "db font-size-14px mb-5",
                    "href"  => $ufo->web_link() . $db->slug("login")
                ]), [
                    "class" => "flex flex-center align-end flex-direction-column"
                ]
            ), ["class" => "grid-2"]
        );
    }

    /**
     * @param $status
     * @param $fields
     * @return string
     * @throws ReflectionException
     * @throws Exception
     */
    protected function signup_submit_form ($status, $fields): string {
        global $ufo, $db;

        if (!$ufo->has_in_array([
            "username", "password"
        ], $fields)) return $status;

        $add_fields = [
            "password" => $fields["password"]
        ];

        if ($ufo->isset_key($fields, "username"))
            $add_fields["username"] = $fields["username"];

        if ($ufo->isset_key($fields, "email"))
            $add_fields["email"] = $fields["email"];

        if ($ufo->isset_key($fields, "no"))
            $add_fields["no"] = $fields["no"];

        /**
         * Check fields custom pattern
         */
        $patterns = $ufo->do_work("ufo_form_signup_patterns", $add_fields);
        if (is_array($patterns)) {
            if (isset($patterns["error"]))
                return $ufo->status(400, $patterns["error"]);
        }


        $add = $ufo->add_member($add_fields);

        if ($add == 0) {
            $status = $ufo->status(0, $ufo->lng("This information was registered by someone else"));
        } else if ($add && !$ufo->equal($add, 503)) {
            $redirect = $ufo->web_link();

            if ($db->meta("accept-member") == "false") {
                $ufo->set_session("ufo_verify_member", $add);
                $redirect .= $db->slug("verify");
            } else
                $redirect .= $db->slug("login");

            $status = $ufo->status(200, [
                "text"     => $ufo->lng("Registration is done"),
                "redirect" => $redirect
            ]);
        } else {
            $status = $ufo->status(503, $ufo->lng("System error"));
        }

        return $status;
    }

    /**
     * @return void
     * @throws Exception
     */
    protected function verify_form ( ) {
        global $ufo, $db;

        if (!$ufo->isset_key($_SESSION, "ufo_verify_member")) {
            if ($ufo->check_login_member())
                $_SESSION["ufo_verify_member"] = $ufo->get_member()["uid"];
            else
                $ufo->die($ufo->redirect($ufo->web_link()));
        }

        $member = $ufo->get_member($_SESSION["ufo_verify_member"]);

        if ($member["verify"] == 1)
            $ufo->die($ufo->redirect($ufo->web_link()));

        $verify = $this->set_verify_code();

        echo $ufo->tag("div", $ufo->tag("img", null, [
            "src"   => $ufo->web_logo()
        ]), [
            "class" => "ufo-form-logo mb-10"
        ]);

        $verify_code = json_decode($db->verify_code, true);
        $length_code = $verify_code["numbers"] + $verify_code["alphabets"];

        if (empty($member["email"]))
            echo $ufo->single_input([
                "placeholder" => $ufo->lng("Email"),
                "name"        => "email",
                "type"        => "email",
                "class"       => "text-center",
                "required"    => true
            ]);

        if (!empty($member["email"]))
            echo $ufo->single_input([
                "placeholder" => $ufo->lng("Code"),
                "value" => $_GET["code"] ?? "",
                "name"  => "code",
                "type"  => "text",
                "class" => "text-center",
                "minlength" => $length_code,
                "maxlength" => $length_code,
                "required"  => true
            ]);

        if (!empty($member["email"]))
            echo $ufo->tag("span", $ufo->lng(
                "An email containing a verification code has been sent to your email."
            ), ["class" => "font-size-13px db"]);

        if (!empty($member["email"])) {
            echo $ufo->tag("div",
                $ufo->tag("div", $ufo->tag("button", $ufo->lng("Confirm"), [
                    "class" => "btn btn-primary font-size-14px mt-10",
                    "style" => "min-width: 100px",
                    "type"  => "submit"
                ]), [
                    "class" => "flex align-center"
                ]) .
                $ufo->tag("div",
                    $ufo->tag("span", "00:00", [
                        "id" => "ufo-ms-countdown-verify",
                        "class" => "db mt-15 font-size-15px cursor-pointer",
                        "data-time" => $ufo->dateInterval($verify["expire"])->total_seconds
                    ]), [
                        "class" => "flex flex-end align-center"
                    ]), ["class" => "grid-2"]
            );
        } else echo $ufo->tag("button", $ufo->lng("Submit"), [
            "class" => "btn btn-primary font-size-14px mt-10",
            "style" => "min-width: 100px",
            "type"  => "submit"
        ]);
    }

    /**
     * @param $status
     * @param $fields
     * @return string
     * @throws Exception
     */
    protected function verify_submit_form ($status, $fields): string {
        global $ufo, $db;

        if (!$ufo->isset_session("ufo_verify_member") ||
            !$ufo->isset_key($fields, "code"))
            return $status;

        $member = $ufo->get_member($_SESSION["ufo_verify_member"]);

        if (!$ufo->isset_key($member, "uid")) {
            $ufo->unset_session("ufo_verify_member");
            return $status;
        }

        if ($ufo->isset_key($fields, "code")) {
            $verify = json_decode($member["verify"], true);

            if (!$ufo->isset_key($verify, [
                "code", "expire"
            ])) return $status;

            if ($verify["expire"] > $ufo->dateTime()) {
                if ($verify["code"] == strtoupper($fields["code"])) {
                    $verification = $ufo->update_member([
                        "verify" => 1
                    ], $member["uid"]);

                    return $ufo->status($verification ? 200 : 503, $verification ? [
                        "text" => $ufo->lng("Your account has been verified"),
                        "redirect" => $ufo->web_link() . $db->slug("account")
                    ] : $ufo->lng("System error"));
                }
            }

            $status = $ufo->status(403, $ufo->lng("The verification code is not valid"));
        } else if ($ufo->isset_key($fields, "email")) {
            if (!filter_var($fields["email"], FILTER_VALIDATE_EMAIL))
                return $ufo->status(0, $ufo->lng("Please enter a valid email"));

            if ($ufo->update_member([
                "email" => $fields["email"]
            ], $member["uid"]))
                $status = $ufo->status(200, [
                    "text" => $ufo->lng("The email was successfully registered"),
                    "redirect" => $ufo->get_url()
                ]);
            else $status = $ufo->status(
                200, $ufo->lng("System error")
            );
        }

        return $status;
    }

    /**
     * @return void
     * @throws Exception
     */
    protected function forgot_password_form () {
        global $ufo, $db;

        if ($ufo->check_login_member())
            $ufo->die($ufo->redirect($ufo->web_link() . $db->slug("account")));

        echo $ufo->tag("div", $ufo->tag("img", null, [
            "src"   => $ufo->web_logo()
        ]), [
            "class" => "ufo-form-logo mb-10"
        ]);

        /**
         * Steps to change password
         */
        if ($ufo->isset_get([
            "email", "code"
        ])) {
            $member = $db
                ->where("email", $_GET["email"])
                ->getOne("members");

            if (!empty($member)) {

                $more = [];

                $ufo->is_json($member["more"], $more);

                if (isset($more["reset_password"])) {
                    $reset_password = $more["reset_password"];

                    $ufo->is_json($reset_password, $reset_password);

                    if ($reset_password["expire"] > $ufo->dateTime()) {
                        if ($ufo->equal($reset_password["code"], strtoupper($_GET["code"]))) {

                            echo $ufo->single_input([
                                "placeholder" => $ufo->lng("New password"),
                                "name"        => "password",
                                "type"        => "password",
                                "class"       => "text-center",
                                "required"    => true
                            ]);

                            echo $ufo->tag("div", $ufo->tag("button", $ufo->lng("Reset password"), [
                                "class" => "btn btn-primary font-size-14px",
                                "style" => "min-width: 100px",
                                "type"  => "submit"
                            ]), [
                                "class" => "flex align-center"
                            ]);

                            return;
                        }

                        $ufo->die($ufo->redirect($ufo->web_link()));
                    } else {
                        echo $ufo->lng("Password recovery verification code has expired");
                        return;
                    }

                    return;
                }

            }

            $ufo->die($ufo->clearUrlParams());
        }

        /**
         * Email form
         */

        echo $ufo->single_input([
            "placeholder" => $ufo->lng("Email"),
            "name"        => "email",
            "type"        => "text",
            "class"       => "text-center",
            "required"    => true
        ]);

        echo $ufo->tag("div", $ufo->tag("button", $ufo->lng("Send verification code"), [
            "class" => "btn btn-primary font-size-14px",
            "style" => "min-width: 100px",
            "type"  => "submit"
        ]), [
            "class" => "flex align-center"
        ]);
    }

    /**
     * @param $status
     * @param $fields
     * @return string
     * @throws ReflectionException
     * @throws Exception
     */
    protected function forgot_password_submit_form ($status, $fields): string {
        global $ufo, $db;

        if ($ufo->isset_get(["email", "code"]) && isset($fields["password"])) {
            /**
             * Change password
             */

            $member = $db->where("email", $_GET["email"])->getOne("members");
            $more   = [];

            $ufo->is_json($member["more"], $more);

            if (isset($more["reset_password"])) {
                $reset_password = $more["reset_password"];

                $ufo->is_json($reset_password, $reset_password);

                if ($reset_password["expire"] > $ufo->dateTime()) {
                    if ($ufo->equal($reset_password["code"], strtoupper($_GET["code"]))) {

                        unset($more["reset_password"]);

                        $update_password = $ufo->update_member([
                            "password" => $fields["password"]
                        ], $member["uid"]);

                        $status = $ufo->status(
                            $update_password ? 200 : 503,
                            $update_password ? [
                                "redirect" => $ufo->web_link() . $db->slug("login"),
                            ] : null
                        );

                    } else {
                        $status = $ufo->status(403, $ufo->lng("The verification code is not valid"));
                    }
                } else {
                    $status = $ufo->status(0, $ufo->lng("Password recovery verification code has expired"));
                }
            }
        } else {
            /**
             * Send verify code
             */

            if (!$ufo->isset_key($fields, "email"))
                return $status;

            $email = $fields["email"];

            if (!filter_var($email, FILTER_VALIDATE_EMAIL))
                return $ufo->status(400, $ufo->lng("Please enter a valid email"));

            $member = $db->where("email", $email)->getOne("members");

            if (empty($member))
                return $ufo->status(404, $ufo->lng("Member not found"));

            $verify  = $ufo->verify_code();
            $process = $ufo->update_member([
                "more" => [
                    "reset_password" => json_encode($verify)
                ]
            ], $member["uid"]);

            if ($process) {
                $process = $ufo->send_mail(
                    $member["email"],
                    $ufo->lng("Reset password"),
                    $this->mail("reset_password", $member["email"], $verify["code"], $verify["expire"])
                );
            }

            $status = $ufo->status(
                $process ? 200 : 503,
                $process ? $ufo->lng(
                    "Password recovery link has been sent to you"
                ) : null
            );
        }

        return $status;
    }

    /**
     * @param $template
     * @param mixed ...$args
     * @return string
     */
    protected function mail ($template, ...$args): string {
        return [
            "verify" => function ($code, $expire) {
                global $ufo;
                return $ufo->mail_template("verify", [
                    "subject",
                    "verify_code",
                    "verify_expire",
                    "verify_link"
                ], [
                    $ufo->lng("User account verification"),
                    $code,
                    $expire,
                    $ufo->urlAddParam("code", $code, null, false)
                ]);
            },
            "reset_password" => function ($email, $code, $expire) {
                global $ufo;
                return $ufo->mail_template("reset_password", [
                    "subject",
                    "verify_code",
                    "verify_expire",
                    "verify_link"
                ], [
                    $ufo->lng("Reset password"),
                    $code,
                    $expire,
                    $ufo->urlAddParam([
                        "email" => $email,
                        "code"  => $code
                    ], null, null, false)
                ]);
            }
        ][$template](...$args);
    }

    /**
     * @return bool|string
     * @throws Exception
     */
    protected function set_verify_code () {
        global $ufo;

        if (!$ufo->isset_key($_SESSION, "ufo_verify_member"))
            return false;

        $member = $ufo->get_member($_SESSION["ufo_verify_member"]);

        if (!$ufo->isset_key($member, "uid")) return false;

        $verify = $ufo->verify_code();

        if ($ufo->is_json($member["verify"])) {
            $member["verify"] = json_decode($member["verify"], true);
            if ($ufo->isset_key($member["verify"], "expire"))
                if ($member["verify"]["expire"] > $ufo->dateTime())
                    return $member["verify"];
        }

        if (!empty($member["email"])) {
            if (!$ufo->send_mail(
                $member["email"],
                $ufo->lng("User account verification"),
                $this->mail("verify", $verify["code"], $verify["expire"])
            )) return false;
        }

        $ufo->fire("ufo_send_verify_code", $member, $verify);

        return $ufo->update_member([
            "verify" => json_encode($verify)
        ], $_SESSION["ufo_verify_member"]) ? $verify : false;
    }

    /**
     * @return void
     * @throws Exception
     */
    protected function init_account () {
        global $ufo, $db, $_;

        if (!$ufo->check_login_member())
            $ufo->die($ufo->redirect($ufo->web_link() . $db->slug("login")));
        else if (!$ufo->verified_member())
            $ufo->die($ufo->redirect($ufo->web_link() . $db->slug("verify")));

        $db->where("position", "user-account");
        $db->where("link", $ufo->end_url());
        $_["title"] = (
            $db->helper->getValue("menu", "title") ?? $ufo->lng("User account")
        ) . " - " . WEB_TITLE;

        $_["this_url"]    = $ufo->get_url();
        $_["this_member"] = $ufo->get_member();

        $this->works();
        $this->dashboard();
        $this->pages();

        $ufo->fire("ufo_account_setup");

        $this->add_style();
        $this->add_script();
        $this->add_meta();
    }

    /**
     * @return void
     */
    protected function add_style ( ) {
        global $ufo;

        $ufo->clear_style();

        $ufo->add_style(ASSETS . "css/account.css");

        $ufo->fire("ufo-account-style");
    }

    /**
     * @return void
     */
    protected function add_script ( ) {
        global $ufo;

        $ufo->clear_script();

        $ufo->add_script("jquery", ASSETS . "script/jquery.min.js", null, "top");

        $ufo->add_script("options", ASSETS . "script/options.js", "jquery", "top");

        $ufo->fire("ufo-account-script");

        $ufo->add_script("front", ASSETS . "script/front.js");
    }

    /**
     * @return void
     */
    protected function add_meta () {
        global $ufo;

        $ufo->clear_meta();

        $ufo->add_meta([
            "name"    => "robots",
            "content" => "noindex, nofollow"
        ]);

        $ufo->fire("ufo-account-meta");
    }

    /**
     * @return void
     */
    protected function works ( ) {
        global $ufo;

        $ufo->add_work("ufo_account_statistics", function () use ($ufo) {
            $statistics = $ufo->get_array("account_statistics");

            return $ufo->tag("div", function () use ($ufo, $statistics) {
                $cards = "";
                foreach ($statistics as $statistic) {
                    $cards .= $ufo->tag("div", $ufo->tag("div",
                        $ufo->tag("i", null, [
                            "class" => $statistic["icon"] ?? ""
                        ]), [
                            "class" => "icon",
                            "style" => $ufo->css([
                                "background" => $statistic["background"] ?? ""
                            ])
                        ]) . $ufo->tag("div", [
                            $ufo->tag("h4", $statistic["title"] ?? "", [
                                "class" => "title"
                            ]),
                            $ufo->tag("span", $statistic["subtitle"] ?? "", [
                                "class" => "subtitle"
                            ])
                        ], [
                            "class" => "content"
                        ]), [
                            "class" => "statistics-card"
                        ]);
                }
                return $cards;
            }, ["class" => "ufo-account-statistics"]);
        });
    }

    /**
     * @return void
     * @throws Exception
     */
    protected function dashboard ( ) {
        global $ufo, $db, $_;

        $ufo->add_array("account_statistics", [
            "title"    => $ufo->lng("Your last login"),
            "subtitle" => $ufo->structureDateTime(
                $ufo->get_member()["last_login"], true
            ),
            "icon"     => "ufo-icon-log-in"
        ]);
        $ufo->add_array("account_statistics", [
            "title"    => $ufo->lng("Comments"),
            "subtitle" => $db->where("mid", $_["this_member"]["uid"])->getValue(
                "comments", "COUNT(id)"
            ) . " " . $ufo->lng("Comment"),
            "icon"     => "ufo-icon-message-square"
        ]);
    }

    /**
     * @return void
     */
    protected function pages ( ) {
        global $ufo;

        $ufo->exert("ufo-account-page-comments", function () use ($ufo) {
            if ($ufo->file_exists_theme("account/comments"))
                $ufo->from_theme("account/comments");
            else
                $ufo->load_layout("front/account/pages/comments");
        });
    }

}
