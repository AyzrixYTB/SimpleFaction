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

namespace Ayzrix\SimpleFaction\Events;

use Ayzrix\SimpleFaction\API\FactionsAPI;
use Ayzrix\SimpleFaction\Utils\Utils;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\level\format\Chunk;

class PlayerMove implements Listener {

    public function PlayerMove (PlayerMoveEvent $event) {
        $player = $event->getPlayer();
        if (in_array($player->getLevel()->getFolderName(), Utils::getIntoConfig("faction_worlds"))) {
            $fromPos = $player->getLevel()->getChunkAtPosition($event->getFrom());
            $toPos = $player->getLevel()->getChunkAtPosition($event->getTo());
            if ($fromPos instanceof Chunk and $toPos instanceof Chunk) {
                if ($fromPos->getX() !== $toPos->getX() || $fromPos->getZ() !== $toPos->getZ()) {
                    if (isset(FactionsAPI::$moving[$player->getName()])) {
                        $zone = FactionsAPI::$moving[$player->getName()];
                        $chunkXP = $toPos->getX();
                        $chunkZP = $toPos->getZ();
                        if (FactionsAPI::isInClaim($player->getLevel(), $chunkXP, $chunkZP)) {
                            $claimer = FactionsAPI::getFactionClaim($player->getLevel(), $chunkXP, $chunkZP);
                            if ($zone !== $claimer) {
                                FactionsAPI::$moving[$player->getName()] = $claimer;
                                $player->sendMessage(Utils::getMessage($player, "LEAVING_MESSAGE", array(Utils::getZoneColor($player, $zone) . $zone)));
                                $player->sendMessage(Utils::getMessage($player, "ENTERING_MESSAGE", array(Utils::getZoneColor($player, $claimer) . $claimer)));
                            }
                        } else {
                            if ($zone !== "Wilderness") {
                                FactionsAPI::$moving[$player->getName()] = "Wilderness";
                                $player->sendMessage(Utils::getMessage($player, "LEAVING_MESSAGE", array(Utils::getZoneColor($player, $zone) . $zone)));
                                $player->sendMessage(Utils::getMessage($player, "ENTERING_MESSAGE", array(Utils::getZoneColor($player, "Wilderness") . "Wilderness")));
                            }
                        }
                    } else {
                        $chunkX = $toPos->getX();
                        $chunkZ = $toPos->getZ();
                        if (FactionsAPI::isInClaim($player->getLevel(), $chunkX, $chunkZ)) {
                            $claimer = FactionsAPI::getFactionClaim($player->getLevel(), $chunkX, $chunkZ);
                            FactionsAPI::$moving[$player->getName()] = $claimer;
                        } else {
                            FactionsAPI::$moving[$player->getName()] = "Wilderness";
                        }
                    }
                }
            }
        }
    }
}