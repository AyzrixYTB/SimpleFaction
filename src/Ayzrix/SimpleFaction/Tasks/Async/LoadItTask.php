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

use Ayzrix\Auction\Utils\MySQL;
use Ayzrix\SimpleFaction\API\FactionsAPI;
use Ayzrix\SimpleFaction\Main;
use Ayzrix\SimpleFaction\Utils\Utils;
use pocketmine\scheduler\AsyncTask;
use pocketmine\Server;

class LoadItTask extends AsyncTask {

    private $provider;
    private $db;

    public function __construct() {
        $this->provider = Utils::getProvider();
        if ($this->provider === "mysql") {
            $this->db = array(Utils::getIntoConfig("mysql_address"), Utils::getIntoConfig("mysql_user"), Utils::getIntoConfig("mysql_password"), Utils::getIntoConfig("mysql_db"));
        } else $this->db = array(Main::getInstance()->getDataFolder() . "SimpleFaction.db");
    }

    public function onRun() {
        $provider = $this->provider;
        $results = [];

        switch ($provider) {
            case "mysql":
                $db = new \MySQLi($this->db[0], $this->db[1], $this->db[2], $this->db[3]);
                $data = $db->query("SELECT faction, players, power, money, allies, claims FROM faction");
                while ($resultArr = $data->fetch_Array(MYSQLI_ASSOC)) {
                    $results["faction"][$resultArr['faction']] = array(unserialize(base64_decode($resultArr['players'])), $resultArr['power'], $resultArr['money'], unserialize(base64_decode($resultArr['allies'])), unserialize(base64_decode($resultArr['claims'])));
                }

                $data = $db->query("SELECT player, faction, role FROM player");
                while ($resultArr = $data->fetch_Array(MYSQLI_ASSOC)) {
                    $results["player"][$resultArr['player']] = array($resultArr['faction'], $resultArr['role']);
                }

                $data = $db->query("SELECT faction, x, y, z, world FROM home");
                while ($resultArr = $data->fetch_Array(MYSQLI_ASSOC)) {
                    $results["home"][$resultArr['faction']] = array($resultArr['x'], $resultArr['y'], $resultArr['z'], $resultArr['world']);
                }

                $data = $db->query("SELECT player, lang FROM lang");
                while ($resultArr = $data->fetch_Array(MYSQLI_ASSOC)) {
                    $results["lang"][$resultArr['player']] = $resultArr['lang'];
                }
                break;
            default:
                $db = new \SQLite3($this->db[0]);
                $data = $db->query("SELECT faction, players, power, money, allies, claims FROM faction");
                while ($resultArr = $data->fetchArray(SQLITE3_ASSOC)) {
                    $results["faction"][$resultArr['faction']] = array(unserialize(base64_decode($resultArr['players'])), $resultArr['power'], $resultArr['money'], unserialize(base64_decode($resultArr['allies'])), unserialize(base64_decode($resultArr['claims'])));
                }

                $data = $db->query("SELECT player, faction, role FROM player");
                while ($resultArr = $data->fetchArray(SQLITE3_ASSOC)) {
                    $results["player"][$resultArr['player']] = array($resultArr['faction'], $resultArr['role']);
                }

                $data = $db->query("SELECT faction, x, y, z, world FROM home");
                while ($resultArr = $data->fetchArray(SQLITE3_ASSOC)) {
                    $results["home"][$resultArr['faction']] = array($resultArr['x'], $resultArr['y'], $resultArr['z'], $resultArr['world']);
                }

                $data = $db->query("SELECT player, lang FROM lang");
                while ($resultArr = $data->fetchArray(SQLITE3_ASSOC)) {
                    $results["lang"][$resultArr['player']] = $resultArr['lang'];
                }
                break;
        }
        $this->setResult($results);
    }

    /**
     * @param Server $server
     */
    public function onCompletion(Server $server) {
        $result = $this->getResult();

        if(!empty($result)) {
            if (isset($result["faction"])) {
                foreach ($result["faction"] as $key => $array) {
                    FactionsAPI::$faction[$key] = array("players" => array_unique($array[0]), "power" => $array[1], "money" => $array[2], "allies" => $array[3]);
                    FactionsAPI::$claim[$key] = $array[4];
                }
            }
            if (isset($result["player"])) {
                foreach ($result["player"] as $key => $array) {
                    FactionsAPI::$player[strtolower($key)] = array("faction" => $array[0], "role" => $array[1]);
                }
            }

            if (isset($result["home"])) {
                foreach ($result["home"] as $key => $array) {
                    FactionsAPI::$home[$key] = array($array[0], $array[1], $array[2], $array[3]);
                }
            }

            if (isset($result["lang"])) {
                foreach ($result["lang"] as $key => $lang) {
                    FactionsAPI::$lang[$key] = $lang;
                }
            }
        }
    }
}