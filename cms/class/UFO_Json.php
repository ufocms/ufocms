<?php

/**
 * Copyright (c) 2022-2024 UFOCMS
 *
 * This software is licensed under the GPLv3 license.
 * See the LICENSE file for more information.
 */

final class UFO_Json {

    protected bool $PUT     = true;

    protected string $FILE  = "";
    protected int $FLAGS    = JSON_UNESCAPED_SLASHES;

    protected array $DATA   = [];
    protected array $RESULT = [];
    protected bool $WHERE   = false;

    protected int $PAGES    = 0;

    /**
     * @param array|string $json
     * @param bool $put
     * @param int $flags
     */
    public function __construct ($json = [], bool $put = true, int $flags = JSON_UNESCAPED_UNICODE) {
        global $ufo;

        if (is_string($json)) {
            if (!empty($json) && file_exists($ufo->slash_folder($json))) {
                $this->FILE = $ufo->slash_folder($json);
                $this->DATA = json_decode(file_get_contents($this->FILE), true);
            }
        } else if (is_array($json))
            $this->DATA = $json;

        $this->PUT   = $put;
        $this->FLAGS = $flags;
    }

    /**
     * @return UFO_Json
     */
    public function reset (): UFO_Json {
        /**
         * Reset Data
         */
        $this->WHERE  = false;
        $this->RESULT = [];

        return $this;
    }

    /**
     * Delete All
     * @return bool
     */
    public function empty (): bool {
        $this->DATA = [];
        return $this->put();
    }

    /**
     * @return $this
     */
    public function reverse (): UFO_Json {
        $this->DATA = array_reverse($this->DATA);
        return $this;
    }

    /**
     * @return array|mixed
     */
    public function get () {
        if ($this->WHERE) {
            /**
             * Delete [#ufo_key#] From Rows
             */
            foreach ($this->RESULT as $k => $v) {
                if (is_array($v))
                    unset($this->RESULT[$k]["[#ufo_key#]"]);
            }

            $result = $this->RESULT;

            $this->RESULT = [];
            $this->WHERE  = false;
        } else return $this->DATA;
        return $result;
    }

    /**
     * @param int $page
     * @param int $limit
     * @return $this
     */
    public function paging (int $page, int $limit): UFO_Json {
        $total = $this->count();
        $this->PAGES = ceil($total / $limit);

        if ($page <= 0 || $page > $this->PAGES)
            $page = 1;

        $offset = (
            min(max($page, 1), $this->PAGES) - 1
        ) * $limit;

        if ($offset < 0)
            $offset = 0;

        $this->DATA = array_slice($this->DATA, $offset, $limit);

        return $this;
    }

    /**
     * @param $k
     * @param $v
     * @return $this
     */
    public function where ($k, $v = null): UFO_Json {
        global $ufo;

        $this->RESULT = [];
        $this->WHERE  = true;

        if (empty($v)) {

            if (is_array($k)) {
                foreach ($k as $item) {
                    if ($ufo->isset_key($this->DATA, $item)) {
                        $this->RESULT[$item] = $this->DATA[$item];
                    }
                }
            } else if ($ufo->isset_key($this->DATA, $k))
                $this->RESULT[$k] = $this->DATA[$k];

        } else {
            foreach ($this->DATA as $key => $value) {
                if ($ufo->isset_key($value, $k)) {

                    $value["[#ufo_key#]"] = $key;

                    if (is_array($v)) {
                        if (in_array($value[$k], $v)) {
                            $this->RESULT[] = $value;
                        }
                    } else {
                        if ($value[$k] == $v) {
                            $this->RESULT[] = $value;
                        }
                    }

                }
            }
        }

        return $this;
    }

    /**
     * @param array $array
     * @param bool $not_exists
     * @return bool
     */
    public function push (array $array, bool $not_exists = false): bool {
        global $ufo;

        if ($not_exists && $this->WHERE && !empty($this->RESULT))
            return false;

        foreach ($array as $k => $item)
            if ($ufo->is_function($item))
                $array[$k] = $item();

        $this->DATA[] = $array;

        return $this->put();
    }

    /**
     * @param array $array
     * @return bool
     */
    public function set_data (array $array): bool {
        $this->DATA = $array;
        return $this->put();
    }

    /**
     * @return bool|string
     */
    public function remove () {
        if ($this->WHERE) {
            if (!empty($this->RESULT)) {

                foreach ($this->RESULT as $k => $item) {
                    if (is_array($item))
                        unset($this->DATA[$item["[#ufo_key#]"]]);
                    else
                        unset($this->DATA[$k]);
                }

                // $this->DATA   = array_values($this->DATA);
                $this->RESULT = [];
                $this->WHERE  = false;

                return $this->put();
            }
            return false;
        } else {
            return "Please write the delete condition before executing the delete command!";
        }
    }

    /**
     * @param array $keys
     * @return bool|string
     */
    public function update (array $keys) {
        global $ufo;

        if ($this->WHERE) {
            if (!empty($this->RESULT)) {
                foreach ($this->RESULT as $item) {
                    foreach ($keys as $key => $value) {
                        if (isset($this->DATA[$item["[#ufo_key#]"]][$key])) {
                            if ($ufo->is_function($value))
                                $value = $value();
                            $this->DATA[$item["[#ufo_key#]"]][$key] = $value;
                        }
                    }
                }
                return $this->put();
            }
            return false;
        } else {
            return "Please write the update condition before executing the update command!";
        }
    }

    /**
     * @param array $keys
     * @return bool|string
     */
    public function add_key (array $keys) {
        global $ufo;

        if ($this->WHERE) {
            if (!empty($this->RESULT)) {
                foreach ($this->RESULT as $item) {
                    foreach ($keys as $key => $value) {
                        if (!isset($this->DATA[$item["[#ufo_key#]"]][$key])) {
                            if ($ufo->is_function($value))
                                $value = $value();
                            $this->DATA[$item["[#ufo_key#]"]][$key] = $value;
                        }
                    }
                }
                return $this->put();
            }
            return false;
        } else {
            return "Please write the add_key condition before executing the add_key command!";
        }
    }

    /**
     * @param string $k
     * @param $v
     * @return bool|false
     */
    public function add_val (string $k, $v): bool {
        $this->DATA[$k] = $v;
        return $this->put();
    }

    /**
     * @param string $k
     * @return bool|false
     */
    public function remove_key (string $k): bool {
        global $ufo;
        if ($ufo->isset_key($this->DATA, $k)) {
            unset($this->DATA[$k]);
            return $this->put();
        }
        return true;
    }

    /**
     * @return int
     */
    public function count (): int {
        return count($this->DATA);
    }

    /**
     * @return int
     */
    public function pages (): int {
        return $this->PAGES;
    }

    /**
     * @return bool
     */
    public function exists () {
        if (!$this->WHERE)
            return "You must enter a conditional statement before checking.";
        return !empty($this->RESULT) ?? false;
    }

    /**
     * @return UFO_Json
     */
    public function trigger_put ( ): UFO_Json {
        $this->PUT = !$this->PUT;
        return $this;
    }

    /**
     * @return bool
     */
    protected function put (): bool {
        if ($this->PUT)
            if (is_file($this->FILE))
                return file_put_contents($this->FILE, json_encode(
                    $this->DATA, $this->FLAGS
                ));
        else
            return true;
        return false;
    }

}