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
use Ayzrix\SimpleFaction\Utils\Provider;
use Ayzrix\SimpleFaction\Utils\Utils;
use pocketmine\level\Level;
use pocketmine\level\Position;
use pocketmine\Player;
use pocketmine\Server;
use pocketmine\utils\TextFormat;

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

    /** @var array $map */
    public static $map = [];

    /** @var array $border */
    public static $border = [];

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
     * @param string $faction
     * @return bool
     */
    public static function existsFaction(string $faction): bool {
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
        Provider::query("INSERT INTO bank (faction, money) VALUES ('$faction', 0)");
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
        Provider::query("DELETE FROM faction WHERE faction = '$faction'");
        Provider::query("DELETE FROM power WHERE faction='$faction'");
        Provider::query("DELETE FROM home WHERE faction='$faction'");
        Provider::query("DELETE FROM claim WHERE faction='$faction'");
        Provider::query("DELETE FROM allies WHERE faction1='$faction' OR faction2='$faction'");
        Provider::query("DELETE FROM bank WHERE faction='$faction'");
        if (Utils::getIntoConfig("broadcast_message_disband") === true) {
            foreach (Server::getInstance()->getOnlinePlayers() as $player) {
                if ($player instanceof Player) {
                    $player->sendMessage(Utils::getMessage($player, "FACTION_DISBAND_BROADCAST", array($name, $faction)));
                }
            }
        }
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
     * @param Level $level
     * @param int $chunkX
     * @param int $chunkZ
     * @return bool
     */
    public static function isInClaim(Level $level, int $chunkX, int $chunkZ): bool {
        $world = $level->getFolderName();
        $result = Provider::getDatabase()->query("SELECT * FROM claim WHERE x='$chunkX' AND z='$chunkZ' and world='$world';");
        if (Utils::getProvider() === "mysql") return $result->num_rows > 0 ? true : false;
        $return = $result->fetchArray(Utils::getAssoc());
        return empty($return) === false;
    }

    /**
     * @param Level $level
     * @param int $chunkX
     * @param int $chunkZ
     * @return string
     */
    public static function getFactionClaim(Level $level, int $chunkX, int $chunkZ): string {
        $world = $level->getFolderName();
        $result = Provider::getDatabase()->query("SELECT faction FROM claim WHERE x='$chunkX' AND z='$chunkZ' and world='$world';");
        if (Utils::getProvider() === "mysql") {
            $array = $result->fetch_Array(Utils::getAssoc());
        } else $array = $result->fetchArray(Utils::getAssoc());
        return $array['faction']?? "";
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
        } else $array = $result->fetchArray(Utils::getAssoc());
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
        Provider::query("INSERT INTO allies (faction1, faction2) VALUES ('$faction', '$faction2')");
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
                $msg = Utils::getMessage($target, "FACTION_SAY");
                $msg = str_replace(["{player}", "{faction}", "{message}", "{rank}"], [$player->getName(), $faction, $message, self::getRank($player->getName())], $msg);
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
     * @param string $faction
     * @param string $name
     */
    public static function renameFaction(string $faction, string $name): void {
        Provider::query("UPDATE faction SET faction='$name' WHERE faction='$faction'");
        Provider::query("UPDATE power SET faction='$name' WHERE faction='$faction'");
        Provider::query("UPDATE home SET faction='$name' WHERE faction='$faction'");
        Provider::query("UPDATE claim SET faction='$name' WHERE faction='$faction'");
        Provider::query("UPDATE allies SET faction1='$name' WHERE faction1='$faction'");
        Provider::query("UPDATE allies SET faction2='$name' WHERE faction2='$faction'");
    }

    /**
     * @param string $faction
     * @return int
     */
    public static function getMoney(string $faction): int {
        $return = Provider::getDatabase()->query("SELECT money FROM bank WHERE faction='$faction';");
        if (Utils::getProvider() === "mysql") {
            $array = $return->fetch_Array(Utils::getAssoc());
        } else {
            $array = $return->fetchArray(Utils::getAssoc());
        }
        return $array["money"]?? 0;
    }

    /**
     * @param string $faction
     * @param int $amount
     */
    public static function addMoney(string $faction, int $amount): void {
        Provider::query("UPDATE bank SET money = money + '$amount' WHERE faction='$faction'");
    }

    /**
     * @param string $faction
     * @param int $amount
     */
    public static function removeMoney(string $faction, int $amount): void {
        if(self::getMoney($faction) - $amount <= 0) {
            self::setMoney($faction, 0);
            return;
        }
        Provider::query("UPDATE bank SET money = money - '$amount' WHERE faction='$faction'");
    }

    /**
     * @param string $faction
     * @param int $amount
     */
    public static function setMoney(string $faction, int $amount): void {
        Provider::query("UPDATE bank SET money = '$amount' WHERE faction='$faction'");
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
     * @param $faction1
     * @return string
     */
    public static function getMapColor(Player $player, $faction1): string {
        if (self::isInFaction($player)) {
            $faction2 = self::getFaction($player);
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
}