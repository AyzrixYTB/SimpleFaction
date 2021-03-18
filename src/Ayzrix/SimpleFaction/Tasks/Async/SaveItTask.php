<?php

/***
 *       _____ _                 _      ______         _   _
 *      / ____(_)               | |    |  ____|       | | (_)
 *     | (___  _ _ __ ___  _ __ | | ___| |__ __ _  ___| |_ _  ___  _ __
 *      \___ \| | '_ ` _ \| '_ \| |/ _ \  __/ _` |/ __| __| |/ _ \| '_ \
 *      ____) | | | | | | | |_) | |  __/ | | (_| | (__| |_| | (_) | | | |
 *     |_____/|_|_| |_| |_| .__/|_|\___|_|  \__,_|\___|\__|_|\___/|_| |_|
 *                        | |
 *                        |_|
 */

namespace Ayzrix\SimpleFaction\Tasks\Async;

use Ayzrix\SimpleFaction\Main;
use Ayzrix\SimpleFaction\Utils\Utils;
use pocketmine\scheduler\AsyncTask;

class SaveItTask extends AsyncTask {

    private $provider;
    private $db;
    private $faction;
    private $player;
    private $home;
    private $lang;
    private $claim;

    public function __construct(string $faction, string $player, string $home, string $lang, string $claim) {
        $this->provider = Utils::getProvider();
        if ($this->provider === "mysql") {
            $this->db = array(Utils::getIntoConfig("mysql_address"), Utils::getIntoConfig("mysql_user"), Utils::getIntoConfig("mysql_password"), Utils::getIntoConfig("mysql_db"));
        } else $this->db = array(Main::getInstance()->getDataFolder() . "SimpleFaction.db");
        $this->faction = $faction;
        $this->player = $player;
        $this->home = $home;
        $this->lang = $lang;
        $this->claim = $claim;
    }

    public function onRun() {
        $provider = $this->provider;
        if ($provider === "mysql") {
            $db = new \MySQLi($this->db[0], $this->db[1], $this->db[2], $this->db[3]);
        } else $db = new \SQLite3($this->db[0]);
        $db->query("DELETE FROM faction");
        $db->query("DELETE FROM player");
        $db->query("DELETE FROM home");
        $db->query("DELETE FROM lang");

        $faction = unserialize($this->faction);
        $claim = unserialize($this->claim);
        $player = unserialize($this->player);
        $home = unserialize($this->home);
        $lang = unserialize($this->lang);

        foreach ($faction as $name => $values) {
            $faction = $name;
            $players = base64_encode(serialize($values["players"]));
            $power = $values["power"];
            $money = $values["money"];
            $allies = base64_encode(serialize($values["allies"]));
            $claims = base64_encode(serialize($claim[$faction]));
            $db->query("INSERT INTO faction (faction, players, power, money, allies, claims) VALUES ('$faction', '$players', '$power', '$money', '$allies', '$claims')");
        }

        foreach ($player as $name => $values) {
            $faction = $values["faction"];
            $role = $values["role"];
            $db->query("INSERT INTO player (player, faction, role) VALUES ('$name', '$faction', '$role');");
        }

        foreach ($home as $name => $values) {
            $db->query("INSERT INTO home (faction, x, y, z, world) VALUES ('$name', '$values[0]', '$values[1]', '$values[2]', '$values[3]');");
        }

        foreach ($lang as $name => $language) {
            $db->query("INSERT INTO lang (player, lang) VALUES ('$name', '$language');");
        }
    }
}