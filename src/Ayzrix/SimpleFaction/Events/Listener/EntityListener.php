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
use Ayzrix\SimpleFaction\Utils\Utils;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\Listener;
use pocketmine\player\Player;

class EntityListener implements Listener {

    public function EntityDamageByEntity(EntityDamageByEntityEvent $event): void {
        $player = $event->getEntity();
        $victime = $event->getDamager();
        if ($player instanceof Player and $victime instanceof Player) {
            if (FactionsAPI::isInFaction($player->getName()) and FactionsAPI::isInFaction($victime->getName())) {
                $faction1 = FactionsAPI::getFaction($player->getName());
                $faction2 = FactionsAPI::getFaction($victime->getName());
                if (Utils::getIntoConfig("faction_pvp") == false and $faction1 === $faction2) $event->cancel();
                if (Utils::getIntoConfig("alliance_pvp") == false and FactionsAPI::areAllies($faction1, $faction2)) $event->cancel();
            }
        }
    }
}
