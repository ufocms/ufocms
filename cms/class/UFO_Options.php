<?php

/**
 * Copyright (c) 2022-2024 UFOCMS
 *
 * This software is licensed under the GPLv3 license.
 * See the LICENSE file for more information.
 */

final class UFO_Options {

    protected array $SAVER = [];
    protected array $FLOAT = [];

    /**
     * @throws Exception
     */
    public function __construct () {
        /**
         * Load all saves
         */
        if ($this->isset_post()) {
            $this->SAVER = json_decode(
                file_get_contents($this->slash_folder(
                    dirname(__FILE__) . "/../content/cache/saver.json"
                )),
                JSON_UNESCAPED_UNICODE
            );
        }

        $this->add_default();
    }

    /**
     * @return void
     * @throws Exception
     */
    protected function add_default () {
        try {
            $this->add_kv("this_page", $this->this_page());
        } catch (Exception $e) {
            $this->error($e);
        }
    }

    /**
     * @return array
     */
    public function get_saver (): array {
        return $this->SAVER;
    }

    /**
     * @return array
     */
    public function get_package (): array {
        $file = (defined("ADMIN") ? $this->back_folder() : "") . _PRIVATE_ . "package.json";
        if (file_exists($file))
            return json_decode(file_get_contents($file), true);
        return [];
    }

    /**
     * @param string $md5
     * @return bool
     */
    public function valid_md5 (string $md5): bool {
        return preg_match('/^[a-f0-9]{32}$/', $md5);
    }

    /**
     * @param string $algo
     * @return string
     */
    public function hash_generator (string $algo = "sha512"): string {
        return hash($algo, rand());
    }

    /**
     * Standard UUID version 4
     *
     * @return string
     * @throws Exception
     */
    public function uuid ( ): string {
        $data = random_bytes(16);
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40); // Set version to 0100
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80); // Set bits 6-7 to 10
        return vsprintf("%s%s-%s-%s-%s-%s%s%s", str_split(bin2hex($data), 4));
    }

    /**
     * @param int $length
     * @param int $min
     * @param int $max
     * @throws Exception
     * @return int
     */
    public function random_digits (int $length, int $min = 1, int $max = 9): int {
        $result = "";
        for ($i = 0; $i < $length; $i++)
            $result .= random_int($min, $max);
        return (int) $result;
    }

    /**
     * @param int $length
     * @return string
     * @throws Exception
     */
    public function random_alphabet (int $length = 10): string {
        $alphabets = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
        $chars_len = strlen($alphabets);
        $generated = "";
        for ($i = 0; $i < $length; $i++)
            $generated .= $alphabets[random_int(0, $chars_len - 1)];
        return $generated;
    }

    /**
     * @param $pass
     * @return string
     */
    public function create_password ($pass): string {
        $md5    = $this->valid_md5($pass) ? $pass : md5($pass);
        $sha256 = hash("sha256", $md5);
        $sha512 = hash("sha512", $sha256);
        return md5($sha512);
    }

    /**
     * @param bool $json
     * @return array|string
     * @throws Exception
     */
    public function verify_code (bool $json = false) {
        global $db;

        $structure = $this->do_work("ufo_structure_verify_code");
        $structure = [
            "code"   => strtoupper(
                $this->random_digits($structure["numbers"]
            ) . $this->random_alphabet($structure["alphabets"])),
            "expire" => $this->addTime((int) $db->verify_timeout, "s")
        ];

        return $json ? json_encode($structure) : $structure;
    }

    /**
     * @param $time
     * @return string
     * @throws Exception
     */
    public function readable_timestamp ($time): string {
        $date = new DateTime("@$time");
        return $date->format("Y-m-d H:i:s");
    }

    /**
     * @param string $format
     * @return string
     */
    public function dateTime (string $format = "Y-m-d H:i:s"): string {
        return date($format);
    }

    /**
     * @param $dy
     * @param $dm
     * @param $dd
     * @return array
     */
    protected function __solarDate ($dy, $dm, $dd): array {
        list($dy, $dm, $dd) = explode('_', $this->replace_number($dy . '_' . $dm . '_' . $dd));

        $g_d_m = [0, 31, 59, 90, 120, 151, 181, 212, 243, 273, 304, 334];
        $gy2 = ($dm > 2) ? ($dy + 1) : $dy;
        $days = 355666 + (365 * $dy) + ((int)(($gy2 + 3) / 4)) - ((int)(($gy2 + 99) / 100)) + ((int)(($gy2 + 399) / 400)) + $dd + $g_d_m[$dm - 1];
        $uy = -1595 + (33 * ((int)($days / 12053)));
        $days %= 12053;
        $uy += 4 * ((int)($days / 1461));
        $days %= 1461;

        if ($days > 365) {
            $uy += (int)(($days - 1) / 365);
            $days = ($days - 1) % 365;
        }

        if ($days < 186) {
            $um = 1 + (int)($days / 31);
            $ud = 1 + ($days % 31);
        } else {
            $um = 7 + (int)(($days - 186) / 30);
            $ud = 1 + (($days - 186) % 30);
        }

        return [$uy, $um, $ud];
    }

    /**
     * @param string $date
     * @return string
     */
    public function solarDateTime (string $date): string {
        if (empty($date)) return "";

        /**
         * Check Isset Time
         */
        $time = explode(" ", $date)[1] ?? "";

        /**
         * Date To Array
         */
        $dateArray = explode("-", explode(" ", $date)[0]);

        /**
         * Implode Result
         */
        return $time . " " . implode("/", $this->__solarDate($dateArray[0], $dateArray[1], $dateArray[2]));
    }

    /**
     * @param string $dateTime
     * @return string
     * @throws Exception
     */
    public function cTime (string $dateTime): string {
        global $db;
        return ($db->meta("type_time") === "solar" ? $this->solarDateTime($dateTime) : $dateTime);
    }

    /**
     * @param string $operator
     * @param int|array $number
     * @param string|array $unit
     * @return string
     */
    public function reduceAddTime (string $operator, $number, $unit): string {
        if (is_array($number) && is_array($unit)) {
            $time = "";
            foreach ($number as $k => $v)
                if (isset($unit[$k]))
                    $time .= $operator . $v . " " . $this->abbreviationUnitTime($unit[$k]) . " ";
            return $time;
        }
        return $operator . $number . " " . $this->abbreviationUnitTime($unit);
    }

    /**
     * @param int|array $number
     * @param string|array $unit
     * @return string
     */
    public function addTime ($number, $unit): string {
        return date("Y-m-d H:i:s", strtotime(
            $this->reduceAddTime("+", $number, $unit),
            time()
        ));
    }

    /**
     * @param int|array $number
     * @param string|array $unit
     * @return string
     */
    public function reduceTime ($number, $unit): string {
        return date("Y-m-d H:i:s", strtotime(
            $this->reduceAddTime("-", $number, $unit),
            time()
        ));
    }

    /**
     * @param string $unit
     * @return string
     */
    public function abbreviationUnitTime (string $unit): string {
        return ["y" => "year", "m" => "month", "d" => "day", "h" => "hours", "i" => "minutes", "s" => "second"][$unit] ?? $unit;
    }

    /**
     * @param string $dateTime
     * @param bool $cTime
     * @return string
     * @throws Exception
     */
    public function structureDateTime (string $dateTime, bool $cTime = false): string {
        global $db;

        if ($cTime)
            $dateTime = $this->cTime($dateTime);

        $structure = $db->meta("structure_datetime");
        if (empty($structure)) {
            $db->update_meta("structure_datetime", "Y-m-d H:i:s");
            $structure = $db->meta("structure_datetime");
        }

        $customStructure = $this->do_work("ufo_structure_datetime", [
            "dateTime" => $dateTime,
            "cTime"    => $cTime
        ]);
        if (!empty($customStructure))
            return $customStructure;

        return (new DateTime($dateTime))->format($structure);
    }

    /**
     * @param string $datetime
     * @param string $format
     * @return bool
     */
    public function validateDateTime (string $datetime, string $format = "Y-m-d H:i:s"): bool {
        $d = DateTime::createFromFormat($format, $datetime);
        return $d && $d->format($format) === $datetime;
    }

    /**
     * @param string $dateTime1
     * @param string|null $dateTime2
     * @return object
     * @throws Exception
     */
    public function dateInterval (string $dateTime1, string $dateTime2 = null): object {
        $dateTime  = new DateTime($dateTime2 ?? $this->dateTime());
        $dateTime2 = new DateTime($dateTime1);
        $interval  = $dateTime->diff($dateTime2);

        $total_seconds = (
            ($interval->y * 365.25 * 24 * 60 * 60) + // Convert years to seconds
            ($interval->m * 30 * 24 * 60 * 60) +     // Convert months to seconds
            ($interval->d * 24 * 60 * 60) +          // Convert days to seconds
            ($interval->h * 60 * 60) +               // Convert hours to seconds
            ($interval->i * 60) +                    // Convert minutes to seconds
            ($interval->s)                           // Add remaining seconds
        );
        $result = [
            "years"   => $interval->y,
            "months"  => $interval->m,
            "days"    => $interval->d,
            "hours"   => $interval->h,
            "minutes" => $interval->i,
            "seconds" => $interval->s,
            "total_seconds" => $total_seconds
        ];

        // Check if the first date is in the past
        if ($interval->invert === 1) {
            foreach ($result as &$value)
                $value = 0;
        }

        return (object) $result;
    }

    /**
     * @param int $n
     * @param int $n2
     * @return float
     */
    public function calculatePercentage (int $n, int $n2): float {
        if ($n == 0 || $n2 == 0) return 0;
        return round(abs(($n / $n2) * 100));
    }

    /**
     * @return string
     * @throws Exception
     */
    public function this_title (): string {
        global $db, $_;
        return $_["title"] ?? $db->meta("web_name");
    }

    /**
     * @return string
     * @throws Exception
     */
    public function web_link (): string {
        global $db, $_;
        return $_["web_url"] ?? $db->meta("web_url");
    }

    /**
     * @return string
     * @throws Exception
     */
    public function admin_url (): string {
        global $db;
        return $db->meta("web_admin_url");
    }

    /**
     * @return string
     */
    public function web_logo (): string {
        return WEB_ICON;
    }

    /**
     * @return string
     * @throws Exception
     */
    public function copyright (): string {
        global $db;
        return $db->meta("copyright");
    }

    /**
     * @return string
     */
    public function admin_path (): string {
        global $admin_folder;
        if ($this->isset_key($this->get_package(), "admin_path"))
            return $this->get_package()["admin_path"];
        return $admin_folder;
    }

    /**
     * @return bool
     */
    public function is_admin (): bool {
        return defined("ADMIN");
    }

    /**
     * @return bool
     */
    public function is_front (): bool {
        return defined("FRONT");
    }

    /**
     * @param int|string $id
     * @return array|mixed
     * @throws Exception
     */
    public function get_admin ($id = null) {
        global $db;

        if (!empty($id))
            return $db->get("admins", "id", $id)[0] ?? false;

        if (isset($_COOKIE[$db->meta("admin_cookie")])) {
            $Get = $db->get("admins", "hash_login", $_COOKIE[$db->meta("admin_cookie")]);
            if (isset($Get[0]))
                return $Get[0];
            else {
                setcookie($db->meta("admin_cookie"), "", time() - 300, "/");
                $this->redirect($db->meta("admin_url"));
                return false;
            }
        }

        return false;
    }

    /**
     * @param int $id
     * @return array|null
     * @throws ReflectionException
     */
    public function has_admin (int $id): ?array {
        global $db;
        return $db->helper->where("id", $id)->getOne("admins");
    }

    /**
     * @param array $data
     * @return array
     * @throws ReflectionException
     * @throws Exception
     */
    public function login_admin (array $data): array {
        global $db, $ufo;

        $db->where("login_name", $db->sanitize_string(
            $data["login_name"]
        ));
        $db->where("password", $this->create_password(
            $data["password"]
        ));

        $admin  = $db->helper->getOne("admins");
        $result = [403, $this->lng("Incorrect information")];

        if (!empty($admin)) {
            $update = $db->update("admins", [
                "last_login" => $ufo->dateTime()
            ], "id", $admin["id"]);

            $result = [
                $update ? 200 : 503,
                $update ? $ufo->lng("You entered") : $ufo->lng("System error")
            ];

            if ($this->equal($result[0], 200)) setcookie(
                $db->meta("admin_cookie"),
                $admin["hash_login"], time() + (86400 * 30), "/"
            );
        }

        return $result;
    }

    /**
     * @return bool
     * @throws Exception
     */
    public function check_login_admin (): bool {
        global $db;

        $cookie = $db->admin_cookie;

        if (isset($_COOKIE[$cookie])) {
            $admin = $db
                ->where("hash_login", $_COOKIE[$cookie])
                ->getOne("admins");

            return !empty($admin);
        }

        return false;
    }

    /**
     * @param array $data
     * @return int|bool
     * @throws ReflectionException
     * @throws Exception
     */
    public function add_member (array $data) {
        global $db;

        $db->where("username", $data["username"]);

        if ($this->isset_key($data, "email"))
            $db->where("email", $data["email"], "=", "OR");

        if ($this->isset_key($data, "no"))
            $db->where("no", $data["no"], "=", "OR");

        $exists = $db->helper->getValue("members", "uid");

        if (!empty($exists))
            return 0;

        if ($this->isset_key($data, "more")) {
            if (!is_array($data["more"]))
                return 503;
        }

        $insert = $db->insert("members", [
            "name"       => $data["name"] ?? "",
            "last_name"  => $data["last_name"] ?? "",
            "username"   => $data["username"],
            "email"      => $data["email"] ?? "",
            "no"         => $data["no"] ?? "",
            "password"   => $this->create_password($data["password"]),
            "photo"      => $data["photo"] ?? $db->meta("unknown_photo"),
            "hash"       => $this->hash_generator("md5"),
            "last_login" => "",
            "last_ip"    => $this->viewer_ip(),
            "dateTime"   => $this->dateTime(),
            "verify"     => $data["verify"] ?? ($db->meta("accept-member") == "true" ? 1 : 0),
            "more"       => json_encode($data["more"] ?? [], JSON_UNESCAPED_UNICODE)
        ]);

        return $insert ? $db->insert_id() : false;
    }

    /**
     * @param array $data
     * @param ?int $uid
     * @return bool
     * @throws Exception
     */
    public function update_member (array $data, int $uid = null): bool {
        global $db;

        if (empty($uid))
            $uid = $this->get_member()["uid"] ?? $this->die($this->lng("Member not found"));

        $beforeData = $db->get("members", "uid", $uid)[0];
        $changedPassword = $this->isset_key($data, "password") && $beforeData["password"] != $data["password"];

        $more = $beforeData["more"];

        $this->is_json($more, $more);

        if (!is_array($more))
            $more = [];

        $more = $this->default($more, $data["more"] ?? []);

        return $db->update("members", [
            "name"       => $data["name"] ?? $beforeData["name"],
            "last_name"  => $data["last_name"] ?? $beforeData["last_name"],
            "username"   => $data["username"] ?? $beforeData["username"],
            "email"      => $data["email"] ?? $beforeData["email"],
            "no"         => $data["no"] ?? $beforeData["no"],
            "password"   => !$changedPassword ? $beforeData["password"] : $this->create_password($data["password"]),
            "photo"      => empty($data["photo"]) ? $beforeData["photo"] : $data["photo"],
            "hash"       => !$changedPassword ? $beforeData["hash"] : $this->hash_generator("md5"),
            "last_login" => $data["last_login"] ?? $beforeData["last_login"],
            "last_ip"    => $data["last_ip"] ?? $beforeData["last_ip"],
            "verify"     => $data["verify"] ?? $beforeData["verify"],
            "more"       => json_encode($more, JSON_UNESCAPED_UNICODE)
        ], "uid", $uid);
    }

    /**
     * @param $args
     * @return mixed
     * @throws Exception
     */
    public function login_member ($args) {
        global $db, $ufo;

        $where  = [
            "password" => $this->create_password($args["password"])
        ];

        if (filter_var($args["username"], FILTER_VALIDATE_EMAIL))
            $where["email"] = $args["username"];
        else if (is_numeric($args["username"]))
            $where["no"] = $args["username"];
        else if (is_string($args["username"]))
            $where["username"] = $args["username"];
        else
            return $this->lng("Not valid username");

        $member = $db->get("members", $where);

        if (isset($member[0])) {
            if ($db->update("members", [
                "last_login" => $ufo->dateTime(),
                "last_ip"    => $ufo->viewer_ip()
            ], "uid", $member[0]["uid"])) {
                setcookie($db->meta("member_cookie"), $member[0]["hash"], time() + (86400 * 30), "/");
                return $member[0];
            }
        }

        return false;
    }

    /**
     * @return bool
     * @throws Exception
     */
    public function logout_member (): bool {
        global $db;
        return $this->unset_cookie($db->member_cookie);
    }

    /**
     * @param $uid
     * @throws Exception
     * @return bool
     */
    public function verified_member ($uid = null): bool {
        return ((int) $this->get_member($uid)["verify"] ?? 0) == 1;
    }

    /**
     * @param $mid
     * @return array
     * @throws Exception
     */
    public function get_member ($mid = null): array {
        global $db;

        $cookie = $db->meta("member_cookie");

        if (!empty($mid))
            return $db->get("members", "uid", $mid)[0] ?? [];

        if ($this->isset_cookie($cookie))
            return $db->get("members", [
                "hash" => $_COOKIE[$cookie]
            ])[0] ?? [];

        return [];
    }

    /**
     * @param int $id
     * @return array|null
     * @throws ReflectionException
     */
    public function has_member (int $id): ?array {
        global $db;
        return $db->helper->where("uid", $id)->getOne("members");
    }

    /**
     * @param int|null $mid
     * @param ?string $role
     * @return bool|array
     * @throws Exception
     */
    public function member_roles (int $mid = null, ?string $role = null) {
        global $db;

        $mid   = $this->get_member($mid);
        $roles = $mid["roles"];
        $roles = json_decode($this->is_array(
            $roles
        ) ? $roles : "[]", true);

        if (empty($role))
            return $roles;

        if (is_array($roles)) {
            if (!in_array($role, $roles))
                $roles[] = $role;
        } else
            $roles = [$role];

        return $db->update("members", [
            "roles" => json_encode($roles)
        ], "uid", $mid["uid"]);
    }

    /**
     * @return false
     * @throws Exception
     */
    public function check_login_member (): bool {
        global $db;

        $cookie = $db->member_cookie;

        if (isset($_COOKIE[$cookie])) {
            $member = $db->where("hash", $_COOKIE[$cookie])->getOne("members");
            return !empty($member);
        }

        return false;
    }

    /**
     * @param $plugin
     * @return string
     */
    public function plugin_url ($plugin): string {
        return URL_PLUGINS . $plugin . "/";
    }

    /**
     * @param string $plugin
     * @return string
     */
    public function plugin_dir (string $plugin = ""): string {
        return PLUGINS . $plugin . SLASH;
    }

    /**
     * @return string
     * @throws Exception
     */
    public function theme_path (): string {
        global $db, $_, $admin_folder;

        if (!empty($_["this_template"]["path"]))
            return $_["this_template"]["path"];

        $theme = $_COOKIE["ufo_theme"] ?? ($_SESSION["ufo_theme"] ?? $db->meta("theme"));
        $templates = new UFO_Json($admin_folder . "content/cache/templates.json");
        $theme = $templates->where("id", $theme)->get()[0] ?? [];

        return $this->slash_folder(THEMES . (
            $_["ufo_theme"] ?? ($theme["path"] ?? "") . SLASH
        ));
    }

    /**
     * @return string
     * @throws Exception
     */
    public function theme_url (): string {
        global $db, $_, $admin_folder;

        if (!empty($_["this_template"]))
            return $_["this_template"]["link"];

        $templates = (new UFO_Json($admin_folder . "content/cache/templates.json"));
        $template  = $_COOKIE["ufo_theme"] ?? ($_SESSION["ufo_theme"] ?? $db->meta("theme"));
        $template  = $templates->where("id", $template)->get()[0] ?? [];

        return $this->sanitize_link(URL_THEME . (
            $_["ufo_theme"] ?? ($template["path"] ?? "") . SLASH
        ));
    }

    /**
     * @return string
     */
    public function get_url (): string {
        $location = (isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] === "on" ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
        if ($this->isset_post("location")) {
            if (filter_var($_POST["location"], FILTER_VALIDATE_URL))
                return parse_url($location)["host"] == parse_url($_POST["location"])["host"] ? $_POST["location"] : $location;
        }
        return $location;
    }

    // HTML, some tags do not require a closing tag
    public array $html_tags_nc = [
        "meta", "link", "hr", "img", "input", "br"
    ];

    /**
     * @param string $tag
     * @param string|callable|array $content
     * @param array $attr
     * @param int $num
     * @return string
     */
    public function tag (string $tag, $content = "", array $attr = [], int $num = 1): string {
        $html  = "";
        $attrs = "";

        if (!is_string($content)) {
            if ($this->is_function($content))
                $content = $content();

            if (is_array($content))
                $content = implode("", $content);
        }

        if ($this->has_keys($attr))
            foreach ($attr as $k => $v)
                if (!empty($k))
                    $attrs .= " " . $k . '="' . $v . '"';

        for ($i = 0; $i < $num; $i++) {
            $html .= "<$tag$attrs>";
            $html .= in_array($tag, $this->html_tags_nc) ? "" : $content;
            $html .= in_array($tag, $this->html_tags_nc) ? "" : "</$tag>";
        }

        return $html;
    }

    /**
     * @param array $css
     * @return string
     */
    public function css (array $css = []): string {
        $join = "";
        foreach ($css as $k => $v)
            $join .= "$k:$v;";
        return $join;
    }

    /**
     * @param int $badge
     * @return string
     */
    public function css_badge (int $badge): string {
        return ["primary", "info", "warning", "danger", "success", "secondary", "light", "dark"][$badge];
    }

    /**
     * @param array $keys
     * @return void
     */
    public function add_meta (array $keys): void {
        if (!isset($this->FLOAT["head"]))
            $this->FLOAT["head"] = [];
        if (!isset($this->FLOAT["head"]["meta"]))
            $this->FLOAT["head"]["meta"] = [];
        $this->FLOAT["head"]["meta"][] = $keys;
    }

    /**
     * @param array $keys
     * @return void
     */
    public function add_link (array $keys): void {
        if (!isset($this->FLOAT["head"]))
            $this->FLOAT["head"] = [];
        if (!isset($this->FLOAT["head"]["link"]))
            $this->FLOAT["head"]["link"] = [];
        $this->FLOAT["head"]["link"][] = $keys;
    }

    /**
     * @param string $address
     * @return void
     */
    public function add_style (string $address): void {
        if ($this->is_url($address)) {
            if (!isset($this->FLOAT["head"]))
                $this->FLOAT["head"] = [];
            if (!isset($this->FLOAT["head"]["css"]))
                $this->FLOAT["head"]["css"] = [];

            $name = isset(pathinfo($address)["filename"]) ? pathinfo($address)["filename"] : rand();
            $name = str_replace(".", "-", $name);

            $this->FLOAT["head"]["css"][] = "\t<link data-name='$name' rel='stylesheet' href='$address'>\n";
        }
    }

    /**
     * @param string $name
     * @param string $address
     * @param ?string $after
     * @param string $location
     * @return void
     */
    public function add_script (string $name, string $address, string $after = null, string $location = "bottom"): void {
        if ($this->is_url($address)) {
            $script = "\t" . $this->tag("script", "", [
                "data-name" => $name,
                "src"       => $address
            ]) . "\n";

            if (!isset($this->FLOAT["head"]["script"][$location][$name]))
                $this->FLOAT["head"]["script"][$location][$name] = [];

            if (empty($after)) {
                $this->FLOAT["head"]["script"][$location][$name][] = $script;
            } else {
                if (!isset($this->FLOAT["head"]["script"][$location][$after]))
                    $this->FLOAT["head"]["script"][$location][$after] = [];
                $this->FLOAT["head"]["script"][$location][$after][] = $script;
            }
        }
    }

    /**
     * @param string $source
     * @return void
     */
    public function add_source (string $source): void {
        if (!isset($this->FLOAT["tag_script"]))
            $this->FLOAT["tag_script"] = [];
        $this->FLOAT["tag_script"][] = $source;
    }

    /**
     * @param string $name
     * @param array $data
     * @return void
     */
    public function localize_script (string $name, array $data = []): void {
        $dataArray = empty($data) ? $this->FLOAT["localize"][$name] : $data;
        echo "\t<script>\n";
        echo "\t\tconst " . $name . " = " . json_encode($dataArray, JSON_UNESCAPED_UNICODE) . ";\n";
        echo "\t</script>";
    }

    /**
     * @param string $_name
     * @param string $_key
     * @param $_value
     * @return void
     */
    public function add_localize_script (string $_name, string $_key, $_value): void {
        if (!isset($this->FLOAT["localize"][$_name]))
            $this->FLOAT["localize"][$_name] = [];
        $this->FLOAT["localize"][$_name][$_key] = $_value;
    }

    /**
     * @param $_name
     * @param $_key
     * @return void
     */
    public function unset_localize_script (string $_name, ?string $_key = null): void {
        if (empty($_key)) {
            if (isset($this->FLOAT["localize"][$_name]))
                unset($this->FLOAT["localize"][$_name]);
        } else if (isset($this->FLOAT["localize"][$_name][$_key]))
            unset($this->FLOAT["localize"][$_name][$_key]);
    }

    /**
     * @return string
     */
    public function loop_load_meta (): string {
        if (!isset($this->FLOAT["head"]))
            $this->FLOAT["head"] = [];

        if (!isset($this->FLOAT["head"]["meta"]))
            $this->FLOAT["head"]["meta"] = [];

        $metas = "";
        foreach ($this->FLOAT["head"]["meta"] as $item) {
            $keys = '';

            foreach ($item as $k => $v)
                $keys .= $k . '="' . $v . '" ';

            $metas .= "\t<meta ".rtrim($keys, " ").">\n";
        }

        return $metas;
    }

    /**
     * @return string
     */
    public function loop_load_link (): string {
        if (!isset($this->FLOAT["head"]))
            $this->FLOAT["head"] = [];

        if (!isset($this->FLOAT["head"]["link"]))
            $this->FLOAT["head"]["link"] = [];

        $links = "";
        foreach ($this->FLOAT["head"]["link"] as $item) {
            $keys = '';
            foreach ($item as $k => $v)
                $keys .= $k . '="' . $v . '" ';
            $links .= "\t<link " . rtrim($keys, " ") . ">\n";
        }

        return $links;
    }

    /**
     * @return void
     */
    public function loop_load_styles (): void {
        if (!isset($this->FLOAT["head"]))
            $this->FLOAT["head"] = [];

        if (!isset($this->FLOAT["head"]["css"]))
            $this->FLOAT["head"]["css"] = [];

        foreach ($this->FLOAT["head"]["css"] as $items)
            echo $items;
    }

    /**
     * @param string $location
     * @return string
     */
    public function loop_load_scripts (string $location = "bottom"): string {
        if (!isset($this->FLOAT["head"]))
            $this->FLOAT["head"] = [];

        if (!isset($this->FLOAT["head"]["script"]))
            $this->FLOAT["head"]["script"] = [];

        if (isset($this->FLOAT["head"]["script"][$location])) {
            foreach ($this->FLOAT["head"]["script"][$location] as $k => $i) {
                foreach ($i as $item)
                    echo $item;
            }
        }

        return "";
    }

    /**
     * @return void
     */
    public function loop_source (): void {
        if (!isset($this->FLOAT["tag_script"]))
            $this->FLOAT["tag_script"] = [];

        foreach ($this->FLOAT["tag_script"] as $item)
            echo "\t" . $this->tag("script", $item) . "\n";
    }

    /**
     * @return false|void
     */
    public function localize_all_script () {
        if (!isset($this->FLOAT["localize"]))
            $this->FLOAT["localize"] = [];

        $this->FLOAT["localize"] = array_reverse($this->FLOAT["localize"]);

        if (empty($this->FLOAT["localize"]))
            return false;

        echo "\t<script>\n";

        foreach ($this->FLOAT["localize"] as $k => $v)
            echo "\t\tconst " . $k . " = " . json_encode($v, JSON_UNESCAPED_UNICODE) . ";\n";

        echo "\t</script>\n";
    }

    /**
     * @return void
     */
    public function clear_link (): void {
        if (!$this->is_admin())
            $this->FLOAT["head"]["link"] = [];
    }

    /**
     * @return void
     */
    public function clear_style (): void {
        if (!$this->is_admin())
            $this->FLOAT["head"]["css"] = [];
    }

    /**
     * @return void
     */
    public function clear_meta (): void {
        if (!$this->is_admin())
            $this->FLOAT["head"]["meta"] = [];
    }

    /**
     * @return void
     */
    public function clear_script (): void {
        if (!$this->is_admin())
            $this->FLOAT["head"]["script"] = [];
    }

    /**
     * @return void
     */
    public function clear_localize_script (): void {
        if (!$this->is_admin())
            $this->FLOAT["localize"] = [];
    }

    /**
     * @param string|null $replacement
     * @return void
     */
    public function clear_body (?string $replacement = null) {
        global $_;
        $_["ufo_clear_body"] = $replacement;
    }

    /**
     * @param array $args
     * @return void
     */
    public function add_menu (array $args = []): void {
        if (empty($args))
            return;

        if (!isset($this->FLOAT["menu"]))
            $this->FLOAT["menu"] = [];

        $menu = [
            "title" => $args["title"],
            "page"  => $args["page"]
        ];

        /**
         * Img & Icon menu
         */
        if (isset($args["img"])) {
            $menu["img"] = $args["img"];
        } else if (isset($args["icon"])) {
            $menu["icon"] = $args["icon"];
        }

        /**
         * Is plugin
         */
        if (isset($args["plugin"]))
            $menu["plugin"] = $args["plugin"];
        else
            $menu["plugin"] = false;

        /**
         * Badge number
         */
        if (isset($args["badge"])) {
            if (is_numeric($args["badge"])) {
                if ($args["badge"] > 0)
                    $menu["badge"] = ((int) $args["badge"] > 99 ? "99+" : $args["badge"]);
            }
        }

        if (isset($args["position"])) {
            $this->insert_array($this->FLOAT["menu"], (int) $args["position"], [$menu]);
        } else {
            $this->FLOAT["menu"][] = $menu;
        }
    }

    /**
     * @return string
     */
    public function loop_menu (): string {
        if (!isset($this->FLOAT["menu"]))
            $this->FLOAT["menu"] = [];

        $MENU   = $this->FLOAT["menu"];
        $JOIN   = "";
        $active = $this->reloadedHere();

        foreach ($MENU as $item) {
            $JOIN .= $this->tag("li",
                $this->tag("div",
                    (isset($item["badge"]) ? $this->tag("div", $item["badge"], ["class" => "admin-menu-badge"]) : "") .
                    (isset($item["icon"]) ? '<i class="' . $item["icon"] . '" data-icon="' . $item["icon"] . '"></i>' : (isset($item['img']) ? '<img src="' . $item['img'] . '">' : '<i class="ufo-icon-question" data-icon="ufo-icon-question"></i>')) .
                    $this->tag("span", ($item["title"] ?? "")),
                    ["class" => "flex flex-start align-center height-100-cent"]),
                [
                    "class" => "item-menu" . ($active ? "" : " active"),
                    "data-" . ($item["plugin"] ? "plugin" : "page") => ($item["plugin"] ?? $item["page"]),
                    "data-page" => $item["page"]
                ]
            );
            $active = true;
        }

        return $JOIN;
    }

    /**
     * @param string $_key
     * @param array $data
     * @return void
     */
    public function add_input (string $_key, array $data = []): void {
        $this->FLOAT["inputs"][$_key] = $this->FLOAT["inputs"][$_key] ?? [];
        $this->FLOAT["inputs"][$_key][] = $data;
    }

    /**
     * @param string $_key
     * @param string|null $end
     * @param bool $array
     * @return bool|array|void
     */
    public function loop_inputs (string $_key, ?string $end = null, bool $array = false) {
        if (empty($end))
            $end = "<div class='mb-5 p-5px'></div>";

        if (!isset($this->FLOAT["inputs"][$_key]))
            return $array ? [] : false;

        if ($array)
            return $this->FLOAT["inputs"][$_key];

        foreach ($this->FLOAT["inputs"][$_key] as $i) {
            $join  = "";
            $class = "form-control";

            foreach ($i as $k => $v) {
                if ($k == "class")
                    $class = $class . " " . $i["class"];
                else
                    $join .= " " . $k . "='" . $v . "'";
            }

            echo '<input class="' . $class . '" ' . $join . '>' . $end;
        }
    }

    /**
     * @param array $arg
     * @return string
     */
    public function single_input (array $arg = []): string {
        $join  = "";
        $class = "form-control";

        foreach ($arg as $k => $v) {
            if (!empty($k)) {
                if ($k == "class")
                    $class = $class . " " . $arg["class"];
                else if ($k != "end")
                    $join .= " " . $k . "='" . $v . "'";
            }
        }

        return '<input class="'.$class.'" '.$join.'>' . ($arg["end"] ?? "<div class='mt-10 p-5px'></div>");
    }

    /**
     * @param string $id
     * @param array $args [
     *     "page"  => String (Default = $db->slug_blog)
     *     "name"  => String,
     *     "class" => String,
     *     "placeholder"  => String,
     *     "autocomplete" => String (on,off)
     * ]
     * @return void
     * @throws Exception
     */
    public function search_form (string $id, array $args = []): void {
        global $db;

        extract($args);

        echo $this->tag("form",
            $this->tag("input", null, [
                "type"  => "search",
                "name"  => $name ?? "search",
                "class" => "form-control",
                "placeholder"  => $placeholder ?? $this->lng("Search"),
                "autocomplete" => $autocomplete ?? "off",
                "minlength"    => 3
            ]) .
            $this->tag("button", $this->tag("i", null, [
                "class" => "ufo-icon-search"
            ]), [
                "class" => "btn btn-primary font-size-20px"
            ]), [
                "id"    => $id,
                "class" => "ufo-search-form flex flex-start " . ($class ?? ""),
                "data-page" => $page ?? $db->slug("blog")
            ]
        );
    }

    /**
     * @param string $file
     * @param mixed ...$args
     * @return string
     * @throws Exception
     */
    public function require (string $file, ...$args): ?string {
        global $ufo, $db, $_, $admin_folder;

        $file = $this->slash_folder(str_replace(
            ".php", "", $file
        ) . ".php");

        if (is_file($file)) {
            ob_start();
            require $this->slash_folder($file);
            return ob_get_clean();
        }

        $this->error($this->lng("File not found") . " ($file)");

        return null;
    }

    /**
     * @param $file
     * @param bool|string $layout
     * @param string $format
     * @param mixed $arg
     * @return mixed (Because it returns composite data from the file,
     *                but it actually returns `boolean`)
     * @throws Exception
     */
    public function load_layout ($file, $layout = true, string $format = ".php", $arg = []) {
        global $ufo, $db, $_, $admin_folder;

        if ($layout)
            $layout = LAYOUT;
        else {
            $layout = "";
            if ($_SERVER["REQUEST_METHOD"] == "POST")
                $format = "";
        }

        $file = $this->slash_folder($layout . $file . $format);

        if (file_exists($file))
            return require $file;

        if (defined("ADMIN"))
            return $this->load_layout("404");

        echo $this->error($this->lng("File not found") . " ($file)", "", false);

        return false;
    }

    /**
     * @param $file
     * @param bool|string $layout
     * @param string $format
     * @param mixed $arg
     * @return string
     * @throws Exception
     */
    public function return_layout ($file, $layout = true, string $format = ".php", $arg = []): string {
        ob_start();
        $this->load_layout($file, $layout, $format, $arg);
        return ob_get_clean();
    }

    /**
     * @param $file
     * @param mixed $args
     * @param bool $error
     * @return void
     * @throws Exception
     */
    public function from_theme ($file, $args = [], bool $error = false): void {
        global $ufo, $db, $_, $admin_folder;

        $file = $this->slash_folder($this->theme_path() . $file . ".php");

        if (
            file_exists($file) && is_file($file) && defined("FRONT") && $ufo->isset_key($_, "this_template") &&
            (($_["this_template"]["set"] || $_["this_template"]["multi"]) || $ufo->isset_key($_SESSION, "ufo_theme_admin_preview"))
        )
            require $file;
        else if ($error)
            $this->error("File not found in template!");
    }

    /**
     * @param $file
     * @param mixed $args
     * @param bool $error
     * @return void
     * @throws Exception
     */
    public function return_from_theme ($file, $args = [], bool $error = false): string {
        ob_start();
        $this->from_theme($file, $args, $error);
        return ob_get_clean();
    }

    /**
     * @param string $folder
     * @return bool
     * @throws Exception
     */
    public function folder_exists_theme (string $folder): bool {
        return is_dir($this->slash_folder($this->theme_path() . $folder));
    }

    /**
     * @param $file
     * @param string $type
     * @return bool
     * @throws Exception
     */
    public function file_exists_theme ($file, string $type = "php"): bool {
        return file_exists($this->slash_folder(
            $this->theme_path() . $file . "." . $type
        ));
    }

    /**
     * @return array
     */
    public function manifest_theme (): array {
        global $_;
        return $_["this_template"]["manifest"] ?? [];
    }

    /**
     * @return string
     * @throws Exception
     */
    public function dir (): string {
        global $db, $_;
        try {
            return $_["dir"] ?? $db->meta("dir");
        } catch (Exception $e) {
            $this->error($e);
            return "ltr";
        }
    }

    /**
     * @return string
     * @throws Exception
     */
    public function charset (): string {
        global $db, $_;
        try {
            return $_["charset"] ?? $db->meta("charset");
        } catch (Exception $e) {
            $this->error($e);
            return "UTF-8";
        }
    }

    /**
     * @param $text
     * @param string $classes
     * @param string $style
     * @param array $attrs
     * @return string
     */
    public function btn ($text, string $classes = "", string $style = "btn btn-primary", array $attrs = []): string {
        return $this->tag("button", $text, $this->default([
            "class" => "$style $classes"
        ], $attrs));
    }

    /**
     * @return string
     * @throws Exception
     */
    public function reverse_float (): string {
        return $this->dir() == "ltr" ? "f-right" : "f-left";
    }

    /**
     * Space left and right based on direction
     * @param int $px
     * @return string
     * @throws Exception
     */
    public function space_lr_by_dir (int $px = 5): string {
        return "m" . ($this->dir() == "rtl" ? "r" : "l") . "-$px";
    }

    /**
     * @param string $string
     * @param bool $condition
     * @param string|null $else
     * @return void
     */
    public function echo (string $string, bool $condition, ?string $else = null): void {
        if ($condition)
            echo $string;
        else if (!empty($else))
            echo $else;
    }

    /**
     * @param mixed $value
     * @param bool $condition
     * @param mixed $else
     * @return mixed|void
     */
    public function return ($value, bool $condition, $else = null) {
        if ($condition)
            return $value;
        else if (!empty($else))
            return $else;
    }

    /**
     * @param string $name
     * @param string $value
     * @param bool $condition
     * @return void
     */
    public function attr (string $name, string $value, bool $condition): void {
        if ($condition) echo " $name='$value' ";
    }

    /**
     * @param string|null $space
     * @param int $number
     * @return string
     */
    public function space (?string $space = null, int $number = 1): string {
        $spaces = "";

        for ($i = 0; $i < $number; $i++) {
            if (empty($space))
                $spaces .= "<div class='mt-10 p-5px width-100-cent db'></div>";
            else
                $spaces .= $space;
        }

        return $spaces;
    }

    /**
     * @param string|int $id
     * @param bool $for_table
     * @return string
     */
    public function checkbox ($id = 0, bool $for_table = false): string {
        $rand = "ch" . rand(1, 100000);
        return '<input type="checkbox" id="'.$rand.'" class="dn" data-id="'.$id.'"><label for="'.$rand.'" class="check'.($for_table ? " for-table" : "").'"><svg width="18px" height="18px" viewBox="0 0 18 18"><path d="M1,9 L1,3.5 C1,2 2,1 3.5,1 L14.5,1 C16,1 17,2 17,3.5 L17,14.5 C17,16 16,17 14.5,17 L3.5,17 C2,17 1,16 1,14.5 L1,9 Z"></path><polyline points="1 9 7 14 15 4"></polyline></svg></label>';
    }

    /**
     * @param string $table
     * @param array $head
     * @param array $row
     * @param array $id
     * @param bool $checkbox
     * @return bool
     */
    public function modern_table (string $table, array $head, array $row, array $id, bool $checkbox = false): bool {
        if (!isset($this->FLOAT["modern-tables"][$table]))
            $this->SAVER["modern-tables"][$table] = [];

        $this->FLOAT["modern-tables"][$table] = [
            "head" => $head,
            "row"  => $row,
            "id"   => $id,
            "checkbox" => $checkbox
        ];

        return true;
    }

    /**
     * @param string $table
     * @param bool $unset
     * @return string
     */
    public function get_modern_table (string $table, bool $unset = true): string {
        if (!isset($this->FLOAT["modern-tables"][$table]))
            return "";

        $name   = $table;
        $table  = $this->FLOAT["modern-tables"][$name];
        $header = "";
        $rows   = "";

        $checkbox = $table["checkbox"];

        foreach ($table["head"] as $k => $v) {
            if ($checkbox) {
                $header .= '<th><div class="width-100-cent flex">' . $this->checkbox('all', true) . $v . '</div></th>';
                $checkbox = false;
            } else
                $header .= '<th>' . $v . '</th>';
        }

        foreach ($table["row"] as $key => $items) {
            $id = $table["id"][$key] ?? 0;
            $rows .= '<tr data-id="' . $id . '">';
            foreach ($items as $k => $v) {
                if ($k == 0 && $table["checkbox"])
                    $rows .= '<td class="flex" data-label="' . $table["head"][$k] . '"><div class="width-100-cent height-100-cent flex flex-start">' . $this->checkbox($id, true) . $v . '</div></td>';
                else
                    $rows .= '<td class="p-content" data-label="' . $table["head"][$k] . '">' . $v . '</td>';
            }
            $rows .= '</tr>';
        }

        if ($unset) // Free memory
            unset($this->FLOAT["modern-tables"][$name]);

        return '<table class="ufo-table ufo-table-' . $name . ' ' . ($table["checkbox"] ? " has-checkbox" : "") . '">
                    <thead>' . $header . '</thead>
                    <tbody>' . $rows . '</tbody>
                </table>';
    }

    /**
     * @param array $data
     * @return array
     * @throws Exception
     */
    public function add_comment (array $data): array {
        global $db;

        if (!$this->has_in_array(["pid", "comment", "for"], $data))
            return ["add" => false, "page" => []];

        $page = (new UFO_Pages())->get($data["pid"]);

        if (!$page)
            return ["add" => false, "page" => []];

        return [
            "add"  => $db->insert("comments", [
                "mid"      => (int) ($data["mid"] ?? (!isset($data["aid"]) ? ($this->get_member()["uid"] ?? 0) : 0)),
                "aid"      => (int) ($data["aid"] ?? (!isset($data["mid"]) ? ($this->get_admin()["id"] ?? 0) : 0)),
                "pid"      => (int) $page["id"],
                "guest"    => isset($data["guest"]) ? json_encode($data["guest"], JSON_UNESCAPED_UNICODE) : null,
                "comment"  => $data["comment"],
                "dateTime" => $this->dateTime(),
                "rate"     => (int) ($data["rate"] ?? 0),
                "more"     => isset($data["more"]) && is_array($data["more"]) ? json_encode($data["more"], JSON_UNESCAPED_UNICODE) : null,
                "_for"     => $data["for"],
                "_reply"   => (int) ($data["replay"] ?? 0),
                "accept"   => $db->meta("accept_comment") == "true" ? 1 : 0
            ]),
            "page" => $page
        ];
    }

    /**
     * @param string $for
     * @param bool $paging
     * @param int $page
     * @param int $limit
     * @param array $where
     * @param string|null $paging_action
     * @param string $orderDirection
     * @return array
     * @throws Exception
     */
    public function get_comments (string $for = "article", bool $paging = false, int $page = 1, int $limit = 0, array $where = [], ?string $paging_action = null, string $orderDirection = "DESC"): array {
        global $db;
        try {
            if ($limit == 0)
                $limit = (int) $db->meta("table_rows");

            if (!empty($for))
                $where["_for"] = $for;

            $db->helper->orderBy("id", $orderDirection);

            if ($paging) {
                $row = $db->pagination("comments", [
                    "page"  => $page,
                    "limit" => $limit,
                    "paging_action" => $paging_action ?? "comments-table-paging"
                ], $where);
            } else {
                $db->helper->orderBy("id");
                $row = ["rows" => $db->get("comments", $where)];
            } $fix = [];

            foreach ($row["rows"] as $item) {
                $admin = $item["aid"] != 0 ? $this->get_admin((int) $item["aid"]) : false;
                $reply = function ($list) {
                    global $db; $fix = [];
                    foreach ($list as $item) {
                        $admin = $item["aid"] != 0 ? $this->get_admin((int) $item["aid"]) : false;
                        $fix[] = [
                            "id"       => $item["id"],
                            "page"     => (new UFO_Pages())->get($item["pid"]),
                            ($item["mid"] != 0 ? "member" : (!empty($item["guest"]) ? "guest" : ($admin ? "admin" : "unknown"))) => ($item["mid"] != 0 ? $this->get_member((int) $item["mid"]) : (!empty($item["guest"]) ? json_decode($item["guest"], true) : ($admin ?? []))),
                            "comment"  => $item["comment"],
                            "dateTime" => $item["dateTime"],
                            "for"      => $item["_for"],
                            "reply"    => $db->get("comments", "_reply", $item["id"]),
                            "is_reply" => true,
                            "rate"     => $item["rate"],
                            "more"     => $this->is_json($item["more"]) ? json_decode($item["more"], true) : $item["more"],
                            "accept"   => $item["accept"] ?? false
                        ];
                    }
                    return $fix;
                };

                if ($item["_reply"] != 0) continue;

                $fix[] = [
                    "id"       => $item["id"],
                    "page"     => (new UFO_Pages())->get($item["pid"]),
                    ($item["mid"] != 0 ? "member" : (!empty($item["guest"]) ? "guest" : ($admin ? "admin" : "unknown"))) => ($item["mid"] != 0 ? $this->get_member((int) $item["mid"]) : (!empty($item["guest"]) ? json_decode($item["guest"], true) : ($admin ?? []))),
                    "comment"  => $item["comment"],
                    "dateTime" => $item["dateTime"],
                    "for"      => $item["_for"],
                    "reply"    => $reply($db->get("comments", "_reply", $item["id"])),
                    "rate"     => $item["rate"],
                    "is_reply" => false,
                    "more"     => $this->is_json($item["more"]) ? json_decode($item["more"], true) : $item["more"],
                    "accept"   => $item["accept"] ?? false
                ];
            }

            $row["rows"] = $fix;

            return $row;
        } catch (Exception $e) {
            $this->error($e);
            return ["rows" => []];
        }
    }

    /**
     * @param int $id
     * @return array|false
     * @throws Exception
     */
    public function get_comment (int $id) {
        global $db;

        $comment = $db->get("comments", "id", $id);

        if (isset($comment[0])) {
            $admin = $comment[0]["aid"] != 0 ? $this->get_admin((int) $comment[0]["aid"]) : false;
            $reply = function ($list) {
                global $db; $fix = [];
                foreach ($list as $item) {
                    $admin = $item["aid"] != 0 ? $this->get_admin((int) $item["aid"]) : false;
                    $fix[] = [
                        "id"       => $item["id"],
                        "page"     => (new UFO_Pages())->get($item["pid"]),
                        ($item["mid"] != 0 ? "member" : (!empty($item["guest"]) ? "guest" : ($admin ? "admin" : "unknown"))) => ($item["mid"] != 0 ? $this->get_member((int) $item["mid"]) : (!empty($item["guest"]) ? json_decode($item["guest"], true) : ($admin ?? []))),
                        "comment"  => $item["comment"],
                        "dateTime" => $item["dateTime"],
                        "for"      => $item["_for"],
                        "reply"    => $db->get("comments", "_reply", $item["id"]),
                        "is_reply" => true,
                        "rate"     => $item["rate"],
                        "more"     => $this->is_json($item["more"]) ? json_decode($item["more"], true) : $item["more"],
                        "accept"   => $item["accept"] ?? false
                    ];
                }
                return $fix;
            };

            return [
                "id"       => $comment[0]["id"],
                "page"     => (new UFO_Pages())->get($comment[0]["pid"]),
                ($comment[0]["mid"] != 0 ? "member" : (!empty($comment[0]["guest"]) ? "guest" : ($admin ? "admin" : "unknown"))) => ($comment[0]["mid"] != 0 ? $this->get_member((int) $comment[0]["mid"]) : (!empty($comment[0]["guest"]) ? json_decode($comment[0]["guest"], true) : ($admin ?? []))),
                "comment"  => $comment[0]["comment"],
                "dateTime" => $comment[0]["dateTime"],
                "for"      => $comment[0]["_for"],
                "reply"    => $reply($db->get("comments", "_reply", $comment[0]["id"])),
                "rate"     => $comment[0]["rate"],
                "is_reply" => false,
                "more"     => $this->is_json($comment[0]["more"]) ? json_decode($comment[0]["more"], true) : $comment[0]["more"],
                "accept"   => $comment[0]["accept"] ?? false
            ];
        }

        return false;
    }

    /**
     * @param int $id
     * @return bool
     * @throws Exception
     */
    public function accept_comment (int $id): bool {
        global $db;
        return $db->update("comments", [
            "accept" => 1
        ], "id", $id);
    }

    /**
     * @param int $id
     * @return bool
     * @throws Exception
     */
    public function remove_comment (int $id) : bool {
        global $db;
        return $db->remove("comments", "id", $id);
    }

    /**
     * @param int $sender
     * @param int $newSender
     * @param bool $admin
     * @return bool
     * @throws Exception
     */
    public function transformCommentsTo (int $sender, int $newSender, bool $admin = true): bool {
        global $db;
        if ($admin) {
            return $db->update("comments", [
                "aid" => $newSender
            ], "aid", $sender);
        } else {
            return $db->update("comments", [
                "mid" => $newSender
            ], "mid", $sender);
        }
    }

    /**
     * @param int $pid
     * @return array
     * @throws Exception
     */
    public function avgCommentsRate (int $pid): array {
        global $db;

        $row = $db->query("SELECT COUNT(`id`) as total, ROUND(AVG(`rate`), 1) as rate FROM `%prefix%comments` WHERE `pid`='$pid' AND `accept`=1")[0];

        if (empty($row["rate"]))
            $row["rate"] = 0;

        return $row;
    }

    /**
     * @param int $pid
     * @return int
     * @throws Exception
     */
    public function countComments (int $pid): int {
        global $db;
        return (int) $db->query("SELECT COUNT(`id`) as c FROM `%prefix%comments` WHERE `pid`='$pid' AND `accept`=1")[0]["c"] ?? 0;
    }

    /**
     * @return void
     */
    public function default_page (): void {
        $this->load_layout("pages" . SLASH . $this->FLOAT["menu"][0]["page"]);
    }

    /**
     * @return string
     * @throws Exception
     */
    public function admin_ajax (): string {
        return URL_ADMIN . "ajax.php?ajax_key=" . ($this->isset_key($this->get_admin(), "ajax_key") ? $this->get_admin()["ajax_key"] : "");
    }

    /**
     * @param string $str
     * @param string $char
     * @return bool
     */
    public function has_char (string $str, string $char): bool {
        return strpos($str, $char) !== false;
    }

    /**
     * @param $array
     * @param $key
     * @return array
     */
    public function minifyArray ($array, string $key): array {
        $results = [];

        if (is_array($array)) {
            if (isset($array[$key]) && key($array) == $key)
                $results[] = $array[$key];

            foreach ($array as $sub_array)
                $results = array_merge($results, $this->minifyArray($sub_array, $key));
        }

        return $results;
    }

    /**
     * @param $lang
     * @param $address
     * @return void
     */
    public function add_lng (string $json_file): void {
        $this->FLOAT["lang"] = $this->FLOAT["lang"] ?? [];

        if (!file_exists($json_file))
            return;

        if (!$this->is_json(
            file_get_contents($json_file),
            $json_file
        )) return;

        $this->FLOAT["lang"] = array_merge(
            $this->FLOAT["lang"], $json_file
        );
    }

    /**
     * @param string $text
     * @return string
     */
    public function lng (string $text): string {
        return $this->FLOAT["lang"][$text] ?? $text;
    }

    /**
     * Replace language text
     *
     * @param string $text
     * @param mixed ...$values
     * @return string
     */
    public function rlng (string $text, ...$values): string {
        $explode = explode("%n", $this->lng($text));
        $newText = "";

        for ($i = 0; $i < count($explode); $i++)
            $newText .= $explode[$i] . ($values[$i] ?? "");

        return $newText;
    }

    /**
     * @return array
     */
    public function all_lng (): array {
        return $this->FLOAT["lang"];
    }

    /**
     * @param string $url
     * @return bool
     */
    public function redirect (string $url): bool {
        ob_start();
        header("Location: $url");
        ob_end_flush();
        return true;
    }

    /**
     * @param string|array $key
     * @param string|null $val
     * @param string|null $url
     * @param bool $redirect
     * @return string
     * @throws Exception
     */
    public function urlAddParam ($key, ?string $val = null, ?string $url = null, bool $redirect = true): string {
        $key = !is_array($key) ? [$key => $val] : $key;
        $url = $this->url_info($url ?? $this->get_url());

        $url["queries"] = array_merge(
            $url["queries"], $key
        );

        $url = $this->rebuild_url($url);

        return $redirect ? $this->redirect($url) : $url;
    }

    /**
     * @param string|array|null $param
     * @param string|null $url
     * @param bool $redirect
     * @return string
     * @throws Exception
     */
    public function urlRemoveParam ($param = null, ?string $url = null, bool $redirect = true): string {
        $param = !is_array($param) && !$this->equal($param, null) ? [$param] : $param;
        $url = empty($url) ? $this->get_url() : $url;
        $url = $this->url_info($url);

        if (empty($param))
            $url["queries"] = [];
        else {
            foreach ($param as $query) {
                foreach ($url["queries"] as $key => $v) {
                    if ($this->equal($query, $key))
                        unset($url["queries"][$key]);
                }
            }
        }

        $url = $this->rebuild_url($url);

        return $redirect ? $this->redirect($url) : $url;
    }

    /**
     * @param string|null $url
     * @param bool $redirect
     * @return bool|string
     * @throws Exception
     */
    public function clearUrlParams (?string $url = null, bool $redirect = true) {
        $url = empty($url) ? $this->get_url() : $url;
        $url = $this->url_info($url);

        $url["queries"] = [];

        $url = $this->rebuild_url($url);

        return $redirect ? $this->redirect($url) : $url;
    }

    /**
     * @return bool
     */
    public function reloadedHere (): bool {
        if ($this->isset_key($_SERVER, "HTTP_CACHE_CONTROL")) {
            return (
                $this->equal($_SERVER["HTTP_CACHE_CONTROL"], "max-age=0") ||
                $this->equal($_SERVER["HTTP_CACHE_CONTROL"], "no-cache")
            ) == 1;
        } else return false;
    }

    /**
     * @return array
     */
    public function lastPage (): array {
        return $_SESSION["ufo_last_page"] ?? [
            "page" => "dashboard"
        ];
    }

    /**
     * @param object|array $object
     * @return array|object
     */
    public function object_to_array ($object) {
        if (is_object($object))
            return array_map([
                $this, "object_to_array"
            ], get_object_vars($object));
        else if (is_array($object))
            return array_map([$this, "object_to_array"], $object);
        return $object;
    }

    /**
     * @param string|null $type
     * @param array $where
     * @return array|bool
     */
    public function this_page (?string $type = "page", array $where = []) {
        global $db;

        try {
            $url = $this->this_url_info();

            if (!isset($where["link"])) $where["link"] = urldecode(
                end($url["slashes"])
            );

            if (!empty($type))
                $where["type"] = $type;

            foreach ($where as $k => $v)
                $db->where($k, $v);

            $page = $db->helper->getOne("pages");

            // Prevent this page from being recognized as the main page
            if (!empty($page["link"]))
                return $page;
        } catch (Exception $e) {}

        return false;
    }

    /**
     * @return array|bool
     * @throws Exception
     */
    public function this_article () {
        return $this->this_page("article");
    }

    /**
     * @throws Exception
     * @return bool|array|UFO_Explorer
     */
    public function here () {
        $place = $this->this_page(null);

        if (is_array($place)) {
            if ($place["type"] == "page" || $place["type"] == "article") {
                $place = new UFO_Explorer([
                    "type"  => $place["type"],
                    "limit" => false,
                    "where" => [
                        "id" => $place["id"]
                    ],
                    "reset" => false
                ]);
            } else {
                $place = new UFO_Explorer([
                    "hunter" => "single-" . $place["type"],
                    "limit"  => false,
                    "id"     => $place["id"],
                    "reset"  => false
                ]);
            }
        }

        return is_object($place) ? ($place->hunt(function ($explorer) {
            return $explorer;
        })[0] ?? false) : false;
    }

    /**
     * @return string
     * @throws Exception
     */
    public function this_urn (): string {
        $url     = $this->this_url_info();
        $slashes = join("/", $url["slashes"]);
        $queries = http_build_query($url["queries"]);
        return urldecode("/" . trim(
            "$slashes?$queries" . (!empty($url["fragment"]) ? "#$url[fragment]" : ""), "/"
        ));
    }

    /**
     * @return string
     */
    public function full_url (): string {
        return (isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] === "on" ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
    }

    /**
     * @param string $page
     * @return bool
     */
    public function match_page (string $page): bool {
        return (bool) strpos($this->full_url(), $this->sanitize_link($page));
    }

    /**
     * @return string
     * @throws Exception
     */
    public function end_url (): string {
        return (string) end($this->this_url_info()["slashes"]);
    }

    /**
     * @param array $parsed
     * @return string
     */
    public function rebuild_url (array $parsed): string {
        $url = "";

        if (!empty($parsed["protocol"]))
            $url .= "$parsed[protocol]://";
        else if (!empty($parsed["scheme"]))
            $url .= "$parsed[scheme]://";

        if (!empty($parsed["user"])) {
            $url .= $parsed["user"];
            if (!empty($parsed["pass"]))
                $url .= ":$parsed[pass]";
            $url .= "@";
        }

        $url .= $parsed["host"] ?? "";

        if (!empty($parsed["port"]))
            $url .= ":$parsed[port]";

        if (!empty($parsed["slashes"]))
            $url .= "/" . implode("/", $parsed["slashes"]);
        else
            $url .= $parsed["path"] ?? "";

        $queries = $parsed["queries"] ?? $parsed["query"];
        if (!empty($queries))
            $url .= "?" . (is_array($queries) ? http_build_query($queries) : $queries);

        if (!empty($parsed["fragment"]))
            $url .= "#$parsed[fragment]";

        return $url;
    }

    /**
     * @param $url
     * @return mixed
     */
    public function is_url ($url) {
        return filter_var($url, FILTER_VALIDATE_URL);
    }

    /**
     * @param array|string $page
     * @param array|string $rule
     * @param array|string $title
     * @param $callback
     * @return void
     */
    public function add_rule ($page, $rule, $title = null, $callback = null) {
        $array = is_array($page) ? $page : (
            is_array($rule) ? $rule : []
        );

        if (!empty($array)) {
            for ($i = 0; $i < count($array); $i++) {
                $this->add_rule(
                    is_array($page) ? ($page[$i] ?? $page) : $page,
                    is_array($rule) ? ($rule[$i] ?? $rule) : $rule,
                    is_array($title) ? ($title[$i] ?? $title) : $title,
                    is_array($callback) ? $callback[$i] : $callback
                );
            }
            return;
        }

        if (!isset($this->FLOAT["rules"]))
            $this->FLOAT["rules"] = [];

        $rule = trim($rule, "/");

        $this->FLOAT["rules"][$rule] = [
            "rule"  => $this->sanitize_link("/" . $rule),
            "title" => $title,
            "path"  => $this->slash_folder($page),
            "callback" => $callback
        ];
    }

    /**
     * @return array
     */
    public function get_rules (): array {
        if (!isset($this->FLOAT["rules"]))
            $this->FLOAT["rules"] = [];

        $join = [];

        foreach ($this->FLOAT["rules"] as $k => $v)
            $join[$v["rule"]] = $v["path"];

        return $join;
    }

    /**
     * @return array|mixed
     */
    public function get_full_rules () {
        if (!isset($this->FLOAT["rules"]))
            $this->FLOAT["rules"] = [];
        return $this->FLOAT["rules"];
    }

    /**
     * @param string $url
     * @throws Exception
     * @return array
     */
    public function url_info (string $url): array {
        $parsed  = parse_url(
            $this->sanitize_xss($url)
        );
        $details = [
            "protocol" => $parsed["scheme"]   ?? null,
            "host"     => $parsed["host"]     ?? null,
            "port"     => $parsed["port"]     ?? null,
            "fragment" => $parsed["fragment"] ?? null
        ];

        $details["slashes"] = explode("/",
            trim($parsed["path"] ?? "", "/")
        );

        parse_str($parsed["query"] ?? "", $details["queries"]);

        return $details;
    }

    /**
     * @return array
     * @throws Exception
     */
    public function this_url_info (): array {
        $parsed = parse_url($this->web_link());
        $link   = str_replace(
            $parsed["path"] ?? "", "/",
            $this->get_url()
        );

        return $this->url_info($link) + [
            "base_path" => trim($parsed["path"] ?? "", "/")
        ];
    }

    /**
     * @param string $slug
     * @return string
     * @throws Exception
     */
    public function slug (string $slug): string {
        global $db;
        return URL_WEBSITE . $db->slug($slug) . "/";
    }

    /**
     * @param array $array
     * @return bool
     */
    public function values_true (array $array): bool {
        foreach ($array as $value)
            if ($value !== true)
                return false;
        return true;
    }

    /**
     * @param array $keys
     * @param array $array
     * @return bool
     */
    public function has_in_array (array $keys, array $array): bool {
        return count(array_intersect(
            $keys, array_keys($array)
        )) == count($keys);
    }

    /**
     * @param $list
     * @return array
     */
    public function v_sort ($list): array {
        asort($list);
        $new_list = array_reverse($list, true);
        $list     = [];
        foreach ($new_list as $k => $v)
            $list[] = $k;
        return $list;
    }

    /**
     * @param $array
     * @param $position
     * @param $insert
     * @return void
     */
    public function insert_array (&$array, $position, $insert) {
        if (is_int($position)) {
            array_splice($array, $position, 0, $insert);
        } else {
            $pos = array_search($position, array_keys($array));
            $array = array_merge(
                array_slice($array, 0, $pos),
                $insert,
                array_slice($array, $pos)
            );
        }
    }

    /**
     * @param $array
     * @param array $keys
     * @return mixed
     */
    public function array_exclude ($array, array $keys) {
        if ($this->has_keys($array)) {
            foreach ($keys as $key) {
                if ($this->isset_key($array, $key))
                    unset($array[$key]);
            }
        }
        return $array;
    }

    /**
     * @param $array
     * @return bool
     * @throws Exception
     */
    public function order_array (&$array): bool {
        $is_array = $this->is_array($array);

        if (!is_array($array) && !$is_array)
            $this->error("UFO Options (order_array) : The array is not valid");

        if ($is_array)
            $array = json_decode($array, true);

        return usort($array, fn($a, $b) =>
            ($a["order"] ?? $a["position"]) <=> ($b["order"] ?? $b["position"])
        );
    }

    /**
     * @param array $array
     * @return bool
     */
    public function has_keys (array $array): bool {
        $keys = array_keys($array);
        return $keys !== array_keys($keys);
    }

    /**
     * @param array $array
     * @param string|int $prefix
     * @param string|int $suffix
     * @return void
     */
    public function prefix_suffix_array (array &$array, $prefix, $suffix): void {
        if ($this->has_keys($array)) {
            foreach ($array as $key => $value) {
                $newKey = $prefix . $key . $suffix;

                $array[$newKey] = $value;

                unset($array[$key]);
            }
        } else {
            foreach ($array as &$value) {
                $value = $prefix . $value . $suffix;
            }
            // Since we're using a reference in the loop, unset the reference after the loop.
            unset($value);
        }
    }

    /**
     * @param array $array1
     * @param array $array2
     * @param bool $remove_excess
     * @return array
     */
    public function default (array $array1, array $array2, bool $remove_excess = false): array {
        /**
         * Merging sub-arrays
         */
        foreach ($array1 as $k1 => $item) {
            if (is_array($item)) {
                foreach ($array2 as $k2 => &$item2) {
                    if ($this->equal($k1, $k2)) {
                        $item2 = $this->default(
                            $item, $item2, $remove_excess
                        );
                    }
                }
            }
        }

        return array_merge($array1, $remove_excess ? array_intersect_key(
            $array1, $array2
        ) : $array2);
    }

    /**
     * @param $name
     * @param $args
     * @param bool $float
     * @return void
     */
    public function add_array ($name, $args, bool $float = true) {
        if (!$float) {
            if (!$this->isset_key($this->SAVER, "args"))
                $this->SAVER["args"] = [];

            if (!$this->isset_key($this->SAVER["args"], $name))
                $this->SAVER["args"][$name] = [];

            $this->SAVER["args"][$name][] = $args;
        } else {
            if (!$this->isset_key($this->FLOAT, "args"))
                $this->FLOAT["args"] = [];

            if (!$this->isset_key($this->FLOAT["args"], $name))
                $this->FLOAT["args"][$name] = [];

            $this->FLOAT["args"][$name][] = $args;
        }
    }

    /**
     * @param $name
     * @param bool $float
     * @return mixed
     */
    public function get_array ($name, bool $float = true) {
        if (!$float)
            return $this->isset_key($this->SAVER["args"], $name) ? $this->SAVER["args"][$name] : [];
        else {
            if (!$this->isset_key($this->FLOAT, "args"))
                $this->FLOAT["args"] = [];
            return $this->isset_key($this->FLOAT["args"], $name) ? $this->FLOAT["args"][$name] : [];
        }
    }

    /**
     * @param $key
     * @param $value
     * @param bool $float
     * @return void
     */
    public function add_kv ($key, $value, bool $float = true) {
        if (!$float) {
            $this->SAVER["kv"][$key] = $value;
        } else {
            if (!$this->isset_key($this->FLOAT, "kv")) $this->FLOAT["kv"] = [];
            $this->FLOAT["kv"][$key] = $value;
        }
    }

    /**
     * @param $key
     * @param bool $float
     * @return false|mixed
     */
    public function get_kv ($key, bool $float = true) {
        if (!$float) {
            if (!$this->isset_key($this->SAVER, "kv")) $this->SAVER["kv"] = [];
            return $this->isset_key($this->SAVER["kv"], $key) ? $this->SAVER["kv"][$key] : false;
        } else {
            if (!$this->isset_key($this->FLOAT, "kv")) $this->FLOAT["kv"] = [];
            return $this->isset_key($this->FLOAT["kv"], $key) ? $this->FLOAT["kv"][$key] : false;
        }
    }

    /**
     * @param array $target
     * @param array $array
     * @return int|mixed
     */
    public function find_by_kv (array $target, array $array) {
        foreach ($array as $key => $item) {
            if (isset($item[$target[0]])) {
                if ($item[$target[0]] == $target[1]) {
                    if (isset($target[2]))
                        return $item[$target[2]];
                    return $key;
                }
            }
        }
        return false;
    }

    /**
     * @param object|array $array
     * @param int|string|array $key
     * @return mixed
     */
    public function isset_key ($array, $key, bool $return = false) {
        $array = (array) $array;

        if (is_array($key)) {
            $exists = [];
            foreach ($key as $k)
                $exists[$k] = $this->isset_key($array, $k);
            return $this->values_true($exists);
        }

        $isset = isset($array[$key]) || array_key_exists($key, $array);

        if ($isset && $return)
            $isset = is_bool($array[$key]) ? true : $array[$key];

        return $isset;
    }

    /**
     * @param string|object|array $keys
     * @param bool $return
     * @return mixed
     * @throws Exception
     */
    public function isset_get ($keys = null, bool $return = false) {
        if ($keys === null)
            return $this->equal($_SERVER["REQUEST_METHOD"], "GET");
        return $this->isset_key($this->this_url_info()["queries"], $keys, $return);
    }

    /**
     * @param ?string|object|array $keys
     * @param bool $return
     * @return mixed
     */
    public function isset_post ($keys = null, bool $return = false) {
        if ($keys === null)
            return $this->equal($_SERVER["REQUEST_METHOD"], "POST");
        return $this->isset_key($_POST, $keys, $return);
    }

    /**
     * @param string|object|array $keys
     * @param bool $return
     * @return mixed
     */
    public function isset_session ($keys, bool $return = false) {
        return $this->isset_key($_SESSION, $keys, $return);
    }

    /**
     * @param string|object|array $keys
     * @param bool $return
     * @return mixed
     */
    public function isset_cookie ($keys, bool $return = false) {
        return $this->isset_key($_COOKIE, $keys, $return);
    }

    /**
     * @param $string
     * @param string $style
     * @param bool $clean
     * @param bool $out_process
     * @return string
     * @throws Exception
     */
    public function error ($string, string $style = "", bool $clean = true, bool $out_process = false): string {
        $html = !$out_process ? "<div class='flex flex-center align-center'><div class='system-notice system-notice-danger mt-20' style='max-width: 80%;".$style."'><span style='font-family: ufocms'>".$string."</span></div></div>" : "<div style='display:flex;justify-content: center'><div style='width: 80%;height: 80px;background: whitesmoke;border-radius: 0 8px 8px 0;font-family: system-ui;font-weight: bolder;display: flex;align-items: center;padding: 0 10px;box-sizing: border-box;border-".($this->dir() == "ltr" ? "left: 5px solid red;" : "right: 5px solid red;")."direction:".$this->dir().";$style'>$string</div></div>";
        if ($clean) {
            ob_clean();
            $this->load_layout("document");
            echo $html;
            $this->load_layout("endDoc");
        }
        return $html;
    }

    /**
     * @param string $msg
     * @param int $status
     * @param bool $html
     * @return void
     * @throws Exception
     */
    public function die (string $msg = "", int $status = 403, bool $html = false) {
        echo ($html ? $this->error($msg) : $msg);
        exit ($status);
    }

    /**
     * @param $status
     * @param $message
     * @param array $data
     * @param bool $returnArray
     * @return array|string
     */
    public function status ($status, $message = null, array $data = [], bool $returnArray = false) {
        $data = [
            "status"  => $status,
            "message" => empty($message) ? (
                $this->success($status) ? $this->lng(
                    "Done successfully"
                ) : $this->lng("System error")
            ) : $message,
        ] + $data;
        return !$returnArray ? json_encode($data, JSON_UNESCAPED_UNICODE) : $data;
    }

    /**
     * @param bool|numeric|string|array $status
     * @return bool
     */
    public function success ($status): bool {
        if (is_numeric($status))
            return $this->equal($status, 200);

        if (is_bool($status))
            return $status;

        $this->is_json($status, $status);

        if (is_array($status))
            return $this->equal($status["status"] ?? ($status[0] ?? 0), 200);

        return $this->equal($status, "true");
    }

    /**
     * @param array $array
     * @param array $data
     * @return array
     * @throws Exception
     */
    public function pagination (array $array, array $data = []): array {
        global $db;

        extract($data);

        $page  = $page ?? 1;
        $total = count($array);
        $limit = $limit ?? $db->table_rows;
        $pages = ceil($total / $limit);

        $page  = max($page, 1);
        $page  = min($page, $pages);

        $offset = ($page - 1) * $limit;
        if ($offset < 0) $offset = 0;

        return [
            "total"  => $total,
            "limit"  => $limit,
            "pages"  => $pages,
            "rows"   => array_slice($array, $offset, $limit),
            "paging" => $this->paging([
                "page"   => $page,
                "total"  => $pages,
                "action" => $action ?? null
            ])
        ];
    }

    /**
     * @param $data
     * @return string
     * @throws Exception
     */
    public function paging ($data): string {
        extract($data);

        $next_page = $page + 1;
        $prev_page = $page - 1;

        if ($this->dir() == "rtl") {
            $paging  = $this->tag('span', $this->tag('i', null, ["class" => "ufo-icon-chevrons-left"]), [
                "class" => "modern-paging-item",
                "data-page" => $total,
                "data-disabled" => $page >= $total ? "true" : "false",
                "data-action"  => $action
            ]);
            $paging .= $this->tag('span', $this->tag('i', null, ["class" => "ufo-icon-chevron-left"]), [
                "class" => "modern-paging-item",
                "data-page" => $next_page,
                "data-disabled" => $total < $next_page ? "true" : "false",
                "data-action"   => $action
            ]);
            $paging .= $this->tag('span', $this->lng("page") . " " . $page . " " . $this->lng("of") . " " . $total, [
                "class" => "of_page"
            ]);
            $paging .= $this->tag('span', $this->tag('i', null, ["class" => "ufo-icon-chevron-right"]), [
                "class" => "modern-paging-item",
                "data-page" => $prev_page,
                "data-disabled" => $page > 1 ? "false" : "true",
                "data-action"   => $action
            ]);
            $paging .= $this->tag('span', $this->tag('i', null, ["class" => "ufo-icon-chevrons-right"]), [
                "class" => "modern-paging-item",
                "data-page" => 1,
                "data-disabled" => $page > 1 ? "false" : "true",
                "data-action"   => $action
            ]);
        } else {
            $paging  = $this->tag('span', $this->tag('i', null, ["class" => "ufo-icon-chevrons-left"]), [
                "class" => "modern-paging-item",
                "data-page" => 1,
                "data-disabled" => $page > 1 ? "false" : "true",
                "data-action"   => $action
            ]);
            $paging .= $this->tag('span', $this->tag('i', null, ["class" => "ufo-icon-chevron-left"]), [
                "class" => "modern-paging-item",
                "data-page" => $prev_page,
                "data-disabled" => $page > 1 ? "false" : "true",
                "data-action"   => $action
            ]);
            $paging .= $this->tag('span', $this->lng("page") . " " . $page . " " . $this->lng("of") . " " . $total, [
                "class" => "of_page"
            ]);
            $paging .= $this->tag('span', $this->tag('i', null, ["class" => "ufo-icon-chevron-right"]), [
                "class" => "modern-paging-item",
                "data-page" => $next_page,
                "data-disabled" => $total < $next_page ? "true" : "false",
                "data-action"   => $action
            ]);
            $paging .= $this->tag('span', $this->tag('i', null, ["class" => "ufo-icon-chevrons-right"]), [
                "class" => "modern-paging-item",
                "data-page" => $total,
                "data-disabled" => $page >= $total ? "true" : "false",
                "data-action"   => $action
            ]);
        }

        return $this->tag("div", $paging, ["class" => "modern-paging"]);
    }

    /**
     * @param array $array
     * @return mixed|string|void
     */
    public function new_shortcode (array $array) {
        global $_;

        if ($this->isset_key($array, "name")) {
            /**
             * Add - Shortcodes Array
             */
            if (!$this->isset_key($this->FLOAT, "shortcodes"))
                $this->FLOAT["shortcodes"] = [];

            /**
             * Add for this plugin
             */
            if (!isset($this->FLOAT["plugin_shortcodes"]))
                $this->FLOAT["plugin_shortcodes"] = [];

            if ($this->isset_key($_, "this_plugin") && !empty($_["this_plugin"])) {
                $plugin = $_["this_plugin"]["manifest"]["name"];

                if (!isset($this->FLOAT["plugin_shortcodes"][$plugin]))
                    $this->FLOAT["plugin_shortcodes"][$plugin] = [];

                $this->FLOAT["plugin_shortcodes"][$plugin][] = $array;
            }

            /**
             * Add for this template
             */
            if (!isset($this->FLOAT["template_shortcodes"]))
                $this->FLOAT["template_shortcodes"] = [];

            if ($this->isset_key($_, "this_template") && !empty($_["this_template"])) {
                $template = $_["this_template"]["manifest"]["name"];

                if (!isset($this->FLOAT["template_shortcodes"][$template]))
                    $this->FLOAT["template_shortcodes"][$template] = [];

                $this->FLOAT["template_shortcodes"][$template][] = $array;
            }

            /**
             * Append To Shortcodes
             */
            $this->FLOAT["shortcodes"][] = [
                "name"    => $array["name"],
                "content" => $array["content"] ?? "",
                "editor"  => $this->isset_key($array, "editor", true)
            ];
        } else return $this->lng("Error: Please set name shortcode");
    }

    /**
     * @param string $string
     * @return string
     */
    public function run_shortcodes (string $string): string {
        return $this->do_work("ufo_render_shortcodes", $string);
    }

    /**
     * @param string $name
     * @return false|array
     */
    public function get_shortcode (string $name) {
        $shortcode = [];

        foreach ($this->FLOAT["shortcodes"] ?? [] as $item) {
            if ($name == $item["name"]) {
                $shortcode = $item;
                break;
            }
        }

        return $shortcode;
    }

    /**
     * @return array
     */
    public function get_all_shortcodes (): array {
        return array_merge($this->FLOAT["shortcodes"] ?? [], [
            "plugins"   => $this->FLOAT["plugin_shortcodes"] ?? [],
            "templates" => $this->FLOAT["template_shortcodes"] ?? []
        ]);
    }

    /**
     * @param array|string $name
     * @param $fn
     * @return void
     */
    public function add_work ($name, $fn) {
        global $_;

        /**
         * Add multiple (works) simultaneously
         */
        if (is_array($name)) {
            foreach ($name as $work)
                $this->add_work($work, $fn);
            return;
        }

        /**
         * Add - UFO works to float
         */
        $this->FLOAT["ufo_works"] = $this->FLOAT["ufo_works"] ?? [];

        /**
         * Check exists
         */
        if (isset($this->FLOAT["ufo_works"][$name]))
            return;

        /**
         * Add for this plugin
         */
        if (!isset($this->FLOAT["ufo_plugin_works"]))
            $this->FLOAT["ufo_plugin_works"] = [];

        if ($this->isset_key($_, "this_plugin") && !empty($_["this_plugin"])) {
            $plugin = $_["this_plugin"]["manifest"]["name"];

            if (!isset($this->FLOAT["ufo_plugin_works"][$plugin]))
                $this->FLOAT["ufo_plugin_works"][$plugin] = [];

            $this->FLOAT["ufo_plugin_works"][$plugin][] = $name;
        }

        /**
         * Add for this template
         */
        if (!isset($this->FLOAT["ufo_template_works"]))
            $this->FLOAT["ufo_template_works"] = [];

        if ($this->isset_key($_, "this_template") && !empty($_["this_template"])) {
            $template = $_["this_template"]["manifest"]["name"];

            if (!isset($this->FLOAT["ufo_template_works"][$template]))
                $this->FLOAT["ufo_template_works"][$template] = [];

            $this->FLOAT["ufo_template_works"][$template][] = $name;
        }

        $this->FLOAT["ufo_works"][$name] = $fn;
    }

    /**
     * @param array|string $name
     * @param mixed $arg
     * @param bool $now
     * @return mixed
     */
    public function do_work ($name, $arg = [], bool $now = true) {
        global $_;

        if (is_array($name)) {
            $result = [];
            foreach ($name as $work)
                $result[$work] = $this->do_work($work, $arg, $now);
            return $result;
        }

        if (!$now) {
            if (!isset($_["waite_works"]))
                $_["works"] = [];

            $_["works"][$name] = $arg;
            return true;
        } else {
            if (!isset($this->FLOAT["ufo_works"][$name]))
                return false;

            return $this->call($this->FLOAT["ufo_works"][$name], $arg);
        }
    }

    /**
     * @return array
     */
    public function get_all_works (): array {
        return ($this->FLOAT["ufo_works"] ?? []) + [
            "plugins"   => $this->FLOAT["ufo_plugin_works"],
            "templates" => $this->FLOAT["ufo_template_works"]
        ];
    }

    /**
     * @param string $name
     * @param $callback
     * @param bool $guest
     * @param $key
     * @return void
     */
    public function add_ajax (string $name, $callback, bool $guest = false, $key = null) {
        if (!isset($this->FLOAT["ufo_ajax"]))
            $this->FLOAT["ufo_ajax"] = [];

        $this->FLOAT["ufo_ajax"][$name] = [
            "callback" => $callback,
            "guest"    => $guest,
            "key"      => empty($key) ? ($this->do_work("ufo_ajax_key") ?? false) : $key
        ];
    }

    /**
     * @param string $name
     * @param $key
     * @return false|int|mixed|string
     * @throws Exception
     */
    public function do_ajax (string $name, $key = null) {
        if (!$_SERVER["REQUEST_METHOD"] == "POST")
            return false;

        if (!isset($this->FLOAT["ufo_ajax"]))
            $this->FLOAT["ufo_ajax"] = [];

        if (!isset($this->FLOAT["ufo_ajax"][$name]))
            return false;

        $ajax = $this->FLOAT["ufo_ajax"][$name];

        if (!$ajax["guest"]) {
            if ($this->is_admin() && !$this->check_login_admin())
                return false;
            if (defined("AJAX_FRONT") && !$this->check_login_member())
                return false;
        }

        if ($ajax["key"] == (empty($key) ? $this->do_work("ufo_ajax_key") : $key)) {
            if ($this->is_function($ajax["callback"])) {
                ob_start();
                if (empty($run = $ajax["callback"]())) {
                    $run = ob_get_flush();
                    ob_clean();
                }
                return $run;
            }
        }

        return false;
    }

    /**
     * @return array
     */
    public function prevent_ajax (): array {
        if ($_SERVER["REQUEST_METHOD"] == "POST" && $this->isset_post("prevent_ajax")) {
            $ex = explode(",", $_POST["prevent_ajax"]); $ed = [];
            foreach ($ex as $items)
                $ed[$items] = false;
            return $ed;
        } else return [];
    }

    /**
     * @param string $title
     * @param string $string
     * @param bool $float
     * @return bool
     */
    public function string (string $title, string $string, bool $float = true): bool {
        if ($float) {
            $this->FLOAT["float_string"] = $this->FLOAT["float_string"] ?? [];
            $this->FLOAT["float_string"][$title] = $string;
        } else {
            $this->SAVER["float_string"] = $this->FLOAT["float_string"] ?? [];
            $this->SAVER["float_string"][$title] = $string;
        }
        return true;
    }

    /**
     * @param string $string
     * @return array
     */
    public function str_split (string $string): array {
        $len    = mb_strlen($string);
        $result = [];
        for ($i = 0; $i < $len; $i++)
            $result[] = mb_substr($string, $i, 1);
        return $result;
    }

    /**
     * @param $numbers
     * @param string $type
     * @return string
     */
    public function replace_number ($numbers, string $type = "en"): string {
        $EN = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9', '.'];
        $FA = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹', ','];
        return $type == 'fa' ? str_replace($EN, $FA, $numbers) : str_replace($FA, $EN, $numbers);
    }

    /**
     * @param $number
     * @param string $separator
     * @param bool|array $unit
     * @return array|false|string
     */
    public function minify_number ($number, string $separator = ",", $unit = true) {
        $absNumber = abs($number);
        $unitIndex = min(intdiv(log10($absNumber), 3), 4); // Maximum index is 4 (for quintillion)

        // Calculate the percentage number for each unit
        $percent = 10 ** ($unitIndex * 3);

        $units  = is_array($unit) ? $unit : [
            "", "K", "M", "B", "T"
        ];
        $number = number_format(
            $percent > 0 ? $number / $percent : $number,
            $percent == 0 ? 0 : 3,
            $separator,
            $separator
        );

        // Find the position of the symbol
        $symbolPosition = strpos($number, $separator);

        // If the symbol is not found or is the last character, return the original string
        if ($symbolPosition === false || $symbolPosition === strlen($number) - 1)
            return $unit ? [$number, $units[$unitIndex]] : $number;

        // Extract the substring up to the symbol (including the symbol)
        $result = substr($number, 0, $symbolPosition);

        // Check if the first character after the symbol is '0'
        if ($number[$symbolPosition + 1] === '0')
            return $unit ? [$result, $units[$unitIndex]] : $result;

        // Otherwise, return the original string
        return $unit ? [$number, $units[$unitIndex]] : $number;
    }

    /**
     * @param int $number
     * @return string
     */
    public function back_folder (int $number = 1): string {
        $join = "";
        for ($i = 0; $i < $number; $i++)
            $join .= $this->slash_folder("../");
        return $join;
    }

    /**
     * @param $address
     * @return string
     */
    public function slash_folder ($address): string {
        return str_replace(["/","\/","//","\\","\\\\","////"], DIRECTORY_SEPARATOR, $address);
    }

    /**
     * @param $f
     * @return bool
     */
    public function is_function ($f): bool {
        return (is_string($f) && function_exists($f)) || (is_object($f) && ($f instanceof Closure));
    }

    /**
     * @param $object
     * @param $method
     * @return bool
     */
    public function is_callable ($object, $method): bool {
        if (method_exists($object, $method)) {
            $reflection = new ReflectionMethod($object, $method);
            return $reflection->isPublic();
        }
        return false;
    }

    /**
     * @param mixed $fn
     * @param mixed $data
     * @return array
     */
    public function fn ($fn, $data = []): array {
        return [
            "data" => $data,
            "fn"   => $this->is_function($fn) ? $fn : fn() => $fn
        ];
    }

    /**
     * @param $target
     * @param ...$args
     * @return mixed
     */
    public function call ($target, ...$args) {
        $result = null;

        // Function
        if ($this->is_function($target))
            $result = $target(...$args);

        // Class
        elseif (is_array($target) && (isset($target[0]) && (
            is_object($target[0]) || is_string($target[0])
        ))) {
            $target[0] = new $target[0];
            if ($this->is_callable($target[0], $target[1]))
                $result = call_user_func($target, ...$args);
        }

        // Function name
        elseif (is_string($target) && function_exists($target))
            $result = call_user_func($target, ...$args);

        // Array function with data, $ufo->fn
        elseif (is_array($target)) {
            if (isset($target["data"]) && isset($target["fn"])) {
                if ($this->is_function($target["fn"]))
                    $result = $target["fn"]($target["data"], ...$args);
            }
        }

        return $result;
    }

    /**
     * @param $folderName
     * @param string $fileType
     * @return array
     */
    public function get_file_list ($folderName, string $fileType = ""): array {
        $array_files = [];

        if (substr($folderName, strlen($folderName) - 1) != SLASH)
            $folderName .= SLASH;

        $folderName = $this->slash_folder($folderName);

        foreach (glob($folderName . "*" . $fileType) as $filename) {
            if (is_dir($filename))
                $type = "folder";
            else
                $type = "file";

            if ($type == "folder") {
                if (is_dir($this->slash_folder($folderName . "/" . str_replace($folderName, '', $filename)))) {
                    $subFolder = $this->get_file_list($this->slash_folder($folderName . '/' . str_replace($folderName, "", $folderName . $filename)));
                    $array_files[] = [
                        "folderName" => str_replace($folderName, "", $filename),
                        "sub-folder" => $subFolder
                    ];
                }
            } else {
                $array_files[] = [$type => $filename];
            }
        }

        return $array_files;
    }

    /**
     * @param $folder
     * @param $format
     * @param string $types
     * @param string $sort_by
     * @return array
     */
    public function get_file_subfolder ($folder = null, $format = null, string $types = "*", string $sort_by = "time"): array {
        if (empty($folder))
            $folder = $this->slash_folder("../content/files/");

        if (is_dir($folder)) {
            $k_v = [];
            $new_sort_list   = [];
            $new_format_list = [];
            $new_types_list  = [];
            $get = array_diff(scandir($folder), [".", ".."]);
            $fix_link = substr(URL_WEBSITE, 0, -1);
            $fix_link = $this->sanitize_link($fix_link . $folder);

            foreach ($get as $item)
                $k_v[$item] = $folder . $item;

            if ($sort_by == "time") {
                foreach ($k_v as $items)
                    $new_sort_list[$items] = filectime($items);

                $new_files = $this->v_sort($new_sort_list);
                $new_sort_list = [];

                foreach ($new_files as $k => $v)
                    if (is_file($v))
                        $new_sort_list[pathinfo($v)["filename"]] = $v;

                $k_v = $new_sort_list;
            }

            if ($format == "link") {
                foreach ($k_v as $item)
                    $new_format_list[] = $fix_link . pathinfo($item)["basename"];
                $k_v = $new_format_list;
            }

            if ($types != "*") {
                foreach ($k_v as $item)
                    if ($this->available_type($types, pathinfo($item)["extension"]))
                        $new_types_list[] = $item;
                $k_v = $new_types_list;
            }

            return $k_v;
        }

        return [];
    }

    /**
     * @param $dir
     * @param string $sort_by
     * @return array|false
     */
    public function all_folders ($dir = null, string $sort_by = "time") {
        if (empty($dir))
            $dir = $this->slash_folder("../content/files");

        $folders  = glob($this->slash_folder($dir . '/*') , GLOB_ONLYDIR);
        $new_list = [];

        if ($sort_by == "time") {
            foreach ($folders as $items)
                $new_list[$items] = filectime($items);
            $folders = $this->v_sort($new_list);
        }

        return $folders;
    }

    /**
     * @return string
     */
    public function join_path (): string {
        $args  = func_get_args();
        $paths = [];

        foreach ($args as $arg)
            $paths = array_merge($paths, (array) $arg);

        return join("/", array_filter(array_map(function ($p) {
            return trim($p, "/");
        }, $paths)));
    }

    /**
     * @param $file
     * @return bool
     */
    public function delete_file ($file): bool {
        if (file_exists($file))
            return unlink($file);
        return false;
    }

    /**
     * @param $dir
     * @return bool
     */
    public function delete_folder ($dir): bool {
        $dir = $this->slash_folder($dir);

        if (!file_exists($dir))
            return true;

        if (!is_dir($dir))
            return unlink($dir);

        foreach (scandir($dir) as $item) {
            if ($item == "." || $item == "..") continue;
            if (!$this->delete_folder($this->slash_folder($dir . "/" . $item)))
                return false;
        }

        return rmdir($dir);
    }

    /**
     * @return string[]
     */
    public function size_units (): array {
        return ["B", "KB", "MB", "GB", "TB", "PB", "EB", "ZB", "YB"];
    }

    /**
     * @param $size
     * @param int $decimals
     * @return array
     */
    public function convert_size ($size, int $decimals = 2): array {
        $power = $size > 0 ? floor(log($size, 1024)) : 0;
        return [
            "size" => number_format($size / pow(1024, $power), $decimals, ".", ","),
            "unit" => $this->size_units()[$power]
        ];
    }

    /**
     * @param $size
     * @param $type
     * @return float|int|string
     */
    public function convert_to_byte ($size, $type) {
        $types = $this->size_units();
        if($key = array_search($type, $types))
            return $size * pow(1024, $key);
        else return "invalid type";
    }

    /**
     * @param $folder
     * @return int
     */
    public function folder_size ($folder): int {
        $size  = 0;
        $files = glob($this->slash_folder($folder . '/*'));
        foreach ($files as $path) {
            is_file($path) && $size += filesize($path);
            is_dir($path) && $size  += $this->folder_size($path);
        }
        return $size;
    }

    /**
     * @param $file
     * @return array
     */
    public function file_size ($file): array {
        return $this->convert_size(file_exists($file) ? filesize($file) : 0);
    }

    /**
     * @param $file
     * @return array
     */
    public function info_file ($file): array {
        $data  = pathinfo($file) + [
            "size" => $this->file_size($file)
        ];
        return [
            "name" => $data["filename"],
            "link" => URL_WEBSITE . str_replace(["\\\\", "\\", "//", "\/"], "/", str_replace(["../", "..\\"], "", $file)),
            "size" => $data["size"],
            "type" => $data["extension"]
        ];
    }

    /**
     * @param string|null $type
     * @return mixed
     */
    public function file_type_icon (?string $type = null) {
        $types = $this->object_to_array($this->do_work("ufo_file_types"));
        if (!$type)
            return $types;
        else if ($this->isset_key($types, $type))
            return $types[$type];
        return false;
    }

    /**
     * @param string $source
     * @param string $destination
     * @return bool
     */
    public function file_copy (string $source, string $destination): bool {
        $source = $this->slash_folder($source);
        $destination = $this->slash_folder($destination);

        if (is_dir($source)) {
            if (!is_dir($destination))
                @mkdir($destination);

            $dir = dir($source);

            while (FALSE !== ($entry = $dir->read())) {
                if ($entry == '.' || $entry == '..')
                    continue;

                $Entry = $this->slash_folder($source . '/' . $entry);

                if (is_dir($Entry)) {
                    $this->file_copy($Entry, $this->slash_folder(
                        $destination . '/' . $entry
                    ));
                    continue;
                }

                copy($Entry, $this->slash_folder($destination . '/' . $entry));
            }

            $dir->close();

            return true;
        }

        return copy($source, $destination);
    }

    /**
     * @param string $filename
     * @param mixed $content
     * @return void
     */
    public function make_file (string $filename, $content) {
        global $_;

        if (is_array($content) || is_object($content))
            $content = json_encode($content, JSON_UNESCAPED_UNICODE);

        if ($this->has_char($filename, '$root'))
            $filename = BASE_PATH . $filename;
        else if ($this->isset_key($_, "this_plugin"))
            $filename = $_["this_plugin"]["path"] . $filename;

        file_put_contents($this->slash_folder($filename), $content);
    }

    /**
     * @param string $zipFile
     * @param string|null $extractTo
     * @return bool
     */
    public function unzip (string $zipFile, string $extractTo = null): bool {
        if (!extension_loaded("zip")) return false;

        $zipFile = $this->slash_folder($zipFile);

        if (!file_exists($zipFile))
            return false;

        $path = pathinfo(realpath($zipFile), PATHINFO_DIRNAME);
        $zip  = new ZipArchive;
        $open = $zip->open($zipFile);

        if ($open) {
            $zip->extractTo((empty($extractTo) ? $path : $extractTo));
            $zip->close();
            return true;
        }

        return false;
    }

    /**
     * @param string $string
     * @return array|string|string[]
     */
    public function sanitize_file_name (string $string) {
        $special_chars = ["?", "[", "]", "/", "\\", "=", "<", ">", ":", ";", ",", "'", "\"", "&", "$", "#", "*", "(", ")", "|", "~", "`", "!", "{", "}", "%", "+"];
        return str_replace($special_chars, "", $string);
    }

    /**
     * @param string $string
     * @return array|string|string[]
     */
    public function sanitize_link (string $string = "") {
        return str_replace(["..", "../", "..\\", "./"], "", str_replace(["/", "////", "\\", "\\\\", "\/", "/\\"], "/", $string));
    }

    /**
     * @return array
     */
    public function get_lower_type_file (): array {
        $new_list = [];

        if (isset($this->get_package()["types"])) {
            $types = $this->get_package()["types"];

            foreach ($types as $value)
                foreach ($value as $k => $v)
                    $new_list[$k] = $v["icon"];
        }
        return $new_list;
    }

    /**
     * @param $category
     * @param $type
     * @return bool
     */
    public function available_type ($category, $type): bool {
        $category = is_array($category) ? $category : explode(",", $category);
        $new_list = [];
        $result   = false;

        foreach ($category as $item)
            $new_list += $this->get_package()["types"][$item] ?? [];

        foreach ($new_list as $k => $v) {
            if ($type == $k) {
                $result = true;
                break;
            }
        }

        return $result;
    }

    /**
     * @param $link
     * @param array $options
     * @param mixed $data
     * @param array $header
     * @return array
     */
    public function curl ($link, array $options = [], $data = [], array $header = []): array {
        $curl = curl_init($link);

        /**
         * Config
         */
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
        curl_setopt_array($curl, $options);

        /**
         * Result
         */
        $response = curl_exec($curl);

        /**
         * Status
         */
        $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        /**
         * Get Error
         */
        $error  = curl_error($curl);

        /**
         * Close Curl
         */
        curl_close($curl);

        return [
            "status"   => $status,
            "error"    => $error,
            "response" => $response
        ];
    }

    /**
     * @param $to
     * @param $subject
     * @param $content
     * @param array $array
     * @return bool|string
     * @throws Exception
     */
    public function send_mail ($to, $subject, $content, array $array = []) {
        global $db;

        $array += unserialize($db->meta("smtp")) + unserialize($db->meta("mail"));

        return $this->do_work("ufo_send_email", $array + [
            "to"      => $to,
            "subject" => $subject,
            "content" => $content
        ]);
    }

    /**
     * @param $filename
     * @param array $search
     * @param array $replace
     * @return string
     * @throws Exception
     */
    public function mail_template ($filename, array $search = [], array $replace = []): string {
        global $admin_folder;

        $filename = $this->slash_folder(
            $admin_folder . "content/cache/emails/$filename.html"
        );
        $template = "";

        if (file_exists($filename))
            $template = file_get_contents($filename);

        $search  = array_merge([
            "web_title", "web_logo", "web_link"
        ], $search);
        $replace = array_merge([
            WEB_TITLE, WEB_ICON, URL_WEBSITE
        ], $replace);

        $this->prefix_suffix_array($search, "%", "%");

        return str_replace($search, $replace, $template);
    }

    /**
     * @return float|int
     */
    public function get_upload_max_size () {
        return (int) str_replace(["b","k","m","g","t","p","e","z","y"], "", strtolower(ini_get("upload_max_filesize")));
    }

    /**
     * @param $title
     * @param $msg
     * @param string $status
     * @return void
     */
    public function add_log ($title, $msg, string $status = "normal") {
        global $admin_folder;

        $available_status = ["normal" => 0, "warning" => 0, "danger" => 0];

        if ($this->isset_key($available_status, $status)) {
            $template = [
                "title"    => $title,
                "message"  => $msg,
                "status"   => $status,
                "dateTime" => $this->dateTime()
            ];
            $file = $this->slash_folder($admin_folder . "content/cache/admin/logs.json");
            $data = json_decode(file_get_contents($file), true);
            $has  = false;

            foreach ($data as $item) {
                if ($item["title"] == $title) $has = true;
            }

            if (!$has) {
                $data[] = $template;
                file_put_contents($file, json_encode($data, JSON_UNESCAPED_UNICODE));
            }
        }
    }

    /**
     * @param $string
     * @param $decode
     * @return bool
     */
    public function is_bas64 ($string, &$decode = null): bool {
        if (!is_string($string) || !preg_match(
            "/^[a-zA-Z0-9\/\r\n+]*={0,2}$/", $string
        )) return false;

        $decoded = base64_decode($string, true);
        if (false === $decoded)
            return false;

        if (base64_encode($decoded) != $string)
            return false;

        return $decode ? $decode = $decoded : true;
    }

    /**
     * @param $target
     * @param &$variable
     * @return bool|array
     */
    public function is_array ($target, &$variable = null) {
        try {
            if (is_string($target)) {
                $decode = json_decode($target, true);
                return $variable === null ? is_array($decode) : $variable = $decode;
            }
        } catch (Exception $e) {}
        return false;
    }

    /**
     * @param $target
     * @param &$variable
     * @return bool|array
     */
    public function is_json ($target, &$variable = null) {
        return $this->is_array($target, $variable);
    }

    /**
     * @param string $string
     * @return string
     */
    public function sanitize_xss (string $string): string {
        $string = str_replace(["&amp;","&lt;","&gt;"], ["&amp;amp;","&amp;lt;","&amp;gt;"], $string);
        $string = preg_replace('/(&#*\w+)[\x00-\x20]+;/u', "$1;", $string);
        $string = preg_replace('/(&#x*[0-9A-F]+);*/iu', "$1;", $string);
        $string = html_entity_decode($string, ENT_COMPAT, "UTF-8");

        $string = preg_replace('#(<[^>]+?[\x00-\x20"\'])(?:on|xmlns)[^>]*+>#iu', '$1>', $string);

        $string = preg_replace('#([a-z]*)[\x00-\x20]*=[\x00-\x20]*([`\'"]*)[\x00-\x20]*j[\x00-\x20]*a[\x00-\x20]*v[\x00-\x20]*a[\x00-\x20]*s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:#iu', '$1=$2nojavascript...', $string);
        $string = preg_replace('#([a-z]*)[\x00-\x20]*=([\'"]*)[\x00-\x20]*v[\x00-\x20]*b[\x00-\x20]*s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:#iu', '$1=$2novbscript...', $string);
        $string = preg_replace('#([a-z]*)[\x00-\x20]*=([\'"]*)[\x00-\x20]*-moz-binding[\x00-\x20]*:#u', '$1=$2nomozbinding...', $string);

        $string = preg_replace('#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?expression[\x00-\x20]*\([^>]*+>#i', '$1>', $string);
        $string = preg_replace('#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?behaviour[\x00-\x20]*\([^>]*+>#i', '$1>', $string);
        $string = preg_replace('#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:*[^>]*+>#iu', '$1>', $string);

        $string = preg_replace('#</*\w+:\w[^>]*+>#i', '', $string);

        do {
            $old_data = $string;
            $string = preg_replace('#</*(?:applet|b(?:ase|gsound|link)|embed|frame(?:set)?|i(?:frame|layer)|l(?:ayer|ink)|meta|object|s(?:cript|tyle)|title|xml)[^>]*+>#i', '', $string);
        } while ($old_data !== $string);

        return $string;
    }

    /**
     * @return mixed
     */
    public function viewer_ip () {
        if (!empty($_SERVER["HTTP_CLIENT_IP"]))
            return $_SERVER["HTTP_CLIENT_IP"];
        elseif (!empty($_SERVER["HTTP_X_FORWARDED_FOR"]))
            return $_SERVER["HTTP_X_FORWARDED_FOR"];
        return $_SERVER["REMOTE_ADDR"];
    }

    /**
     * @param $prop
     * @param $replace
     * @return false|int
     */
    public function rewrite_htaccess ($prop, $replace) {
        $htaccess = (defined("ADMIN") ? $this->back_folder() : "") . ".htaccess";
        if (is_file($htaccess)) {
            $rows = file_get_contents($htaccess); preg_match('/'.$prop.'/i', $rows, $output);
            if (!empty($output)) {
                return file_put_contents($htaccess,  preg_replace('/'.$prop.'/i', $replace, $rows));
            } else {
                return file_put_contents($htaccess, $rows . "\n" . $replace);
            }
        }
        return false;
    }

    /**
     * @param array $array
     * @return bool
     */
    public function add_admin_widget (array $array): bool {
        $this->FLOAT["ufo_admin_widgets"] = $this->FLOAT["ufo_admin_widgets"] ?? [];

        if (!$this->has_in_array(["column", "script", "title"], $array))
            return false;

        if (isset($array["include"]) || isset($array["html"])) {
            if ($array["column"] == 1 || $array["column"] == 2) {
                $this->FLOAT["ufo_admin_widgets"]["0x" . rand()] = [
                    "title"  => $array["title"],
                    "column" => $array["column"],
                    isset($array["include"]) ? "include" : "html" => $array["html"] ?? $this->slash_folder($array["include"]),
                    "script" => empty($array["script"]) ? rand(0, 9999) : $array["script"]
                ];
                return true;
            }
        }

        return false;
    }

    /**
     * @return array|array[]
     */
    public function get_admin_widgets (): array {
        $columns = ["column" => [], "column2" => []];
        if (isset($this->FLOAT["ufo_admin_widgets"]) && $this->is_admin()) {

            foreach ($this->FLOAT["ufo_admin_widgets"] as $widget) {
                if ($widget["column"] == 1)
                    $columns["column"][] = $widget;
            }

            foreach ($this->FLOAT["ufo_admin_widgets"] as $widget) {
                if ($widget["column"] == 2)
                    $columns["column2"][] = $widget;
            }

        }
        return $columns;
    }

    /**
     * @param string $k
     * @param $v
     * @return void
     */
    public function set_session (string $k, $v) {
        if (!session_id()) session_start();
        $_SESSION[$k] = $v;
    }

    /**
     * @param string $session
     * @return void
     */
    public function unset_session (string $session) {
        if (!session_id()) session_start();
        if ($this->isset_key($_SESSION, $session))
            unset($_SESSION[$session]);
    }

    /**
     * @param string $cookie
     * @return bool
     */
    public function unset_cookie (string $cookie): bool {
        if (isset($_COOKIE[$cookie])) {
            unset($_COOKIE[$cookie]);
            return setcookie($cookie, "", 0, "/");
        }
        return true;
    }

    /**
     * @param array $array
     * @return bool
     * @throws Exception
     */
    public function add_task (array $array): bool {
        /**
         * Add tasks list
         */
        if (!$this->isset_key($this->FLOAT, "ufo_tasks")) {
            $this->FLOAT["ufo_tasks"] = [];
        }

        /**
         * Check parameters
         */
        if ($this->isset_key($array, "name") && $this->isset_key($array, "fn")) {
            if ($this->is_function($array["fn"])) {
                $this->FLOAT["ufo_tasks"][$array["name"]] = $array["fn"];
            }
        }

        return (new UFO_Task())->add($array);
    }

    /**
     * @param string $name
     * @return bool
     */
    public function remove_task (string $name): bool {
        return (new UFO_Task())->remove($name);
    }

    /**
     * @param string $name
     * @return false|mixed
     */
    public function status_task (string $name) {
        return (new UFO_Task())->status($name);
    }

    /**
     * @param string $name
     * @return false|mixed
     */
    public function get_task (string $name) {
        return (new UFO_Task())->get($name);
    }

    /**
     * @return array
     */
    public function tasks (): array {
        return $this->FLOAT["ufo_tasks"] ?? [];
    }

    /**
     * @param mixed $one
     * @param mixed $two
     * @return bool
     */
    public function equal ($one, $two): bool {
        return $one == $two;
    }

    /**
     * Increase
     *
     * @param $num1
     * @param $num2
     * @return int
     */
    public function inc ($num1, $num2): int {
        return $num1 + $num2;
    }

    /**
     * Decrease
     *
     * @param $num1
     * @param $num2
     * @return int
     */
    public function dec ($num1, $num2): int {
        return $num1 - $num2;
    }

    /**
     * @param array $keys
     * @param string $str
     * @return false|mixed
     */
    public function match_keys_str (array $keys, string $str) {
        $matched = false; foreach ($keys as $key) {
            if ($key == $str) $matched = $key;
        } return $matched;
    }

    /**
     * @param string $name
     * @param array $data
     * @return UFO_Options
     */
    public function add_center (string $name, array $data): UFO_Options {
        $this->FLOAT["centers"] = $this->FLOAT["centers"] ?? [];
        $this->FLOAT["centers"][$name] = $this->FLOAT["centers"][$name] ?? [];
        $this->FLOAT["centers"][$name][] = $data;
        return $this;
    }

    /**
     * @param string $name
     * @return mixed|null
     */
    public function get_center (string $name) {
        return $this->FLOAT["centers"][$name] ?? null;
    }

    /**
     * @param string $html
     * @return string
     */
    public function minify_html (string $html): string {
        return preg_replace([
            '/(\n|^)(\x20+|\t)/', '/(\n|^)\/\/(.*?)(\n|$)/', '/\n/', '/\<\!--.*?-->/', '/(\x20+|\t)/', '/\>\s+\</', '/(\"|\')\s+\>/', '/=\s+(\"|\')/'
        ], [
            "\n", "\n", " ", "", " ", "><", "$1>", "=$1"
        ], $html);
    }

    /**
     * @param array|string $name
     * @param $fn
     * @return void
     */
    public function exert ($name, $fn) {
        if (!isset($this->FLOAT["exerts"]))
            $this->FLOAT["exerts"] = [];

        if (is_array($name)) {
            foreach ($name as $item)
                $this->exert($item, $fn);
            return;
        }

        if (!isset($this->FLOAT["exerts"][$name]))
            $this->FLOAT["exerts"][$name] = [];

        $this->FLOAT["exerts"][$name][] = $fn;
    }

    /**
     * @param string|array $name
     * @param $args
     * @return array
     */
    public function fire ($name, ...$args): array {
        $name   = is_array($name) ? $name : [$name];
        $result = [];

        foreach ($name as $nexert)
            foreach ($this->FLOAT["exerts"][$nexert] ?? [] as $exert) {
                $args[]   = $nexert;
                $result[] = $this->call($exert, ...$args);
            }

        return $result;
    }

    /**
     * @param string $name
     * @param string|int $size (width)
     * @param array|int $attrs (height)
     * @param string|null $unit
     * @return false|string
     */
    public function thumbnail (string $name, $size, $attrs = [], ?string $unit = "px") {
        if (!$this->isset_key($this->FLOAT, "thumbnails"))
            $this->FLOAT["thumbnails"] = [];

        /**
         * Add a thumbnail to the list of thumbnails
         */
        if ((is_numeric($size) && $size != -1) || is_string($size)) {
            $this->FLOAT["thumbnails"][$name] = [
                "width"  => $size,
                "height" => empty($attrs) ? "auto" : $attrs,
                "unit"   => $unit
            ];
            return true;
        }

        if (!$this->isset_key($this->FLOAT["thumbnails"], $name))
            return false;

        /**
         * Render thumbnail
         */
        $config = $this->FLOAT["thumbnails"][$name];
        $sizes  = $this->get_package()["thumbnail_sizes"];

        if (is_string($config["width"])) {
            if ($this->isset_key($sizes, $config["width"])) {
                $config = array_merge($config, $sizes[$config["width"]]);
            } else {
                if ($config["unit"] != null) {
                    throw new \RuntimeException(str_replace(
                        "%list",
                        implode(", ", array_keys($sizes)),
                        $this->lng("The desired image size is not correct. You can see the correct sizes in this list (%list)")
                    ));
                }
            }
        }

        $config["width"]  .= $config["unit"];
        $config["height"] .= $config["height"] == "auto" ? "" : $config["unit"];

        return $this->tag("img", null, array_merge($config, (array) $attrs, [
            "style" => "width: " . $config["width"] . ";height: " . $config["height"] . ";"
        ]));
    }

    /**
     * @return void
     * @throws Exception
     */
    public function document () {
        $this->load_layout("document");
    }

    /**
     * @return void
     * @throws Exception
     */
    public function endDoc () {
        $this->load_layout("endDoc");
    }

    /**
     * @return void
     * @throws Exception
     */
    public function header () {
        $this->load_layout("document");
        $this->from_theme("header");
    }

    /**
     * @return void
     * @throws Exception
     */
    public function footer () {
        $this->from_theme("footer");
        $this->load_layout("endDoc");
    }

}