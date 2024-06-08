/**
 * Copyright (c) 2022-2024 UFOCMS
 *
 * This software is licensed under the GPLv3 license.
 * See the LICENSE file for more information.
 */

if (typeof ufo_code_error !== "undefined") {

    document.title = ufo_code_error.title

    $("body").css({
        "overflow": "scroll"
    })

    $($(`.ufo-code-container li`).get(ufo_code_error.line - 1)).css({
        "background": "red",
        "color": "white"
    });

}