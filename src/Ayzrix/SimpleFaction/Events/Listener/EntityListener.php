<?php

namespace Ayzrix\SimpleFaction\Events\Listener;

use Ayzrix\SimpleFaction\API\FactionsAPI;
use Ayzrix\SimpleFaction\Utils\Utils;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\Listener;
use pocketmine\Player;

class EntityListener implements Listener {

    public function EntityDamageByEntity(EntityDamageByEntityEvent $event) {
        $player = $event->getEntity();
        $victime = $event->getDamager();
        if ($player instanceof Player and $victime instanceof Player) {
            if (FactionsAPI::isInFaction($player) and FactionsAPI::isInFaction($victime)) {
                $faction1 = FactionsAPI::getFaction($player);
                $faction2 = FactionsAPI::getFaction($victime);
                if (Utils::getIntoConfig("faction_pvp") === false and $faction1 === $faction2) $event->setCancelled();
                if (Utils::getIntoConfig("alliance_pvp") === false and FactionsAPI::areAllies($faction1, $faction2)) $event->setCancelled();
            }
        }
    }
}