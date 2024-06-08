<?php

    $data = [];
    if (isset($_POST['type']) && $_POST['type'] == "edit") {
        $data = $db->get("members", "uid", base64_decode($_POST['mem']))[0];
    }

    $join  = $ufo->space();

    $join .=
        $ufo->tag('div',
            $ufo->tag('img', null, [
                "src"   => $data["photo"] ?? $db->meta("unknown_photo"),
                "class" => "event-none",
                "data-error" => $db->meta("unknown_photo")
            ]) .
            $ufo->tag('div',
                $ufo->tag('i', null, ["class" => "ufo-icon-semicircular rotating"]),
                ["class" => "e-user-cover-photo-loader font-size-30 dn"]
            ), ["class" => "new-user-photo mt-10 mb-50 select-user-photo cursor-pointer"]
        );

    $join .= $ufo->single_input([
        "placeholder"  => $ufo->lng("username") . " *",
        "value"        => $data["username"] ?? "",
        "name"         => "username",
        "class"        => "member-input",
        "autocomplete" => "off"
    ]);
    $join .= $ufo->single_input([
        "placeholder"  => $ufo->lng("email"),
        "value"        => $data["email"] ?? "",
        "name"         => "email",
        "class"        => "member-input rtl-to-ltr-placeholder",
        "autocomplete" => "off",
        "data-empty"   => true
    ]);
    $join .= $ufo->single_input([
        "placeholder"  => $ufo->lng("phone"),
        "value"        => $data["no"] ?? "",
        "name"         => "no",
        "class"        => "member-input rtl-to-ltr-placeholder",
        "autocomplete" => "off",
        "data-empty"   => true
    ]);
    $join .= $ufo->single_input([
        "placeholder"  => $ufo->lng("password") . " *",
        "value"        => $data["password"] ?? "",
        "name"         => "password",
        "class"        => "member-input rtl-to-ltr-placeholder",
        "autocomplete" => "off"
    ]);
    $join .= $ufo->single_input([
        "placeholder"  => $ufo->lng("name"),
        "value"        => $data["name"] ?? "",
        "name"         => "name",
        "autocomplete" => "off",
        "class"        => "member-input",
        "data-empty"   => true
    ]);
    $join .= $ufo->single_input([
        "placeholder"  => $ufo->lng("last name"),
        "value"        => $data["last_name"] ?? "",
        "name"         => "last_name",
        "autocomplete" => "off",
        "class"        => "member-input",
        "data-empty"   => true
    ]);

    foreach ($ufo->get_array("ufo-edit-member-input") as $item) {
        $join .= $item;
    }

    echo $join;