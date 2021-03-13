<?php

namespace Ayzrix\SimpleFaction\Utils;

use Ayzrix\SimpleFaction\Main;
use Ayzrix\SimpleFaction\Tasks\Async\QueryTask;

class MySQL {

    /**
     * @return \MySQLi
     */
    public static function getDatabase(): \MySQLi {
        return new \MySQLi(Utils::getIntoConfig("mysql_address"), Utils::getIntoConfig("mysql_user"), Utils::getIntoConfig("mysql_password"), Utils::getIntoConfig("mysql_db"));
    }

    /**
     * @return string
     */
    public static function init(): void {
        self::getDatabase()->query("CREATE TABLE IF NOT EXISTS faction (player VARCHAR(255) PRIMARY KEY, faction VARCHAR(255), role VARCHAR(255));");
        self::getDatabase()->query("CREATE TABLE IF NOT EXISTS power (faction VARCHAR(255) PRIMARY KEY, power int);");
    }

    /**
     * @param string $text
     */
    public static function query(string $text): void {
        Main::getInstance()->getServer()->getAsyncPool()->submitTask(new QueryTask($text));
    }
}