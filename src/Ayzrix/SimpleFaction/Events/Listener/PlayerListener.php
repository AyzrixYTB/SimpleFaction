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
use pocketmine\block\Block;
use pocketmine\block\BlockLegacyIds as Ids;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\item\ItemIds;
use pocketmine\player\Player;
use pocketmine\world\format\Chunk;

class PlayerListener implements Listener {

    public function PlayerJoin(PlayerJoinEvent $event): void {
        $player = $event->getPlayer();
        if (!FactionsAPI::hasLanguages($player)) {
            FactionsAPI::setLanguages($player, Utils::getIntoLang("default-language"));
        }
    }

    public function PlayerDeath(PlayerDeathEvent $event): void {
        $player = $event->getPlayer();
        if ($player instanceof Player) {
            $cause = $player->getLastDamageCause();
            if ($cause instanceof EntityDamageByEntityEvent) {
                $damager = $cause->getDamager();
                if ($damager instanceof Player) {
                    if (FactionsAPI::isInFaction($damager->getName())) {
                        $dFaction = FactionsAPI::getFaction($damager->getName());
                        FactionsAPI::addPower($dFaction, (int)Utils::getIntoConfig("power_gain_per_kill"));
                        if (FactionsAPI::isInFaction($player->getName())) {
                            $pFaction = FactionsAPI::getFaction($player->getName());
                            if (isset(FactionsAPI::$Wars[$dFaction]) and FactionsAPI::$Wars[$dFaction]["faction"] === $pFaction) {
                                FactionsAPI::$Wars[$dFaction]["kills"]++;
                            }
                        }
                    }
                }
            }

            if (FactionsAPI::isInFaction($player->getName())) {
                $pFaction = FactionsAPI::getFaction($player->getName());
                FactionsAPI::removePower($pFaction, (int)Utils::getIntoConfig("power_lost_per_death"));
            }
        }
    }

    public function PlayerInteract(PlayerInteractEvent $event): void {
        $player = $event->getPlayer();
        $block = $event->getBlock();
        $item = $event->getItem();
        if (in_array($player->getWorld()->getFolderName(), Utils::getIntoConfig("faction_worlds"))) {
            $pos = $event->getBlock()->getPosition()->asVector3();
            $chunkX = $pos->getFloorX() >> Chunk::COORD_BIT_SIZE;
            $chunkZ = $pos->getFloorZ() >> Chunk::COORD_BIT_SIZE;
            if (FactionsAPI::isInClaim($player->getWorld(), $chunkX, $chunkZ)) {
                switch ($block->getId()) {
                    case Ids::FENCE_GATE:
                    case Ids::ACACIA_FENCE_GATE:
                    case Ids::BIRCH_FENCE_GATE:
                    case Ids::DARK_OAK_FENCE_GATE:
                    case Ids::SPRUCE_FENCE_GATE:
                    case Ids::JUNGLE_FENCE_GATE:
                    case Ids::IRON_TRAPDOOR:
                    case Ids::WOODEN_TRAPDOOR:
                    case Ids::TRAPDOOR:
                    case Ids::OAK_FENCE_GATE:
                    case Ids::CHEST:
                    case Ids::TRAPPED_CHEST:
                    case Ids::FURNACE:
                    case Ids::IRON_DOOR_BLOCK:
                    case Ids::ACACIA_DOOR_BLOCK:
                    case Ids::BIRCH_DOOR_BLOCK:
                    case Ids::DARK_OAK_DOOR_BLOCK:
                    case Ids::JUNGLE_DOOR_BLOCK:
                    case Ids::OAK_DOOR_BLOCK:
                    case Ids::SPRUCE_DOOR_BLOCK:
                    case Ids::ENDER_CHEST:
                        if (FactionsAPI::isInFaction($player->getName())) {
                            $claimer = FactionsAPI::getFactionClaim($player->getWorld(), $chunkX, $chunkZ);
                            $faction = FactionsAPI::getFaction($player->getName());
                            if ($faction !== $claimer) $event->cancel();
                        } else $event->cancel();
                        break;
                }

                    switch ($item->getId()) {
                        case ItemIds::BUCKET:
                        case ItemIds::DIAMOND_HOE:
                        case ItemIds::GOLD_HOE:
                        case ItemIds::IRON_HOE:
                        case ItemIds::STONE_HOE:
                        case ItemIds::WOODEN_HOE:
                        case ItemIds::DIAMOND_SHOVEL:
                        case ItemIds::GOLD_SHOVEL:
                        case ItemIds::IRON_SHOVEL:
                        case ItemIds::STONE_SHOVEL:
                        case ItemIds::WOODEN_SHOVEL:
                            if (FactionsAPI::isInFaction($player->getName())) {
                                $claimer = FactionsAPI::getFactionClaim($player->getWorld(), $chunkX, $chunkZ);
                                $faction = FactionsAPI::getFaction($player->getName());
                                if ($faction !== $claimer) $event->cancel();
                            } else $event->cancel();
                            break;
                    }
            }
        }
    }

    public function PlayerChat(PlayerChatEvent $event): void {
        $player = $event->getPlayer();
        $message = $event->getMessage();
        if (isset(FactionsAPI::$chat[$player->getName()])) {
            $chat = FactionsAPI::$chat[$player->getName()];
            switch ($chat) {
                case "FACTION":
                    $event->cancel();
                    FactionsAPI::factionMessage($player, $message);
                    break;
                case "ALLIANCE":
                    $event->cancel();
                    FactionsAPI::allyMessage($player, $message);
                    break;
            }
        }
    }
}
