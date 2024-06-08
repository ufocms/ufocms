<?php

/**
 * Copyright (c) 2024 UFOCMS
 *
 * This software is licensed under the GPLv3 license.
 * See the LICENSE file for more information.
 */

final class UFO_Explorer {

    /**
     * Query structure
     *
     * @var array $query
     */
    public array $query = [
        "hunter"        => "pages",
        "category"      => [],
        "tags"          => [],
        "author"        => [],
        "status"        => 1,
        "limit"         => 8,
        "page"          => 1,
        "paging_action" => "",
        "type"          => "",
        "extend"        => "",
        "where"         => [],
        "search"        => null,
        "sort"          => "newest",
        "filters"       => [],
        "reset"         => true
    ];

    protected array $rows   = [];
    protected array $paging = [];

    public int $collected   = 0;
    public array $hunted    = [];

    protected array $prefix = [];

    /**
     * @param array $query
     * @throws Exception
     */
    public function __construct (array $query = []) {
        global $ufo;

        /** Default search */
        if ((
            $ufo->isset_get("search") || $ufo->isset_post("search")
        ) && !$ufo->isset_key($query, "search")) {
            $search = $_GET["search"] ?? $_POST["category"];
            $this->query["search"] = mb_strlen($search) >= 3 ? $search : null;
        }

        /** Default category */
        if ((
            $ufo->isset_get("category") || $ufo->isset_post("category")
        ) && !$ufo->isset_key($query, "category")) {
            $category = $_GET["category"] ?? $_POST["category"];

            if (!empty($category))
                $this->query["category"] = explode(",", $category);

            else if ($_SERVER["REQUEST_METHOD"] == "GET")
                $ufo->urlRemoveParam("category");
        }

        $this->query = $ufo->default(
            $this->query, $query
        );

        $this->__collect_data();
    }

    public function __call (string $method, array $arguments) {
        global $ufo;

        if ($ufo->isset_key($this->hunted, $method)) {
            if ($ufo->is_function($this->hunted[$method]))
                return $this->hunted[$method]($this, ...$arguments);
            else
                return $this->hunted[$method];
        }
    }

    public function __get ($key) {
        return $this->hunted[$key] ?? null;
    }

    public function __set ($name, $value) {
        $this->hunted[$name] = $value;
    }

    /**
     * @return bool
     */
    public function ready ( ): bool {
        return !empty($this->rows);
    }

    /**
     * @return bool
     */
    public function empty ( ): bool {
        return !$this->ready();
    }

    /**
     * @param ?Closure $method
     * @return array|bool|int
     */
    public function hunt (?Closure $method = null) {
        global $ufo;

        if ($ufo->is_function($method)) {
            $results = [];
            while ($this->hunt())
                $results[] = $method($this);
            return $results;
        }

        if ($this->collected > $this->count() - 1) {
            if ($this->query["reset"])
                $this->reset();
            return false;
        }

        $this->hunted = $this->rows[$this->collected] ?? [];

        $this->__extend();

        if ($ufo->isset_key($this->hunted, "extend") && !empty($this->hunted["extend"])) {
            if (is_object($this->hunted["extend"]))
                $this->hunted["extend"] = strtolower(get_class($this->hunted["extend"]));
            $this->__extend($this->hunted["extend"]);
        }

        return $this->collected++ == 0 ? true : $this->collected;
    }

    /**
     * Reset all hunts
     *
     * @return bool
     */
    public function reset ( ): bool {
        if ($this->collected > $this->count() - 1) {
            $this->collected = 0;
            $this->hunted    = [];
            $this->prefix    = [];
            return true;
        }
        return false;
    }

    /**
     * @return int
     */
    public function count ( ): int {
        return count($this->rows);
    }

    /**
     * @param string|int $index
     * @param string|null $thumbnail
     * @return bool|mixed|string|void|null
     * @throws Exception
     */
    public function photo ($index = -1, ?string $thumbnail = null) {
        global $ufo, $db;

        if (!isset($this->hunted["photo"]))
            return false;

        if ($ufo->is_array($this->hunted["photo"])) {
            $this->hunted["photo"] = json_decode($this->hunted["photo"], true);
        }

        if (is_string($index) ?? !empty($thumbnail)) {
            return $ufo->thumbnail($thumbnail ?? $index, -1, [
                "src" => $this->hunted["photo"][0] ?? $db->meta("error_photo"),
                "alt" => $this->title()
            ]);
        } else {
            return $index != -1 ? (
                $this->hunted["photo"][$index] ?? $db->meta("error_photo")
            ) : $this->hunted["photo"];
        }
    }

    /**
     * @return bool|string
     * @throws Exception
     */
    public function link ( ) {
        global $ufo;

        if (!$ufo->isset_key($this->hunted, "link"))
            return false;

        return $this->hunted["link"];
    }

    /**
     * @return array|false|mixed
     * @throws Exception
     */
    public function category ( ) {
        global $ufo;

        if (!$ufo->isset_key($this->hunted, "category"))
            return false;

        $ufo->is_array($this->hunted["category"], $this->hunted["category"]);

        return (new UFO_Pages())->get_category($this->hunted["category"]);
    }

    /**
     * @return bool|array
     */
    public function tags ( ) {
        global $ufo;

        if (!$ufo->isset_key($this->hunted, "tags"))
            return false;

        $this->hunted["tags"] = trim($this->hunted["tags"]);

        if (!empty($this->hunted["tags"]))
            return explode(",", $this->hunted["tags"]);

        return [];
    }

    /**
     * @param bool $readable
     * @return false|string
     */
    public function status (bool $readable = true) {
        global $ufo;

        if (!$ufo->isset_key($this->hunted, "tags"))
            return false;

        return $readable ? (new UFO_Pages())->status_to_text($this->hunted["status"]) : $this->hunted["status"];
    }

    /**
     * @return false|object
     * @throws Exception
     */
    public function author ( ) {
        global $ufo;

        if (!$ufo->isset_key($this->hunted, "author"))
            return false;

        $author = $ufo->is_json($this->hunted["author"]) ? json_decode($this->hunted["author"], true) : [];

        if (!is_array($author) || empty($author))
            return false;

        $author = array_merge($author,
            $author["from"] == "admin" ? $ufo->get_admin($author["id"]) : $ufo->get_member($author["id"])
        );

        return (object) $author;
    }

    /**
     * @return false|string
     * @throws Exception
     */
    public function time ($cTime = true) {
        global $ufo;

        if (!$ufo->isset_key($this->hunted, "dateTime"))
            return false;

        return $ufo->structureDateTime($this->hunted["dateTime"], $cTime);
    }

    /**
     * @return string
     */
    public function content ( ) {
        global $ufo;

        if (!$ufo->isset_key($this->hunted, "content"))
            return false;

        // All shortcodes are executed in UFO_Template.
        // There is no need for this anymore :)
        //
        // return $ufo->run_shortcodes($this->content);

        return $this->content;
    }

    /**
     * @param string|null $key
     * @return false|mixed
     */
    public function paging (?string $key = null) {
        return empty($key) ? $this->paging : (
            $this->paging[$key] ?? false
        );
    }

    /**
     * @return array
     * @throws Exception
     */
    protected function __explore ( ): array {
        global $ufo;

        extract($this->query);

        $info = $hunter;
        foreach ($ufo->get_array("ufo-explorer") ?? [] as $item) {
            if ($hunter == $item["name"]) {
                $info = $item; break;
            }
        }

        if (is_array($info) && $ufo->isset_key($info, "hunter")) {
            $explore = $info["hunter"]($this);
        } else {
            throw new \RuntimeException("The desired hunter(" . $hunter . ") to collect information was not found");
        }

        return is_array($explore) ? $explore : [];
    }

    /**
     * @return void
     * @throws Exception
     */
    protected function __collect_data ( ) {
        global $ufo;

        extract($this->query);

        $explore = $this->__explore();

        if ($limit != 0)
            $this->paging = $ufo->array_exclude($explore, ["rows"]);

        $this->rows = $explore["rows"] ?? $explore;
    }

    /**
     * @param string $extend
     * @return bool
     */
    protected function __extend (string $extend = ""): bool {
        global $ufo;

        $extend = $ufo->fire("ufo-explorer-" . $this->query["hunter"] . (
            !empty($extend) ? "-" : ""
        ) . $extend, $this);

        foreach ($extend as $item) {
            if (is_array($item)) {
                foreach ($item as $k => $v) {
                    if ($k == "prefix") {
                        $this->prefix = array_merge($this->prefix, $v);
                    } else {
                        $this->hunted[$k] = $v;
                    }
                }
            }
        }

        return true;
    }

}