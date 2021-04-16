<?php

namespace Ayzrix\SimpleFaction\Entity;

use Ayzrix\SimpleFaction\API\FactionsAPI;
use Ayzrix\SimpleFaction\Utils\Utils;
use pocketmine\entity\Entity;
use pocketmine\entity\EntityIds as Ids;
use pocketmine\event\entity\EntityDamageEvent;

class FloatingTextEntity extends Entity {

    const NETWORK_ID = Ids::NPC;
    public $height = 0.1;
    public $width = 0.1;
    public $gravity = 0;

    public function getName() : string {
        return "FloatingTextEntity";
    }

    public function initEntity() : void {
        parent::initEntity();
        $this->setImmobile(true);
        $this->setNameTagAlwaysVisible(true);
        $this->setScale(0.001);
    }

    public function attack(EntityDamageEvent $source): void {
        $source->setCancelled(true);
    }

    public function onUpdate(int $currentTick) : bool {
        $factions = FactionsAPI::getAllPowers();
        arsort($factions);
        $i = 1;
        $nametag = Utils::getIntoConfig("floating_text_title") . "\n";
        foreach ($factions as $faction => $powers) {
            if ($i > (int)Utils::getIntoConfig("floating_text_limit")) break;
            $message = Utils::getIntoConfig("floating_text_line");
            $message = str_replace(["{number}", "{faction}", "{power}", "{bank}", "{members}"], [$i, $faction, $powers, FactionsAPI::getMoney($faction), count(FactionsAPI::getAllPlayers($faction))], $message);
            $nametag .= $message . "\n";
            $i++;
        }
        $this->setNameTag($nametag);
        return parent::onUpdate($currentTick);
    }

    public function tryChangeMovement(): void {}
}
