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

    /**
     * @return string
     */
    public static function getPrefix(): string {
        return self::getIntoConfig("PREFIX");
    }

    /**
     * @param string $text
     * @param array $args
     * @return string
     */
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
        $message = str_replace('{prefix}', self::getPrefix(), $message);
        return $message;
    }

    /**
     * @return string
     */
    public static function getProvider(): string {
        return strtolower(self::getIntoConfig("PROVIDER"));
    }

    /**
     * @param string $value
     * @return bool|string|int|array
     */
    public static function getIntoConfig(string $value) {
        $config = Main::getInstance()->getConfig();
        return $config->get($value);
    }

    /**
     * @param string $value
     * @return string|array
     */
    public static function getIntoLang(string $value) {
        $config = new Config(Main::getInstance()->getDataFolder() . "lang.yml", Config::YAML);
        return $config->get($value);
    }

    /**
     * @param Player $player
     * @param string $zone
     * @return string
     */
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
                } else {
                    return self::getIntoConfig("zones_colors")["Enemies"];
                }
            } else {
                return self::getIntoConfig("zones_colors")["Enemies"];
            }
        }
    }

    /**
     * @param bool $claim
     * @param string $faction1
     * @param string $faction2
     * @return array
     */
    public static function getBorderColor(bool $claim, string $faction1 = "", string $faction2 = ""): array {
        if ($claim) {
            if ($faction1 === $faction2) {
                $color = self::getIntoConfig("border_colors")["Own-Faction"];
                $color = explode(", ", $color);
                return $color;
            } else if (FactionsAPI::areAllies($faction1, $faction2)) {
                $color = self::getIntoConfig("border_colors")["Allies"];
                $color = explode(", ", $color);
                return $color;
            } else {
                $color = self::getIntoConfig("border_colors")["Enemies"];
                $color = explode(", ", $color);
                return $color;
            }
        } else {
            $color = self::getIntoConfig("border_colors")["Wilderness"];
            $color = explode(", ", $color);
            return $color;
        }
    }

    /**
     * @param string $text
     */
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