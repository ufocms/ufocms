<?php

/**
 * Copyright (c) 2022-2024 UFOCMS
 *
 * This software is licensed under the GPLv3 license.
 * See the LICENSE file for more information.
 */

final class UFO_Img extends UFO_Editor_Widget {

    public function set_title ( ): string {
        global $ufo;
        return $ufo->lng("Image");
    }

    public function set_icon ( ): string {
        return "ufo-icon-image";
    }

    public function set_template ( ): array {
        global $db;
        return [
            "tag"   => "img",
            "attrs" => [
                "src"   => $db->meta("unknown_photo"),
                "style" => "width: 150px;height: 150px"
            ]
        ];
    }

    public function set_control ( ): array {
        global $ufo;
        return [
            "tag"  => "div",
            "html" => [
                [
                    "tag"   => "span",
                    "html"  => [$ufo->lng("Select image")],
                    "attrs" => [
                        "class" => "mb-15 db font-size-17px"
                    ]
                ],
                [
                    "tag"   => "div",
                    "html"  => [
                        [
                            "tag"   => "button",
                            "html"  => [
                                [
                                    "tag"   => "i",
                                    "attrs" => [
                                        "class" => "ufo-icon-image font-size-18px"
                                    ]
                                ]
                            ],
                            "attrs" => [
                                "data-type" => "img",
                                "class"     => "active"
                            ]
                        ],
                        [
                            "tag"   => "button",
                            "html"  => [
                                [
                                    "tag"   => "i",
                                    "attrs" => [
                                        "class" => "ufo-icon-link font-size-18px"
                                    ]
                                ]
                            ],
                            "attrs" => [
                                "data-type" => "link"
                            ]
                        ]
                    ],
                    "attrs" => [
                        "class" => "ufo-switch-type-wrp ufo-switch-type-src-img"
                    ]
                ],
                [
                    "tag"   => "div",
                    "html"  => [
                        [
                            "tag"   => "span",
                            "html"  => [$ufo->lng("Image link")],
                            "attrs" => [
                                "class" => "db mb-10"
                            ]
                        ],
                        [
                            "tag"   => "input",
                            "attrs" => [
                                "class" => "form-control"
                            ]
                        ]
                    ],
                    "attrs" => [
                        "class" => "ufo-img-src-link width-100-cent mb-15 dn"
                    ]
                ],
                [
                    "tag"   => "div",
                    "html"  => [
                        [
                            "tag"  => "div",
                            "html" => [
                                [
                                    "tag"  => "div",
                                    "html" => [
                                        "tag"   => "i",
                                        "attrs" => [
                                            "class" => "ufo-icon-circle-notch rotating"
                                        ]
                                    ],
                                    "attrs" => [
                                        "class" => "loader dn"
                                    ]
                                ],
                                [
                                    "tag"   => "img"
                                ]
                            ],
                            "attrs" => [
                                "class" => "img-container"
                            ]
                        ]
                    ],
                    "attrs" => [
                        "class" => "ufo-select-image"
                    ]
                ],
                [
                    "tag"   => "span",
                    "html"  => [$ufo->lng("Image size")],
                    "attrs" => [
                        "class" => "mb-15 db font-size-17px"
                    ]
                ],
                [
                    "tag"   => "div",
                    "html"  => [
                        [
                            "tag"   => "button",
                            "html"  => ["PX"],
                            "attrs" => [
                                "data-type" => "px",
                                "class"     => "active"
                            ]
                        ],
                        [
                            "tag"   => "button",
                            "html"  => ["REM"],
                            "attrs" => [
                                "data-type" => "rem"
                            ]
                        ],
                        [
                            "tag"   => "button",
                            "html"  => ["%"],
                            "attrs" => [
                                "data-type" => "%"
                            ]
                        ]
                    ],
                    "attrs" => [
                        "class" => "ufo-switch-type-wrp ufo-switch-size-img"
                    ]
                ],
                [
                    "tag"   => "div",
                    "html"  => [
                        [
                            "tag"   => "input",
                            "attrs" => [
                                "value" => 0,
                                "class" => "value"
                            ]
                        ],
                        [
                            "tag"   => "input",
                            "attrs" => [
                                "type"  => "range",
                                "value" => 0,
                                "min"   => 20,
                                "max"   => 500,
                                "class" => "ufo-range-slider"
                            ]
                        ],
                        [
                            "tag"  => "span",
                            "html" => $ufo->lng("Width")
                        ]
                    ],
                    "attrs" => [
                        "class" => "ufo-range-container ufo-img-width-resizer"
                    ]
                ],
                [
                    "tag"   => "div",
                    "html"  => [
                        [
                            "tag"   => "input",
                            "attrs" => [
                                "value" => 0,
                                "class" => "value"
                            ]
                        ],
                        [
                            "tag"   => "input",
                            "attrs" => [
                                "type"  => "range",
                                "value" => 0,
                                "min"   => 20,
                                "max"   => 500,
                                "class" => "ufo-range-slider"
                            ]
                        ],
                        [
                            "tag"  => "span",
                            "html" => $ufo->lng("Height")
                        ]
                    ],
                    "attrs" => [
                        "class" => "ufo-range-container ufo-img-height-resizer mt-10"
                    ]
                ]
            ]
        ];
    }

    public function set_name_script ( ): string {
        return "ufo_img_widget";
    }

}