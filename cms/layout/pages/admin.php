<?php

    $admin = $ufo->get_admin($_POST["admin"] ?? null) ?? [];

    if ( isset($_POST["admin"]) ) {
        if ( !$admin ) {
            $ufo->die($ufo->lng("Not found"));
        }
    }

    if ( isset($_POST["add"]) ) {
        $admin = [];
    }

    $join  = $ufo->space();
    
    $join .=
        $ufo->tag('div',
            $ufo->tag('img', null, [
                "src"   => $admin["photo"] ?? $db->meta("unknown_photo"),
                "class" => "event-none",
                "data-error" => $db->meta("unknown_photo")
            ]) .
            $ufo->tag('div',
                $ufo->tag('i', null, ["class" => "ufo-icon-semicircular rotating"]),
                ["class" => "e-user-cover-photo-loader font-size-30 dn"]
            ), ["class" => "new-manager-photo mt-10 mb-50 select-admin-photo cursor-pointer"]
        );

    $join .= $ufo->tag("label", $ufo->lng("name") . $ufo->single_input([
        "placeholder"  => $ufo->lng("name") . " *",
        "value"        => $admin["name"] ?? "",
        "name"         => "name",
        "class"        => "ufo-manager-input mt-10",
        "autocomplete" => "off"
    ]), ["class" => "font-size-15px"]);
    $join .= $ufo->tag("label", $ufo->lng("last name") . $ufo->single_input([
        "placeholder"  => $ufo->lng("last name") . " *",
        "value"        => $admin["last_name"] ?? "",
        "name"         => "last_name",
        "class"        => "ufo-manager-input mt-10",
        "autocomplete" => "off"
    ]), ["class" => "font-size-15px"]);
    $join .= $ufo->tag("label", $ufo->lng("login name") . $ufo->single_input([
        "placeholder"  => $ufo->lng("login name") . " *",
        "value"        => $admin["login_name"] ?? "",
        "name"         => "login_name",
        "class"        => "ufo-manager-input mt-10",
        "autocomplete" => "off"
    ]), ["class" => "font-size-15px"]);
    $join .= $ufo->tag("label", $ufo->lng("email") . $ufo->single_input([
        "placeholder"  => $ufo->lng("email") . " *",
        "value"        => $admin["email"] ?? "",
        "name"         => "email",
        "class"        => "ufo-manager-input rtl-to-ltr-placeholder mt-10",
        "autocomplete" => "off"
    ]), ["class" => "font-size-15px"]);
    $join .= $ufo->tag("label", $ufo->lng("password") . $ufo->single_input([
        "placeholder"  => $ufo->lng("password") . " *",
        "value"        => $admin["password"] ?? "",
        "name"         => "password",
        "class"        => "ufo-manager-input rtl-to-ltr-placeholder mt-10",
        "autocomplete" => "off"
    ]), ["class" => "font-size-15px"]);

    echo $join;