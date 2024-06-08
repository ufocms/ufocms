<?php

/**
 * Copyright (c) 2022-2024 UFOCMS
 *
 * This software is licensed under the GPLv3 license.
 * See the LICENSE file for more information.
 */

final class UFO_Media {

    private string $folder;

    public function __construct ( $ajax = false, $autorun = true) {
        global $ufo;

        $this->folder = (defined("FRONT") ? "" : $ufo->back_folder()) . $ufo->slash_folder(FILES);
        if ($ajax) {
            if (isset($_POST["address"]) && is_string($_POST["address"])) {
                $this->folder = $ufo->slash_folder($_POST["address"]) . SLASH;
            }
        }

        $this->setup_work();

        if ($autorun && !$ufo->get_kv("ufo_media_setup"))
            $this->setup_toolbar();

        $ufo->add_kv("ufo_media_setup", true);
    }

    /**
     * @return void
     */
    private function setup_work ( ) {
        global $ufo;

        $this->delete();
        $this->info_file();

        $ufo->add_work("ufo_file_types", function () {
            return $this->limit_type_files();
        });
        $ufo->add_work("ufo_fm_template", function () {
            return $this->html_stats() . $this->html_toolbar() . $this->html_folders() . $this->html_files();
        });
        $ufo->add_work("ufo_dl_file", function ($file) {
            $this->download($file);
        });
        $ufo->add_work("ufo_change_data_file", function ($arg) {
            return $this->save_changed($arg["file"], $arg["name"], $arg["content"] ?? "NaN");
        });
        $ufo->add_work("ufo_count_file_of_type", function ($type) {
            return $this->count_file_of_type($type);
        });
        $ufo->add_work("ufo_new_file", function ($arg) {
            return $this->new_file($arg["dir"], $arg["file"], $arg["type"]);
        });
        $ufo->add_work("ufo_new_folder", function ($arg) {
            return $this->new_folder($arg["name"], $arg["dir"] ?? null);
        });
        $ufo->add_work("ufo_uploader", function ($arg) {
            return $this->upload($arg);
        });
    }

    /**
     * @return void
     * @throws Exception
     */
    private function setup_toolbar ( ) {
        global $ufo;

        $size  = $ufo->folder_size($this->folder);
        $size  = $ufo->convert_size($size);

        $ufo->add_array("stats-fm", [
            "icon"   => "ufo-icon-pie-chart",
            "title"  => $ufo->lng("size"),
            "number" => "$size[size] $size[unit]"
        ]);
        $ufo->add_array("stats-fm", [
            "icon"   => "ufo-icon-music",
            "title"  => $ufo->lng("music"),
            "number" => $ufo->do_work("ufo_count_file_of_type", "audio")
        ]);
        $ufo->add_array("stats-fm", [
            "icon"   => "ufo-icon-image",
            "title"  => $ufo->lng("picture"),
            "number" => $ufo->do_work("ufo_count_file_of_type", "img")
        ]);
        $ufo->add_array("stats-fm", [
            "icon"   => "ufo-icon-folders",
            "title"  => $ufo->lng("total"),
            "number" => $this->multi_count_files()
        ]);

        $ufo->add_array("fm-toolbar-1", [
            "icon"  => "ufo-icon-upload-cloud font-size-20px",
            "title" => $ufo->lng("upload"),
            "action" => "upload"
        ]);
        $ufo->add_array("fm-toolbar-1", [
            "icon"  => "ufo-icon-folder font-size-20px",
            "title" => $ufo->lng("new folder"),
            "action" => "create_folder"
        ]);
        $ufo->add_array("fm-toolbar-1", [
            "icon"  => "ufo-icon-file font-size-20px",
            "title" => $ufo->lng("new file"),
            "action" => "create_file"
        ]);

        $ufo->add_array("fm-toolbar-2", [
            "icon"   => "ufo-icon-chevron-" . ($ufo->dir() == "ltr" ? "right" : "left") . " mt-5",
            "style"  => "btn btn-primary font-size-17px flex flex-center",
            "attr"   => ["disabled"=>true,"title"=>"back"],
            "action" => "back"
        ]);
        $ufo->add_array("fm-toolbar-2", [
            "icon"   => "ufo-icon-trash mt-5",
            "style"  => "btn btn-danger font-size-18px flex flex-center",
            "attr"   => ["disabled"=>true,"title"=>"delete"],
            "action" => "delete"
        ]);
    }

    /**
     * @param $type
     * @return int
     */
    public function count_file_of_type ( $type ): int {
        global $ufo; $count = 0;
        foreach ($ufo->minifyArray($ufo->get_file_list($this->folder), "file") as $item) {
            if ( is_file($item) && file_exists($item) ) {
                if ( isset($this->types()[$type]) ) {
                    $types = $this->types()[$type];
                    foreach ($types as $k => $v) {
                        if ( isset(pathinfo($item)["extension"]) ) {
                            if ( pathinfo($item)["extension"] == $k ) {
                                $count++;
                            }
                        }
                    }
                }
            }
        }
        return $count;
    }

    /**
     * @param array $types
     * @return int
     */
    public function multi_count_files (array $types = []): int {
        global $ufo;

        $sum  = 0;
        $docs = function ( $types = [] ) {
            $new_list = [];
            foreach ($types as $k => $v)
                $new_list[] = $k;
            return $new_list;
        };

        foreach ($docs((empty($types) ? $this->types() : $types)) as $item)
            $sum += $ufo->do_work("ufo_count_file_of_type", $item);

        return $sum;
    }

    /**
     * @param $address
     * @return bool
     */
    public function set_folder ( $address ): bool {
        $this->folder = $address;
        return true;
    }

    /**
     * @return mixed
     */
    public function types ( ) {
        global $ufo;
        return $ufo->get_package()["types"];
    }

    /**
     * @return array
     */
    public function limit_type_files ( ): array {
        $types    = $this->types();
        $new_list = [];
        foreach ($types as $v)
            foreach ($v as $k => $item)
                $new_list[$k] = $item;
        return $new_list;
    }

    /**
     * @param string $string
     * @return array|string|string[]
     */
    private function fix_address ( string $string ) {
        global $ufo;
        return $ufo->sanitize_link($string);
    }

    /**
     * @return string
     */
    public function html_stats ( ): string {
        global $ufo;
        return $ufo->tag("section",
            function () {
                global $ufo;$join = "";
                foreach ($ufo->get_array("stats-fm") as $item)
                    $join .=
                        $ufo->tag("div",
                            $ufo->tag("div",
                                $ufo->tag("div",
                                    $ufo->tag("div", $ufo->tag("i", null, ["class" => $item["icon"]]), ["class" => "icon"]) .
                                    $ufo->tag("span", $item["title"])
                                    , ["class" => "flex flex-start align-center"]) .
                                $ufo->tag("div", $item["number"], ["class" => "flex flex-end align-center"]), ["class" => "stats-fm-card"]
                            ), ["class" => "stats-fm-row"]);
                return $join;
            }, ["class" => "stats-fm"]
        );
    }

    /**
     * @return string
     * @throws Exception
     */
    public function html_toolbar ( ): string {
        global $ufo;
        return $ufo->tag("div",
            $ufo->tag("div",
                $ufo->tag("div",
                    $ufo->tag("ul",
                        function ( ) {
                            global $ufo; $join = "";
                            foreach ($ufo->get_array("fm-toolbar-1") as $item) {
                                $join .=
                                    $ufo->tag("li",
                                        $ufo->tag("i", null, ["class"=>$item["icon"]]) .
                                        $ufo->tag("span", $item["title"]), ($item["attr"] ?? []) + [
                                            "data-action" => $item["action"] ?? "",
                                            "class" => "fm-btn-action"
                                        ]
                                    );
                            }
                            return $join;
                        }
                    ), ["class" => "fm-toolbar"]
                ) .
                $ufo->tag("div",
                    function ( ) {
                        global $ufo; $join = "";
                        foreach ($ufo->get_array("fm-toolbar-2") as $item) {
                            $join .=
                                $ufo->btn($ufo->tag("i", null, ["class"=>$item["icon"]]), "fm-btn-action", $item["style"], (isset($item["attr"]) ? $item["attr"] : []) + [
                                    "data-action" => $item["action"] ?? "",
                                ]);
                        }
                        return $join;
                    }, ["class"=>"flex p-5px fm-toolbar-two", "dir"=>($ufo->dir() == "ltr" ? "rtl" : "ltr")])
                , ["class" => "container"]
            ), ["class"=>"fm-toolbar-wrp"]
        );
    }

    /**
     * @return string
     */
    public function html_folders ( ): string {
        global $ufo;
        $append = "";
        foreach ($ufo->all_folders($this->folder) as $item) {
            $folder_name = pathinfo($item)["filename"];
            $append .=
                $ufo->tag("div", $ufo->tag("i", null, ["class"=>"solid-icon ufo-icon-folder cl-yellow"]) .
                    $ufo->tag("span",$folder_name),
                    ["class"=>"fm-folder-container","data-type"=>"folder","data-address"=>$item,"data-name"=>$folder_name]);
        }
        return $ufo->tag("div",$append,["class"=>"fm-row-folders"]);
    }

    /**
     * @return string
     */
    public function html_files ( ): string {
        global $ufo;

        $join = "";
        foreach ($ufo->get_file_subfolder($this->folder) as $k => $v) {
            $type = pathinfo($v)["extension"];
            $name = $k;
            $get_icon = $ufo->file_type_icon($type);
            if ( $get_icon ) {
                $src = function ($icon, $title, $src) {
                    global $ufo;
                    if ( $icon["img"] == "this" ) {
                        return $ufo->tag("img", null, ["src"=>$src, "loading"=>"lazy"]);
                    } else if ( !empty($icon["icon"]) ) {
                        return
                            $ufo->tag("div",
                                $ufo->tag("i", null, ["class"=>$icon["icon"]]) .
                                $ufo->tag("div",
                                    $ufo->tag("span", $title)
                                )
                                ,["class"=>"fm-icon-file"]);
                    }
                    return [];
                };
                $join .=
                    $ufo->tag("div",
                        $ufo->tag("div",
                            $ufo->tag("span", "", ["class"=>"error"]).
                            $src($get_icon, $name, $v)
                            ,["class"=>"file-card","data-type"=>"file","data-address"=>$v,"data-name"=>$name]
                        ), ["class"=>"file-card-container"]
                    );
            }
        }

        return $ufo->tag("section", empty($join) ? '<h4 class="fm-empty-files text-center width-100-cent">'.$ufo->lng("This folder is empty").'</h4>' : $join, ["class"=>"file-manager"]);
    }

    /**
     * @param $dir
     * @param $file
     * @param $type
     * @return array
     */
    public function new_file ( $dir, $file, $type = "" ): array {
        global $ufo;

        $accept_type = $this->types()["text"];
        $status      = false;

        foreach ($accept_type as $k => $v) {
            if ( $k == $type ) {
                $status = true;
            }
        }

        $file = $ufo->sanitize_file_name($file);
        $file = $this->fix_address($file);

        if ( !empty($file) ) {
            if ( $status && is_dir($ufo->slash_folder($dir) . SLASH) ) {
                if ( file_exists($ufo->slash_folder($dir) . SLASH . $file . "." . $type) ) {
                    $status = [
                        "status"  => 0,
                        "message" => $ufo->lng("There is another file with this name Choose another name")
                    ];
                } else {
                    $address = $ufo->slash_folder($dir) . $file . "." . $type;
                    $create  = file_put_contents($address, "");
                    if ( file_exists($address) ) {
                        $status = [
                            "status"  => 200,
                            "message" => $ufo->lng("Done successfully")
                        ];
                    } else {
                        $status = [
                            "status"  => 503,
                            "message" => $ufo->lng("System error")
                        ];
                    }
                }
            } else {
                $status = [
                    "status"  => 403,
                    "message" => $ufo->lng("File format is not allowed")
                ];
            }
        } else {
            $status = [
                "status"  => -1,
                "message" => $ufo->lng("Please enter a file name")
            ];
        }

        return $status;
    }

    /**
     * @return void
     */
    public function info_file ( ) {
        global $ufo;
        $ufo->add_work("ufo_fm_detail_file", function (string $address) {
            global $ufo; ob_start();
            $info = $ufo->info_file($address);
            $ufo->load_layout($ufo->slash_folder("pages/snippets/info-file"), true, ".php", [] +
                $info + [
                    "content" => $this->show_file($address, $info["type"])
                ]
            );
            $content = ob_get_flush();
            ob_clean();
            return $content;
        });
    }

    /**
     * @param $address
     * @param $type
     * @return string
     */
    public function show_file ( $address, $type ): string {
        global $ufo;

        $type_pack = function ( $type ) {
            $result = "";
            foreach ($this->types() as $key => $v) {
                foreach ($v as $k => $item) {
                    if ( $k == $type ) {
                        $result = $key;
                    }
                }
            }
            return $result;
        };
        $type_pack = $type_pack($type);

        switch ($type_pack) {
            case "img":
                $template = $ufo->tag("img",null, [
                    "class" => "fm-demo-img p-10px",
                    "src"   => $address
                ]);
                break;
            case "text":
                $template = $ufo->tag("textarea", (is_file($address) && file_exists($address) ? file_get_contents($address) : ""), [
                    "id" => "fm-content-editor"
                ]);
                break;
            case "audio":
                $template = $ufo->tag("audio",
                    $ufo->tag("source", null, [
                        "src"  => $address,
                        "type" => "audio/" . $type
                    ]), [
                        "controls"     => "",
                        "controlsList" => "nodownload"
                    ]);
                break;
            case "video":
                $template = $ufo->tag("video",
                    $ufo->tag("source", null, [
                        "src"  => $address,
                        "type" => "video/" . $type
                    ]), [
                        "controls"      => "",
                        "controlsList"  => "nodownload",
                        "playsinline"   => ""
                    ]
                );
                break;
            default:
                $template = $ufo->tag("h3", $ufo->lng("Preview of this file type is not visible"));
        }

        return $template;
    }

    /**
     * @param string $file
     * @param string $name
     * @param string $content
     * @return false|string
     */
    public function save_changed ( string $file, string $name, $content = "%NULL%" ) {
        global $ufo;

        if ( file_exists($file) || is_dir($file) ) {
            if ( $content != "%NULL%" && is_file($file) )
                file_put_contents($file, $content);

            $has  = false;
            $info = pathinfo($file);
            $name = $ufo->sanitize_file_name($name);
            $new_address = str_replace($info["basename"], $name . (isset($info["extension"]) ? "." . $info["extension"] : ""), $file);

            if ( $content == "%NULL%" && is_file($file) ) {
                foreach ($ufo->get_file_subfolder($ufo->slash_folder($info["dirname"] . "/")) as $item) {
                    if ( is_file($item) && file_exists($item) ) {
                        $pathInfo = pathinfo($item);
                        if ( isset($pathInfo["extension"]) && isset($info["extension"]) ) {
                            if ( $pathInfo["filename"] . $pathInfo["extension"] == $name . $info["extension"] ) {
                                $has = true;
                            }
                        }
                    }
                }
            }

            if ( !$has || (!is_dir($new_address) && !file_exists($new_address)) ) {
                if ( rename($file, $new_address) ) {
                    return $ufo->status(200, [
                        "new_address" => $new_address,
                        "new_name"    => $name,
                        "new_link"    => URL_WEBSITE . $this->fix_address(str_replace(["../", "..\\"], "", $new_address))
                    ]);
                } else {
                    return $ufo->status(503, $ufo->lng("System error"));
                }
            } else {
                return $ufo->status(0, $ufo->lng("There is another file or folder with this name Choose another name"));
            }
        } else {
            return $ufo->status(404, $ufo->lng("The desired folder or file does not exist"));
        }
    }

    /**
     * @param $name
     * @param $dir
     * @return array
     */
    public function new_folder ( $name, $dir = null ): array {
        global $ufo;

        $name   = $ufo->sanitize_file_name($name);
        $dir    = (empty($dir) ? $this->folder : $dir);
        $join   = $ufo->slash_folder($dir . $name);

        $status = [
            "status"  => 503,
            "message" => $ufo->lng("System error")
        ];

        if ( is_dir($join) ) {
            $status = [
                "status"  => 0,
                "message" => $ufo->lng("exists folder")
            ];
        } else {
            if ( !empty($name) ) {
                if ( mkdir($join) ) {
                    $status = [
                        "status"  => 200,
                        "message" => $ufo->lng("Done successfully")
                    ];
                }
            } else {
                $status = [
                    "status"  => -1,
                    "message" => $ufo->lng("Please enter a file name")
                ];
            }
        }

        return $status;
    }

    /**
     * @return void
     */
    public function delete ( ) {
        global $ufo;
        $ufo->add_work("ufo_fm_delete", function ( array $array ) {
            global $ufo; extract($array);

            if ( !is_array($address) ) {
                $result = false;
                switch ($_POST['type']) {
                    case "file":
                        $result = $ufo->delete_file($address);
                        break;
                    case "folder":
                        $result = $ufo->delete_folder($address);
                        break;
                }
                return json_encode([
                    "status" => $result ? 200 : 503
                ]);
            } else {
                $folders = $address["folder"] ?? [];
                $files   = $address["file"] ?? [];
                $resultAll = [];

                if ( !empty($folders) ) {
                    foreach ($folders as $item) {
                        $resultAll[$item] = $ufo->delete_folder($item);
                    }
                }
                if ( !empty($files) ) {
                    foreach ($files as $item) {
                        $resultAll[$item] = $ufo->delete_file($item);
                    }
                }

                return json_encode($resultAll, JSON_UNESCAPED_UNICODE);
            }
        });
    }

    /**
     * @param $file
     * @return void
     * @throws Exception
     */
    public function download ( $file ) {
        global $ufo;
        if( !file_exists($file) ){
            $ufo->error($ufo->lng("File not exists"));
            $ufo->die();
        } else {
            if ( extension_loaded("fileinfo") ) {
                header("Cache-Control: public");
                header("Content-Description: File Transfer");
                header("Content-Disposition: attachment; filename=" . pathinfo($file)["basename"]);
                header("Content-Type: " . mime_content_type($file));
                header("Content-Transfer-Encoding: binary");
                header("Content-length: " . filesize($file));
                readfile($file);
            } else {
                $ufo->die("PHP Extension: FileInfo not support", 503, true);
            }
        }
    }

    /**
     * @param $arg
     * @return int|string|array
     */
    public function upload ( $arg ) {
        global $ufo;

        extract($arg);

        /** Check options */
        if ( !isset($file) || !isset($folder) || !ini_get("file_uploads") )
            return 0;

        /** File Data < Name, Type, Size > */
        $Name = $file["name"];
        $Type = explode(".", $Name);
        $Type = array_pop($Type);
        $Size = $file["size"] / 1024 / 1024;

        /**
         * $Size = ( default ) $ufo->upload_max_filesize
         * $Directory = ( default ) $this->folder
         */
        $LimitSize = $size ?? (int) $ufo->get_upload_max_size();
        $Directory = $folder ?? $this->folder;
        $Directory = $ufo->slash_folder($Directory . "/");

        if (!is_dir($Directory))
            mkdir($Directory);

        /**
         * Access
         * Message
         * Status
         * < Variable For Return Result >
         */
        $Access   = false;
        $Continue = true;
        $Message  = "";
        $Status   = 0;

        /**
         * Check Important Items :
         * Size File
         * File Exists
         */

        if ( file_exists($Name) ) {
            $Message  = $ufo->lng("file already exists");
            $Continue = false;
        }

        if ( $Size > $LimitSize ) {
            $Message  = $ufo->lng("File size is more than allowed.");
            $Message .= " " . $ufo->rlng("The maximum file size for uploading is %n %n", $LimitSize, "MB");
            $Continue = false;
        }

        foreach ($types ?? $this->limit_type_files() as $k => $item) {
            if ( strchr(isset($types) ? $item : $k, $Type) )
                $Access = true;
        }

        if ( $Access && $Continue ) {
            $Name = isset($name) ? "$name.$Type": $Name;

            if (isset($no_type)) {
                $Name = explode(".", $Name);
                $Name = $Name[0];
            }

            if ( move_uploaded_file($file["tmp_name"], $Directory . $Name) ) {
                $Status  = 200;
                $Message = $ufo->lng("Uploaded successfully");
            } else {
                $Status  = 503;
                $Message = $ufo->lng("Failed upload");
            }
        } else if (!$Access) {
            $Status  = 403;
            $Message = $ufo->lng("File format is not allowed");
        }

        return !isset($array) ? $ufo->status($Status, $Message) : [
            $Status, $Message
        ];
    }

}