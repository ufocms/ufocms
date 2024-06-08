<?php

if ($ufo->check_login_member())
    $ufo->logout_member();

$ufo->redirect($ufo->web_link());