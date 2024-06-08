/**
 * Copyright (c) 2022-2024 UFOCMS
 *
 * This software is licensed under the GPLv3 license.
 * See the LICENSE file for more information.
 */

ufo.freeze(ufo_info);

/**
 * Preload I18n
 * @type {Promise<*|[]>}
 */
const ufo_i18n = async function () {
    return await fetch(ufo_info.web_url + "float/i18n")
        .then(result => result.json()).catch(() => [])
}();

ufo.apply(null, async function () {
    "use strict";

    let $_this;
    let $saver = {
        "lang": "ufo",
        "languages": await ufo_i18n,
        "init": 0,
        "page": typeof ufo_info.page === "object" ? (
            (ufo_info.page.plugin ?? ufo_info.page.page) ?? "dashboard"
        ) : "dashboard",
        "changedPage": false,
        "checkLogin" : false,
        "clear"  : false,
        "events" : {},
        "onerror": [],
        "notify" : [],
        "widgets": [],
        "setting": [],
        "advance_setting": {},
        "security": {},
        "paging"  : [],
        "xhr"     : {
            ce: 0
        },
        "member"  : {
            actions: [],
            last_page: 1
        },
        "managers": {},
        "pages"   : {
            actions: []
        },
        "ctxmenu" : {},
        "fm"      : {
            option: [],
            uploads: [],
            FILES: [],
            dir: `..${ufo_info.slash}content${ufo_info.slash}files${ufo_info.slash}`
        }
    };

    let debug  = ufo_info.debug === "true" ?? false,
        admin_ajax_url = ufo_info.ajax_url,
        admin_web_url  = ufo_info.admin_url,
        web_url        = ufo_info.web_url,
        SLASH          = ufo_info.slash,
        is_panel       = ufo_info.panel,
        file_types     = ufo_info.types,
        ajax_error = false,
        fm_select_items = {
            folder: [],
            file: []
        };

    $_this = {

        init ( ) {
            $_this.JPlugin();
            $_this.addFuns();

            if ( $saver.clear )
                $.console().clear();

            try {
                $saver.clear = true;

                $_this.admin_login();

                if ( is_panel )
                    $_this.init_panel();

                new Promise((resolve, reject) => {
                    try {
                        $.fun().do({name: "exec", param: {}});
                        resolve();
                    } catch ( e ) {
                        $_this.printError("Error execute script", e)
                    }
                }).then($_this.nextProcess).catch(() => {
                    $_this.printError("Error execute script")
                });

                $_this.panelConsole();

                if ( is_panel ) {
                    $_this.panel_loader(200);
                }
            } catch ( error ) {
                $_this.layer_loader(false);
                $_this.printError(' Debug corrupt scripts ', error);
            }
        },

        init_panel ( ) {
            $_this.menu();
            $_this.dashboard();
            $_this.managers();
            $_this.defaultSetting();
            $_this.setting();
            $_this.members();
            $_this.pages();
            $_this.comments();
            $_this.media_manager();
            $_this.ajax_loader();
            $_this.paging();
            $_this.auto_completion();
        },

        lng (arg = {}) {
            if (typeof arg === "string") arg = {
                string: arg
            };

            if (arg.string === "*")
                return $saver.languages;

            return $saver.languages[arg.string] ?? arg.string;
        },

        panelConsole ( ) {
            $.console().print("%cUFOCMS LAUNCHED", `text-align: center;text-shadow:0 1px 0 hsl(174,5%,80%),0 2px 0 hsl(174,5%,75%),0 3px 0 hsl(174,5%,70%),0 4px 0 hsl(174,5%,66%),0 5px 0 hsl(174,5%,64%),0 6px 0 hsl(174,5%,62%),0 7px 0 hsl(174,5%,61%),0 8px 0 hsl(174,5%,60%),0 0 5px rgba(0,0,0,.05),0 1px 3px rgba(0,0,0,.2),0 3px 5px rgba(0,0,0,.2),0 5px 10px rgba(0,0,0,.2),0 10px 10px rgba(0,0,0,.2),0 20px 20px rgba(0,0,0,.3);font-size: 75px`);
        },

        addFuns ( ) {
            $.fun().apply({
                name: "req",
                method: $_this.request
            });
            $.fun().apply({
                name: "getParam",
                method: $_this.getParameter
            });
            $.fun().apply({
                name: "change_page",
                method: $_this.changePage
            });
            $.fun().apply({
                name: "when_change_page",
                method: function ( {name, fn} ) {
                    let has = false;
                    if ( typeof name !== "undefined" ) {
                        if ( typeof $saver.events.changePage === "undefined" ) {
                            $saver.events.changePage = []
                        }
                        $saver.events.changePage.map(i => (i.name === name ? has = true : ""));
                        if ( !has && typeof fn === "function" ) {
                            $saver.events.changePage.push({name, fn})
                        }
                    }
                }
            });
            $.fun().apply({
                name: "panel_loader",
                method: $_this.panel_loader
            });
            $.fun().apply({
                name: "register_widget",
                method: function (op) {
                    if ( typeof op.name !== "undefined" && typeof op.method === "function" ) {
                        let has = false;
                        $.each($saver.widgets, ( k, v ) => {
                            if ( op.name === v.name ) {
                                has = true
                            }
                        });
                        if ( !has ) {
                            $saver.widgets.push({
                                name: op.name,
                                method: op.method
                            });
                        }
                    }
                }
            });
            $.fun().apply({
                name: "unset_default_ajax_action",
                method: function () {
                    $_this.set_ajax_error(()=>{});
                }
            });
            $.fun().apply({
                name: "unset_ajax_loader",
                method: function () {
                    $_this.layer_loader(false)
                }
            });
            $.fun().apply({
                name: "media",
                method: $_this.media
            });
            $.fun().apply({
                name: "remove_array",
                method: $_this.remove_array
            });
            $.fun().apply({
                name: "remove_obj",
                method: $_this.remove_obj
            });
            $.fun().apply({
                name: "search",
                method: $_this.search
            });
            $.fun().apply({
                name: "onerror",
                method: $_this.onerror
            });
            $.fun().apply({
                name: "notify",
                method: $_this.notify
            });
            $.fun().apply({
                name: "this_page",
                method: ( ) => {return $saver.page}
            });
            $.fun().apply({
                name: "register_setting",
                method  ( {name, method} ) {
                    $saver.setting[name] = method;
                }
            });
            $.fun().apply({
                name: "lng",
                method: $_this.lng
            });
            $.fun().apply({
                name: "member",
                method ( {action, data} ) {
                    const actions = {
                        merge_table ( ) {
                            if ( typeof data.th === "undefined" || typeof data.td === "undefined" ) {
                                $_this.printError('Member action (merge_table) ', "Please Enter (th, td)");
                            } else {
                                $(`.ufo-table-members thead tr th:last-child`).before(`<th>${data.th}</th>`);
                                $(`.ufo-table-members tbody tr td:last-child`).before(`<td data-lable="${data.th}">${data.td}</td>`);
                            }
                        },
                        add_action ( ) {
                            $(`.member-action-select`).append(`<option value="${typeof data.val !== "undefined" ? data.val : "member action : undefined val"}">${typeof data.title !== "undefined" ? data.title : "member action : undefined title"}</option>`);
                            if ( typeof data.name === "undefined" || typeof data.method === "undefined" ) {
                                $_this.printError('Member action (add_action)', "Please Enter (name, method)");
                            } else {
                                $saver.member.actions[data.name] = data.method;
                            }
                        }
                    };
                    return typeof actions[action] !== "undefined" ? actions[action]() : false;
                }
            });
            $.fun().apply({
                name: "pages",
                method ( {action, data} ) {
                    const actions = {
                        merge_table ( ) {
                            if ( typeof data.th === "undefined" || typeof data.td === "undefined" ) {
                                $_this.printError('Page action (merge_table) ', "Please Enter (th, td)");
                            } else {
                                $(`.ufo-table-pages thead tr th:last-child`).before(`<th>${data.th}</th>`);
                                $(`.ufo-table-pages tbody tr td:last-child`).before(`<td data-lable="${data.th}">${data.td}</td>`);
                            }
                        },
                        add_action ( ) {
                            $(`.pages-action-select`).append(`<option value="${typeof data.val !== "undefined" ? data.val : "page action : undefined val"}">${typeof data.title !== "undefined" ? data.title : "page action : undefined title"}</option>`);
                            if ( typeof data.name === "undefined" || typeof data.method === "undefined" ) {
                                $_this.printError('Page action (add_action)', "Please Enter (name, method)");
                            } else {
                                $saver.pages.actions[data.name] = data.method;
                            }
                        }
                    };
                    return typeof actions[action] !== "undefined" ? actions[action]() : false;
                }
            });
            $.fun().apply({
                name: "paging",
                method ( { name, method } ) {
                    let has = false;

                    $saver.paging.map(i => {
                        if (i.name === name)
                            has = true;
                    });

                    if (has) {
                        $_this.remove_obj({
                            obj: $saver.paging,
                            prop: "name",
                            val: name
                        });
                    }

                    $saver.paging.push({name, method});
                    $_this.paging();
                }
            });
            $.fun().apply({
                name: "add_paging",
                method: $_this.add_paging
            });
            $.fun().apply({
                name: "popup_message",
                method: $_this.popup_message
            });
            $.fun().apply({
                name: "contextmenu",
                method: $_this.contextmenu
            });
            $.fun().apply({
                name: "fm_register_option",
                method: function ( {name, callbacks} ) {
                    let find = false;
                    $.each($saver.fm.option, (k, v) => {if ( v.name == name ) find = true;});
                    if ( !find ) {
                        $saver.fm.option.push({
                            name,
                            callbacks: {
                                action: typeof callbacks.action === "function" ? callbacks.action : ( ) => {
                                    alert($_this.lng("The entered action is not a function! Please define a function"));
                                },
                                select: typeof callbacks.select !== "undefined" ? callbacks.select : ( ) => {},
                                unselect: typeof callbacks.unselect !== "undefined" ? callbacks.unselect : ( ) => {},
                            }
                        });
                    }
                }
            });
            $.fun().apply({
                name: "layer_lock",
                method: function (
                    {
                        content= "",
                        id= (Math.floor(Math.random() * 10000))
                    }) {
                    return {
                        create(data) {
                            $("body").prepend(`<div class='ufo-layer-lock' data-id="${id}">${(
                                typeof data === "undefined" ? content : data
                            )}</div>`);
                        },
                        get_id() {
                            return id;
                        },
                        remove() {
                            $(`.ufo-layer-lock[data-id="${id}"]`).remove();
                        }
                    };
                }
            });
            $.fun().apply({
                name: "open_window",
                method: function ( {href, width = 400, height = 400} ) {
                    return window.open(href, "", `width=${width},height=${height}`)
                }
            });
            $.fun().apply({
                name: "advance_setting",
                method: data => {
                    if ( typeof data.id !== "undefined" && typeof data.result !== "undefined" ) {
                        $saver.advance_setting[data.id] = data.result;
                        $(`.ufo-save-advance-setting`).removeAttr("disabled");
                    }
                }
            });
            $.fun().apply({
                name: "security_setting",
                method: data => {
                    if ( typeof data.id !== "undefined" && typeof data.result !== "undefined" ) {
                        $saver.security[data.id] = data.result;
                    }
                }
            });
            $.fun().apply({
                name: "image_error",
                method: $_this.img_error
            });
            $.fun().apply({
                name: "ufo_editor_init",
                method: function ( fn ) {
                    if ( typeof fn === "function" ) {
                        if ( ufo.isNULL(ufo.get("ufo_editor_init_fns")) ) {
                            ufo.save("ufo_editor_init_fns", []);
                        }
                        ufo.push("ufo_editor_init_fns", fn);
                    }
                }
            });
            $.fun().apply({
                name: "reload_options",
                method: $_this.options
            });
            $.fun().apply({
                name: "selectable_table",
                method: function (args) {
                    const checks = {}, ch_length = (len = 0) => {
                        $.each(checks, () => len++);
                        return len
                    };

                    if (typeof args["items"] !== "undefined")
                        args.items.map(i => checks[i.id] = i);

                    function request (data) {
                        $.fun().do({
                            name: "req",
                            param: {
                                data: {
                                    callback: "selectable_table",
                                    ...args.query,
                                    ...data
                                },
                                done: renderTable
                            }
                        })
                    }

                    function renderTable (result) {
                        $(`.ufo-window-selectable-table`).remove();
                        $(`.content-page`).append(
                            `<div class="media-container ufo-window-selectable-table">
                                    <div data-media="">
                                        <div class="media-header">
                                           <i class="ufo-icon-x f-right cls-media action-media"></i>
                                        </div>
                                        <div class="content overflow-auto ufo-selectable-table-cn" style="padding: 10px; height: calc(100% - 70px);">
                                            <div class="flex mb-10 head-option-pages">
                                                <div class="right flex align-center ufo-selectable-table-toolbar">
                                                    <button class="btn btn-light mr-10 ufo-selectable-table-count">${$_this.lng("%n items selected").replace("%n", ch_length())}</button>
                                                </div>
                                                <div class="left flex flex-start">
                                                    <input class="form-control ufo-search-selectable-table" placeholder="${$_this.lng("search")}" value="" type="search">
                                                    <button class="btn btn-primary ufo-btn-search-selectable-table ml-10 mr-10">
                                                        <i class="ufo-icon-search font-size-25px"></i>
                                                    </button>
                                                </div>
                                            </div>${result}
                                        </div>
                                    </div>
                                </div>`);

                        if (typeof args.limit === "number") {
                            const checkbox_all = $(`input[type="checkbox"][data-id="all"]`);
                            $(`label[for="${checkbox_all.attr("id")}"]`).remove();
                            checkbox_all.remove();
                        }

                        let chHead = $(`.ufo-table-ufo-selectable-table.has-checkbox thead input[type="checkbox"][data-id="all"]`),
                            chBody = $(`.ufo-table-ufo-selectable-table.has-checkbox tbody input[type="checkbox"]`),
                            checkbox = function (first) {
                                const id = Number($(this).data("id"));

                                if (typeof args.limit === "number" && typeof checks[id] === "undefined") {
                                    if (ufo.count(checks) >= args.limit) {
                                        $(this).prop("checked", false);
                                        $.ufo_dialog({
                                            title: $_this.lng("You cannot choose more than %n item").replace("%n", args.limit),
                                            options: {
                                                layer: "above"
                                            }
                                        });
                                        return false;
                                    }
                                }

                                if (first)
                                    $(`.ufo-table-ufo-selectable-table.has-checkbox tbody input[type="checkbox"]${!$(this).prop("checked") ? ":checked" : ""}`).click();
                                else if (typeof checks[id] !== "undefined")
                                    delete checks[id];
                                else checks[id] = {
                                        id: id,
                                        title: $(`input[type="checkbox"][data-id="${id}"]`).parent().find("span").text()
                                    };
                                $(`.ufo-selectable-table-count`).html($_this.lng("%n items selected").replace(`%n`, ch_length()));
                            };

                        $.each(checks, (k, v) =>
                            $(`.ufo-table-ufo-selectable-table.has-checkbox tbody input[type="checkbox"][data-id="${k}"]`).click()
                        );

                        $(`.ufo-window-selectable-table .cls-media`).unbind().click(function ( ) {
                            typeof args["callback"] !== "undefined" ? args.callback(function (result = []) {
                                $.each(checks, (k, v) => result.push(v));
                                return result
                            }()) : "";
                            $(`.ufo-window-selectable-table`).remove()
                        });

                        $(`.ufo-selectable-table-count`).unbind().click(function () {
                            $.ufo_dialog({
                                title: $_this.lng("Selected items"),
                                content: function (list = []) {
                                    $.each(checks, (k, v) => {
                                        list.push({
                                            id: k,
                                            title: v.title
                                        })
                                    });
                                    return list
                                }(),
                                options: {
                                    selection: true,
                                    textField: "title",
                                    valueField: "id",
                                    multiple: true,
                                    allowSearch: true,
                                    okText: $_this.lng("Delete"),
                                    cancelText: $_this.lng("close"),
                                    layer: "above",
                                    callbacks: {
                                        itemSelect: function (e, i) {
                                            $.each(i, (k, v) =>
                                                $(`input[type="checkbox"][data-id="${v.id}"]:checked`).click()
                                            );
                                            $(`.ufo-selectable-table-count`).html(
                                                $_this.lng("%n items selected").replace(`%n`, ch_length())
                                            );
                                        }
                                    }
                                }
                            })
                        });

                        $.fun().do({
                            name: "paging",
                            param: {
                                name: "ufo-selectable-table-paging",
                                method: page => request({paging: page})
                            }
                        });

                        $.fun().do({
                            name: "search",
                            param: {
                                input: ".ufo-search-selectable-table",
                                container: ".ufo-selectable-table tbody",
                                items: ".ufo-selectable-table tbody tr",
                                prop: ".title"
                            }
                        });
                        $(".ufo-btn-search-selectable-table").unbind().click(function () {
                            const input = $(".ufo-search-selectable-table"), val = input.val();
                            if (val.length >= 3) request({
                                search: {
                                    ...args.query.search,
                                    value: val
                                }
                            }); else request()
                        });
                        $(".ufo-search-selectable-table").bind("input", function () {
                            if ($(this).val().length <= 0) request()
                        }).on("keyup", function (e) {
                            if (e.key === "Enter" || e.keyCode === 13) {
                                $(".ufo-btn-search-selectable-table").click()
                            }
                        });

                        chHead.unbind().click(function () {checkbox.bind(this)(true)});
                        chBody.unbind().click(function () {checkbox.bind(this)(false)})
                    }

                    request();
                }
            })
        },

        options ( ) {
            $(`table .clickable`).each(function () {
                $(this).unbind().click(function () {
                    $.ufo_dialog({title: $(this).html()})
                });
            });
        },

        printError (title, error, notify_title, notify_icon) {
            $(`.system-float-notice-container`).remove();
            $.fun().do({
                name: "notify",
                param: {
                    title: typeof notify_title === "undefined" ? "system crash" : notify_title,
                    icon: typeof notify_icon === "undefined" ? "ufo-icon-error-dead" : notify_icon,
                    time: 2
                }
            });
            $.console().group(`%c${title}`, "background:red;color:#ffffff;font-weight:bold;padding: 4px;border-radius: 4px");
            $.console().print(error);
            $.console().groupEnd(error);
        },

        onerror (arg) {
            if ( typeof arg.name === "undefined" || typeof arg.method !== "function" ) return false;
            let status = true;
            $.each($saver.onerror, (k, v) => v.name === arg.name ? status = false : "");
            if ( !status ) return false;
            $saver.onerror.push({name: arg.name, method: arg.method});
        },

        detectError ( ) {
            window.onerror = function(error) {
                let count = 0;
                $.each($saver.onerror, (k, v) => {
                    count++; v.method(error);
                    setTimeout(()=>{
                        if ( !debug ) {
                            $saver.clear = false;
                            $_this.changePage({page: "dashboard"});
                        }
                        if ( !debug ) {
                            $saver.clear = false;
                            $_this.changePage({page: "dashboard"});
                            setTimeout(()=>{
                                $.console().clear();
                                $_this.panelConsole();
                            }, 105);
                        } else {
                            $_this.init();
                        }
                        $_this.printError(` Error Number ${count} : Debug corrupt scripts `, error);
                    }, 110);
                });
            };
        },

        auto_completion ( ) {
            // Scroll Content Page
            // $(".content-page").ufo_scroll({type: "v"});

            // Full Screen
            $(".fullscreen-page").unbind().click(function () {
                if ( $(this).hasClass("active") ) {
                    document.exitFullscreen();
                    $(this).removeClass("active").removeClass("ufo-icon-minimize").addClass("ufo-icon-maximize");
                } else {
                    $.fullScreen();
                    $(this).addClass("active").removeClass("ufo-icon-maximize").addClass("ufo-icon-minimize");
                }
            });

            // Exit
            $(".exit-cms").unbind().click(function () {
                location.href = web_url;
            });

            // Set Img Error
            $_this.img_error();

            // Context Menu
            if ( !ufo.os.mobile ) {
                $(window).on("contextmenu", e => e.preventDefault());
                $.fun().do({
                    name: "contextmenu",
                    param: {
                        target: "input, textarea",
                        not: function () {
                            let join = "";
                            let not = ["button", "submit", "radio", "checkbox", "color", "date", "file", "image", "datetime-local", "month", "range", "reset", "week", "time", "hidden"];
                            $.each(not, (k, v) => {
                                join += `input[type="${v}"],textarea[type="${v}"],`;
                            });
                            return join.removeLast(1);
                        }(),
                        items: function () {
                            const item = {};
                            item[`<span class="f-left">${$_this.lng("copy")}</span>`] = target => {
                                target.select();
                                target.val().copy();
                            };
                            item[$_this.lng("cut")] = target => {
                                target.select();
                                target.val().copy();
                                target.val("");
                            };
                            return item;
                        }()
                    }
                });
            }
            $(window).click(()=>$(".ufo-context-menu").remove()).mousedown(function(e){if(e.which===3){$(".ufo-context-menu").remove()}});

            // Fix Scroll
            $("*").on("scroll", function ( ) {
                $(".ufo-context-menu").remove();
            });

            // Fix resize
            if ( !ufo.os.mobile ) {
                $(window).on("resize", function () {
                    if ( !$(".ufo-layer-float-resize").length ) {
                        const layer_lock = $.fun().do({name: "layer_lock"});
                        layer_lock.create(`<div class="ufo-layer-float-resize width-100-cent height-100-cent flex flex-center align-center flex-direction-column"><i class="ufo-resize-animate" style="font-size: 85px"></i><h3 class="mt-10">${$_this.lng("setup page size")}</h3></div>`);
                        setTimeout(layer_lock.remove, 1500);
                    }
                });
            }

            // UFO Tabs
            $(`.ufo-tabs`).find(".ufo-tabs-items").unbind().click(function ( ) {
                $(".ufo-tabs-pages").removeClass("active");
                $(".ufo-tabs-items").removeClass("active");

                $(`.ufo-tabs-pages[data-ufo-tab="${$(this).data("ufo-tab")}"]`).addClass("active");
                $(this).removeClass("active").addClass("active");

                $(`.ufo-tabs`).trigger("ufoTabs.change", $(this));
            });
        },

        request (op) {
            return $.ajax($.extend({
                url : admin_ajax_url,
                type: "POST"
            }, op, {
                beforeSend: typeof op.loader !== "undefined" ? op.loader : undefined,
                success   : typeof op.done   !== "undefined" ? op.done   : undefined,
                error     : typeof op.error  !== "undefined" ? op.error  : undefined
            }));
        },

        ajax_loader ( ) {
            $.fun().do({
                name: "onerror",
                param: {
                    name: "ajaxLoader",
                    method: ( ) => {
                        $_this.panel_loader(503);
                        $_this.layer_loader(false);
                    }
                }
            });
            $(document)
                .ajaxStart(function () {
                    $saver.xhr.ce = 0;
                    $_this.panel_loader(0);
                    if ( $(window).width() <= 1000 ) {
                        $_this.layer_loader(true);
                    }
                })
                .ajaxStop(function () {
                    $saver.xhr.ce = 0;
                    $_this.layer_loader(false);
                    $_this.panel_loader(200);
                    $_this.auto_completion();
                })
                .ajaxError(function (xhr) {
                    if ( !ajax_error ) {
                        if ( $saver.xhr.ce === 0 ) {
                            $saver.xhr.ce += 1;
                            if ( debug ) {
                                $_this.printError($_this.lng("Connection error"), xhr, $_this.lng("Connection error"), "ufo-icon-wifi-off");
                            } else {
                                $_this.popup_message({
                                    title: 400,
                                    content: typeof $_this.lng("Connection error") !== "undefined" ? $_this.lng("Connection error") : "Connection error"
                                });
                            }
                            $_this.layer_loader(false);
                            $_this.panel_loader(503);
                        }
                    } else if ( typeof ajax_error === "function" ) {
                        ajax_error();
                    }
                });
        },

        set_ajax_error (action) {
            ajax_error = action;
        },

        getParameter ({address, key}) {
            let url = new URL(address);
            let param = url.searchParams.get(key);
            return !param ? false : param;
        },

        changePage (options) {
            const {plugin, callback, page, data} = $.extend({
                callback: undefined,
                plugin: null,
                page: null,
                data: {}
            }, options);

            const data_merged = $.extend({
                from_admin: true,
                callback: "load_page",
                page, plugin
            }, data);

            $_this.request({
                data : data_merged,
                cache: false,
                loader: () => {
                    if (typeof callback === "function")
                        callback(0);
                },
                done: (data) => {
                    $saver.page = ufo.isNULL(data_merged.plugin) ? data_merged.page : data_merged.plugin;
                    $(".content-page").html(data); $_this.init();
                    if (typeof callback === "function")
                        callback(200, data);
                    ($saver.events?.changePage ?? []).forEach(i => i.fn($saver.page));
                },
                error: (xhr) => {
                    $(".content-page").html("Network Error!");
                    if (typeof callback === "function") callback(-1);
                }
            })
        },

        menu ( ) {
            $(`.open-menu-side`).unbind().click(function () {
                $_this.menu_action();
            });

            if ($saver.init <= 0) {
                $(`.menu, .admin-header`).unbind().click(function (event) {
                    event.stopPropagation();
                });

                if ($(document).width() <= 1000) {
                    $(window).click(function () {
                        if ($(".open-menu-side").hasClass("active")) {
                            $(".open-menu-side").click()
                        }
                    })
                }
            }

            $(`.menu-items li`).unbind().click(function () {
                $(`.menu-items li`).removeClass("active");

                let $this = $(this);

                $_this.changePage({
                    data: {
                        from_admin: true,
                        callback  : "load_page",
                        save_last_page: true,
                        page      : $this.data("page"),
                        plugin    : !$this.data("plugin") ? null : $this.data("plugin")
                    },
                    callback: (step, data) => {
                        $_this.panel_loader(step);
                        switch (step) {
                            case 200:
                                $saver.page = !$this.data("plugin") ? $this.data("page") : $this.data("plugin");
                                $this.addClass("active");
                                if ( $(window).width() <= 1000 ) {
                                    $(`.open-menu-side`).addClass("active");
                                    $_this.menu_action();
                                }
                                $saver.events.changePage.map(i => i.fn($saver.page));
                                break;
                        }
                    }
                })
            });
        },

        menu_action ( ) {
            const menu = $(`.open-menu-side`);
            if ( menu.hasClass("active") ) {
                menu.removeClass("active").removeClass("ufo-icon-x").addClass("ufo-icon-menu");
                $('.menu').addClass("close");
                setTimeout(()=>{
                    $('.menu').removeClass("active").removeClass("close");
                }, 300);
            } else {
                menu.addClass("active").removeClass("ufo-icon-menu").addClass("ufo-icon-x");
                $('.menu').removeClass("close").addClass("active");
            }
        },

        nextProcess ( ) {
            // Dashboard process
            if ( $saver.page === "dashboard" ) {
                // Run all widget script
                $.each($saver.widgets, ( k, v ) => v.method());
            }

            $.fun().do({
                name: "when_change_page",
                param: {
                    name: "ufo",
                    fn: function ( page ) {
                        $(".content-page").scrollTop(0)
                    }
                }
            });

            $_this.options();

            $saver.init++;
        },

        admin_login ( ) {
            $(".form-login").submit(function (e) {
                e.preventDefault();
                
                const $button = $(this).find(`button[type="submit"]`);
                if ($button.hasClass("lock")) return false;
                
                const beforeText = $button.text();
                const $inputs = {};

                $(this).serializeArray().map(input =>
                    $inputs[input.name] = (
                        input.name === "password" ? $.md5(input.value) : input.value
                    )
                );

                ufo.req({
                    data: {
                        callback: "login",
                        inputs: $inputs
                    },
                    dataType: "json",
                    loader: () => {
                        $button.addClass("lock");
                        $button.html('<i class="ufo-icon-circle-notch rotating"></i>');
                    },
                    done: (data) => {
                        $button.removeClass("lock");
                        $button.html(beforeText);
                        if (data.status === 200) {
                            if (is_panel) {

                                $(`.item-menu.active`).click();
                                $(`.ufo-login-popup`).remove();

                            } else location.href = admin_web_url;
                        } else {
                            const $errorText = $(`#ufo-error-text-login`);
                            $errorText.html(`<i class="ufo-icon-info cl-danger font-size-16px"></i><span class="ml-5 mr-5 font-size-14px">${data.message}</span>`);
                            setTimeout(() => $errorText.empty(), 2000);
                        }
                    },
                    error: (xhr) => {
                        $button.removeClass("lock");
                        $button.html($_this.lng("Connection error"));
                        setTimeout(() => {
                            $button.html(beforeText);
                        }, 2000);
                    }
                });
            })
        },

        dashboard ( ) {
            if ( $saver.page !== "dashboard" ) return;

            $(`.ufo-logout-admin`).unbind().click(function (e) {
                e.stopPropagation();
                location.href = admin_web_url + "logout.php";
            });

            // Logs
            $(".ufo-btn-empty-logs").unbind().click(function () {
                $.fun().do({
                    name : "req",
                    param: {
                        data: {
                            callback: "empty_logs"
                        },
                        dataType: "json",
                        done ( result ) {
                            if ( result.status === 200 ) {
                                $_this.changePage({
                                    page: "dashboard"
                                });
                                return false;
                            }
                            $_this.popup_message({
                                title: result.message
                            })
                        }, error ( ) {
                            $_this.popup_message({
                                title: $_this.lng("Connection error")
                            })
                        }
                    }
                })
            });

            // Widget notes
            $.fun().do({
                name: "register_widget",
                param: {
                    name: "ufo_widget_notes",
                    method: function () {

                        function remove_notes ( ) {
                            const remover = $(this);
                            const ID      = remover.data("id");

                            $.fun().do({
                                name: "req",
                                param: {
                                    data: {
                                        callback: "remove_note",
                                        id: ID
                                    },
                                    dataType: "json",
                                    done ( result ) {
                                        if ( result.status === 200 ) {
                                            $(`.ufo-note-list ul li[data-id="${ID}"]`).remove();
                                            return false;
                                        }
                                        $_this.popup_message({
                                            title: $_this.lng(result.message)
                                        })
                                    }, error ( ) {
                                        $_this.popup_message({
                                            title: $_this.lng("Connection error")
                                        })
                                    }
                                }
                            })
                        }

                        function show_full ( ) {
                            $_this.popup_message({
                                content: $(this).text()
                            });
                        }

                        $(".ufo-add-note-btn").unbind().click(function () {
                            const note = $(".ufo-add-note-input");

                            if ( $_this.detectVoid(note.val()) ) {
                                $.fun().do({
                                    name: "req",
                                    param: {
                                        data: {
                                            callback: "add_note",
                                            note: note.val()
                                        },
                                        dataType: "json",
                                        done ( result ) {
                                            if ( result.status === 200 ) {
                                                $(".ufo-note-list ul").prepend(`<li data-id="${result.message}"><span>${note.val()}</span><i class="ufo-icon-x remove" data-id="${result.message}"></i></li>`); note.val("");
                                                $(".ufo-note-list ul li .remove").unbind().click(remove_notes);
                                                $(".ufo-note-list ul li span").unbind().click(show_full);
                                                return false;
                                            }
                                            $_this.popup_message({
                                                title: $_this.lng(result.message)
                                            })
                                        },
                                        error ( ) {
                                            $_this.popup_message({
                                                title: $_this.lng("Connection error")
                                            })
                                        }
                                    }
                                })
                            }
                        });
                        $(".ufo-note-list ul li .remove").unbind().click(remove_notes);
                        $(".ufo-note-list ul li span").unbind().click(show_full);
                    }
                }
            });
        },

        setting ( ) {
            if ( $saver.page !== "setting" ) return;

            $.fun().do({
                name: "search",
                param: {
                    input: ".setting-search",
                    nothing: $(".setting-search").data("nothing"),
                    container: ".settings",
                    items: ".setting-items",
                    prop: ".content .title"
                }
            });

            $(".setting-items").unbind().click(function () {
                const item = $(this);
                $.fun().do({
                    name: "req",
                    param: {
                        data: {
                            page: item.data("setting"),
                            callback: "setting"
                        },
                        done: result => {
                            $(`.content-page`).html(result);
                            page_script(item.data("setting"));
                            $saver.events.changePage.map(i => i.fn($saver.page));
                        }
                    }
                });
            });

            function page_script ( m ) {
                typeof $saver.setting[m] !== "undefined" ? $saver.setting[m]() : console.log(m + " Not Found : Please Add Custom Script Setting");
            }
        },

        defaultSetting ( ) {
            $.fun().do({
                name: "register_setting",
                param: {
                    name: "menu",
                    method: $_this.menu_list
                }
            });
            $.fun().do({
                name: "register_setting",
                param: {
                    name: "plugins",
                    method: $_this.plugins
                }
            });
            $.fun().do({
                name: "register_setting",
                param: {
                    name: "templates",
                    method: $_this.templates
                }
            });
            $.fun().do({
                name: "register_setting",
                param: {
                    name: "market",
                    method: function () {
                        $.fun().do({
                            name: "ufo_api", param: "market"
                        })()
                    }
                }
            });
            $.fun().do({
                name: "register_setting",
                param: {
                    name: "advance-setting",
                    method: function () {
                        $_this.advance_setting();
                    }
                }
            });
            $.fun().do({
                name: "register_setting",
                param: {
                    name: "security",
                    method: function () {
                        $_this.security();
                    }
                }
            });
            $.fun().do({
                name: "register_setting",
                param: {
                    name: "update",
                    method: function () {
                        $_this.updateSystem();
                    }
                }
            })
        },

        members ( ) {
            if ($saver.page !== "members") return false;

            $.fun().do({
                name: "paging",
                param: {
                    name: "member-table-paging",
                    method: page => {
                        $saver.member.last_page = page;
                        load_members({to_page: page});
                    }
                }
            });
            $.fun().do({
                name: "member",
                param: {
                    action: "add_action",
                    data: {
                        title: $_this.lng("delete selected items"),
                        val: "delete",
                        name: "delete",
                        method: function (selections) {
                            if (selections.length === 0) {
                                $_this.popup_message({content: $_this.lng("first select the desired user")});
                            } else {
                                $.ufo_dialog({
                                    title: $_this.lng("remove users").toString(),
                                    content: function(){
                                        let join = '';
                                        $.each(selections, (k, v) => {
                                            join += "," + $(`.ufo-table-members tbody tr[data-id="${v}"] span.username`).text();
                                        });
                                        return join = join.substring(1);
                                    }(),
                                    options: {
                                        cancel: true,
                                        okText: $_this.lng("delete"),
                                        cancelText: $_this.lng("cancel"),
                                        callbacks: {
                                            okClick ( ) {
                                                this.hide();
                                                $.fun().do({
                                                    name: "req",
                                                    param: {
                                                        dataType: "json",
                                                        data: {
                                                            callback: "members",
                                                            action: "remove",
                                                            type: "multiple",
                                                            list: selections
                                                        }, loader ( ) {
                                                            $_this.layer_loader(true, false);
                                                        }, done ( result ) {
                                                            $_this.layer_loader(true, false);
                                                            if ( result.status === 200 ) {
                                                                $.each(selections, ( k, v ) => {
                                                                    const item = $(`.ufo-table-members tbody tr[data-id="${v}"]`);
                                                                    item.fadeOut("slow");
                                                                    setTimeout(() => item.remove(), 1000);
                                                                    setTimeout(item.remove, 2000);
                                                                });
                                                            } else {
                                                                $.fun().do({
                                                                    name: "popup_message",
                                                                    param: {
                                                                        title: $_this.lng("Error %n").replace("%n", result.status),
                                                                        content: result.message
                                                                    }
                                                                });
                                                            }
                                                        }
                                                    }
                                                });
                                            }
                                        }
                                    }
                                });
                            }
                            $(`.member-action-select option`).removeAttr("selected");
                            $(`.member-action-select .d-select`).attr("selected", true);
                        }
                    }
                }
            });

            const checkbox = $(".ufo-table-members");

            checkbox.find(`input[type="checkbox"][data-id="all"]`).unbind().click(function () {
                if ($(this).hasClass("checked")) {
                    checkbox.find(`tbody input[type="checkbox"]:checked`).click();
                    $(this).removeClass("checked");
                } else {
                    $(this).addClass("checked");
                    checkbox.find(`tbody input[type="checkbox"]`).each(function () {
                        if (!$(this).is(":checked")) {
                            $(this).addClass("checked").click();
                        }
                    });
                }
            });

            $(`.member-action-select`).unbind().bind("input", function () {
                const selections = [], val = $(this).val();
                checkbox.find(`tbody input[type="checkbox"]`).each(function () {
                    if ($(this).is(":checked")) selections.push($(this).data("id"));
                });
                if ( val !== "options" ) {
                    $(`.member-action-select option`).removeAttr("selected");
                    $(`.member-action-select option[value="options"]`).attr("selected", true);
                }
                if ( selections.length !== 0 ) {
                    return {options() {}, ...$saver.member.actions}[val](selections);
                } else {
                    $.ufo_dialog({title: $_this.lng("first select the desired user")})
                }
            });

            $(`.add-new-user`).unbind().click(function () {
                editor("add", {}, (result, d) => {
                    if ( result.status === 200 ) {
                        d.hide();load_members();
                    } else {
                        $_this.popup_message({
                            title: result.status,
                            content: result.message
                        });
                    }
                });
            });

            $(`.remove-member`).unbind().click(function () {
                const target = $(this);
                $.ufo_dialog({
                    title: $_this.lng("remove user"),
                    content: $_this.lng("Are you sure you want to delete user %n?").replace('%n',  $(`.ufo-table-members tbody tr[data-id="${target.data("mem")}"] .username`).html()),
                    options: {
                        cancel: true,
                        okText: $_this.lng("delete"),
                        cancelText: $_this.lng("cancel"),
                        callbacks: {
                            okClick ( ) {
                                this.hide();
                                $.fun().do({
                                    name: "req",
                                    param: {
                                        dataType: "json",
                                        data: {
                                            callback: "members",
                                            action: "remove",
                                            uid: target.data("mem")
                                        }, loader ( ) {
                                            $_this.layer_loader(true, false);
                                        }, done ( result ) {
                                            const item = $(`.ufo-table-members tbody tr[data-id="${target.data("mem")}"]`);
                                            $_this.layer_loader(true, false);
                                            if ( result.status === 200 ) {
                                                item.fadeOut("slow");
                                                setTimeout(()=>{
                                                    item.remove();
                                                }, 2000);
                                            } else {
                                                $.fun().do({
                                                    name: "popup_message",
                                                    param: {
                                                        title: $_this.lng("Error %n").replace("%n", result.status),
                                                        content: result.message
                                                    }
                                                });
                                            }
                                        }
                                    }
                                });
                            }
                        }
                    }
                });
            });

            $_this.search({
                input: ".search-member",
                container: ".ufo-table-members tbody",
                items: ".ufo-table-members tbody tr",
                prop: ".username"
            });
            $(`.btn-search-member`).unbind().click(function () {
                const val = $(`.search-member`).val().toString();
                if ( val.length === 0 ) {
                    load_members({to_page: 1});
                } else {
                    load_members({search: val});
                }
            });
            $(`.search-member`).unbind("keydown").bind("keydown", function ( e ) {
                if ( e.keyCode === 13 ) {
                    $(`.btn-search-member`).click()
                }
            });

            $(`.accept-member`).unbind().click(function () {
                const button = $(this);
                $.fun().do({
                    name: "req",
                    param: {
                        dataType: "json",
                        data: {
                            callback: "members",
                            action: "accept",
                            uid: button.data("mem")
                        }, loader ( ) {
                            $_this.layer_loader(true, false);
                        }, done ( result ) {
                            $_this.layer_loader(true, false);
                            if ( result.status === 200 ) {
                                button.remove()
                            } else {
                                $.fun().do({
                                    name: "popup_message",
                                    param: {
                                        title: $_this.lng("Error %n").replace("%n", result.status),
                                        content: result.message
                                    }
                                });
                            }
                        }
                    }
                });
            });

            $(`.edit-member`).unbind().click(function () {
                const target = $(this);
                editor("edit", {
                    mem: target.data("mem")
                },(result, d)=>{
                    if ( result.status === 200 ) {
                        d.hide();
                        load_members({
                            to_page: $saver.member.last_page
                        });
                    } else {
                        $_this.popup_message({
                            title: result.status,
                            content: result.message
                        });
                    }
                });
            });

            function editor(type, arg, done) {
                $.fun().do({
                    name: "req",
                    param: {
                        data: {
                            callback: "members",
                            action: "editor",
                            type: type, ...arg
                        },
                        dataType: "json",
                        done(result) {
                            $.ufo_dialog({
                                title: result.title,
                                content: result.content,
                                options: {
                                    okText: result.button,
                                    buttons: duDialog.OK_CANCEL,
                                    cancelText: $_this.lng("close"),
                                    callbacks: {
                                        okClick() {
                                            const d = this;
                                            const inputs = $(".member-input").ufo_inputs($_this.lng("please fill in the fields"));
                                            if (!inputs.error) {
                                                $.fun().do({
                                                    name: "req",
                                                    param: {
                                                        data: {
                                                            callback: "members",
                                                            action: "edit",
                                                            type: type,
                                                            photo: $saver.member.photo,
                                                            ...inputs, ...arg
                                                        }, done(result) {
                                                            done(JSON.parse(result), d, inputs);
                                                        }
                                                    }
                                                });
                                            }
                                        }
                                    }
                                }, done() {
                                    $(`.select-user-photo`).unbind().click(function () {
                                        $.fun().do({
                                            name: "media",
                                            param: {
                                                id: "user-photo",
                                                reset: false,
                                                show_label: true,
                                                limit: 1,
                                                types: "img",
                                                loader ( ) {$(`.e-user-cover-photo-loader`).removeClass("dn");},
                                                done ( ) {$(`.e-user-cover-photo-loader`).addClass("dn");},
                                                result(result) {
                                                    $saver.member.photo = result[0];
                                                    $(`.select-user-photo img`).attr("src", $saver.member.photo);
                                                }
                                            }
                                        });
                                    });
                                    $_this.img_error();
                                }
                            });
                        }
                    }
                });
            }

            function load_members ( data = {} ) {
                $.fun().do({
                    name: "change_page",
                    param: {
                        page: "members",
                        data: data,
                        callback ( status ) {
                            switch ( status ) {
                                case 0:$_this.layer_loader(true, false);break;
                                case 200:$_this.layer_loader(false, false);break;
                            }
                        }
                    }
                });
            }
        },

        media_manager ( ) {
            if ( $saver.page !== "files" ) return;

            let $media, select = false, now_file;
            function remove_selected(array, target){return array.filter(item => item != target)}

            const uploader = $_this.upload_file({
                name: "ufo_fm_manager",
                folder: $saver.fm.dir,
                limit: 99999
            });
            uploader.reset();

            return {
                init ( ) {
                    $media = this;
                    $media.reset();

                    $media.response();
                    $(window).resize($media.response);

                    $media.scroll();
                    $media.actionFiles();
                    $media.ctxMenu();
                    $media.action_toolbar();
                    $media.check();
                },
                reset ( ) {
                    $saver.fm.dir = `..${SLASH}content${SLASH}files${SLASH}`;
                    fm_select_items = {
                        folder: [],
                        file: []
                    };
                    select = false;
                },
                response ( ) {
                    $(".file-card").each(function (){
                        let card = $(this);
                        card.find("span").each(function () {
                            $(this).width(0);
                            $(this).width(Number(card.width() - 15) + "px");
                        });
                    });
                },
                check ( ) {},
                scroll ( ) {
                    $(".fm-row-folders").ufo_scroll({type: "h"});
                    $(".fm-toolbar").ufo_scroll({type: "h"});
                    $(".fm-toolbar-two").ufo_scroll({type: "h"});
                },
                actionFiles ( ) {
                    function select_action(target) {
                        target.addClass("selected");

                        fm_select_items.folder = remove_selected(fm_select_items.folder, target.data("address"));
                        fm_select_items.file = remove_selected(fm_select_items.file, target.data("address"));

                        switch (target.data("type")) {
                            case "folder":
                                fm_select_items.folder.push(target.data("address"));
                                break;
                            case "file":
                                fm_select_items.file.push(target.data("address"));
                                break;
                        }
                        select = true;
                    }
                    $(".file-card, .fm-folder-container").unbind().on('taphold',{delay: 300}, function () {
                        select_action($(this));
                        $media.do_action_toolbar_select();
                    }).on("click", function () {
                        const target = $(this);
                        if ( select ) {
                            if ( target.hasClass("selected") ) {
                                fm_select_items.folder = remove_selected(fm_select_items.folder, target.data("address"));
                                fm_select_items.file = remove_selected(fm_select_items.file, target.data("address"));
                                target.removeClass("selected");
                                $media.do_action_toolbar_unselect();
                                if ( fm_select_items.folder.length === 0 && fm_select_items.file.length === 0 ) {
                                    select = false;
                                }
                            } else {select_action(target)}
                        } else {
                            switch (target.data("type")) {
                                case "file":
                                    $media.actions().open_file(target);
                                    break;
                                case "folder":
                                    $media.actions().open_folder(target);
                                    break;
                            }
                        }
                    });
                },
                ctxMenu ( ) {
                    $.fun().do({
                        name: "contextmenu",
                        param: {
                            target: ".file-card-container,.fm-folder-container",
                            items: function (){
                                const items = {};
                                items[$_this.lng("delete")] = (item) => {
                                    let target;
                                    if ( item.hasClass("file-card-container") ) {target = item.find(".file-card");} else if ( item.hasClass("fm-folder-container") ) {target = item;}
                                    $.ufo_dialog({
                                        title: target.data("type") === "file" ? $_this.lng("delete %n").replace("%n", $_this.lng("file")) : $_this.lng("delete %n").replace("%n", $_this.lng("folder")),
                                        content: target.data("name"),
                                        options: {
                                            okText: $_this.lng("delete"),
                                            cancel: true,
                                            callbacks: {
                                                okClick ( ) {
                                                    const d = this;
                                                    $media.actions().delete(target.data("type"), target.data("address"), data => {
                                                        if ( data.status === 200 ) {
                                                            item.remove();
                                                        } else {
                                                            $.fun().do({
                                                                name: "popup_message",
                                                                param: {title: $_this.lng("System error")}
                                                            });
                                                        }d.hide();
                                                    });
                                                }
                                            }
                                        }
                                    });
                                };
                                items[$_this.lng("rename")] = k => {
                                    let target;

                                    if ( k.data("type") === "folder" ) {
                                        target = k;
                                    } else if ( k.find(".file-card").data("type") === "file" ) {
                                        target = k.find(".file-card");
                                    }

                                    $.ufo_dialog({
                                        title:"",
                                        content: function () {
                                            return `<input class="form-control fm-rename" value="${target.data("name")}">`;
                                        }(),
                                        options: {
                                            okText: $_this.lng("rename"),
                                            cancel: true,
                                            callbacks: {
                                                okClick( ) {
                                                    now_file = target;
                                                    $media.actions().rename($("input.fm-rename").val());
                                                    this.hide();
                                                }
                                            }
                                        }
                                    });
                                };
                                return items;
                            }()
                        }
                    });
                },
                actions ( ) {
                    return {
                        delete ( type, address, done ) {
                            $.fun().do({
                                name: "req",
                                param: {
                                    data: {callback: "media_action",action:"delete",type,address,prevent_ajax: "plugins"},
                                    dataType: "json",
                                    done ( result ) {
                                        done(result);
                                        if ( !$(".file-manager").html() ) {
                                            $(`.file-manager`).empty();
                                            $(`.file-manager`).parent().append(`<h4 class="fm-empty-files text-center width-100-cent">${$_this.lng("This folder is empty")}</center>`);
                                        }
                                    }, error ( xhr ) {
                                        $.fun().do({
                                            name: "popup_message",
                                            param: {
                                                title: $_this.lng("Connection error")
                                            }
                                        });
                                    }
                                }
                            });
                        },
                        rename ( name, content = "%NULL%" ) {
                            $.fun().do({
                                name: "req",
                                param: {
                                    data: {
                                        callback: "media_action",
                                        action: "save_changed",
                                        address: now_file.data("address"),
                                        name: name,
                                        content_file: content,
                                        prevent_ajax: "plugins"
                                    },
                                    dataType: "json",
                                    done ( result ) {
                                        if ( result.status === 200 ) {
                                            now_file.data("address", result.message.new_address);
                                            now_file.data("name", result.message.new_name);
                                            now_file.find("span").html(result.message.new_name);
                                        } else {
                                            $.fun().do({
                                                name: "popup_message",
                                                param: {
                                                    title: "",
                                                    content: result.message
                                                }
                                            });
                                        }
                                    }, error ( xhr ) {
                                        $.ufo_dialog({title: $_this.lng("Connection error")});
                                    }
                                }
                            });
                        },
                        open_file ( file ) {
                            now_file = file;
                            const address = file.data("address");
                            $.fun().do({
                                name: "req",
                                param: {
                                    data: {
                                        callback: "media_action",
                                        action: "info_file", address, prevent_ajax: "plugins"
                                    },
                                    done ( result ) {
                                        $(`body`).prepend(result);
                                        $(`.ufo-popup-modal .close`).unbind().click(function () {
                                            $(".ufo-popup-modal-layer").remove();
                                        });
                                        $media.actions().editor();
                                    },
                                    error ( xhr ) {
                                        $.ufo_dialog({title: $_this.lng("Connection error")});
                                    }
                                }
                            });
                        },
                        editor ( ) {
                            const inputs = {
                                link: $(".fm-info-link-file"),
                                name: $(".fm-info-name-file")
                            };
                            let editor;

                            if ( $("#fm-content-editor").length ) {
                                editor = CodeMirror.fromTextArea(document.getElementById("fm-content-editor"), {
                                    lineNumbers: true,
                                    mode: "text/html",
                                    matchBrackets: true
                                });
                                if ( $("body").data("theme") === "dark" ) {
                                    editor.setOption("theme", "dracula");
                                }
                            }

                            $(".fm-btn-dl").unbind().click(function () {
                                window.open(admin_web_url + "download.php?file=" + now_file.data("address"));
                            });

                            inputs.link.unbind().click(function () {this.select()});

                            $(".fm-save-changed-info").unbind().click(function () {
                                let data = {
                                    name: inputs.name.val(),
                                    content_file: (typeof editor !== "undefined" ? editor.getValue() : "%NULL%")
                                };
                                $.fun().do({
                                    name: "req",
                                    param: {
                                        data: {
                                            callback: "media_action",
                                            action: "save_changed",
                                            address: now_file.data("address"),
                                            ...data
                                        },
                                        dataType: "json",
                                        done ( result ) {
                                            if ( result.status === 200 ) {
                                                inputs.link.val(result.message.new_link);
                                                now_file.data("address", result.message.new_address);
                                                now_file.data("name", result.message.new_name);
                                                now_file.find("span").html(result.message.new_name);
                                            } else {
                                                $.fun().do({
                                                    name: "popup_message",
                                                    param: {
                                                        title: "",
                                                        content: result.message
                                                    }
                                                });
                                            }
                                        }, error ( xhr ) {
                                            $.ufo_dialog({title: $_this.lng("Connection error")});
                                        }
                                    }
                                });
                            });

                            $(".fm-delete-file").unbind().click(function () {
                                $.ufo_dialog({
                                    title: now_file.data("type") === "file" ? $_this.lng("delete %n").replace("%n", $_this.lng("file")) : $_this.lng("delete %n").replace("%n", $_this.lng("folder")),
                                    content: now_file.data("name"),
                                    options: {
                                        okText: $_this.lng("delete"),
                                        cancel: true,
                                        callbacks: {
                                            okClick ( ) {
                                                const d = this;
                                                $media.actions().delete(now_file.data("type"), now_file.data("address"), function () {
                                                    now_file.parent().remove();
                                                    $(".ufo-popup-modal-layer").remove();
                                                    now_file = undefined;
                                                    d.hide();
                                                });
                                            }
                                        }
                                    }
                                });
                            });
                        },
                        open_folder ( folder ) {
                            $.fun().do({
                                name: "req",
                                param: {
                                    data: {
                                        callback: "media_action",
                                        action: "open_folder",
                                        address: folder.data("address"),
                                        prevent_ajax: "plugins"
                                    },
                                    dataType: "json",
                                    done ( result ) {
                                        $(`.fm-empty-files`).remove();
                                        const files   = $(result.files);
                                        const folders = $(result.folders);
                                        const stats   = $(result.stats);

                                        if ( result.files == null ) {
                                            $(`.file-manager`).empty();
                                            $(`.file-manager`).parent().append(`<h4 class="fm-empty-files text-center width-100-cent">${$_this.lng("This folder is empty")}</center>`);
                                        }
                                        $(`.file-manager`).html(files.html());
                                        $(`.fm-row-folders`).html(folders.html());
                                        $(`.stats-fm`).html(stats.html());

                                        $media.init();
                                        $media.actions().folder_back(result.back);
                                        $saver.fm.dir = result.location;
                                    },
                                    error ( xhr ) {
                                        $.ufo_dialog({title: $_this.lng("Connection error")});
                                    }
                                }
                            });
                        },
                        folder_back ( folder ) {
                            const target = $(`.fm-btn-action[data-action="back"]`);
                            if ( folder === "../content" || folder === "..\\content" || folder === "content" ) {
                                target.attr("disabled", true).unbind();
                                return false;
                            }
                            target.attr("disabled", false).unbind().click(function () {
                                $media.actions().open_folder({
                                    data: function () {
                                        uploader.setFolder(folder);
                                        return folder;
                                    }
                                });
                            });
                        }
                    };
                },
                action_toolbar ( ) {
                    // Toolbar - 1
                    $.fun().do({
                        name: "fm_register_option",
                        param: {
                            name: "delete",
                            callbacks: {
                                action: function (items, option) {
                                    if ( items.file.length === 0 && items.folder.length === 0 ) {
                                        alert($_this.lng("Please select a file or folder"));
                                    } else {
                                        $.ufo_dialog({
                                            title: String(Number(fm_select_items.file.length + fm_select_items.folder.length)),
                                            content: $_this.lng("Are you sure you want to delete the selected items?"),
                                            options: {
                                                okText: $_this.lng("delete all"),
                                                cancel: true,
                                                callbacks: {
                                                    okClick ( ) {
                                                        const d = this;
                                                        $media.actions().delete("", items, result => {
                                                            $.each(result, (k, v) => {
                                                                if ( v ) {
                                                                    $(".file-card,.fm-folder-container").each(function () {
                                                                        $(this).removeClass("selected");
                                                                        if ( $(this).data("address") === k ) {
                                                                            if ( $(this).data("type") === "file" ) {
                                                                                $(this).parent(".file-card-container").remove();
                                                                            } else {
                                                                                $(this).remove();
                                                                            }
                                                                        }
                                                                    });
                                                                } else {
                                                                    const f = $(`.file-card[data-address="${k}"]`);
                                                                    f.addClass("error");
                                                                    f.find("span.error").html($_this.lng("Error deleting"));
                                                                    setTimeout(() => {
                                                                        $(".file-card.error").removeClass("error").removeClass("selected");
                                                                        $(".file-card.error span.error").empty();
                                                                    }, 3000);
                                                                }
                                                            });
                                                            $(`.fm-btn-action[data-action="delete"]`).attr("disabled", true);
                                                            option.selected(false);
                                                            option.reset();
                                                            d.hide();
                                                        });
                                                    }
                                                }
                                            }
                                        });
                                    }
                                },
                                select: function (items) {
                                    $(`.fm-btn-action[data-action="delete"]`).attr("disabled", false);
                                },
                                unselect: function (items) {
                                    if ( items.folder.length === 0 && items.file.length === 0 ) {
                                        $(`.fm-btn-action[data-action="delete"]`).attr("disabled", true);
                                    }
                                }
                            }
                        }
                    });

                    // Toolbar - 2
                    $.fun().do({
                        name: "fm_register_option",
                        param: {
                            name: "create_file",
                            callbacks: {
                                action: function () {
                                    $.ufo_dialog({
                                        title: $_this.lng("new file"),
                                        content:
                                            `<input placeholder="${$_this.lng("file name")}" name="name" class="form-control fm-new-file-input" autocomplete="off">
                                                 <select class="fm-select-type-file form-control mt-5">
                                                    <option value="txt">txt</option>
                                                 </select>
                                            `,
                                        options: {
                                            okText: $_this.lng("create"),
                                            cancel: true,
                                            callbacks: {
                                                okClick ( ) {
                                                    let d = this;
                                                    let input = $(".fm-new-file-input").ufo_inputs("Please enter a file name");

                                                    if ( !input.error ) {
                                                        $.fun().do({
                                                            name: "req",
                                                            param: {
                                                                data: {
                                                                    callback: "media_action",
                                                                    action: "create_file",
                                                                    address: $saver.fm.dir,
                                                                    filename: String(input.name),
                                                                    type: $(".fm-select-type-file").val(),
                                                                    prevent_ajax: "plugins"
                                                                }, dataType: "json",
                                                                done ( result ) {
                                                                    if ( result.status === 200 ) {
                                                                        $media.actions().open_folder({
                                                                            data: function ( value ) {
                                                                                return {address: $saver.fm.dir}[value]
                                                                            }
                                                                        });
                                                                        d.hide();return;
                                                                    }
                                                                    $.fun().do({
                                                                        name: "popup_message",
                                                                        param: {
                                                                            title: "",
                                                                            content: result.message
                                                                        }
                                                                    });
                                                                }, error ( xhr ) {
                                                                    $.ufo_dialog({title: $_this.lng("Connection error")});
                                                                }
                                                            }
                                                        });
                                                    }
                                                }
                                            }
                                        }
                                    });
                                }
                            }
                        }
                    });
                    $.fun().do({
                        name: "fm_register_option",
                        param: {
                            name: "create_folder",
                            callbacks: {
                                action: function () {
                                    $.ufo_dialog({
                                        title: $_this.lng("new folder"),
                                        content: `<input placeholder="${$_this.lng("folder name")}" name="name" class="form-control fm-new-folder-input" autocomplete="off">`,
                                        options: {
                                            okText: $_this.lng("create"),
                                            cancel: true,
                                            callbacks: {
                                                okClick ( ) {
                                                    let d = this;
                                                    let input = $(".fm-new-folder-input").ufo_inputs("Please enter a file name");

                                                    if ( !input.error ) {
                                                        $.fun().do({
                                                            name: "req",
                                                            param: {
                                                                data: {
                                                                    callback: "media_action",
                                                                    action: "create_folder",
                                                                    address: $saver.fm.dir,
                                                                    name: String(input.name),
                                                                    prevent_ajax: "plugins"
                                                                }, dataType: "json",
                                                                done ( result ) {
                                                                    if ( result.status === 200 ) {
                                                                        $media.actions().open_folder({
                                                                            data: function ( value ) {
                                                                                return {address: $saver.fm.dir}[value]
                                                                            }
                                                                        });
                                                                        d.hide();return;
                                                                    }
                                                                    $.fun().do({
                                                                        name: "popup_message",
                                                                        param: {
                                                                            title: "",
                                                                            content: result.message
                                                                        }
                                                                    });
                                                                }, error ( xhr ) {
                                                                    $.ufo_dialog({title: $_this.lng("Connection error")});
                                                                }
                                                            }
                                                        });
                                                    }
                                                }
                                            }
                                        }
                                    });
                                }
                            }
                        }
                    });
                    $.fun().do({
                        name: "fm_register_option",
                        param: {
                            name: "upload",
                            callbacks: {
                                action: $media.upload
                            }
                        }
                    });

                    $(".fm-btn-action").unbind().click(function () {
                        $media.do_action_toolbar($(this).data("action"));
                    });
                },
                do_action_toolbar ( name ) {
                    let find = false;
                    $.each($saver.fm.option, ( k, v ) => {
                        if ( name == v.name ) {
                            find = true;
                            v.callbacks.action(fm_select_items, {
                                selected: function (v) {
                                    select = v;
                                },
                                reset: function () {
                                    fm_select_items.folder = [];
                                    fm_select_items.file   = [];
                                }
                            });
                        }
                    });
                    if ( !find ) alert($_this.lng("Please register this option"));
                },
                do_action_toolbar_select ( ) {
                    $.each($saver.fm.option, ( k, v ) => {
                        if ( typeof v.callbacks.select === "function" ) {
                            v.callbacks.select(fm_select_items);
                        }
                    });
                },
                do_action_toolbar_unselect ( ) {
                    $.each($saver.fm.option, ( k, v ) => {
                        if ( typeof v.callbacks.unselect === "function" ) {
                            v.callbacks.unselect(fm_select_items);
                        }
                    });
                },
                upload ( ) {
                    uploader.setFolder($saver.fm.dir);
                    uploader.open();
                }
            }.init();
        },

        menu_list ( ) {
            $(".menu-category-container").ufo_scroll({type: "h"});

            let current = Number($(".menu-toolbar-btns").data("menu"));

            let options = {
                add (data = {}, done = ()=>{}) {
                    let link = null;
                    $.fun().do({
                        name: "req",
                        param: {
                            data: {
                                callback: "snippets",
                                file: "add-menu",
                                ...data
                            },
                            done (layout) {
                                $.ufo_dialog({
                                    title  : typeof data.sub !== "undefined" ? $_this.lng("Add submenu") : (
                                        typeof data.edit === "number" ? $_this.lng("Edit menu") : $_this.lng("Add menu")
                                    ),
                                    content: layout,
                                    options: {
                                        cancel: true,
                                        cancelText: $_this.lng("Cancel"),
                                        okText: typeof data.edit === "number" ? $_this.lng("Edit") : $_this.lng("Add"),
                                        callbacks: {
                                            okClick ( ) {
                                                const dialog = this;
                                                const $title = $(`.add-menu-input[name="title"]`);
                                                const $icon  = $(`.add-menu-input[name="icon"]`);
                                                const $link  = $(`.add-menu-input[name="link"]`);

                                                if (ufo.isNULL($title.val())) {
                                                    $.ufo_dialog({
                                                        title: $_this.lng("Please enter a title")
                                                    });
                                                    return false;
                                                }

                                                $.fun().do({
                                                    name: "req",
                                                    param: {
                                                        data: {
                                                            callback: "menu",
                                                            action  : typeof data.edit === "number" ? "edit" : "add",
                                                            title   : $title.val(),
                                                            icon    : $icon.val(),
                                                            link    : link ?? $link.val(),
                                                            ...data
                                                        },
                                                        dataType: "json",
                                                        done (result) {
                                                            if (result.status === 200) {
                                                                dialog.hide();
                                                                done(result.message, {
                                                                    title: $title.val(),
                                                                    icon : $icon.val(),
                                                                    link : $link.val()
                                                                });
                                                            } else {
                                                                $.ufo_dialog({
                                                                    title: result.message
                                                                })
                                                            }
                                                        },
                                                        error (xhr) {
                                                            $.ufo_dialog({
                                                                title: $_this.lng("Connection error")
                                                            })
                                                        }
                                                    }
                                                })
                                            }
                                        }
                                    },
                                    done ( ) {
                                        // Select page
                                        $(".select-menu-links").unbind("click").click(function ( ) {
                                            $.fun().do({
                                                name: "selectable_table",
                                                param: {
                                                    query: {
                                                        table: "pages",
                                                        id: "id",
                                                        fields: ["title"],
                                                        titles: [
                                                            $_this.lng("title")
                                                        ],
                                                        search: {
                                                            fields: ["title"]
                                                        }
                                                    },
                                                    limit: 1,
                                                    callback: items => {
                                                        items = items[0] ?? {};
                                                        link  = items.id ?? null;

                                                        $(this).html(items.title ?? $_this.lng("Select link"));

                                                        $(".delete-menu-link")[link !== null ? "removeAttr" : "attr"]("disabled", true);
                                                    }
                                                }
                                            })
                                        });

                                        $(".delete-menu-link").unbind("click").click(function ( ) {
                                            link = null; $(this).attr("disabled", true);
                                            $(`.select-menu-links`).html($_this.lng("Select link"));
                                        })
                                    }
                                });
                            },
                            error (xhr) {
                                $.ufo_dialog({
                                    content: $_this.lng("Connection error")
                                })
                            }
                        }
                    })
                },
                get (menu, sub, done = ()=>{}) {
                    $.fun().do({
                        name: "req",
                        param: {
                            data: {
                                prevent_ajax: "plugins",
                                callback: "menu",
                                action  : "get",
                                menu    : menu,
                                sub     : sub
                            },
                            dataType: "json",
                            done (result) {
                                if (result.status === 200) {
                                    done(result.message?.items)
                                } else {
                                    $.ufo_dialog({
                                        title: result.message
                                    })
                                }
                            },
                            error ( ) {
                                $.ufo_dialog({
                                    title: $_this.lng("Connection error")
                                })
                            }
                        }
                    })
                },
                delete (menu, done = ()=>{}) {
                    $.ufo_dialog({
                        title: $_this.lng("Are you sure you want to delete this item?"),
                        options: {
                            cancel: true,
                            cancelText: $_this.lng("Cancel"),
                            okText: $_this.lng("Delete"),
                            callbacks: {
                                okClick ( ) {
                                    $.fun().do({
                                        name: "req",
                                        param: {
                                            data: {
                                                prevent_ajax: "plugins",
                                                callback: "menu",
                                                action  : "delete",
                                                menu    : menu
                                            },
                                            dataType: "json",
                                            done (result) {
                                                if (result.status === 200) {
                                                    return done(result)
                                                }
                                                $.ufo_dialog({
                                                    title: result.message
                                                })
                                            },
                                            error (xhr) {
                                                $.ufo_dialog({
                                                    title: $_this.lng("Connection error")
                                                })
                                            }
                                        }
                                    });
                                    this.hide();
                                }
                            }
                        }
                    })
                },

                get_positions (done) {
                    $.fun().do({
                        name: "req",
                        param: {
                            data: {
                                callback: "menu",
                                action  : "positions"
                            },
                            dataType: "json",
                            done (result) {
                                if (result.status === 200) {
                                    done(result.message);
                                    return false;
                                }
                                $.ufo_dialog({
                                    title: result.message
                                })
                            },
                            error ( ) {
                                $.ufo_dialog({
                                    title: "Connection error"
                                })
                            }
                        }
                    })
                },
                positions (done = ()=>{}) {
                    this.get_positions(positions => {
                        $.ufo_dialog({
                            title  : $_this.lng("Positions"),
                            content: `
                                    <button class="btn btn-primary width-100-cent font-size-13px mt-10 mb-10 add-new-menu-position">${$_this.lng("Add a new position")}</button>
                                    <ul class="list-menu-positions"></ul>
                                `,
                            done ( ) {
                                const $content = $(`.du-dialog:last-child .dlg-content`);
                                const $list    = $(`.list-menu-positions`);

                                function list_actions (action) {
                                    return {
                                        delete () {
                                            $(`.delete-menu-position`).unbind("click").click(function ( ) {
                                                const position = $(this).data("id");
                                                $.ufo_dialog({
                                                    title: $_this.lng("Are you sure you want to delete this item?"),
                                                    options: {
                                                        cancel: true,
                                                        cancelText: $_this.lng("Cancel"),
                                                        okText: $_this.lng("Delete"),
                                                        callbacks: {
                                                            okClick () {
                                                                this.hide();
                                                                $.fun().do({
                                                                    name: "req",
                                                                    param: {
                                                                        data: {
                                                                            prevent_ajax: "plugins",
                                                                            callback: "menu",
                                                                            action  : "delete_position",
                                                                            position: position
                                                                        },
                                                                        dataType: "json",
                                                                        done (result) {
                                                                            if (result.status === 200) {
                                                                                $list.find(`[data-id="${position}"]`).remove();
                                                                                return false;
                                                                            }
                                                                            $.ufo_dialog({
                                                                                title: result.message
                                                                            })
                                                                        },
                                                                        error (xhr) {
                                                                            $.ufo_dialog({
                                                                                title: $_this.lng("Connection error")
                                                                            })
                                                                        }
                                                                    }
                                                                })
                                                            }
                                                        }
                                                    }
                                                })
                                            })
                                        }
                                    }[action]()
                                }

                                $.each(positions, (k, v) => {
                                    $list.append(`<li data-id="${k}">${v}<i class="ufo-icon-trash delete-menu-position" data-id="${k}"></i></li>`)
                                });

                                list_actions("delete");

                                $(".add-new-menu-position").click(function ( ) {
                                    $.ufo_dialog({
                                        title: $_this.lng("Add a new position"),
                                        content: `
                                                <input class="form-control name-position" placeholder="${$_this.lng("name")}">
                                            `,
                                        options: {
                                            cancel: true,
                                            okText: $_this.lng("Save"),
                                            callbacks: {
                                                okClick ( ) {
                                                    const dlg  = this;
                                                    const name = $(`.name-position`);

                                                    if (ufo.isNULL(name.val())) {
                                                        name.css({
                                                            "border-color": "red"
                                                        }).focus();
                                                        setTimeout(() => name.removeAttr("style"), 1500)
                                                        return false;
                                                    }

                                                    $.fun().do({
                                                        name: "req",
                                                        param: {
                                                            data: {
                                                                prevent_ajax: "plugins",
                                                                callback: "menu",
                                                                action  : "add_position",
                                                                name    : name.val()
                                                            },
                                                            dataType: "json",
                                                            done (result) {
                                                                if (result.status === 200) {
                                                                    dlg.hide();

                                                                    $list.prepend(`<li data-id="${result.message.id}">${result.message.name}<i class="ufo-icon-trash delete-menu-position" data-id="${result.message.id}"></i></li>`);
                                                                    list_actions("delete");

                                                                    return false;
                                                                }
                                                                $.ufo_dialog({
                                                                    title: result.message
                                                                })
                                                            },
                                                            error ( ) {
                                                                $.ufo_dialog({
                                                                    title: $_this.lng("Connection error")
                                                                })
                                                            }
                                                        }
                                                    })
                                                }
                                            }
                                        }
                                    })
                                });
                            }
                        });
                    })
                },
                change_position (menu, done = ()=>{}) {
                    this.get_positions(positions => {
                        $.ufo_dialog({
                            title: $_this.lng("Changing Position"),
                            content: function () {
                                let array = [];
                                $.each(positions, (k, v) => {
                                    array.push({
                                        name: v,
                                        id  : k
                                    });
                                });
                                return array;
                            }(),
                            options: {
                                allowSearch: true,
                                selection: true,
                                textField: "name",
                                valueField: "id",
                                callbacks: {
                                    itemSelect: function (e, i) {
                                        $.fun().do({
                                            name: "req",
                                            param: {
                                                data: {
                                                    callback: "menu",
                                                    action  : "change_position",
                                                    menu    : menu,
                                                    position: i.id
                                                },
                                                dataType: "json",
                                                done (result) {
                                                    $.ufo_dialog({
                                                        title: result.message
                                                    });
                                                    done(i, result);
                                                },
                                                error ( ) {
                                                    $.ufo_dialog({
                                                        title: $_this.lng("Connection error")
                                                    })
                                                }
                                            }
                                        });
                                    }
                                }
                            }
                        });
                    })
                },

                select_menu (id) {
                    const $selector = $(".select-menu");
                    const $active   = $selector.find(`span[data-id="${id}"]`);

                    $selector.find(`span`).attr("hidden", true);
                    $active.removeAttr("hidden");

                    // Load submenus
                    options.get(undefined, id, items => {
                        $(`.menu-e-row`).html(items);
                        if ($(`.menu-e-row li`).length) {
                            $(`.empty-submenu-list`).remove()
                        }
                        options.showEmptyList();
                        context(`.menu-e-row`);
                    });

                    // Active toolbar buttons
                    $(".menu-toolbar-btns").attr("data-id", id);

                    $(`.menu-toolbar-btns[data-action="change_position"] span`).html(
                        $active.data("position")
                    );

                    current = id;
                },

                showEmptyList (list) {
                    list = $(list ?? ".menu-e-row");
                    if (list.length && !list.find("li").length) {
                        $(".empty-submenu-list").remove();
                        $(`<h4 class="empty-submenu-list width-100-cent text-center font-size-15px mt-30">${(
                            !$(".select-menu span").length ? $_this.lng("Create the first menu") : $_this.lng("Create your first submenu")
                        )}</h4>`).insertAfter(list);
                    }
                }
            };
            let toolbar_actions = {
                add_submenu (menu, list) {
                    const btn = $(this);
                    options.add({
                        sub: menu ?? current
                    }, result => {
                        $(list ?? `.menu-e-row`).append(result.item);
                        $(`.empty-submenu-list`).remove();
                        context();
                    })
                },
                change_position ( ) {
                    options.change_position(current, (position, result) => {
                        console.log(current)
                        const $menu = $(`.select-menu [data-id="${current}"]`);

                        $menu.attr("data-position", position.name);
                        $menu.attr("data-id-position", position.id);
                        $menu.data("position", position.name);
                        $menu.data("id-position", position.id);

                        $(`.menu-toolbar-btns[data-action="change_position"] span`).html(
                            position.name
                        );
                    })
                },
                edit ( ) {
                    options.add({
                        edit: current
                    }, (result, inputs) => {
                        $(`.select-menu [data-id="${current}"]`).html(inputs.title)
                    })
                },
                delete ( ) {
                    options.delete(current, result => {
                        $(`.select-menu [data-id="${current}"]`).remove();

                        let $next = $($(`.select-menu span[data-id]`).get(0));

                        $next.removeAttr("hidden");
                        $(`.menu-e-row`).empty();

                        if (!$(`.select-menu span`).length) {
                            options.showEmptyList();
                            $(`.select-menu`).prepend(`<span class="empty">${$_this.lng("Create the first menu")}</span>`);
                            $(`.menu-toolbar-btns`).attr("disabled", true).attr("data-menu", 0);
                            current = 0;
                        } else {
                            $(`.menu-toolbar-btns`).attr("disabled", false).attr("data-menu", $next.data("menu"));
                            options.select_menu(current = Number($next.data("id")));
                        }
                    })
                }
            };
            let sortable = list => {
                list = list ?? `.menu-e-row`;
                $.ufo_sorting(list, {
                    before_swipe(e) {
                        e.preventDefault();
                        context_methods.close();
                    },
                    reorder(e) {
                        const items = [];
                        $(list).find("li.item").each(function () {
                            items.push($(this).data("menu"));
                        });
                        $.fun().do({
                            name: "req",
                            param: {
                                data: {
                                    prevent_ajax: "plugins",
                                    callback: "menu",
                                    action: "sort",
                                    items: items
                                },
                                dataType: "json",
                                done(result) {
                                    if (result.status !== 200)
                                        $.ufo_dialog({
                                            title: result.message
                                        })
                                },
                                error() {
                                    $.ufo_dialog({
                                        title: $_this.lng("Connection error")
                                    })
                                }
                            }
                        })
                    }
                });
            }
            let context_methods = {
                close ( ) {$(`.context-e-menu`).slideUp()},
                submenu ( ) {
                    const menu = Number($(this).data("menu"));

                    context_methods.close();
                    options.get(undefined, menu, menus => {
                        $.ufo_dialog({
                            title: $_this.lng("Submenus"),
                            content: `
                                    <div style="min-height: 200px">
                                        <button class="btn btn-primary add-submenu font-size-14px width-100-cent mt-15" data-menu="${menu}">${$_this.lng("Add submenu")}</button>
                                        <ul class="menu-e-row submenu-list mt-10">${menus}</ul>
                                    </div>
                                `,
                            done ( ) {
                                const list = $(".du-dialog:last-child").find(`.menu-e-row.submenu-list`);

                                options.showEmptyList(list);
                                $(`.add-submenu`).unbind("click").click(function ( ) {
                                    toolbar_actions["add_submenu"].bind(this)(
                                        Number($(this).data("menu")),
                                        $(".du-dialog:last-child").find(`.menu-e-row.submenu-list`)
                                    )
                                });

                                context(list);
                                sortable(list);
                            }
                        })
                    });
                },
                edit ( ) {
                    const menu = Number($(this).data("menu"));

                    context_methods.close();
                    options.add({
                        edit: menu
                    }, (result, inputs) => {
                        $(`[data-menu="${menu}"] .title`).html(inputs.title)
                    })
                },
                remove ( ) {
                    const menu  = Number($(this).data("menu"));
                    const $menu = $(`.item[data-menu="${menu}"]`);

                    context_methods.close();
                    options.delete(menu, result => {
                        const list = $(".du-dialog:last-child").find(`.menu-e-row.submenu-list`);

                        $menu.remove();

                        options.showEmptyList(list);
                        options.showEmptyList();
                    })
                }
            };
            let context = list => $(list ?? `.menu-e-row`).find(`.menu-show-context`).unbind("click").click(function () {
                const context = $(this).next();
                if (context.hasClass("active")) {
                    context.slideUp().removeClass("active");
                } else {
                    $(`.context-e-menu`).slideUp().removeClass("active");
                    context.slideDown().addClass("active");
                    context.find(`[data-action]`).unbind("click").click(function () {
                        context_methods[$(this).data("action")].bind(this)()
                    })
                }
            });

            context();
            sortable();

            $(".menu-add").unbind("click").click(function () {
                options.add({}, (result, inputs) => {
                    const selector = $(`.select-menu`);

                    selector.find("span.empty").remove();
                    selector.find("span").attr("hidden", true);
                    selector.append(`<span data-id="${result.id}" data-position="${$_this.lng("Every where")}" data-id-position="every-where">${inputs.title}</span>`);

                    $(".menu-toolbar-btns").removeAttr("disabled").attr("data-menu", result.id);
                    $(`.menu-toolbar-btns[data-action="change_position"] span`).html($_this.lng("Every where"));

                    $(`.menu-e-row`).empty();

                    options.showEmptyList();

                    current = result.id;
                })
            });

            $(".select-menu").unbind("click").click(function () {
                const $selector = $(this);

                if ($selector.find(`span[data-id]`).length) {
                    $.ufo_dialog({
                        title: $_this.lng("Select menu"),
                        content: function () {
                            let array = [];
                            $selector.find(`span[data-id]`).each(function () {
                                array.push({
                                    name: $(this).text(),
                                    id  : $(this).data("id")
                                });
                            });
                            return array;
                        }(),
                        options: {
                            selection: true,
                            textField: "name",
                            valueField: "id",
                            callbacks: {
                                itemSelect: function (e, i) {
                                    options.select_menu(i.id)
                                }
                            }
                        }
                    });
                } else {
                    $(".menu-add").click()
                }
            });

            $(".menu-positions").unbind("click").click(function () {
                options.positions()
            });

            $(".menu-toolbar-btns").unbind("click").click(function () {
                toolbar_actions[$(this).data("action")].bind(this)();
            });
        },

        plugins ( ) {
            function response ( ) {
                $(`.plugin-info-list-properties`).not(".without-auto-width").width($(`.side-plugin-info`).width() + "px");
            }
            window.onresize = response;

            $(".plugin-show-info").unbind().click(function () {
                const target = $(this);
                $.fun().do({
                    name: "req",
                    param: {
                        data: {
                            callback: "plugin",
                            action: "detail",
                            plugin: target.data("plugin")
                        },
                        done ( result ) {
                            {
                                $(`body`).prepend(result);
                                response();
                                $(`.close`).click(function () {
                                    $(`.ufo-popup-modal-layer`).remove();
                                });
                            }
                            {
                                $(".ufo-uninstall-plugin").unbind().click(function () {
                                    const btn = $(this);
                                    $.ufo_dialog({
                                        title: $_this.lng("Warning"),
                                        content: $_this.lng("Are you sure you want to delete this plugin?"),
                                        options: {
                                            cancel: true,
                                            okText: $_this.lng("Delete"),
                                            callbacks: {
                                                okClick ( ) {
                                                    this.hide();
                                                    $.fun().do({
                                                        name: "req",
                                                        param: {
                                                            data: {
                                                                callback: "plugin",
                                                                action: "uninstall",
                                                                plugin: btn.data("id")
                                                            },
                                                            dataType: "json",
                                                            done ( result ) {
                                                                if ( result.status ) {
                                                                    $(".plugin-detail-modal").remove();
                                                                    $(`.plugins-column[data-plugin="${btn.data("id")}"]`).remove();
                                                                    return false;
                                                                }
                                                                $.ufo_dialog({content: result.message});
                                                            },
                                                            error ( ) {
                                                                $.ufo_dialog({content: $_this.lng("Connection error")})
                                                            }
                                                        }
                                                    })
                                                }
                                            }
                                        }
                                    });
                                });
                            }
                            {
                                if ( !$(".plugin-info-accordion").length ) {
                                    $(".ufo-pt-title-permissions").remove();
                                    $(`.ufo-pt-permissions`).empty();
                                }
                            }
                        },
                        error ( xhr ) {
                            $.ufo_dialog({
                                content: $_this.lng("Connection error")
                            })
                        }
                    }
                });
            });
            $(".shutdown-plugin").unbind().bind("input", function () {
                const $switch = $(this);

                $.fun().do({
                    name: "req",
                    param: {
                        data: {
                            callback: "plugin",
                            action: !$switch.is(":checked") ? "shutdown" : "active",
                            plugin: $switch.data("plugin")
                        },
                        dataType: "json",
                        loader ( ) {
                            $switch.hide();
                            $switch.parent().append(`<i class="ufo-icon-circle-notch rotating loader font-size-25px mt-10" data-plugin="${$switch.data("plugin")}" style="width: 35px;height: 25px"></i>`);
                        },
                        done ( result ) {
                            $switch.parent().find(`i.loader[data-plugin="${$switch.data("plugin")}"]`).remove();
                            $switch.show();
                            $.fun().do({
                                name: "popup_message",
                                param: {
                                    title: "",
                                    content: result.message
                                }
                            });
                        },
                        error ( xhr ) {
                            $switch.parent().find(`i.loader[data-plugin="${$switch.data("plugin")}"]`).remove();
                            $switch.show();
                            $.fun().do({
                                name: "popup_message",
                                param: {
                                    title: "",
                                    content: $_this.lng("Connection error")
                                }
                            });
                        }
                    }
                });
            });
            $(".ufo-add-new-plugin").unbind().click(function () {
                $_this.actionTPT("upload-plugin", this);
            });

            {
                $_this.actionTPT("process-update", "plugin");
            }
        },

        templates ( ) {
            function response ( ) {
                $(`.plugin-info-list-properties`).not(".without-auto-width").width($(`.side-plugin-info`).width() + "px");
            }
            window.onresize = response;

            $(".ufo-add-new-template").unbind().click(function () {
                $_this.actionTPT("upload-template", this);
            });
            $(".ufo-template-show-info").unbind().click(function () {
                const target = $(this);
                $.fun().do({
                    name: "req",
                    param: {
                        data: {
                            callback: "template",
                            action: "detail",
                            template: target.data("template")
                        },
                        done ( result ) {
                            {
                                $(`body`).prepend(result);
                                response();

                                $(`.close`).click(function () {
                                    $(`.ufo-popup-modal-layer`).remove();
                                });
                            }
                            {
                                $(".ufo-template-show-preview").unbind().click(function () {
                                    const btn = $(this);
                                    $.fun().do({
                                        name: "req",
                                        param: {
                                            data: {
                                                callback: "template",
                                                action: "preview",
                                                template: btn.data("id")
                                            },
                                            dataType: "json",
                                            done ( result ) {
                                                if ( result.status === 200 ) {
                                                    $.fun().do({
                                                        name: "open_window",
                                                        param: {
                                                            href: result.message,
                                                            width: 800,
                                                            height: 500
                                                        }
                                                    })
                                                } else {
                                                    $.ufo_dialog({content: result.message})
                                                }
                                            },
                                            error ( ) {
                                                $.ufo_dialog({content: $_this.lng("Connection error")})
                                            }
                                        }
                                    })
                                });
                                $(".ufo-template-delete").unbind().click(function () {
                                    const btn = $(this);
                                    $.ufo_dialog({
                                        title: $_this.lng("Warning"),
                                        content: $_this.lng("Are you sure you want to delete this template?"),
                                        options: {
                                            cancel: true,
                                            okText: $_this.lng("Delete"),
                                            callbacks: {
                                                okClick ( ) {
                                                    this.hide();
                                                    $.fun().do({
                                                        name: "req",
                                                        param: {
                                                            data: {
                                                                callback: "template",
                                                                action: "delete",
                                                                template: btn.data("id")
                                                            },
                                                            dataType: "json",
                                                            done ( result ) {
                                                                if ( result.status ) {
                                                                    $(".plugin-detail-modal").remove();
                                                                    $(`.plugins-column[data-template="${btn.data("id")}"]`).remove();
                                                                    return false;
                                                                }
                                                                $.ufo_dialog({content: result.message});
                                                            },
                                                            error ( ) {
                                                                $.ufo_dialog({content: $_this.lng("Connection error")})
                                                            }
                                                        }
                                                    })
                                                }
                                            }
                                        }
                                    });
                                });
                            }
                            {
                                if ( !$(".plugin-info-accordion").length ) {
                                    $(".ufo-pt-title-permissions").remove();
                                    $(`.ufo-pt-permissions`).empty();
                                }
                            }
                        },
                        error ( xhr ) {
                            $.ufo_dialog({
                                content: $_this.lng("Connection error")
                            })
                        }
                    }
                });
            });
            $(".shutdown-template").unbind().bind("input", function () {
                const $switch = $(this);

                if ( $(this).is(':checked') ) {
                    let select = false;
                    $.ufo_dialog({
                        content: [
                            { name: $_this.lng("Default"), id: "set" },
                            { name: $_this.lng("Multiple template"), id: "multi" }
                        ],
                        options: {
                            selection: true,
                            textField: 'name',
                            valueField: 'id',
                            callbacks: {
                                itemSelect: function (e, i) {
                                    select = true;
                                    $.fun().do({
                                        name: "req",
                                        param: {
                                            data: {
                                                callback: "template",
                                                action: "active",
                                                mode: i.id,
                                                template: $switch.data("template")
                                            },
                                            dataType: "json",
                                            done ( result ) {
                                                $switch.prop('checked', true);
                                                $.ufo_dialog({
                                                    content: result.message
                                                })
                                            },
                                            error ( xhr ) {
                                                $switch.prop('checked', false);
                                                $.ufo_dialog({
                                                    content: $_this.lng("Connection error")
                                                })
                                            }
                                        }
                                    })
                                }
                            }
                        },
                        done: function () {
                            let selectItem = false, wrp = false;
                            $(".dlg-select-item").unbind().click(function () {
                                selectItem = true
                            });
                            $(".dlg-wrapper").unbind().click(function () {
                                wrp = true;
                            });
                            $(".du-dialog").unbind().click(function () {
                                if ( !selectItem && !wrp ) {
                                    $switch.prop('checked', false);
                                } else {
                                    wrp = false;
                                }
                            });
                        }
                    })
                } else {
                    $.fun().do({
                        name: "req",
                        param: {
                            data: {
                                callback: "template",
                                action: "shutdown",
                                template: $switch.data("template")
                            },
                            dataType: "json",
                            done ( result ) {
                                $.ufo_dialog({
                                    content: result.message
                                })
                            },
                            error ( xhr ) {
                                $.ufo_dialog({
                                    content: $_this.lng("Connection error")
                                })
                            }
                        }
                    })
                }
            });

            {
                $_this.actionTPT("process-update", "template");
            }
        },

        pages ( ) {
            if ($saver.page !== "pages") return false;

            let $self_page, $type_show_page = "page";
            let last_category_paging = 1;

            return {
                init ( ) {
                    $self_page = this;

                    setTimeout(() => {
                        $self_page.category();
                        $self_page.pages();
                    }, 250)
                },
                removePage ( selections ) {
                    $.fun().do({
                        name: "req",
                        param: {
                            data: {
                                callback: "pages",
                                action  : "delete",
                                pages   : selections
                            },
                            dataType: "json",
                            done ( result ) {
                                if ( result.status === 200 ) {
                                    if ( Array.isArray(selections) ) {
                                        selections.map(i => {
                                            $(`.ufo-table-pages tbody tr[data-id="${i}"]`).remove()
                                        });
                                    } else {
                                        $(`.ufo-table-pages tbody tr[data-id="${selections}"]`).remove()
                                    }
                                } else {
                                    $.ufo_dialog({content: result.message});
                                }
                            },
                            error ( error ) {
                                $.ufo_dialog({content: $_this.lng("Connection error")});
                            }
                        }
                    })
                },
                pages ( ) {
                    const table = $(".ufo-table-pages");

                    table.find(`input[type="checkbox"][data-id="all"]`).unbind().click(function () {
                        if ($(this).hasClass("checked")) {
                            table.find(`tbody input[type="checkbox"]:checked`).click();
                            $(this).removeClass("checked");
                        } else {
                            $(this).addClass("checked");
                            table.find(`tbody input[type="checkbox"]`).each(function () {
                                if (!$(this).is(":checked")) {
                                    $(this).addClass("checked").click();
                                }
                            });
                        }
                    });

                    $.fun().do({
                        name: "paging",
                        param: {
                            name: "page-table-paging",
                            method: page => {
                                load_page({to_page: page});
                            }
                        }
                    });
                    $.fun().do({
                        name: "pages",
                        param: {
                            action: "add_action",
                            data: {
                                title: $_this.lng("delete selected items"),
                                val: "delete",
                                name: "delete",
                                method: function (selections) {
                                    if (selections.length === 0) {
                                        $_this.popup_message({content: $_this.lng("first select the desired page")});
                                    } else {
                                        $.ufo_dialog({
                                            title: $_this.lng("remove pages").toString(),
                                            content: function(){
                                                let join = '';
                                                $.each(selections, (k, v) => {
                                                    join += "," + $(`.ufo-table-pages tbody tr[data-id="${v}"] span.title`).text();
                                                });
                                                return join = join.substring(1);
                                            }(),
                                            options: {
                                                cancel: true,
                                                okText: $_this.lng("delete"),
                                                cancelText: $_this.lng("cancel"),
                                                callbacks: {
                                                    okClick ( ) {
                                                        this.hide();
                                                        $self_page.removePage(selections);
                                                    }
                                                }
                                            }
                                        });
                                    }
                                    $(`.pages-action-select option`).removeAttr("selected");
                                    $(`.pages-action-select .d-select`).attr("selected", true);
                                }
                            }
                        }
                    });
                    $.fun().do({
                        name: "pages",
                        param: {
                            action: "add_action",
                            data: {
                                title: $_this.lng("Change status"),
                                val: "change-status",
                                name: "change-status",
                                method: function (selections) {
                                    if (selections.length === 0) {
                                        $_this.popup_message({content: $_this.lng("first select the desired page")});
                                    } else {
                                        $.ufo_dialog({
                                            title: $_this.lng("Change status").toString(),
                                            content: function(){
                                                let join = '';
                                                $.each(selections, (k, v) => {
                                                    join += ", " + $(`.ufo-table-pages tbody tr[data-id="${v}"] span.title`).text();
                                                });
                                                return join = join.substring(1);
                                            }(),
                                            options: {
                                                cancel: true,
                                                okText: $_this.lng("Change"),
                                                cancelText: $_this.lng("cancel"),
                                                callbacks: {
                                                    okClick ( ) {
                                                        this.hide();
                                                        $.ufo_dialog({
                                                            title: $_this.lng("Select"),
                                                            content: [{
                                                                name: $_this.lng("draft"),
                                                                id: 0
                                                            },{
                                                                name: $_this.lng("published"),
                                                                id: 1
                                                            },{
                                                                name: $_this.lng("hidden"),
                                                                id: 2
                                                            }],
                                                            options: {
                                                                selection: true,
                                                                textField: 'name',
                                                                valueField: 'id',
                                                                callbacks: {
                                                                    itemSelect: function (e, i) {
                                                                        $.fun().do({
                                                                            name: "req",
                                                                            param: {
                                                                                data: {
                                                                                    callback: "pages",
                                                                                    action: "status",
                                                                                    pages: selections,
                                                                                    status: i.id
                                                                                },
                                                                                dataType: "json",
                                                                                done ( result ) {
                                                                                    if ( result.status === 200 ) {
                                                                                        load_page({type_page: "all"});
                                                                                    } else {
                                                                                        $.ufo_dialog({content: result.message})
                                                                                    }
                                                                                },
                                                                                error ( xhr ) {
                                                                                    $.ufo_dialog({content: $_this.lng("Connection error")})
                                                                                }
                                                                            }
                                                                        })
                                                                    }
                                                                }
                                                            }
                                                        });
                                                    }
                                                }
                                            }
                                        });
                                    }
                                    $(`.pages-action-select option`).removeAttr("selected");
                                    $(`.pages-action-select .d-select`).attr("selected", true);
                                }
                            }
                        }
                    });

                    $_this.search({
                        input: ".search-pages",
                        container: ".ufo-table-pages tbody",
                        items: ".ufo-table-pages tbody tr",
                        prop: ".title"
                    });
                    $(`.btn-search-pages`).unbind().click(function () {
                        const val = $(`.search-pages`).val().toString();
                        if ( val.length === 0 ) {
                            load_page({to_page: 1});
                        } else {
                            load_page({search: val});
                        }
                    });

                    $(".ufo-pages-scroll").ufo_scroll({type: "h"});
                    $(".option-pages-toolbar-wrp").ufo_scroll({type: "h"});

                    $(`.pages-action-select`).unbind().bind("input", function () {
                        const selections = [];
                        table.find(`tbody input[type="checkbox"]`).each(function () {
                            if ($(this).is(":checked")) selections.push($(this).data("id"));
                        });
                        return {
                            options() {}, ...$saver.pages.actions
                        }[$(this).val()](selections);
                    });
                    $(`.option-pages-toolbar[data-page]`).unbind().click(function () {
                        $(`.option-pages-toolbar[data-page]`).removeClass("active");
                        $(this).addClass("active");
                        load_page({
                            type_page: $(this).data("action")
                        });
                    });

                    $(`.remove-page`).unbind().click(function () {
                        const $remove = $(this);
                        $.ufo_dialog({
                            content: $_this.lng("Are you sure you want to delete this page?"),
                            options: {
                                cancel: true,
                                okText: $_this.lng("yes"),
                                callbacks: {
                                    okClick ( ) {
                                        $self_page.removePage($remove.data("page"));
                                        this.hide()
                                    }
                                }
                            }
                        })
                    });
                    $(".create-new-page").unbind().click(function () {
                        location.href = admin_web_url + "ufo-editor?type=" + $(this).data("type");
                    });
                    $(".change-type-show").unbind().click(function () {
                        if ( $(this).data("type") === "page" || $(this).data("type") === "article" ) {
                            $type_show_page = $(this).data("type");
                            $(this).attr("data-type", $(this).data("type"));
                            load_page();
                        }
                    });

                    function load_page ( data = {} ) {
                        $.fun().do({
                            name: "req",
                            param: {
                                data: {
                                    callback: "load_page",
                                    page: "snippets/pages",
                                    type: $type_show_page,
                                    ...data
                                },
                                done ( result ) {
                                    $(`.ufo-tabs-pages[data-ufo-tab="pages"]`).html(result);
                                    $self_page.pages();
                                },
                                error ( xhr ) {
                                    $(`.ufo-tabs-pages[data-ufo-tab="pages"]`).html($_this.lng("Connection error"));
                                }
                            }
                        });
                    }
                },
                category ( ) {
                    /**
                     * Pagination
                     */
                    $.fun().do({
                        name: "paging",
                        param: {
                            name: "category-table-paging",
                            method: page => {
                                last_category_paging = page;
                                load_category({to_page: last_category_paging});
                            }
                        }
                    });

                    /**
                     * Search
                     */
                    $_this.search({
                        input: ".ufo-search-category",
                        container: ".ufo-table-category tbody",
                        items: ".ufo-table-category tbody tr",
                        prop: ".title"
                    });
                    $(`.ufo-btn-search-category`).unbind().click(function () {
                        const val = $(`.ufo-search-category`).val().toString();
                        if ( val.length === 0 ) {
                            load_category({to_page: 1});
                        } else {
                            load_category({search: val});
                        }
                    });

                    /**
                     * Create
                     */
                    let select_photo = "";
                    $(".ufo-create-category").unbind().click(function () {
                        add_edit(true)
                    });

                    /**
                     * Remove
                     */
                    $(".remove-category").unbind().click(function () {
                        const removeBTN = $(this);
                        $.ufo_dialog({
                            title: $_this.lng("Are you sure you want to delete this item?"),
                            options: {
                                cancel: true,
                                okText: $_this.lng("delete"),
                                callbacks: {
                                    okClick ( ) {
                                        const dialog = this;
                                        $.fun().do({
                                            name : "req",
                                            param: {
                                                data: {
                                                    callback: "delete_category",
                                                    category: removeBTN.data("cat")
                                                },
                                                dataType: "json",
                                                done ( result ) {
                                                    if ( result.status === 200 ) {
                                                        dialog.hide();
                                                        load_category();
                                                        return false;
                                                    }
                                                    $.ufo_dialog({title: result.message})
                                                },
                                                error ( xhr ) {
                                                    $.ufo_dialog({title: $_this.lng("Connection error")})
                                                }
                                            }
                                        })
                                    }
                                }
                            }
                        })
                    });

                    /**
                     * Edit
                     */
                    $(".edit-category").unbind().click(function () {
                        const editBTN = $(this);
                        $.fun().do({
                            name: "req",
                            param: {
                                data: {
                                    callback: "get_category",
                                    category: editBTN.data("cat")
                                },
                                dataType: "json",
                                done ( result ) {
                                    if ( typeof result.id !== "undefined" ) {
                                        add_edit(false, result)
                                    } else {
                                        $.ufo_dialog({title: $_this.lng("System error")})
                                    }
                                },
                                error ( xhr ) {
                                    $.ufo_dialog({title: $_this.lng("Connection error")})
                                }
                            }
                        })
                    });

                    /**
                     * Reload Category
                     */
                    function load_category ( data = {} ) {
                        $.fun().do({
                            name: "req",
                            param: {
                                data: {
                                    callback: "load_page",
                                    page: "snippets/category",
                                    to_page: last_category_paging,
                                    ...data
                                },
                                done ( result ) {
                                    $(`.ufo-tabs-pages[data-ufo-tab="category"]`).html(result);
                                    $self_page.category();
                                },
                                error ( xhr ) {
                                    $(`.ufo-tabs-pages[data-ufo-tab="category"]`).html($_this.lng("Connection error"));
                                }
                            }
                        });
                    }

                    /**
                     * Add & Edit - Category
                     */
                    function add_edit ( create, data = {} ) {
                        select_photo = typeof data.photo !== "undefined" ? (ufo.isNULL(data.photo) ? $(".ufo-create-category").data("img") : data.photo) : $(".ufo-create-category").data("img");
                        $.ufo_dialog({
                            title: data.title ?? $_this.lng("New category"),
                            content: `
                                    <div class="select-img-category"><img class="cursor-pointer" src="${select_photo}"></div>
                                    <input class="form-control mt-10 ufo-title-category" placeholder="${$_this.lng("title")}" value="${data.title ?? ""}">
                                    <input class="form-control mt-10 ufo-link-category" placeholder="${$_this.lng("link")}" value="${data.link ?? ""}">
                                    <textarea class="form-control ufo-desc-category ufo-prevent-resize p-5px mt-10 height-100px" placeholder="${$_this.lng("description")}">${data.description ?? ""}</textarea>
                                `,
                            options: {
                                cancel: true,
                                okText: $_this.lng("Submit"),
                                callbacks: {
                                    okClick ( ) {
                                        const dialog = this;
                                        const $title = $(`input.ufo-title-category`);
                                        const $link  = $(`input.ufo-link-category`);
                                        const $desc  = $(`textarea.ufo-desc-category`);

                                        if ( !$_this.detectVoid($title.val(), null) ) {
                                            $.ufo_dialog({title: $_this.lng("Please enter a title")});
                                            return false;
                                        }
                                        if ( !$_this.detectVoid($link.val(), null) ) {
                                            $.ufo_dialog({title: $_this.lng("Please enter a link")});
                                            return false;
                                        }

                                        $.fun().do({
                                            name: "req",
                                            param: {
                                                data: {
                                                    callback: create ? "create_category" : "update_category",
                                                    photo: select_photo,
                                                    title: $title.val(),
                                                    link : $link.val(),
                                                    description : $desc.val(),
                                                    category: data.id ?? 0
                                                },
                                                dataType: "json",
                                                done ( result ) {
                                                    if ( result.status === 200 ) {
                                                        dialog.hide();
                                                        load_category();
                                                        return false;
                                                    }
                                                    $.ufo_dialog({title: result.message})
                                                },
                                                error ( xhr ) {
                                                    $.ufo_dialog({title: $_this.lng("Connection error")})
                                                }
                                            }
                                        });
                                    }
                                }
                            },
                            done ( ) {
                                const $photo = $(`.select-img-category`);
                                const $title = $(`input.ufo-title-category`);
                                const $link  = $(`input.ufo-link-category`);

                                $title.unbind().bind("input", function () {
                                    $link.val($(this).val().replaceAll(" ", "-"))
                                });
                                $link.unbind().bind("input", function () {
                                    $link.val($(this).val().replaceAll(" ", "-"))
                                });

                                $photo.unbind().click(function () {
                                    $.fun().do({
                                        name: "media",
                                        param: {
                                            id: "ufo-photo-category",
                                            reset: true,
                                            show_label: true,
                                            limit: 1,
                                            types: "img",
                                            loader ( ) {},
                                            done ( ) {},
                                            result ( result ) {
                                                select_photo = result[0];
                                                $photo.find("img").attr("src", select_photo)
                                            }
                                        }
                                    });
                                });
                            }
                        })
                    }
                }
            }.init();
        },

        comments ( data = {} ) {
            /**
             * Pagination
             */
            $.fun().do({
                name: "paging",
                param: {
                    name: "comments-table-paging",
                    method: page => {
                        load_comments({
                            to_page: page
                        });
                    }
                }
            });

            /**
             * Select mode
             */
            $(".ufo-select-mode-comments").unbind().bind("input", function () {
                load_comments({
                    to_page: 1,
                    mode: $(`.ufo-select-mode-comments option[value="${$(this).val()}"]`).data("accept"),
                    accept: $(this).val()
                })
            });

            /**
             * Accept comment
             */
            $(".accept-comment").unbind().click(function () {
                const accept = $(this);
                $.fun().do({
                    name: "req",
                    param: {
                        data: {
                            callback: "comment",
                            action  : "accept",
                            comment : accept.data("comment")
                        }, dataType: "json", done ( result ) {
                            if ( result.status === 200 ) {
                                load_comments(); return false
                            }
                            $.ufo_dialog({title: result.message})
                        }, error ( xhr ) {
                            $.ufo_dialog({
                                title: $_this.lng("Connection error")
                            })
                        }
                    }
                });
            });

            /**
             * Reply comment
             */
            $(".reply-comment").unbind().click(function () {
                const reply = $(this);
                $.ufo_dialog({
                    title: $_this.lng("Reply"),
                    content: `<textarea class="form-control ufo-comment-admin-reply ufo-resize-v" placeholder="${$_this.lng("Type something...")}"></textarea>`,
                    options: {
                        cancel: true,
                        okText: $_this.lng("Submit"),
                        callbacks: {
                            okClick ( ) {
                                const dialog = this;
                                const text   = $(".ufo-comment-admin-reply").val();
                                if ( $_this.detectVoid(text, $_this.lng(text)) ) {
                                    $.fun().do({
                                        name: "req",
                                        param: {
                                            data: {
                                                callback: "comment",
                                                action  : "reply",
                                                comment : reply.data("comment"),
                                                text    : text
                                            }, dataType: "json", done ( result ) {
                                                if ( result.status === 200 ) {dialog.hide()}
                                                $.ufo_dialog({title: result.message});
                                            }, error ( xhr ) {
                                                $.ufo_dialog({
                                                    title: $_this.lng("Connection error")
                                                })
                                            }
                                        }
                                    });
                                }
                            }
                        }
                    }
                });
            });

            /**
             * Remove comment
             */
            $(".remove-comment").unbind().click(function () {
                const remover = $(this);
                $.ufo_dialog({
                    title: $_this.lng("Are you sure you want to delete this comment?"),
                    options: {
                        cancel: true,
                        okText: $_this.lng("yes"),
                        callbacks: {
                            okClick ( ) {
                                this.hide();
                                $.fun().do({
                                    name: "req",
                                    param: {
                                        data: {
                                            callback: "comment",
                                            action: "remove",
                                            comment: remover.data("comment")
                                        }, dataType: "json", done ( result ) {
                                            if ( result.status === 200 ) {
                                                $(`.ufo-table-comments tr[data-id="${remover.data("comment")}"]`).remove();
                                                return false
                                            }
                                            $.ufo_dialog({
                                                title: result.message
                                            })
                                        }, error ( xhr ) {
                                            $.ufo_dialog({
                                                title: $_this.lng("Connection error")
                                            })
                                        }
                                    }
                                })
                            }
                        }
                    }
                })
            });

            /**
             * Info comment
             */
            $(".info-comment").unbind().click(function () {
                const info = $(this);
                $.fun().do({
                    name: "req",
                    param: {
                        data: {
                            callback: "comment",
                            action  : "info",
                            comment : info.data("comment")
                        }, dataType: "json", done ( result ) {
                            if ( result.status === 200 ) {
                                try {
                                    result = result["message"];
                                    if ( result.length !== 0 ) {
                                        let member = result["member"] ?? result["admin"] ?? result["guest"] ?? result;

                                        if ( typeof member["name"] === "undefined" || typeof member !== "object" ) {
                                            $.ufo_dialog({
                                                title: $_this.lng("The information is incomplete")
                                            });
                                            return false;
                                        }

                                        let name    = member["name"] ?? "";
                                        let email   = member["email"] ?? "";
                                        let comment = result["comment"];
                                        let page    = typeof result["page"]["link"] !== "undefined" ? result["page"]["link"] : false;
                                        if ( page ) { page = `<a href="${info.data("page-link")}" target="_blank">${info.data("title-page")}</a>` }

                                        $.ufo_dialog({
                                            content: `${page}
                                                      <label class="mt-15 mb-10 db">
                                                          ${$_this.lng("Name")}
                                                          <input value="${name}" class="form-control" readonly>
                                                      </label>
                                                      <label class="mb-10 db">
                                                          ${$_this.lng("Email")}
                                                          <input value="${email}" class="form-control" readonly>
                                                      </label>
                                                      <label class="db">
                                                          ${$_this.lng("Comment")}
                                                          <textarea class="form-control ufo-comment-admin-reply ufo-resize-v" readonly>${comment}</textarea>
                                                      </label>
                                                `,
                                            options: {
                                                okText: $_this.lng("close")
                                            }
                                        });
                                    } else {
                                        $.ufo_dialog({title: $_this.lng("Not found")})
                                    }
                                } catch ( e ) {
                                    console.log(e)
                                    $.ufo_dialog({title: $_this.lng("System error")})
                                }
                            } else {
                                $.ufo_dialog({title: result.message})
                            }
                        }, error ( xhr ) {
                            $.ufo_dialog({
                                title: $_this.lng("Connection error")
                            })
                        }
                    }
                });
            });

            /**
             * Show full comment text
             */
            $(".ufo-show-full-comment").unbind().click(function () {
                $.ufo_dialog({
                    title: $(this).html(), options: {
                        okText: $_this.lng("close")
                    }
                });
            });

            /**
             * Reload Comments
             */
            function load_comments ( $data = {} ) {
                $.fun().do({
                    name: "req",
                    param: {
                        data: {
                            callback: "load_page",
                            page: "comments",
                            ...data, ...$data
                        },
                        done ( result ) {
                            $(`.content-page`).html(result);
                            $_this.comments({...data, ...$data});
                        },
                        error ( xhr ) {
                            $(`.content-page`).html($_this.lng("Connection error"));
                        }
                    }
                });
            }
        },

        actionTPT ( action, data = {} ) {
            const actions = {
                "upload-plugin": function () {
                    const upload = $_this.upload_file({
                        name: "ufo-plugin-upload" + Math.floor(Math.random() * 100),
                        folder: "../content/plugins/",
                        limit: 1,
                        types: {
                            "zip": "ufo-icon-archive"
                        },
                        callbacks: {
                            done ( result ) {
                                $(`.du-dialog.dlg--open`).remove();
                                $.fun().do({
                                    name: "req",
                                    param: {
                                        data: {
                                            callback: "plugin",
                                            action: "upload",
                                            plugin: result.name,
                                            prevent_ajax: "plugins"
                                        },
                                        dataType: "json",
                                        done ( result ) {
                                            if ( result.status === 200 ) {
                                                // Browsers blocked the open window
                                                // $.fun().do({
                                                //     name: "open_window",
                                                //     param: {
                                                //         width: 750,
                                                //         height: 550,
                                                //         href: web_url + "ufo-plugin-wizard"
                                                //     }
                                                // });
                                                location.href = web_url + "ufo-plugin-wizard"
                                            } else {
                                                $.fun().do({
                                                    name: "popup_message",
                                                    param: {content: result.message}
                                                });
                                            }
                                        },
                                        error ( xhr ) {
                                            $.fun().do({
                                                name: "popup_message",
                                                param: {content: $_this.lng("Connection error")}
                                            });
                                        }
                                    }
                                });
                            }
                        }
                    });
                    upload.open();
                },
                "upload-template": function () {
                    const upload = $_this.upload_file({
                        name: "ufo-template-upload" + Math.floor(Math.random() * 100),
                        folder: "../content/theme/",
                        limit: 1,
                        types: {
                            "zip": "ufo-icon-archive"
                        },
                        callbacks: {
                            done ( result ) {
                                $(`.du-dialog.dlg--open`).remove();
                                $.fun().do({
                                    name: "req",
                                    param: {
                                        data: {
                                            callback: "template",
                                            action: "upload",
                                            template: result.name,
                                            prevent_ajax: "plugins"
                                        },
                                        dataType: "json",
                                        done ( result ) {
                                            if ( result.status === 200 ) {
                                                // Browsers blocked the open window
                                                // $.fun().do({
                                                //     name: "open_window",
                                                //     param: {
                                                //         width: 750,
                                                //         height: 550,
                                                //         href: web_url + "ufo-template-wizard"
                                                //     }
                                                // });
                                                location.href = web_url + "ufo-template-wizard"
                                            } else {
                                                $.fun().do({
                                                    name: "popup_message",
                                                    param: {content: result.message}
                                                });
                                            }
                                        },
                                        error ( xhr ) {
                                            $.fun().do({
                                                name: "popup_message",
                                                param: {content: $_this.lng("Connection error")}
                                            });
                                        }
                                    }
                                });
                            }
                        }
                    });
                    upload.open();
                },
                "process-update": function ( ) {
                    let hashes = [], hashesStr = "", type = data;
                    $(`.ufo-${type}-item-actions`).each(function () {
                        hashes.push($(this).data("id"));
                        $(this).find(".first").append(`<i class="ufo-icon-loader rotating font-size-28px ufo-tpt-update-loader"></i>`);
                    });
                    hashes.map(i => hashesStr += i + "/");
                    setTimeout(() => {
                        $.fun().do({
                            name: "ufo_api", param: "checkUpdateTPT"
                        })(hashesStr.slice(0, -1), function( ) {
                            $.fun().do({name: "unset_ajax_loader"})
                        }, function ( result ) {
                            if ( result.length === 0 ) {
                                $(`.ufo-tpt-update-loader`).remove();
                                return false;
                            }
                            result.map(i => {
                                $(`.ufo-${type}-item-actions`).find(".first").find(".ufo-icon-loader").remove();
                                $(`.ufo-${type}-item-actions[data-id="${i.id}"]`).each(function () {
                                    if ( $(this).data("version") !== i.version ) {
                                        $(this).find(".first").append(`<span class="ufo-update-${type}" data-id="${i.id}">${$_this.lng("update")}</span>`)
                                    }
                                })
                            });
                            $(`.ufo-update-${type}`).unbind().click(function () {
                                const
                                    btn = $(this),
                                    $api_link = $.fun().do({name: "ufo_api", param: "getAPI"})();

                                $.fun().do({
                                    name: "req",
                                    param: {
                                        url: $api_link,
                                        data: {cmd: `market dl id='${$(this).data("id")}'`},
                                        done ( result ) {
                                            if ( ufo.isJSON(result) ) {
                                                result = JSON.parse(result);
                                                if ( result.status === 200 ) {

                                                    $.fun().do({
                                                        name: "req",
                                                        param: {
                                                            data: {
                                                                callback: "market",
                                                                action  : "dl",
                                                                link    : result.link,
                                                                type    : result.type,
                                                                mode    : "update"
                                                            },
                                                            dataType: "json",
                                                            done ( result ) {
                                                                if ( result.status === 200 ) {
                                                                    btn.remove();
                                                                    $.fun().do({
                                                                        name: "open_window",
                                                                        param: {
                                                                            width: 750,
                                                                            height: 550,
                                                                            href: result.message.link
                                                                        }
                                                                    });
                                                                } else {
                                                                    $.ufo_dialog({
                                                                        content: result.message
                                                                    })
                                                                }
                                                            },
                                                            error ( xhr ) {
                                                                $.ufo_dialog({
                                                                    content: $_this.lng("Connection error")
                                                                })
                                                            }
                                                        }
                                                    });

                                                } else {
                                                    $.ufo_dialog({
                                                        content: $_this.lng(result.error)
                                                    })
                                                }
                                            } else {
                                                $.ufo_dialog({
                                                    content: result
                                                })
                                            }
                                        },
                                        error ( xhr ) {
                                            $.ufo_dialog({
                                                content: $_this.lng("Connection error")
                                            })
                                        }
                                    }
                                });
                            })
                        }, function () {
                            $(`.ufo-tpt-update-loader`).remove();
                        });
                    }, 300);
                }
            };
            if ( typeof actions[action] !== "undefined" ) {
                actions[action]();
            }
        },

        advance_setting ( ) {
            $.fun().do({
                name: "search",
                param: {
                    input: ".ufo-more-setting-head input",
                    nothing: $_this.lng("Nothing Found :("),
                    container: ".ufo-more-setting-ul",
                    items: ".ufo-more-setting-li",
                    prop: ".ufo-advance-setting-title"
                }
            });

            $(".ufo-save-advance-setting").unbind().click(function () {
                if (ufo.objIsEmpty($saver.advance_setting))
                    return false;

                $.fun().do({
                    name: "req",
                    param: {
                        data: {
                            callback: "advance_setting",
                            action: "save",
                            setting: $saver.advance_setting
                        },
                        dataType: "json",
                        done (result) {
                            if (result.status === 200) {
                                location.reload();
                            } else {
                                $.ufo_dialog({title: result.message})
                            }
                        },
                        error (xhr) {
                            $.ufo_dialog({
                                title: $_this.lng("Connection error")
                            })
                        }
                    }
                })
            }).attr("disabled", true);

            $(".ufo-setting-web-logo, .ufo-setting-web-banner").unbind().click(function () {
                const photo = $(this).find("img");
                const witch = $(this).hasClass("ufo-setting-web-logo") ? "logo" : "banner";
                $.fun().do({
                    name: "media",
                    param: {
                        id: `ufo-setting-web-${witch}`,
                        reset: true,
                        show_label: true,
                        limit: 1,
                        types: "img",
                        result ( result ) {
                            $.fun().do({
                                name: "advance_setting",
                                param: {
                                    id: `ufo-web-${witch}`,
                                    result: result[0]
                                }
                            });
                            photo.attr("src", result[0]);
                        }
                    }
                });
            });

            $(".ufo-setting-web-name").unbind().bind("input", function () {
                $.fun().do({
                    name: "advance_setting",
                    param: {
                        id: "ufo-web-name",
                        result: $(this).val()
                    }
                })
            });

            $(".ufo-setting-footer-copyright").unbind().bind("input", function () {
                $.fun().do({
                    name: "advance_setting",
                    param: {
                        id: "ufo-copyright",
                        result: $(this).val()
                    }
                })
            });

            $(".ufo-select-charset").unbind().bind("input", function () {
                $.fun().do({
                    name: "advance_setting",
                    param: {
                        id: "ufo-charset",
                        result: $(this).val()
                    }
                })
            });

            $(".ufo-select-language").unbind().click(function () {
                const selection = $(this);
                $.fun().do({
                    name: "req",
                    param: {
                        data: {
                            callback: "advance_setting",
                            action  : "languages"
                        },
                        dataType: "json",
                        done ( result ) {
                            $.ufo_dialog({
                                title: $_this.lng("Select"),
                                content: function (list = [], id = 0) {
                                    $.each(result, (k, v) => {
                                        id++; list.push({id: id, name: v.toUpperCase()})
                                    })
                                    return list
                                }(),
                                options: {
                                    allowSearch: true,
                                    selection: true,
                                    textField: 'name',
                                    valueField: 'id',
                                    callbacks: {
                                        itemSelect: function (e, i) {
                                            $.fun().do({
                                                name: "advance_setting",
                                                param: {
                                                    id: "ufo-language",
                                                    result: i.name.toLowerCase()
                                                }
                                            });
                                            selection.val(i.name);
                                        }
                                    }
                                },
                                done ( ) {
                                    $(`input.dlg-search`).attr("placeholder", $_this.lng("Search"))
                                }
                            });
                        }
                    }
                })
            });

            $(".ufo-select-direction").unbind().click(function () {
                const selection = $(this);
                $.ufo_dialog({
                    title: $_this.lng("Select"),
                    content: [
                        {id: 1, name: "LTR"},
                        {id: 2, name: "RTL"}
                    ],
                    options: {
                        selection: true,
                        textField: 'name',
                        valueField: 'id',
                        callbacks: {
                            itemSelect: function (e, i) {
                                $.fun().do({
                                    name: "advance_setting",
                                    param: {
                                        id: "ufo-direction",
                                        result: i.name.toLowerCase()
                                    }
                                });
                                selection.val(i.name);
                            }
                        }
                    }
                });
            });

            $(".ufo-select-timezone").unbind().click(function () {
                const selection = $(this);
                $.fun().do({
                    name: "req",
                    param: {
                        data: {
                            callback: "advance_setting",
                            action  : "timezones"
                        },
                        dataType: "json",
                        done ( result ) {
                            $.ufo_dialog({
                                title: $_this.lng("Select"),
                                content: function (list = [], id = 0) {
                                    $.each(result, (k, v) => {
                                        id++; list.push({id: id, name: v.toUpperCase()})
                                    })
                                    return list
                                }(),
                                options: {
                                    allowSearch: true,
                                    selection: true,
                                    textField: 'name',
                                    valueField: 'id',
                                    callbacks: {
                                        itemSelect: function (e, i) {
                                            $.fun().do({
                                                name: "advance_setting",
                                                param: {
                                                    id: "ufo-timezone",
                                                    result: i.name.toLowerCase()
                                                }
                                            });
                                            selection.val(i.name);
                                        }
                                    }
                                },
                                done ( ) {
                                    $(`input.dlg-search`).attr("placeholder", $_this.lng("Search"))
                                }
                            });
                        }
                    }
                })
            });

            $(".ufo-select-ctime").unbind().click(function () {
                const selection = $(this);
                $.fun().do({
                    name: "req",
                    param: {
                        data: {
                            callback: "advance_setting",
                            action  : "ctime"
                        },
                        dataType: "json",
                        done ( result ) {
                            $.ufo_dialog({
                                title: $_this.lng("Select"),
                                content: function (list = [], id = 0) {
                                    $.each(result, (k, v) => {
                                        id++; list.push({id: id, name: $_this.lng(v[0].toUpperCase() + v.slice(1))})
                                    })
                                    return list
                                }(),
                                options: {
                                    allowSearch: true,
                                    selection: true,
                                    textField: 'name',
                                    valueField: 'id',
                                    callbacks: {
                                        itemSelect: function (e, i) {
                                            i.name = i.name.toLowerCase();
                                            $.fun().do({
                                                name: "advance_setting",
                                                param: {
                                                    id: "ufo-ctime",
                                                    result: i.name
                                                }
                                            });
                                            selection.val($_this.lng(i.name[0].toUpperCase() + i.name.slice(1)))
                                        }
                                    }
                                },
                                done ( ) {
                                    $(`input.dlg-search`).attr("placeholder", $_this.lng("Search"))
                                }
                            });
                        }
                    }
                })
            });

            $(".ufo-select-structure-datetime").unbind().bind("input", function () {
                $.fun().do({
                    name: "advance_setting",
                    param: {
                        id: "ufo-structure-datetime",
                        result: $(this).val()
                    }
                });
            });

            $(".ufo-select-theme").unbind().click(function () {
                const selection = $(this);
                $.ufo_dialog({
                    title: $_this.lng("Select"),
                    content: function (list = []) {
                        $.each(["Dark", "Light"], (k, v) => {
                            list.push({id: v, name: $_this.lng($_this.lng(v))})
                        })
                        return list
                    }(),
                    options: {
                        allowSearch: true,
                        selection: true,
                        textField: 'name',
                        valueField: 'id',
                        callbacks: {
                            itemSelect: function (e, i) {
                                $.fun().do({
                                    name: "advance_setting",
                                    param: {
                                        id: "ufo-theme",
                                        result: i.id
                                    }
                                });
                                selection.val($_this.lng(i.id).toUpperCase())
                            }
                        }
                    },
                    done ( ) {
                        $(`input.dlg-search`).attr("placeholder", $_this.lng("Search"))
                    }
                });
            });

            $(".ufo-max-upload-size").unbind().bind("input", function () {
                $.fun().do({
                    name: "advance_setting",
                    param: {
                        id: "ufo-max-upload-size",
                        result: $(this).val()
                    }
                });
            }).ufo_just_number();

            $(".ufo-memory-limit").unbind().bind("input", function () {
                $.fun().do({
                    name: "advance_setting",
                    param: {
                        id: "ufo-memory-limit",
                        result: $(this).val()
                    }
                });
            }).ufo_just_number();

            $(".ufo-setting-group-btn").each(function () {
                const group = $(this);
                group.find(`button`).unbind().click(function () {
                    const btn = $(this);
                    if ( btn.hasClass("active") ) { return false }

                    group.find(`button`).removeClass("active");
                    btn.addClass("active");

                    if ( btn.data("warning") ) {
                        $.ufo_dialog({
                            title: $_this.lng("Warning"),
                            content: btn.data("warning"),
                            options: {
                                callbacks: {
                                    okClick ( ) {
                                        save(); this.hide()
                                    }
                                }
                            }
                        })
                    } else {save()}

                    function save ( ) {
                        $.fun().do({
                            name: "advance_setting",
                            param: {
                                id: btn.data("setting"), result: btn.data("val")
                            }
                        })
                    }
                });
            });

            /**
             * Start - Account settings
             */

            $(".ufo-account-photo-size").unbind().bind("input", function () {
                ufo.do("advance_setting", {
                    id: "ufo-account-photo-size",
                    result: $(this).val()
                });
            }).ufo_just_number();

            $(".ufo-account-folder-profiles").unbind().bind("input", function () {
                ufo.do("advance_setting", {
                    id: "ufo-account-folder-profiles",
                    result: $(this).val()
                });
            });

            $(".ufo-timeout-verify-code").unbind().bind("input", function () {
                ufo.do("advance_setting", {
                    id: "ufo-timeout-verify-code",
                    result: $(this).val()
                });
            }).ufo_just_number();

            $(".ufo-verify-code-numbers").unbind().bind("input", function () {
                ufo.do("advance_setting", {
                    id: "ufo-verify-code-numbers",
                    result: $(this).val()
                });
            }).ufo_just_number();

            $(".ufo-verify-code-alphabets").unbind().bind("input", function () {
                ufo.do("advance_setting", {
                    id: "ufo-verify-code-alphabets",
                    result: $(this).val()
                });
            }).ufo_just_number();

            /**
             * End - Account settings
             */

            /**
             * Start - Email settings
             */

            $("input.ufo-advance-mail-host").unbind().bind("input", function () {
                $.fun().do({
                    name: "advance_setting",
                    param: {
                        id: "ufo-mail-host",
                        result: $(this).val()
                    }
                });
            });

            $("select.ufo-advance-mail-auth").unbind().bind("input", function () {
                $.fun().do({
                    name: "advance_setting",
                    param: {
                        id: "ufo-mail-auth",
                        result: $(this).val()
                    }
                });
            });

            $("select.ufo-advance-mail-secure").unbind().bind("input", function () {
                $.fun().do({
                    name: "advance_setting",
                    param: {
                        id: "ufo-mail-secure",
                        result: $(this).val()
                    }
                });
            });

            $("input.ufo-advance-mail-port").unbind().bind("input", function () {
                $.fun().do({
                    name: "advance_setting",
                    param: {
                        id: "ufo-mail-port",
                        result: $(this).val()
                    }
                });
            });

            $("input.ufo-advance-mail-email").unbind().bind("input", function () {
                $.fun().do({
                    name: "advance_setting",
                    param: {
                        id: "ufo-mail-email",
                        result: $(this).val()
                    }
                });
            });

            $("input.ufo-advance-mail-password").unbind().bind("input", function () {
                $.fun().do({
                    name: "advance_setting",
                    param: {
                        id: "ufo-mail-password",
                        result: $(this).val()
                    }
                });
            });

            $("input.ufo-advance-from-email").unbind().bind("input", function () {
                $.fun().do({
                    name: "advance_setting",
                    param: {
                        id: "ufo-mail-from-email",
                        result: $(this).val()
                    }
                });
            });

            $("input.ufo-advance-from-name").unbind().bind("input", function () {
                $.fun().do({
                    name: "advance_setting",
                    param: {
                        id: "ufo-mail-from-name",
                        result: $(this).val()
                    }
                });
            });

            /**
             * End - mail settings
             */

            $(`input.ufo-slug`).each(function () {
                $(this).unbind().bind("input", function () {
                    $.fun().do({
                        name: "advance_setting",
                        param: {
                            id: `ufo-slug-${$(this).attr("name")}`,
                            result: $(this).val()
                        }
                    });
                });
            });
        },

        security ( ) {

            /**
             * Search
             */
            $.fun().do({
                name: "search",
                param: {
                    input: ".ufo-more-setting-head input",
                    nothing: $_this.lng("Nothing Found :("),
                    container: ".ufo-more-setting-ul",
                    items: ".ufo-more-setting-li",
                    prop: ".ufo-advance-setting-title"
                }
            });

            /**
             * New Key
             */
            $(`.ufo-security-new-key`).unbind().click(function () {
                const hash = ufo.RHash();

                $(`.${$(this).data("input")}`).val(hash);

                $.fun().do({
                    name: "security_setting",
                    param: {
                        id: $(this).data("security"), result: hash
                    }
                })
            });

            /**
             * Group Buttons
             */
            $(".ufo-security-group-btn").each(function () {
                const group = $(this);
                group.find(`button`).unbind().click(function () {
                    const btn = $(this);
                    if ( btn.hasClass("active") ) { return false }

                    group.find(`button`).removeClass("active");
                    btn.addClass("active");

                    if ( btn.data("warning") ) {
                        $.ufo_dialog({
                            title: $_this.lng("Warning"),
                            content: btn.data("warning"),
                            options: {
                                callbacks: {
                                    okClick ( ) {
                                        save(); this.hide()
                                    }
                                }
                            }
                        })
                    } else {save()}

                    function save ( ) {
                        $.fun().do({
                            name: "security_setting",
                            param: {
                                id: btn.data("security"), result: btn.data("val")
                            }
                        })
                    }
                });
            });

            /**
             * Save
             */
            $(`.ufo-save-security-setting`).unbind().click(function () {
                let c = 0; $.each($saver.security, (k, v) => c++);
                if ( c !== 0 ) {
                    $.fun().do({
                        name: "req",
                        param: {
                            data: {
                                callback: "save_security",
                                result: $saver.security
                            },
                            dataType: "json",
                            done(result) {
                                if (result.status === 200) {
                                    location.reload();
                                } else {
                                    $.ufo_dialog({content: result.message})
                                }
                            },
                            error() {
                                $.ufo_dialog({content: $_this.lng("Connection error")})
                            }
                        }
                    })
                }
            });

        },

        managers ( ) {

            // Quick access admin
            $(`.profile-admin`).unbind().click(function () {
                $_this.request({
                    data: {
                        callback: "load_page",
                        page: "admin"
                    },
                    done ( result ) {
                        $.ufo_dialog({
                            title: $_this.lng("Your information"),
                            content: result,
                            options: {
                                okText: $_this.lng("close")
                            }
                        });
                    },
                    error ( xhr ) {
                        $.ufo_dialog({
                            content: $_this.lng("System error")
                        });
                    }
                });
            });

            // Start managers
            $.fun().do({
                name: "register_setting",
                param: {
                    name: "managers",
                    method: function () {
                        function load ( data = {} ) {
                            $_this.changePage({
                                page: "managers",
                                callback: status => {
                                    if ( status === 200 ) { init() }
                                },
                                data: data
                            })
                        }

                        function init ( ) {

                            let last_paging = 1;

                            $.fun().do({
                                name: "paging",
                                param: {
                                    name: "managers-table-paging",
                                    method: page => {
                                        last_paging = page;
                                        load({to_page: page});
                                    }
                                }
                            });

                            // Search
                            $_this.search({
                                input: ".search-managers",
                                container: ".ufo-table-managers tbody",
                                items: ".ufo-table-managers tbody tr",
                                prop: ".name"
                            });
                            $(`.btn-search-managers`).unbind().click(function () {
                                const val = $(`.search-managers`).val().toString();
                                if ( val.length === 0 ) {
                                    load({to_page: 1});
                                } else {
                                    load({search: val});
                                }
                            });

                            // Add manager
                            $(".add-new-manager").unbind().click(function () {
                                $_this.request({
                                    data: {
                                        callback: "load_page",
                                        add: true,
                                        page: "admin",
                                        prevent_ajax: "plugins"
                                    },
                                    done ( result ) {
                                        $.ufo_dialog({
                                            title: $_this.lng("New manager"),
                                            content: result,
                                            options: {
                                                cancel: true,
                                                okText: $_this.lng("add"),
                                                callbacks: {
                                                    okClick ( ) {
                                                        const dialog = this;
                                                        const fields = {
                                                            photo: $saver.managers.photo ?? ""
                                                        };

                                                        $(".ufo-manager-input").each(function () {
                                                            fields[$(this).attr("name")] = $(this).val()
                                                        });

                                                        $_this.request({
                                                            data: {
                                                                callback: "managers",
                                                                action: "add",
                                                                fields: fields,
                                                                prevent_ajax: "plugins"
                                                            },
                                                            dataType: "json",
                                                            done ( result ) {
                                                                if ( result.status === 200 ) {
                                                                    load(); dialog.hide()
                                                                } else {
                                                                    $.ufo_dialog({title: result.message})
                                                                }
                                                            },
                                                            error ( ) {
                                                                $.ufo_dialog({title: $_this.lng("Connection error")})
                                                            }
                                                        });
                                                    }
                                                }
                                            },
                                            done: function () {
                                                $(`.select-admin-photo`).unbind().click(function () {
                                                    $.fun().do({
                                                        name: "media",
                                                        param: {
                                                            id: "manager-photo",
                                                            reset: false,
                                                            show_label: true,
                                                            limit: 1,
                                                            types: "img",
                                                            loader ( ) {$(`.e-user-cover-photo-loader`).removeClass("dn");},
                                                            done ( ) {$(`.e-user-cover-photo-loader`).addClass("dn");},
                                                            result(result) {
                                                                $saver.managers.photo = result[0];
                                                                $(`.select-admin-photo img`).attr("src", $saver.managers.photo);
                                                            }
                                                        }
                                                    });
                                                });
                                            }
                                        })
                                    },
                                    error ( ) {
                                        $.ufo_dialog({
                                            content: $_this.lng("Connection error")
                                        })
                                    }
                                })
                            });

                            // Remove manager
                            $(".remove-manager").unbind().click(function () {
                                const submit = $(this), listManager = [];
                                let selectManager = 0;

                                $.ufo_dialog({
                                    title: $_this.lng("Are you sure you want to delete this manager?"),
                                    content: function ( ) {
                                        $(".ufo-table-managers tbody tr").each(function () {
                                            if ( Number(submit.data("admin")) !== Number($(this).data("id")) ) {
                                                listManager.push({
                                                    id  : $(this).data("id"),
                                                    name: $(this).find(".name").text()
                                                })
                                            }
                                        });
                                        return `<label class="db mt-15">${$_this.lng("Transfer all data to")} : <input class="form-control mt-10 ufo-select-transform-manager text-center cursor-pointer" readonly placeholder="${$_this.lng("select")}"></label>`;
                                    }(),
                                    options: {
                                        cancel: true,
                                        okText: $_this.lng("delete"),
                                        callbacks: {
                                            okClick ( ) {
                                                const dialog = this;
                                                if ( selectManager === 0 ) {
                                                    $.ufo_dialog({
                                                        title: $_this.lng("Please select an administrator")
                                                    })
                                                } else {
                                                    $_this.request({
                                                        data: {
                                                            callback: "managers",
                                                            action: "remove",
                                                            manager: submit.data("admin"),
                                                            transform: selectManager
                                                        },
                                                        dataType: "json",
                                                        done ( result ) {
                                                            if ( result.status === 200 ) {
                                                                load(); dialog.hide()
                                                            } else {
                                                                $.ufo_dialog({title: result.message})
                                                            }
                                                        },
                                                        error ( ) {
                                                            $.ufo_dialog({title: $_this.lng("Connection error")})
                                                        }
                                                    });
                                                }
                                            }
                                        }
                                    },
                                    done ( ) {
                                        $(`.ufo-select-transform-manager`).unbind().click(function () {
                                            const selector = $(this);
                                            $.ufo_dialog({
                                                title: $_this.lng("Select"),
                                                content: listManager,
                                                options: {
                                                    allowSearch: true,
                                                    selection: true,
                                                    textField: 'name',
                                                    valueField: 'id',
                                                    callbacks: {
                                                        itemSelect: function (e, i) {
                                                            selectManager = i.id;
                                                            selector.val(i.name)
                                                        }
                                                    }
                                                },
                                                done ( ) {
                                                    $(`input.dlg-search`).attr("placeholder", $_this.lng("Search"))
                                                }
                                            });
                                        })
                                    }
                                })
                            });

                            // Edit manager
                            $(".edit-manager").unbind().click(function () {
                                const submit = $(this);
                                $_this.request({
                                    data: {
                                        callback: "load_page",
                                        admin: submit.data("admin"),
                                        page: "admin",
                                        prevent_ajax: "plugins"
                                    },
                                    done ( result ) {
                                        $.ufo_dialog({
                                            title: $_this.lng("edit manager"),
                                            content: result,
                                            options: {
                                                cancel: true,
                                                okText: $_this.lng("edit"),
                                                callbacks: {
                                                    okClick ( ) {
                                                        const dialog = this;
                                                        const fields = {
                                                            photo: $saver.managers.photo ?? ""
                                                        };

                                                        $(".ufo-manager-input").each(function () {
                                                            fields[$(this).attr("name")] = $(this).val()
                                                        });

                                                        $_this.request({
                                                            data: {
                                                                callback: "managers",
                                                                action: "edit",
                                                                admin: submit.data("admin"),
                                                                fields: fields,
                                                                prevent_ajax: "plugins"
                                                            },
                                                            dataType: "json",
                                                            done ( result ) {
                                                                if ( result.status === 200 ) {
                                                                    load({
                                                                        to_page: last_paging
                                                                    }); dialog.hide()
                                                                } else {
                                                                    $.ufo_dialog({title: result.message})
                                                                }
                                                            },
                                                            error ( ) {
                                                                $.ufo_dialog({title: $_this.lng("Connection error")})
                                                            }
                                                        });
                                                    }
                                                }
                                            },
                                            done: function () {
                                                $(`.select-admin-photo`).unbind().click(function () {
                                                    $.fun().do({
                                                        name: "media",
                                                        param: {
                                                            id: "manager-photo",
                                                            reset: false,
                                                            show_label: true,
                                                            limit: 1,
                                                            types: "img",
                                                            loader ( ) {$(`.e-user-cover-photo-loader`).removeClass("dn");},
                                                            done ( ) {$(`.e-user-cover-photo-loader`).addClass("dn");},
                                                            result(result) {
                                                                $saver.managers.photo = result[0];
                                                                $(`.select-admin-photo img`).attr("src", $saver.managers.photo);
                                                            }
                                                        }
                                                    });
                                                });
                                            }
                                        })
                                    },
                                    error ( ) {
                                        $.ufo_dialog({
                                            content: $_this.lng("Connection error")
                                        })
                                    }
                                })
                            });

                        }

                        init();
                    }
                }
            })

        },

        updateSystem ( ) {
            $(`.ufo-upgrade-page-render`).html(`<div class="ufo-update-cn"><div class="ufo-update-circle pending"><div class="ufo-update-circle-core"></div></div><div class="width-100-cent ufo-info-new-version"></div></div>`);

            const
                loader              = {
                    tag: "div",
                    html: [
                        {
                            tag: "div",
                            html: [
                                {tag: "div"},
                                {tag: "div"},
                                {tag: "div"}
                            ],
                            attrs: {
                                class: "ufo-update-loader-pulse"
                            }
                        }
                    ],
                    attrs: {
                        class: "ufo-update-loader"
                    }
                },
                UFOUpdateLoader     = $(`.ufo-update-circle`),
                UFOUpdateLoaderCore = UFOUpdateLoader.find(`.ufo-update-circle-core`);

            function dl_nv ( ) {
                $(`.ufo-btn-get-new-version`).unbind().click(function () {
                    const button = $(this);
                    const saveUpdateCoreHtml = UFOUpdateLoaderCore.html();

                    $.fun().do({
                        name: "req",
                        param: {
                            data: {
                                callback: "system_update",
                                action  : "dnv",
                                link    : button.data("link"),
                                version : button.data("version"),
                            },
                            dataType: "json",
                            loader ( ) {
                                $.fun().do({name: "unset_ajax_loader"});
                                button.hide();
                                $(`.ufo-info-new-version`).hide();
                                UFOUpdateLoader.addClass("pending");
                                UFOUpdateLoaderCore.html($.fun().do({
                                    name: "json2html",
                                    param: loader
                                }));
                            },
                            done ( result ) {
                                UFOUpdateLoader.removeClass("pending");
                                UFOUpdateLoaderCore.html(saveUpdateCoreHtml);

                                if ( result.status === 200 ) {
                                    setTimeout(() => {
                                        button.show();
                                        $(`.ufo-info-new-version`).show();
                                        button.html($_this.lng(`Install now`));
                                        button.unbind("click").click(function () {
                                            location.href = admin_web_url + "update";
                                        });
                                    }, 300);
                                } else {
                                    button.show();
                                    $(`.ufo-info-new-version`).show();
                                    $.ufo_dialog({
                                        title: result.message
                                    })
                                }
                            },
                            error ( xhr ) {
                                $.ufo_dialog({
                                    title: $_this.lng("Connection error")
                                })
                            }
                        }
                    })
                });
            }

            $.fun().do({
                name: "ufo_api", param: "system"
            })().newVersion(
                ufo_info.version,
                ( ) => {
                    $.fun().do({name: "unset_ajax_loader"});
                    UFOUpdateLoaderCore.html($.fun().do({
                        name: "json2html",
                        param: loader
                    }));
                },
                result => {
                    if ( typeof result.link !== "undefined" ) {
                        UFOUpdateLoader.removeClass("pending");
                        UFOUpdateLoaderCore.html(`
                                <i class="ufo-icon-upgrade"></i>
                                ${result.version}
                            `);
                        setTimeout(( ) => {
                            $(`<div class="ufo-cn-get-new-version"><button class="btn btn-primary ufo-btn-get-new-version">${$_this.lng("Get the new version")}</button></div>`).insertAfter(UFOUpdateLoader);
                            $(`.ufo-info-new-version`).addClass("show").html(`<div class="ufo-about-new-version"><span>${$_this.lng("About the new version")}</span></div>` + result.about);
                            $("<div style='width: 100%;height: 1px;'></div>").insertAfter(`.ufo-info-new-version`);

                            $(`.ufo-btn-get-new-version`).data("link", result.link).data("version", result.version);

                            dl_nv();
                        }, 300);
                    } else {
                        UFOUpdateLoader.removeClass("pending").addClass("stop");
                        UFOUpdateLoaderCore.html(`
                                <i class="ufo-icon-ufocms"></i>
                                ${ufo_info.version}
                            `);
                    }
                },
                xhr => {
                    UFOUpdateLoader.removeClass("pending").addClass("stop");
                    UFOUpdateLoaderCore.html(`
                            <i class="ufo-icon-ufocms"></i>
                            ${ufo_info.version}
                        `);
                }
            );
        },

        // Start Options

        JPlugin ( ) {
            (function ($) {
                $.fn.ufo_inputs = function (error = "please fill in the fields" ) {
                    let values = {
                        error: false
                    };

                    $(this).each(function () {
                        const item = $(this);
                        if ( typeof item.data("empty") !== "undefined" ) {
                            values[item.attr("name")] = item.val();
                        } else {
                            if ( $_this.detectVoid(item.val(), null) ) {
                                values[item.attr("name")] = item.val();
                            } else {
                                values.error = error;
                            }
                        }
                    });

                    if ( values.error ) alert($_this.lng(values.error));

                    return values;
                };
            }(jQuery));
        },

        paging ( ) {
            $(`.modern-paging .modern-paging-item`).unbind().click(function () {
                const item = $(this);
                if ( Number(item.data("disabled")) === 1 ) return false;
                $saver.paging.map(i => {
                    if ( i.name === item.data("action") ) {
                        if ( typeof i.method === "function" ) i.method(item.data("page"));
                    }
                });
            });
        },

        add_paging ( {container, page, total, action, change} ) {
            const $total     = total;
            const $page      = page;
            const $action    = action;
            const $next_page = $page + 1;
            const $prev_page = $page - 1;

            if ( ufo.dir === "rtl" ) {
                $(container).append($.fun().do({
                    name: "json2html",
                    param: {
                        tag: "div",
                        html: [
                            {
                                tag: "span",
                                html: [
                                    {
                                        tag: "i",
                                        attrs: {
                                            class: "ufo-icon-chevrons-left"
                                        }
                                    }
                                ],
                                attrs: {
                                    class: "modern-paging-item",
                                    "data-page": $total,
                                    "data-disabled": $page >= $total ? "true" : "false",
                                    "data-action": $action
                                }
                            },
                            {
                                tag: "span",
                                html: [
                                    {
                                        tag: "i",
                                        attrs: {
                                            class: "ufo-icon-chevron-left"
                                        }
                                    }
                                ],
                                attrs: {
                                    class: "modern-paging-item",
                                    "data-page": $next_page,
                                    "data-disabled": $total < $next_page ? "true" : "false",
                                    "data-action": $action
                                }
                            },

                            {
                                tag: "span",
                                html: [$_this.lng("page") + " " + $page + " " + $_this.lng("of") + " " + $total],
                                attrs: {
                                    class: "of_page"
                                }
                            },

                            {
                                tag: "span",
                                html: [
                                    {
                                        tag: "i",
                                        attrs: {
                                            class: "ufo-icon-chevron-right"
                                        }
                                    }
                                ],
                                attrs: {
                                    class: "modern-paging-item",
                                    "data-page": $prev_page,
                                    "data-disabled": $page > 1 ? "false" : "true",
                                    "data-action": $action
                                }
                            },
                            {
                                tag: "span",
                                html: [
                                    {
                                        tag: "i",
                                        attrs: {
                                            class: "ufo-icon-chevrons-right"
                                        }
                                    }
                                ],
                                attrs: {
                                    class: "modern-paging-item",
                                    "data-page": "1",
                                    "data-disabled": $page > 1 ? "false" : "true",
                                    "data-action": $action
                                }
                            },
                        ],
                        attrs: {
                            class: "modern-paging",
                            "data-action": $action
                        }
                    }
                }));
            } else {
                $(container).append($.fun().do({
                    name: "json2html",
                    param: {
                        tag: "div",
                        html: [
                            {
                                tag: "span",
                                html: [
                                    {
                                        tag: "i",
                                        attrs: {
                                            class: "ufo-icon-chevrons-left"
                                        }
                                    }
                                ],
                                attrs: {
                                    class: "modern-paging-item",
                                    "data-page": 1,
                                    "data-disabled": $page > 1 ? "false" : "true",
                                    "data-action": $action
                                }
                            },
                            {
                                tag: "span",
                                html: [
                                    {
                                        tag: "i",
                                        attrs: {
                                            class: "ufo-icon-chevron-left"
                                        }
                                    }
                                ],
                                attrs: {
                                    class: "modern-paging-item",
                                    "data-page": $prev_page,
                                    "data-disabled": $page > 1 ? "false" : "true",
                                    "data-action": $action
                                }
                            },

                            {
                                tag: "span",
                                html: [$_this.lng("page") + " " + $page + " " + $_this.lng("of") + " " + $total],
                                attrs: {
                                    class: "of_page"
                                }
                            },

                            {
                                tag: "span",
                                html: [
                                    {
                                        tag: "i",
                                        attrs: {
                                            class: "ufo-icon-chevron-right"
                                        }
                                    }
                                ],
                                attrs: {
                                    class: "modern-paging-item",
                                    "data-page": $next_page,
                                    "data-disabled": $total < $next_page ? "true" : "false",
                                    "data-action": $action
                                }
                            },
                            {
                                tag: "span",
                                html: [
                                    {
                                        tag: "i",
                                        attrs: {
                                            class: "ufo-icon-chevrons-right"
                                        }
                                    }
                                ],
                                attrs: {
                                    class: "modern-paging-item",
                                    "data-page": $total,
                                    "data-disabled": $page >= $total ? "true" : "false",
                                    "data-action": $action
                                }
                            },
                        ],
                        attrs: {
                            class: "modern-paging",
                            "data-action": $action
                        }
                    }
                }));
            }

            $.fun().do({
                name: "paging",
                param: {
                    name: $action,
                    method: function ( page ) {
                        function next ( ) {
                            $(`.modern-paging[data-action="${$action}"]`).remove();
                            $_this.add_paging({
                                container: container,
                                page: page,
                                total: $total,
                                action: $action,
                                change: change
                            });
                        }
                        {
                            typeof change === "function" ? change.bind({
                                next: next
                            })(page) : () => {next()};
                        }
                    }
                }
            });
        },

        notify ( arg ) {
            const mode = typeof arg.mode !== "undefined" ? arg.mode : "danger";
            const zone = typeof arg.zone !== "undefined" ? arg.zone : "center";
            const id   = Math.round(Math.random() * 1000);
            const time = typeof arg.time !== "undefined" ? arg.time : 3;
            let count  = 0;

            if ( !$(`.system-float-notice-container`).length ) $(`body`).prepend('<div class="system-float-notice-container"></div>');

            $(`.system-float-notice-container`).append(`
                    <div class="system-float-notice ${zone} sfn-${id}">
                        <div class="content">
                            <span class="icon ${mode}">
                                <i class="${arg.icon}"></i>
                            </span>
                            <span>${arg.title}</span>
                        </div>
                    </div>
                `);

            $saver.notify.push({
                id, interval: undefined
            });

            $.each($saver.notify, ( k, v ) => {
                if ( v.id === id ) {
                    $saver.notify[k]["interval"] = setInterval(()=>{ count++;
                        if ( count >= time ) {
                            clearInterval(v.interval);
                            $(`.sfn-${v.id}`).remove();
                            $_this.remove_obj({
                                obj: $saver.notify,
                                prop: "id",
                                val: v.id
                            });
                        }
                    },1000);
                }
            });
        } ,

        popup_message ( {title = "", content = ""} ) {
            try {
                $.ufo_dialog({
                    title, content,
                    options: {
                        okText: $_this.lng("close")
                    }
                });
            }catch(e){}
        },

        detectVoid ( value, text = "Please check the field" ) {
            if ( value.length === 0 ) {
                if ( text != null ) {
                    alert($_this.lng(text));
                }
                return false;
            }
            if ( value.substring(0,1) === "‌" || value.substring(0,1) === " " ) {
                if ( text != null ) {
                    alert(text + " (" + $_this.lng("It should not start with a distance") + ") ");
                }
                return false;
            }
            return true;
        },

        media ( options ) {
            let actions, _save_files = [];

            function save_files ( files ) {
                _save_files = files;
            }

            if ( typeof options.sync !== "undefined" && options.sync ) {
                options.reset = true;
                options.sync  = false;
            }
            if ( typeof options.id !== "undefined" && typeof options.reset !== "undefined" && !options.reset ) {
                if ( $(`.media-container:last-child [data-media="0x${options.id}"]`).length ) {
                    $(`.media-container:last-child [data-media="0x${options.id}"]`).toggleClass("dn");
                    return false;
                }
            }

            $(`.media-container [data-media="0x${options.id}"]`).remove();

            if ( $(`.media-container:last-child`).length === 0 ) {
                if ( $(`.content-page`).length !== 0 ) {
                    $(`.content-page`).append(`<div class="media-container"></div>`);
                } else {
                    $("body").append(`<div class="media-container"></div>`);
                }
            }

            $.fun().do({
                name: "req",
                param: {
                    data: {callback: "media", types: (typeof options.types !== "undefined" ? options.types : "*")},
                    dataType: "json",
                    loader ( ) {
                        actions = typeof options.loader === "undefined" ? "" : options.loader();
                    },
                    done ( result ) {
                        actions = typeof options.done === "undefined" ? "" : options.done();

                        const rowFile = result.files;
                        const id    = `0x${typeof options.id !== "undefined" ? options.id : Math.floor(Math.random() * 1000)}`;
                        let   files = "";
                        let   c_files = 0;

                        save_files(rowFile);
                        $.each(rowFile, ( k,v ) => {
                            c_files++;
                            files += `<option class="${(!v.image ? "no-img" : "")}" data-link="${v.link}" data-name="${k}" data-img-class="${(!v.image ? "ufo-no-img" : "")}" ${(!v.image ? `data-ufo-icon="${file_types[v.type]}"` : ``)} data-img-src="${v.link}" data-img-label="${k}" value="${k}"></option>`;
                        });

                        $(".media-container:last-child").append(
                            `<div data-media="${id}" class="user-select-none">
                                     <div class="media-header">
                                        <i class="ufo-icon-x f-right cls-media action-media" data-id="${id}"></i>
                                        <i class="ufo-icon-refresh-ccw f-right refresh-media action-media" data-id="${id}"></i>
                                        <button class="btn btn-light f-left font-size-14px back-folder-media" disabled="true" data-id="${id}" style="margin: 0 10px;height: 36px;margin: 0 10px;padding: 0;width: 45px;">
                                            <i class="ufo-icon-chevron-left font-size-21px"></i>
                                        </button>
                                        <button class="btn btn-light f-left width-25-cent font-size-14px select-media" data-id="${id}">${$_this.lng("select")}</button>
                                     </div>
                                     <div style="padding: 0 10px 10px;">
                                        <input type="text" class="form-control width-100-cent search-media" placeholder="${$_this.lng("search")}">
                                     </div>
                                     <div class="folders" style="padding: 0 4px">${result.folder}</div>
                                     <div class="content overflow-auto">
                                        ${(c_files === 0 ? `<div class="empty"><i class="ufo-icon-desert"></i><h3>${$_this.lng("This folder is empty")}</h3></div>` : `<select class="media-selector show-lable" ${typeof options.limit !== "undefined" ? `multiple="multiple"` : ''}>${files}</select>`)}
                                     </div>
                                 </div>`
                        );

                        func_options(id, options);
                    },
                    error ( xhr ) {
                        actions = typeof options.error === "undefined" ? "" : options.failed(xhr);
                        error($_this.lng("Connection error"));
                    }
                }
            });

            function change_folder ( folder, loading, done, error ) {
                $.fun().do({
                    name: "req",
                    param: {
                        data: {
                            callback: "media",
                            folder: folder,
                            types: (typeof options.types !== "undefined" ? options.types : "*")
                        }, dataType:"json", beforeSend: loading, done, error
                    }
                });
            }

            function _change_folder ( folder, id, content_html ) {
                if ( $(`.media-container:last-child [data-media="${id}"] .media-selector`).length ) {
                    $(`.media-container:last-child [data-media="${id}"] .media-selector`).data('picker').destroy();
                }
                change_folder(folder, function () {
                    content_html.parent().find(".folders").empty();
                    content_html.html(`<i class="ufo-icon-semicircular rotating" style="font-size: 50px;"></i>`);
                    content_html.css({
                        display: "flex",
                        justifyContent: "center",
                        alignItems: "center"
                    });
                }, function (result) {
                    let files = "", c_files = 0;
                    save_files(result.files);
                    $.each(result.files, ( k,v ) => {
                        c_files++;
                        files += `<option class="${(!v.image ? "no-img" : "")}" data-link="${v.link}" data-name="${k}" data-img-class="${(!v.image ? "ufo-no-img" : "")}" ${(!v.image ? `data-ufo-icon="${file_types[v.type]}"` : ``)} data-img-src="${v.link}" data-img-label="${k}" value="${k}"></option>`;
                    });
                    content_html.parent().find(".folders").html(result.folder);
                    content_html.attr("style", "");
                    content_html.html(c_files === 0 ? `<div class="empty"><i class="ufo-icon-desert"></i><h3>${$_this.lng("This folder is empty")}</h3></div>` : `<select class="media-selector show-lable" ${typeof options.limit !== "undefined" ? `multiple="multiple"` : ''}>${files}</select>`);
                    if ( c_files === 0 ) {
                        content_html.css({
                            display: "flex",
                            justifyContent: "center",
                            alignCenter: "center"
                        });
                    }

                    func_options(id, options);

                    $(`[data-media="${id}"] .back-folder-media`).unbind().click(function () {
                        _change_folder(result.back, id, content_html);
                    }).attr("disabled", false);
                    if ( result.back === `../content` || result.back === `..\\content` || result.back === `content` ) {
                        $(`[data-media="${id}"] .back-folder-media`).unbind().attr("disabled", true);
                    }
                }, function (xhr) {
                    content_html.html(`<i class="ufo-icon-alert-triangle" style="font-size: 50px;"></i>`);
                    content_html.css({
                        display: "flex",
                        justifyContent: "center",
                        alignItems: "center",
                        color: "red"
                    });
                });
            }

            function func_options (id, options) {
                const content_html = $(`[data-media="${id}"] .content`);

                $(`[data-media="${id}"] .fm-row-folders`).ufo_scroll({type: "h"});
                $(`[data-media="${id}"] .cls-media`).unbind().click(function () {
                    $(`.media-container:last-child [data-media="${$(this).data("id")}"]`).addClass("dn");
                });
                $(`[data-media="${id}"] .refresh-media`).unbind().click(function () {
                    options.sync = true;
                    $_this.media(options);
                });
                $(`[data-media="${id}"] .select-media`).unbind().click(function () {
                    let selected   = $(`.media-container:last-child [data-media="${$(this).data("id")}"] ul.thumbnails li div.thumbnail.selected`);
                    let joinSelect = typeof options.withName !== "undefined" && options.withName ? {} : [];

                    selected.each(function () {
                        if ( typeof options.withName !== "undefined" && options.withName ) {
                            joinSelect[$(this).data("name")] = $(this).data("link")
                        } else {
                            joinSelect.push($(this).data("link"))
                        }
                    });

                    if ( joinSelect.length === 0 ) {
                        error($_this.lng("Please select"));
                        return false;
                    }

                    typeof options.result === "function" ? options.result(joinSelect) : null;

                    $(`[data-media="${id}"] .cls-media[data-id="${$(this).data("id")}"]`).click();
                });
                $(`[data-media="${id}"] .fm-folder-container`).unbind().click(function () {
                    _change_folder($(this).data("address"), id, content_html);
                });

                $_this.search({
                    input: `[data-media="${id}"] .search-media`,
                    items: `[data-media="${id}"] .thumbnails li`,
                    prop: `p`
                });

                if ( typeof options.limit !== "undefined" ) {
                    options.limit_reached = function () {
                        error($_this.lng("You can not select more than % item").replace("%n", options.limit));
                    }
                }

                options.show_label = true;

                $(`.media-container:last-child [data-media="${id}"] .media-selector`).imagepicker(options);
            }

            function error ( error ) {
                typeof options.error !== "undefined" &&
                typeof options.error === "function"  ? options.error(error) : alert(error);
            }
        },

        upload_file ( options = {} ) {
            let params = $.extend({
                limit_size: ufo_info.max_size,
                limit: 999,
                folder: $saver.fm.dir,
                reset: true,
                types: ufo_info.types,
                minimize: true,
                html: {
                    empty: `<div class="empty"><i class="ufo-icon-desert"></i><span>${$_this.lng("List is empty")}</span></div>`
                },
                callbacks: {
                    done : ( ) => {},
                    error: ( ) => {},
                    abort: ( ) => {}
                }
            }, options);

            params.limit = Number(params.limit) + 1;

            if ( typeof params.name === "undefined" ) {
                $.ufo_dialog({
                    title: "",
                    content: $_this.lng("Please enter the name of the uploader")
                });
                return false;
            } else if ( typeof $saver.fm.uploads[params.name] === "undefined" ) {
                $saver.fm.uploads[params.name] = [];
            }

            if ( typeof $saver.fm.FILES[params.name] === "undefined" ) $saver.fm.FILES[params.name] = [];

            function setFolder ( folder ) {
                params.folder = folder;
            }
            function update_status_node ( node, status ) {
                $.each($saver.fm.uploads[params.name], ( k, v ) => {
                    if ( v.node == node ) {
                        $saver.fm.uploads[params.name][k].status = status;
                    }
                });
            }
            function get_node ( node ) {
                let data;
                $.each($saver.fm.uploads[params.name], ( k, v ) => {
                    if ( v.node == node ) {
                        data = v;
                    }
                });
                return data;
            }
            function remove_node ( node, from_list = true ) {
                const target = $(".fm-upload-preview-files").find(`.file[data-node="${node}"]`);
                if ( from_list ) $saver.fm.uploads[params.name].removeObj("node", node);
                target.slideUp(800);
                setTimeout(()=>{target.remove();check_empty()}, 900);
            }
            function check_empty ( ) {
                const target = $(".fm-upload-preview-files");
                if ( !target.find(".file").length ) {
                    $(`.fm-minimize-uploading[data-name-upload="${params.name}"]`).remove();
                    target.html(params.html.empty);
                    return true;
                }
                return false;
            }
            function check_type ( type ) {
                let result = false;
                $.each(params.types, ( k, v ) => {
                    if ( k == type ) result = true;
                });
                return result;
            }

            function response_size () {}
            function minimize_uploading ( ) {
                if ( !params.minimize || $saver.fm.uploads[params.name].length === 0 ) {return false;}

                let minimize_node = $(`.fm-minimize-uploading[data-name-upload="${params.name}"]`);

                if ( !minimize_node.length ) {
                    $(`body`).prepend(
                        `<div class="fm-minimize-uploading" data-name-upload="${params.name}">
                                        <div class="header"><i class="ufo-icon-x close"></i><i class="ufo-icon-minus minimize"></i></div>
                                        <div class="list"></div>
                                    </div>`);
                    minimize_node = $(`.fm-minimize-uploading[data-name-upload="${params.name}"]`);
                }

                $("div.fm-minimize-uploading").css({overflow: "hidden"});

                minimize_node.find(`.list`).html($(".fm-upload-preview-files")[0].outerHTML);
                minimize_node.find(`.list .fm-upload-preview-files`).css({
                    width: 100 + "%",
                    border: "unset",
                    height: "calc(100% - 40px)"
                });
                minimize_node.find(`.minimize`).unbind().click(function () {
                    if ( $(this).hasClass("active") ) {
                        $(this).removeClass("active");
                        minimize_node.animate( { height:"200px" }, { queue:false, duration:500 });
                        minimize_node.find(`.fm-upload-preview-files .file .layer`).css({display: "flex"});
                    } else {
                        $(this).addClass("active");
                        minimize_node.animate( { height:"40px"}, { queue:false, duration:500 });
                        minimize_node.find(`.fm-upload-preview-files .file .layer`).css({display: "none"});
                    }
                });
                minimize_node.find(`.close`).unbind().click(function () {
                    minimize_node.remove();
                });
                response_size();
                minimize_node.find(`.list .file`).each(function ( ) {
                    set_file_options(Number($(this).data("node")));
                });
            }
            function progress ( percent, target, color = "#04a0db",  color2 = "#fff" ) {
                let create = `-webkit-linear-gradient(left, ${color} ${percent}%, ${color2} 0)`;
                $(target).css({
                    "background": create
                });
                return create;
            }
            function uploader ( file, node, html ) {
                let form_data = new FormData();
                let target    = `.fm-upload-preview-files .file[data-node="${node}"]`;

                form_data.append('FILE', file);
                form_data.append('callback', 'media_action');
                form_data.append('action', 'upload');
                form_data.append('folder', params.folder);
                form_data.append('prevent_ajax', "plugins");

                const xhr = $.fun().do({
                    name: "req",
                    param: {
                        data: form_data,
                        contentType: false,
                        processData: false,
                        dataType: "json",
                        xhr: function ( ) {
                            const xhr = new window.XMLHttpRequest();
                            xhr.upload.addEventListener("progress", function (evt) {
                                if (evt.lengthComputable) {
                                    let percent = (evt.loaded / evt.total) * 100;
                                    progress(percent, target);
                                }
                            }, false);
                            return xhr;
                        },
                        loader ( ) {
                            $_this.panel_loader(200);
                            $_this.layer_loader(false);
                        },
                        done ( result ) {
                            result.status = Number(result.status);
                            if ( result.status === 200 ) {
                                progress(100, target, "green");
                                $(target).find("i.abort").removeClass("ufo-icon-x").addClass("ufo-icon-check");
                                $(target).find(".circle").css({
                                    "background": "green",
                                    "border-color": "green"
                                });
                                if ( typeof params.callbacks.done === "function" ) params.callbacks.done(file);
                            } else {
                                progress(100, target, "red");
                                if ( typeof params.callbacks.error === "function" ) params.callbacks.error(result.status);
                            }
                            $(target + ' *').css({color: "white"});
                            $(target).find("span").html(result.message);
                            $(target + " .options *").unbind("click");
                            update_status_node(node, result.status);
                            unset_file_options(node);
                            setTimeout(()=>{
                                remove_node(node, params.reset);
                                check_empty();
                            }, 5000);
                        },
                        error ( xhr ) {
                            progress(100, target, "red");
                            if ( typeof params.callbacks.error === "function" ) params.callbacks.error(xhr);
                            unset_file_options(node);
                            setTimeout(()=>{
                                remove_node(node, params.reset);
                                check_empty();
                            }, 5000);
                        }
                    }
                });

                $saver.fm.uploads[params.name].push({file, node, xhr, html, status: 0});
            }
            function set_file_options ( node ) {
                $(`.fm-upload-preview-files .file[data-node="${node}"] i.abort`).unbind().click(function () {
                    $.ufo_dialog({
                        title: $_this.lng("Cancel upload"),
                        content: $_this.lng("Are you sure you want to cancel uploading this file?"),
                        options: {
                            cancel: true,
                            okText: $_this.lng("yes"),
                            callbacks: {
                                okClick ( ) {
                                    $_this.set_ajax_error(()=>{});
                                    get_node(node).xhr.abort();
                                    $_this.set_ajax_error(false);
                                    remove_node(node, params.reset);
                                    unset_file_options(node);
                                    this.hide();
                                }
                            }
                        }
                    });
                });
            }
            function unset_file_options ( node ) {
                $(`.fm-upload-preview-files .file[data-node="${node}"] .options *`).unbind();
            }

            return {
                reset ( ) {
                    $saver.fm.uploads[params.name] = [];
                    $saver.fm.FILES[params.name] = [];
                },
                removeType ( type ) {
                    params.types = Object.keys(params.types).filter(key =>
                        key !== type).reduce((obj, key) => {
                            obj[key] = params.types[key];
                            return obj;
                        }, {}
                    );
                },
                addType ( type, icon ) {
                    if ( typeof type === "undefined" ) return;
                    if ( type.toString().length === 0 ) return;
                    params.types[type] = icon;
                },
                showTypes ( ) {
                    return params.types;
                },
                open ( ) {
                    $.ufo_dialog({
                        title: $_this.lng("file upload"),
                        content: `<div class="fm-popup-upload-list"><div class="fm-upload-container">
                                    <label for="fm-up-input-file">${(ufo.os.mobile ? $_this.lng("Select the files") : $_this.lng("Choose or Drag & Drop Files") ) }
                                        <input id="fm-up-input-file" type='file' class='width-100-cent height-100-cent' multiple>
                                    </label>
                                 </div><div class="fm-upload-preview-files">${params.html.empty}</div></div>`,
                        options: {
                            okText: $_this.lng("close"),
                            callbacks: {
                                okClick ( ) {
                                    minimize_uploading();
                                    this.hide();
                                }
                            }
                        },
                        done ( ) {
                            $(`.fm-minimize-uploading[data-name-upload="${params.name}"]`).remove();
                            renderHistoryUpload();

                            let input = $("#fm-up-input-file");
                            let container = $(".fm-upload-container");
                            let label = $(".fm-upload-container label");
                            let preview = $(".fm-upload-preview-files");

                            const readURL = file => {
                                return new Promise((res, rej) => {
                                    const reader = new FileReader();
                                    reader.onload = e => res(e.target.result);
                                    reader.onerror = e => rej(e);
                                    reader.readAsDataURL(file);
                                });
                            };
                            const convert_size = (bytes, unit = true, decimals = 2) => {
                                if (bytes === 0) return '0 Bytes';

                                const k = 1024;
                                const dm = decimals < 0 ? 0 : decimals;
                                const sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];

                                const i = Math.floor(Math.log(bytes) / Math.log(k));

                                return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + (unit ? sizes[i] : "");
                            }

                            function renderHistoryUpload ( ) {
                                let history   = $saver.fm.uploads[params.name];
                                let container = $(".fm-popup-upload-list").find(".fm-upload-preview-files");

                                $.each(history, ( k, v ) => {
                                    container.find(".empty").remove();
                                    container.append(v.html);
                                    set_file_options(v.node);
                                    response_size();
                                });
                            }
                            function handleFILES ( e ) {
                                let list = [];

                                if (e.originalEvent.dataTransfer) {
                                    if (e.originalEvent.dataTransfer.files.length < params.limit) {
                                        if (e.originalEvent.dataTransfer.files.length) {
                                            e.preventDefault();
                                            e.stopPropagation();
                                            list = e.originalEvent.dataTransfer.files;
                                            container.removeClass("drag");
                                            preview.removeClass("drag");
                                        }
                                    } else {
                                        $.ufo_dialog({
                                            title: "",
                                            content: $_this.lng("You can not upload more than %n files").replace("%n", Number(params.limit) - 1)
                                        });
                                    }
                                } else {
                                    if (e.target.files.length < params.limit) {
                                        list = e.target.files;
                                    } else {
                                        $.ufo_dialog({
                                            title: "",
                                            content: $_this.lng("You can not upload more than %n files").replace("%n", Number(params.limit) - 1)
                                        });
                                    }
                                }

                                $.each(list, ( k, v ) => saveFILES(v));

                                if ( typeof $(this).val !== "undefined" ) {
                                    $(this).val(null);
                                }
                            }
                            async function saveFILES (file) {
                                const size = Number((file.size / 1024 / 1024).toFixed(0));
                                const split_name = file.name.split(".");
                                let   has = false;

                                $.each($saver.fm.uploads[params.name], ( k, v ) => {
                                    if ( v.file.name == file.name ) {
                                        has = true;
                                    }
                                });

                                if ( !has ) {
                                    if ($saver.fm.uploads[params.name].length < params.limit && $saver.fm.FILES[params.name].length < params.limit ) {
                                        if ( split_name.length > 1 && check_type(split_name.pop()) ) {
                                            if ( size <= params.limit_size ) {
                                                $saver.fm.FILES[params.name].push(file);
                                                await renderFILES(file);
                                            } else {
                                                $.ufo_dialog({
                                                    title: file.name,
                                                    content: $_this.lng("File size is more than allowed.")
                                                });
                                            }
                                        } else {
                                            $.ufo_dialog({
                                                title: file.name,
                                                content: $_this.lng("File format is not allowed")
                                            });
                                        }
                                    } else {
                                        $.ufo_dialog({
                                            title: "",
                                            content: $_this.lng("You can not upload more than %n files").replace("%n", Number(params.limit) - 1)
                                        });
                                    }
                                }
                            }
                            async function renderFILES ( file, upload = true ) {
                                preview.find(".empty").remove();
                                const node = Math.floor(Math.random() * 1000);
                                const html = `<div class="file" data-node="${node}">
                                            ${await icon_by_type(file)}
                                            <div class="info"><h4>${file.name}</h4><span>size : ${convert_size(file.size)}</span></div>
                                            <div class="options"><div class="circle"><i class="ufo-icon-x abort"></i></div></div>
                                        </div>`;

                                preview.prepend(html);
                                set_file_options(node);

                                if ( upload ) uploader(file, node, html);

                                window.onresize = function () {response_size()};
                            }
                            async function icon_by_type ( file ) {
                                let icon = "ufo-icon-file-question";
                                let type = file.name.split(".").pop();

                                if ( file.type.match("image") ) {
                                    const url = await readURL(file);
                                    icon = `<img src="${url}">`;
                                } else {
                                    $.each(params.types, ( k, v ) => {
                                        if ( k == type ) {icon = v;}
                                    });
                                    return `<div class="icon"><i class="${icon}"></i></div>`;
                                }

                                return icon;
                            }

                            container.on("dragstart dragover", function () {
                                $(this).addClass("drag");
                                preview.addClass("drag");
                            }).on("dragend dragleave drop", function () {
                                preview.removeClass("drag");
                                $(this).removeClass("drag");
                            });

                            input.change(handleFILES);
                            label.on("drop", handleFILES);
                        }
                    });
                },
                setFolder ( folder ) {
                    setFolder(folder);
                }
            };
        },

        search ( {input, container, items, prop, nothing} ) {
            $(input).on("keyup", function() {
                let value = $(this).val().toLowerCase();
                $(items).filter(function() {
                    $(this).toggle($(this).find(prop).text().toLowerCase().indexOf(value) > -1);
                });
                if ( typeof container !== "undefined" ) {
                    if($(container).children(':visible').length == 0) {
                        if ( !$("nothing").length ) {
                            $(container).parent().append(`<nothing class='flex flex-center width-100-cent'>${
                                typeof nothing === "undefined" ? $_this.lng("Nothing Found :(") : nothing
                            }</nothing>`);
                        }
                    } else {
                        $(container).parent().find("nothing").remove();
                    }
                }
            });
        },

        panel_loader ( status ) {
            if ( ufo_info.panel ) {
                switch (status) {
                    case 0:
                        $_this.layer_loader(true, false);
                        break;
                    case 200:
                        $_this.layer_loader(false, false);
                        break;
                    case 503: break;
                    default: $_this.layer_loader(false, false);
                }
            }
        },

        layer_loader ( active, mt = true ) {
            if ( ufo_info.panel ) {
                if ( !mt ) {
                    if ( $(window).width() < 1000 ) {
                        return false;
                    }
                }
                if ( !$(".panel-loader").length ) {
                    $("body").prepend(`<div class="panel-loader"><div class="width-100-cent height-100-cent flex flex-center align-center"><div class="box"><i class="ufo-icon-semicircular rotating"></i></div></div></div>`);
                }
                if ( active ) {
                    $(`.panel-loader`).removeClass("dn").addClass("db");
                } else {
                    $(`.panel-loader`).removeClass("db").addClass("dn");
                }
            }
        },

        remove_array ( data ) {
            return data.array.filter(function (k) {
                k[data.prop] !== data.value;
            });
        },

        remove_obj ( data ) {
            return data.obj.findIndex(x => x[data.prop] == [data.val] ) >= 0 ? data.obj.splice( data.obj.findIndex( x => x[data.prop] == [data.val] ), 1 ) : undefined;
        },

        rand_num ( len = 10 ) {
            return Math.round(Math.random() * len);
        },

        img_error ( ) {
            $("img[data-error]").unbind("error").bind("error", function () {
                $(this).attr("src", $(this).data("error"));
                $(this).unbind("error");
                $(this).removeAttr("data-error");
            });
        },

        contextmenu ( {target, not, items} ) {
            try {
                $(target).unbind("contextmenu").on("contextmenu", function (e) {
                    e.preventDefault();
                    try {
                        $(".ufo-context-menu").remove();
                        let append = "", $t = $(this);
                        $("body").prepend(`<div class="ufo-context-menu" style="display: block;left: ${e.pageX}px;top: ${e.pageY}px"><ul>${append}</ul></div>`);
                        $.each(items, (k, v) => {
                            const li = document.createElement("li");
                            li.innerHTML = k;
                            $(li).click(function () {
                                v($t, this)
                            });
                            $(".ufo-context-menu ul").append(li);
                        });
                    } catch (e) {
                    }
                });
                $(not).unbind("contextmenu");
            } catch (e) {}
        },

    };

    if (is_panel && typeof ufo_info.page === "object" && !$saver.changedPage) {
        $_this.changePage({
            data: ufo_info.page,
            callback: status => {
                if (status === 200) {
                    $(`.menu-items li`).removeClass("active");
                    $(`.menu-items li[data-page="${$saver.page}"], .menu-items li[data-plugin="${$saver.page}"]`).addClass("active");
                }
            }
        });
        $saver.changedPage = true;
    } else {
        $_this.panel_loader(0);
        document.onreadystatechange = function () {
            if (document.readyState === "complete") {
                $_this.panel_loader(200);
            }
        };

        $_this.init();
    }
}).do();