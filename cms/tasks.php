<?php @ob_start(); ob_clean();

const UFO_TASK = true;

$admin_page = true;

require "include" . DIRECTORY_SEPARATOR . "load.php";

try {

    /**
     * Run template
     */
    (new UFO_Template("tasks"))->front_init();

    /**
     * KEY -> task edit
     */
    if ($ufo->isset_get("key")) {

        /**
         * Check connect UFO_Tasks
         */
        if (!(new UFO_Task("run", $_GET["key"])))
            $ufo->die(403);

    } else $ufo->die(403);

} catch (Exception $e) {}

ob_end_flush(); ?>