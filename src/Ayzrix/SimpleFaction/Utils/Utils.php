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

use Ayzrix\SimpleFaction\Main;
use pocketmine\Player;
use pocketmine\utils\Config;

class Utils {

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
        $lang = self::getLanguages($player);
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
     * @return int
     */
    public static function getAssoc(): int {
        if (self::getProvider() === "mysql") {
            return MYSQLI_ASSOC;
        } else return SQLITE3_ASSOC;
    }

    /**
     * @return string
     */
    public static function getProvider(): string {
        return strtolower(self::getIntoConfig("PROVIDER"));
    }

    /**
     * @param Player $player
     * @return bool
     */
    public static function hasLanguages(Player $player): bool {
        $name = $player->getName();
        $result = Provider::getDatabase()->query("SELECT lang FROM lang WHERE player='$name';");
        if (Utils::getProvider() === "mysql") return $result->num_rows > 0 ? true : false;
        $return = $result->fetchArray(Utils::getAssoc());
        return empty($return) === false;
    }

    /**
     * @param Player $player
     * @param string $lang
     */
    public static function setLanguages(Player $player, string $lang): void {
        $name = $player->getName();
        Provider::query("INSERT INTO lang (player, lang) VALUES ('$name', '$lang')");
    }

    /**
     * @param Player $player
     * @return string
     */
    public static function getLanguages(Player $player): string {
        $name = $player->getName();
        $faction = Provider::getDatabase()->query("SELECT lang FROM lang WHERE player='$name';");
        if (Utils::getProvider() === "mysql") {
            $array = $faction->fetch_Array(Utils::getAssoc());
        } else {
            $array = $faction->fetchArray(Utils::getAssoc());
        }
        return $array["lang"]?? self::getIntoLang("default-language");
    }

    /**
     * @param Player $player
     * @param string $lang
     */
    public static function changeLanguages(Player $player, string $lang): void {
        $name = $player->getName();
        Provider::query("UPDATE lang SET lang = '$lang' WHERE player='$name'");
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
}