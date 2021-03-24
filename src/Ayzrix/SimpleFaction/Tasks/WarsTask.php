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

namespace Ayzrix\SimpleFaction\Tasks;

use Ayzrix\SimpleFaction\API\FactionsAPI;
use Ayzrix\SimpleFaction\Main;
use Ayzrix\SimpleFaction\Utils\Utils;
use pocketmine\Player;
use pocketmine\scheduler\Task;
use pocketmine\Server;

class WarsTask extends Task {

    /**
     * @var string
     */
    private $faction;

    /**
     * @var string
     */
    private $faction2;

    /**
     * WarsTask constructor.
     * @param string $faction
     * @param string $faction2
     */
    public function __construct(string $faction, string $faction2)
    {
        $this->faction = $faction;
        $this->faction2 = $faction2;
    }

    /**
     * @param int $currentTick
     */
    public function onRun(int $currentTick) {
        if (FactionsAPI::$Wars[$this->faction]["kills"] > FactionsAPI::$Wars[$this->faction2]["kills"]) {
            foreach (FactionsAPI::getAllPlayers($this->faction) as $player) {
                if (Server::getInstance()->getPlayer($player)) {
                    $player = Server::getInstance()->getPlayer($player);
                    if ($player instanceof Player) {
                        $player->sendMessage(Utils::getMessage($player, "WIN_WARS_FACTION_BROADCAST", array($this->faction2)));
                    }
                }
            }

            foreach (FactionsAPI::getAllPlayers($this->faction2) as $player) {
                if (Server::getInstance()->getPlayer($player)) {
                    $player = Server::getInstance()->getPlayer($player);
                    if ($player instanceof Player) {
                        $player->sendMessage(Utils::getMessage($player, "LOST_WARS_FACTION_BROADCAST", array($this->faction)));
                    }
                }
            }
        } else if (FactionsAPI::$Wars[$this->faction2] > FactionsAPI::$Wars[$this->faction]) {
            foreach (FactionsAPI::getAllPlayers($this->faction2) as $player) {
                if (Server::getInstance()->getPlayer($player)) {
                    $player = Server::getInstance()->getPlayer($player);
                    if ($player instanceof Player) {
                        $player->sendMessage(Utils::getMessage($player, "WIN_WARS_FACTION_BROADCAST", array($this->faction)));
                    }
                }
            }

            foreach (FactionsAPI::getAllPlayers($this->faction) as $player) {
                if (Server::getInstance()->getPlayer($player)) {
                    $player = Server::getInstance()->getPlayer($player);
                    if ($player instanceof Player) {
                        $player->sendMessage(Utils::getMessage($player, "LOST_WARS_FACTION_BROADCAST", array($this->faction2)));
                    }
                }
            }
        }

        unset(FactionsAPI::$Wars[$this->faction]);
        unset(FactionsAPI::$Wars[$this->faction2]);
    }
}