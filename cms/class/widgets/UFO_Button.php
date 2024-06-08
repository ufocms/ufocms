<?php

/**
 * Copyright (c) 2022-2024 UFOCMS
 *
 * This software is licensed under the GPLv3 license.
 * See the LICENSE file for more information.
 */

final class UFO_Button extends UFO_Editor_Widget {

    public function set_title ( ): string {
        global $ufo;
        return $ufo->lng("Button");
    }

    public function set_icon ( ): string {
        return "ufo-icon-vote";
    }

    // public function set_document ( ): string {
    //     return "https://ufocms.org";
    // }

    public function set_control ( ): array {
        global $ufo;
        return [
            "tag" => "div",
            "html" => [
                [
                    "tag"   => "label",
                    "html"  => [
                        [
                            "tag"   => "span",
                            "html"  => $ufo->lng("text"),
                            "attrs" => [
                                "class" => "db width-100-cent mr-5 ml-5 mb-5"
                            ]
                        ],
                        [
                            "tag"   => "input",
                            "attrs" => [
                                "placeholder" => $ufo->lng("text"),
                                "class" => "form-control mt-15 UFO-Button-input-text-button"
                            ]
                        ]
                    ],
                    "attrs" => ["class" => "mt-15 db"]
                ],
                [
                    "tag"   => "label",
                    "html"  => [
                        [
                            "tag"   => "span",
                            "html"  => $ufo->lng("style"),
                            "attrs" => [
                                "class" => "db width-100-cent mr-5 ml-5 mb-5"
                            ]
                        ],
                        [
                            "tag"   => "select",
                            "html"  => $this->button_styles(),
                            "attrs" => [
                                "class" => "form-control UFO-Button-select-style-button"
                            ]
                        ]
                    ],
                    "attrs" => ["class" => "mt-15 db"]
                ]
            ]
        ];
    }

    public function set_template ( ): array {
        global $ufo;
        return [
            "tag"  => "button",
            "html" => $ufo->lng("Click"),
            "attrs" => [
                "class" => "btn btn-primary"
            ]
        ];
    }

    public function set_name_script ( ): string {
        return "ufo_widget_button";
    }

    public function button_styles ( ): array {
        global $ufo;

        $styles  = ["primary", "info", "danger", "success", "light", "dark", "secondary", "warning"];
        $options = [];

        foreach ($styles as $style) {
            $options[] = [
                "tag"   => "option",
                "html"  => $ufo->lng($style),
                "attrs" => [
                    "value" => $style
                ]
            ];
        }

        return $options;
    }

}