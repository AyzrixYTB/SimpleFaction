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

namespace Ayzrix\SimpleFaction\Events\Listener;

use Ayzrix\SimpleFaction\API\FactionsAPI;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\Listener;

class BlockListener implements Listener {

    public function BlockBreak(BlockBreakEvent $event) {
        $player = $event->getPlayer();
        $chunk = $player->getLevel()->getChunkAtPosition($event->getBlock());
        $chunkX = $chunk->getX();
        $chunkZ = $chunk->getZ();
        if (FactionsAPI::isInClaim($player->getLevel(), $chunkX, $chunkZ)) {
            if (FactionsAPI::isInFaction($player)) {
                $claimer = FactionsAPI::getFactionClaim($player->getLevel(), $chunkX, $chunkZ);
                $faction = FactionsAPI::getFaction($player);
                if ($faction !== $claimer) $event->setCancelled(true);
            } else $event->setCancelled(true);
        }
    }

    public function BlockPlace(BlockBreakEvent $event) {
        $player = $event->getPlayer();
        $chunk = $player->getLevel()->getChunkAtPosition($event->getBlock());
        $chunkX = $chunk->getX();
        $chunkZ = $chunk->getZ();
        if (FactionsAPI::isInClaim($player->getLevel(), $chunkX, $chunkZ)) {
            if (FactionsAPI::isInFaction($player)) {
                $claimer = FactionsAPI::getFactionClaim($player->getLevel(), $chunkX, $chunkZ);
                $faction = FactionsAPI::getFaction($player);
                if ($faction !== $claimer) $event->setCancelled(true);
            } else $event->setCancelled(true);
        }
    }
}