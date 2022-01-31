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
use Ayzrix\SimpleFaction\Entity\FloatingTextEntity;
use Ayzrix\SimpleFaction\Events\Listener\BlockListener;
use Ayzrix\SimpleFaction\Events\Listener\EntityListener;
use Ayzrix\SimpleFaction\Events\Listener\PlayerListener;
use Ayzrix\SimpleFaction\Events\PlayerMove;
use Ayzrix\SimpleFaction\Tasks\Async\LoadItTask;
use Ayzrix\SimpleFaction\Tasks\MapTask;
use Ayzrix\SimpleFaction\Tasks\BorderTask;
use Ayzrix\SimpleFaction\Utils\Utils;
use onebone\economyapi\EconomyAPI;
use pocketmine\entity\Entity;
use pocketmine\entity\EntityDataHelper;
use pocketmine\entity\EntityFactory;
use pocketmine\entity\Location;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\plugin\PluginBase;
use pocketmine\world\Position;
use pocketmine\world\World;

class Main extends PluginBase {

    private static Main $instance;

    private static EconomyAPI $economyAPI;

    public function onEnable(): void {
        self::$instance = $this;
        $this->saveDefaultConfig();
        $this->saveResource("lang.yml");
        Utils::loadConfig();
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

        if (Utils::getIntoConfig("floating_text") === true) {
            $this->initFloatingText();
        }
    }

    public function onDisable(): void {
        foreach ($this->getServer()->getWorldManager()->getWorlds() as $level) {
            foreach ($level->getEntities() as $entity) {
                if ($entity instanceof FloatingTextEntity) {
                    $entity->close();
                }
            }
        }
    }

    private function initDatabase(): void {
        if (Utils::getProvider() === "mysql") {
            $db = new \MySQLi(Utils::getIntoConfig("mysql_address"), Utils::getIntoConfig("mysql_user"), Utils::getIntoConfig("mysql_password"), Utils::getIntoConfig("mysql_db"));
            Utils::$db = $db;
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

    public function initFloatingText(): void {
        EntityFactory::getInstance()->register(FloatingTextEntity::class, function(World $world, CompoundTag $nbt) : FloatingTextEntity{
            return new FloatingTextEntity(EntityDataHelper::parseLocation($nbt, $world), $nbt);
        }, ['SimpleFaction_FloatingText', 'minecraft:simplefaction_floatingtext'],'minecraft:simplefaction_floatingtext',true);
        $coordinates = Utils::getIntoConfig("floating_text_coordinates");
        $coordinates = explode(":", $coordinates);
        $levelName = $coordinates[3];
        $level = $this->getServer()->getWorldManager()->getWorldByName($levelName);
        if ($level instanceof World) {
            $level->loadChunk((float)$coordinates[0] >> 4, (float)$coordinates[2] >> 4);
            $floatingtext = new FloatingTextEntity(new Location((float)$coordinates[0], (float)$coordinates[1], (float)$coordinates[2],0,0,$level));
            $floatingtext->spawnToAll();
        } else {
            $this->getLogger()->notice("Please provide a valid world for the floatingtext system");
        }
    }
}
