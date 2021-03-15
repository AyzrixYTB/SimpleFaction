<?php

namespace Ayzrix\SimpleFaction\Commands;

use Ayzrix\SimpleFaction\API\FactionsAPI;
use Ayzrix\SimpleFaction\Main;
use Ayzrix\SimpleFaction\Utils\Utils;
use pocketmine\command\CommandSender;
use pocketmine\command\PluginCommand;
use pocketmine\Player;

class Faction extends PluginCommand {

    public function __construct(Main $plugin) {
        parent::__construct("faction", $plugin);
        $this->setDescription("Faction main command");
        $this->setAliases(["f", "fac"]);
    }

    public function execute(CommandSender $player, string $commandLabel, array $args): bool {
        if ($player instanceof Player) {
            if(isset($args[0])) {
                switch ($args[0]) {
                    case "help":
                    case "h":
                        if(isset($args[1])) {
                            switch ($args[1]) {
                                case 2:
                                    $player->sendMessage(Utils::getConfigMessage("HELP_HEADER", array(2, 5)));
                                    $player->sendMessage(Utils::getConfigMessage("HELP_2", array(2, 5)));
                                    break;
                                case 3:
                                    $player->sendMessage(Utils::getConfigMessage("HELP_HEADER", array(3, 5)));
                                    $player->sendMessage(Utils::getConfigMessage("HELP_3", array(2, 5)));
                                    break;
                                case 4:
                                    $player->sendMessage(Utils::getConfigMessage("HELP_HEADER", array(4, 5)));
                                    $player->sendMessage(Utils::getConfigMessage("HELP_4", array(2, 5)));
                                    break;
                                case 5:
                                    $player->sendMessage(Utils::getConfigMessage("HELP_HEADER", array(5, 5)));
                                    $player->sendMessage(Utils::getConfigMessage("HELP_5", array(2, 5)));
                                    break;
                                default:
                                    $player->sendMessage(Utils::getConfigMessage("HELP_HEADER", array(1, 5)));
                                    $player->sendMessage(Utils::getConfigMessage("HELP_1", array(2, 5)));
                                    break;
                            }
                        } else {
                            $player->sendMessage(Utils::getConfigMessage("HELP_HEADER", array(1, 5)));
                            $player->sendMessage(Utils::getConfigMessage("HELP_1", array(2, 5)));
                        }
                        return true;
                    case "create":
                    case "make":
                        if (isset($args[1])) {
                            if (ctype_alnum($args[1])) {
                                if(strlen($args[1]) > Utils::getIntoConfig("min_faction_name_lenght")) {
                                    if (strlen($args[1]) < Utils::getIntoConfig("max_faction_name_lenght")) {
                                        if (!FactionsAPI::existsFaction($args[1])) {
                                            if (!FactionsAPI::isInFaction($player)) {
                                                $player->sendMessage(Utils::getConfigMessage("SUCESSFULL_CREATED", array($args[1])));
                                                FactionsAPI::createFaction($player, $args[1]);
                                            } else $player->sendMessage(Utils::getConfigMessage("ALREADY_IN_FACTION"));
                                        } else $player->sendMessage(Utils::getConfigMessage("FACTION_ALREADY_EXIST"));
                                    } else $player->sendMessage(Utils::getConfigMessage("FACTION_NAME_TOO_LONG"));
                                } else $player->sendMessage(Utils::getConfigMessage("FACTION_NAME_TOO_SHORT"));
                            } else $player->sendMessage(Utils::getConfigMessage("INVALID_NAME"));
                        } else $player->sendMessage(Utils::getConfigMessage("CREATE_USAGE"));
                        return true;
                    case "delete":
                    case "del":
                    case "disband":
                        if (FactionsAPI::isInFaction($player)) {
                            if (FactionsAPI::getRank($player->getName()) === "Leader") {
                                $player->sendMessage(Utils::getConfigMessage("SUCESSFULL_DISBAND", array(FactionsAPI::getFaction($player))));
                                FactionsAPI::disbandFaction($player, FactionsAPI::getFaction($player));
                            } else $player->sendMessage(Utils::getConfigMessage("ONLY_LEADER"));
                        } else $player->sendMessage(Utils::getConfigMessage("MUST_BE_IN_FACTION"));
                        return true;
                    case "info":
                        if (isset($args[1])) {
                            if (FactionsAPI::existsFaction($args[1])) {
                                $faction = $args[1];
                                $power = FactionsAPI::getPower($faction);
                                $leader = FactionsAPI::getLeader($faction);
                                $officers = implode(" ,", FactionsAPI::getOfficers($faction));
                                $members = implode(" ,", FactionsAPI::getMembers($faction));
                                $memberscount = count(FactionsAPI::getAllPlayers($faction));
                                $player->sendMessage(Utils::getConfigMessage("FACTION_INFO_HEADER", array($faction)));
                                $message = Utils::getIntoConfig("FACTON_INFO_CONTENT");
                                $message = str_replace("{faction}", $faction, $message);
                                $message = str_replace("{power}", $power, $message);
                                $message = str_replace("{leader}", $leader, $message);
                                $message = str_replace("{officers}", $officers, $message);
                                $message = str_replace("{members}", $members, $message);
                                $message = str_replace("{memberscount}", $memberscount, $message);
                                $player->sendMessage($message);
                            } else $player->sendMessage(Utils::getConfigMessage("FACTION_NOT_EXIST"));
                        } else {
                            if (FactionsAPI::isInFaction($player)) {
                                $faction = FactionsAPI::getFaction($player);
                                $power = FactionsAPI::getPower($faction);
                                $leader = FactionsAPI::getLeader($faction);
                                $officers = implode(" ,", FactionsAPI::getOfficers($faction));
                                $members = implode(" ,", FactionsAPI::getMembers($faction));
                                $memberscount = count(FactionsAPI::getAllPlayers($faction));
                                $player->sendMessage(Utils::getConfigMessage("FACTION_INFO_HEADER", array($faction)));
                                $message = Utils::getIntoConfig("FACTON_INFO_CONTENT");
                                $message = str_replace("{faction}", $faction, $message);
                                $message = str_replace("{power}", $power, $message);
                                $message = str_replace("{leader}", $leader, $message);
                                $message = str_replace("{officers}", $officers, $message);
                                $message = str_replace("{members}", $members, $message);
                                $message = str_replace("{memberscount}", $memberscount, $message);
                                $player->sendMessage($message);
                            } else $player->sendMessage(Utils::getConfigMessage("MUST_BE_IN_FACTION"));
                        }
                        return true;
                    case "sethome":
                        if (FactionsAPI::isInFaction($player)) {
                            if (FactionsAPI::getRank($player->getName()) === "Leader" or FactionsAPI::getRank($player->getName()) === "Officer") {
                                if (!FactionsAPI::existsHome(FactionsAPI::getFaction($player))) {
                                    if (in_array($player->getLevel()->getFolderName(), Utils::getIntoConfig("faction_worlds"))) {
                                        $faction = FactionsAPI::getFaction($player);
                                        FactionsAPI::createHome($faction, $player->getPosition());
                                        $player->sendMessage(Utils::getConfigMessage("HOME_SET"));
                                    } else $player->sendMessage(Utils::getConfigMessage("NOT_FACTION_WORLD"));
                                } else $player->sendMessage(Utils::getConfigMessage("ALREADY_HAVE_HOME"));
                            } else $player->sendMessage(Utils::getConfigMessage("ONLY_LEADER_OR_OFFICER"));
                        } else $player->sendMessage(Utils::getConfigMessage("MUST_BE_IN_FACTION"));
                        return true;
                    case "delhome":
                        if (FactionsAPI::isInFaction($player)) {
                            if (FactionsAPI::getRank($player->getName()) === "Leader" or FactionsAPI::getRank($player->getName()) === "Officer") {
                                if (FactionsAPI::existsHome(FactionsAPI::getFaction($player))) {
                                    $faction = FactionsAPI::getFaction($player);
                                    FactionsAPI::deleteHome($faction);
                                    $player->sendMessage(Utils::getConfigMessage("HOME_DELETE"));
                                } else $player->sendMessage(Utils::getConfigMessage("NOT_HAVE_HOME"));
                            } else $player->sendMessage(Utils::getConfigMessage("ONLY_LEADER_OR_OFFICER"));
                        } else $player->sendMessage(Utils::getConfigMessage("MUST_BE_IN_FACTION"));
                        return true;
                    case "home":
                        if (FactionsAPI::isInFaction($player)) {
                            if (FactionsAPI::existsHome(FactionsAPI::getFaction($player))) {
                                $faction = FactionsAPI::getFaction($player);
                                $player->teleport(FactionsAPI::getHome($faction));
                                $player->sendMessage(Utils::getConfigMessage("HOME_TELEPORTED"));
                            } else $player->sendMessage(Utils::getConfigMessage("NOT_HAVE_HOME"));
                        } else $player->sendMessage(Utils::getConfigMessage("MUST_BE_IN_FACTION"));
                        return true;
                    case 'claim':
                        if (FactionsAPI::isInFaction($player)) {
                            if (FactionsAPI::getRank($player->getName()) === "Leader" or FactionsAPI::getRank($player->getName()) === "Officer") {
                                if (in_array($player->getLevel()->getFolderName(), Utils::getIntoConfig("faction_worlds"))) {
                                    if (!FactionsAPI::isInClaim($player)) {
                                        $faction = FactionsAPI::getFaction($player);
                                        $claimCount = FactionsAPI::getClaimCount($faction);
                                        if ($claimCount - 1 < count(Utils::getIntoConfig("claims"))) {
                                            $powerNeeded = (int)Utils::getIntoConfig("claims")[$claimCount];
                                            if (FactionsAPI::getPower($faction) >= $powerNeeded) {
                                                FactionsAPI::claimChunk($player, $faction);
                                                $player->sendMessage(Utils::getConfigMessage("CLAIM_SUCESS"));
                                            } else $player->sendMessage(Utils::getConfigMessage("NOT_ENOUGHT_POWER", array($powerNeeded)));
                                        } else $player->sendMessage(Utils::getConfigMessage("MAX_CLAIM"));
                                    } else $player->sendMessage(Utils::getConfigMessage("ALREADY_CLAIMED", array(FactionsAPI::getFactionClaim($player))));
                                } else $player->sendMessage(Utils::getConfigMessage("NOT_FACTION_WORLD"));
                            } else $player->sendMessage(Utils::getConfigMessage("ONLY_LEADER_OR_OFFICER"));
                        } else $player->sendMessage(Utils::getConfigMessage("MUST_BE_IN_FACTION"));
                        return true;
                    case "unclaim":
                        if (FactionsAPI::isInFaction($player)) {
                            if (FactionsAPI::getRank($player->getName()) === "Leader" or FactionsAPI::getRank($player->getName()) === "Officer") {
                                if (FactionsAPI::isInClaim($player)) {
                                    $faction = FactionsAPI::getFaction($player);
                                    if (FactionsAPI::getFactionClaim($player) === $faction) {
                                        FactionsAPI::deleteClaim($player, $faction);
                                        $player->sendMessage(Utils::getConfigMessage("UNCLAIM_SUCESS"));
                                    } else $player->sendMessage(Utils::getConfigMessage("NOT_CLAIM_BY_YOUR_FACTION"));
                                } else $player->sendMessage(Utils::getConfigMessage("NOT_CLAIM_BY_YOUR_FACTION"));
                            } else $player->sendMessage(Utils::getConfigMessage("ONLY_LEADER_OR_OFFICER"));
                        } else $player->sendMessage(Utils::getConfigMessage("MUST_BE_IN_FACTION"));
                        return true;
                    case "leave":
                        if (FactionsAPI::isInFaction($player)) {
                            if (FactionsAPI::getRank($player->getName()) !== "Leader") {
                                FactionsAPI::leaveFaction($player);
                                $player->sendMessage(Utils::getConfigMessage("LEAVE_SUCESS"));
                            } else $player->sendMessage(Utils::getConfigMessage("LEADER_CANNOT_LEAVE"));
                        } else $player->sendMessage(Utils::getConfigMessage("MUST_BE_IN_FACTION"));
                        return true;
                    case "kick":
                        if (isset($args[1])) {
                            if (FactionsAPI::isInFaction($player)) {
                                if (FactionsAPI::getRank($player->getName()) === "Leader" or FactionsAPI::getRank($player->getName()) === "Officer") {
                                    $faction = FactionsAPI::getFaction($player);
                                    if (FactionsAPI::getPlayerFaction($args[1]) === $faction) {
                                        if (FactionsAPI::getRank($args[1]) !== "Leader" and FactionsAPI::getRank($args[1]) !== FactionsAPI::getRank($player->getName())) {
                                            FactionsAPI::kickFaction($args[1]);
                                            $player->sendMessage(Utils::getConfigMessage("KICK_SUCESS", array($args[1])));
                                        } else $player->sendMessage(Utils::getConfigMessage("CANNOT_KICK_PLAYER"));
                                    } else $player->sendMessage(Utils::getConfigMessage("PLAYER_NOT_IN_YOUR_FACTION"));
                                } else $player->sendMessage(Utils::getConfigMessage("ONLY_LEADER_OR_OFFICER"));
                            } else $player->sendMessage(Utils::getConfigMessage("MUST_BE_IN_FACTION"));
                        } else $player->sendMessage(Utils::getConfigMessage("KICK_USAGE"));
                        return true;
                    case "promote":
                        if (isset($args[1])) {
                            if (FactionsAPI::isInFaction($player)) {
                                if (FactionsAPI::getRank($player->getName()) === "Leader") {
                                    $faction = FactionsAPI::getFaction($player);
                                    if (FactionsAPI::getPlayerFaction($args[1]) === $faction) {
                                        if (FactionsAPI::getRank($args[1]) === "Member") {
                                            FactionsAPI::promoteFaction($args[1]);
                                            $player->sendMessage(Utils::getConfigMessage("PROMOTE_SUCESS", array($args[1])));
                                        } else $player->sendMessage(Utils::getConfigMessage("PLAYER_ALREADY_OFFICER"));
                                    } else $player->sendMessage(Utils::getConfigMessage("PLAYER_NOT_IN_YOUR_FACTION", array($args[1])));
                                } else $player->sendMessage(Utils::getConfigMessage("ONLY_LEADER"));
                            } else $player->sendMessage(Utils::getConfigMessage("MUST_BE_IN_FACTION"));
                        } else $player->sendMessage(Utils::getConfigMessage("PROMOTE_USAGE"));
                        return true;
                    case "demote":
                        if (isset($args[1])) {
                            if (FactionsAPI::isInFaction($player)) {
                                if (FactionsAPI::getRank($player->getName()) === "Leader") {
                                    $faction = FactionsAPI::getFaction($player);
                                    if (FactionsAPI::getPlayerFaction($args[1]) === $faction) {
                                        if (FactionsAPI::getRank($args[1]) === "Officer") {
                                            FactionsAPI::demoteFaction($args[1]);
                                            $player->sendMessage(Utils::getConfigMessage("DEMOTE_SUCESS", array($args[1])));
                                        } else $player->sendMessage(Utils::getConfigMessage("PLAYER_ALREADY_MEMBER"));
                                    } else $player->sendMessage(Utils::getConfigMessage("PLAYER_NOT_IN_YOUR_FACTION", array($args[1])));
                                } else $player->sendMessage(Utils::getConfigMessage("ONLY_LEADER"));
                            } else $player->sendMessage(Utils::getConfigMessage("MUST_BE_IN_FACTION"));
                        } else $player->sendMessage(Utils::getConfigMessage("DEMOTE_USAGE"));
                        return true;
                    case "about":
                        $player->sendMessage("§c§lPlugin created by Ayzrix.");
                        $player->sendMessage("§4§lYoutube:§r§f Ayzrix");
                        $player->sendMessage("§b§lTwitter:§r§f @Ayzrix");
                        $player->sendMessage("§6§lDownload link:§r§f github.com/AyzrixYTB/SimpleFaction");
                        return true;
                    default:
                        $player->sendMessage(Utils::getConfigMessage("COMMAND_USAGE"));
                        return true;
                }
            } else $player->sendMessage(Utils::getConfigMessage("COMMAND_USAGE"));
        } else $player->sendMessage(Utils::getConfigMessage("PLAYER_ONLY"));
        return true;
    }
}