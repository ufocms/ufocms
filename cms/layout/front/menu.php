<ul class="ufo-front-menu">
    <?php foreach ((new UFO_Menu())->get([
        "where" => [
            "position" => "main-menu"
        ]
    ])[0]["submenu"] ?? [] as $item) { ?>
        <li>
            <a href="<?=$item["link"]?>"><?=$item["title"]?></a>
        </li>
    <?php } ?>
</ul>