<?php

/**
 * Copyright (c) 2022-2025 UFOCMS
 *
 * This software is licensed under the GPLv3 license.
 * See the LICENSE file for more information.
 */

final class UFO_Pages {

    public string $table = "pages";

    /**
     * Do not execute this method, this method is automatically executed in the core of UFO
     *
     * @throws Exception
     * @return void
     */
    public function init () {
        global $ufo;

        if ($ufo->is_admin()) {
            $this->tabs();
        }

        if (defined("FRONT"))
            $this->front();

        $ufo->add_array("ufo-explorer", [
            "name"   => "pages",
            "hunter" => function ($explorer) {
                global $db; extract($explorer->query);

                if (!$limit)
                    $limit = $db->meta("table_rows");

                foreach ($where as $prop => $value)
                    $db->where($prop, $value);

                if (isset($category)) {
                    if (is_array($category)) {
                        foreach ($category as $cat) {
                            $db->where("category", '%"' . $cat . '"%', "LIKE");
                        }
                    } else {
                        $db->where("category", '%"' . ((int) $category) . '"%', "LIKE");
                    }
                }

                return (new UFO_Pages())->all(
                    $type, $limit, $page,
                    $limit > 0 ? $paging_action : false, $search,
                    $status, $sort
                );
            }
        ]);
        $ufo->exert("ufo-explorer-pages", function ($explorer) {
            global $db;
            $explorer->link = URL_WEBSITE . $db->meta("slug_blog") . "/" . $explorer->link;
        });

        $ufo->add_array("ufo-explorer", [
            "name"   => "category",
            "hunter" => function ($explorer) {
                extract($explorer->query);
                return $this->all_category(
                    $search, $limit ?? false, $page,
                    $limit, $from ?? "page"
                );
            }
        ]);
        $ufo->exert("ufo-explorer-category", function ($explorer) {
            global $db;
            $explorer->link = URL_WEBSITE . $db->meta("slug_category") . "/" . $explorer->link;
        });

        $ufo->add_work("ufo_pages_get_full_url", function ($page) {
            global $ufo, $db;

            if (is_numeric($page))
                $page = $this->get((int) $page);

            if (empty($page))
                return false;

            if ($page["type"] == "article") {
                $page = $ufo->web_link() . $db->slug("blog") . "/" . $page["link"];
            } else if ($page["type"] == "page") {
                $page = $ufo->web_link() . $page["link"];
            } else {
                $page = (string) $ufo->do_work($page["type"] . "_pages_get_full_url", $page);
            }

            return $ufo->sanitize_link($page);
        });
    }

    /**
     * @return void
     */
    private function tabs () {
        global $ufo;

        $ufo->add_array("ufo-page-tab-items", [
            "title"    => $ufo->lng("pages"),
            "tab"      => "pages",
            "active"   => true,
            "include"  => $ufo->slash_folder(LAYOUT . "pages/snippets/pages")
        ]);
        $ufo->add_array("ufo-page-tab-items", [
            "title"    => $ufo->lng("category"),
            "tab"      => "category",
            "active"   => false,
            "include"  => $ufo->slash_folder(LAYOUT . "pages/snippets/category")
        ]);

        $ufo->add_array("ufo-toolbar-pages", [
            "title"  => $ufo->lng("all"),
            "style"  => "primary",
            "action" => "all"
        ]);
        $ufo->add_array("ufo-toolbar-pages", [
            "title"  => $ufo->lng("draft"),
            "style"  => "warning",
            "action" => 0
        ]);
        $ufo->add_array("ufo-toolbar-pages", [
            "title"  => $ufo->lng("published"),
            "style"  => "success",
            "action" => 1
        ]);
        $ufo->add_array("ufo-toolbar-pages", [
            "title"  => $ufo->lng("hidden"),
            "style"  => "secondary",
            "action" => 2
        ]);
        $ufo->add_array("ufo-toolbar-pages", [
            "title"  => $ufo->lng("encrypted"),
            "style"  => "info",
            "action" => 3
        ]);
    }

    /**
     * @return string[][]
     */
    public function get_status (): array {
        return [
            ["draft", "warning"],
            ["published", "success"],
            ["hidden", "secondary"],
            ["encrypted", "info"]
        ];
    }

    /**
     * @param int|null $status
     * @param string $style
     * @return string
     */
    public function status_to_text (?int $status, $style = true): string {
        global $ufo;

        $status  = (int) $status;
        $_status = $this->get_status();

        return ($ufo->isset_key($_status, $status) ? (
            $style ? $ufo->tag("span", $ufo->lng($_status[$status][0]), ["class"=>"ufo-badge " . $_status[$status][1]]) : $_status[$status][0]
       ) : "undefined");
    }

    /**
     * @param string $type
     * @param int $limit
     * @param int $page
     * @param bool|string $paging
     * @param string|bool $search
     * @param string|int $status
     * @param string $sort
     * @return array|string
     * @throws Exception
     */
    public function all (
        string $type = "page", int $limit = 0, int $page = 1, $paging = false,
        $search = false, $status = "all", string $sort = ""
    ) {
        global $ufo, $db;

        try {
            if ($limit == 0) {
                $limit = $db->meta("table_rows");
            }

            $where = ["type" => $type];

            if ($search)
                $db->where($search["prop"] ?? "title", '%' . ($search["value"] ?? $search) . '%', "LIKE");

            if ($paging || is_string($paging)) {
                if ($status != "all")
                    $where["status"] = $status;

                $fields = $this->__sort($sort);

                return $db->pagination($this->table, [
                    "page"  => $page,
                    "limit" => $limit,
                    "paging_action" => (is_string($paging) ? $paging : "page-table") . "-paging"
                ], $where, $fields);
            }

            return $db->get($this->table, "type", $type);
        } catch (Exception $e) {
            $ufo->error($e);
            return false;
        }
    }

    /**
     * @param array $data
     * @return int|string
     * @throws Exception
     */
    public function create (array $data = []) {
        global $ufo, $db;

        /**
         * For stop process
         */
        $continue = true;

        /**
         * Convert to small list
         */
        $status_list = function ($list = []) {
            foreach ($this->get_status() as $k => $status)
                $list[$status[0]] = $k;
            return $list;
        };
        $status_list = $status_list();

        /**
         * Important parameters
         */
        $important = ["title", "link", "content", "photo", "category", "tags", "status", "type", "author"];
        $stringPRM = join(", ", $important);

        /**
         * Check the data items in the important list
         */
        foreach ($important as $item) {
            if (!isset($data[$item]))
                $continue = false;
        }

        /**
         * ERROR : parameters
         */
        if (!$continue)
            return "Please enter all parameters : " . $stringPRM;

        extract($data);

        /**
         * ERROR : photo
         */
        if (!is_array($photo))
            return "The photo must be an array";

        /**
         * ERROR : category
         */
        if (!is_array($category))
            return "The category must be an array";

        /**
         * ERROR : status
         */
        if (!isset($status_list[$status]))
            return "The status defined is incorrect : " . join(", ", array_keys($status_list));

        /**
         * Rewrite author
         * ERROR : author
         */
        if (is_array($author)) {
            if (isset($author["id"]) && isset($author["from"])) {
                $author = [
                    "id"   => $author["id"],
                    "from" => $author["from"]
                ];
            } else $author = ["id" => 0, "from" => "unknown"];
        } else $author = ["id" => 0, "from" => "unknown"];

        /**
         * Check exists
         */
        if ($this->exists($title))
            return "There is a page with this name";

        /**
         * Insert & Return result
         */
        return $db->insert($this->table, [
            "title"    => (string) $title,
            "content"  => (string) $content,
            "photo"    => json_encode($photo),
            "link"     => $link,
            "category" => json_encode($category),
            "tags"     => is_array($tags) ? json_encode($tags) : $tags,
            "status"   => $status,
            "type"     => in_array($type, ["page", "article"]) ? $type : "page",
            "author"   => json_encode($author),
            "password" => $password ?? "",
            "dateTime" => $ufo->dateTime()
        ]) ? 200 : 503;
    }

    /**
     * @param $page
     * @return bool
     * @throws Exception
     */
    public function exists ($page): bool {
        global $db;

        $key = "title";

        if (is_numeric($page))
            $key = "id";

        return isset($db->get($this->table, $key, $page)[0]);
    }

    /**
     * @param $id
     * @param bool $decode
     * @return false|mixed
     * @throws ReflectionException
     */
    public function get ($id, bool $decode = true) {
        global $ufo, $db;

        $key = "id";
        if (is_string($id))
            $key = "title";

        $page = $db->get($this->table, $key, $id);

        if (isset($page[0])) {
            $page = $page[0];
            
            $ufo->is_json($page["author"], $page["author"]);
            $ufo->is_array($page["photo"], $page["photo"]);
            $ufo->is_array($page["category"], $page["category"]);
            
            if ($decode && is_array($page["category"])) {
                $categories = [];
                foreach ($page["category"] as $item) {
                    $category = $db->where("id", $item)->getOne("category");
                    if (!empty($category))
                        $categories[$category["id"]] = $category["title"];
                }
                $page["category"] = $categories;
            }
        }

        return $page ?? false;
    }

    /**
     * @param $pages
     * @return bool
     * @throws Exception
     */
    public function delete ($pages): bool {
        global $ufo, $db;

        $error = false;

        if (is_array($pages)) {
            foreach ($pages as $item) {
                $item = $ufo->is_bas64($item) ? base64_decode($item) : $item;
                if (!$db->remove($this->table, "id", $item)) {
                    $error = true;
                } else {
                    $file = $ufo->slash_folder(_CACHE_ . "editor/" . md5($item) . ".ufo");
                    if (file_exists($file)) {unlink($file);}
                }
            }
        } else {
            $pages = $ufo->is_bas64($pages) ? base64_decode($pages) : $pages;
            $error = !$db->remove($this->table, "id", $pages);
            $file  = $ufo->slash_folder(_CACHE_ . "editor/" . md5($pages) . ".ufo");
            if (!$error) {
                if (file_exists($file)) {unlink($file);}
            }
        }

        return !$error;
    }

    /**
     * @param $pages
     * @param $status
     * @return bool
     * @throws Exception
     */
    public function change_status ($pages, $status): bool {
        global $ufo, $db;

        $error = false;

        if (is_array($pages)) {
            foreach ($pages as $item) {
                $item = $ufo->is_bas64($item) ? base64_decode($item) : $item;
                if (!$db->update($this->table, ["status" => (int) $status], "id", $item)) {
                    $error = true;
                }
            }
        } else {
            $pages = $ufo->is_bas64($pages) ? base64_decode($pages) : $pages;
            $error = !$db->update($this->table, ["status" => (int) $status], "id", $pages);
        }

        return $error;
    }

    /**
     * @param $id
     * @return array
     * @throws Exception
     */
    public function in_category ($id): array {
        global $db;
        return [
            "page"    => $db->where("category", '%"' . $id . '"%', "LIKE")->where("type", "page")->query("SELECT COUNT(id) as c FROM `%prefix%$this->table`")[0]["c"],
            "article" => $db->where("category", '%"' . $id . '"%', "LIKE")->where("type", "article")->query("SELECT COUNT(id) as c FROM `%prefix%$this->table`")[0]["c"],
            "other"   => $db->where("category", '%"' . $id . '"%', "LIKE")->where("type", "page", "!=")->where("type", "article", "!=")->query("SELECT COUNT(id) as c FROM `%prefix%$this->table`")[0]["c"],
        ];
    }

    /**
     * @param string|null $search
     * @param bool $paging
     * @param int $page
     * @param int $limit
     * @param string $from
     * @return array
     * @throws Exception
     */
    public function all_category (string $search = null, bool $paging = false, int $page = 1, int $limit = 0, string $from = "page"): array {
        global $ufo, $db;

        if ($paging) {
            if ($limit == 0) {
                $limit = $db->meta("table_rows");
            }
            $row = array_reverse($db->pagination("category", [
                "page"  => $page,
                "limit" => $limit,
                "paging_action" => "category-table-paging"
            ], ["_from" => $from]));
        } else if ($search) {
            $search = $db->sanitize_string($search);
            $row = [
                "rows" => $db->query("SELECT * FROM `%prefix%category` WHERE `title` LIKE '%$search%' AND `_from`='$from'")
            ];
        } else {
            $row = ["rows" => $db->get("category", "_from", $from)];
        } $fix = [];

        foreach ($row["rows"] as $item) {
            $item["used"] = $this->in_category($item["id"]);
            $fix[] = $item;
        }

        $row["rows"] = $fix;

        return $row;
    }

    /**
     * @param $id
     * @return array|mixed
     * @throws Exception
     */
    public function get_category ($id) {
        global $db;

        if (is_array($id)) {

            /**
             * Prevent MySqli query error
             */
            foreach ($id as $k => $v) {
                if (!is_numeric($v))
                    unset($id[$k]);
            }

            $category = array_values($id);
            if (!empty($category))
                $result = $db->query("SELECT * FROM `%prefix%category` WHERE `id` IN (" . implode(",", $category) . ")");

        } else {
            $category = $db->get("category", "id", $id);
            $result = $category[0] ?? [];
        }

        return $result ?? false;
    }

    /**
     * @param $data
     * @return int|string
     * @throws Exception
     */
    public function create_category ($data) {
        global $ufo, $db;

        $continue  = true;
        $important = ["title", "link"];
        $stringPRM = join(", ", $important);

        /**
         * Check the data items in the important list
         */
        foreach ($important as $item) {
            if (!isset($data[$item])) {
                $continue = false;
            }
        }

        /**
         * ERROR : parameters
         */
        if (!$continue)
            return "Please enter all parameters : $stringPRM";

        extract($data);

        /**
         * Insert & Return result
         */
        return $db->insert("category", [
            "title"       => (string) $title,
            "photo"       => (string) ($photo ?? ""),
            "description" => (string) ($description ?? ""),
            "link"        => (string) $link,
            "_from"       => (string) ($from ?? "page")
        ]) ? 200 : 503;
    }

    /**
     * @param int $category
     * @return bool
     * @throws Exception
     */
    public function delete_category (int $category): bool {
        global $db;
        return $db->remove("category", "id", $category);
    }

    /**
     * @param $category
     * @param $data
     * @return int|string
     * @throws Exception
     */
    public function update_category ($category, $data) {
        global $ufo, $db;

        $continue  = true;
        $important = ["title", "link"];
        $stringPRM = join(", ", $important);

        /**
         * Check the data items in the important list
         */
        foreach ($important as $item) {
            if (!isset($data[$item])) {
                $continue = false;
            }
        }

        /**
         * ERROR : parameters
         */
        if (!$continue)
            return "Please enter all parameters : $stringPRM";

        extract($data);

        /**
         * Update & Return result
         */
        return $db->update("category", [
            "title"       => (string) $title,
            "photo"       => (string) ($photo ?? ""),
            "description" => (string) ($description ?? ""),
            "link"        => (string) $link
        ], "id", $category) ? 200 : 503;
    }

    /**
     * @param $id
     * @param $from
     * @param $toID
     * @param $toFrom
     * @return bool
     * @throws Exception
     */
    public function transformAll ($id, $from, $toID, $toFrom): bool {
        global $ufo, $db;

        $success = true;

        foreach ($db->get("$this->table") as $item) {
            if ($ufo->is_json($item["author"])) {
                $author = json_decode($item["author"], true);
                if ($author["id"] == $id && $author["from"] == $from) {
                    $success = $db->update("$this->table", [
                        "author" => json_encode([
                            "id"   => $toID,
                            "from" => $toFrom
                        ], JSON_UNESCAPED_UNICODE)
                    ], "id", $item["id"]);
                }
            }
        }

        return $success;
    }

    /**
     * @return void
     */
    public function checkPassword () {
        global $ufo;
        $ufo->add_source('ufo.register("register", () => ufo.do({name:"ufo_password_page"}))');
    }

    /**
     * @param $sort
     * @return string[]|null
     * @throws Exception
     */
    protected function __sort ($sort): ?array {
        global $db;

        $fields = null;
        $sort   = explode("*", $sort);

        switch ($sort[0]) {
            case "most-popular":
                unset($sort[0]);

                $db->helper
                    ->join("comments", "%prefix%$this->table.id=%prefix%comments.pid AND %prefix%comments.accept=1", "LEFT OUTER")
                    ->groupBy("$db->prefix$this->table.id")
                    ->orderBy("$db->prefix$this->table.id")
                    ->orderBy("rate");

                foreach ($sort as $s) {
                    if ($s == "limit-null-rate") {
                        $db->helper->where("rate", "null", "!=");
                        break;
                    }
                }

                $fields = ["AVG(%prefix%comments.rate) as rate", "%prefix%$this->table.*"];
                break;
            case "newest":
                $db->helper->orderBy("%prefix%$this->table.id");
                break;
        }

        return $fields;
    }

    /**
     * @return void
     * @throws Exception
     */
    protected function front () {
        global $ufo;

        /**
         * Configure this page
         */
        $ufo->exert("ufo_theme_setup", function () {
            global $ufo, $db, $_;

            $page = $ufo->this_page(null);

            if (empty($page["link"]))
                return false;

            $_["title"] = "$page[title] - $_[title]";

            /**
             * If this page is draft or hidden, no one can access it
             * and it will be redirected to the homepage link of the
             * website.
             */
            if (
                $ufo->equal($page["status"], 0) ||
                $ufo->equal($page["status"], 2)
            ) $ufo->die($ufo->redirect(
                $ufo->web_link()
            ));

            /**
             * Check the password
             */
            if (!empty($page["password"])) {
                if (!$ufo->isset_get("password")) {
                    /**
                     * Prevent SEO of this page
                     */
                    $ufo->add_meta([
                        "name"    => "robots",
                        "content" => "noindex, nofollow"
                    ]);

                    /**
                     * Replace the form password with body html
                     */
                    $ufo->clear_body($ufo->tag("form", [
                        $ufo->tag("h4", $ufo->lng("Enter the page password"), [
                            "class" => "text-center width-100-cent mb-20"
                        ]),
                        $ufo->single_input([
                            "name" => "password",
                            "type" => "password",
                            "placeholder" => $ufo->lng("Password"),
                        ]),
                        $ufo->btn($ufo->lng("Confirm"), "font-size-14px", "btn btn-primary", [
                            "type" => "submit"
                        ])
                    ], [
                        "action" => $ufo->full_url(),
                        "method" => "GET",
                        "class"  => "ufo-form-password-page"
                    ]));

                } else {
                    /**
                     * Check the password
                     */
                    if (!$ufo->equal($page["password"], $_GET["password"]))
                        $ufo->die($ufo->urlRemoveParam("password"));
                }
            } else if ($ufo->success($db->seo)) {
                /** Setup seo tags */
                $ufo->add_meta([
                    "name" => "title",
                    "content" => $page["title"]
                ]);
                $ufo->add_meta([
                    "name" => "keywords",
                    "content" => $page["tags"]
                ]);
                $ufo->add_meta([
                    "name" => "description",
                    "content" => $page["short_desc"]
                ]);
            }
        });
    }

}
