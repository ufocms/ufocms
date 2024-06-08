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
        `<div class="ufo-step-circle-pages" data-page="3" style="display: block;"><div class="ufo-head-title"><h3 class="text">${ufo.lng("info")}</h3></div><div class="ufo-wizard-act db"><button class="btn btn-primary f-left close-window">${ufo.lng("back")}</button><div class="width-100-cent flex flex-center align-center" style="height: calc(100% - 70px);font-size: 18px;"></div><br><br></div></div>`
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
        init() {
            $self = this;
            $self.unzip();
        },
        request(step, success) {
            ufo.req({
                data: {
                    callback: "ufo-update-system",
                    prevent_ajax: "plugins",
                    step: step
                },
                dataType: "json",
                done(result) {
                    if (result.status === 200) {
                        success(result);
                    } else {
                        $self.showERROR(step);
                    }
                },
                error: xhr => $self.showERROR(step)
            })
        },
        unzip() {
            $(`.ufo-step-circle-pages`).remove();
            $(`.ufo-wizard-step-container`).parent().append(template_step[0]);
            $self.request("unzip", function () {
                $self.install();
            });
        },
        install() {
            $steps.next();
            $(`.ufo-step-circle-pages`).remove();
            $(`.ufo-wizard-step-container`).parent().append(template_step[1]);
            $self.request("install", function () {
                $self.info();
            });
        },
        info() {
            $steps.next();

            $(`.ufo-step-circle-pages`).remove();
            $(`.ufo-wizard-step-container`).parent().append(template_step[2]);
            const container = $(`.ufo-wizard-act`);

            $(`.close-window`).unbind().click(function () {
                location.href = $admin_url
            });

            container.find("div").html(ufo.lng("The update was completed successfully"));
        },
        showERROR(step) {
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
        }
    };

    steps.init();
});