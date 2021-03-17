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
use Ayzrix\SimpleFaction\Utils\Provider;
use Ayzrix\SimpleFaction\Utils\Utils;
use onebone\economyapi\EconomyAPI;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;

class Main extends PluginBase {

    /** @var Main */
    private static $instance;

    /** @var $economyAPI */
    private static $economyAPI;

    public function onEnable(): bool {
        self::$instance = $this;
        $this->saveDefaultConfig();
        $this->saveResource("lang.yml");
        @mkdir($this->getDataFolder() . "Languages/");
        foreach ((new Config($this->getDataFolder() . "lang.yml", Config::YAML))->get("languages") as $prefix => $file) {
            $this->saveResource("Languages/{$file}.yml");
        }

        if ((strtolower(Utils::getIntoConfig("PROVIDER")) === "mysql") and (Utils::getIntoConfig("mysql_address") === "SERVER ADDRESS" or Utils::getIntoConfig("mysql_user") === "USER" or Utils::getIntoConfig("mysql_password") === "YOUR PASSWORD" or Utils::getIntoConfig("mysql_db") === "YOUR DB")) {
            $this->getLogger()->error("Error, please setup a valid mysql server");
            $this->getServer()->disablePlugins();
            return false;
        }

        self::$economyAPI = EconomyAPI::getInstance();
        $this->getServer()->getCommandMap()->register("simplefaction", new Faction($this));
        $this->getServer()->getPluginManager()->registerEvents(new PlayerListener(), $this);
        $this->getServer()->getPluginManager()->registerEvents(new BlockListener(), $this);
        $this->getServer()->getPluginManager()->registerEvents(new EntityListener(), $this);
        Provider::init();
        return true;
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