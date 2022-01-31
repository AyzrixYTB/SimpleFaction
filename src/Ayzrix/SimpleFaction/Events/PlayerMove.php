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
use pocketmine\math\Vector3;
use pocketmine\world\format\Chunk;

class PlayerMove implements Listener {

    public function PlayerMove (PlayerMoveEvent $event): void {
        $player = $event->getPlayer();
        if (in_array($player->getWorld()->getFolderName(), Utils::getIntoConfig("faction_worlds"))) {
            $chunkFrom = $event->getFrom()->asVector3();
            $fromPosX = $chunkFrom->getFloorX() >> Chunk::COORD_BIT_SIZE;
            $fromPosZ = $chunkFrom->getFloorZ() >> Chunk::COORD_BIT_SIZE;
            $chunkTo = $event->getFrom()->asVector3();
            $toPosX = $chunkTo->getFloorX() >> Chunk::COORD_BIT_SIZE;
            $toPosZ = $chunkTo->getFloorZ() >> Chunk::COORD_BIT_SIZE;
            if ($player->getWorld()->getOrLoadChunkAtPosition($chunkFrom) instanceof Chunk and $player->getWorld()->getOrLoadChunkAtPosition($chunkTo) instanceof Chunk) {
                if ($fromPosX !== $toPosX || $fromPosZ !== $toPosZ) {
                    if (isset(FactionsAPI::$moving[$player->getName()])) {
                        $zone = FactionsAPI::$moving[$player->getName()];
                        if (FactionsAPI::isInClaim($player->getWorld(), $toPosX, $toPosZ)) {
                            $claimer = FactionsAPI::getFactionClaim($player->getWorld(), $toPosX, $toPosZ);
                            if ($zone !== $claimer) {
                                FactionsAPI::$moving[$player->getName()] = $claimer;
                                switch (strtolower(Utils::getIntoConfig("entering_leaving_messsage"))) {
                                    case "message":
                                        $player->sendMessage(Utils::getMessage($player, "LEAVING_MESSAGE", array(Utils::getZoneColor($player, $zone) . $zone)));
                                        $player->sendMessage(Utils::getMessage($player, "ENTERING_MESSAGE", array(Utils::getZoneColor($player, $claimer) . $claimer)));
                                        break;
                                    case "popup":
                                        $player->sendPopup(Utils::getMessage($player, "LEAVING_MESSAGE", array(Utils::getZoneColor($player, $zone) . $zone)));
                                        $player->sendPopup(Utils::getMessage($player, "ENTERING_MESSAGE", array(Utils::getZoneColor($player, $claimer) . $claimer)));
                                        break;
                                    case "tip":
                                        $player->sendTip(Utils::getMessage($player, "LEAVING_MESSAGE", array(Utils::getZoneColor($player, $zone) . $zone)));
                                        $player->sendTip(Utils::getMessage($player, "ENTERING_MESSAGE", array(Utils::getZoneColor($player, $claimer) . $claimer)));
                                        break;
                                }
                            }
                        } else {
                            if ($zone !== "Wilderness") {
                                FactionsAPI::$moving[$player->getName()] = "Wilderness";
                                switch (strtolower(Utils::getIntoConfig("entering_leaving_messsage"))) {
                                    case "message":
                                        $player->sendMessage(Utils::getMessage($player, "LEAVING_MESSAGE", array(Utils::getZoneColor($player, $zone) . $zone)));
                                        $player->sendMessage(Utils::getMessage($player, "ENTERING_MESSAGE", array(Utils::getZoneColor($player, "Wilderness") . "Wilderness")));
                                        break;
                                    case "popup":
                                        $player->sendPopup(Utils::getMessage($player, "LEAVING_MESSAGE", array(Utils::getZoneColor($player, $zone) . $zone)));
                                        $player->sendPopup(Utils::getMessage($player, "ENTERING_MESSAGE", array(Utils::getZoneColor($player, "Wilderness") . "Wilderness")));
                                        break;
                                    case "tip":
                                        $player->sendTip(Utils::getMessage($player, "LEAVING_MESSAGE", array(Utils::getZoneColor($player, $zone) . $zone)));
                                        $player->sendTip(Utils::getMessage($player, "ENTERING_MESSAGE", array(Utils::getZoneColor($player, "Wilderness") . "Wilderness")));
                                        break;
                                }
                            }
                        }
                    } else {
                        if (FactionsAPI::isInClaim($player->getWorld(), $toPosX, $toPosZ)) {
                            $claimer = FactionsAPI::getFactionClaim($player->getWorld(), $toPosX, $toPosZ);
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
