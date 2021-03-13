<?php

namespace Ayzrix\SimpleFaction\Events\Listener;

use Ayzrix\SimpleFaction\API\FactionsAPI;
use Ayzrix\SimpleFaction\Utils\Utils;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\Player;

class PlayerListener implements Listener {

    public function PlayerDeath(PlayerDeathEvent $event) {
        $player = $event->getPlayer();
        if ($player instanceof Player) {
            $cause = $player->getLastDamageCause();
            if ($cause instanceof EntityDamageByEntityEvent) {
                $damager = $cause->getDamager();
                if ($damager instanceof Player) {
                    if (FactionsAPI::isInFaction($damager)) {
                        $dFaction = FactionsAPI::getFaction($damager);
                        FactionsAPI::addPower($dFaction, (int)Utils::getIntoConfig("power_gain_per_kill"));
                    }
                }
            }

            if (FactionsAPI::isInFaction($player)) {
                $pFaction = FactionsAPI::getFaction($player);
                FactionsAPI::removePower($pFaction, (int)Utils::getIntoConfig("power_lost_per_death"));
            }
        }
    }
}