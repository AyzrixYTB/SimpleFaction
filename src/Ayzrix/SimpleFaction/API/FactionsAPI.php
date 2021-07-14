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

namespace Ayzrix\SimpleFaction\API;

use Ayzrix\SimpleFaction\Main;
use Ayzrix\SimpleFaction\Tasks\WarsTask;
use Ayzrix\SimpleFaction\Utils\Utils;
use pocketmine\level\Level;
use pocketmine\level\Position;
use pocketmine\Player;
use pocketmine\Server;
use pocketmine\utils\TextFormat;

class FactionsAPI {

    /** @var array $moving */
    public static $moving = [];

    /** @var array $faction */
    public static $faction = [];

    /** @var array $player */
    public static $player = [];

    /** @var array $home */
    public static $home = [];

    /** @var array $lang */
    public static $lang = [];

    /** @var array $claim */
    public static $claim = [];

    /** @var array $invitation */
    public static $invitation = [];

    /** @var array $invitationTimeout */
    public static $invitationTimeout = [];

    /** @var array $Alliesinvitation */
    public static $Alliesinvitation = [];

    /** @var array $AlliesinvitationTimeout */
    public static $AlliesinvitationTimeout = [];

    /** @var array $chat */
    public static $chat = [];

    /** @var array $map */
    public static $map = [];

    /** @var array $border */
    public static $border = [];

    /** @var array $Warsinvitation */
    public static $Warsinvitation = [];

    /** @var array $Wars */
    public static $Wars = [];

    const MAP_KEY_CHARS = "\\/#?ç¬£$%=&^ABCDEFGHJKLMNOPQRSTUVWXYZÄÖÜÆØÅ1234567890abcdeghjmnopqrsuvwxyÿzäöüæøåâêîûô";
    const MAP_WIDTH = 48;
    const MAP_HEIGHT = 10;
    const MAP_KEY_MIDDLE = TextFormat::AQUA . "+";
    const MAP_KEY_WILDERNESS = TextFormat::GRAY . "-";
    const MAP_KEY_OVERFLOW = TextFormat::WHITE . "-" . TextFormat::RESET;
    const MAP_OVERFLOW_MESSAGE = self::MAP_KEY_OVERFLOW . ": Trop de faction (>86) sur la carte.";
    const DIRECTIONS = [
        "N" => 'N',
        "NE" => '/',
        "E" => 'E',
        "SE" => '\\',
        "S" => 'S',
        "SW" => '/',
        "W" => 'W',
        "NW" => '\\',
        "NONE" => '+'
    ];

    const COLOR_ACTIVE = TextFormat::GREEN;
    const COLOR_INACTIVE = TextFormat::RED;

    /**
     * @param string $name
     * @return bool
     */
    public static function isInFaction(string $name): bool {
        return isset(self::$player[strtolower($name)]);
    }

    /**
     * @param string $faction
     * @return bool
     */
    public static function existsFaction(string $faction): bool {
        $faction = strtolower($faction);
        foreach (self::$faction as $key => $value) {
            if (strtolower($key) === $faction) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param Player $player
     * @param string $faction
     */
    public static function createFaction(Player $player, string $faction): void {
        $name = $player->getName();
        self::$faction[$faction] = array("players" => array($player->getName()), "power" => (int)Utils::getIntoConfig("default_power"), "money" => 0, "allies" => array());
        self::$player[strtolower($player->getName())] = array("faction" => $faction, "role" => "Leader");
        self::$claim[$faction] = array();

        $nameE = Utils::real_escape_string($name);
        $factionE = Utils::real_escape_string($faction);
        $players = Utils::real_escape_string(base64_encode(serialize(array($player->getName()))));
        $allies = Utils::real_escape_string(base64_encode(serialize(array())));
        $claims = Utils::real_escape_string(base64_encode(serialize(array())));
        Utils::query("INSERT INTO faction (faction, players, power, money, allies, claims) VALUES ('$factionE', '$players', 0, 0, '$allies', '$claims')");
        Utils::query("INSERT INTO player (player, faction, role) VALUES ('$nameE', '$factionE', 'Leader')");

        if (Utils::getIntoConfig("broadcast_message_created") === true) {
            foreach (Server::getInstance()->getOnlinePlayers() as $player) {
                if ($player instanceof Player) {
                    $player->sendMessage(Utils::getMessage($player, "FACTION_CREATE_BROADCAST", array($name, $faction)));
                }
            }
        }
    }

    /**
     * @param Player $player
     * @param string $faction
     */
    public static function disbandFaction(Player $player, string $faction): void {
        $name = $player->getName();

        if (isset(self::$faction[$faction]))unset(self::$faction[$faction]);
        if (isset(self::$home[$faction])) unset(self::$home[$faction]);
        if (isset(self::$claim[$faction])) unset(self::$claim[$faction]);
        $factionE = Utils::real_escape_string($faction);
        Utils::query("DELETE FROM faction WHERE faction='$factionE'");
        Utils::query("DELETE FROM home WHERE faction='$factionE'");

        foreach (self::$player as $player => $value) {
            if ($value["faction"] === $faction) {
                unset(self::$player[$player]);
                $playerE = Utils::real_escape_string($player);
                Utils::query("DELETE FROM player WHERE player='$playerE'");
            }
        }

        if (Utils::getIntoConfig("broadcast_message_disband") === true) {
            foreach (Server::getInstance()->getOnlinePlayers() as $player) {
                if ($player instanceof Player) {
                    $player->sendMessage(Utils::getMessage($player, "FACTION_DISBAND_BROADCAST", array($name, $faction)));
                }
            }
        }
    }

    /**
     * @param string $name
     * @return string
     */
    public static function getFaction(string $name): string {
        return self::$player[strtolower($name)]["faction"]?? "";
    }

    /**
     * @param string $name
     * @return string
     */
    public static function getRank(string $name): string {
        return self::$player[strtolower($name)]["role"]?? "";
    }

    /**
     * @param string $faction
     * @return int
     */
    public static function getPower(string $faction): int {
        return self::$faction[$faction]["power"]?? 0;
    }

    /**
     * @param string $faction
     * @param int $amount
     */
    public static function addPower(string $faction, int $amount): void {
        self::$faction[$faction]["power"] = self::getPower($faction) + $amount;
        $factionE = Utils::real_escape_string($faction);
        Utils::query("UPDATE faction SET power = power + $amount WHERE faction='$factionE'");
    }

    /**
     * @param string $faction
     * @param int $amount
     */
    public static function removePower(string $faction, int $amount): void {
        if (self::getPower($faction) - $amount <= 0) {
            self::setPower($faction, 0);
            $factionE = Utils::real_escape_string($faction);
            Utils::query("UPDATE faction SET power = 0 WHERE faction='$factionE'");
            return;
        }
        self::$faction[$faction]["power"] = self::getPower($faction) - $amount;
        $factionE = Utils::real_escape_string($faction);
        Utils::query("UPDATE faction SET power = power - $amount WHERE faction='$factionE'");
    }

    /**
     * @param string $faction
     * @param int $amount
     */
    public static function setPower(string $faction, int $amount): void {
        self::$faction[$faction]["power"] = $amount;
        $factionE = Utils::real_escape_string($faction);
        Utils::query("UPDATE faction SET power = $amount WHERE faction='$factionE'");
    }

    /**
     * @param string $faction
     * @return array
     */
    public static function getAllPlayers(string $faction): array {
        return self::$faction[$faction]["players"]?? array();
    }

    /**
     * @param string $faction
     * @return string
     */
    public static function getLeader(string $faction): string {
        foreach (self::getAllPlayers($faction) as $player) {
            if (self::getRank($player) === "Leader") {
                return $player;
            }
        }
        return "";
    }

    /**
     * @param string $faction
     * @return array
     */
    public static function getOfficers(string $faction): array {
        $array = [];
        foreach (self::getAllPlayers($faction) as $player) {
            if(self::getRank($player) === "Officer") {
                $array[] = $player;
            }
        }
        return $array;
    }

    /**
     * @param string $faction
     * @return array
     */
    public static function getMembers(string $faction): array {
        $array = [];
        foreach (self::getAllPlayers($faction) as $player) {
            if(self::getRank($player) === "Member") {
                $array[] = $player;
            }
        }
        return $array;
    }

    /**
     * @param string $faction
     * @return bool
     */
    public static function existsHome(string $faction): bool {
        return isset(self::$home[$faction]);
    }

    /**
     * @param string $faction
     * @param Position $position
     */
    public static function createHome(string $faction, Position $position): void {
        $x = round($position->getX()) + 0.5;
        $y = round($position->getY()) + 0.5;
        $z = round($position->getZ()) + 0.5;
        $world = $position->getLevel()->getFolderName();
        self::$home[$faction] = array($x, $y, $z, $world);

        $factionE = Utils::real_escape_string($faction);
        $worldE = Utils::real_escape_string($world);
        Utils::query("INSERT INTO home (faction, x, y, z, world) VALUES ('$factionE', '$x', '$y', '$z', '$worldE')");
    }

    /**
     * @param string $faction
     */
    public static function deleteHome(string $faction): void {
        if (isset(self::$home[$faction])) unset(self::$home[$faction]);
        $factionE = Utils::real_escape_string($faction);
        Utils::query("DELETE FROM home WHERE faction='$factionE'");
    }

    /**
     * @param string $faction
     * @return Position
     */
    public static function getHome(string $faction): Position {
        $array = self::$home[$faction];
        return new Position((int)$array[0], (int)$array[1], (int)$array[2], Main::getInstance()->getServer()->getLevelByName($array[3]));
    }

    /**
     * @param Player $player
     * @param string $faction
     */
    public static function claimChunk(Player $player, string $faction): void {
        $chunk = $player->getLevel()->getChunkAtPosition($player);
        $chunkX = $chunk->getX();
        $chunkZ = $chunk->getZ();
        $world = $player->getLevel()->getFolderName();
        $claims = self::$claim[$faction];
        array_push($claims, "{$chunkX}:{$chunkZ}:{$world}");
        self::$claim[$faction] = $claims;
        $claims = Utils::real_escape_string(base64_encode(serialize($claims)));
        $factionE = Utils::real_escape_string($faction);
        Utils::query("UPDATE faction SET claims = '$claims' WHERE faction='$factionE'");
    }

    /**
     * @param Player $player
     * @param string $faction
     * @return bool
     */
    public static function isChunkNextToClaim(Player $player, string $faction): bool {
        $playerChunk = $player->getLevel()->getChunkAtPosition($player);
        $playerChunkX = $playerChunk->getX();
        $playerChunkZ = $playerChunk->getZ();
        foreach(self::$claim[$faction] as $factionClaim) {
            $factionClaim = explode(":", $factionClaim);
            if ($player->getLevel()->getFolderName() !== $factionClaim[2]) continue;
            for ($x = -1; $x <= 1; $x++) {
                for ($z = -1; $z <= 1; $z++) {
                    if(abs($x) === abs($z)) continue;
                    if (($playerChunkX + $x === (int)$factionClaim[0]) && ($playerChunkZ + $z === (int)$factionClaim[1])) return true;
                }
            }
        }
        return false;
    }

    /**
     * @param Level $level
     * @param int $chunkX
     * @param int $chunkZ
     * @return bool
     */
    public static function isInClaim(Level $level, int $chunkX, int $chunkZ): bool {
        $world = $level->getFolderName();
        $array = [];
        foreach (self::$claim as $faction => $claims) {
            $array = array_merge($array, $claims);
        }
        return in_array("{$chunkX}:{$chunkZ}:{$world}", $array);
    }

    /**
     * @param Level $level
     * @param int $chunkX
     * @param int $chunkZ
     * @return string
     */
    public static function getFactionClaim(Level $level, int $chunkX, int $chunkZ): string {
        $world = $level->getFolderName();
        foreach (self::$claim as $faction => $claims) {
            foreach ($claims as $claim) {
               if ($claim === "{$chunkX}:{$chunkZ}:{$world}") return $faction;
            }
        }
        return "";
    }

    /**
     * @param Player $player
     * @param string $faction
     */
    public static function deleteClaim(Player $player, string $faction) {
        $chunk = $player->getLevel()->getChunkAtPosition($player);
        $chunkX = $chunk->getX();
        $chunkZ = $chunk->getZ();
        $world = $player->getLevel()->getFolderName();
        $claim = self::$claim[$faction];
        unset($claim[array_search("{$chunkX}:{$chunkZ}:{$world}", $claim)]);
     	self::$claim[$faction] = $claim;
        $claims = Utils::real_escape_string(base64_encode(serialize($claim)));
        $factionE = Utils::real_escape_string($faction);
        Utils::query("UPDATE faction SET claims = '$claims' WHERE faction='$factionE'");
    }

    /**
     * @param string $faction
     * @return int
     */
    public static function getClaimCount(string $faction): int {
        return count(self::$claim[$faction])?? 0;
    }

    /**
     * @param Player $player
     */
    public static function leaveFaction(Player $player): void {
        $name = $player->getName();
        $faction = self::getFaction($player->getName());
        $array = self::$faction[$faction]["players"];
        unset($array[array_search($name, $array)]);
        self::$faction[$faction]["players"] = $array;
        unset(self::$player[strtolower($name)]);
        $players = Utils::real_escape_string(base64_encode(serialize($array)));
        $factionE = Utils::real_escape_string($faction);
        $nameE = Utils::real_escape_string($name);
        Utils::query("UPDATE faction SET players = '$players' WHERE faction='$factionE'");
        Utils::query("DELETE FROM player WHERE player='$nameE'");
    }

    /**
     * @param string $name
     */
    public static function kickFaction(string $name): void {
        $name = strtolower($name);
        $faction = self::getFaction($name);
        $array = self::$faction[$faction]["players"];
        $arraylower = (array)json_decode(strtolower(json_encode($array)));
        unset($array[array_search($name, $arraylower)]);
        self::$faction[$faction]["players"] = $array;
        unset(self::$player[$name]);
        $players = Utils::real_escape_string(base64_encode(serialize($array)));
        $factionE = Utils::real_escape_string($faction);
        $nameE = Utils::real_escape_string($name);
        Utils::query("UPDATE faction SET players = '$players' WHERE faction='$factionE'");
        Utils::query("DELETE FROM player WHERE player='$nameE'");
    }

    /**
     * @param string $name
     */
    public static function promoteFaction(string $name): void {
        self::$player[strtolower($name)]["role"] = "Officer";
        $nameE = Utils::real_escape_string($name);
        Utils::query("UPDATE player SET role = 'Officer' WHERE player='$nameE'");
    }

    /**
     * @param string $name
     */
    public static function demoteFaction(string $name): void {
        self::$player[strtolower($name)]["role"] = "Member";
        $nameE = Utils::real_escape_string($name);
        Utils::query("UPDATE player SET role = 'Member' WHERE player='$nameE'");
    }

    /**
     * @param string $name
     */
    public static function transferFaction(string $name): void {
        self::$player[strtolower($name)]["role"] = "Leader";
        $nameE = Utils::real_escape_string($name);
        Utils::query("UPDATE player SET role = 'Leader' WHERE player='$nameE'");
    }

    /**
     * @param Player $player
     * @param string $faction
     */
    public static function sendInvitation(Player $player, string $faction): void {
        self::$invitation[$player->getName()] = $faction;
        self::$invitationTimeout[$player->getName()] = time() + (int)Utils::getIntoConfig("invitation_expire_time");
    }

    /**
     * @param Player $player
     */
    public static function acceptInvitation(Player $player): void {
        $name = $player->getName();
        $faction = self::$invitation[$player->getName()];
        self::$player[strtolower($name)] = array("faction" => $faction, "role" => "Member");
        $array = self::$faction[$faction]["players"];
        array_push($array, $name);
        self::$faction[$faction]["players"] = $array;
        $players = Utils::real_escape_string(base64_encode(serialize($array)));
        $factionE = Utils::real_escape_string($name);
        Utils::query("UPDATE faction SET players = '$players' WHERE faction='$factionE'");
        $nameE = Utils::real_escape_string($name);
        Utils::query("INSERT INTO player (player, faction, role) VALUES ('$nameE', '$factionE', 'Member')");

        foreach (self::getAllPlayers($faction) as $player) {
            if (Server::getInstance()->getPlayer($player)) {
                $player = Server::getInstance()->getPlayer($player);
                if ($player instanceof Player) {
                    $player->sendMessage(Utils::getMessage($player, "JOIN_FACTION_BROADCAST", array($name)));
                }
            }
        }
        unset(self::$invitation[$player->getName()]);
        unset(self::$invitationTimeout[$player->getName()]);
    }

    /**
     * @param Player $player
     */
    public static function denyInvitation(Player $player): void {
        unset(self::$invitation[$player->getName()]);
        unset(self::$invitationTimeout[$player->getName()]);
    }

    /**
     * @return array
     */
    public static function getAllPowers(): array {
        $return = [];
        foreach (self::$faction as $faction => $value) {
            $return[$faction] = $value["power"];
        }
        return $return;
    }

    /**
     * @param Player $player
     * @param int $page
     */
    public static function sendFactionTop(Player $player, int $page = 1): void {
        $factions = self::getAllPowers();
        $maxpage = intval(abs(count($factions) / 10));
        $rest = count($factions) % 10;
        arsort($factions);
        if ($rest > 0) $maxpage++;
        if ($page === 0) $page = 1;
        if ($page > $maxpage) $page = $maxpage;
        $deptop = (($page - 1) * 10) + 1;
        $fintop = (($page - 1) * 10) + 11;

        $i = 1;
        $message = Utils::getMessage($player, "TOP_FACTION_HEADER", array($page, $maxpage));

        foreach ($factions as $faction => $power) {
            if ($i >= $fintop) break;
            if ($i < $deptop) {
                $i++;
                continue;
            }

            $memberscount = count(self::getAllPlayers($faction));
            $line = Utils::getMessage($player, "TOP_FACTION_LINE");
            $line = str_replace(["{index}", "{faction}", "{power}", "{members}"], [$i, $faction, $power, $memberscount], $line);
            $message .= PHP_EOL . $line;
            $i++;
        }
        $player->sendMessage($message);
    }

    /**
     * @param string $faction
     * @param string $faction2
     */
    public static function sendAlliesInvitation(string $faction, string $faction2): void {
        self::$Alliesinvitation[$faction] = $faction2;
        self::$AlliesinvitationTimeout[$faction] = time() + (int)Utils::getIntoConfig("allies_invitation_expire_time");

        foreach (self::getAllPlayers($faction) as $player) {
            if (Server::getInstance()->getPlayer($player)) {
                $player = Server::getInstance()->getPlayer($player);
                if ($player instanceof Player) {
                    $player->sendMessage(Utils::getMessage($player, "ALLIES_INVITE_SUCCESS_TARGET", array($faction2)));
                }
            }
        }
    }

    /**
     * @param string $faction
     */
    public static function acceptAlliesInvitation(string $faction): void {
        $faction2 = self::$Alliesinvitation[$faction];

        $allies = self::$faction[$faction]["allies"];
        array_push($allies, $faction2);
        self::$faction[$faction]["allies"] = $allies;
        $allies = Utils::real_escape_string(base64_encode(serialize($allies)));
        $factionE = Utils::real_escape_string($faction);
        Utils::query("UPDATE faction SET allies = '$allies' WHERE faction='$factionE'");

        $allies = self::$faction[$faction2]["allies"];
        array_push($allies, $faction);
        self::$faction[$faction2]["allies"] = $allies;
        $allies = Utils::real_escape_string(base64_encode(serialize($allies)));
        $faction2E = Utils::real_escape_string($faction2);
        Utils::query("UPDATE faction SET allies = '$allies' WHERE faction='$faction2E'");

        foreach (self::getAllPlayers($faction) as $player) {
            if (Server::getInstance()->getPlayer($player)) {
                $player = Server::getInstance()->getPlayer($player);
                if ($player instanceof Player) {
                    $player->sendMessage(Utils::getMessage($player, "NEW_ALLIANCE_FACTION_BROADCAST", array($faction2)));
                }
            }
        }

        foreach (self::getAllPlayers($faction2) as $player) {
            if (Server::getInstance()->getPlayer($player)) {
                $player = Server::getInstance()->getPlayer($player);
                if ($player instanceof Player) {
                    $player->sendMessage(Utils::getMessage($player, "NEW_ALLIANCE_FACTION_BROADCAST", array($faction)));
                }
            }
        }
        unset(self::$Alliesinvitation[$faction]);
        unset(self::$AlliesinvitationTimeout[$faction]);
    }

    /**
     * @param string $faction
     */
    public static function denyAlliesInvitation(string $faction): void {
        unset(self::$Alliesinvitation[$faction]);
        unset(self::$AlliesinvitationTimeout[$faction]);
    }

    /**
     * @param string $faction1
     * @param string $faction2
     * @return bool
     */
    public static function areAllies(string $faction1, string $faction2): bool {
        return in_array($faction2, self::$faction[$faction1]["allies"]);
    }

    /**
     * @param string $faction1
     * @param string $faction2
     */
    public static function removeAllies(string $faction1, string $faction2): void {
        foreach (self::getAllPlayers($faction1) as $player) {
            if (Server::getInstance()->getPlayer($player)) {
                $player = Server::getInstance()->getPlayer($player);
                if ($player instanceof Player) {
                    $player->sendMessage(Utils::getMessage($player, "ALLIES_REMOVE_SUCCESS", array($faction2)));
                }
            }
        }

        foreach (self::getAllPlayers($faction2) as $player) {
            if (Server::getInstance()->getPlayer($player)) {
                $player = Server::getInstance()->getPlayer($player);
                if ($player instanceof Player) {
                    $player->sendMessage(Utils::getMessage($player, "ALLIES_REMOVE_SUCCESS", array($faction1)));
                }
            }
        }

        $allies = self::$faction[$faction1]["allies"];
        unset($allies[array_search($faction2, $allies)]);
        self::$faction[$faction1]["allies"] = $allies;
        $allies = Utils::real_escape_string(base64_encode(serialize($allies)));
        $faction1E = Utils::real_escape_string($faction1);
        Utils::query("UPDATE faction SET allies = '$allies' WHERE faction='$faction1E'");

        $allies = self::$faction[$faction2]["allies"];
        unset($allies[array_search($faction1, $allies)]);
        self::$faction[$faction2]["allies"] = $allies;
        $allies = Utils::real_escape_string(base64_encode(serialize($allies)));
        $faction2E = Utils::real_escape_string($faction2);
        Utils::query("UPDATE faction SET allies = '$allies' WHERE faction='$faction2E'");
    }

    /**
     * @param string $faction
     * @return int
     */
    public static function getAlliesCount(string $faction): int {
        return count(self::$faction[$faction]["allies"])?? 0;
    }

    /**
     * @param string $faction
     * @return array
     */
    public static function getAllies(string $faction): array {
        return self::$faction[$faction]["allies"]?? [];
    }

    public static function factionMessage(Player $player, string $message) {
        $faction = self::getFaction($player->getName());
        foreach (self::getAllPlayers($faction) as $target) {
            $target = Server::getInstance()->getPlayer($target);
            if ($target instanceof Player) {
                $msg = Utils::getMessage($target, "FACTION_SAY");
                $msg = str_replace(["{player}", "{faction}", "{message}", "{rank}"], [$player->getName(), $faction, $message, self::getRank($player->getName())], $msg);
                $target->sendMessage($msg);
            }
        }
    }

    public static function allyMessage(Player $player, string $message) {
        $faction = self::getFaction($player->getName());
        foreach (self::getAllies($faction) as $ally) {
            if (self::existsFaction($ally)) {
                foreach (self::getAllPlayers($ally) as $target) {
                    $target = Server::getInstance()->getPlayer($target);
                    if ($target instanceof Player) {
                        $msg = Utils::getMessage($target, "ALLY_SAY");
                        $msg = str_replace(["{player}", "{faction}", "{message}", "{rank}"], [$player->getName(), $faction, $message, self::getRank($player->getName())], $msg);
                        $target->sendMessage($msg);
                    }
                }
            }
        }

        foreach (self::getAllPlayers($faction) as $target) {
            $target = Server::getInstance()->getPlayer($target);
            if ($target instanceof Player) {
                $msg = Utils::getMessage($target, "ALLY_SAY");
                $msg = str_replace(["{player}", "{faction}", "{message}", "{rank}"], [$player->getName(), $faction, $message, self::getRank($player->getName())], $msg);
                $target->sendMessage($msg);
            }
        }
    }

    /**
     * @param string $factionName
     * @param string $name
     */
    public static function renameFaction(string $factionName, string $name): void {
        $faction = self::$faction[$factionName];
        self::$faction[$name] = $faction;
        $nameE = Utils::real_escape_string($name);
        $factionNameE = Utils::real_escape_string($factionName);
        Utils::query("UPDATE faction SET faction = '$nameE' WHERE faction='$factionNameE'");

        foreach (self::getAllPlayers($factionName) as $player) {
            self::$player[strtolower($player)]["faction"] = $name;
            $playerE = Utils::real_escape_string($player);
            Utils::query("UPDATE player SET faction = '$nameE' WHERE player='$playerE'");
        }

        if (self::existsHome($factionName)) {
            $home = self::$home[$factionName];
            self::$home[$name] = $home;
            unset(self::$home[$factionName]);
            Utils::query("UPDATE home SET faction = '$nameE' WHERE faction='$factionNameE'");
        }

        $claims = self::$claim[$factionName]?? [];
        $claims[$name] = $claims;
        unset(self::$claim[$factionName]);
        unset(self::$faction[$factionName]);
    }

    /**
     * @param string $faction
     * @return int
     */
    public static function getMoney(string $faction): int {
        return self::$faction[$faction]["money"]?? 0;
    }

    /**
     * @param string $faction
     * @param int $amount
     */
    public static function addMoney(string $faction, int $amount): void {
        self::$faction[$faction]["money"] = self::getMoney($faction) + $amount;
        $factionE = Utils::real_escape_string($faction);
        Utils::query("UPDATE faction SET money = money + $amount WHERE faction='$factionE'");
    }

    /**
     * @param string $faction
     * @param int $amount
     */
    public static function removeMoney(string $faction, int $amount): void {
        if (self::getMoney($faction) - $amount <= 0) {
            self::setMoney($faction, 0);
            return;
        }
        self::$faction[$faction]["money"] = self::getMoney($faction) - $amount;
        $factionE = Utils::real_escape_string($faction);
        Utils::query("UPDATE faction SET money = money - $amount WHERE faction='$factionE'");
    }

    /**
     * @param string $faction
     * @param int $amount
     */
    public static function setMoney(string $faction, int $amount): void {
        self::$faction[$faction]["money"] = $amount;
        $factionE = Utils::real_escape_string($faction);
        Utils::query("UPDATE faction SET money = $amount WHERE faction='$factionE'");
    }

    /**
     * @param Player $player
     * @return string[]
     */
    public static function getMap(Player $player): array {
        $center = $player->getLevel()->getChunkAtPosition($player);
        $height = self::MAP_HEIGHT;
        $width = self::MAP_WIDTH;
        $compass = self::getAsciiCompass($player->getYaw());
        $header = Utils::getIntoConfig("MAP_HEADER");
        $header = str_replace(["{X}", "{Z}"], [$center->getX(), $center->getZ()], $header);
        $map = [$header];
        $legend = [];
        $characterIndex = 0;
        $overflown = false;

        for ($dz = 0; $dz < $height; $dz++) {
            $row = "";
            for ($dx = 0; $dx < $width; $dx++) {
                $chunkX = $center->getX() - ($width / 2) + $dx;
                $chunkZ = $center->getZ() - ($height / 2) + $dz;
                if ($chunkX === $center->getX() && $chunkZ === $center->getZ()) {
                    $row .= self::MAP_KEY_MIDDLE;
                    continue;
                }

                if (self::isInCLaim($player->getLevel(), $chunkX, $chunkZ)) {
                    $faction = self::getFactionClaim($player->getLevel(), $chunkX, $chunkZ);
                    if (($symbol = array_search($faction, $legend)) === false && $overflown) {
                        $row .= self::MAP_KEY_OVERFLOW;
                    } else {
                        if ($symbol === false) $legend[($symbol = self::MAP_KEY_CHARS[$characterIndex++])] = $faction;
                        if ($characterIndex === strlen(self::MAP_KEY_CHARS)) $overflown = true;
                        $row .= self::getMapColor($player, $faction) . $symbol;
                    }
                } else $row .= self::MAP_KEY_WILDERNESS;
            }

            if ($dz <= 2) {
                $row = $compass[$dz] . substr($row, 3 * strlen(self::MAP_KEY_MIDDLE));
            }
            $map[] = $row;
        }

        $map[] = implode(" ", array_map(function (string $character, $faction) use ($player): string {
            return self::getMapColor($player, $faction) . $character . " §f: " . $faction;
        }, array_keys($legend), $legend));
        if ($overflown) $map[] = self::MAP_KEY_OVERFLOW . Utils::getMessage($player, "TOO_MUCH_FACTION");
        return $map;
    }

    /**
     * @param Player $player
     * @param string $faction1
     * @return string
     */
    public static function getMapColor(Player $player, string $faction1): string {
        if (self::isInFaction($player->getName())) {
            $faction2 = self::getFaction($player->getName());
            if ($faction1 !== $faction2) {
                if (!self::areAllies($faction1, $faction2)) {
                    return TextFormat::RED;
                } else return TextFormat::YELLOW;
            } else return TextFormat::GREEN;
        } else return TextFormat::RED;
    }

    /**
     * @param float $degrees
     * @return array
     */
    public static function getAsciiCompass(float $degrees): array {
        $rows = [["NW", "N", "NE"], ["W", "NONE", "E"], ["SW", "S", "SE"]];
        $direction = self::getDirectionsByDegrees($degrees);
        return array_map(function (array $directions) use ($direction): string {
            $row = "";
            foreach ($directions as $d) {
                $row .= ($direction === $d ? self::COLOR_ACTIVE : self::COLOR_INACTIVE) . self::DIRECTIONS[$d];
            }
            return $row;
        }, $rows);
    }

    /**
     * @param float $degrees
     * @return string
     */
    public static function getDirectionsByDegrees(float $degrees): string {
        $degrees = ($degrees - 157) % 360;
        if ($degrees < 0) $degrees += 360;

        return array_keys(self::DIRECTIONS)[(int)floor($degrees / 45)];
    }

    /**
     * @param Player $player
     * @return bool
     */
    public static function hasLanguages(Player $player): bool {
        $name = $player->getName();
        return isset(self::$lang[$name]);
    }

    /**
     * @param Player $player
     * @param string $lang
     */
    public static function setLanguages(Player $player, string $lang): void {
        $name = $player->getName();
        $nameE = Utils::real_escape_string($name);

        if (self::hasLanguages($player)) {
            Utils::query("UPDATE lang SET lang = '$lang' WHERE player='$nameE'");
        } else Utils::query("INSERT INTO lang (player, lang) VALUES ('$nameE', '$lang')");

        self::$lang[$name] = $lang;
    }

    /**
     * @param Player $player
     * @return string
     */
    public static function getLanguages(Player $player): string {
        $name = $player->getName();
        
        if (self::hasLanguages($player)) {
            return self::$lang[$name];
        } else {
            return "EN";
        }
    }

    /**
     * @param string $faction
     * @param string $faction2
     */
    public static function sendWarsInvitation(string $faction, string $faction2): void {
        self::$Warsinvitation[$faction] = [$faction2, time() + 30];

        foreach (self::getAllPlayers($faction) as $player) {
            if (Server::getInstance()->getPlayer($player)) {
                $player = Server::getInstance()->getPlayer($player);
                if ($player instanceof Player) {
                    $player->sendMessage(Utils::getMessage($player, "WARS_INVITE_SUCCESS_TARGET", array($faction2)));
                }
            }
        }
    }

    /**
     * @param string $faction
     */
    public static function acceptWarsInvitation(string $faction): void {
        $faction2 = self::$Warsinvitation[$faction][0];

        foreach (self::getAllPlayers($faction) as $player) {
            if (Server::getInstance()->getPlayer($player)) {
                $player = Server::getInstance()->getPlayer($player);
                if ($player instanceof Player) {
                    $player->sendMessage(Utils::getMessage($player, "WARS_FACTION_BROADCAST", array($faction2)));
                }
            }
        }

        foreach (self::getAllPlayers($faction2) as $player) {
            if (Server::getInstance()->getPlayer($player)) {
                $player = Server::getInstance()->getPlayer($player);
                if ($player instanceof Player) {
                    $player->sendMessage(Utils::getMessage($player, "WARS_FACTION_BROADCAST", array($faction)));
                }
            }
        }
        self::startWars($faction, $faction2);
        unset(self::$Warsinvitation[$faction]);
    }

    /**
     * @param string $faction
     */
    public static function denyWarsInvitation(string $faction): void {
        if (isset(self::$Warsinvitation[$faction])) {
            unset(self::$Warsinvitation[$faction]);
        }
    }

    /**
     * @param string $faction
     * @param string $faction2
     */
    public static function startWars(string $faction, string $faction2): void {
        self::$Wars[$faction] = array("faction" => $faction2, "kills" => 0);
        self::$Wars[$faction2] = array("faction" => $faction, "kills" => 0);

        Main::getInstance()->getScheduler()->scheduleDelayedTask(new WarsTask($faction, $faction2), 20*(int)Utils::getIntoConfig("war_timer"));
    }
}
