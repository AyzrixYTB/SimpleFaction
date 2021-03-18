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

use Ayzrix\SimpleFaction\API\FactionsAPI;
use Ayzrix\SimpleFaction\Commands\Faction;
use Ayzrix\SimpleFaction\Events\Listener\BlockListener;
use Ayzrix\SimpleFaction\Events\Listener\EntityListener;
use Ayzrix\SimpleFaction\Events\Listener\PlayerListener;
use Ayzrix\SimpleFaction\Tasks\Async\LoadItTask;
use Ayzrix\SimpleFaction\Tasks\Async\SaveItTask;
use Ayzrix\SimpleFaction\Tasks\MapTask;
use Ayzrix\SimpleFaction\Tasks\BorderTask;
use Ayzrix\SimpleFaction\Utils\Utils;
use onebone\economyapi\EconomyAPI;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;

class Main extends PluginBase {

    /** @var Main */
    private static $instance;

    /** @var EconomyAPI $economyAPI */
    private static $economyAPI;

    public function onLoad() {
        self::$instance = $this;
        $this->saveDefaultConfig();
        $this->saveResource("lang.yml");

        if ((strtolower(Utils::getIntoConfig("PROVIDER")) === "mysql") and (Utils::getIntoConfig("mysql_address") === "SERVER ADDRESS" or Utils::getIntoConfig("mysql_user") === "USER" or Utils::getIntoConfig("mysql_password") === "YOUR PASSWORD" or Utils::getIntoConfig("mysql_db") === "YOUR DB")) {
            $this->getLogger()->error("Error, please setup a valid mysql server");
            $this->getServer()->getPluginManager()->disablePlugin($this);
            return;
        }

        $this->initDatabase();
    }

    public function onEnable() {
        @mkdir($this->getDataFolder() . "Languages/");
        foreach ((new Config($this->getDataFolder() . "lang.yml", Config::YAML))->get("languages") as $prefix => $file) {
            $this->saveResource("Languages/{$file}.yml");
        }
        self::$economyAPI = EconomyAPI::getInstance();
        $this->getServer()->getCommandMap()->register("simplefaction", new Faction($this));
        $this->getServer()->getPluginManager()->registerEvents(new PlayerListener(), $this);
        $this->getServer()->getPluginManager()->registerEvents(new BlockListener(), $this);
        $this->getServer()->getPluginManager()->registerEvents(new EntityListener(), $this);
        $this->getScheduler()->scheduleRepeatingTask(new MapTask(), 20*3);
        $this->getScheduler()->scheduleRepeatingTask(new BorderTask(), 15);
    }

    public function onDisable() {
        $this->getServer()->getAsyncPool()->submitTask(new SaveItTask(serialize(FactionsAPI::$faction), serialize(FactionsAPI::$player), serialize(FactionsAPI::$home), serialize(FactionsAPI::$lang), serialize(FactionsAPI::$claim)));
    }

    private function initDatabase() {
        Utils::query("CREATE TABLE IF NOT EXISTS faction (faction VARCHAR(255) PRIMARY KEY, players TEXT, power int, money int, allies TEXT, claims TEXT);");
        Utils::query("CREATE TABLE IF NOT EXISTS player (player VARCHAR(255) PRIMARY KEY, faction VARCHAR(255), role VARCHAR(255));");
        Utils::query("CREATE TABLE IF NOT EXISTS home (faction VARCHAR(255) PRIMARY KEY, x int, y int, z int, world VARCHAR(255));");
        Utils::query("CREATE TABLE IF NOT EXISTS lang (player VARCHAR(255) PRIMARY KEY, lang VARCHAR(255));");
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