<?php

/**
 * Copyright (c) 2023-2024 UFOCMS
 *
 * This software is licensed under the GPLv3 license.
 * See the LICENSE file for more information.
 */

final class UFO_Task {

    protected UFO_Json $json;

    public function __construct ( $mode = "edit", $key = null ) {
        global $admin_folder;

        $this->json = new UFO_Json($admin_folder . "content/cache/tasks.json");

        if ( $mode == "run" ) {
            return $this->run($key);
        }

        return true;
    }

    /**
     * @param $key
     * @return bool
     * @throws Exception
     */
    protected function run ( $key ): bool {
        global $ufo, $db;

        if ( $db->meta("tasks") == "on" ) {
            if ( $db->meta("task_key") == $key ) {

                /**
                 * Get all tasks
                 */
                $tasks = $this->all();

                /**
                 * Implementation and review of all tasks
                 */
                foreach ($tasks as $k => $v) {

                    try {

                        /**
                         * IF - It's time to execute, execute the task
                         */
                        if ( $v["next"] <= $ufo->dateTime() ) {

                            /**
                             * Result
                             */
                            $success = false;

                            /**
                             * IF - function exists
                             */
                            if ( isset($v["fn"]) ) {

                                /**
                                 * Run function
                                 */
                                $v["fn"]();
                                $success = true;

                            } else if ( isset($v["file"]) ) {

                                /**
                                 * Check file
                                 */
                                if ( file_exists($v["file"]) && is_file($v["file"]) ) {

                                    /**
                                     * Run script
                                     */
                                    require $v["file"];

                                    /**
                                     * Function -> name task -> run
                                     */
                                    if ( function_exists($v["name"]) ) {
                                        /**
                                         * Get result task
                                         */
                                        call_user_func($v["name"]);
                                        $success = true;
                                    }

                                } else {

                                    /**
                                     * Add log if file not exists
                                     */
                                    $ufo->add_log(
                                        $ufo->lng("Task execution error") . " - " . $v["name"],
                                        $ufo->lng("The file specified to execute the task could not be found"),
                                        "warning"
                                    );

                                }

                            }

                            /**
                             * IF - process is success add new time for task
                             */
                            if ( $success ) {

                                /**
                                 * Update task
                                 */
                                $this->json->where("name", $v["name"])->update([
                                    "next" => $this->taskToTime($v),
                                    "status" => "success"
                                ]);

                            } else {

                                /**
                                 * Update task
                                 */
                                $this->json->where("name", $v["name"])->update([
                                    "status" => "error"
                                ]);

                            }
                        }

                    } catch ( Exception $e ) {
                        /**
                         * Add exception to logs
                         */
                        $ufo->add_log("Exception task - " . $v["name"], $e, "danger");
                    }

                }

                return true;

            } else {

                /**
                 * Add log - password wrong
                 */
                $ufo->add_log(
                    $ufo->lng("Execution of tasks - password"),
                    $ufo->lng("Tasks failed because wrong password was entered, please check"),
                    "danger"
                );

                return false;

            }
        } else {
            return false;
        }
    }

    /**
     * @param array $array
     * @return string
     */
    protected function stringify ( array $array ): string {
        global $ufo;
        $i = ($ufo->isset_key($array, "i") ? $array["i"] : "*"); // Minutes 1-60
        $h = ($ufo->isset_key($array, "h") ? $array["h"] : "*"); // Hours 1-24
        $d = ($ufo->isset_key($array, "d") ? $array["d"] : "*"); // Day of the month 1-31
        $m = ($ufo->isset_key($array, "m") ? $array["m"] : "*"); // Month 1-12
        $y = ($ufo->isset_key($array, "y") ? $array["y"] : "*"); // Year
        return $i . " " . $h . " " . $d . " " . $m . " " . $y . " " . ($ufo->isset_key($array, "file") ? $array["file"] : "*");
    }

    /**
     * @param array $task
     * @return array
     */
    protected function parse ( array $task ): array {
        $exp = explode(" ", $task["task"]);
        $arr = [
            "i" => $exp[0],
            "h" => $exp[1],
            "d" => $exp[2],
            "m" => $exp[3],
            "y" => $exp[4],
            "name" => $task["name"],
            "next" => $task["next"]
        ];
        if ( $exp[5] != "*" ) $arr["file"] = $exp[5] . ".php";
        return $arr;
    }

    /**
     * @return array
     */
    public function all ( ): array {
        global $ufo;

        /**
         * Get all task
         */
        $tasks    = $this->json->get();

        /**
         * Get all the tasks functions
         */
        $fn_tasks = $ufo->tasks();

        /**
         * New list
         */
        $list     = [];

        /**
         * Add more data
         */
        foreach ( $tasks as $item ) {
            $data = [];

            /**
             * Function task
             */
            if ( $ufo->isset_key($fn_tasks, $item["name"]) ) {
                $data["fn"] = $fn_tasks[$item["name"]];
            }

            $list[] = $this->parse($item) + $data;
        }

        return $list;
    }

    /**
     * @param array $array
     * @return bool|false
     * @throws Exception
     */
    public function add ( array $array ): bool {
        global $ufo, $_;

        /**
         * Plugin & Template name
         */
        $from = (isset($_["this_template"]["manifest"]["name"]) ? " - Template ( ".$_["this_template"]["manifest"]["name"]." )" : (isset($_["this_plugin"]["manifest"]["name"]) ? " - Plugin ( ".$_["this_plugin"]["manifest"]["name"]." )" : "") );

        /**
         * IF - this template not is default prevent continue
         */
        if ( isset($_["this_template"]["set"]) ) {
            if ( !$_["this_template"]["set"] ) {
                return false;
            }
        }

        /**
         * Check parameters required
         */
        if ( !$ufo->isset_key($array, "name") ) {
            $ufo->die($ufo->error("Task name not is set" . $from));
            return false;
        }

        $all   = $this->all();
        $has   = false;
        $fix   = [];
        $times = [];

        /**
         * Check exists task
         */
        foreach ($all as $k => $v) {
            if ( $v["name"] == $array["name"] ) {
                $has = true; break;
            }
        }

        /**
         * Prevent continue
         */
        if ( $has ) return true;

        /**
         * Limit times
         */
        $limit = [
            "i" => [1, 60],
            "h" => [1, 24],
            "d" => [1, 31],
            "m" => [1, 12],
            "y" => [1, 999],
        ];

        /**
         * Check limit times
         */
        foreach ( $limit as $k => $v ) {
            if ( $ufo->isset_key($array, $k) ) {
                $time = $array[$k];
                if ( $time >= $v[0] && $time <= $v[1] ) {
                    $fix[$k] = $times[$k] = $time;
                }
            }
        }

        /**
         * IF - file isset
         */
        if ( $ufo->isset_key($array, "file") ) {
            /**
             * Check if file exists
             */
            $file = $ufo->slash_folder($array["file"] . ".php");
            if ( is_file($file) ) {
                /**
                 * Prevent tasks file run again
                 */
                if ( pathinfo($file)["filename"] != "tasks" ) {
                    /**
                     * Add to fix array
                     */
                    $fix["file"] = $array["file"];
                }
            } else {
                return false;
            }
        }

        /**
         * Task data
         */
        $task = [
            "name" => $array["name"],
            "next" => $this->next_time($times),
            "task" => $this->stringify($fix),
            "status" => "pending"
        ];

        /**
         * Add plugin id
         */
        if ( isset($_["this_plugin"]) ) {
            $task["plugin"] = $_["this_plugin"]["id"];
        }

        /**
         * Add template id
         */
        if ( isset($_["this_template"]) ) {
            $task["template"] = $_["this_template"]["id"];
        }

        /**
         * Add to tasks
         */
        return $this->json->push($task);
    }

    /**
     * @param string $name
     * @return bool
     */
    public function remove ( string $name ): bool {
        return $this->json->where("name", $name)->remove();
    }

    /**
     * @param string $name
     * @return false|mixed
     */
    public function status ( string $name ) {
        return $this->json->where("name", $name)->get()[0]["status"] ?? false;
    }

    /**
     * @param string $name
     * @return false|mixed
     */
    public function get ( string $name ) {
        return $this->json->where("name", $name)->get()[0] ?? false;
    }

    /**
     * @return array|mixed
     */
    public function getNormalRow ( ) {
        return $this->json->get();
    }

    /**
     * Calculate next time run
     *
     * @param array $times
     * @return false|string
     */
    protected function next_time ( array $times ): string {
        global $ufo;
        $addTime = []; $addTimeUnit = [];
        foreach ($times as $k => $time) {
            if ( $time != "*" ) {
                $addTime[]     = $time;
                $addTimeUnit[] = $k;
            }
        }
        return $ufo->addTime($addTime, $addTimeUnit);
    }

    /**
     * @param $task
     * @return false|string
     */
    protected function taskToTime ( $task ) {
        if ( is_array($task) ) {
            /**
             * Remove keys
             */
            unset($task["name"]);
            unset($task["next"]);
            unset($task["file"]);
            unset($task["status"]);

            if (isset($task["fn"]))
                unset($task["fn"]);

            /**
             * Readable time
             */
            return $this->next_time($task);
        } else {
            /**
             * Task to array
             */
            $task  = explode(" ", $task);

            /**
             * Get all times
             */
            $times = [$task[0], $task[1], $task[2], $task[3], $task[4]];

            /**
             * Units
             */
            $units = ["i", "h", "d", "m", "y"];

            /**
             * New unit values
             */
            $list  = [];

            /**
             * Add units values
             */
            foreach ($times as $k => $v) {
                if ( $v != "*" ) {
                    $list[$units[$k]] = $v;
                }
            }

            /**
             * Readable time
             */
            return $this->next_time($list);
        }
    }

}