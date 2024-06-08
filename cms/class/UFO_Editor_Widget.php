<?php

/**
 * Copyright (c) 2022-2024 UFOCMS
 *
 * This software is licensed under the GPLv3 license.
 * See the LICENSE file for more information.
 */

class UFO_Editor_Widget {

    const Editable = 0x1;

    /**
     * @param $callback
     * @return bool
     */
    private function callable ($callback): bool {
        global $ufo;
        return $ufo->is_callable($this, $callback);
    }

    /**
     * @return string|null
     */
    private function get_title ( ): ?string {
        if ($this->callable("set_title")) {
            return $this->set_title();
        } else {
            return null;
        }
    }

    /**
     * @return string|null
     */
    private function get_icon ( ): ?string {
        if ($this->callable("set_icon")) {
            return $this->set_icon();
        } else {
            return null;
        }
    }

    /**
     * @return int|null
     */
    private function get_type ( ): ?int {
        if ($this->callable("set_type")) {
            return $this->set_type();
        } else {
            return null;
        }
    }

    /**
     * @return string|null
     */
    private function get_document ( ): ?string {
        if ($this->callable("set_document")) {
            return $this->set_document();
        } else {
            return null;
        }
    }

    /**
     * @return array
     */
    private function get_controls ( ): array {
        if ($this->callable("set_control")) {
            return $this->set_control();
        } else {
            return [];
        }
    }

    /**
     * @return array
     */
    private function get_template ( ): array {
        if ($this->callable("set_template")) {
            return $this->set_template();
        } else {
            return [];
        }
    }

    /**
     * @return string|null
     */
    private function get_name_script ( ): ?string {
        if ($this->callable("set_name_script")) {
            return $this->set_name_script();
        } else {
            return null;
        }
    }

    /**
     * @return null
     */
    private function get_style ( ) {
        if ($this->callable("set_style")) {
            return $this->set_style();
        } else {
            return null;
        }
    }

    /**
     * @return null
     */
    private function get_script ( ) {
        if ($this->callable("set_script")) {
            return $this->set_script();
        } else {
            return null;
        }
    }

    /**
     * @return array
     */
    private function get_widget ( ): array {
        return [
            "name"  => get_class($this),
            "title" => $this->get_title(),
            "icon"  => $this->get_icon(),
            "type"  => $this->get_type(),
            "document" => $this->get_document(),
            "controls" => $this->get_controls(),
            "template" => $this->get_template(),
            "style"  => $this->get_style(),
            "script" => [
                "name" => $this->get_name_script(),
                "src" => $this->get_script()
            ]
        ];
    }

    public function __construct ( ) {
        global $ufo;
        if ( $ufo->match_page($ufo->admin_path() . "ufo-editor") || $ufo->match_page("float/ufo_all_editor_widgets") ) {
            $ufo->add_array("ufo_editor_widgets", $this->get_widget());
        }
    }

}