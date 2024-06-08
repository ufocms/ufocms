/**
 * Copyright (c) 2022-2024 UFOCMS
 *
 * This software is licensed under the GPLv3 license.
 * See the LICENSE file for more information.
 */

/**
 * Prototypes
 */
String.prototype.removeLast = function (n = 1) {
    let string = this.split('');
    string.length = string.length - n;
    return string.join('');
};
String.prototype.copy = function () {
    let hiddenClipboard = $('#_hiddenClipboard_');
    if (!hiddenClipboard.length) {
        $('body').append('<textarea readonly style="position:absolute;top: -9999px;" id="_hiddenClipboard_"></textarea>');
        hiddenClipboard = $('#_hiddenClipboard_');
    }
    hiddenClipboard.html(this);
    hiddenClipboard.select();
    document.execCommand('copy');
    document.getSelection().removeAllRanges();
    hiddenClipboard.remove();
};
String.prototype.trim = function (chars) {
    let str = this, start = 0, end = str.length;

    if (chars === undefined)
        chars = " ";

    while (start < end && chars.indexOf(str[start]) >= 0)
        ++start;

    while (end > start && chars.indexOf(str[end - 1]) >= 0)
        --end;

    return (start > 0 || end < str.length) ? str.substring(start, end) : str;
};
Array.prototype.removeObj = function (prop,val) {
    const pos = this.findIndex(x => x[prop] === val);
    if (pos >= 0) return this.splice(pos, 1);
};

/**
 * UFO Jquery plugins
 */
(function ($) {

    $.console = function () {
        return {
            print ( ...args ) {
                queueMicrotask (console.log.bind(console, ...args));
            },
            clear ( ) {
                queueMicrotask (console.clear.bind(console));
            },
            group ( ...args ) {
                queueMicrotask (console.group.bind(console, ...args));
            },
            groupEnd ( ...args ) {
                queueMicrotask (console.groupEnd.bind(console, ...args));
            }
        }
    };

    $.ufo_dialog = function ({title, content, options, done}) {
        try {
            if (typeof arguments[0] === "string")
                title = arguments[0];

            if (typeof title === "undefined")
                title = "";
            if (typeof content === "undefined")
                content = "";

            options = $.extend({
                okText: ufo.lng("Close"),
                dark  : ufo.admin_theme === "dark",
                cancelText: ufo.lng("Cancel")
            }, typeof options === "object" ? options : {});

            if (typeof options.cancel !== "undefined" && options.cancel) {
                options.buttons = duDialog.OK_CANCEL
                if (typeof options.cancel === "string")
                    options.cancelText = options.cancel
            }

            const d = new duDialog(title.toString(), content, options);

            if (typeof options.layer === "string") {
                if (options.layer === "above")
                    $("body .du-dialog:last-child").css("z-index", 0x77359400);
            }

            if (options.allowSearch) {
                $(`.du-dialog:last-child input.dlg-search`).attr("placeholder", ufo.lng("Search"));
            }

            if (typeof done === "function") {
                done();
                return d;
            }
        } catch ( e ) {}
    };

    $.fn.ufo_paging = function (options) {
        let default_options = {
            pageSize: 4,
            increaseSlide: 5,
            startPage: 0,
            numberPage: 0,
            items: ".items",
            paging: null,
            prev: "<",
            next: ">"
        };
        let countLI = 0;
        let merged = {...default_options, ...options};

        merged.items = $(merged.items);

        let pageCount = merged.items.length / merged.pageSize;
        let totalSlidePPage = Math.floor(pageCount / merged.increaseSlide);

        if (merged.paging === null) {
            $(this).after(`<div class="paging"></div>`);
        } else {
            $(merged.paging).addClass("paging");
        }
        $(".paging").empty();
        const pagingContainer = $(".paging");

        for (let i = 0; i < pageCount; i++) {
            pagingContainer.append('<li>' + (i + 1) + '</li> ');
            if (i > merged.pageSize) {
                pagingContainer.find("li").eq(i).hide();
            }
        }

        let prev = $("<li/>").addClass("prev").html(merged.prev).click(function () {
            merged.startPage -= 5;
            merged.increaseSlide -= 5;
            merged.numberPage--;
            slide();
        });
        prev.hide();

        let next = $("<li/>").addClass("next").html(merged.next).click(function () {
            merged.startPage += 5;
            merged.increaseSlide += 5;
            merged.numberPage++;
            slide();
        });

        pagingContainer.find("li").each(function () {
            if (!$(this).hasClass("prev") || !$(this).hasClass("next")) countLI++;
        });

        if (countLI >= merged.increaseSlide) {
            pagingContainer.prepend(prev).append(next);
        }

        pagingContainer.find("li").first().find("a").addClass("active");

        let slide = function (sens) {
            pagingContainer.find("li").hide();

            for (let t = merged.startPage; t < merged.increaseSlide; t++) {
                pagingContainer.find("li").eq(t + 1).show();
            }
            if (merged.startPage == 0) {
                next.show();
                prev.hide();
            } else if (merged.numberPage == totalSlidePPage) {
                next.hide();
                prev.show();
            } else {
                next.show();
                prev.show();
            }
        }

        let showPage = function (page) {
            merged.items.hide();
            merged.items.each(function (n) {
                if (n >= merged.pageSize * (page - 1) && n < merged.pageSize * page)
                    $(this).show();
            });
        }

        showPage(1);

        pagingContainer.find("li.next").click();
        pagingContainer.find("li.prev").click();

        pagingContainer.find("li").eq(0).addClass("active");
        pagingContainer.find("li").eq(1).addClass("active");

        pagingContainer.find("li").click(function () {
            if ($(this).hasClass("prev") || $(this).hasClass("next")) return false;

            pagingContainer.find("li").removeClass("active");
            $(this).addClass("active");
            showPage(parseInt($(this).text()));
        });

        return this;
    };

    $.fn.copy = function () {
        let val = false;
        if (this.is("select") || this.is("textarea") || this.is("input"))
            val = this.val();
        else
            val = this.text();
        val.copy();
    };

    $.fullScreen = function () {
        let viewer = $("html")[0];
        let screen = viewer.mozRequestFullScreen || viewer.webkitRequestFullscreen || viewer.requestFullscreen;
        screen.call(viewer);
    };

    $.ufo_range = function (config) {
        config  = $.extend({
            min: 0,
            max: 100,
            start: 0,
            title: "range",
            change: ()=>{}
        }, config);

        const range    = $("<div>");
        const inputVal = $("<input>");
        const input    = $("<input>");
        const span     = $("<span>");

        range.addClass("ufo-range-container");

        inputVal.val(config.start);

        input.attr("type", "range");
        input.attr("min", parseInt(config.min));
        input.attr("max", parseInt(config.max));
        input.val(config.start);
        input.addClass("ufo-range-slider");

        span.html(config.title);

        range.append(inputVal);
        range.append(input);
        range.append(span);

        input.bind("input", function ( ) {
            inputVal.val(parseInt($(this).val()));
            if ( typeof config.change === "function" ) {
                config.change.bind(this)();
            }
        });

        return range;
    };

    $.fn.ufo_just_number = function () {
        $(this).on("change keyup paste keydown input focus", function (e) {
            this.value = this.value.replace(/[^0-9]/g, '')
        })
    };

    $.compareVersion = function (v1, comparator, v2) {
        comparator = comparator === '=' ? '==' : comparator;
        if (['==', '===', '<', '<=', '>', '>=', '!=', '!=='].indexOf(comparator) === -1) {
            throw new Error('Invalid comparator. ' + comparator);
        }
        let v1parts = v1.split('.'), v2parts = v2.split('.');
        let maxLen = Math.max(v1parts.length, v2parts.length);
        let part1, part2;
        let cmp = 0;
        for (let i = 0; i < maxLen && !cmp; i++) {
            part1 = parseInt(v1parts[i], 10) || 0;
            part2 = parseInt(v2parts[i], 10) || 0;
            if (part1 < part2) {
                cmp = 1
            }
            if (part1 > part2) {
                cmp = -1
            }
        }
        return eval('0' + comparator + cmp);
    };

    $.fn.scrollOn = function (callback) {
        let element  = $(this);
        let stop     = false;
        let actions  = {
            stop ( ) { stop = true },
            start ( ) { stop = false }
        };
        $(window).scroll(function () {
            if ( !stop ) {
                let posElement    = element.position();
                let top           = posElement.top;
                let height        = element.height();
                let scrollTop     = $(window).scrollTop();
                let windowHeight  = $(window).height();
                let wScrollTop    = scrollTop + (windowHeight / 2.5) + (height / 3);
                if ( wScrollTop >= top ) { callback.bind(actions)(wScrollTop, top) }
            }
        });
    };

    $.replaceText = function (target, replace, selector = "body") {
        $(selector + `:contains(${target})`).html(
            $(selector + `:contains(${target})`).html().replace(target, replace)
        );
    };

    $.fn.ms_countdown = function (time, timeout) {
        let timer = () => {
            let minutes = Math.floor(time / 60);
            let seconds = time % 60;

            minutes = minutes < 10 ? "0" + minutes : minutes;
            seconds = seconds < 10 ? "0" + seconds : seconds;

            $(this).text(minutes + ":" + seconds);

            if (time <= 0) {
                clearInterval($timer);
                $(this).text("00:00");
                if (typeof timeout === "function")
                    timeout()
            }
        }; timer();
        let $timer = setInterval(() => {
            time--; timer();
        }, 1000);
    };

    $.fn.group_checkbox = function () {
        const checkboxes = $(this);
        checkboxes.bind("input", function () {
            checkboxes.not(this).prop("checked", false)
        });
    };

    $.fn.ufo_accordion = function () {
        const accordion = $(this);
        accordion.find(".ufo-accordion-top").unbind("click").click(function (e) {
            const $this = $(this), $next = $this.next();

            $next.slideToggle();
            $this.parent().toggleClass("open");

            accordion.find(".ufo-accordion-content").not($next).slideUp().parent().removeClass("open");
        })
    };

    $.fn.animateRotate = function (angle, duration, easing, complete) {
        const args = $.speed(duration, easing, complete);
        const step = args.step;
        return this.each(function (i, e) {
            args.complete = $.proxy(args.complete, e);
            args.step = function (now) {
                $.style(e, "transform", `rotate(${now}deg)`);
                if (step) return step.apply(e, arguments);
            };

            $({deg: 0}).animate({deg: angle}, args);
        });
    };

    $.fn.ToggleClass = function (classes) {
        classes.toString().split(" ").forEach(c =>
            $(this).toggleClass(c)
        );
        return $(this)
    };

    function ufo_scroll () {
        this.id = arguments[0];
        this.type = arguments[1] == 'h' ? 'h' : 'v';
        this.width = typeof arguments[2] == 'number' ? arguments[2] : null;
        this.height = typeof arguments[3] == 'number' ? arguments[3] : null;
        this.$obj = null;
        this.is_scroll = false;
        this.start_pos = null;
        this.init();
    }

    ufo_scroll.prototype.init = function () {
        this.$obj = $(this.id);
        if (this.$obj.length == 1) {
            this.$obj.addClass('ufo_scroll');
            if (this.width != null) this.$obj.css('width', this.width);
            if (this.height != null) this.$obj.css('height', this.height);
            this.bindEvent();
        }
    };

    ufo_scroll.prototype.bindEvent = function () {
        var _this = this;
        this.$obj.on('mousedown', function (event) {
            _this.is_scroll = true;
            _this.start_pos = {
                base_x: _this.$obj.scrollLeft(),
                base_y: _this.$obj.scrollTop(),
                x: event.pageX,
                y: event.pageY
            };
            _this.$obj.css('cursor', 'move');
        });
        $(document).on('mouseup', function () {
            _this.is_scroll = false;
            _this.$obj.css('cursor', 'default');
        });
        $(document).on('mousemove', function (event) {
            if (_this.is_scroll) {
                var dist;
                if (_this.type == 'h') {
                    var x = event.pageX;
                    dist = _this.start_pos.base_x - x + _this.start_pos.x;
                    _this.$obj.scrollLeft(dist);
                } else {
                    var y = event.pageY;
                    dist = _this.start_pos.base_y - y + _this.start_pos.y;
                    _this.$obj.scrollTop(dist);
                }
            }
        });
        this.$obj.get(0).addEventListener('touchstart', function (event) {
            if (event.targetTouches.length == 1) {
                //event.preventDefault();
                _this.is_scroll = true;
                _this.start_pos = {
                    base_x: _this.$obj.scrollLeft(),
                    base_y: _this.$obj.scrollTop(),
                    x: event.targetTouches[0].pageX,
                    y: event.targetTouches[0].pageY
                };
                _this.$obj.css('cursor', 'move');
            }
        });
        this.$obj.get(0).addEventListener('touchend', function (event) {
            _this.is_scroll = false;
            _this.$obj.css('cursor', 'default');
        });
        this.$obj.get(0).addEventListener('touchmove', function (event) {
            if (_this.is_scroll) {
                if (event.targetTouches.length == 1) {
                    //event.preventDefault();
                    var dist;
                    if (_this.type == 'h') {
                        var x = event.targetTouches[0].pageX;
                        dist = _this.start_pos.base_x - x + _this.start_pos.x;
                        _this.$obj.scrollLeft(dist);
                    } else {
                        var y = event.targetTouches[0].pageY;
                        dist = _this.start_pos.base_y - y + _this.start_pos.y;
                        _this.$obj.scrollTop(dist);
                    }
                }
            }
        });
    };

    ;(function ($) {
        $.fn.extend({
            ufo_scroll: function (options) {
                const defaults = {
                    type: "h",
                    width: null,
                    height: null
                };
                const opts = $.extend(defaults, options);
                return this.each(function () {
                    new ufo_scroll(this, opts.type, opts.width, opts.height);
                    return false;
                });
            }
        });
    })(jQuery);

})(jQuery);

/**
 * Global options
 */
(function () {
    const saver = {}, callbacks = {};

    window.ufo = {
        dir: $("html").attr("dir"),
        admin_theme: $("body").data("theme"),

        lng: text => ufo.do("lng", text),
        rlng: (text, ...values) => {
            text = ufo.lng(text);
            for (let i = 0; i < values.length; i++)
                text = text.replace("%n", values[i] ?? "%n");
            return text
        },

        os: {
            system: $.platform.original,
            mobile: $.platform.type === "mobile",
            browser: {
                name: $.browser.original,
                version: $.platform.version.original,
                build: $.browser.version.revision
            }
        },

        save: (name, value) => saver[name] = value,
        get : name => typeof saver[name] !== "undefined" ? saver[name] : null,
        push: function (name, value) {
            if (Array.isArray(saver[name]))
                saver[name].push(value);
        },

        isNULL: function (target) {
            if (Array.isArray(target))
                return target.length === 0

            if (typeof target === "object")
                return (function () {
                    let c = 0;
                    $.each(target, (k, v) => c++);
                    return (c === 0);
                }());

            if (typeof target === "string")
                return target.toString().split("").length === 0;
        },
        isJSON: function (string) {
            try {
                return JSON.parse(string)
            } catch (e) {}
            return false
        },
        objIsEmpty: function (obj) {
            for (const prop in obj) {
                if (Object.hasOwn(obj, prop)) {
                    return false
                }
            }
            return true
        },
        isDom: function (target) {
            if (target instanceof HTMLCollection && target.length) {
                for (let a = 0, len = target.length; a < len; a++) {
                    if (!checkInstance(target[a])) {
                        return false;
                    }
                }
                return true;
            } else return checkInstance(target);

            function checkInstance(elem) {
                if ((elem instanceof jQuery && elem.length) || elem instanceof HTMLElement)
                    return true;
                return false;
            }
        },
        isTrue: function (target) {
            if (typeof target === "boolean")
                return target;

            if (typeof target === "string")
                return target === "true";

            return Boolean(target);
        },
        validate: function (str) {
            // Email
            const Email = String(str).toLowerCase().match(/^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/);
            if (Email) return "email";

            return false;
        },
        count: function (target) {
            let c = 0;
            if (typeof target === "object")
                return Object.keys(target).length;
            else if (Array.isArray(target))
                return target.length;
            if (typeof target === "string")
                return target.length;
            return c
        },
        RHash: function (length = 50) {
            let hash = "", possible = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";
            for (let i = 0; i < length; i++) {
                hash += possible.charAt(Math.floor(Math.random() * possible.length));
            } return "0x" + hash;
        },

        GET: function (name) {
            if (typeof URLSearchParams !== "undefined") {
                return new URLSearchParams(window.location.search).get(name);
            } else {
                name = name.replace(/[\[]/, "\\\[").replace(/[\]]/, "\\\]");
                let regex = new RegExp("[\\?&]" + name + "=([^&#]*)"),
                    results = regex.exec(location.search);
                return results == null ? "" : decodeURIComponent(results[1].replace(/\+/g, " "));
            }
        },

        url: {
            addParam (k, v = null, reload = false) {
                if (typeof k === "object") {
                    $.each(k, (key, value) =>
                        ufo.url.addParam(key, value, false)
                    );
                    if (reload) location.reload();
                    return;
                }

                if (history.pushState) {
                    let searchParams = new URLSearchParams(window.location.search);
                    searchParams.set(k, v);

                    let new_url = window.location.protocol + "//" + window.location.host + window.location.pathname + '?' + searchParams.toString();

                    if (reload)
                        location.href = new_url;
                    else
                        window.history.pushState({
                            path: new_url
                        }, '', new_url);
                }
            },
            removeParam (k, reload = false) {
                if (Array.isArray(k)) {
                    k.forEach(value =>
                        ufo.url.removeParam(value, false)
                    );
                    if (reload) location.reload();
                    return;
                }

                let url = new URL(window.location.href);

                url.searchParams.delete(k);

                let newUrl = url.href;

                if (reload)
                    return location.reload();

                window.history.pushState({
                    path: newUrl
                }, '', newUrl)
            },
            slashes: location.pathname.toString().trim("/").split("/")
        },

        exert: function (name, method, script) {
            if (typeof name !== "string") {
                $.error("Exert : Please enter the name as a string");
                return false
            }

            if (typeof method !== "function") {
                $.error("Exert : Please enter the method as a function");
                return false
            }

            script = ufo_script_is_running ?? (script ?? null);

            if (typeof callbacks[name] === "undefined") {
                callbacks[name] = script === null ? [] : {}
            }

            if (typeof method === "function") {
                if (script === null && Array.isArray(callbacks[name])) {
                    callbacks[name].push(method)
                } else if (typeof callbacks[name] === "object") {
                    if (typeof callbacks[name][script] !== "function") {
                        callbacks[name][script] = method
                    }
                }
            }
        },
        fire : function (name, ...args) {
            const $callbacks = callbacks[name] ?? [];
            if (Array.isArray($callbacks)) {
                $callbacks.map(method => typeof method === "function" ? method(...args) : null)
            } else if (typeof $callbacks === "object") {
                $.each($callbacks, (k, v) => typeof v === "function" ? v(...args) : null)
            }
        },

        apply: (obj, method) => {
            if (typeof obj === "string" || obj === null) obj = {
                name: obj ?? "0x" + Math.round(Math.random() * 1000), method
            };
            return $.fun().apply(obj)
        },
        do: (obj, param) => {
            if (typeof obj === "string") obj = {
                name: obj, param
            };
            return $.fun().do(obj)
        },

        register: (name, method) => ufo.do({
            name: "register",
            param: {name: name ?? "0x" + Math.round(Math.random() * 1000), method}
        }),

        sleep: delay => {
            const start = new Date().getTime();
            while (new Date().getTime() < start + delay) ;
        },

        req: obj => ufo.do("req", obj),
        xhr: (upload, download) => () => {
            let xhr = new window.XMLHttpRequest();

            xhr.upload.addEventListener("progress", function (evt) {
                if (evt.lengthComputable) {
                    let percentComplete = evt.loaded / evt.total;
                    if (upload instanceof Function)
                        upload(Math.round(percentComplete * 100));
                }
            }, false);

            xhr.addEventListener("progress", function (evt) {
                if (evt.lengthComputable) {
                    let percentComplete = evt.loaded / evt.total;
                    if (download instanceof Function)
                        download(Math.round(percentComplete * 100));
                }
            }, false);

            return xhr;
        },

        click: (element, fn) => $(element).unbind("click").click(fn),

        freeze: obj => {
            Object.freeze(obj);
            for (const key in obj)
                if (obj.hasOwnProperty(key) && typeof obj[key] === "object")
                    ufo.freeze(obj[key]);
        },

        shortcodes: {
            find (text) {
                // Pattern to match shortcodes, capturing the shortcode name, optional attributes, and optional content
                let pattern = /\[(\w+)(.*?)\](?:([^[]*?)\[\/\1\])?/gs, match;

                const
                    shortcodes = [],
                    extractAttrs = attrs => {
                        const attributes = {};
                        const pattern = /(\w+)=("[^"]*"|\[[^\]]*\]|{[^}]*}|\d+(?:,\d+)*(?:,"[^"]*")*(?:,\d+)*)/g;
                        let match;

                        while ((match = pattern.exec(attrs)) !== null) {
                            const key = match[1];
                            let value = match[2].trim().replace(/^"|"$/g, '');

                            try {
                                // Attempt to parse JSON values
                                value = JSON.parse(match[2]);
                            } catch (e) {
                                if (value.includes(',')) {
                                    // Handle lists by splitting on commas and trimming each element
                                    value = value.split(',').map(item => {
                                        const trimmedItem = item.trim();
                                        return isNaN(trimmedItem) ? trimmedItem.replace(/^"|"$/g, '') : +trimmedItem;
                                    });
                                }
                            }

                            attributes[key] = value;
                        }

                        return attributes;
                    };

                // Iterate over all matches
                while ((match = pattern.exec(text)) !== null) {
                    const
                        shortcode = match[0],
                        name = match[1],
                        attrs = match[2] ? match[2].trim() : "",
                        content = match[3] || ""; // Content is optional

                    shortcodes.push({
                        shortcode, name,
                        attrs: attrs ? extractAttrs(attrs) : {},
                        content
                    })
                }

                return shortcodes;
            },
            render (name, fn) {
                if (typeof fn !== "function")
                    throw "Shortcodes fn not a function";

                const shortcodes  = this.find($("html").text());
                const $shortcodes = [];

                shortcodes.forEach(shortcode => {
                    if (shortcode.name === name)
                        $shortcodes.push(shortcode)
                });

                $shortcodes.forEach(value => fn({
                    ...value,
                    replace: function (string) {
                        let $place = $(`*:not(html):contains('${value.shortcode}')`);

                        if (!$place.length) {
                            /**
                             * Try again to find this shortcode
                             * @type {*|jQuery|HTMLElement}
                             */
                            $place = $(`*:not(html):contains("${value.shortcode}")`);
                            if (!$place.length)
                                return;
                        }

                        $place = $($place[$place.length - 1]);

                        let $text = $place.html().toString().replaceAll("\n", "");

                        $($place).html($text.replace(
                            value.shortcode, string.toString()
                        ));
                    }
                }));
            }
        },

        hasElement (element, fn, ...args) {
            if (document.querySelector(element)?.isConnected)
                return fn.bind($(document.querySelector(element)))(...args);
            return false;
        },
        whenDefinedElement (element, fn, ...args) {
            if (typeof element !== "string")
                return false;

            let interval = setInterval(() => {
                if (document.querySelector(element)?.isConnected) {
                    clearInterval(interval);
                    fn.bind($(document.querySelector(element)))(...args)
                }
            }, 100);
        }
    };

    ufo.freeze(window.ufo)
}());

/**
 * Json2Html & Html2Json
 */
ufo.apply("json2html", function (json) {
    const newTN = (text) => document.createTextNode(text);
    const newEL = (tag, attr) => {
        const EL = document.createElement(typeof tag === "undefined" ? "div" : (tag.length === 0 ? "div" : tag));
        if (attr) Object.entries(attr).forEach(([k, v]) => EL.setAttribute(k, v));
        return EL;
    };
    const JSON2DF = (data, PAR = new DocumentFragment()) => {
        const CH = typeof data === "string" ? newTN(data) : newEL(data.tag, data.attrs)
        if (data.html) {
            if (Array.isArray(data.html)) {
                data.html.forEach(d => JSON2DF(d, CH));
            } else {
                JSON2DF(data.html, CH);
            }
        }
        PAR.append(CH);
        return PAR;
    };
    const fakeElement = $(`<div></div>`);
    fakeElement.html(JSON2DF(json));
    return fakeElement.html();
});
ufo.apply("html2json", function (element) {
    let treeObject = {};

    element = `<div>${element}</div>`;
    element = $(element.replaceAll("\n", ""))[0];

    function treeHTML(element, object) {
        object["tag"] = element.nodeName.toString().toLowerCase();

        if (object["tag"] !== "parsererror") {
            let nodeList = element.childNodes;

            /**
             * Content
             */
            if (nodeList != null) {
                if (nodeList.length) {
                    object["html"] = [];
                    for (let i = 0; i < nodeList.length; i++) {
                        if (nodeList[i].nodeType == 3) {
                            if (nodeList[i].nodeValue.replace(/\s+/gm, "").length !== 0) {
                                object["html"].push(nodeList[i].nodeValue);
                            }
                        } else {
                            object["html"].push({});
                            treeHTML(nodeList[i], object["html"][object["html"].length - 1]);
                        }
                    }
                }
            }

            /**
             * Attributes
             */
            if (element.attributes != null) {
                if (element.attributes.length) {
                    object["attrs"] = {};
                    for (let i = 0; i < element.attributes.length; i++) {
                        object["attrs"][element.attributes[i].nodeName] = element.attributes[i].nodeValue;
                    }
                }
            }
        }
    }

    treeHTML(element, treeObject);

    return treeObject;
});

/**
 * Steps
 */
ufo.apply("circle-step", $param => {
    $param = $.extend({
        steps: 4,
        items: [1, 2, 3, 4],
        container: ".ufo-steps-container",
        prev: ".prev",
        next: ".next",
        finish: () => {}
    }, $param);
    const container = $($param.container);
    const callbacks = {
        next() {
            if ($param.current >= $param.steps) {
                $($param.next).attr("disabled", true);
                $param.finish();
            } else {
                change_step($param.current, $param.current + 1);
                $param.current++;
                $($param.prev).attr("disabled", false);
            }
        },
        prev() {
            if ($param.current >= 2) {
                $($param.next).attr("disabled", false);
                change_step($param.current, $param.current - 1);
                $param.current--;
                if ($param.current === 1) {
                    $($param.prev).attr("disabled", true);
                }
            }
        }
    };

    $param.current = 1;

    function init() {
        let items = "";

        $.each($param.items, (k, v) => {
            items += `<li class="ufo-circle-step-items" data-item="${k + 1}">${v}</li>`
        });

        container.html(`<div class="ufo-circle-step-items"><div class="ufo-circle-steps-empty"></div><div class="ufo-circle-steps-complete"></div><ul>${items}</ul></div>`);

        $(`.ufo-step-circle-pages[data-page="${$param.current}"]`).show();
        $(`.ufo-circle-step-items[data-item="${$param.current}"]`).addClass("active");

        $($param.next).unbind().click(function () {
            callbacks.next();
        });
        $($param.prev).unbind().click(function () {
            callbacks.prev();
        });
    }

    function change_step(active, $new) {
        $(`.ufo-step-circle-pages[data-page="${active}"]`).hide();
        $(`.ufo-step-circle-pages[data-page="${$new}"]`).show();

        const activeStep = $(".ufo-circle-step-items.active");
        activeStep.removeClass("active");

        const newActiveStep = $(`.ufo-circle-step-items[data-item="${$new}"]`);
        newActiveStep.addClass("active");

        const p = (100 / ($param.steps - 1)) * ($new - 1);
        $(".ufo-circle-steps-complete").css({width: p + "%"});

        if (active < $new) {
            activeStep.addClass("complete");
            activeStep.html("<i class='ufo-icon-check'></i>");
        }

        if (newActiveStep.hasClass("complete")) {
            newActiveStep.removeClass("complete");
            newActiveStep.html($param.items[$new - 1]);
        }
    }

    init();
    return callbacks;
});