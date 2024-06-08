<?php

/**
 * Copyright (c) 2022-2024 UFOCMS
 *
 * This software is licensed under the GPLv3 license.
 * See the LICENSE file for more information.
 */

final class UFO_Text extends UFO_Editor_Widget {

    public function set_title ( ) {
        global $ufo;
        return $ufo->lng("Text");
    }

    public function set_icon ( ): string {
        return "ufo-icon-text";
    }

    public function set_type ( ): int {
        return UFO_Editor_Widget::Editable;
    }

}