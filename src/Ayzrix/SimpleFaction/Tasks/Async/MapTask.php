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

namespace Ayzrix\SimpleFaction\Tasks\Async;

use Ayzrix\SimpleFaction\API\FactionsAPI;
use pocketmine\Player;
use pocketmine\scheduler\Task;
use pocketmine\Server;
use pocketmine\utils\TextFormat;

class MapTask extends Task {

    /**
     * @param int $currentTick
     * @return bool
     */
    public function onRun(int $currentTick): bool {
        if (empty(FactionsAPI::$map)) return false;
        foreach (FactionsAPI::$map as $name => $bool) {
            $player = Server::getInstance()->getPlayer($name);
            if ($player instanceof Player) {
                $player->sendMessage(implode(TextFormat::EOL, FactionsAPI::getMap($player)));
            } else unset(FactionsAPI::$map[$name]);
        }
        return true;
    }
}