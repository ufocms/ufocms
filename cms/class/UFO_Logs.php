<?php

/**
 * Copyright (c) 2022-2024 UFOCMS
 *
 * This software is licensed under the GPLv3 license.
 * See the LICENSE file for more information.
 */

final class UFO_Logs {

    private array $save_errors = [];

    public function init_handler ( ) {
        set_exception_handler([$this, "exception_handler"]);
        set_error_handler([$this, "error_handler"], E_ALL);
    }

    /**
     * @param $no
     * @param $message
     * @param $file
     * @param $line
     * @return void
     * @throws Exception
     */
    public function error_handler ( $no, $message, $file, $line ): void {
        global $db;

        $to_array = [$no, $message, file_get_contents($file), $file, $line];

        $this->save_errors[] = $to_array;

        if ( $db->meta("debug") == "true" ) {
            $this->render_error(true, true);
        } else {
            $this->render_error();
        }
    }

    /**
     * @param $exception
     * @param bool $show_errors
     * @return void
     * @throws Exception
     */
    public function exception_handler ( $exception, bool $show_errors = false ): void {
        global $ufo, $db;

        $to_array = [
            $exception->getCode(),
            $exception->getMessage(),
            file_get_contents($exception->getFile()),
            $exception->getFile(),
            $exception->getLine()
        ];
        $this->save_errors[] = $to_array;

        if ( $show_errors || $ufo->success($db->debug) ) {
            $this->render_error(false, true);
        } else {
            $this->render_error();
        }
    }

    /**
     * @param bool $error
     * @param bool $codes
     * @return void
     * @throws Exception
     */
    private function render_error (bool $error = true, bool $codes = false): void {
        global $ufo;

        ob_start();
        echo '<div class="p-10px" dir="' . $ufo->dir() . '">';
        foreach ($this->save_errors as $items) {
            $line = array_pop($items);
            $file = $items[3];

            if (!$codes) {
                echo '<d>';
                $this->error_notice($ufo->lng("System error") . '<a href="https://ufocms.org/docs/system-error" target="_blank" class="' . $ufo->reverse_float() . '">' . $ufo->lng("See help page") . '</a>');
            }

            $this->plugin_error();

            if ($codes) {
                $this->error_notice('<strong>' . ($error ? $ufo->lng("Error") : $ufo->lng("Exception")) . '</strong><span class="' . $ufo->reverse_float() . '">' . $items[1] . ' - Line ' . $line . '</span>');
                $this->error_notice('<strong>' . $ufo->lng("file") . '</strong><span class="' . $ufo->reverse_float() . '">' . $file . '</span>');

                echo $ufo->tag("div",
                    $ufo->tag("ol", $this->render_lines($items[2]), [
                        "dir" => "ltr",
                        "class" => "ufo-code-container"
                    ])
                );
            }

            $ufo->localize_script("ufo_code_error", [
                "line" => $line,
                "title" => $ufo->this_title()
            ]);
        }
        echo '</div>';
        $content = ob_get_flush();
        ob_clean();

        echo
            '<html dir="' . $ufo->dir() . '">
                <head>
                    <meta charset="UTF-8">
                    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
                    <meta http-equiv="X-UA-Compatible" content="ie=edge">
                    <title>' . $ufo->lng("System error") . '</title> ' . $this->css() . '
                </head>
                <body data-theme="' . THEME . '">' . $content . $this->script() . '</body>
            </html>';

        $ufo->die();
    }

    /**
     * @param $data
     * @return string
     */
    private function render_lines ( $data ): string {
        $explode = explode("\n", $data);
        $join    = "";

        foreach ($explode as $item)
            $join .= "<li><pre>" . htmlentities($item) . "</pre></li>";

        return $join;
    }

    /**
     * @param $msg
     * @return void
     */
    private function error_notice ( $msg ): void {
        echo '<div class="ufo-error danger">' . $msg . '</div>';
    }

    /**
     * @return void
     * @throws Exception
     */
    private function plugin_error ( ): void {
        global $ufo, $_;

        if (isset($_["this_plugin"])) {
            $link = '';

            if (isset($_["this_plugin"]["manifest"]["document"]))
                $link = "<br><br><a target='_blank' href='{$_["this_plugin"]["manifest"]["document"]}' class='{$ufo->reverse_float()}'>{$ufo->lng("Plugin Help Page")}</a><br>";

            $this->error_notice(
                '<strong>' . $ufo->lng("Plugin Error") . '</strong><span class="' . $ufo->reverse_float() . '">' .
                $_["this_plugin"]["manifest"]["name"] . $link . '</span>'
            );
        }
    }

    /**
     * @return string
     */
    private function css ( ): string {
        return
            "<link data-name='ufo-theme' rel='stylesheet' href='" . (URL_WEBSITE . "content/assets/css/theme/" . THEME . ".css") . "'>" .
            "<link data-name='ufo-all' rel='stylesheet' href='" . (URL_WEBSITE . "content/assets/font/all.css") . "'>" .
            "<link data-name='ufo' rel='stylesheet' href='" . (URL_WEBSITE . "content/assets/css/ufo.css") . "'>" .
            "<link data-name='ufo-ui' rel='stylesheet' href='" . (URL_WEBSITE . "content/assets/css/ui.css") . "'>";
    }

    /**
     * @return string
     */
    private function script ( ): string {
        return (
            !defined("ADMIN") ? "<script data-name='ufo-jquery' src='".(URL_WEBSITE . "content/assets/script/jquery.min.js")."'></script>" : ""
        ) . "<script data-name='ufo-logs' src='".(URL_WEBSITE . "content/assets/script/logs.js")."'></script>";
    }

}