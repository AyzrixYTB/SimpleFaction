<?php

namespace Ayzrix\SimpleFaction;

use Ayzrix\SimpleFaction\Commands\Faction;
use Ayzrix\SimpleFaction\Events\Listener\PlayerListener;
use Ayzrix\SimpleFaction\Utils\MySQL;
use pocketmine\plugin\PluginBase;

class Main extends PluginBase {

    /** @var Main */
    private static $instance;

    public function onEnable() {
        self::$instance = $this;
        $this->saveDefaultConfig();
        $this->getServer()->getCommandMap()->register("simplefaction", new Faction($this));
        $this->getServer()->getPluginManager()->registerEvents(new PlayerListener(), $this);
        MySQL::init();
    }

    /**
     * @return Main
     */
    public static function getInstance(): Main {
        return self::$instance;
    }
}