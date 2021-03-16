<?php

namespace Ayzrix\SimpleFaction\API;

use Ayzrix\SimpleFaction\Main;
use Ayzrix\SimpleFaction\Utils\MySQL;
use Ayzrix\SimpleFaction\Utils\Utils;
use pocketmine\level\Position;
use pocketmine\Player;
use pocketmine\Server;

class FactionsAPI {

    /** @var array $invitation */
    public static $invitation = [];

    /** @var array $invitationTimeout */
    public static $invitationTimeout = [];

    /**
     * @param Player $player
     * @return bool
     */
    public static function isInFaction(Player $player): bool {
        $name = $player->getName();
        $result = MySQL::getDatabase()->query("SELECT player FROM faction WHERE player='$name';");
        return $result->num_rows > 0 ? true : false;
    }

    /**
     * @param $faction
     * @return bool
     */
    public static function existsFaction($faction): bool {
        $faction = strtolower($faction);
        $result = MySQL::getDatabase()->query("SELECT player FROM faction WHERE lower(faction)='$faction';");
        return $result->num_rows > 0 ? true : false;
    }

    /**
     * @param Player $player
     * @param string $faction
     */
    public static function createFaction(Player $player, string $faction): void {
        $name = $player->getName();
        MySQL::query("INSERT INTO faction (player, faction, role) VALUES ('$name', '$faction', 'Leader')");
        MySQL::query("INSERT INTO power (faction, power) VALUES ('$faction', 0)");
        if (Utils::getIntoConfig("broadcast_message_created") === true) Server::getInstance()->broadcastMessage(Utils::getConfigMessage("FACTION_CREATE_BROADCAST", array($name, $faction)));
    }

    /**
     * @param Player $player
     * @param string $faction
     */
    public static function disbandFaction(Player $player, string $faction): void {
        $name = $player->getName();
        MySQL::query("DELETE FROM faction WHERE faction = '$faction'");
        MySQL::query("DELETE FROM power WHERE faction='$faction'");
        if (Utils::getIntoConfig("broadcast_message_disband") === true) Server::getInstance()->broadcastMessage(Utils::getConfigMessage("FACTION_DISBAND_BROADCAST", array($name, $faction)));
    }

    /**
     * @param Player $player
     * @return string
     */
    public static function getFaction(Player $player): string {
        $name = $player->getName();
        $faction = MySQL::getDatabase()->query("SELECT faction FROM faction WHERE player='$name';");
        $array = $faction->fetch_Array(MYSQLI_ASSOC);
        return $array["faction"]?? "";
    }

    /**
     * @param string $name
     * @return string
     */
    public static function getPlayerFaction(string $name): string {
        $name = strtolower($name);
        $faction = MySQL::getDatabase()->query("SELECT faction FROM faction WHERE lower(player)='$name';");
        $array = $faction->fetch_Array(MYSQLI_ASSOC);
        return $array["faction"]?? "";
    }

    /**
     * @param string $name
     * @return string
     */
    public static function getRank(string $name): string {
        $faction = MySQL::getDatabase()->query("SELECT role FROM faction WHERE player='$name';");
        $array = $faction->fetch_Array(MYSQLI_ASSOC);
        return $array["role"]?? "";
    }

    /**
     * @param string $faction
     * @return int
     */
    public static function getPower(string $faction): int {
        $return = MySQL::getDatabase()->query("SELECT power FROM power WHERE faction='$faction';");
        $array = $return->fetch_Array(MYSQLI_ASSOC);
        return $array["power"]?? 0;
    }

    /**
     * @param string $faction
     * @param int $amount
     */
    public static function addPower(string $faction, int $amount): void {
        MySQL::query("UPDATE power SET power = power + '$amount' WHERE faction='$faction'");
    }

    /**
     * @param string $faction
     * @param int $amount
     */
    public static function removePower(string $faction, int $amount): void {
       if(self::getPower($faction) - $amount <= 0) {
           self::setPower($faction, 0);
           return;
       }
       MySQL::query("UPDATE power SET power = power - '$amount' WHERE faction='$faction'");
    }

    /**
     * @param string $faction
     * @param int $amount
     */
    public static function setPower(string $faction, int $amount): void {
        MySQL::query("UPDATE power SET power = '$amount' WHERE faction='$faction'");
    }

    /**
     * @param string $faction
     * @return array
     */
    public static function getAllPlayers(string $faction): array {
        $res = MySQL::getDatabase()->query("SELECT player FROM faction WHERE faction='$faction'");
        $return = [];
        while ($resultArr = $res->fetch_Array(MYSQLI_ASSOC)) {
            $return[] = $resultArr['player'];
        }
        return $return;
    }

    /**
     * @param string $faction
     * @return string
     */
    public static function getLeader(string $faction): string {
        $return = MySQL::getDatabase()->query("SELECT player FROM faction WHERE faction='$faction' AND role = 'Leader';");
        $array = $return->fetch_Array(MYSQLI_ASSOC);
        return $array['player'];
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
        $faction = strtolower($faction);
        $result = MySQL::getDatabase()->query("SELECT x FROM home WHERE lower(faction)='$faction';");
        return $result->num_rows > 0 ? true : false;
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
        MySQL::query("INSERT INTO home (faction, x, y, z, world) VALUES ('$faction', '$x', '$y', '$z', '$world')");
    }

    /**
     * @param string $faction
     */
    public static function deleteHome(string $faction): void {
        MySQL::query("DELETE FROM home WHERE faction='$faction'");
    }

    /**
     * @param string $faction
     * @return Position
     */
    public static function getHome(string $faction): Position {
        $result = MySQL::getDatabase()->query("SELECT * FROM home WHERE faction='$faction';");
        $array = $result->fetch_Array(MYSQLI_ASSOC);
        return new Position((int)$array['x'], (int)$array['y'], (int)$array['z'], Main::getInstance()->getServer()->getLevelByName($array['world']));
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
        MySQL::query("INSERT INTO claim (faction, x, z, world) VALUES ('$faction', '$chunkX', '$chunkZ', '$world')");
    }

    /**
     * @param Player $player
     * @return bool
     */
    public static function isInClaim(Player $player): bool {
        $chunk = $player->getLevel()->getChunkAtPosition($player);
        $chunkX = $chunk->getX();
        $chunkZ = $chunk->getZ();
        $world = $player->getLevel()->getFolderName();
        $result = MySQL::getDatabase()->query("SELECT * FROM claim WHERE x='$chunkX' AND z='$chunkZ' and world='$world';");
        return $result->num_rows > 0 ? true : false;
    }

    /**
     * @param Player $player
     * @return string
     */
    public static function getFactionClaim(Player $player): string {
        $chunk = $player->getLevel()->getChunkAtPosition($player);
        $chunkX = $chunk->getX();
        $chunkZ = $chunk->getZ();
        $world = $player->getLevel()->getFolderName();
        $result = MySQL::getDatabase()->query("SELECT faction FROM claim WHERE x='$chunkX' AND z='$chunkZ' and world='$world';");
        $array = $result->fetch_array(MYSQLI_ASSOC);
        return $array['faction'];
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
        MySQL::query("DELETE FROM claim WHERE faction='$faction' AND x='$chunkX' AND z='$chunkZ' AND world='$world'");
    }

    /**
     * @param string $faction
     * @return int
     */
    public static function getClaimCount(string $faction): int {
        $result = MySQL::getDatabase()->query("SELECT COUNT(faction) FROM claim where faction='$faction'");
        $array = $result->fetch_array(MYSQLI_ASSOC);
        return (int)$array['COUNT(faction)']?? 0;
    }

    /**
     * @param Player $player
     */
    public static function leaveFaction(Player $player): void {
        $name = $player->getName();
        MySQL::query("DELETE FROM faction WHERE player = '$name'");
    }

    /**
     * @param string $name
     */
    public static function kickFaction(string $name): void {
        $name = strtolower($name);
        MySQL::query("DELETE FROM faction WHERE lower(player) = '$name'");
    }

    /**
     * @param string $name
     */
    public static function promoteFaction(string $name): void {
        $name = strtolower($name);
        MySQL::query("UPDATE faction SET role = 'Officer' WHERE lower(player)='$name'");
    }

    /**
     * @param string $name
     */
    public static function demoteFaction(string $name): void {
        $name = strtolower($name);
        MySQL::query("UPDATE faction SET role = 'Member' WHERE lower(player)='$name'");
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
        MySQL::query("INSERT INTO faction (player, faction, role) VALUES ('$name', '$faction', 'Member')");
        foreach (self::getAllPlayers($faction) as $player) {
            if (Server::getInstance()->getPlayer($player)) {
                $player = Server::getInstance()->getPlayer($player);
                if ($player instanceof Player) {
                    $player->sendMessage(Utils::getConfigMessage("JOIN_FACTION_BROADCAST", array($name)));
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
}