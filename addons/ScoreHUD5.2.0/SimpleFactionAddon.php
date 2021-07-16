<?php

/**
 * @name SimpleFactionAddon
 * @version 1.0.0
 * @main JackMD\ScoreHud\Addons\SimpleFactionAddon
 * @depend SimpleFaction
 */
namespace JackMD\ScoreHud\Addons {

    use Ayzrix\SimpleFaction\API\FactionsAPI;
    use JackMD\ScoreHud\addon\AddonBase;
	use pocketmine\Player;

	class SimpleFactionAddon extends AddonBase {

		/**
		 * @param Player $player
		 * @return array
		 */
		public function getProcessedTags(Player $player): array{
			return [
				"{faction_name}" => $this->getPlayerFaction($player),
				"{faction_power}" => $this->getFactionPower($player),
                "{faction_rank}" => $this->getFactionRank($player)
			];
		}

		/**
		 * @param Player $player
		 * @return string
		 */
		public function getPlayerFaction(Player $player): string{
            if (FactionsAPI::isInFaction($player->getName())) {
                return FactionsAPI::getFaction($player->getName());
            } else return "No Faction";
		}

        /**
         * @param Player $player
         * @return string|int
         */
        public function getFactionPower(Player $player) {
            if (FactionsAPI::isInFaction($player->getName())) {
                return FactionsAPI::getPower(FactionsAPI::getFaction($player->getName()));
            } else return "No Faction";
        }

        /**
         * @param Player $player
         * @return string
         */
        public function getFactionRank(Player $player): string {
		    if (FactionsAPI::isInFaction($player->getName())) {
		        return FactionsAPI::getRank($player->getName());
            } else return "No Faction";
        }
	}
}