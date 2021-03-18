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
use pocketmine\command\CommandSender;
use pocketmine\command\PluginCommand;
use pocketmine\Player;
use pocketmine\Server;
use pocketmine\utils\TextFormat;

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
                        if(isset($args[1])) {
                            switch ($args[1]) {
                                case 2:
                                    $player->sendMessage(Utils::getMessage($player, "HELP_HEADER", array(2, 5)));
                                    $player->sendMessage(Utils::getMessage($player, "HELP_2", array(2, 5)));
                                    break;
                                case 3:
                                    $player->sendMessage(Utils::getMessage($player, "HELP_HEADER", array(3, 5)));
                                    $player->sendMessage(Utils::getMessage($player, "HELP_3", array(2, 5)));
                                    break;
                                case 4:
                                    $player->sendMessage(Utils::getMessage($player, "HELP_HEADER", array(4, 5)));
                                    $player->sendMessage(Utils::getMessage($player, "HELP_4", array(2, 5)));
                                    break;
                                case 5:
                                    $player->sendMessage(Utils::getMessage($player, "HELP_HEADER", array(5, 5)));
                                    $player->sendMessage(Utils::getMessage($player, "HELP_5", array(2, 5)));
                                    break;
                                default:
                                    $player->sendMessage(Utils::getMessage($player, "HELP_HEADER", array(1, 5)));
                                    $player->sendMessage(Utils::getMessage($player, "HELP_1", array(2, 5)));
                                    break;
                            }
                        } else {
                            $player->sendMessage(Utils::getMessage($player, "HELP_HEADER", array(1, 5)));
                            $player->sendMessage(Utils::getMessage($player, "HELP_1", array(2, 5)));
                        }
                        return true;
                    case "create":
                    case "make":
                        if (isset($args[1])) {
                            if (ctype_alnum($args[1])) {
                                if(strlen($args[1]) > (int)Utils::getIntoConfig("min_faction_name_lenght")) {
                                    if (strlen($args[1]) < (int)Utils::getIntoConfig("max_faction_name_lenght")) {
                                        if (!FactionsAPI::existsFaction($args[1])) {
                                            if (!FactionsAPI::isInFaction($player)) {
                                                $player->sendMessage(Utils::getMessage($player, "SUCCESSFULL_CREATED", array($args[1])));
                                                FactionsAPI::createFaction($player, $args[1]);
                                            } else $player->sendMessage(Utils::getMessage($player, "ALREADY_IN_FACTION"));
                                        } else $player->sendMessage(Utils::getMessage($player, "FACTION_ALREADY_EXIST"));
                                    } else $player->sendMessage(Utils::getMessage($player, "FACTION_NAME_TOO_LONG"));
                                } else $player->sendMessage(Utils::getMessage($player, "FACTION_NAME_TOO_SHORT"));
                            } else $player->sendMessage(Utils::getMessage($player, "INVALID_NAME"));
                        } else $player->sendMessage(Utils::getMessage($player, "CREATE_USAGE"));
                        return true;
                    case "delete":
                    case "del":
                    case "disband":
                        if (FactionsAPI::isInFaction($player)) {
                            if (FactionsAPI::getRank($player->getName()) === "Leader") {
                                $player->sendMessage(Utils::getMessage($player, "SUCCESSFULL_DISBAND", array(FactionsAPI::getFaction($player))));
                                FactionsAPI::disbandFaction($player, FactionsAPI::getFaction($player));
                            } else $player->sendMessage(Utils::getMessage($player, "ONLY_LEADER"));
                        } else $player->sendMessage(Utils::getMessage($player, "MUST_BE_IN_FACTION"));
                        return true;
                    case "info":
                        if (isset($args[1])) {
                            if (FactionsAPI::existsFaction($args[1])) {
                                $faction = $args[1];
                                $power = FactionsAPI::getPower($faction);
                                $leader = FactionsAPI::getLeader($faction);
                                if (Server::getInstance()->getPlayer($leader)) {
                                    $leaderMessage = "§a{$leader}";
                                } else $leaderMessage = "§c{$leader}";


                                $officers = FactionsAPI::getOfficers($faction);
                                $officerMessage = "";
                                foreach ($officers as $officer) {
                                    if (Server::getInstance()->getPlayer($officer)){
                                        $officerMessage .= "§a{$officer}§f, ";
                                    } else $officerMessage .= "§c{$officer}§f, ";
                                }
                                if($officerMessage === "") $officerMessage = "§cNone";

                                $members = FactionsAPI::getMembers($faction);
                                $memberMessage = "";
                                foreach ($members as $member) {
                                    if (Server::getInstance()->getPlayer($member)){
                                        $memberMessage .= "§a{$member}§f, ";
                                    } else $memberMessage .= "§c{$member}§f, ";
                                }
                                if($memberMessage === "") $memberMessage = "§cNone";
                                $memberscount = count(FactionsAPI::getAllPlayers($faction));
                                $bank = FactionsAPI::getMoney($faction);
                                $allies = FactionsAPI::getAllies($faction);
                                $alliesMessage = implode(", ", $allies);
                                if (empty($allies)) $alliesMessage = "§cNone";
                                $player->sendMessage(Utils::getMessage($player, "FACTION_INFO_HEADER", array($faction)));
                                $message = Utils::getMessage($player, "FACTON_INFO_CONTENT");
                                $message = str_replace(["{faction}", "{power}", "{leader}", "{officers}", "{members}", "{memberscount}", "{bank}", "{allies}"], [$faction, $power, $leaderMessage, $officerMessage, $memberMessage, $memberscount, $bank, $alliesMessage], $message);
                                $player->sendMessage($message);
                            } else $player->sendMessage(Utils::getMessage($player, "FACTION_NOT_EXIST"));
                        } else {
                            if (FactionsAPI::isInFaction($player)) {
                                $faction = FactionsAPI::getFaction($player);
                                $power = FactionsAPI::getPower($faction);
                                $leader = FactionsAPI::getLeader($faction);
                                if (Server::getInstance()->getPlayer($leader)) {
                                    $leaderMessage = "§a{$leader}";
                                } else $leaderMessage = "§c{$leader}";
                                $officers = FactionsAPI::getOfficers($faction);
                                $officerMessage = "";
                                foreach ($officers as $officer) {
                                    if (Server::getInstance()->getPlayer($officer)){
                                        $officerMessage .= "§a{$officer}§f, ";
                                    } else $officerMessage .= "§c{$officer}§f, ";
                                }
                                if($officerMessage === "") $officerMessage = "§cNone";

                                $members = FactionsAPI::getMembers($faction);
                                $memberMessage = "";
                                foreach ($members as $member) {
                                    if (Server::getInstance()->getPlayer($member)){
                                        $memberMessage .= "§a{$member}§f, ";
                                    } else $memberMessage .= "§c{$member}§f, ";
                                }
                                if($memberMessage === "") $memberMessage = "§cNone";
                                $memberscount = count(FactionsAPI::getAllPlayers($faction));
                                $bank = FactionsAPI::getMoney($faction);
                                $allies = FactionsAPI::getAllies($faction);
                                $alliesMessage = implode(", ", $allies);
                                if (empty($allies)) $alliesMessage = "§cNone";
                                $player->sendMessage(Utils::getMessage($player, "FACTION_INFO_HEADER", array($faction)));
                                $message = Utils::getMessage($player, "FACTON_INFO_CONTENT");
                                $message = str_replace(["{faction}", "{power}", "{leader}", "{officers}", "{members}", "{memberscount}", "{bank}", "{allies}"], [$faction, $power, $leaderMessage, $officerMessage, $memberMessage, $memberscount, $bank, $alliesMessage], $message);
                                $player->sendMessage($message);
                            } else $player->sendMessage(Utils::getMessage($player, "MUST_BE_IN_FACTION"));
                        }
                        return true;
                    case "sethome":
                        if (FactionsAPI::isInFaction($player)) {
                            if (FactionsAPI::getRank($player->getName()) === "Leader" or FactionsAPI::getRank($player->getName()) === "Officer") {
                                if (!FactionsAPI::existsHome(FactionsAPI::getFaction($player))) {
                                    if (in_array($player->getLevel()->getFolderName(), Utils::getIntoConfig("faction_worlds"))) {
                                        $faction = FactionsAPI::getFaction($player);
                                        FactionsAPI::createHome($faction, $player->getPosition());
                                        $player->sendMessage(Utils::getMessage($player, "HOME_SET"));
                                    } else $player->sendMessage(Utils::getMessage($player, "NOT_FACTION_WORLD"));
                                } else $player->sendMessage(Utils::getMessage($player, "ALREADY_HAVE_HOME"));
                            } else $player->sendMessage(Utils::getMessage($player, "ONLY_LEADER_OR_OFFICER"));
                        } else $player->sendMessage(Utils::getMessage($player, "MUST_BE_IN_FACTION"));
                        return true;
                    case "delhome":
                        if (FactionsAPI::isInFaction($player)) {
                            if (FactionsAPI::getRank($player->getName()) === "Leader" or FactionsAPI::getRank($player->getName()) === "Officer") {
                                if (FactionsAPI::existsHome(FactionsAPI::getFaction($player))) {
                                    $faction = FactionsAPI::getFaction($player);
                                    FactionsAPI::deleteHome($faction);
                                    $player->sendMessage(Utils::getMessage($player, "HOME_DELETE"));
                                } else $player->sendMessage(Utils::getMessage($player, "NOT_HAVE_HOME"));
                            } else $player->sendMessage(Utils::getMessage($player, "ONLY_LEADER_OR_OFFICER"));
                        } else $player->sendMessage(Utils::getMessage($player, "MUST_BE_IN_FACTION"));
                        return true;
                    case "home":
                        if (FactionsAPI::isInFaction($player)) {
                            if (FactionsAPI::existsHome(FactionsAPI::getFaction($player))) {
                                $faction = FactionsAPI::getFaction($player);
                                $player->teleport(FactionsAPI::getHome($faction));
                                $player->sendMessage(Utils::getMessage($player, "HOME_TELEPORTED"));
                            } else $player->sendMessage(Utils::getMessage($player, "NOT_HAVE_HOME"));
                        } else $player->sendMessage(Utils::getMessage($player, "MUST_BE_IN_FACTION"));
                        return true;
                    case 'claim':
                        if (FactionsAPI::isInFaction($player)) {
                            if (FactionsAPI::getRank($player->getName()) === "Leader" or FactionsAPI::getRank($player->getName()) === "Officer") {
                                if (in_array($player->getLevel()->getFolderName(), Utils::getIntoConfig("faction_worlds"))) {
                                    $chunk = $player->getLevel()->getChunkAtPosition($player);
                                    $chunkX = $chunk->getX();
                                    $chunkZ = $chunk->getZ();
                                    if (!FactionsAPI::isInClaim($player->getLevel(), $chunkX, $chunkZ)) {
                                        $faction = FactionsAPI::getFaction($player);
                                        $claimCount = FactionsAPI::getClaimCount($faction);
                                        if ($claimCount - 1 < count(Utils::getIntoConfig("claims"))) {
                                            $powerNeeded = (int)Utils::getIntoConfig("claims")[$claimCount];
                                            if (FactionsAPI::getPower($faction) >= $powerNeeded) {
                                                FactionsAPI::claimChunk($player, $faction);
                                                $player->sendMessage(Utils::getMessage($player, "CLAIM_SUCCESS"));
                                            } else $player->sendMessage(Utils::getMessage($player, "NOT_ENOUGHT_POWER", array($powerNeeded)));
                                        } else $player->sendMessage(Utils::getMessage($player, "MAX_CLAIM"));
                                    } else $player->sendMessage(Utils::getMessage($player, "ALREADY_CLAIMED", array(FactionsAPI::getFactionClaim($player->getLevel(), $chunkX, $chunkZ))));
                                } else $player->sendMessage(Utils::getMessage($player, "NOT_FACTION_WORLD"));
                            } else $player->sendMessage(Utils::getMessage($player, "ONLY_LEADER_OR_OFFICER"));
                        } else $player->sendMessage(Utils::getMessage($player, "MUST_BE_IN_FACTION"));
                        return true;
                    case "unclaim":
                        if (FactionsAPI::isInFaction($player)) {
                            if (FactionsAPI::getRank($player->getName()) === "Leader" or FactionsAPI::getRank($player->getName()) === "Officer") {
                                $chunk = $player->getLevel()->getChunkAtPosition($player);
                                $chunkX = $chunk->getX();
                                $chunkZ = $chunk->getZ();
                                if (FactionsAPI::isInClaim($player->getLevel(), $chunkX, $chunkZ)) {
                                    $faction = FactionsAPI::getFaction($player);
                                    if (FactionsAPI::getFactionClaim($player->getLevel(), $chunkX, $chunkZ) === $faction) {
                                        FactionsAPI::deleteClaim($player, $faction);
                                        $player->sendMessage(Utils::getMessage($player, "UNCLAIM_SUCCESS"));
                                    } else $player->sendMessage(Utils::getMessage($player, "NOT_CLAIM_BY_YOUR_FACTION"));
                                } else $player->sendMessage(Utils::getMessage($player, "NOT_CLAIM_BY_YOUR_FACTION"));
                            } else $player->sendMessage(Utils::getMessage($player, "ONLY_LEADER_OR_OFFICER"));
                        } else $player->sendMessage(Utils::getMessage($player, "MUST_BE_IN_FACTION"));
                        return true;
                    case "leave":
                        if (FactionsAPI::isInFaction($player)) {
                            if (FactionsAPI::getRank($player->getName()) !== "Leader") {
                                FactionsAPI::leaveFaction($player);
                                $player->sendMessage(Utils::getMessage($player, "LEAVE_SUCCESS"));
                            } else $player->sendMessage(Utils::getMessage($player, "LEADER_CANNOT_LEAVE"));
                        } else $player->sendMessage(Utils::getMessage($player, "MUST_BE_IN_FACTION"));
                        return true;
                    case "kick":
                        if (isset($args[1])) {
                            if (FactionsAPI::isInFaction($player)) {
                                if (FactionsAPI::getRank($player->getName()) === "Leader" or FactionsAPI::getRank($player->getName()) === "Officer") {
                                    $faction = FactionsAPI::getFaction($player);
                                    if (FactionsAPI::getPlayerFaction($args[1]) === $faction) {
                                        if (FactionsAPI::getRank($args[1]) !== "Leader" and FactionsAPI::getRank($args[1]) !== FactionsAPI::getRank($player->getName())) {
                                            FactionsAPI::kickFaction($args[1]);
                                            $player->sendMessage(Utils::getMessage($player, "KICK_SUCCESS", array($args[1])));
                                        } else $player->sendMessage(Utils::getMessage($player, "CANNOT_KICK_PLAYER"));
                                    } else $player->sendMessage(Utils::getMessage($player, "PLAYER_NOT_IN_YOUR_FACTION"));
                                } else $player->sendMessage(Utils::getMessage($player, "ONLY_LEADER_OR_OFFICER"));
                            } else $player->sendMessage(Utils::getMessage($player, "MUST_BE_IN_FACTION"));
                        } else $player->sendMessage(Utils::getMessage($player, "KICK_USAGE"));
                        return true;
                    case "promote":
                        if (isset($args[1])) {
                            if (FactionsAPI::isInFaction($player)) {
                                if (FactionsAPI::getRank($player->getName()) === "Leader") {
                                    $faction = FactionsAPI::getFaction($player);
                                    if (FactionsAPI::getPlayerFaction($args[1]) === $faction) {
                                        if (FactionsAPI::getRank($args[1]) === "Member") {
                                            FactionsAPI::promoteFaction($args[1]);
                                            $player->sendMessage(Utils::getMessage($player, "PROMOTE_SUCCESS", array($args[1])));
                                        } else $player->sendMessage(Utils::getMessage($player, "PLAYER_ALREADY_OFFICER"));
                                    } else $player->sendMessage(Utils::getMessage($player, "PLAYER_NOT_IN_YOUR_FACTION", array($args[1])));
                                } else $player->sendMessage(Utils::getMessage($player, "ONLY_LEADER"));
                            } else $player->sendMessage(Utils::getMessage($player, "MUST_BE_IN_FACTION"));
                        } else $player->sendMessage(Utils::getMessage($player, "PROMOTE_USAGE"));
                        return true;
                    case "demote":
                        if (isset($args[1])) {
                            if (FactionsAPI::isInFaction($player)) {
                                if (FactionsAPI::getRank($player->getName()) === "Leader") {
                                    $faction = FactionsAPI::getFaction($player);
                                    if (FactionsAPI::getPlayerFaction($args[1]) === $faction) {
                                        if (FactionsAPI::getRank($args[1]) === "Officer") {
                                            FactionsAPI::demoteFaction($args[1]);
                                            $player->sendMessage(Utils::getMessage($player, "DEMOTE_SUCCESS", array($args[1])));
                                        } else $player->sendMessage(Utils::getMessage($player, "PLAYER_ALREADY_MEMBER"));
                                    } else $player->sendMessage(Utils::getMessage($player, "PLAYER_NOT_IN_YOUR_FACTION", array($args[1])));
                                } else $player->sendMessage(Utils::getMessage($player, "ONLY_LEADER"));
                            } else $player->sendMessage(Utils::getMessage($player, "MUST_BE_IN_FACTION"));
                        } else $player->sendMessage(Utils::getMessage($player, "DEMOTE_USAGE"));
                        return true;
                    case "invite":
                        if (isset($args[1])) {
                            if (FactionsAPI::isInFaction($player)) {
                                if (FactionsAPI::getRank($player->getName()) === "Leader" or FactionsAPI::getRank($player->getName()) === "Officer") {
                                    if (Server::getInstance()->getPlayer($args[1])) {
                                        $target = Server::getInstance()->getPlayer($args[1]);
                                        if ($target instanceof Player) {
                                            if (!FactionsAPI::isInFaction($target)) {
                                                $faction = FactionsAPI::getFaction($player);
                                                FactionsAPI::sendInvitation($target, $faction);
                                                $target->sendMessage(Utils::getMessage($target, "INVITE_SUCCESS_TARGET", array($faction)));
                                                $player->sendMessage(Utils::getMessage($player, "INVITE_SUCCESS", array($target->getName())));
                                            } else $player->sendMessage(Utils::getMessage($player, "PLAYER_ALREADY_IN_FACTION"));
                                        } else $player->sendMessage(Utils::getMessage($player, "PLAYER_NOT_ONLINE"));
                                    } else $player->sendMessage(Utils::getMessage($player, "PLAYER_NOT_ONLINE"));
                                } else $player->sendMessage(Utils::getMessage($player, "ONLY_LEADER_OR_OFFICER"));
                            } else $player->sendMessage(Utils::getMessage($player, "MUST_BE_IN_FACTION"));
                        } else $player->sendMessage(Utils::getMessage($player, "INVITE_USAGE"));
                        return true;
                    case "accept":
                        if (!FactionsAPI::isInFaction($player)) {
                            if (isset(FactionsAPI::$invitation[$player->getName()])) {
                                $timer = FactionsAPI::$invitationTimeout[$player->getName()];
                                $timer = $timer - time();
                                if ($timer > 0) {
                                    $faction = FactionsAPI::$invitation[$player->getName()];
                                    if (count(FactionsAPI::getAllPlayers($faction)) < (int)Utils::getIntoConfig("faction_max_members")) {
                                        FactionsAPI::acceptInvitation($player);
                                        $player->sendMessage(Utils::getMessage($player, "ACCEPT_SUCCESS", array($faction)));
                                    } else $player->sendMessage(Utils::getMessage($player, "FACTION_FULL"));
                                } else $player->sendMessage(Utils::getMessage($player, "INVITATION_EXPIRED"));
                            } else $player->sendMessage(Utils::getMessage($player, "DONT_HAVE_INVITATION"));
                        } else $player->sendMessage(Utils::getMessage($player, "ALREADY_IN_FACTION"));
                    return true;
                    case "deny":
                        if (!FactionsAPI::isInFaction($player)) {
                            if (isset(FactionsAPI::$invitation[$player->getName()])) {
                                $timer = FactionsAPI::$invitationTimeout[$player->getName()];
                                $timer = $timer - time();
                                if ($timer > 0) {
                                    $faction = FactionsAPI::$invitation[$player->getName()];
                                    FactionsAPI::denyInvitation($player);
                                    $player->sendMessage(Utils::getMessage($player, "DENY_SUCCESS", array($faction)));
                                } else $player->sendMessage(Utils::getMessage($player, "INVITATION_EXPIRED"));
                            } else $player->sendMessage(Utils::getMessage($player, "DONT_HAVE_INVITATION"));
                        } else $player->sendMessage(Utils::getMessage($player, "ALREADY_IN_FACTION"));
                        return true;
                    case "top":
                        if(isset($args[1])) {
                            FactionsAPI::sendFactionTop($player, (int)$args[1]);
                        } else FactionsAPI::sendFactionTop($player);
                        return true;
                    case "transfer":
                    case "leader":
                    if (isset($args[1])) {
                        if (FactionsAPI::isInFaction($player)) {
                            if (FactionsAPI::getRank($player->getName()) === "Leader") {
                                $faction = FactionsAPI::getFaction($player);
                                if (FactionsAPI::getPlayerFaction($args[1]) === $faction) {
                                    if (strtolower($args[1]) !== strtolower($player->getName())) {
                                        FactionsAPI::demoteFaction($player->getName());
                                        FactionsAPI::transferFaction($args[1]);
                                        $player->sendMessage(Utils::getMessage($player, "TRANSFER_SUCCESS", array($args[1])));
                                    } else $player->sendMessage(Utils::getMessage($player, "CANNOT_TRANFER_YOURSELF"));
                                } else $player->sendMessage(Utils::getMessage($player, "PLAYER_NOT_IN_YOUR_FACTION", array($args[1])));
                            } else $player->sendMessage(Utils::getMessage($player, "ONLY_LEADER"));
                        } else $player->sendMessage(Utils::getMessage($player, "MUST_BE_IN_FACTION"));
                    } else $player->sendMessage(Utils::getMessage($player, "TRANSFER_USAGE"));
                        return true;
                    case "allies":
                    case "ally":
                        if (isset($args[1])) {
                            switch ($args[1]) {
                                case "add":
                                    if (FactionsAPI::isInFaction($player)) {
                                        if (FactionsAPI::getRank($player->getName()) === "Leader") {
                                            if (isset($args[2])) {
                                                if (FactionsAPI::existsFaction($args[2])) {
                                                    $faction1 = FactionsAPI::getFaction($player);
                                                    $faction2 = $args[2];
                                                    FactionsAPI::sendAlliesInvitation($faction2, $faction1);
                                                    $player->sendMessage(Utils::getMessage($player, "ALLIES_INVITE_SUCCESS", array($faction2)));
                                                } else $player->sendMessage(Utils::getMessage($player, "FACTION_NOT_EXIST"));
                                            } else $player->sendMessage(Utils::getMessage($player, "ALLIES_REMOVE_USAGE"));
                                        } else $player->sendMessage(Utils::getMessage($player, "ONLY_LEADER"));
                                    } else $player->sendMessage(Utils::getMessage($player, "MUST_BE_IN_FACTION"));
                                    return  true;
                                case "remove":
                                    if (FactionsAPI::isInFaction($player)) {
                                        if (FactionsAPI::getRank($player->getName()) === "Leader") {
                                            if (isset($args[2])) {
                                                if (FactionsAPI::existsFaction($args[2])) {
                                                    $faction1 = FactionsAPI::getFaction($player);
                                                    $faction2 = $args[2];
                                                    if (FactionsAPI::areAllies($faction1, $faction2)) {
                                                        FactionsAPI::removeAllies($faction1, $faction2);
                                                    } else $player->sendMessage(Utils::getMessage($player, "NOT_ALLIES", array($faction2)));
                                                } else $player->sendMessage(Utils::getMessage($player, "FACTION_NOT_EXIST"));
                                            } else $player->sendMessage(Utils::getMessage($player, "ALLIES_REMOVE_USAGE"));
                                        } else $player->sendMessage(Utils::getMessage($player, "ONLY_LEADER"));
                                    } else $player->sendMessage(Utils::getMessage($player, "MUST_BE_IN_FACTION"));
                                    return  true;
                                case "list":
                                    if (FactionsAPI::isInFaction($player)) {
                                        $faction = FactionsAPI::getFaction($player);
                                        $player->sendMessage(Utils::getMessage($player, "ALLIES_LIST_HEADER"));
                                        $message = Utils::getMessage($player, "ALLIES_LIST");
                                        $allies = FactionsAPI::getAllies($faction);
                                        $allieMessage = implode(", ", FactionsAPI::getAllies($faction));
                                        if (empty($allies)) $allieMessage = "§cNone";
                                        $message = str_replace("{allies}", $allieMessage, $message);
                                        $player->sendMessage($message);
                                    } else $player->sendMessage(Utils::getMessage($player, "MUST_BE_IN_FACTION"));
                                    return  true;
                                case "accept":
                                    if (FactionsAPI::isInFaction($player)) {
                                        if (FactionsAPI::getRank($player->getName()) === "Leader") {
                                            $faction = FactionsAPI::getFaction($player);
                                            if (isset(FactionsAPI::$Alliesinvitation[$faction])) {
                                                $faction2 = FactionsAPI::$Alliesinvitation[$faction];
                                                $timer = FactionsAPI::$AlliesinvitationTimeout[$faction];
                                                $timer = $timer - time();
                                                if ($timer > 0) {
                                                    if (FactionsAPI::getAlliesCount($faction) < (int)Utils::getIntoConfig("faction_max_allies")) {
                                                        if (FactionsAPI::getAlliesCount($faction2) < (int)Utils::getIntoConfig("faction_max_allies")) {
                                                            FactionsAPI::acceptAlliesInvitation($faction);
                                                        } else $player->sendMessage(Utils::getMessage($player, "FACTION_MAX_ALLIES", array($faction2)));
                                                    } else $player->sendMessage(Utils::getMessage($player, "YOUR_FACTION_MAX_ALLIES"));
                                                } else $player->sendMessage(Utils::getMessage($player, "ALLIES_REQUEST_EXPIRE"));
                                            } else $player->sendMessage(Utils::getMessage($player, "DONT_HAVE_ALLIES_REQUEST"));
                                        } else $player->sendMessage(Utils::getMessage($player, "ONLY_LEADER"));
                                    } else $player->sendMessage(Utils::getMessage($player, "MUST_BE_IN_FACTION"));
                                    return  true;
                                case "deny":
                                    if (FactionsAPI::isInFaction($player)) {
                                        if (FactionsAPI::getRank($player->getName()) === "Leader") {
                                            $faction = FactionsAPI::getFaction($player);
                                            if (isset(FactionsAPI::$Alliesinvitation[$faction])) {
                                                $timer = FactionsAPI::$AlliesinvitationTimeout[$faction];
                                                $timer = $timer - time();
                                                if ($timer > 0) {
                                                    $player->sendMessage(Utils::getMessage($player, "ALLIES_DENY_SUCCESS", array(FactionsAPI::$Alliesinvitation[$faction])));
                                                    FactionsAPI::denyAlliesInvitation($faction);
                                                } else $player->sendMessage(Utils::getMessage($player, "ALLIES_REQUEST_EXPIRE"));
                                            } else $player->sendMessage(Utils::getMessage($player, "DONT_HAVE_ALLIES_REQUEST"));
                                        } else $player->sendMessage(Utils::getMessage($player, "ONLY_LEADER"));
                                    } else $player->sendMessage(Utils::getMessage($player, "MUST_BE_IN_FACTION"));
                                    return  true;
                                default:
                                    $player->sendMessage(Utils::getMessage($player, "ALLIES_USAGE"));
                                    return true;
                            }
                        } else $player->sendMessage(Utils::getMessage($player, "ALLIES_USAGE"));
                        return true;
                    case "chat":
                        if (FactionsAPI::isInFaction($player)) {
                            if (isset($args[1])) {
                                switch (strtolower($args[1])) {
                                    case "faction":
                                    case "fac":
                                    case "f":
                                    if (isset(FactionsAPI::$chat[$player->getName()])) {
                                        if (FactionsAPI::$chat[$player->getName()] !== "FACTION") {
                                            FactionsAPI::$chat[$player->getName()] = "FACTION";
                                            $player->sendMessage(Utils::getMessage($player, "CHAT_SUCCESS", array("FACTION")));
                                        } else $player->sendMessage(Utils::getMessage($player, "ALREADY_THIS_CHAT", array("FACTION")));
                                    } else {
                                        FactionsAPI::$chat[$player->getName()] = "FACTION";
                                        $player->sendMessage(Utils::getMessage($player, "CHAT_SUCCESS", array("FACTION")));
                                    }
                                        break;
                                    case "alliance":
                                    case "ally":
                                    case "a":
                                    if (isset(FactionsAPI::$chat[$player->getName()])) {
                                        if (FactionsAPI::$chat[$player->getName()] !== "ALLIANCE") {
                                            FactionsAPI::$chat[$player->getName()] = "ALLIANCE";
                                            $player->sendMessage(Utils::getMessage($player, "CHAT_SUCCESS", array("ALLIANCE")));
                                        } else $player->sendMessage(Utils::getMessage($player, "ALREADY_THIS_CHAT", array("ALLIANCE")));
                                    } else {
                                        FactionsAPI::$chat[$player->getName()] = "ALLIANCE";
                                        $player->sendMessage(Utils::getMessage($player, "CHAT_SUCCESS", array("ALLIANCE")));
                                    }
                                        break;
                                    case "global":
                                    case "g":
                                        if (isset(FactionsAPI::$chat[$player->getName()])) {
                                            unset(FactionsAPI::$chat[$player->getName()]);
                                            $player->sendMessage(Utils::getMessage($player, "CHAT_SUCCESS", array("GLOBAL")));
                                        } else $player->sendMessage(Utils::getMessage($player, "ALREADY_THIS_CHAT", array("GLOBAL")));
                                        break;
                                    default:
                                        $player->sendMessage(Utils::getMessage($player, "CHAT_USAGE"));
                                        break;
                                }
                            } else $player->sendMessage(Utils::getMessage($player, "CHAT_USAGE"));
                        } else $player->sendMessage(Utils::getMessage($player, "MUST_BE_IN_FACTION"));
                        return true;
                    case "bank":
                        if (isset($args[1])) {
                            if (FactionsAPI::isInFaction($player)) {
                                switch($args[1]) {
                                    case "deposit":
                                    case "d":
                                        if (isset($args[2])) {
                                            $faction = FactionsAPI::getFaction($player);
                                            $money = (int)$args[2];
                                            if ($money > 0) {
                                                if (Main::getEconomy()->myMoney($player) >= $money) {
                                                    FactionsAPI::addMoney($faction, $money);
                                                    Main::getEconomy()->reduceMoney($player, $money);
                                                    $player->sendMessage(Utils::getMessage($player, "BANK_DEPOST_SUCCESS", array($money)));
                                                } else $player->sendMessage(Utils::getMessage($player, "NOT_ENOUGH_MONEY"));
                                            } else $player->sendMessage(Utils::getMessage($player, "ENTER_VALID_NUMBER"));
                                        } else $player->sendMessage(Utils::getMessage($player, "BANK_DEPOSIT_USAGE"));
                                        break;
                                    case "withdraw":
                                    case "w":
                                    if (FactionsAPI::getRank($player->getName()) !== "Member") {
                                        if (isset($args[2])) {
                                            $faction = FactionsAPI::getFaction($player);
                                            $money = (int)$args[2];
                                            if ($money > 0) {
                                                if (FactionsAPI::getMoney($faction) >= $money) {
                                                    FactionsAPI::removeMoney($faction, $money);
                                                    Main::getEconomy()->addMoney($player, $money);
                                                    $player->sendMessage(Utils::getMessage($player, "BANK_WITHDRAW_SUCCESS", array($money)));
                                                } else $player->sendMessage(Utils::getMessage($player, "FACTION_NOT_ENOUGH_MONEY"));
                                            } else $player->sendMessage(Utils::getMessage($player, "ENTER_VALID_NUMBER"));
                                        } else $player->sendMessage(Utils::getMessage($player, "BANK_WITHDRAW_USAGE"));
                                    } else $player->sendMessage(Utils::getMessage($player, "ONLY_LEADER_OR_OFFICER"));
                                        break;
                                    case "status":
                                    case "s":
                                        $faction = FactionsAPI::getFaction($player);
                                        $money = FactionsAPI::getMoney($faction);
                                        $player->sendMessage(Utils::getMessage($player, "BANK_STATUS", array($money)));
                                        break;
                                }
                            } else $player->sendMessage(Utils::getMessage($player, "MUST_BE_IN_FACTION"));
                        } else $player->sendMessage(Utils::getMessage($player, "BANK_USAGE"));
                        return true;
                    case "lang":
                        $langs = "";
                        foreach (Utils::getIntoLang("languages") as $key => $val) $langs .= ', ' . $key;
                        $langs = substr($langs, 2);
                        if (isset($args[1])) {
                            if (isset(Utils::getIntoLang("languages")[$args[1]])) {
                                FactionsAPI::setLanguages($player, $args[1]);
                                $fullName = Utils::getIntoLang("languages-fullname")[$args[1]];
                                $player->sendMessage(Utils::getMessage($player, "LANG_CHANGE_SUCCESS", array($fullName)));
                            } else $player->sendMessage(Utils::getMessage($player, "LANG_NOT_EXIST"));
                        } else $player->sendMessage(Utils::getMessage($player, "LANG_USAGE", array($langs)));
                        return true;
                    case "map":
                        if (isset($args[1])) {
                            switch (strtolower($args[1])) {
                                case "on":
                                    if (!isset(FactionsAPI::$map[$player->getName()])) {
                                        FactionsAPI::$map[$player->getName()] = true;
                                        $player->sendMessage(Utils::getMessage($player, "MAP_ON_SUCCESS"));
                                    } else $player->sendMessage(Utils::getMessage($player, "MAP_ALREADY_ON"));
                                    break;
                                case "off":
                                    if (isset(FactionsAPI::$map[$player->getName()])) {
                                        unset(FactionsAPI::$map[$player->getName()]);
                                        $player->sendMessage(Utils::getMessage($player, "MAP_OFF_SUCCESS"));
                                    } else $player->sendMessage(Utils::getMessage($player, "MAP_ALREADY_OFF"));
                                    break;
                                default:
                                    $player->sendMessage(Utils::getMessage($player, "MAP_USAGE"));
                                    break;
                            }
                        } else $player->sendMessage(implode(TextFormat::EOL, FactionsAPI::getMap($player)));
                        return true;
                    case "border":
                        if (isset(FactionsAPI::$border[$player->getName()])) {
                            unset(FactionsAPI::$border[$player->getName()]);
                            $player->sendMessage(Utils::getMessage($player, "BORDER_DESACTIVATED"));
                        } else {
                            FactionsAPI::$border[$player->getName()] = true;
                            $player->sendMessage(Utils::getMessage($player, "BORDER_ACTIVATED"));
                        }
                        return true;
                    case "admin":
                        if ($player->hasPermission("simplefaction.admin")) {
                            if (isset($args[1])) {
                                switch ($args[1]) {
                                    case "addpower":
                                        if (isset($args[2]) and isset($args[3])) {
                                            if (FactionsAPI::existsFaction($args[2])) {
                                                if (is_numeric($args[3])) {
                                                    FactionsAPI::addPower($args[2], (int)$args[3]);
                                                    $player->sendMessage(Utils::getMessage($player, "ADMIN_ADDPOWER_SUCCESS", array((int)$args[3])));
                                                } else $player->sendMessage(Utils::getMessage($player, "ENTER_VALID_NUMBER"));
                                            } else $player->sendMessage(Utils::getMessage($player, "FACTION_NOT_EXIST"));
                                        } else $player->sendMessage(Utils::getMessage($player, "ADMIN_ADDPOWER_USAGE"));
                                        break;
                                    case "removepower":
                                        if (isset($args[2]) and isset($args[3])) {
                                            if (FactionsAPI::existsFaction($args[2])) {
                                                if (is_numeric($args[3])) {
                                                    FactionsAPI::removePower($args[2], (int)$args[3]);
                                                    $player->sendMessage(Utils::getMessage($player, "ADMIN_REMOVEPOWER_SUCCESS", array((int)$args[3])));
                                                } else $player->sendMessage(Utils::getMessage($player, "ENTER_VALID_NUMBER"));
                                            } else $player->sendMessage(Utils::getMessage($player, "FACTION_NOT_EXIST"));
                                        } else $player->sendMessage(Utils::getMessage($player, "ADMIN_REMOVEPOWER_USAGE"));
                                        break;
                                    case "setpower":
                                        if (isset($args[2]) and isset($args[3])) {
                                            if (FactionsAPI::existsFaction($args[2])) {
                                                if (is_numeric($args[3])) {
                                                    FactionsAPI::setPower($args[2], (int)$args[3]);
                                                    $player->sendMessage(Utils::getMessage($player, "ADMIN_SETPOWER_SUCCESS", array((int)$args[3])));
                                                } else $player->sendMessage(Utils::getMessage($player, "ENTER_VALID_NUMBER"));
                                            } else $player->sendMessage(Utils::getMessage($player, "FACTION_NOT_EXIST"));
                                        } else $player->sendMessage(Utils::getMessage($player, "ADMIN_SETPOWER_USAGE"));
                                        break;
                                    case "delete":
                                        if (isset($args[2])) {
                                            if (FactionsAPI::existsFaction($args[2])) {
                                                FactionsAPI::disbandFaction($player, $args[2]);
                                                $player->sendMessage(Utils::getMessage($player, "ADMIN_DELETE_SUCCESS"));
                                            } else $player->sendMessage(Utils::getMessage($player, "FACTION_NOT_EXIST"));
                                        } else $player->sendMessage(Utils::getMessage($player, "ADMIN_DELETE_USAGE"));
                                        break;
                                    case "rename":
                                        if (isset($args[2]) and isset($args[3])) {
                                            if (FactionsAPI::existsFaction($args[2])) {
                                                if (!FactionsAPI::existsFaction($args[3])) {
                                                    FactionsAPI::renameFaction($args[2], $args[3]);
                                                    $player->sendMessage(Utils::getMessage($player, "ADMIN_RENAME_SUCCESS", array($args[3])));
                                                } else $player->sendMessage(Utils::getMessage($player, "FACTION_ALREADY_EXIST"));
                                            } else $player->sendMessage(Utils::getMessage($player, "FACTION_NOT_EXIST"));
                                        } else $player->sendMessage(Utils::getMessage($player, "ADMIN_RENAME_USAGE"));
                                        break;
                                    case "unclaim":
                                        $chunk = $player->getLevel()->getChunkAtPosition($player);
                                        $chunkX = $chunk->getX();
                                        $chunkZ = $chunk->getZ();
                                        if (FactionsAPI::isInClaim($player->getLevel(), $chunkX, $chunkZ)) {
                                            $faction = FactionsAPI::getFactionClaim($player->getLevel(), $chunkX, $chunkZ);
                                            FactionsAPI::deleteClaim($player, $faction);
                                            $player->sendMessage(Utils::getMessage($player, "ADMIN_UNCLAIM_SUCCESS"));
                                        } else $player->sendMessage(Utils::getMessage($player, "ADMIN_NOT_IN_CLAIM"));
                                        break;
                                }
                            } else $player->sendMessage(Utils::getMessage($player, "ADMIN_USAGE"));
                        } else $player->sendMessage("§cYou don't have the permission to use this command");
                        break;
                    case "about":
                        $player->sendMessage("§c§lPlugin created by Ayzrix.");
                        $player->sendMessage("§4§lYoutube:§r§f Ayzrix");
                        $player->sendMessage("§b§lTwitter:§r§f @Ayzrix");
                        $player->sendMessage("§6§lDownload link:§r§f github.com/AyzrixYTB/SimpleFaction");
                        return true;
                    default:
                        $player->sendMessage(Utils::getMessage($player, "COMMAND_USAGE"));
                        return true;
                }
            } else $player->sendMessage(Utils::getMessage($player, "COMMAND_USAGE"));
        } else $player->sendMessage(Utils::getIntoConfig("PLAYER_ONLY"));
        return true;
    }
}