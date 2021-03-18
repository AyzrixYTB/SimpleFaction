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
use pocketmine\level\particle\RedstoneParticle;
use pocketmine\math\Vector3;
use pocketmine\Player;
use pocketmine\scheduler\Task;
use pocketmine\Server;

class BorderTask extends Task {

    /**
     * @param int $currentTick
     * @return bool
     */
    public function onRun(int $currentTick): bool {
        if (empty(FactionsAPI::$border)) return false;
        foreach (FactionsAPI::$border as $name => $bool) {
            $player = Server::getInstance()->getPlayer($name);
            if ($player instanceof Player) {
                $chunk = $player->getLevel()->getChunkAtPosition($player);
                $minX = (int)$chunk->getX() * 16;
                $maxX = $minX + 16;
                $minZ = (int)$chunk->getZ() * 16;
                $maxZ = $minZ + 16;
                for ($x = $minX; $x <= $maxX; $x += 0.5) {
                    for ($z = $minZ; $z <= $maxZ; $z += 0.5) {
                        if ($x === $minX or $x === $maxX or $z === $minZ or $z === $maxZ) {
                            $player->getLevel()->addParticle(new RedstoneParticle(new Vector3($x, $player->getY() + 0.8, $z)), [$player]);
                        }
                    }
                }
            } else unset(FactionsAPI::$border[$name]);
        }
        return true;
    }
}