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

class QueryTask extends AsyncTask {

    private $provider;
    private $db;
    private $text;

    public function __construct(string $text) {
        $this->provider = Utils::getProvider();
        $this->text = $text;
        if ($this->provider === "mysql") {
            $this->db = array(Utils::getIntoConfig("mysql_address"), Utils::getIntoConfig("mysql_user"), Utils::getIntoConfig("mysql_password"), Utils::getIntoConfig("mysql_db"));
        } else $this->db = array(Main::getInstance()->getDataFolder() . "SimpleFaction.db");
    }

    public function onRun() {
        $provider = $this->provider;

        switch ($provider) {
            case "mysql":
                $db = new \MySQLi($this->db[0], $this->db[1], $this->db[2], $this->db[3]);
                $db->query($this->text);
                break;
            default:
                $db = new \SQLite3($this->db[0]);
                $db->query($this->text);
                break;
        }
    }
}