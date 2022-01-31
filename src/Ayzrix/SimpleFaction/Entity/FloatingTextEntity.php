<?php

namespace Ayzrix\SimpleFaction\Entity;

use Ayzrix\SimpleFaction\API\FactionsAPI;
use Ayzrix\SimpleFaction\Utils\Utils;
use JetBrains\PhpStorm\Pure;
use pocketmine\entity\Entity;
use pocketmine\entity\EntitySizeInfo;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\protocol\types\entity\EntityIds;

class FloatingTextEntity extends Entity {

    public float $height = 0.1;
    public float $width = 0.1;
    public $gravity = 0;

    public static function getNetworkTypeId() : string{ return EntityIds::NPC; }

    #[Pure] protected function getInitialSizeInfo() : EntitySizeInfo{
        return new EntitySizeInfo(0.1, 0.1); //TODO: eye height ??
    }

    public function getName(): string {
        return "FloatingTextEntity";
    }

    public function initEntity(CompoundTag $nbt): void
    {
        parent::initEntity($nbt);
        $this->setImmobile(true);
        $this->setNameTagAlwaysVisible(true);
        $this->setScale(0.001);
    }

    public function attack(EntityDamageEvent $source): void {
        $source->cancel();
    }

    public function onUpdate(int $currentTick): bool {
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
