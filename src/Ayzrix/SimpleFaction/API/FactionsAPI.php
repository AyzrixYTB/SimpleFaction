<?php

namespace Ayzrix\SimpleFaction\API;

use Ayzrix\SimpleFaction\Main;
use Ayzrix\SimpleFaction\Utils\Provider;
use Ayzrix\SimpleFaction\Utils\Utils;
use pocketmine\level\Position;
use pocketmine\Player;
use pocketmine\Server;

class FactionsAPI {

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

    /**
     * @param Player $player
     * @return bool
     */
    public static function isInFaction(Player $player): bool {
        $name = $player->getName();
        $result = Provider::getDatabase()->query("SELECT player FROM faction WHERE player='$name';");
        if (Utils::getProvider() === "mysql") return $result->num_rows > 0 ? true : false;
        $return = $result->fetchArray(Utils::getAssoc());
        return empty($return) === false;
    }

    /**
     * @param $faction
     * @return bool
     */
    public static function existsFaction($faction): bool {
        $faction = strtolower($faction);
        $result = Provider::getDatabase()->query("SELECT player FROM faction WHERE lower(faction)='$faction';");
        if (Utils::getProvider() === "mysql") return $result->num_rows > 0 ? true : false;
        $return = $result->fetchArray(Utils::getAssoc());
        return empty($return) === false;
    }

    /**
     * @param Player $player
     * @param string $faction
     */
    public static function createFaction(Player $player, string $faction): void {
        $name = $player->getName();
        Provider::query("INSERT INTO faction (player, faction, role) VALUES ('$name', '$faction', 'Leader')");
        Provider::query("INSERT INTO power (faction, power) VALUES ('$faction', 0)");
        if (Utils::getIntoConfig("broadcast_message_created") === true) Server::getInstance()->broadcastMessage(Utils::getConfigMessage("FACTION_CREATE_BROADCAST", array($name, $faction)));
    }

    /**
     * @param Player $player
     * @param string $faction
     */
    public static function disbandFaction(Player $player, string $faction): void {
        $name = $player->getName();
        Provider::query("DELETE FROM faction WHERE faction = '$faction'");
        Provider::query("DELETE FROM power WHERE faction='$faction'");
        Provider::query("DELETE FROM home WHERE faction='$faction'");
        Provider::query("DELETE FROM claim WHERE faction='$faction'");
        Provider::query("DELETE FROM allies WHERE faction1='$faction' OR faction2='$faction'");
        if (Utils::getIntoConfig("broadcast_message_disband") === true) Server::getInstance()->broadcastMessage(Utils::getConfigMessage("FACTION_DISBAND_BROADCAST", array($name, $faction)));
    }

    /**
     * @param Player $player
     * @return string
     */
    public static function getFaction(Player $player): string {
        $name = $player->getName();
        $faction = Provider::getDatabase()->query("SELECT faction FROM faction WHERE player='$name';");
        if (Utils::getProvider() === "mysql") {
            $array = $faction->fetch_Array(Utils::getAssoc());
        } else {
            $array = $faction->fetchArray(Utils::getAssoc());
        }
        return $array["faction"]?? "";
    }

    /**
     * @param string $name
     * @return string
     */
    public static function getPlayerFaction(string $name): string {
        $name = strtolower($name);
        $faction = Provider::getDatabase()->query("SELECT faction FROM faction WHERE lower(player)='$name';");
        if (Utils::getProvider() === "mysql") {
            $array = $faction->fetch_Array(Utils::getAssoc());
        } else {
            $array = $faction->fetchArray(Utils::getAssoc());
        }
        return $array["faction"]?? "";
    }

    /**
     * @param string $name
     * @return string
     */
    public static function getRank(string $name): string {
        $faction = Provider::getDatabase()->query("SELECT role FROM faction WHERE player='$name';");
        if (Utils::getProvider() === "mysql") {
            $array = $faction->fetch_Array(Utils::getAssoc());
        } else {
            $array = $faction->fetchArray(Utils::getAssoc());
        }
        return $array["role"]?? "";
    }

    /**
     * @param string $faction
     * @return int
     */
    public static function getPower(string $faction): int {
        $return = Provider::getDatabase()->query("SELECT power FROM power WHERE faction='$faction';");
        if (Utils::getProvider() === "mysql") {
            $array = $return->fetch_Array(Utils::getAssoc());
        } else {
            $array = $return->fetchArray(Utils::getAssoc());
        }
        return $array["power"]?? 0;
    }

    /**
     * @param string $faction
     * @param int $amount
     */
    public static function addPower(string $faction, int $amount): void {
        Provider::query("UPDATE power SET power = power + '$amount' WHERE faction='$faction'");
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
        Provider::query("UPDATE power SET power = power - '$amount' WHERE faction='$faction'");
    }

    /**
     * @param string $faction
     * @param int $amount
     */
    public static function setPower(string $faction, int $amount): void {
        Provider::query("UPDATE power SET power = '$amount' WHERE faction='$faction'");
    }

    /**
     * @param string $faction
     * @return array
     */
    public static function getAllPlayers(string $faction): array {
        $res = Provider::getDatabase()->query("SELECT player FROM faction WHERE faction='$faction'");
        $return = [];

        if (Utils::getProvider() === "mysql") {
            while ($resultArr = $res->fetch_Array(Utils::getAssoc())) {
                $return[] = $resultArr['player'];
            }
        } else {
            while ($resultArr = $res->fetchArray(Utils::getAssoc())) {
                $return[] = $resultArr['player'];
            }
        }
        return $return;
    }

    /**
     * @param string $faction
     * @return string
     */
    public static function getLeader(string $faction): string {
        $return = Provider::getDatabase()->query("SELECT player FROM faction WHERE faction='$faction' AND role = 'Leader';");
        if (Utils::getProvider() === "mysql") {
            $array = $return->fetch_Array(Utils::getAssoc());
        } else {
            $array = $return->fetchArray(Utils::getAssoc());
        }
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
        $result = Provider::getDatabase()->query("SELECT x FROM home WHERE lower(faction)='$faction';");
        if (Utils::getProvider() === "mysql") return $result->num_rows > 0 ? true : false;
        $return = $result->fetchArray(Utils::getAssoc());
        return empty($return) === false;
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
        Provider::query("INSERT INTO home (faction, x, y, z, world) VALUES ('$faction', '$x', '$y', '$z', '$world')");
    }

    /**
     * @param string $faction
     */
    public static function deleteHome(string $faction): void {
        Provider::query("DELETE FROM home WHERE faction='$faction'");
    }

    /**
     * @param string $faction
     * @return Position
     */
    public static function getHome(string $faction): Position {
        $result = Provider::getDatabase()->query("SELECT * FROM home WHERE faction='$faction';");
        if (Utils::getProvider() === "mysql") {
            $array = $result->fetch_Array(Utils::getAssoc());
        } else {
            $array = $result->fetchArray(Utils::getAssoc());
        }
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
        Provider::query("INSERT INTO claim (faction, x, z, world) VALUES ('$faction', '$chunkX', '$chunkZ', '$world')");
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
        $result = Provider::getDatabase()->query("SELECT * FROM claim WHERE x='$chunkX' AND z='$chunkZ' and world='$world';");
        if (Utils::getProvider() === "mysql") return $result->num_rows > 0 ? true : false;
        $return = $result->fetchArray(Utils::getAssoc());
        return empty($return) === false;
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
        $result = Provider::getDatabase()->query("SELECT faction FROM claim WHERE x='$chunkX' AND z='$chunkZ' and world='$world';");
        if (Utils::getProvider() === "mysql") {
            $array = $result->fetch_Array(Utils::getAssoc());
        } else {
            $array = $result->fetchArray(Utils::getAssoc());
        }
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
        Provider::query("DELETE FROM claim WHERE faction='$faction' AND x='$chunkX' AND z='$chunkZ' AND world='$world'");
    }

    /**
     * @param string $faction
     * @return int
     */
    public static function getClaimCount(string $faction): int {
        $result = Provider::getDatabase()->query("SELECT COUNT(faction) FROM claim where faction='$faction'");
        if (Utils::getProvider() === "mysql") {
            $array = $result->fetch_Array(Utils::getAssoc());
        } else {
            $array = $result->fetchArray(Utils::getAssoc());
        }
        return (int)$array['COUNT(faction)']?? 0;
    }

    /**
     * @param Player $player
     */
    public static function leaveFaction(Player $player): void {
        $name = $player->getName();
        Provider::query("DELETE FROM faction WHERE player = '$name'");
    }

    /**
     * @param string $name
     */
    public static function kickFaction(string $name): void {
        $name = strtolower($name);
        Provider::query("DELETE FROM faction WHERE lower(player) = '$name'");
    }

    /**
     * @param string $name
     */
    public static function promoteFaction(string $name): void {
        $name = strtolower($name);
        Provider::query("UPDATE faction SET role = 'Officer' WHERE lower(player)='$name'");
    }

    /**
     * @param string $name
     */
    public static function demoteFaction(string $name): void {
        $name = strtolower($name);
        Provider::query("UPDATE faction SET role = 'Member' WHERE lower(player)='$name'");
    }

    /**
     * @param string $name
     * @param string $faction
     */
    public static function transferFaction(string $name, string $faction): void {
        $name = strtolower($name);
        Provider::query("UPDATE faction SET role = 'Leader' WHERE lower(player)='$name'");
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
        Provider::query("INSERT INTO faction (player, faction, role) VALUES ('$name', '$faction', 'Member')");
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

    /**
     * @return array
     */
    public static function getAllPowers(): array {
        $result = Provider::getDatabase()->query("SELECT * FROM power ORDER BY power;");
        $return = [];
        if (Utils::getProvider() === "mysql") {
            foreach($result->fetch_all() as $val){
                $return[$val[0]] = (int)$val[1];
            }
            $result->close();
        } else {
            while ($val = $result->fetchArray(Utils::getAssoc())) {
                $return[$val['faction']] = (int)$val['power'];
            }
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
        $message = Utils::getConfigMessage("TOP_FACTION_HEADER", array($page, $maxpage));

        foreach ($factions as $faction => $power) {
            if ($i >= $fintop) break;
            if ($i < $deptop) {
                $i++;
                continue;
            }

            $memberscount = count(self::getAllPlayers($faction));
            $line = Utils::getIntoConfig("TOP_FACTION_LINE");
            $line = str_replace("{index}", $i, $line);
            $line = str_replace("{faction}", $faction, $line);
            $line = str_replace("{power}", $power, $line);
            $line = str_replace("{members}", $memberscount, $line);
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
                    $player->sendMessage(Utils::getConfigMessage("ALLIES_INVITE_SUCESS_TARGET", array($faction2)));
                }
            }
        }
    }

    /**
     * @param string $faction
     */
    public static function acceptAlliesInvitation(string $faction): void {
        $faction2 = self::$Alliesinvitation[$faction];
        Provider::query("INSERT INTO allies (faction1, faction2) VALUES ('$faction', '$faction2')");
        foreach (self::getAllPlayers($faction) as $player) {
            if (Server::getInstance()->getPlayer($player)) {
                $player = Server::getInstance()->getPlayer($player);
                if ($player instanceof Player) {
                    $player->sendMessage(Utils::getConfigMessage("NEW_ALLIANCE_FACTION_BROADCAST", array($faction2)));
                }
            }
        }

        foreach (self::getAllPlayers($faction2) as $player) {
            if (Server::getInstance()->getPlayer($player)) {
                $player = Server::getInstance()->getPlayer($player);
                if ($player instanceof Player) {
                    $player->sendMessage(Utils::getConfigMessage("NEW_ALLIANCE_FACTION_BROADCAST", array($faction)));
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
        $result = Provider::getDatabase()->query("SELECT ID FROM allies WHERE (faction1 = '$faction1' AND faction2 = '$faction2') OR (faction1 = '$faction2' AND faction2 = '$faction1');");
        if (Utils::getProvider() === "mysql") return $result->num_rows > 0 ? true : false;
        $return = $result->fetchArray(Utils::getAssoc());
        return empty($return) === false;
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
                    $player->sendMessage(Utils::getConfigMessage("ALLIES_REMOVE_SUCESS", array($faction2)));
                }
            }
        }

        foreach (self::getAllPlayers($faction2) as $player) {
            if (Server::getInstance()->getPlayer($player)) {
                $player = Server::getInstance()->getPlayer($player);
                if ($player instanceof Player) {
                    $player->sendMessage(Utils::getConfigMessage("ALLIES_REMOVE_SUCESS", array($faction1)));
                }
            }
        }
        Provider::query("DELETE FROM allies WHERE (faction1='$faction1' AND faction2='$faction2') OR (faction1='$faction2' AND faction2='$faction1')");
    }

    /**
     * @param string $faction
     * @return int
     */
    public static function getAlliesCount(string $faction): int {
        $result = Provider::getDatabase()->query("SELECT COUNT(ID) FROM allies WHERE faction1='$faction' OR faction2='$faction'");
        if (Utils::getProvider() === "mysql") {
            $array = $result->fetch_Array(Utils::getAssoc());
        } else {
            $array = $result->fetchArray(Utils::getAssoc());
        }
        return (int)$array['COUNT(ID)']?? 0;
    }

    /**
     * @param string $faction
     * @return array
     */
    public static function getAllies(string $faction): array {
        $result = Provider::getDatabase()->query("SELECT faction2 FROM allies WHERE faction1='$faction'");
        $return = [];

        if (Utils::getProvider() === "mysql") {
            while ($resultArr = $result->fetch_Array(Utils::getAssoc())) $return[] = $resultArr['faction2'];
        } else while ($resultArr = $result->fetchArray(Utils::getAssoc())) $return[] = $resultArr['faction2'];

        $result = Provider::getDatabase()->query("SELECT faction1 FROM allies WHERE faction2='$faction'");

        if (Utils::getProvider() === "mysql") {
            while ($resultArr = $result->fetch_Array(Utils::getAssoc())) $return[] = $resultArr['faction1'];
        } else while ($resultArr = $result->fetchArray(Utils::getAssoc())) $return[] = $resultArr['faction1'];

        return $return;
    }

    public static function factionMessage(Player $player, string $message) {
        $faction = self::getFaction($player);
        foreach (self::getAllPlayers($faction) as $target) {
            $target = Server::getInstance()->getPlayer($target);
            if ($target instanceof Player) {
                $msg = Utils::getConfigMessage("FACTION_SAY");
                $msg = str_replace("{player}", $player->getName(), $msg);
                $msg = str_replace("{faction}", $faction, $msg);
                $msg = str_replace("{message}", $message, $msg);
                $msg = str_replace("{rank}", self::getRank($player->getName()), $msg);
                $target->sendMessage($msg);
            }
        }
    }

    public static function allyMessage(Player $player, string $message) {
        $faction = self::getFaction($player);
        foreach (self::getAllies($faction) as $ally) {
            if (self::existsFaction($ally)) {
                foreach (self::getAllPlayers($ally) as $target) {
                    $target = Server::getInstance()->getPlayer($target);
                    if ($target instanceof Player) {
                        $msg = Utils::getConfigMessage("ALLY_SAY");
                        $msg = str_replace("{player}", $player->getName(), $msg);
                        $msg = str_replace("{faction}", $faction, $msg);
                        $msg = str_replace("{message}", $message, $msg);
                        $msg = str_replace("{rank}", self::getRank($player->getName()), $msg);
                        $target->sendMessage($msg);
                    }
                }
            }
        }

        foreach (self::getAllPlayers($faction) as $target) {
            $target = Server::getInstance()->getPlayer($target);
            if ($target instanceof Player) {
                $msg = Utils::getConfigMessage("ALLY_SAY");
                $msg = str_replace("{player}", $player->getName(), $msg);
                $msg = str_replace("{faction}", $faction, $msg);
                $msg = str_replace("{message}", $message, $msg);
                $msg = str_replace("{rank}", self::getRank($player->getName()), $msg);
                $target->sendMessage($msg);
            }
        }
    }
}