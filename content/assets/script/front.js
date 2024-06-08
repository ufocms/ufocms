/**
 * Copyright (c) 2022-2024 UFOCMS
 *
 * This software is licensed under the GPLv3 license.
 * See the LICENSE file for more information.
 */

ufo.freeze(ufo_data);

/**
 * Preload I18n
 * @type {Promise<*|[]>}
 */
const ufo_i18n = async function () {
    return await fetch(ufo_data.web_url + "float/i18n")
        .then(result => result.json()).catch(() => [])
}();

ufo.apply(null, async function () {
    const $saver = {
        languages: await ufo_i18n,
        paging: []
    };
    let $_this;

    const web_url = ufo_data.web_url;
    const front_ajax_url = web_url + "ajax.php";

    return {

        init ( ) {
            $_this = this;

            $_this.addFuns();

            $_this.front();
            $_this.account();
            $_this.img_error();
            $_this.paging();

            $.fun().do({name: "exec", param: {}});

            return $_this;
        },

        addFuns ( ) {
            ufo.apply({
                name: "lng",
                method: $_this.lng
            });
            ufo.apply({
                name: "req",
                method: $_this.request
            });
            ufo.apply({
                name: "getParam",
                method: $_this.getParameter
            });
            ufo.apply({
                name: "paging",
                method ( { name, method } ) {
                    let has = false;
                    $saver.paging.map(i => {if ( i.name === name ) {has = true;}});
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
            ufo.apply({
                name: "ufo_password_page",
                method: function () {
                    let pass = prompt($_this.lng("Please enter the password"));
                    if (pass)
                        ufo.url.addParam("password", pass, true);
                }
            });
            ufo.apply({
                name: "ufo_front_reload",
                method: $_this.front
            });
            ufo.apply({
                name: "ufo_toggle_loader",
                method: function ( ) {
                    if (!$(".panel-loader").length)
                        $(`body`).prepend(`<div class="panel-loader db">
                            <div class="width-100-cent height-100-cent flex flex-center align-center">
                                <div class="box"><i class="ufo-icon-semicircular rotating"></i></div>
                            </div>
                        </div>`);
                    else $(".panel-loader").remove();
                }
            });
        },

        lng ( arg = null ) {
            if (typeof arg === "string") arg = {
                string: arg
            };

            if (arg.string === "*")
                return $saver.languages;

            return $saver.languages[arg.string] ?? arg.string;
        },

        request ( op ) {
            if (op.data instanceof FormData) {
                op.data.append("location", location.href);
            } else {
                op.data = $.extend({
                    location: location.href
                }, op.data ?? {});
            }
            return $.ajax($.extend({
                url : front_ajax_url,
                type: "POST"
            }, op, {
                beforeSend: typeof op.loader !== "undefined" ? op.loader : undefined,
                success   : typeof op.done   !== "undefined" ? op.done   : undefined,
                error     : typeof op.error  !== "undefined" ? op.error  : undefined
            }));
        },

        front ( ) {
            /** Preview template */
            if (ufo_data.preview) {
                $(`body`).prepend(`<button class="btn btn-primary ufo-exit-preview-template">${$_this.lng("Exit preview")}</button>`);
                $(`.ufo-exit-preview-template`).unbind().click(function () {
                    const btn = $(this);
                    $.fun().do({
                        name: "req",
                        param: {
                            data: {
                                callback: "ufo-exit-preview"
                            },
                            loader() {
                                btn.html($_this.lng("Wait"))
                            },
                            done(result) {
                                location.reload();
                            },
                            error(xhr) {
                                alert($_this.lng("Connection error"))
                            }
                        }
                    })
                });
            }

            /** Comments */
            $(`.ufo-btn-open-comment`).unbind().click(function () {
                $(`.ufo-send-comment-wrp`).slideToggle();
            });

            /** Star rating */
            $("ufo-star-rating").each(function () {
                const $star = $(this);
                const stars = Number($(this).data("star") || 5) + 1;

                $star.data("rate", 1);

                for (let i = 1; i < stars; i++) {
                    const star = $(`<star class="${i === 1 ? "active" : ""}"></star>`);

                    $star.append(star);

                    star.data("star", i);
                    star.click(function () {
                        $star.find("star").removeClass("active");

                        const rate = Number($(this).data("star"));
                        for (let j = 0; j < rate; j++) {
                            $($star.find(`star`)[j]).addClass("active");
                        }

                        $star.data("rate", rate);
                    });
                }
            });

            /** Send comment */
            $(".ufo-send-comment").unbind().click(function () {
                const btn = $(this);

                const name  = $(`.ufo-field-comment[data-name="name"]`);
                const email = $(`.ufo-field-comment[data-name="email"]`);
                const content = $(`.ufo-field-comment[data-name="content"]`);

                if ( !$_this.detectVoid(content.val(), $_this.lng("Please write your comment")) ) {
                    return false;
                }

                const data = {
                    comment: content.val(),
                    page: btn.data("p"),
                    rate: $("ufo-star-rating").data("rate"),
                    for : "article"
                };

                if ( name.length && email.length ) {
                    if ( !$_this.detectVoid(name.val(), "Please check the fields") ) {return false}
                    if ( !$_this.detectVoid(email.val(), "Please check the fields") ) {return false}

                    data["name"]  = name.val();
                    data["email"] = email.val();
                }

                if ( typeof $(this).data("reply") !== "undefined" ) {
                    data["reply"] = $(this).data("reply");
                }

                $.fun().do({
                    name: "req",
                    param: {
                        data: {
                            callback: "ufo_front_comment",
                            action: "submit_comment",
                            ...data
                        },
                        dataType: "json",
                        done ( result ) {
                            alert(result.message);
                            if ( result.status === 200 || result.status === 100 ) {
                                setTimeout(()=>{
                                    location.reload()
                                }, 1000)
                            }
                        },
                        error ( xhr ) {
                            alert($_this.lng("Connection error"));
                        }
                    }
                });
            });

            /** Reply comment */
            $(`[data-reply-cm]`).unbind().click(function () {
                $(`.ufo-send-comment-wrp`).slideDown();
                $(`.ufo-send-comment`).data("reply", $(this).data("reply-cm"));
                $('html, body').animate({
                    scrollTop: $(".ufo-send-comment-wrp").offset().top - ($(".ufo-send-comment-wrp").height() / 2)
                }, 1500);
            });

            /** Comment paging **/
            ufo.do({
                name: "paging",
                param: {
                    name: "comments-table-paging",
                    method: page => {
                        ufo.req({
                            data: {
                                callback: "ufo_front_comment",
                                action: "load_comments",
                                ppage: page,
                                p: $(`.ufo-comment-wrp`).data("p")
                            },
                            loader ( ) {
                                $(`.ufo-comment-wrp`).html(`<div style="width: 100%;height: fit-content;display: flex;justify-content: center;align-items: center;"><i class="ufo-icon-circle-notch rotating db" style="font-size: 25px;"></i></div>`)
                            },
                            done ( result ) {
                                $(`.ufo-comment-wrp`).replaceWith(result);
                                $_this.front();
                            },
                            error ( ) {location.reload()}
                        })
                    }
                }
            })

            /** Submit form */
            $(".ufo-form").unbind().submit(function (e) {
                e.preventDefault();

                const form   = $(this);
                const submit = form.find(`[type="submit"]`);
                const submitText = submit.html();
                const fields = {};

                let $continue = true;

                form.find("[name]").each(function () {
                    let field = $(this), name = field.attr("name"), type = field.attr("type");
                    let value = field.val();

                    if (ufo.isNULL(value.trim())) {
                        const placeholder = field.attr("placeholder");

                        if (typeof placeholder !== "undefined" && placeholder !== false)
                            alert(ufo.lng("Please enter the %n").replace(`%n`, placeholder));

                        field.css({
                            borderColor: "red"
                        }).focus();
                        setTimeout(() =>
                            field.removeAttr("style").focus(), 1500
                        );

                        $continue = false; return false;
                    }

                    if (type === "password")
                        value = $.md5(field.val());

                    fields[name] = value;
                });

                if (!$continue)
                    return false;

                ufo.req({
                    data: {
                        callback: "ufo_form",
                        fields
                    },
                    dataType: "json",
                    loader ( ) {
                        submit.attr("disabled", true);
                        submit.html($_this.lng("wait"))
                    },
                    done ( result ) {
                        submit.attr("disabled", false);
                        submit.html(submitText);

                        if (result.status === 200 && result.message?.redirect) {
                            location.href = result.message.redirect
                        } else {
                            alert(result.message)
                        }
                    },
                    error ( ) {
                        submit.attr("disabled", false);
                        submit.html(submitText);
                        alert($_this.lng("Connection error"))
                    }
                })
            });

            /** Scroll articles */
            if ($(".ufo-list-articles").length) {
                ufo.do("paging", {
                    name: "home_articles-paging",
                    method: page => ufo.url.addParam(
                        "page", page, true
                    )
                })
            }
        },

        account ( ) {
            /** Verify form */
            {
                const $countdown = $("#ufo-ms-countdown-verify");

                function resend ( ) {
                    const text = $(this).text();

                    $(this).unbind("click");

                    $.fun().do({
                        name: "req",
                        param: {
                            data: {
                                callback: "ufo_account_verify_code"
                            },
                            dataType: "json",
                            loader ( ) {
                                $countdown.html("");
                                $countdown.addClass("ufo-icon-circle-notch").addClass("rotating");
                            },
                            complete ( ) {
                                $countdown.html(text);
                                $countdown.removeClass("ufo-icon-circle-notch").removeClass("rotating");
                            },
                            done (result) {
                                if (result.status === 200) {
                                    $countdown.data("time", result.message.time);
                                    countdown()
                                } else if (typeof result.message.redirect === "string") {
                                    location.href = result.message.redirect
                                } else {
                                    alert(result.message)
                                }
                            },
                            error ( ) {
                                $countdown.unbind("click").click(resend);
                                alert(ufo.lng("Connection error"))
                            }
                        }
                    })
                }

                function countdown ( ) {
                    $countdown.ms_countdown($countdown.data("time"), () => {
                        $countdown.html(ufo.lng("Resend"));
                        $countdown.unbind("click").click(resend);
                    })
                }

                countdown()
            }

            if (!$(".ufo-account").length) return;

            const page = ufo.url.slashes[ufo.url.slashes.length - 1];

            /** Menu */
            {
                let menu = $(".ufo-account-menu");

                function toggleMenu () {
                    menu = $(".ufo-account-menu");
                    if (menu.hasClass("active")) {
                        menu.removeClass("active").addClass("close");
                        setTimeout(() => menu.removeClass(
                            "close"
                        ), 450);
                    } else menu.addClass("active").removeClass("close");
                }

                menu.click(e =>
                    e.stopPropagation()
                );
                $("#open-menu").click(e => (
                    e.stopPropagation(), toggleMenu(e)
                ));
                $("html").click(() => {
                    if (menu.hasClass("active") && !menu.hasClass("close"))
                        toggleMenu()
                });

                $(".ufo-account-list-menu li").click(function (e) {
                    $(this).find(".submenu-action").toggleClass("active");
                    $(this).find(".submenu").slideToggle();
                });
                $(".ufo-account-list-menu .submenu .menu.active").each(function () {
                    const parent_element = $(this).parent().parent();
                    parent_element.parent().find(".submenu-action").addClass("active")
                    parent_element.slideDown();
                });
            }

            $(".ufo-acc-info-photo").click(function ( ) {
                const photoTag  = $(this);
                const inputFILE = $("<input type='file'>");

                inputFILE.click();
                inputFILE.bind("input", function ( ) {
                    const file = $(this)[0].files[0];
                    const form = new FormData();
                    const allowedSize  = ufo_data.upload_photo.size;
                    const allowedTypes = ufo_data.upload_photo.types;

                    form.append("callback", "ufo_account_member_photo");
                    form.append("file", file);

                    if (!allowedTypes.includes(file.type.split("/").pop())) {
                        alert(`${ufo.lng("File format is not allowed")}. ${
                            ufo.lng("Allowed formats : ")
                        }(${allowedTypes.join(", ")})`);
                        return false
                    }

                    if (file.size / 1024 / 1024 > allowedSize) {
                        alert(`${ufo.lng("File size is more than allowed.") + ufo.rlng(
                            "The maximum file size for uploading is %n %n", allowedSize, "MB"
                        )}`);
                        return false
                    }

                    $.fun().do({
                        name: "req",
                        param: {
                            data: form,
                            processData: false,
                            contentType: false,
                            dataType: "json",
                            xhr: ufo.xhr(
                                percent => photoTag.addClass("uploading").attr("data-percentage", percent)
                            ),
                            complete ( ) {
                                photoTag.removeClass("uploading")
                            },
                            done (result) {
                                if (result.message?.photo) {
                                    photoTag.find("img").attr("src", result.message.photo)
                                    $(".ufo-mini-profile img").attr("src", result.message.photo)
                                } else {
                                    alert(result.message.text ?? result.message)
                                }
                            },
                            error ( ) {
                                alert(ufo.lng("Connection error"))
                            }
                        }
                    })
                })
            });

            $("#ufo-acc-info").submit(function (event) {
                event.preventDefault();

                const fields = {};

                $(this).find(`[name]`).each(function () {
                    fields[$(this).attr("name")] = $(this).val()
                });

                $.fun().do({
                    name: "req",
                    param: {
                        data: {
                            callback: "ufo_account_save_info",
                            fields: fields
                        },
                        dataType: "json",
                        loader ( ) {
                            $.fun().do({
                                name: "ufo_toggle_loader"
                            });
                        },
                        complete ( ) {
                            $.fun().do({
                                name: "ufo_toggle_loader"
                            });
                        },
                        done (result) {
                            if (result.status !== 200)
                                alert(result.message)
                        },
                        error (xhr) {
                            alert($_this.lng("Connection error"))
                        }
                    }
                })
            });

            if (page === "comments") {
                ufo.do("paging", {
                    name: "comments-table-paging",
                    method: page => ufo.url.addParam(
                        "page", page, true
                    )
                })
            }
        },

        getParameter ( {address, key} ) {
            let url = new URL(address);
            let param = url.searchParams.get(key);
            return !param ? false : param;
        },

        img_error ( ) {
            $("img[data-error]").unbind("error").bind("error", function () {
                $(this).attr("src", $(this).data("error"));
                $(this).unbind("error");
                $(this).removeAttr("data-error");
            });
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

        remove_array ( data ) {
            return data.array.filter(function (k) {
                k[data.prop] !== data.value;
            });
        },

        remove_obj ( data ) {
            return data.obj.findIndex(x => x[data.prop] == [data.val] ) >= 0 ? data.obj.splice( data.obj.findIndex( x => x[data.prop] == [data.val] ), 1 ) : undefined;
        }

    }.init();
}).do();