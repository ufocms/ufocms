/**
 * Copyright (c) 2022-2024 UFOCMS
 *
 * This software is licensed under the GPLv3 license.
 * See the LICENSE file for more information.
 */

ufo.register(null, function () {
    let $self;

    const $admin_url = ufo_data.admin_url;

    const template_step = [
        `<div class="ufo-step-circle-pages" data-page="1" style="display: block;"><div class="ufo-head-title"><h3 class="text">${ufo.lng("Extracting data")}</h3></div><div class="ufo-wizard-act"><img class="mt-10" src="${$admin_url}content/img/open-folder.gif"><div class="progress-bar-container mt-20" style="width:80%"><div class="progress-bar"></div></div></div></div>`,
        `<div class="ufo-step-circle-pages" data-page="2" style="display: block;"><div class="ufo-head-title"><h3 class="text">${ufo.lng("Installing")}</h3></div><div class="ufo-wizard-act"><img class="mt-10" src="${$admin_url}content/img/gears.gif"><div class="progress-bar-container mt-20" style="width:80%"><div class="progress-bar"></div></div></div></div>`,
        `<div class="ufo-step-circle-pages" data-page="3" style="display: block;"><div class="ufo-head-title"><h3 class="text">${ufo.lng("info")}</h3></div><div class="ufo-wizard-act db"><button class="btn btn-primary f-left close-window">${ufo.lng("back")}</button><br><br></div></div>`
    ];

    const error_step = `
        <i class="ufo-icon-folder-x" style="font-size: 30vh;"></i>
        <span class="mt-20 font-size-20px">%n</span>
    `;

    const $steps = ufo.do("circle-step", {
        steps: 3,
        container: ".ufo-wizard-step-container",
        items: [
            `<i class="ufo-icon-archive"></i>`,
            `<i class="ufo-icon-settings"></i>`,
            `<i class="ufo-icon-info"></i>`
        ]
    });

    const steps = {
        init () {
            $self = this;
            $self.unzip();
        },
        request (step, success) {
            ufo.req({
                data: {
                    callback: "ufo-install-wizard-" + $self.getPT(),
                    prevent_ajax: "plugins",
                    step: step,
                    plugin: $.cookie("ufo-install-wizard-" + $self.getPT()),
                    theme: $.cookie("ufo-install-wizard-" + $self.getPT()),
                },
                dataType: "json",
                done(result) {
                    if (result.status === 200) {
                        success(result);
                    } else {
                        $self.showERROR(step);
                    }
                },
                error(xhr) {
                    $self.showERROR(step);
                }
            })
        },
        getPT () {
            const plugin = "ufo-install-wizard-plugin";
            const theme = "ufo-install-wizard-template";
            const uPlugin = "ufo-update-wizard-plugin";
            const uTheme = "ufo-update-wizard-template";

            if (
                typeof $.cookie(plugin) !== "undefined" ||
                typeof $.cookie(uPlugin) !== "undefined"
            ) {
                return "plugin";
            } else if (
                typeof $.cookie(theme) !== "undefined" ||
                typeof $.cookie(uTheme) !== "undefined"
            ) {
                return "template";
            }
        },
        showERROR (step) {
            let $error = "";

            $steps.next();
            $steps.next();
            $steps.next();

            $(`.ufo-circle-step-items[data-item="1"] i, .ufo-circle-step-items[data-item="2"] i`).addClass("ufo-icon-x");

            switch (step) {
                case "unzip":
                    $error = ufo.lng("Error extracting data");
                    break;
                case "install":
                    $error = ufo.lng("Installation error");
                    break;
                case "info":
                    $error = ufo.lng("Error displaying information");
            }

            $(".ufo-wizard-act").html(error_step.replace("%n", $error));
            $(".ufo-step-circle-pages").show();
        },
        unzip () {
            $(`.ufo-step-circle-pages`).remove();
            $(`.ufo-wizard-step-container`).parent().append(template_step[0]);
            $self.request("unzip", function () {
                $self.install();
            });
        },
        install () {
            $steps.next();
            $(`.ufo-step-circle-pages`).remove();
            $(`.ufo-wizard-step-container`).parent().append(template_step[1]);
            $self.request("install", function (result) {
                $self.info(result);
            });
        },
        info (result) {
            $steps.next();

            $(`.ufo-step-circle-pages`).remove();
            $(`.ufo-wizard-step-container`).parent().append(template_step[2]);
            const container = $(`.ufo-wizard-act`);

            $(`.close-window`).unbind().click(function () {
                location.href = ufo_data.url_admin
            });

            if (typeof result.info !== "undefined") {
                $.each(result.info, (key, value) => {
                    if (typeof value.error === "undefined") {
                        container.append(`
                            <details class="ufo-accordion">
                                <summary>${ufo.lng(this.getPT() + " %n").replace("%n", key)} ${(value.mode === "update" ? "(" + ufo.lng("update") + ")" : "")}</summary>
                                <div class="p-5px">${$self.create_info(value)}</div>
                            </details>
                        `);
                    } else {
                        container.append(`
                            <details class="ufo-accordion">
                                <summary>${ufo.lng(this.getPT() + " %n").replace("%n", key)} ${(value.mode === "update" ? "(" + ufo.lng("update") + ")" : "")}</summary>
                                <div class="p-5px">${value.error}</div>
                            </details>
                        `);
                    }
                });
            } else {
                container.removeClass("db").css({
                    display: "flex",
                    justifyContent: "center",
                    alignItems: "center",
                    flexDirection: "column-reverse"
                });
                container.append(ufo.lng("<h3 class='mb-10'>" + ufo.lng("Error loading information") + "</h3>"));
            }

            setTimeout(() => {
                $.removeCookie("ufo-install-wizard-plugin", {path: "/"});
                $.removeCookie("ufo-install-wizard-template", {path: "/"});
                $.removeCookie("ufo-update-wizard-plugin", {path: "/"});
                $.removeCookie("ufo-update-wizard-template", {path: "/"});
            }, 500);
        },
        create_info (info) {
            let join = `<ul class="ufo-list-info mt-20">`;
            $.each(info, (k, v) => {
                if (k !== "mode") {
                    join += `<li><div class="title">${ufo.lng(k)}</div><div class="description">${v}</div></li>`;
                }
            });
            return join + "</ul>";
        }
    };

    steps.init();
})