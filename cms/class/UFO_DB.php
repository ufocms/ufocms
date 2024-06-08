<?php

/**
 * Copyright (c) 2022-2024 UFOCMS
 *
 * This software is licensed under the GPLv3 license.
 * See the LICENSE file for more information.
 */

require "helper" . DIRECTORY_SEPARATOR . "db_helper.php";

class UFO_DB {

    public ?string $host;
    public ?string $pass;
    public string $user;
    public string $name;
    public ?int $port;
    public ?string $prefix;
    public ?string $charset;
    public ?string $collate;
    public ?string $socket;
    public db_helper $helper;

    /**
     * Setup DataBase
     */
    public function __construct (
        $host, $user, $pass, $db, $prefix = null,
        $charset = null, $collate = null,
        $port = null, $socket = null
    ) {
        /**
         * Add Variables
         */
        $this->host    = $host;
        $this->user    = $user;
        $this->pass    = $pass;
        $this->name    = $db;
        $this->port    = $port;
        $this->prefix  = $prefix;
        $this->charset = $charset;
        $this->collate = $collate;
        $this->socket  = $socket;

        /**
         * Set up the database to connect
         */
        $this->helper = new db_helper(
            $this->host,
            $this->user,
            $this->pass,
            $this->name,
            $this->port,
            $this->prefix,
            $this->charset,
            $this->socket
        );
    }

    /**
     * @param $key
     * @return mixed
     * @throws Exception
     */
    public function __get ($key) {
        return $this->meta($key);
    }

    /**
     * @param $key
     * @param $value
     * @return bool
     * @throws Exception
     */
    public function __set ($key, $value) {
        return $this->update_meta($key, $value);
    }

    /**
     * @param $table
     * @return bool
     * @throws Exception
     */
    public function table_exists ($table): bool {
        return $this->helper->tableExists($table);
    }

    /**
     * @param string $query
     * @return string|null
     */
    public function sanitize_string (string $query): ?string {
        try {
            return $this->helper->escape($query);
        } catch (Exception $e) {
            $this->helper->error($e);
            return null;
        }
    }

    /**
     * @param $prop
     * @param string|int $value
     * @param string $operator
     * @param string $cond
     * @return db_helper
     */
    public function where ($prop, $value = 'DBNULL', string $operator = '=', string $cond = 'AND'): db_helper {
        return $this->helper->where($prop, $value, $operator, $cond);
    }

    /**
     * @param $query
     * @return array|string
     * @throws Exception
     */
    public function query ($query) {
        return $this->helper->query($query);
    }

    /**
     * @param $_key
     * @return mixed|void
     * @throws Exception
     */
    public function meta ($_key) {
        try {
            return $this->where("_key", $_key)->getOne("meta")["_value"] ?? false;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * @param $_key
     * @return string|bool
     * @throws Exception
     */
    public function isset_meta ($_key) {
        return $this->meta($_key) !== FALSE;
    }

    /**
     * @param $_key
     * @param $_value
     * @return bool
     * @throws Exception
     */
    public function update_meta ($_key, $_value): bool {
        try {
            if ($this->isset_meta($_key))
                return $this->update("meta", [
                    "_value" => is_array($_value) ? serialize($_value) : $_value
                ], "_key", $_key);

            return $this->add_meta($_key, $_value);
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * @param $_key
     * @param $_value
     * @return bool
     * @throws Exception
     */
    public function add_meta ($_key, $_value): bool {
        return $this->isset_meta($_key) ? $this->update_meta($_key, $_value) : $this->insert('meta', [
            "_key" => $_key,
            "_value" => $_value
        ]);
    }

    /**
     * @param string $slug
     * @return string|bool
     * @throws Exception
     */
    public function slug (string $slug) {
        return $this->meta("slug_" . $slug);
    }

    /**
     * @param $table
     * @param $where_key
     * @param $where_value
     * @param string $operator
     * @param string $cond
     * @param $numRows
     * @param $columns
     * @return array|string
     * @throws ReflectionException
     */
    public function get ($table, $where_key = null, $where_value = null, string $operator = "=", string $cond = "AND", $numRows = null, $columns = null) {
        if (is_array($where_key)) {
            foreach ($where_key as $k => $v)
                $this->helper->where($k, $v, $operator, $cond);
        } else if (!empty($where_key) && !empty($where_value))
            $this->helper->where($where_key, $where_value, $operator, $cond);

        return $this->helper->get($table, $numRows, $columns);
    }

    /**
     * @param $table
     * @param bool $single
     * @return array|false|string
     * @throws Exception
     */
    public function get_columns ($table, bool $single = false) {
        if ($this->table_exists($table)) {
            $fields = $this->query("SHOW COLUMNS FROM `%prefix%" . $table . "`");
            $append = [];

            foreach ($fields as $item)
                $append[] = $item['Field'];

            return ($single ? $append : $fields);
        } else {
            return false;
        }
    }

    /**
     * @param string $table
     * @param bool $null
     * @return array
     * @throws Exception
     */
    public function get_fields (string $table, bool $null = true): array {
        $result = [];

        foreach ($this->get_columns($table) as $field) {
            if (!$null)
                if (!empty($field["Extra"]) || !empty($field["Default"]) || $field["Null"] == "YES") continue;
            $result[] = preg_replace('/[0-9]/', "", str_replace(["(", ")"], "", $field["Field"]));
        }

        return $result;
    }

    /**
     * @param $table
     * @param $data
     * @param $_key
     * @param $_value
     * @param string $cond
     * @return bool
     * @throws Exception
     */
    public function update ($table, $data, $_key, $_value = null, string $cond = "AND"): bool {
        /**
         * Check exists table
         * Check empty @array $data
         */
        if ($this->table_exists($table) && !empty($data)) {
            /**
             * Set Where
             */
            if (is_array($_key)) {
                foreach ($_key as $k => $v)
                    $this->helper->where($k, $this->sanitize_string($v), "=", $cond);
            } else {
                if (!empty($_key) && !empty($_value))
                    $this->helper->where($_key, $this->sanitize_string($_value), "=", $cond);
            }

            /**
             * Update Fields
             */
            return $this->helper->update($table, $data);
        }

        return false;
    }

    /**
     * @param string $table
     * @param array $data
     * @return bool|int
     * @throws Exception
     */
    public function insert (string $table, array $data) {
        /**
         * Check exists table
         * Check empty @array $data
         */
        if ($this->table_exists($table) && !empty($data)) {

            /**
             * Prevent exception or error
             *
             * Autofill - filling in empty fields
             */
            $fields = $this->get_fields($table, false);
            foreach ($fields as $items) {
                if (isset($data[$items])) continue;
                $data[$items] = 0;
            }

            /**
             * Insert to table
             */
            return $this->helper->insert($table, $data);
        }

        return false;
    }

    /**
     * @return numeric
     * @throws ReflectionException
     */
    public function insert_id () {
        return $this->helper->getInsertId();
    }

    /**
     * @param $table
     * @param $data
     * @param array $where
     * @param null $fields
     * @param string $operator
     * @param string $cond
     * @return array
     * @throws Exception
     */
    public function pagination ($table, $data, array $where = [], $fields = null, string $operator = "=", string $cond = "AND"): array {
        global $ufo; extract($data);

        try {
            $page = (int) $page;
            if ($page <= 0)
                $page = 1;

            if (!isset($limit))
                $limit = $this->meta("table_rows");

            foreach ($where as $k => $v)
                $this->helper->where($k, $v, $operator, $cond);

            $this->helper->pageLimit = $limit;
            $rows = $this->helper->arraybuilder()->paginate($table, $page, $fields);

            if ($page > $this->helper->totalPages)
                $page = 1;

            return [
                "rows" => $rows,
                "text" => $ufo->lng("showing") . " $page " . $ufo->lng("of") . " " . $this->helper->totalPages,
                "current" => $page,
                "total"   => $this->helper->totalPages,
                "paging"  => $ufo->paging([
                    "page"   => empty($rows) ? 1 : $page,
                    "total"  => $this->helper->totalPages == 0 ? 1 : $this->helper->totalPages,
                    "action" => $paging_action ?? ""
                ])
            ];
        } catch (Exception $e) {
            $ufo->error($e);
            return [];
        }
    }

    /**
     * @param $table
     * @param int $limit
     * @param int $page
     * @param $fields
     * @return array|false|string|db_helper
     * @throws Exception
     */
    public function paging ($table, int $limit = 10, int $page = 1, $fields = null) {
        $this->helper->pageLimit = $limit;
        return $this->helper->arrayBuilder()->paginate($table, $page, $fields);
    }

    /**
     * @param $table
     * @param $k
     * @param $v
     * @param string $cond
     * @return bool
     * @throws Exception
     */
    public function remove ($table, $k, $v = null, string $cond = "AND"): bool {
        if ($this->table_exists($table)) {
            if (is_array($k))
                foreach ($k as $key => $value)
                    $this->helper->where($key, $value, "=", $cond);
            else
                $this->helper->where($k, $v, "=", $cond);

            /**
             * Delete Row
             */
            $delete = $this->helper->delete($table);

            /**
             * Check Exists
             */
            return empty($v) ? $delete : !(
                isset($this->get($table, $k, $v)[0])
            );
        } else return false;
    }

    /**
     * Get all columns of table pages
     *
     * @return array|string
     * @throws Exception
     */
    public function columns (string $table, array $except = [], $full = true) {
        global $ufo, $db;

        $columns = $db->query("SHOW COLUMNS FROM %prefix%$table");

        foreach ($except as $exc) {
            foreach ($columns as $k => $column) {
                if ($exc == $column["Field"])
                    unset($columns[$k]);
            }
        }

        if (!$full)
            return $ufo->minifyArray($columns, "Field");
        else
            return $columns;
    }

}