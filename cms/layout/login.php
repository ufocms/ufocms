<div class="login-container flex flex-center flex-direction-column align-center">

    <?= $_["ufo_admin_login_logo"] ?>

    <form class="form-login flex flex-center flex-direction-column align-center">

        <?php
            $ufo->loop_inputs("admin_login", "<div class='space-inputs'></div>");

            echo $ufo->tag("div", null, [
                "id" => "ufo-error-text-login",
                "class" => "width-100-cent flex align-center mt-5 mb-10"
            ]);

            echo $ufo->btn($ufo->lng("login"), "width-100-cent btn-login", "btn btn-primary", [
                "type" => "submit"
            ]);
        ?>

    </form>

</div>