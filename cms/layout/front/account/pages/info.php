<?php $_["ufo_acc_page_title"] = $ufo->lng("Account information");

if ($ufo->is_json($db->account_upload_photo)) {
    $config = json_decode($db->account_upload_photo, true);

    $ufo->add_localize_script("ufo_data", "upload_photo", [
        "types" => $config["types"],
        "size"  => $config["size"]
    ]);
}

$ufo->add_array("ufo_account_info_inputs", [
    "class" => "form-control",
    "type"  => "text",
    "name"  => "name",
    "value" => $_["this_member"]["name"],
    "required"    => true,
    "placeholder" => $ufo->lng("name")
]);
$ufo->add_array("ufo_account_info_inputs", [
    "class" => "form-control",
    "type"  => "text",
    "name"  => "last_name",
    "value" => $_["this_member"]["last_name"],
    "required"    => true,
    "placeholder" => $ufo->lng("last name")
]);
$ufo->add_array("ufo_account_info_inputs", [
    "class" => "form-control",
    "type"  => "email",
    "name"  => "email",
    "value" => $_["this_member"]["email"],
    "required"    => true,
    "placeholder" => $ufo->lng("email"),
    (!empty($_["this_member"]["email"]) ? "readonly" : null) => null
]);
$ufo->add_array("ufo_account_info_inputs", [
    "class" => "form-control",
    "type"  => "text",
    "name"  => "no",
    "value" => $_["this_member"]["no"],
    "required"    => true,
    "placeholder" => $ufo->lng("phone number"),
    (!empty($_["this_member"]["no"]) ? "readonly" : null) => null
]);
?>

<form id="ufo-acc-info">

    <div class="ufo-acc-info-photo">
        <img src="<?= $_["this_member"]["photo"] ?? $db->meta("unknown_photo") ?>" data-error="<?= $db->meta("unknown_photo") ?>">
    </div>

    <div class="ufo-acc-info-inputs mt-20">
        <?php foreach ($ufo->get_array("ufo_account_info_inputs") as $item) {
            echo $ufo->tag("div", $ufo->tag("label",
                $ufo->tag("span", $item["placeholder"], [
                        "class" => "font-size-14px db mb-5"
                ]).
                $ufo->single_input($item + [
                    "autocomplete" => "off"
                ])
            ));
        } ?>
    </div>

    <button class="btn btn-primary ufo-acc-save-info" type="submit">
        <?= $ufo->lng("Save") ?>
    </button>

</form>

<?php $ufo->fire("ufo-account-member-info-page") ?>