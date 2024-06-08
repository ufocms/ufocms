<?php

/**
 * Copyright (c) 2022-2024 UFOCMS
 *
 * This software is licensed under the GPLv3 license.
 * See the LICENSE file for more information.
 */

final class UFO_OS {

    private bool $real_usage = true;
    private array $statistics = [];

    /**
     * @param bool $unit
     * @return int|string
     */
    public function current_mem_usage (bool $unit = true) {
        global $ufo;
        $mem = memory_get_usage($this->real_usage);
        $cnt = $ufo->convert_size($mem, 0);
        return $unit ? "$cnt[size] $cnt[unit]" : $mem;
    }

    /**
     * @param bool $unit
     * @return int|string
     */
    public function peak_mem_usage (bool $unit = true) {
        global $ufo;
        $mem = memory_get_peak_usage($this->real_usage);
        $cnt = $ufo->convert_size($mem, 0);
        return $unit ? "$cnt[size] $cnt[unit]" : $mem;
    }

    /**
     * @param $usage
     * @return float|int
     */
    public function mem_usage_to_percentage ($usage) {
        global $ufo;
        $percentage = round((int)$ufo->convert_to_byte((int)$usage, "MB") * 100 / $ufo->convert_to_byte((int)ini_get("memory_limit"), "MB"));
        return $percentage == 0 ? 1 : $percentage;
    }

    /**
     * @param string $info
     */
    public function set_mem_usage (string $info = "") {
        $this->statistics[] = [
            "info" => $info,
            "memory_usage" => $this->current_mem_usage()
        ];
    }

    /**
     * @return array
     */
    public function get_memory_info (): array {
        $ARRAY = [];

        foreach ($this->statistics as $statistic) {
            $ARRAY["memory_usage"] = $statistic["memory_usage"];
            $ARRAY["memory_limit"] = ini_get("memory_limit");
            $ARRAY["percent"] = $this->mem_usage_to_percentage($ARRAY["memory_usage"]);
            $ARRAY["Info"] = $statistic["info"];
        }

        $ARRAY["memory_peak"] = $this->peak_mem_usage();

        return $ARRAY;
    }

    /**
     * @param string $info
     */
    public function set_start_memory (string $info = "Initial Memory Usage") {
        $this->set_mem_usage($info);
    }

    /**
     * @param string $info
     */
    public function set_end_memory (string $info = "Memory Usage at the End") {
        $this->set_mem_usage($info);
    }

}