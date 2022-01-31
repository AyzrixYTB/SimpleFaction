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

namespace Ayzrix\SimpleFaction\Commands;

use Ayzrix\SimpleFaction\API\FactionsAPI;
use Ayzrix\SimpleFaction\Main;
use Ayzrix\SimpleFaction\Utils\Utils;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\utils\TextFormat;
use pocketmine\world\format\Chunk;

class Faction extends Command {

    private Main $pg;
    
    private function getPlugin() : Main {
        return $this->pg;
    }
    
    public function __construct(Main $plugin) {
        $this->pg = $plugin;
        parent::__construct("faction","Faction main command","/f help",["f", "fac"]);
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): bool {
        if ($sender instanceof Player) {
            if(isset($args[0])) {
                switch ($args[0]) {
                    case "help":
                        if(isset($args[1])) {
                            switch ($args[1]) {
                                case 2:
                                    $sender->sendMessage(Utils::getMessage($sender, "HELP_HEADER", array(2, 5)));
                                    $sender->sendMessage(Utils::getMessage($sender, "HELP_2", array(2, 5)));
                                    break;
                                case 3:
                                    $sender->sendMessage(Utils::getMessage($sender, "HELP_HEADER", array(3, 5)));
                                    $sender->sendMessage(Utils::getMessage($sender, "HELP_3", array(2, 5)));
                                    break;
                                case 4:
                                    $sender->sendMessage(Utils::getMessage($sender, "HELP_HEADER", array(4, 5)));
                                    $sender->sendMessage(Utils::getMessage($sender, "HELP_4", array(2, 5)));
                                    break;
                                case 5:
                                    $sender->sendMessage(Utils::getMessage($sender, "HELP_HEADER", array(5, 5)));
                                    $sender->sendMessage(Utils::getMessage($sender, "HELP_5", array(2, 5)));
                                    break;
                                default:
                                    $sender->sendMessage(Utils::getMessage($sender, "HELP_HEADER", array(1, 5)));
                                    $sender->sendMessage(Utils::getMessage($sender, "HELP_1", array(2, 5)));
                                    break;
                            }
                        } else {
                            $sender->sendMessage(Utils::getMessage($sender, "HELP_HEADER", array(1, 5)));
                            $sender->sendMessage(Utils::getMessage($sender, "HELP_1", array(2, 5)));
                        }
                        return true;
                    case "create":
                    case "make":
                        if (isset($args[1])) {
                            if (ctype_alnum($args[1])) {
                                if(strlen($args[1]) > (int)Utils::getIntoConfig("min_faction_name_lenght")) {
                                    if (strlen($args[1]) < (int)Utils::getIntoConfig("max_faction_name_lenght")) {
                                        if (!in_array(strtolower($args[1]), (array)Utils::getIntoConfig("banned_names"))) {
                                            if (!FactionsAPI::existsFaction($args[1])) {
                                                if (!FactionsAPI::isInFaction($sender->getName())) {
                                                    $sender->sendMessage(Utils::getMessage($sender, "SUCCESSFULL_CREATED", array($args[1])));
                                                    FactionsAPI::createFaction($sender, $args[1]);
                                                } else $sender->sendMessage(Utils::getMessage($sender, "ALREADY_IN_FACTION"));
                                            } else $sender->sendMessage(Utils::getMessage($sender, "FACTION_ALREADY_EXIST"));
                                        } else $sender->sendMessage(Utils::getMessage($sender, "FACTION_NAME_BANNED"));
                                    } else $sender->sendMessage(Utils::getMessage($sender, "FACTION_NAME_TOO_LONG"));
                                } else $sender->sendMessage(Utils::getMessage($sender, "FACTION_NAME_TOO_SHORT"));
                            } else $sender->sendMessage(Utils::getMessage($sender, "INVALID_NAME"));
                        } else $sender->sendMessage(Utils::getMessage($sender, "CREATE_USAGE"));
                        return true;
                    case "delete":
                    case "del":
                    case "disband":
                        if (FactionsAPI::isInFaction($sender->getName())) {
                            if (FactionsAPI::getRank($sender->getName()) === "Leader") {
                                $sender->sendMessage(Utils::getMessage($sender, "SUCCESSFULL_DISBAND", array(FactionsAPI::getFaction($sender->getName()))));
                                FactionsAPI::disbandFaction($sender, FactionsAPI::getFaction($sender->getName()));
                            } else $sender->sendMessage(Utils::getMessage($sender, "ONLY_LEADER"));
                        } else $sender->sendMessage(Utils::getMessage($sender, "MUST_BE_IN_FACTION"));
                        return true;
                    case "info":
                        if (isset($args[1])) {
                            if (FactionsAPI::existsFaction($args[1])) {
                                $faction = $args[1];
                                $power = FactionsAPI::getPower($faction);
                                $leader = FactionsAPI::getLeader($faction);
                                if (Server::getInstance()->getPlayerExact($leader)) {
                                    $leaderMessage = "§a{$leader}";
                                } else $leaderMessage = "§c{$leader}";


                                $officers = FactionsAPI::getOfficers($faction);
                                $officerMessage = "";
                                foreach ($officers as $officer) {
                                    if (Server::getInstance()->getPlayerExact($officer)){
                                        $officerMessage .= "§a{$officer}§f, ";
                                    } else $officerMessage .= "§c{$officer}§f, ";
                                }
                                if($officerMessage === "") $officerMessage = "§cNone";

                                $members = FactionsAPI::getMembers($faction);
                                $memberMessage = "";
                                foreach ($members as $member) {
                                    if (Server::getInstance()->getPlayerExact($member)){
                                        $memberMessage .= "§a{$member}§f, ";
                                    } else $memberMessage .= "§c{$member}§f, ";
                                }
                                if($memberMessage === "") $memberMessage = "§cNone";
                                $memberscount = count(FactionsAPI::getAllPlayers($faction));
                                $bank = FactionsAPI::getMoney($faction);
                                $allies = FactionsAPI::getAllies($faction);
                                $alliesMessage = implode(", ", $allies);
                                if (empty($allies)) $alliesMessage = "§cNone";
                                $sender->sendMessage(Utils::getMessage($sender, "FACTION_INFO_HEADER", array($faction)));
                                $message = Utils::getMessage($sender, "FACTON_INFO_CONTENT");
                                $message = str_replace(["{faction}", "{power}", "{leader}", "{officers}", "{members}", "{memberscount}", "{bank}", "{allies}"], [$faction, $power, $leaderMessage, $officerMessage, $memberMessage, $memberscount, $bank, $alliesMessage], $message);
                                $sender->sendMessage($message);
                            } else $sender->sendMessage(Utils::getMessage($sender, "FACTION_NOT_EXIST"));
                        } else {
                            if (FactionsAPI::isInFaction($sender->getName())) {
                                $faction = FactionsAPI::getFaction($sender->getName());
                                $power = FactionsAPI::getPower($faction);
                                $leader = FactionsAPI::getLeader($faction);
                                if (Server::getInstance()->getPlayerExact($leader)) {
                                    $leaderMessage = "§a{$leader}";
                                } else $leaderMessage = "§c{$leader}";
                                $officers = FactionsAPI::getOfficers($faction);
                                $officerMessage = "";
                                foreach ($officers as $officer) {
                                    if (Server::getInstance()->getPlayerExact($officer)){
                                        $officerMessage .= "§a{$officer}§f, ";
                                    } else $officerMessage .= "§c{$officer}§f, ";
                                }
                                if($officerMessage === "") $officerMessage = "§cNone";

                                $members = FactionsAPI::getMembers($faction);
                                $memberMessage = "";
                                foreach ($members as $member) {
                                    if (Server::getInstance()->getPlayerExact($member)){
                                        $memberMessage .= "§a{$member}§f, ";
                                    } else $memberMessage .= "§c{$member}§f, ";
                                }
                                if($memberMessage === "") $memberMessage = "§cNone";
                                $memberscount = count(FactionsAPI::getAllPlayers($faction));
                                $bank = FactionsAPI::getMoney($faction);
                                $allies = FactionsAPI::getAllies($faction);
                                $alliesMessage = implode(", ", $allies);
                                if (empty($allies)) $alliesMessage = "§cNone";
                                $sender->sendMessage(Utils::getMessage($sender, "FACTION_INFO_HEADER", array($faction)));
                                $message = Utils::getMessage($sender, "FACTON_INFO_CONTENT");
                                $message = str_replace(["{faction}", "{power}", "{leader}", "{officers}", "{members}", "{memberscount}", "{bank}", "{allies}"], [$faction, $power, $leaderMessage, $officerMessage, $memberMessage, $memberscount, $bank, $alliesMessage], $message);
                                $sender->sendMessage($message);
                            } else $sender->sendMessage(Utils::getMessage($sender, "MUST_BE_IN_FACTION"));
                        }
                        return true;
                    case "war":
                    case "wars":
                        if (Utils::getIntoConfig("war_system") === true) {
                            if (isset($args[1])) {
                                switch ($args[1]) {
                                    case "add":
                                    case "invite":
                                        if (FactionsAPI::isInFaction($sender->getName())) {
                                            if (FactionsAPI::getRank($sender->getName()) === "Leader" or FactionsAPI::getRank($sender->getName()) === "Officer") {
                                                if (isset($args[2])) {
                                                    if (FactionsAPI::existsFaction($args[2])) {
                                                        $faction1 = FactionsAPI::getFaction($sender->getName());
                                                        $faction2 = $args[2];
                                                        FactionsAPI::sendWarsInvitation($faction2, $faction1);
                                                        $sender->sendMessage(Utils::getMessage($sender, "WARS_INVITE_SUCCESS", array($faction2)));
                                                    } else $sender->sendMessage(Utils::getMessage($sender, "FACTION_NOT_EXIST"));
                                                } else $sender->sendMessage(Utils::getMessage($sender, "WARS_ADD_USAGE"));
                                            } else $sender->sendMessage(Utils::getMessage($sender, "ONLY_LEADER_OR_OFFICER"));
                                        } else $sender->sendMessage(Utils::getMessage($sender, "MUST_BE_IN_FACTION"));
                                        return true;
                                    case "accept":
                                        if (FactionsAPI::isInFaction($sender->getName())) {
                                            if (FactionsAPI::getRank($sender->getName()) === "Leader") {
                                                $faction = FactionsAPI::getFaction($sender->getName());

                                                if (isset(FactionsAPI::$Warsinvitation[$faction])) {
                                                    $time = FactionsAPI::$Warsinvitation[$faction][1];

                                                    if (time() < $time) {
                                                        FactionsAPI::acceptWarsInvitation($faction);
                                                    } else {
                                                        $sender->sendMessage(Utils::getMessage($sender, "WARS_REQUEST_EXPIRE"));
                                                        unset(FactionsAPI::$Warsinvitation[$faction]);
                                                    }
                                                } else $sender->sendMessage(Utils::getMessage($sender, "DONT_HAVE_WARS_REQUEST"));
                                            } else $sender->sendMessage(Utils::getMessage($sender, "ONLY_LEADER"));
                                        } else $sender->sendMessage(Utils::getMessage($sender, "MUST_BE_IN_FACTION"));
                                        return true;
                                    case "deny":
                                        if (FactionsAPI::isInFaction($sender->getName())) {
                                            if (FactionsAPI::getRank($sender->getName()) === "Leader") {
                                                $faction = FactionsAPI::getFaction($sender->getName());

                                                if (isset(FactionsAPI::$Warsinvitation[$faction])) {
                                                    $time = FactionsAPI::$Warsinvitation[$faction][1];

                                                    if (time() < $time) {
                                                        FactionsAPI::denyWarsInvitation($faction);
                                                        $sender->sendMessage(Utils::getMessage($sender, "WARS_DENY_SECCESS"));
                                                    } else {
                                                        $sender->sendMessage(Utils::getMessage($sender, "WARS_REQUEST_EXPIRE"));
                                                        unset(FactionsAPI::$Warsinvitation[$faction]);
                                                    }
                                                } else $sender->sendMessage(Utils::getMessage($sender, "DONT_HAVE_WARS_REQUEST"));
                                            } else $sender->sendMessage(Utils::getMessage($sender, "ONLY_LEADER"));
                                        } else $sender->sendMessage(Utils::getMessage($sender, "MUST_BE_IN_FACTION"));
                                        return true;
                                    default:
                                        $sender->sendMessage(Utils::getMessage($sender, "WARS_USAGE"));
                                        return true;
                                }
                            } else $sender->sendMessage(Utils::getMessage($sender, "WARS_USAGE"));
                        } else $sender->sendMessage(Utils::getMessage($sender, "WARS_DISABLED"));
                        return true;
                    case "who":
                        if (isset($args[1])) {
                            if (FactionsAPI::isInFaction($args[1])) {
                                $faction = FactionsAPI::getFaction($args[1]);
                                $power = FactionsAPI::getPower($faction);
                                $leader = FactionsAPI::getLeader($faction);
                                if (Server::getInstance()->getPlayerExact($leader)) {
                                    $leaderMessage = "§a{$leader}";
                                } else $leaderMessage = "§c{$leader}";


                                $officers = FactionsAPI::getOfficers($faction);
                                $officerMessage = "";
                                foreach ($officers as $officer) {
                                    if (Server::getInstance()->getPlayerExact($officer)) {
                                        $officerMessage .= "§a{$officer}§f, ";
                                    } else $officerMessage .= "§c{$officer}§f, ";
                                }
                                if ($officerMessage === "") $officerMessage = "§cNone";

                                $members = FactionsAPI::getMembers($faction);
                                $memberMessage = "";
                                foreach ($members as $member) {
                                    if (Server::getInstance()->getPlayerExact($member)) {
                                        $memberMessage .= "§a{$member}§f, ";
                                    } else $memberMessage .= "§c{$member}§f, ";
                                }
                                if ($memberMessage === "") $memberMessage = "§cNone";
                                $memberscount = count(FactionsAPI::getAllPlayers($faction));
                                $bank = FactionsAPI::getMoney($faction);
                                $allies = FactionsAPI::getAllies($faction);
                                $alliesMessage = implode(", ", $allies);
                                if (empty($allies)) $alliesMessage = "§cNone";
                                $sender->sendMessage(Utils::getMessage($sender, "FACTION_INFO_HEADER", array($faction)));
                                $message = Utils::getMessage($sender, "FACTON_INFO_CONTENT");
                                $message = str_replace(["{faction}", "{power}", "{leader}", "{officers}", "{members}", "{memberscount}", "{bank}", "{allies}"], [$faction, $power, $leaderMessage, $officerMessage, $memberMessage, $memberscount, $bank, $alliesMessage], $message);
                                $sender->sendMessage($message);
                            } else $sender->sendMessage(Utils::getMessage($sender, "NOT_IN_FACTION"));
                        } else $sender->sendMessage(Utils::getMessage($sender, "WHO_USAGE"));
                        break;
                    case "sethome":
                        if (FactionsAPI::isInFaction($sender->getName())) {
                            if (FactionsAPI::getRank($sender->getName()) === "Leader" or FactionsAPI::getRank($sender->getName()) === "Officer") {
                                if (!FactionsAPI::existsHome(FactionsAPI::getFaction($sender->getName()))) {
                                    if (in_array($sender->getWorld()->getFolderName(), Utils::getIntoConfig("faction_worlds"))) {
                                        $faction = FactionsAPI::getFaction($sender->getName());
                                        FactionsAPI::createHome($faction, $sender->getPosition());
                                        $sender->sendMessage(Utils::getMessage($sender, "HOME_SET"));
                                    } else $sender->sendMessage(Utils::getMessage($sender, "NOT_FACTION_WORLD"));
                                } else $sender->sendMessage(Utils::getMessage($sender, "ALREADY_HAVE_HOME"));
                            } else $sender->sendMessage(Utils::getMessage($sender, "ONLY_LEADER_OR_OFFICER"));
                        } else $sender->sendMessage(Utils::getMessage($sender, "MUST_BE_IN_FACTION"));
                        return true;
                    case "delhome":
                        if (FactionsAPI::isInFaction($sender->getName())) {
                            if (FactionsAPI::getRank($sender->getName()) === "Leader" or FactionsAPI::getRank($sender->getName()) === "Officer") {
                                if (FactionsAPI::existsHome(FactionsAPI::getFaction($sender->getName()))) {
                                    $faction = FactionsAPI::getFaction($sender->getName());
                                    FactionsAPI::deleteHome($faction);
                                    $sender->sendMessage(Utils::getMessage($sender, "HOME_DELETE"));
                                } else $sender->sendMessage(Utils::getMessage($sender, "NOT_HAVE_HOME"));
                            } else $sender->sendMessage(Utils::getMessage($sender, "ONLY_LEADER_OR_OFFICER"));
                        } else $sender->sendMessage(Utils::getMessage($sender, "MUST_BE_IN_FACTION"));
                        return true;
                    case "home":
                        if (FactionsAPI::isInFaction($sender->getName())) {
                            if (FactionsAPI::existsHome(FactionsAPI::getFaction($sender->getName()))) {
                                $faction = FactionsAPI::getFaction($sender->getName());
                                $sender->teleport(FactionsAPI::getHome($faction));
                                $sender->sendMessage(Utils::getMessage($sender, "HOME_TELEPORTED"));
                            } else $sender->sendMessage(Utils::getMessage($sender, "NOT_HAVE_HOME"));
                        } else $sender->sendMessage(Utils::getMessage($sender, "MUST_BE_IN_FACTION"));
                        return true;
                    case 'claim':
                        if (FactionsAPI::isInFaction($sender->getName())) {
                            if (FactionsAPI::getRank($sender->getName()) === "Leader" or FactionsAPI::getRank($sender->getName()) === "Officer") {
                                if (in_array($sender->getWorld()->getFolderName(), Utils::getIntoConfig("faction_worlds"))) {
                                    $pos = $sender->getPosition()->asVector3();
                                    $chunkX = $pos->getFloorX() >> Chunk::COORD_BIT_SIZE;
                                    $chunkZ = $pos->getFloorZ() >> Chunk::COORD_BIT_SIZE;
                                    if (!FactionsAPI::isInClaim($sender->getWorld(), $chunkX, $chunkZ)) {
                                        $faction = FactionsAPI::getFaction($sender->getName());
                                        $claimCount = FactionsAPI::getClaimCount($faction);
                                        if ($claimCount > 0) {
                                            if (Utils::getIntoConfig("adjacent_claims")) {
                                                if (!FactionsAPI::isChunkNextToClaim($sender, $faction)) {
                                                    $sender->sendMessage(Utils::getMessage($sender, "NOT_ADJACENT"));
                                                    return false;
                                                }
                                            }
                                        }
                                        $claimMode = Utils::getIntoConfig("claim-mode");
                                        if ($claimMode === "CUSTOM") {
                                            if ($claimCount < count(Utils::getIntoConfig("custom_claims"))) {
                                                $powerNeeded = (int)Utils::getIntoConfig("custom_claims")[$claimCount];
                                                if (FactionsAPI::getPower($faction) >= $powerNeeded) {
                                                    FactionsAPI::claimChunk($sender, $faction);
                                                    $sender->sendMessage(Utils::getMessage($sender, "CLAIM_SUCCESS"));
                                                } else $sender->sendMessage(Utils::getMessage($sender, "NOT_ENOUGHT_POWER", array($powerNeeded)));
                                            } else $sender->sendMessage(Utils::getMessage($sender, "MAX_CLAIM"));
                                        } else {
                                            if ($claimCount < (int)Utils::getIntoConfig("max_claims")) {
                                                if ($claimMode === "ADDITIVE") $powerNeeded = (int)Utils::getIntoConfig("starting_claim_price") + (Utils::getIntoConfig("factor") * $claimCount);
                                                else $powerNeeded = (int)Utils::getIntoConfig("starting_claim_price") * (Utils::getIntoConfig("factor") ** $claimCount);
                                                if (FactionsAPI::getPower($faction) >= (int)$powerNeeded) {
                                                    FactionsAPI::claimChunk($sender, $faction);
                                                    $sender->sendMessage(Utils::getMessage($sender, "CLAIM_SUCCESS"));
                                                } else $sender->sendMessage(Utils::getMessage($sender, "NOT_ENOUGHT_POWER", array($powerNeeded)));
                                            } else $sender->sendMessage(Utils::getMessage($sender, "MAX_CLAIM"));
                                        }
                                    } else $sender->sendMessage(Utils::getMessage($sender, "ALREADY_CLAIMED", array(FactionsAPI::getFactionClaim($sender->getWorld(), $chunkX, $chunkZ))));
                                } else $sender->sendMessage(Utils::getMessage($sender, "NOT_FACTION_WORLD"));
                            } else $sender->sendMessage(Utils::getMessage($sender, "ONLY_LEADER_OR_OFFICER"));
                        } else $sender->sendMessage(Utils::getMessage($sender, "MUST_BE_IN_FACTION"));
                        return true;
                    case "unclaim":
                        if (FactionsAPI::isInFaction($sender->getName())) {
                            if (FactionsAPI::getRank($sender->getName()) === "Leader" or FactionsAPI::getRank($sender->getName()) === "Officer") {
                                if (in_array($sender->getWorld()->getFolderName(), Utils::getIntoConfig("faction_worlds"))) {
                                    $pos = $sender->getPosition()->asVector3();
                                    $chunkX = $pos->getFloorX() >> Chunk::COORD_BIT_SIZE;
                                    $chunkZ = $pos->getFloorZ() >> Chunk::COORD_BIT_SIZE;
                                    if (FactionsAPI::isInClaim($sender->getWorld(), $chunkX, $chunkZ)) {
                                        $faction = FactionsAPI::getFaction($sender->getName());
                                        if (FactionsAPI::getFactionClaim($sender->getWorld(), $chunkX, $chunkZ) === $faction) {
                                            FactionsAPI::deleteClaim($sender, $faction);
                                            $sender->sendMessage(Utils::getMessage($sender, "UNCLAIM_SUCCESS"));
                                        } else $sender->sendMessage(Utils::getMessage($sender, "NOT_CLAIM_BY_YOUR_FACTION"));
                                    } else $sender->sendMessage(Utils::getMessage($sender, "NOT_CLAIM_BY_YOUR_FACTION"));
                                } else $sender->sendMessage(Utils::getMessage($sender, "NOT_FACTION_WORLD"));
                            } else $sender->sendMessage(Utils::getMessage($sender, "ONLY_LEADER_OR_OFFICER"));
                        } else $sender->sendMessage(Utils::getMessage($sender, "MUST_BE_IN_FACTION"));
                        return true;
                    case "leave":
                        if (FactionsAPI::isInFaction($sender->getName())) {
                            if (FactionsAPI::getRank($sender->getName()) !== "Leader") {
                                FactionsAPI::leaveFaction($sender);
                                $sender->sendMessage(Utils::getMessage($sender, "LEAVE_SUCCESS"));
                            } else $sender->sendMessage(Utils::getMessage($sender, "LEADER_CANNOT_LEAVE"));
                        } else $sender->sendMessage(Utils::getMessage($sender, "MUST_BE_IN_FACTION"));
                        return true;
                    case "kick":
                        if (isset($args[1])) {
                            if (FactionsAPI::isInFaction($sender->getName())) {
                                if (FactionsAPI::getRank($sender->getName()) === "Leader" or FactionsAPI::getRank($sender->getName()) === "Officer") {
                                    $faction = FactionsAPI::getFaction($sender->getName());
                                    if (FactionsAPI::getFaction($args[1]) === $faction) {
                                        if (FactionsAPI::getRank($args[1]) !== "Leader" and FactionsAPI::getRank($args[1]) !== FactionsAPI::getRank($sender->getName())) {
                                            FactionsAPI::kickFaction($args[1]);
                                            $sender->sendMessage(Utils::getMessage($sender, "KICK_SUCCESS", array($args[1])));
                                        } else $sender->sendMessage(Utils::getMessage($sender, "CANNOT_KICK_PLAYER"));
                                    } else $sender->sendMessage(Utils::getMessage($sender, "PLAYER_NOT_IN_YOUR_FACTION"));
                                } else $sender->sendMessage(Utils::getMessage($sender, "ONLY_LEADER_OR_OFFICER"));
                            } else $sender->sendMessage(Utils::getMessage($sender, "MUST_BE_IN_FACTION"));
                        } else $sender->sendMessage(Utils::getMessage($sender, "KICK_USAGE"));
                        return true;
                    case "promote":
                        if (isset($args[1])) {
                            if (FactionsAPI::isInFaction($sender->getName())) {
                                if (FactionsAPI::getRank($sender->getName()) === "Leader") {
                                    $faction = FactionsAPI::getFaction($sender->getName());
                                    if (FactionsAPI::getFaction($args[1]) === $faction) {
                                        if (FactionsAPI::getRank($args[1]) === "Member") {
                                            FactionsAPI::promoteFaction($args[1]);
                                            $sender->sendMessage(Utils::getMessage($sender, "PROMOTE_SUCCESS", array($args[1])));
                                        } else $sender->sendMessage(Utils::getMessage($sender, "PLAYER_ALREADY_OFFICER"));
                                    } else $sender->sendMessage(Utils::getMessage($sender, "PLAYER_NOT_IN_YOUR_FACTION", array($args[1])));
                                } else $sender->sendMessage(Utils::getMessage($sender, "ONLY_LEADER"));
                            } else $sender->sendMessage(Utils::getMessage($sender, "MUST_BE_IN_FACTION"));
                        } else $sender->sendMessage(Utils::getMessage($sender, "PROMOTE_USAGE"));
                        return true;
                    case "demote":
                        if (isset($args[1])) {
                            if (FactionsAPI::isInFaction($sender->getName())) {
                                if (FactionsAPI::getRank($sender->getName()) === "Leader") {
                                    $faction = FactionsAPI::getFaction($sender->getName());
                                    if (FactionsAPI::getFaction($args[1]) === $faction) {
                                        if (FactionsAPI::getRank($args[1]) === "Officer") {
                                            FactionsAPI::demoteFaction($args[1]);
                                            $sender->sendMessage(Utils::getMessage($sender, "DEMOTE_SUCCESS", array($args[1])));
                                        } else $sender->sendMessage(Utils::getMessage($sender, "PLAYER_ALREADY_MEMBER"));
                                    } else $sender->sendMessage(Utils::getMessage($sender, "PLAYER_NOT_IN_YOUR_FACTION", array($args[1])));
                                } else $sender->sendMessage(Utils::getMessage($sender, "ONLY_LEADER"));
                            } else $sender->sendMessage(Utils::getMessage($sender, "MUST_BE_IN_FACTION"));
                        } else $sender->sendMessage(Utils::getMessage($sender, "DEMOTE_USAGE"));
                        return true;
                    case "invite":
                        if (isset($args[1])) {
                            if (FactionsAPI::isInFaction($sender->getName())) {
                                if (FactionsAPI::getRank($sender->getName()) === "Leader" or FactionsAPI::getRank($sender->getName()) === "Officer") {
                                    if (Server::getInstance()->getPlayerExact($args[1])) {
                                        $target = Server::getInstance()->getPlayerExact($args[1]);
                                        if ($target instanceof Player) {
                                            if (!FactionsAPI::isInFaction($target->getName())) {
                                                $faction = FactionsAPI::getFaction($sender->getName());
                                                FactionsAPI::sendInvitation($target, $faction);
                                                $target->sendMessage(Utils::getMessage($target, "INVITE_SUCCESS_TARGET", array($faction)));
                                                $sender->sendMessage(Utils::getMessage($sender, "INVITE_SUCCESS", array($target->getName())));
                                            } else $sender->sendMessage(Utils::getMessage($sender, "PLAYER_ALREADY_IN_FACTION"));
                                        } else $sender->sendMessage(Utils::getMessage($sender, "PLAYER_NOT_ONLINE"));
                                    } else $sender->sendMessage(Utils::getMessage($sender, "PLAYER_NOT_ONLINE"));
                                } else $sender->sendMessage(Utils::getMessage($sender, "ONLY_LEADER_OR_OFFICER"));
                            } else $sender->sendMessage(Utils::getMessage($sender, "MUST_BE_IN_FACTION"));
                        } else $sender->sendMessage(Utils::getMessage($sender, "INVITE_USAGE"));
                        return true;
                    case "accept":
                        if (!FactionsAPI::isInFaction($sender->getName())) {
                            if (isset(FactionsAPI::$invitation[$sender->getName()])) {
                                $timer = FactionsAPI::$invitationTimeout[$sender->getName()];
                                $timer = $timer - time();
                                if ($timer > 0) {
                                    $faction = FactionsAPI::$invitation[$sender->getName()];
                                    if (FactionsAPI::existsFaction($faction)) {
                                        if (count(FactionsAPI::getAllPlayers($faction)) < (int)Utils::getIntoConfig("faction_max_members")) {
                                            FactionsAPI::acceptInvitation($sender);
                                            $sender->sendMessage(Utils::getMessage($sender, "ACCEPT_SUCCESS", array($faction)));
                                        } else $sender->sendMessage(Utils::getMessage($sender, "FACTION_FULL"));
                                    } else $sender->sendMessage(Utils::getMessage($sender, "FACTION_NOT_EXIST"));
                                } else $sender->sendMessage(Utils::getMessage($sender, "INVITATION_EXPIRED"));
                            } else $sender->sendMessage(Utils::getMessage($sender, "DONT_HAVE_INVITATION"));
                        } else $sender->sendMessage(Utils::getMessage($sender, "ALREADY_IN_FACTION"));
                    return true;
                    case "deny":
                        if (!FactionsAPI::isInFaction($sender->getName())) {
                            if (isset(FactionsAPI::$invitation[$sender->getName()])) {
                                $timer = FactionsAPI::$invitationTimeout[$sender->getName()];
                                $timer = $timer - time();
                                if ($timer > 0) {
                                    $faction = FactionsAPI::$invitation[$sender->getName()];
                                    FactionsAPI::denyInvitation($sender);
                                    $sender->sendMessage(Utils::getMessage($sender, "DENY_SUCCESS", array($faction)));
                                } else $sender->sendMessage(Utils::getMessage($sender, "INVITATION_EXPIRED"));
                            } else $sender->sendMessage(Utils::getMessage($sender, "DONT_HAVE_INVITATION"));
                        } else $sender->sendMessage(Utils::getMessage($sender, "ALREADY_IN_FACTION"));
                        return true;
                    case "top":
                        if(isset($args[1])) {
                            FactionsAPI::sendFactionTop($sender, (int)$args[1]);
                        } else FactionsAPI::sendFactionTop($sender);
                        return true;
                    case "transfer":
                    case "leader":
                    if (isset($args[1])) {
                        if (FactionsAPI::isInFaction($sender->getName())) {
                            if (FactionsAPI::getRank($sender->getName()) === "Leader") {
                                $faction = FactionsAPI::getFaction($sender->getName());
                                if (FactionsAPI::getFaction($args[1]) === $faction) {
                                    if (strtolower($args[1]) !== strtolower($sender->getName())) {
                                        FactionsAPI::demoteFaction($sender->getName());
                                        FactionsAPI::transferFaction($args[1]);
                                        $sender->sendMessage(Utils::getMessage($sender, "TRANSFER_SUCCESS", array($args[1])));
                                    } else $sender->sendMessage(Utils::getMessage($sender, "CANNOT_TRANFER_YOURSELF"));
                                } else $sender->sendMessage(Utils::getMessage($sender, "PLAYER_NOT_IN_YOUR_FACTION", array($args[1])));
                            } else $sender->sendMessage(Utils::getMessage($sender, "ONLY_LEADER"));
                        } else $sender->sendMessage(Utils::getMessage($sender, "MUST_BE_IN_FACTION"));
                    } else $sender->sendMessage(Utils::getMessage($sender, "TRANSFER_USAGE"));
                        return true;
                    case "allies":
                    case "ally":
                        if (isset($args[1])) {
                            switch ($args[1]) {
                                case "add":
                                    if (FactionsAPI::isInFaction($sender->getName())) {
                                        if (FactionsAPI::getRank($sender->getName()) === "Leader") {
                                            if (isset($args[2])) {
                                                if (FactionsAPI::existsFaction($args[2])) {
                                                    $faction1 = FactionsAPI::getFaction($sender->getName());
                                                    $faction2 = $args[2];
                                                    FactionsAPI::sendAlliesInvitation($faction2, $faction1);
                                                    $sender->sendMessage(Utils::getMessage($sender, "ALLIES_INVITE_SUCCESS", array($faction2)));
                                                } else $sender->sendMessage(Utils::getMessage($sender, "FACTION_NOT_EXIST"));
                                            } else $sender->sendMessage(Utils::getMessage($sender, "ALLIES_ADD_USAGE"));
                                        } else $sender->sendMessage(Utils::getMessage($sender, "ONLY_LEADER"));
                                    } else $sender->sendMessage(Utils::getMessage($sender, "MUST_BE_IN_FACTION"));
                                    return  true;
                                case "remove":
                                    if (FactionsAPI::isInFaction($sender->getName())) {
                                        if (FactionsAPI::getRank($sender->getName()) === "Leader") {
                                            if (isset($args[2])) {
                                                if (FactionsAPI::existsFaction($args[2])) {
                                                    $faction1 = FactionsAPI::getFaction($sender->getName());
                                                    $faction2 = $args[2];
                                                    if (FactionsAPI::areAllies($faction1, $faction2)) {
                                                        FactionsAPI::removeAllies($faction1, $faction2);
                                                    } else $sender->sendMessage(Utils::getMessage($sender, "NOT_ALLIES", array($faction2)));
                                                } else $sender->sendMessage(Utils::getMessage($sender, "FACTION_NOT_EXIST"));
                                            } else $sender->sendMessage(Utils::getMessage($sender, "ALLIES_REMOVE_USAGE"));
                                        } else $sender->sendMessage(Utils::getMessage($sender, "ONLY_LEADER"));
                                    } else $sender->sendMessage(Utils::getMessage($sender, "MUST_BE_IN_FACTION"));
                                    return  true;
                                case "list":
                                    if (FactionsAPI::isInFaction($sender->getName())) {
                                        $faction = FactionsAPI::getFaction($sender->getName());
                                        $sender->sendMessage(Utils::getMessage($sender, "ALLIES_LIST_HEADER"));
                                        $message = Utils::getMessage($sender, "ALLIES_LIST");
                                        $allies = FactionsAPI::getAllies($faction);
                                        $allieMessage = implode(", ", FactionsAPI::getAllies($faction));
                                        if (empty($allies)) $allieMessage = "§cNone";
                                        $message = str_replace("{allies}", $allieMessage, $message);
                                        $sender->sendMessage($message);
                                    } else $sender->sendMessage(Utils::getMessage($sender, "MUST_BE_IN_FACTION"));
                                    return  true;
                                case "accept":
                                    if (FactionsAPI::isInFaction($sender->getName())) {
                                        if (FactionsAPI::getRank($sender->getName()) === "Leader") {
                                            $faction = FactionsAPI::getFaction($sender->getName());
                                            if (isset(FactionsAPI::$Alliesinvitation[$faction])) {
                                                $faction2 = FactionsAPI::$Alliesinvitation[$faction];
                                                $timer = FactionsAPI::$AlliesinvitationTimeout[$faction];
                                                $timer = $timer - time();
                                                if ($timer > 0) {
                                                    if (FactionsAPI::getAlliesCount($faction) < (int)Utils::getIntoConfig("faction_max_allies")) {
                                                        if (FactionsAPI::getAlliesCount($faction2) < (int)Utils::getIntoConfig("faction_max_allies")) {
                                                            FactionsAPI::acceptAlliesInvitation($faction);
                                                        } else $sender->sendMessage(Utils::getMessage($sender, "FACTION_MAX_ALLIES", array($faction2)));
                                                    } else $sender->sendMessage(Utils::getMessage($sender, "YOUR_FACTION_MAX_ALLIES"));
                                                } else $sender->sendMessage(Utils::getMessage($sender, "ALLIES_REQUEST_EXPIRE"));
                                            } else $sender->sendMessage(Utils::getMessage($sender, "DONT_HAVE_ALLIES_REQUEST"));
                                        } else $sender->sendMessage(Utils::getMessage($sender, "ONLY_LEADER"));
                                    } else $sender->sendMessage(Utils::getMessage($sender, "MUST_BE_IN_FACTION"));
                                    return  true;
                                case "deny":
                                    if (FactionsAPI::isInFaction($sender->getName())) {
                                        if (FactionsAPI::getRank($sender->getName()) === "Leader") {
                                            $faction = FactionsAPI::getFaction($sender->getName());
                                            if (isset(FactionsAPI::$Alliesinvitation[$faction])) {
                                                $timer = FactionsAPI::$AlliesinvitationTimeout[$faction];
                                                $timer = $timer - time();
                                                if ($timer > 0) {
                                                    $sender->sendMessage(Utils::getMessage($sender, "ALLIES_DENY_SUCCESS", array(FactionsAPI::$Alliesinvitation[$faction])));
                                                    FactionsAPI::denyAlliesInvitation($faction);
                                                } else $sender->sendMessage(Utils::getMessage($sender, "ALLIES_REQUEST_EXPIRE"));
                                            } else $sender->sendMessage(Utils::getMessage($sender, "DONT_HAVE_ALLIES_REQUEST"));
                                        } else $sender->sendMessage(Utils::getMessage($sender, "ONLY_LEADER"));
                                    } else $sender->sendMessage(Utils::getMessage($sender, "MUST_BE_IN_FACTION"));
                                    return  true;
                                default:
                                    $sender->sendMessage(Utils::getMessage($sender, "ALLIES_USAGE"));
                                    return true;
                            }
                        } else $sender->sendMessage(Utils::getMessage($sender, "ALLIES_USAGE"));
                        return true;
                    case "chat":
                        if (FactionsAPI::isInFaction($sender->getName())) {
                            if (isset($args[1])) {
                                switch (strtolower($args[1])) {
                                    case "faction":
                                    case "fac":
                                    case "f":
                                    if (isset(FactionsAPI::$chat[$sender->getName()])) {
                                        if (FactionsAPI::$chat[$sender->getName()] !== "FACTION") {
                                            FactionsAPI::$chat[$sender->getName()] = "FACTION";
                                            $sender->sendMessage(Utils::getMessage($sender, "CHAT_SUCCESS", array("FACTION")));
                                        } else $sender->sendMessage(Utils::getMessage($sender, "ALREADY_THIS_CHAT", array("FACTION")));
                                    } else {
                                        FactionsAPI::$chat[$sender->getName()] = "FACTION";
                                        $sender->sendMessage(Utils::getMessage($sender, "CHAT_SUCCESS", array("FACTION")));
                                    }
                                        break;
                                    case "alliance":
                                    case "ally":
                                    case "a":
                                    if (isset(FactionsAPI::$chat[$sender->getName()])) {
                                        if (FactionsAPI::$chat[$sender->getName()] !== "ALLIANCE") {
                                            FactionsAPI::$chat[$sender->getName()] = "ALLIANCE";
                                            $sender->sendMessage(Utils::getMessage($sender, "CHAT_SUCCESS", array("ALLIANCE")));
                                        } else $sender->sendMessage(Utils::getMessage($sender, "ALREADY_THIS_CHAT", array("ALLIANCE")));
                                    } else {
                                        FactionsAPI::$chat[$sender->getName()] = "ALLIANCE";
                                        $sender->sendMessage(Utils::getMessage($sender, "CHAT_SUCCESS", array("ALLIANCE")));
                                    }
                                        break;
                                    case "global":
                                    case "g":
                                        if (isset(FactionsAPI::$chat[$sender->getName()])) {
                                            unset(FactionsAPI::$chat[$sender->getName()]);
                                            $sender->sendMessage(Utils::getMessage($sender, "CHAT_SUCCESS", array("GLOBAL")));
                                        } else $sender->sendMessage(Utils::getMessage($sender, "ALREADY_THIS_CHAT", array("GLOBAL")));
                                        break;
                                    default:
                                        $sender->sendMessage(Utils::getMessage($sender, "CHAT_USAGE"));
                                        break;
                                }
                            } else $sender->sendMessage(Utils::getMessage($sender, "CHAT_USAGE"));
                        } else $sender->sendMessage(Utils::getMessage($sender, "MUST_BE_IN_FACTION"));
                        return true;
                    case "bank":
                        if (Utils::getIntoConfig("economy_system") === true) {
                            if (isset($args[1])) {
                                if (FactionsAPI::isInFaction($sender->getName())) {
                                    switch ($args[1]) {
                                        case "deposit":
                                        case "d":
                                            if (isset($args[2])) {
                                                $faction = FactionsAPI::getFaction($sender->getName());
                                                $money = (int)$args[2];
                                                if ($money > 0) {
                                                    if (Main::getEconomy()->myMoney($sender) >= $money) {
                                                        FactionsAPI::addMoney($faction, $money);
                                                        Main::getEconomy()->reduceMoney($sender, $money);
                                                        $sender->sendMessage(Utils::getMessage($sender, "BANK_DEPOST_SUCCESS", array($money)));
                                                    } else $sender->sendMessage(Utils::getMessage($sender, "NOT_ENOUGH_MONEY"));
                                                } else $sender->sendMessage(Utils::getMessage($sender, "ENTER_VALID_NUMBER"));
                                            } else $sender->sendMessage(Utils::getMessage($sender, "BANK_DEPOSIT_USAGE"));
                                            break;
                                        case "withdraw":
                                        case "w":
                                            if (FactionsAPI::getRank($sender->getName()) !== "Member") {
                                                if (isset($args[2])) {
                                                    $faction = FactionsAPI::getFaction($sender->getName());
                                                    $money = (int)$args[2];
                                                    if ($money > 0) {
                                                        if (FactionsAPI::getMoney($faction) >= $money) {
                                                            FactionsAPI::removeMoney($faction, $money);
                                                            Main::getEconomy()->addMoney($sender, $money);
                                                            $sender->sendMessage(Utils::getMessage($sender, "BANK_WITHDRAW_SUCCESS", array($money)));
                                                        } else $sender->sendMessage(Utils::getMessage($sender, "FACTION_NOT_ENOUGH_MONEY"));
                                                    } else $sender->sendMessage(Utils::getMessage($sender, "ENTER_VALID_NUMBER"));
                                                } else $sender->sendMessage(Utils::getMessage($sender, "BANK_WITHDRAW_USAGE"));
                                            } else $sender->sendMessage(Utils::getMessage($sender, "ONLY_LEADER_OR_OFFICER"));
                                            break;
                                        case "status":
                                        case "s":
                                            $faction = FactionsAPI::getFaction($sender->getName());
                                            $money = FactionsAPI::getMoney($faction);
                                            $sender->sendMessage(Utils::getMessage($sender, "BANK_STATUS", array($money)));
                                            break;
                                    }
                                } else $sender->sendMessage(Utils::getMessage($sender, "MUST_BE_IN_FACTION"));
                            } else $sender->sendMessage(Utils::getMessage($sender, "BANK_USAGE"));
                        } else $sender->sendMessage(Utils::getMessage($sender, "BANK_DISABLED"));
                        return true;
                    case "lang":
                        $langs = "";
                        foreach (Utils::getIntoLang("languages") as $key => $val) $langs .= ', ' . $key;
                        $langs = substr($langs, 2);
                        if (isset($args[1])) {
                            if (isset(Utils::getIntoLang("languages")[$args[1]])) {
                                FactionsAPI::setLanguages($sender, $args[1]);
                                $fullName = Utils::getIntoLang("languages-fullname")[$args[1]];
                                $sender->sendMessage(Utils::getMessage($sender, "LANG_CHANGE_SUCCESS", array($fullName)));
                            } else $sender->sendMessage(Utils::getMessage($sender, "LANG_NOT_EXIST"));
                        } else $sender->sendMessage(Utils::getMessage($sender, "LANG_USAGE", array($langs)));
                        return true;
                    case "map":
                        if (isset($args[1])) {
                            switch (strtolower($args[1])) {
                                case "on":
                                    if (!isset(FactionsAPI::$map[$sender->getName()])) {
                                        FactionsAPI::$map[$sender->getName()] = true;
                                        $sender->sendMessage(Utils::getMessage($sender, "MAP_ON_SUCCESS"));
                                    } else $sender->sendMessage(Utils::getMessage($sender, "MAP_ALREADY_ON"));
                                    break;
                                case "off":
                                    if (isset(FactionsAPI::$map[$sender->getName()])) {
                                        unset(FactionsAPI::$map[$sender->getName()]);
                                        $sender->sendMessage(Utils::getMessage($sender, "MAP_OFF_SUCCESS"));
                                    } else $sender->sendMessage(Utils::getMessage($sender, "MAP_ALREADY_OFF"));
                                    break;
                                default:
                                    $sender->sendMessage(Utils::getMessage($sender, "MAP_USAGE"));
                                    break;
                            }
                        } else $sender->sendMessage(implode(TextFormat::EOL, FactionsAPI::getMap($sender)));
                        return true;
                    case "border":
                        if (isset(FactionsAPI::$border[$sender->getName()])) {
                            unset(FactionsAPI::$border[$sender->getName()]);
                            $sender->sendMessage(Utils::getMessage($sender, "BORDER_DESACTIVATED"));
                        } else {
                            FactionsAPI::$border[$sender->getName()] = true;
                            $sender->sendMessage(Utils::getMessage($sender, "BORDER_ACTIVATED"));
                        }
                        return true;
                    case "here":
                        if (in_array($sender->getWorld()->getFolderName(), Utils::getIntoConfig("faction_worlds"))) {
                            $pos = $sender->getPosition()->asVector3();
                            $chunkX = $pos->getFloorX() >> Chunk::COORD_BIT_SIZE;
                            $chunkZ = $pos->getFloorZ() >> Chunk::COORD_BIT_SIZE;
                            if (FactionsAPI::isInClaim($sender->getWorld(), $chunkX, $chunkZ)) {
                                $faction = FactionsAPI::getFactionClaim($sender->getWorld(), $chunkX, $chunkZ);
                                $sender->sendMessage(Utils::getMessage($sender, "HERE_SUCCESS", array($faction, $faction)));
                            } else $sender->sendMessage(Utils::getMessage($sender, "HERE_NOT_CLAIMED"));
                        } else $sender->sendMessage(Utils::getMessage($sender, "NOT_FACTION_WORLD"));
                        return true;
                    case "admin":
                        if ($sender->hasPermission("simplefaction.admin")) {
                            if (isset($args[1])) {
                                switch ($args[1]) {
                                    case "addpower":
                                        if (isset($args[2]) and isset($args[3])) {
                                            if (FactionsAPI::existsFaction($args[2])) {
                                                if (is_numeric($args[3])) {
                                                    if ((int)$args[3] > 0) {
                                                        FactionsAPI::addPower($args[2], (int)$args[3]);
                                                        $sender->sendMessage(Utils::getMessage($sender, "ADMIN_ADDPOWER_SUCCESS", array((int)$args[3])));
                                                    } else $sender->sendMessage(Utils::getMessage($sender, "ENTER_VALID_NUMBER"));
                                                } else $sender->sendMessage(Utils::getMessage($sender, "ENTER_VALID_NUMBER"));
                                            } else $sender->sendMessage(Utils::getMessage($sender, "FACTION_NOT_EXIST"));
                                        } else $sender->sendMessage(Utils::getMessage($sender, "ADMIN_ADDPOWER_USAGE"));
                                        break;
                                    case "removepower":
                                        if (isset($args[2]) and isset($args[3])) {
                                            if (FactionsAPI::existsFaction($args[2])) {
                                                if (is_numeric($args[3])) {
                                                    if ((int)$args[3] > 0) {
                                                        FactionsAPI::removePower($args[2], (int)$args[3]);
                                                        $sender->sendMessage(Utils::getMessage($sender, "ADMIN_REMOVEPOWER_SUCCESS", array((int)$args[3])));
                                                    } else $sender->sendMessage(Utils::getMessage($sender, "ENTER_VALID_NUMBER"));
                                                } else $sender->sendMessage(Utils::getMessage($sender, "ENTER_VALID_NUMBER"));
                                            } else $sender->sendMessage(Utils::getMessage($sender, "FACTION_NOT_EXIST"));
                                        } else $sender->sendMessage(Utils::getMessage($sender, "ADMIN_REMOVEPOWER_USAGE"));
                                        break;
                                    case "setpower":
                                        if (isset($args[2]) and isset($args[3])) {
                                            if (FactionsAPI::existsFaction($args[2])) {
                                                if (is_numeric($args[3])) {
                                                    if ((int)$args[3] > 0) {
                                                        FactionsAPI::setPower($args[2], (int)$args[3]);
                                                        $sender->sendMessage(Utils::getMessage($sender, "ADMIN_SETPOWER_SUCCESS", array((int)$args[3])));
                                                    } else $sender->sendMessage(Utils::getMessage($sender, "ENTER_VALID_NUMBER"));
                                                } else $sender->sendMessage(Utils::getMessage($sender, "ENTER_VALID_NUMBER"));
                                            } else $sender->sendMessage(Utils::getMessage($sender, "FACTION_NOT_EXIST"));
                                        } else $sender->sendMessage(Utils::getMessage($sender, "ADMIN_SETPOWER_USAGE"));
                                        break;
                                    case "addmoney":
                                        if (isset($args[2]) and isset($args[3])) {
                                            if (FactionsAPI::existsFaction($args[2])) {
                                                if (is_numeric($args[3])) {
                                                    if ((int)$args[3] > 0) {
                                                        FactionsAPI::addMoney($args[2], (int)$args[3]);
                                                        $sender->sendMessage(Utils::getMessage($sender, "ADMIN_ADDMONEY_SUCCESS", array((int)$args[3])));
                                                    } else $sender->sendMessage(Utils::getMessage($sender, "ENTER_VALID_NUMBER"));
                                                } else $sender->sendMessage(Utils::getMessage($sender, "ENTER_VALID_NUMBER"));
                                            } else $sender->sendMessage(Utils::getMessage($sender, "FACTION_NOT_EXIST"));
                                        } else $sender->sendMessage(Utils::getMessage($sender, "ADMIN_ADDMONEY_USAGE"));
                                        break;
                                    case "removemoney":
                                        if (isset($args[2]) and isset($args[3])) {
                                            if (FactionsAPI::existsFaction($args[2])) {
                                                if (is_numeric($args[3])) {
                                                    if ((int)$args[3] > 0) {
                                                        FactionsAPI::removeMoney($args[2], (int)$args[3]);
                                                        $sender->sendMessage(Utils::getMessage($sender, "ADMIN_REMOVEMONEY_SUCCESS", array((int)$args[3])));
                                                    } else $sender->sendMessage(Utils::getMessage($sender, "ENTER_VALID_NUMBER"));
                                                } else $sender->sendMessage(Utils::getMessage($sender, "ENTER_VALID_NUMBER"));
                                            } else $sender->sendMessage(Utils::getMessage($sender, "FACTION_NOT_EXIST"));
                                        } else $sender->sendMessage(Utils::getMessage($sender, "ADMIN_REMOVEMONEY_USAGE"));
                                        break;
                                    case "setmoney":
                                        if (isset($args[2]) and isset($args[3])) {
                                            if (FactionsAPI::existsFaction($args[2])) {
                                                if (is_numeric($args[3])) {
                                                    if ((int)$args[3] > 0) {
                                                        FactionsAPI::setMoney($args[2], (int)$args[3]);
                                                        $sender->sendMessage(Utils::getMessage($sender, "ADMIN_SETMONEY_SUCCESS", array((int)$args[3])));
                                                    } else $sender->sendMessage(Utils::getMessage($sender, "ENTER_VALID_NUMBER"));
                                                } else $sender->sendMessage(Utils::getMessage($sender, "ENTER_VALID_NUMBER"));
                                            } else $sender->sendMessage(Utils::getMessage($sender, "FACTION_NOT_EXIST"));
                                        } else $sender->sendMessage(Utils::getMessage($sender, "ADMIN_SETMONEY_USAGE"));
                                        break;
                                    case "delete":
                                        if (isset($args[2])) {
                                            if (FactionsAPI::existsFaction($args[2])) {
                                                FactionsAPI::disbandFaction($sender, $args[2]);
                                                $sender->sendMessage(Utils::getMessage($sender, "ADMIN_DELETE_SUCCESS"));
                                            } else $sender->sendMessage(Utils::getMessage($sender, "FACTION_NOT_EXIST"));
                                        } else $sender->sendMessage(Utils::getMessage($sender, "ADMIN_DELETE_USAGE"));
                                        break;
                                    case "rename":
                                        if (isset($args[2]) and isset($args[3])) {
                                            if (ctype_alnum($args[3])) {
                                                if(strlen($args[3]) > (int)Utils::getIntoConfig("min_faction_name_lenght")) {
                                                    if (strlen($args[3]) < (int)Utils::getIntoConfig("max_faction_name_lenght")) {
                                                        if (!in_array(strtolower($args[1]), Utils::getIntoConfig("banned_names"))) {
                                                            if (FactionsAPI::existsExactlyFaction($args[2])) {
                                                                if (!FactionsAPI::existsFaction($args[3])) {
                                                                    FactionsAPI::renameFaction($args[2], $args[3]);
                                                                    $sender->sendMessage(Utils::getMessage($sender, "ADMIN_RENAME_SUCCESS", array($args[3])));
                                                                } else $sender->sendMessage(Utils::getMessage($sender, "FACTION_ALREADY_EXIST"));
                                                            } else $sender->sendMessage(Utils::getMessage($sender, "FACTION_NOT_EXIST"));
                                                        } else $sender->sendMessage(Utils::getMessage($sender, "FACTION_NAME_BANNED"));
                                                    } else $sender->sendMessage(Utils::getMessage($sender, "FACTION_NAME_TOO_LONG"));
                                                } else $sender->sendMessage(Utils::getMessage($sender, "FACTION_NAME_TOO_SHORT"));
                                            } else $sender->sendMessage(Utils::getMessage($sender, "INVALID_NAME"));
                                        } else $sender->sendMessage(Utils::getMessage($sender, "ADMIN_RENAME_USAGE"));
                                        break;
                                    case "unclaim":
                                        if (in_array($sender->getWorld()->getFolderName(), Utils::getIntoConfig("faction_worlds"))) {
                                            $pos = $sender->getPosition()->asVector3();
                                            $chunkX = $pos->getFloorX() >> Chunk::COORD_BIT_SIZE;
                                            $chunkZ = $pos->getFloorZ() >> Chunk::COORD_BIT_SIZE;
                                            if (FactionsAPI::isInClaim($sender->getWorld(), $chunkX, $chunkZ)) {
                                                $faction = FactionsAPI::getFactionClaim($sender->getWorld(), $chunkX, $chunkZ);
                                                FactionsAPI::deleteClaim($sender, $faction);
                                                $sender->sendMessage(Utils::getMessage($sender, "ADMIN_UNCLAIM_SUCCESS"));
                                            } else $sender->sendMessage(Utils::getMessage($sender, "ADMIN_NOT_IN_CLAIM"));
                                        } else $sender->sendMessage(Utils::getMessage($sender, "NOT_FACTION_WORLD"));
                                        break;
                                    case "home":
                                        if(isset($args[2])) {
                                            if(FactionsAPI::existsFaction($args[2])) {
                                                if (FactionsAPI::existsHome($args[2])) {
                                                    $sender->teleport(FactionsAPI::getHome($args[2]));
                                                    $sender->sendMessage(Utils::getMessage($sender, "ADMIN_HOME_TELEPORTED", [$args[2]]));
                                                } else $sender->sendMessage(Utils::getMessage($sender, "ADMIN_NOT_HAVE_HOME"));
                                            } else $sender->sendMessage(Utils::getMessage($sender, "FACTION_NOT_EXIST"));
                                        } else $sender->sendMessage(Utils::getMessage($sender, "ADMIN_HOME_USAGE"));
                                        break;
                                }
                            } else $sender->sendMessage(Utils::getMessage($sender, "ADMIN_USAGE"));
                        } else $sender->sendMessage(Utils::getMessage($sender, "ADMIN_NO_PERMISSION"));
                        break;
                    case "about":
                        $sender->sendMessage("§c§lPlugin created by Ayzrix.");
                        $sender->sendMessage("§4§lYoutube:§r§f Ayzrix");
                        $sender->sendMessage("§b§lTwitter:§r§f @Ayzrix");
                        $sender->sendMessage("§6§lDownload link:§r§f github.com/AyzrixYTB/SimpleFaction");
                        return true;
                    default:
                        $sender->sendMessage(Utils::getMessage($sender, "COMMAND_USAGE"));
                        return true;
                }
            } else $sender->sendMessage(Utils::getMessage($sender, "COMMAND_USAGE"));
        } else $sender->sendMessage(str_replace("{prefix}", Utils::getPrefix(), Utils::getIntoConfig("PLAYER_ONLY")));
        return true;
    }
}
