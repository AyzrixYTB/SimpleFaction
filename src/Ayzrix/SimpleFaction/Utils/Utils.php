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

namespace Ayzrix\SimpleFaction\Utils;

use Ayzrix\SimpleFaction\API\FactionsAPI;
use Ayzrix\SimpleFaction\Main;
use Ayzrix\SimpleFaction\Tasks\Async\QueryTask;
use pocketmine\Player;
use pocketmine\Server;
use pocketmine\utils\Config;

class Utils {

    /** @var null|\MySQLi  */
    public static $db = null;

    private static $config = [];

    public static function getPrefix(): string {
        return self::getIntoConfig("PREFIX");
    }

    public static function getMessage(Player $player, string $text, array $args = array()): string {
        $lang = FactionsAPI::getLanguages($player);
        $file = self::getIntoLang("languages")[$lang];
        $config = new Config(Main::getInstance()->getDataFolder() . "Languages/{$file}.yml", Config::YAML);
        $message = $config->get($text);
        if (!empty($args)) {
            foreach ($args as $arg) {
                $message = preg_replace("/[%]/", $arg, $message, 1);
            }
        }
        return str_replace('{prefix}', self::getPrefix(), $message);
    }

    public static function getProvider(): string {
        return strtolower(self::getIntoConfig("PROVIDER"));
    }

    public static function loadConfig() {
        self::$config = Main::getInstance()->getConfig()->getAll();
    }

    public static function getIntoConfig(string $value) {
        return self::$config[$value];
    }

    public static function getIntoLang(string $value) {
        $config = new Config(Main::getInstance()->getDataFolder() . "lang.yml", Config::YAML);
        return $config->get($value);
    }

    public static function getZoneColor(Player $player, string $zone): string {
        if ($zone === "Wilderness") {
            return self::getIntoConfig("zones_colors")["Wilderness"];
        } else {
            if (FactionsAPI::isInFaction($player->getName())) {
                $faction = FactionsAPI::getFaction($player->getName());
                if ($faction === $zone) {
                    return self::getIntoConfig("zones_colors")["Own-Faction"];
                } else if (FactionsAPI::areAllies($faction, $zone)) {
                    return self::getIntoConfig("zones_colors")["Allies"];
                } else return self::getIntoConfig("zones_colors")["Enemies"];
            } else return self::getIntoConfig("zones_colors")["Enemies"];
        }
    }

    public static function getBorderColor(bool $claim, string $faction1 = "", string $faction2 = ""): array {
        if ($claim) {
            if ($faction1 === $faction2) {
                $color = self::getIntoConfig("border_colors")["Own-Faction"];
                return explode(", ", $color);
            } else if (FactionsAPI::areAllies($faction1, $faction2)) {
                $color = self::getIntoConfig("border_colors")["Allies"];
                return explode(", ", $color);
            } else {
                $color = self::getIntoConfig("border_colors")["Enemies"];
                return explode(", ", $color);
            }
        } else {
            $color = self::getIntoConfig("border_colors")["Wilderness"];
            return explode(", ", $color);
        }
    }

    public static function query(string $text) {
        Server::getInstance()->getAsyncPool()->submitTask(new QueryTask($text));
    }

    /**
     * @param string $text
     * @return string
     */
    public static function real_escape_string(string $text): string {
        switch (self::getProvider()) {
            case "mysql":
                return self::$db->real_escape_string($text);
            default:
                return \SQLite3::escapeString($text);
        }
    }
}