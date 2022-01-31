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

namespace Ayzrix\SimpleFaction\Tasks;

use Ayzrix\SimpleFaction\API\FactionsAPI;
use Ayzrix\SimpleFaction\Utils\Utils;
use pocketmine\color\Color;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\scheduler\Task;
use pocketmine\Server;
use pocketmine\world\format\Chunk;
use pocketmine\world\particle\DustParticle;

class BorderTask extends Task {

    public function onRun(): void {
        if (empty(FactionsAPI::$border)) return;
        foreach (FactionsAPI::$border as $name => $bool) {
            $player = Server::getInstance()->getPlayerExact($name);
            if ($player instanceof Player) {
                $level = $player->getWorld();
                $pos = $player->getPosition()->asVector3();
                $chunkX = $pos->getFloorX() >> Chunk::COORD_BIT_SIZE;
                $chunkZ = $pos->getFloorZ() >> Chunk::COORD_BIT_SIZE;
                $minX = (float)$chunkX * 16;
                $maxX = (float)$minX + 16;
                $minZ = (float)$chunkZ * 16;
                $maxZ = (float)$minZ + 16;

                for ($x = $minX; $x <= $maxX; $x += 0.5) {
                    for ($z = $minZ; $z <= $maxZ; $z += 0.5) {
                        if ($x === $minX || $x === $maxX || $z === $minZ || $z === $maxZ) {
                            $claimedBy = FactionsAPI::getFactionClaim($level, $chunkX, $chunkZ);
                            if ($claimedBy !== "") {
                                if (FactionsAPI::isInFaction($player->getName())) {
                                    $color = Utils::getBorderColor(true, $claimedBy, FactionsAPI::getFaction($player->getName()));
                                    $player->getWorld()->addParticle(new Vector3($x, $pos->getY() + 0.8, $z),new DustParticle(new Color((int)$color[0], (int)$color[1], (int)$color[2])),[$player]);
                                } else {
                                    $color = Utils::getBorderColor(true, $claimedBy);
                                    $player->getWorld()->addParticle(new Vector3($x, $pos->getY() + 0.8, $z),new DustParticle(new Color((int)$color[0], (int)$color[1], (int)$color[2])), [$player]);
                                }
                            } else {
                                $color = Utils::getBorderColor(false);
                                $player->getWorld()->addParticle(new Vector3($x, $pos->getY() + 0.8, $z),new DustParticle(new Color((int)$color[0], (int)$color[1], (int)$color[2])), [$player]);
                            }
                        }
                    }
                }
            } else unset(FactionsAPI::$border[$name]);
        }
    }
}
