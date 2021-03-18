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
     * @param string $text
     */
    public static function query(string $text): void {
        Main::getInstance()->getServer()->getAsyncPool()->submitTask(new QueryTask($text));
    }
}