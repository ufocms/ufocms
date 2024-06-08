<?php $ufo->header() ?>

    <div class="width-100-cent height-100-cent flex flex-center align-center">

        <form class="ufo-form">

            <?php
                $fields = $ufo->do_work("ufo_" . $_["ufo_this_form"] . "_form_fields", $_["ufo_this_form"]);
                if (!$fields && $fields !== NULL)
                    $ufo->fire("ufo_default_form_fields", $_["ufo_this_form"]);
            ?>

        </form>

    </div>

<?php $ufo->footer() ?>