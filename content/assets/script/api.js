/**
 * Copyright (c) 2022-2024 UFOCMS
 *
 * This software is licensed under the GPLv3 license.
 * See the LICENSE file for more information.
 */

ufo.apply("ufo_api", function (api) {
    const $api_link = "https://api.ufocms.org";

    function json2html (json = {}) {
        return ufo.do("json2html", json)
    }

    return {

        market () {
            const templates = ufo_info.templates, plugins = ufo_info.plugins;

            {
                function matchList ($list, $id) {
                    return typeof $list[$id] !== "undefined"
                }

                function exists (list, v) {
                    return matchList(list, v.id) ? {
                        "exists": true,
                        "update": list[v.id] !== v.version
                    } : {
                        "exists": false,
                        "update": false
                    };
                }

                function download (type, link, btn) {
                    $.fun().do({
                        name: "req",
                        param: {
                            data: {
                                callback: "market",
                                action: "dl",
                                link: link,
                                type: type,
                                mode: "install"
                            },
                            dataType: "json",
                            xhr: ufo.xhr(
                                cent => $(`p[data-id="${btn.data("id")}"]`).html(cent + "%"),
                                cent => $(`p[data-id="${btn.data("id")}"]`).html(cent + "%"),
                            ),
                            loader() {
                                $.fun().do({name: "unset_ajax_loader"});
                                btn.html(`<p data-id="${btn.data("id")}" style="font-size: 13px !important;margin: 5px 0 0;}">0%</p>`);
                            },
                            done(result) {
                                if (result.status === 200) {
                                    btn.empty();
                                    btn.addClass("ufo-icon-check");
                                    location.href = result.message.link;
                                } else {
                                    $.ufo_dialog({
                                        content: result.message
                                    })
                                }
                            },
                            error(xhr) {
                                $.ufo_dialog({
                                    content: ufo.lng("Connection error")
                                })
                            }
                        }
                    });
                }

                function submit_dl () {
                    const btn = $(this);
                    const icon = $(`<i class="db"></i>`);

                    if ($.compareVersion(ufo_info.version, "<", btn.data("rv"))) {
                        $.ufo_dialog({
                            content: ufo.lng("For installation, UFO version must be above $version").replace("$version", btn.data("rv")),
                            options: {
                                okText: ufo.lng("close")
                            }
                        })
                    } else {
                        btn.html(icon);
                        $.fun().do({
                            name: "req",
                            param: {
                                url: $api_link,
                                data: {
                                    cmd: `market dl id='${$(this).data("id")}'`
                                },
                                loader: () => {
                                    $.fun().do({name: "unset_ajax_loader"});
                                    btn.removeClass("ufo-icon-refresh-ccw").removeClass("ufo-icon-check").removeClass("ufo-icon-arrow-down");
                                    icon.addClass("ufo-icon-loader").addClass("rotating");
                                    btn.attr("disabled", true);
                                },
                                done(result) {
                                    btn.empty();
                                    if (ufo.isJSON(result)) {
                                        result = JSON.parse(result);
                                        if (result.status === 200) {
                                            download(result.type, result.link, btn)
                                        } else {
                                            btn.addClass("ufo-icon-x");
                                            $.ufo_dialog({
                                                content: ufo.lng(result.error)
                                            })
                                        }
                                    } else {
                                        btn.addClass("ufo-icon-x");
                                        $.ufo_dialog({
                                            content: result
                                        })
                                    }
                                },
                                error(xhr) {
                                    btn.attr("disabled", false);
                                    btn.empty();
                                    btn.addClass("ufo-icon-x");
                                    icon.removeClass("ufo-icon-loader").removeClass("rotating").addClass("ufo-icon-x");
                                }
                            }
                        });
                    }
                }

                function update () {
                    const btn = $(this);
                    const icon = $(`<i class="db"></i>`);

                    if ($.compareVersion(ufo_info.version, "<", btn.data("rv"))) {
                        $.ufo_dialog({
                            content: ufo.lng("To update, UFO version must be above $version").replace("$version", btn.data("rv")),
                            options: {
                                okText: ufo.lng("close")
                            }
                        })
                    } else {
                        btn.html(icon);
                        $.fun().do({
                            name: "req",
                            param: {
                                url: $api_link,
                                data: {cmd: `market dl id='${$(this).data("id")}'`},
                                loader() {
                                    $.fun().do({name: "unset_ajax_loader"});
                                    icon.addClass("ufo-icon-loader").addClass("rotating");
                                    btn.removeClass("ufo-icon-refresh-ccw").removeClass("ufo-icon-check").removeClass("ufo-icon-arrow-down");
                                    btn.attr("disabled", true);
                                },
                                done(result) {
                                    btn.empty();
                                    if (ufo.isJSON(result)) {
                                        result = JSON.parse(result);
                                        if (result.status === 200) {
                                            btn.addClass("ufo-icon-check");
                                            $.fun().do({
                                                name: "req",
                                                param: {
                                                    data: {
                                                        callback: "market",
                                                        action: "dl",
                                                        link: result.link,
                                                        type: result.type,
                                                        mode: "update"
                                                    },
                                                    dataType: "json",
                                                    done(result) {
                                                        if (result.status === 200) {
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
                                                    error(xhr) {
                                                        $.ufo_dialog({
                                                            content: ufo.lng("Connection error")
                                                        })
                                                    }
                                                }
                                            });
                                        } else {
                                            btn.addClass("ufo-icon-x");
                                            $.ufo_dialog({
                                                content: ufo.lng(result.error)
                                            })
                                        }
                                    } else {
                                        btn.addClass("ufo-icon-x");
                                        $.ufo_dialog({
                                            content: result
                                        })
                                    }
                                },
                                error(xhr) {
                                    btn.empty();
                                    btn.addClass("ufo-icon-x");
                                    $.ufo_dialog({
                                        content: ufo.lng("Connection error")
                                    })
                                }
                            }
                        });
                    }
                }

                function allItemMarket (container, page = 1, paging = false, fn = () => {
                }) {
                    const show = $(this);
                    const type = $(this).data("type");

                    $.fun().do({
                        name: "req",
                        param: {
                            url: $api_link,
                            data: {cmd: `market full type="${(type === "plugins" ? "plugin" : "template")}",page="${page}"`},
                            dataType: "json",
                            done(result) {
                                $(".ufo-row-market").remove();

                                /**
                                 * Render rows
                                 */
                                {
                                    rowMarket(container, result.rows, type, (type === "plugins" ? "Plugins" : "Templates"));
                                    const show = $(`.ufo-market-show-all`);
                                    show.html(ufo.lng("Back") + show.find(`i`)[0].outerHTML).unbind().click(function () {
                                        $(`.ufo-market-cn`).empty();
                                        $.fun().do({name: "ufo_api", param: "market"})()
                                    });
                                }

                                /**
                                 * Pagination
                                 */
                                {
                                    if (!paging) {
                                        $.fun().do({
                                            name: "add_paging",
                                            param: {
                                                container: ".ufo-market-cn",
                                                page: result.current,
                                                total: result.total,
                                                action: "ufo-market-paging",
                                                change: function (page) {
                                                    let $t = this;
                                                    allItemMarket.bind(show)(container, page, true, function () {
                                                        $t.next();
                                                    });
                                                }
                                            }
                                        })
                                    }
                                }

                                fn();
                            },
                            error(xhr) {
                                $.ufo_dialog({
                                    content: ufo.lng("Connection error")
                                })
                            }
                        }
                    });
                }

                function rowMarket (container, list, type, title) {
                    let $rowMarket;

                    container.append(`<div class='ufo-row-market' data-type='${type}'></div>`);

                    $rowMarket = $(`.ufo-row-market[data-type='${type}']`);

                    $rowMarket.append(json2html({
                        tag: "div",
                        html: [
                            {
                                tag: "div",
                                html: [
                                    {
                                        tag: "h4",
                                        html: ufo.lng(title)
                                    }
                                ],
                                attrs: {
                                    class: "p-10px"
                                }
                            },
                            {
                                tag: "div",
                                html: [
                                    {
                                        tag: "span",
                                        html: [ufo.lng("all"), {
                                            tag: "i",
                                            attrs: {
                                                class: "ufo-icon-chevron-" + (ufo.dir === "rtl" ? "left" : "right")
                                            }
                                        }],
                                        attrs: {
                                            "data-type": type,
                                            class: (ufo.dir === "rtl" ? "f-left" : "f-right") + " ufo-market-show-all"
                                        }
                                    }
                                ],
                                attrs: {
                                    class: "p-10px"
                                }
                            }
                        ],
                        attrs: {
                            class: "grid-2"
                        }
                    }));
                    $rowMarket.append("<hr>");

                    $rowMarket.append(json2html({
                        tag: "div", attrs: {
                            class: "ufo-columns-market"
                        }
                    }));

                    $rowMarket = $(`.ufo-row-market[data-type='${type}'] .ufo-columns-market`);

                    $.each(list, (k, v) => {
                        const check = exists(type === "plugins" ? plugins : templates, v);
                        $rowMarket.append(json2html({
                            tag: "div",
                            html: [
                                {
                                    tag: "div",
                                    html: [
                                        {
                                            tag: "img",
                                            attrs: {
                                                src: v.photo,
                                                "data-error": ufo_info.error_photo
                                            }
                                        },
                                        {
                                            tag: "span",
                                            html: [v.name]
                                        }
                                    ],
                                    attrs: {
                                        "data-id": v.id,
                                        class: "ufo-market-show-detail"
                                    }
                                },
                                {
                                    tag: "div",
                                    html: [
                                        {
                                            tag: "div",
                                            html: [
                                                {
                                                    tag: "button",
                                                    attrs: {
                                                        "data-id": v.id,
                                                        "data-rv": v.required_version,
                                                        class: (
                                                            check.exists ? (
                                                                check.update ? "ufo-icon-refresh-ccw ufo-market-update-btn" : "ufo-icon-check"
                                                            ) : "ufo-icon-arrow-down ufo-market-dl-btn"
                                                        )
                                                    }
                                                }
                                            ],
                                            attrs: {
                                                class: "install-cn"
                                            }
                                        }
                                    ]
                                }
                            ],
                            attrs: {
                                class: "ufo-market-item"
                            }
                        }))
                    });

                    $(`.ufo-market-dl-btn`).unbind().click(submit_dl);
                    $(`.ufo-market-update-btn`).unbind().click(update);

                    $(`.ufo-market-show-detail`).unbind().click(function () {
                        const item = $(this);
                        $.fun().do({
                            name: "req",
                            param: {
                                url: $api_link,
                                data: {
                                    cmd: `market get list='${item.data("id")}'`
                                },
                                dataType: "json",
                                done(result) {
                                    if (typeof result[0] !== "undefined") {
                                        result = result[0];
                                        const reverseElement = ufo.dir === "rtl" ? "ltr" : "rtl";
                                        const check = exists(result.type === "plugin" ? plugins : templates, result);

                                        $(`body`).prepend(`<div class="ufo-popup-modal-layer plugin-detail-modal ufo-market-detail">
                                                 <div class="ufo-popup-modal">
                                                        <div class="header">
                                                            <h4 class="title">${result.name}</h4>
                                                            <div class="close">
                                                                <i class="ufo-icon-x"></i>
                                                                <span>${ufo.lng("close")}</span>
                                                            </div>
                                                        </div>
                                                        <div class="container">
                                                
                                                            <div class="side side-plugin-info">
                                                                <div class="top">
                                                                    <div class="plugin-detail-logo">
                                                                        <img src="${result.logo}" data-error="${ufo_info.error_photo}">
                                                                    </div>
                                                                    <button data-id="${result.id}" class="btn ${check.exists ? (check.update ? "btn-info ufo-market-update-btn" : "btn-primary") : "btn-success ufo-market-dl-btn"}" data-rv="${result.required_version}" style="margin: 5px 0 15px;width: 100%;">${check.exists ? (check.update ? ufo.lng("Update") : ufo.lng("Installed")) : ufo.lng("Install")}</button>
                                                                </div>
                                                                
                                                                <ul class="plugin-info-list-properties">
                                                                    <li>
                                                                        <div>${ufo.lng("Developer")}</div>
                                                                        <div dir="${reverseElement}" title="${result.developer.username}">${result.developer.username}</div>
                                                                    </li>
                                                                    <li>
                                                                        <div>${ufo.lng("Install")}</div>
                                                                        <div dir="${reverseElement}">${result.install}</div>
                                                                    </li>
                                                                    <li>
                                                                        <div>${ufo.lng("Rated")}</div>
                                                                        <div dir="${reverseElement}">${result.rate}/5</div>
                                                                    </li>
                                                                    <li>
                                                                        <div>${ufo.lng("Size")}</div>
                                                                        <div dir="${reverseElement}">${result.size}</div>
                                                                    </li>
                                                                </ul>
                                                            </div>
                                                
                                                            <div class="content overflow-auto db p-10px">
                                                            
                                                                 <ul class="ufo-tabs ufo-pages-scroll ufo_scroll">
                                                                      <li class="ufo-tabs-items active" data-ufo-tab="description">${ufo.lng("Description")}</li>
                                                                      <li class="ufo-tabs-items" data-ufo-tab="comments">${ufo.lng("Comments")}</li>
                                                                 </ul>
                                                                 
                                                                 <div class="ufo-tabs-pages ufo-market-tab-page active" data-ufo-tab="description">${result.content}</div>
                                                                 
                                                                 <div class="ufo-tabs-pages" data-ufo-tab="comments">${comments(result.comments.rows)}</div>
                                                                 
                                                            </div>
                                                
                                                        </div>
                                                    </div>   
                                             </div>`);

                                        $(`.ufo-market-dl-btn`).unbind().click(submit_dl);
                                        $(`.ufo-market-update-btn`).unbind().click(update);

                                        $(`.ufo-market-detail .close`).unbind().click(() => $(`.ufo-market-detail`).remove())
                                    }
                                },
                                error(xhr) {
                                    $.ufo_dialog({
                                        content: ufo.lng("Connection error")
                                    })
                                }
                            }
                        })
                    });
                    $(`.ufo-market-show-all`).unbind().click(function () {
                        allItemMarket.bind(this)(container)
                    });

                    $.fun().do({name: "image_error"});
                }

                function commentItem (v) {
                    return {
                        tag: "li",
                        html: [
                            {
                                tag: "div",
                                html: [
                                    {
                                        tag: "img",
                                        attrs: {
                                            src: function () {
                                                if (typeof v.admin !== "undefined") {
                                                    return v.admin.photo
                                                } else if (typeof v.developer !== "undefined") {
                                                    return v.developer.photo
                                                } else if (typeof v.member !== "undefined") {
                                                    return v.member.photo
                                                } else if (typeof v.guest !== "undefined") {
                                                    return "#"
                                                }
                                            }(),
                                            "data-error": ufo_info.unknown_img
                                        }
                                    }
                                ]
                            },
                            {
                                tag: "div",
                                html: [
                                    {
                                        tag: "div",
                                        html: [
                                            {
                                                tag: "div",
                                                html: [{
                                                    tag: "h4",
                                                    html: function () {
                                                        if (typeof v.admin !== "undefined") {
                                                            return v.admin.username
                                                        } else if (typeof v.developer !== "undefined") {
                                                            return v.developer.username
                                                        } else if (typeof v.member !== "undefined") {
                                                            return v.member.username
                                                        } else if (typeof v.guest !== "undefined") {
                                                            return v.guest
                                                        }
                                                    }()
                                                }]
                                            },
                                            {
                                                tag: "div",
                                                html: function (stars = []) {
                                                    for (let i = 0; i < v.rate; i++) {
                                                        stars.push({
                                                            tag: "i",
                                                            attrs: {
                                                                class: "ufo-icon-star-fill",
                                                                style: "margin: 0 2px"
                                                            }
                                                        })
                                                    }
                                                    return stars
                                                }(),
                                                attrs: {
                                                    class: "flex flex-end p-5px"
                                                }
                                            }
                                        ],
                                        attrs: {
                                            class: "grid-2"
                                        }
                                    },
                                    {
                                        tag: "p",
                                        html: [v.comment + ""]
                                    }
                                ]
                            }
                        ]
                    }
                }

                function comments (list) {
                    const items = [];

                    $.each(list, (k, v) => {
                        const comments = [];
                        const reply = [];

                        comments.push(commentItem(v));

                        $.each(v.reply, ($k, $v) => {
                            reply.push(commentItem($v));
                            comments.push({
                                tag: "div", html: reply, attrs: {class: "ufo-market-comment-reply"}
                            })
                        })

                        items.push({
                            tag: "div",
                            html: comments
                        })
                    });

                    return json2html({
                        tag: "ul",
                        html: items,
                        attrs: {
                            class: "ufo-market-comments"
                        }
                    })
                }
            }

            {
                $.fun().do({
                    name: "req",
                    param: {
                        url: $api_link,
                        data: {
                            cmd: "market full"
                        },
                        dataType: "json",
                        done(result) {
                            let container = $(".ufo-market-cn");

                            /**
                             * Render search
                             */
                            container.append("<div class='ufo-market-search'></div>");
                            const search = $(`.ufo-market-search`);
                            search.append(json2html({
                                tag: "div",
                                html: [
                                    {
                                        tag: "input",
                                        attrs: {
                                            placeholder: ufo.lng("Search")
                                        }
                                    },
                                    {
                                        tag: "i",
                                        attrs: {
                                            class: "ufo-icon-search"
                                        }
                                    }
                                ],
                                attrs: {
                                    class: "p-5px",
                                    style: "height: 55px"
                                }
                            }));

                            /**
                             * Rows
                             */
                            rowMarket(container, result.plugins.rows, "plugins", "Plugins");
                            rowMarket(container, result.templates.rows, "templates", "Templates");

                            search.find("input").bind("input", function () {
                                const input = $(this), icon = $(".ufo-market-search i");
                                if ($(this).val().length >= 2) {
                                    $.fun().do({
                                        name: "req",
                                        param: {
                                            url: $api_link,
                                            data: {cmd: `market search name="${$(this).val()}"`},
                                            dataType: "json",
                                            done(result) {
                                                const plugins = function (list = []) {
                                                    result.map(i => (i.type === "plugin" ? list.push(i) : ""));
                                                    return list;
                                                }();
                                                const templates = function (list = []) {
                                                    result.map(i => (i.type === "template" ? list.push(i) : ""));
                                                    return list
                                                }();

                                                $(".ufo-row-market").remove();

                                                rowMarket(container, plugins, "plugins", "Plugins");
                                                rowMarket(container, templates, "templates", "Templates");
                                            },
                                            error(xhr) {
                                                $.ufo_dialog({
                                                    content: ufo.lng("Connection error")
                                                })
                                            }
                                        }
                                    });
                                } else {
                                    $(".ufo-row-market").remove();

                                    /**
                                     * Rows
                                     */
                                    rowMarket(container, result.plugins.rows, "plugins", "Plugins");
                                    rowMarket(container, result.templates.rows, "templates", "Templates");
                                }
                                if ($(this).val().length !== 0) {
                                    icon.removeClass("ufo-icon-search").addClass("ufo-icon-x").addClass("cursor-pointer");
                                    icon.unbind().click(function () {
                                        input.val("").trigger("input")
                                    })
                                } else {
                                    icon.removeClass("ufo-icon-x").addClass("ufo-icon-search");
                                    icon.unbind()
                                }
                            });
                        },
                        error(xhr) {
                            $.ufo_dialog({
                                content: ufo.lng("Connection error")
                            })
                        }
                    }
                });
            }
        },

        checkUpdateTPT (hashes, loader = () => {}, done = () => {}, error = () => {}) {
            $.fun().do({
                name: "req",
                param: {
                    url: $api_link,
                    data: {
                        cmd: `market get list='${hashes}'`
                    },
                    dataType: "json",
                    loader: loader,
                    done: done,
                    error: error
                }
            });
        },

        system () {
            return {
                newVersion(version, loader = () => {
                }, done = () => {
                }, error = () => {
                }) {
                    $.fun().do({
                        name: "req",
                        param: {
                            url: $api_link,
                            data: {
                                cmd: `system update version='${version}'`
                            },
                            dataType: "json",
                            loader: loader,
                            done: done,
                            error: error
                        }
                    });
                }
            }
        },

        getAPI () {
            return $api_link;
        }

    }[api];
});