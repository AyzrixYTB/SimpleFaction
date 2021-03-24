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

namespace Ayzrix\SimpleFaction;

use Ayzrix\SimpleFaction\Commands\Faction;
use Ayzrix\SimpleFaction\Events\Listener\BlockListener;
use Ayzrix\SimpleFaction\Events\Listener\EntityListener;
use Ayzrix\SimpleFaction\Events\Listener\PlayerListener;
use Ayzrix\SimpleFaction\Events\PlayerMove;
use Ayzrix\SimpleFaction\Tasks\Async\LoadItTask;
use Ayzrix\SimpleFaction\Tasks\MapTask;
use Ayzrix\SimpleFaction\Tasks\BorderTask;
use Ayzrix\SimpleFaction\Utils\Utils;
use onebone\economyapi\EconomyAPI;
use pocketmine\plugin\PluginBase;

class Main extends PluginBase {

    /** @var Main */
    private static $instance;

    /** @var EconomyAPI $economyAPI */
    private static $economyAPI;

    public function onEnable() {
        self::$instance = $this;
        $this->saveDefaultConfig();
        $this->saveResource("lang.yml");
        $this->initDatabase();

        @mkdir($this->getDataFolder() . "Languages/");
        foreach (Utils::getIntoLang("languages") as $prefix => $file) {
            $this->saveResource("Languages/{$file}.yml");
        }
        $this->getServer()->getCommandMap()->register("simplefaction", new Faction($this));
        $this->getServer()->getPluginManager()->registerEvents(new PlayerListener(), $this);
        $this->getServer()->getPluginManager()->registerEvents(new BlockListener(), $this);
        $this->getServer()->getPluginManager()->registerEvents(new EntityListener(), $this);
        $this->getScheduler()->scheduleRepeatingTask(new MapTask(), 20*3);
        $this->getScheduler()->scheduleRepeatingTask(new BorderTask(), 15);
        if (Utils::getIntoConfig("entering_leaving") === true) {
            $this->getServer()->getPluginManager()->registerEvents(new PlayerMove(), $this);
        }
        if (Utils::getIntoConfig("economy_system") === true) {
            $economy = $this->getServer()->getPluginManager()->getPlugin("EconomyAPI");
            if (is_null($economy)) {
                $this->getLogger()->notice("Please install a valid version of EconomyAPI");
                $this->getServer()->getPluginManager()->disablePlugin($this);
                return;
            }
            self::$economyAPI = EconomyAPI::getInstance();
        }
    }

    public function onDisable() {
        Utils::saveAll();
    }

    private function initDatabase() {
        if (Utils::getProvider() === "mysql") {
            $db = new \MySQLi(Utils::getIntoConfig("mysql_address"), Utils::getIntoConfig("mysql_user"), Utils::getIntoConfig("mysql_password"), Utils::getIntoConfig("mysql_db"));
        } else $db = new \SQLite3($this->getDataFolder() . "SimpleFaction.db");

        $db->query("CREATE TABLE IF NOT EXISTS faction (faction VARCHAR(255) PRIMARY KEY, players TEXT, power int, money int, allies TEXT, claims TEXT);");
        $db->query("CREATE TABLE IF NOT EXISTS player (player VARCHAR(255) PRIMARY KEY, faction VARCHAR(255), role VARCHAR(255));");
        $db->query("CREATE TABLE IF NOT EXISTS home (faction VARCHAR(255) PRIMARY KEY, x int, y int, z int, world VARCHAR(255));");
        $db->query("CREATE TABLE IF NOT EXISTS lang (player VARCHAR(255) PRIMARY KEY, lang VARCHAR(255));");
        $this->getServer()->getAsyncPool()->submitTask(new LoadItTask());
    }

    /**
     * @return Main
     */
    public static function getInstance(): Main {
        return self::$instance;
    }

    /**
     * @return EconomyAPI
     */
    public static function getEconomy(): EconomyAPI {
        return self::$economyAPI;
    }
}