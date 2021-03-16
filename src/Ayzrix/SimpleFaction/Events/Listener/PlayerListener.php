<?php

namespace Ayzrix\SimpleFaction\Events\Listener;

use Ayzrix\SimpleFaction\API\FactionsAPI;
use Ayzrix\SimpleFaction\Utils\Utils;
use pocketmine\block\BlockIds;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\item\ItemIds;
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

    public function PlayerInteract(PlayerInteractEvent $event) {
        $player = $event->getPlayer();
        $block = $event->getBlock();
        $item = $event->getItem();
        if (FactionsAPI::isInClaim($player)) {
            switch ($block->getId()) {
                case BlockIds::FENCE_GATE:
                case BlockIds::ACACIA_FENCE_GATE:
                case BlockIds::BIRCH_FENCE_GATE:
                case BlockIds::DARK_OAK_FENCE_GATE:
                case BlockIds::SPRUCE_FENCE_GATE:
                case BlockIds::JUNGLE_FENCE_GATE:
                case BlockIds::IRON_TRAPDOOR:
                case BlockIds::WOODEN_TRAPDOOR:
                case BlockIds::TRAPDOOR:
                case BlockIds::OAK_FENCE_GATE:
                case BlockIds::CHEST:
                case BlockIds::TRAPPED_CHEST:
                    if (FactionsAPI::isInFaction($player)) {
                        $claimer = FactionsAPI::getFactionClaim($player);
                        $faction = FactionsAPI::getFaction($player);
                        if ($faction !== $claimer) $event->setCancelled(true);
                    } else $event->setCancelled(true);
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
                    if (FactionsAPI::isInFaction($player)) {
                        $claimer = FactionsAPI::getFactionClaim($player);
                        $faction = FactionsAPI::getFaction($player);
                        if ($faction !== $claimer) $event->setCancelled(true);
                    } else $event->setCancelled(true);
            }
        }
    }

    public function PlayerChat(PlayerChatEvent $event) {
        $player = $event->getPlayer();
        $message = $event->getMessage();

        if (isset(FactionsAPI::$chat[$player->getName()])) {
            $chat = FactionsAPI::$chat[$player->getName()];
            switch ($chat) {
                case "FACTION":
                    $event->setCancelled(true);
                    FactionsAPI::factionMessage($player, $message);
                    break;
                case "ALLIANCE":
                    $event->setCancelled(true);
                    FactionsAPI::allyMessage($player, $message);
                    break;
            }
        }
    }
}