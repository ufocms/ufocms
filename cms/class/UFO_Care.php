<?php

/**
 * Copyright (c) 2022-2024 UFOCMS
 *
 * This software is licensed under the GPLv3 license.
 * See the LICENSE file for more information.
 */

final class UFO_Care {

    /**
     * @throws Exception
     */
    public function init () {
        $this->sanitize_GET();
        $this->php_version_handler();
        $this->extensions_handler();
    }

    /**
     * @return array[]
     */
    protected function php_extensions (): array {
        global $ufo;
        $args = [
            "classes" => [
                "ZipArchive" => true
            ],
            "functions" => [
                "mysqli_connect" => true,
                "preg_match" => true
            ],
            "extensions" => [
                "fileinfo" => true,
                "hash" => true,
                "curl" => true,
                "json" => true,
                "mysqli" => true,
                "session" => true,
                "date" => true,
                "zip" => true
            ]
        ];
        $result = [];
        $list_error = [];
        foreach ($args as $k => $v) {
            foreach ($v as $ik => $iv) {
                switch ($k) {
                    case "classes":
                        $result[$ik] = class_exists($ik);
                        break;
                    case "functions":
                        $result[$ik] = function_exists($ik);
                        break;
                    case "extensions":
                        $result[$ik] = extension_loaded($ik);
                        break;
                }
                if (!$result[$ik] && $iv) $list_error[$ik] = "error";
                $result[$ik] = $result[$ik] ? $ufo->lng("supported") : $ufo->lng("not support");
            }
        }
        return [$result, "error_list" => $list_error];
    }

    /**
     * @return bool|int
     */
    protected function check_php_version () {
        return version_compare(phpversion(), '7.4', '>=');
    }

    /**
     * @return void
     * @throws Exception
     */
    protected function extensions_handler () {
        global $ufo, $_;

        $extensions = $this->php_extensions();

        if (!empty($extensions["error_list"])) {

            $_["dir"] = "ltr";

            $ufo->error("Your host does not support multiple php extensions", "", true, true);

            ?>

            <div style="display:flex;justify-content: center;align-items: center;width: 100%;margin: 80px 0 0">
                <ul style="list-style: none;padding: 0;margin: 0;">
                    <?php foreach ($extensions[0] ?? [] as $k => $v) { ?>
                        <li style="font-size: 20px;margin: 0 0 10px;width: 100%;display: flex;">
                            <div style="width: 200px;border-right: 3px solid gray"><?= $k ?></div>
                            <div style="width: 200px;margin-left:10px;padding: 5px 10px;text-align: center;<?= $v == "not support" ? "background:red;color:white" : "" ?>"><?= $v ?></div>
                        </li>
                    <?php } ?>
                </ul>
            </div>

            <?php $ufo->die();
        }
    }

    /**
     * @return void
     * @throws Exception
     */
    protected function php_version_handler () {
        global $ufo, $_;

        if (!$this->check_php_version()) {
            $_["dir"] = "ltr";

            $ufo->error("PHP version must be above 7.4", "", true, true);

            $ufo->die();
        }
    }

    /**
     * @return void
     */
    protected function sanitize_GET () {
        global $ufo;

        foreach ($_GET as $k => $v) {
            if (is_string($v)) {
                $_GET[$k] = $ufo->sanitize_xss($v);
            }
        }
    }

}