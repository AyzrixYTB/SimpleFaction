<?php

namespace Ayzrix\SimpleFaction\Events\Listener;

use Ayzrix\SimpleFaction\API\FactionsAPI;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\Listener;

class BlockListener implements Listener {

    public function BlockBreak(BlockBreakEvent $event) {
        $player = $event->getPlayer();
        if (FactionsAPI::isInClaim($player)) {
            if (FactionsAPI::isInFaction($player)) {
                $claimer = FactionsAPI::getFactionClaim($player);
                $faction = FactionsAPI::getFaction($player);
                if ($faction !== $claimer) $event->setCancelled(true);
            } else $event->setCancelled(true);
        }
    }

    public function BlockPlace(BlockBreakEvent $event) {
        $player = $event->getPlayer();
        if (FactionsAPI::isInClaim($player)) {
            if (FactionsAPI::isInFaction($player)) {
                $claimer = FactionsAPI::getFactionClaim($player);
                $faction = FactionsAPI::getFaction($player);
                if ($faction !== $claimer) $event->setCancelled(true);
            } else $event->setCancelled(true);
        }
    }
}