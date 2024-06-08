<?php

/**
 * Copyright (c) 2022-2024 UFOCMS
 *
 * This software is licensed under the GPLv3 license.
 * See the LICENSE file for more information.
 */

final class UFO_Menu {

    public string $table = "menu";
    public UFO_Json $positions;

    public function init () {
        global $ufo;

        $ufo->add_array("ufo-explorer", [
            "name"   => "menu",
            "hunter" => function ($explorer) {
                return $this->get($explorer->query);
            }
        ]);
    }

    public function __construct ( ) {
        global $ufo, $admin_folder;

        $this->positions = new UFO_Json(
            $ufo->slash_folder($admin_folder . "content/cache/positions.json")
        );
    }

    /**
     * @param array $fields
     * @return int|bool
     * @throws Exception
     */
    public function add (array $fields) {
        global $ufo, $db;

        $Fields = array_merge([
            "title"    => "",
            "icon"     => "",
            "link"     => "",
            "sub"      => 0,
            "position" => "every-where"
        ], $fields);

        if (is_string($Fields["title"])) {
            if (mb_strlen($Fields["title"]) < 1)
                return false;
        } else return false;

        if (is_numeric($Fields["link"]))
            $Fields["link"] = $ufo->do_work("ufo_pages_get_full_url", (int) $Fields["link"]);

        if (!$ufo->equal($Fields["sub"], 0)) {
            $db->where("id", $Fields["sub"]);
            $menu = $db->helper->getOne("menu");

            if (!$ufo->isset_key($menu, "id"))
                return false;

            $Fields["position"] = $menu["position"];

            $db->where("sub", $Fields["sub"]);
            $db->helper->orderBy("id");
            $last_sub = $db->helper->getOne($this->table, "display_order");

            if (isset($last_sub["display_order"]))
                $Fields["display_order"] = $last_sub["display_order"] + 1;
        }

        if ($db->insert($this->table, $Fields))
            return $db->insert_id();

        return false;
    }

    /**
     * @param int $id
     * @param array $fields
     * @return bool
     * @throws Exception
     */
    public function update (int $id, array $fields): bool {
        global $ufo, $db;

        if ($ufo->isset_key($fields, "link") && is_numeric($fields["link"]))
            $fields["link"] = $ufo->do_work("ufo_pages_get_full_url", (int) $fields["link"]);

        return $db->update($this->table, $fields, [
            "id" => $id
        ]);
    }

    /**
     * @param array $query = [
     *      "where"     => Array,
     *      "only_menu" => Bool
     * ]
     * @throws Exception
     * @return array
     */
    public function get (array $query): array {
        global $ufo, $db;

        $query = $ufo->default([
            "where"     => [],
            "only_menu" => false
        ], $query);

        extract($query);

        foreach ($query["where"] as $k => $v)
            $db->where($k, $v);

        $db->helper->orderBy("display_order", "ASC");

        $lists = $db->get($this->table);

        if (!$only_menu) {
            foreach ($lists as $k => $list)
                $lists[$k]["submenu"] = $this->get([
                    "where"   => [
                        "sub" => $list["id"]
                    ]
                ]);
        }

        return $lists;
    }

    /**
     * @param int $id
     * @return bool
     * @throws Exception
     */
    public function delete (int $id): bool {
        return $this->__deep_delete($this->get([
            "hunter" => "menu",
            "where"  => ["id" => $id]
        ]));
    }

    /**
     * @param $menu
     * @return bool
     * @throws Exception
     */
    private function __deep_delete ($menu): bool {
        global $ufo, $db;

        $result = false;

        foreach ($menu as $item) {
            $result = $db->remove($this->table, "id", $item["id"]);
            if ($ufo->isset_key($item, "submenu"))
                $result = $this->__deep_delete($item["submenu"]);
        }

        return $result;
    }

    /**
     * @return array
     */
    public function positions (): array {
        global $_;
        return array_merge($this->positions->reverse()->get(), $_["menu_positions"] ?? []);
    }

    /**
     * @param string $id
     * @param string $name
     * @param bool $save
     * @return bool
     */
    public function add_position (string $id, string $name, bool $save = false): bool {
        global $ufo, $_, $admin_folder;

        if (!$ufo->isset_key($_, "menu_positions"))
            $_["menu_positions"] = [];

        if ($save) {
            $positions = $this->positions;

            if (!$ufo->isset_key($positions->get(), $id))
                $positions->add_val($id, $name);
        }

        if (!$ufo->isset_key($_["menu_positions"], $id))
            $_["menu_positions"][$id] = $name;

        return true;
    }

    /**
     * @param $position
     * @return bool|string
     */
    public function delete_position ($position) {
        global $ufo;
        if ($this->positions->where($position)->exists()) {
            return $this->positions->where($position)->remove();
        } else {
            return $ufo->lng("You cannot delete this position");
        }
    }

    /**
     * @param int|array $id
     * @param $position
     * @return bool
     * @throws Exception
     */
    public function change_position ($id, $position): bool {
        global $ufo;

        $result = false;
        $menu   = is_numeric($id) ? $this->get([
            "hunter" => "menu",
            "where"  => [
                "id" => $id
            ]
        ]) : $id;

        foreach ($menu as $item) {
            $result = $this->update($item["id"], [
                "position" => $position
            ]);
            if ($ufo->isset_key($item, "submenu"))
                $result = $this->change_position($item["submenu"], $position);
        }

        return empty($menu) || $result;
    }

    /**
     * @param $position
     * @return string
     */
    public function readable_position ($position): string {
        return $this->positions()[$position] ?? "";
    }

}