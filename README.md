[![Discord](https://img.shields.io/discord/800828802921529355.svg?label=&logo=discord&logoColor=ffffff&color=7389D8&labelColor=6A7EC2)](https://discord.gg/ruBKMD9a9J) [![](https://poggit.pmmp.io/shield.api/SimpleFaction)](https://poggit.pmmp.io/p/SimpleFaction) [![](https://poggit.pmmp.io/shield.dl.total/SimpleFaction)](https://poggit.pmmp.io/p/SimpleFaction)
# SimpleFaction

###### Simple faction plugin replacing FactionsPro which is no longer updated.

## Commands

| Command Name   | Command Description                                      | Available for                         |
|----------------|----------------------------------------------------------|---------------------------------------|
| `/f help`      | Faction help menu.                                       | <font color="#03fc73"> everyone       |
| `/f create`    | Create a faction.                                        | <font color="#03fc73"> everyone       |
| `/f info`      | Get information of a faction.                            | <font color="#03fc73"> everyone       |
| `/f who`       | Get information of a player's faction.                   | <font color="#03fc73"> everyone       |
| `/f accept`    | Accept a faction invitation.                             | <font color="#03fc73"> everyone       |
| `/f deny`      | Deny a faction invitation.                               | <font color="#03fc73"> everyone       |
| `/f chat`      | Change your chat configuration.                          | <font color="#03fc73"> everyone       |
| `/f home`      | Teleport to your faction's home                          | <font color="#03fc73"> everyone       |
| `/f top`       | Shows the top factions.                                  | <font color="#03fc73"> everyone       |
| `/f bank`      | Manage your faction's bank.                              | <font color="#03fc73"> everyone       |
| `/f leave`     | Leave your current faction.                              | <font color="#03fc73"> everyone       |
| `/f map`       | Show the nearby claims.                                  | <font color="#03fc73"> everyone       |
| `/f border`    | Show the chunk border limit.                             | <font color="#03fc73"> everyone       |
| `/f here  `    | Show claims information.                                 | <font color="#03fc73"> everyone       |
| `/f claim`     | Claim a chunk.                                           | <font color="#fca503"> officers       |
| `/f unclaim`   | Unclaim a chunk.                                         | <font color="#fca503"> officers       |
| `/f invite`    | Invite a player to your faction.                         | <font color="#fca503"> officers       |
| `/f kick`      | Kick a player from your faction.                         | <font color="#fca503"> officers       |
| `/f sethome`   | Set your faction's home.                                 | <font color="#fca503"> officers       |
| `/f delhome`   | Delete your faction's home.                              | <font color="#fca503"> officers       |
| `/f war`       | Manage faction wars.                                     | <font color="#fca503"> officers       |
| `/f delete`    | Delete your faction.                                     | <font color="#1589F0"> leader         |
| `/f allies`    | Manage your faction alliances.                           | <font color="#1589F0"> leader         |
| `/f promote`   | Promote a member.                                        | <font color="#1589F0"> leader         |
| `/f demote`    | Demote an officer.                                       | <font color="#1589F0"> leader         |
| `/f transfer`  | Transfer your leader status.                             | <font color="#1589F0"> leader         |
| `/f admin`     | Administrative commands.                                 | <font color="red"> staff (simplefaction.admin)             |

## Future additions

| Name              | Description                                               | Type      |
|-------------------|-----------------------------------------------------------|-----------|
| `Safezone system` | Allows you to create safezones that cannot be claimed.    | system    |
| `UI System`       | Add an UI extension plugin.                               | extension |

## Features

| Feature                   | SimpleFaction   | FactionsPro| PiggyFactions|
|---------------------------|-----------------|------------|--------------|
| `SQLite3 Support`         | ✔               | ✔         | ✔            |
| `MySQL Support`           | ✔               | ❌         | ✔            |
| `Async Queries`           | ✔               | ❌         | ✔            |
| `Editable message`        | ✔               | ❌         | ✔            |
| `Multiple claim`          | ✔               | ❌         | ✔            |                  
| `Multi-Language Support`  | ✔               | ❌         | ✔            |
| `Economy System`          | ✔               | ❌         | ✔            |
| `EconomyAPI Support`      | ✔               | ❌         | ✔            |
| `ScoreHUD Support`        | ✔               | ❌         | ❌            |
| `Simplicity`              | ✔               | ✔         | ❌            |
| `Floating Text`           | ✔               | ❌         | ❌            |

## Additional plugins
| Name              | Usage                         | Download                                                          |
|-------------------|-------------------------------|-------------------------------------------------------------------|
| PureChat          | Chat integration              | [Download](https://poggit.pmmp.io/r/119566/PureChat_dev-2.phar)   |
| ScoreHUD          | Scoreboard integration        | [Download](https://poggit.pmmp.io/p/ScoreHud)                     |
| FacEssential      | Chat & Scoreboard integration | [Download](https://github.com/Zoumi-Dev/FacEssential)             |
| Scoreboard        | Scoreboard integration        | [Download](https://poggit.pmmp.io/r/119565/Scoreboard_dev-5.phar) |
| EconomyAPI        | Bank system                   | [Download](https://poggit.pmmp.io/p/EconomyAPI)                   |
| Rank              | Chat integration              | [Download](https://github.com/Virvolta/Rank)                      |

## Contributors
- @Se7en-dev
- @max-xoo

## Translators
- **English** - @Ayzrix, Se7en-dev & UnEnanoMas.
- **French** - @Ayzrix.
- **Spanish** - @Santi.

## Config
```yaml
#     _____ _                 _      ______         _   _
#    / ____(_)               | |    |  ____|       | | (_)
#   | (___  _ _ __ ___  _ __ | | ___| |__ __ _  ___| |_ _  ___  _ __
#    \___ \| | '_ ` _ \| '_ \| |/ _ \  __/ _` |/ __| __| |/ _ \| '_ \
#    ____) | | | | | | | |_) | |  __/ | | (_| | (__| |_| | (_) | | | |
#   |_____/|_|_| |_| |_| .__/|_|\___|_|  \__,_|\___|\__|_|\___/|_| |_|
#                      | |
#                      |_|
#

# Database provider (SQLITE | MYSQL)
PROVIDER: "SQLITE"

# Edit this only if 'PROVIDER' is on MYSQL
mysql_address: "SERVER ADDRESS"
mysql_user: "USER"
mysql_password: "YOUR PASSWORD"
mysql_db: "YOUR DB"

# Broadcast a message on faction creation / deletion
broadcast_message_created: true
broadcast_message_disband: true

# Activate or deactivate entering and leaving messages.
entering_leaving: true

# Activate or deactivate the bank system (/f bank)
economy_system: false

# Activate or deactivate the war system (/f war)
war_system: true

# War duration in seconds
war_timer: 300

# Faction name limit.
min_faction_name_lenght: 3
max_faction_name_lenght: 16

# Power gained per kill and lost per death.
power_gain_per_kill: 1
power_lost_per_death: 1

# Worlds where the players can claim.
faction_worlds: ["world"]

# TRUE = ENABLE | FALSE = DISABLE
faction_pvp: false
alliance_pvp: false

# Time in seconds before invitations expire
invitation_expire_time: 30
allies_invitation_expire_time: 60

# Max members in a faction.
faction_max_members: 20

# Max allies in a faction.
faction_max_allies: 2

# Default power of factions
default_power: 0

# Select the claim mode you want (CUSTOM | MULTIPLICATIVE | ADDITIVE)
claim-mode: "CUSTOM"

# CUSTOM : Define the power needed for each individual claim.
# Maximum claim number is defined by the amount of lines you add in the list below.
# (1 line = 1 claim)

# Edit this part ONLY if you select CUSTOM as the claim mode.
custom_claims:
  - 100
  - 500
  - 800
  - 1000
  - 2500
  - 5000

# MULTIPLICATIVE : Define the power needed for each claim with a multiplicative factor.
# Claim cost = starting claim price*(multiplicative factor**claim count)
# EXAMPLE with starting claim price = 100, multiplicative factor = 2 :
# 1st claim costs 100*(2**0) = 100, 2nd claim costs 100*(2**1) = 200, 3rd claim 100*(2**2) = 400 etc...

# ADDITIVE : Define the power needed for each claim with an additive factor.
# Claim cost = starting claim price+(additive factor*claim count)
# EXAMPLE with starting claim price = 100, additive factor = 500 :
# 1st claim costs 100+(500*0) = 100, 2nd claim costs 100+(500*1) = 600, 3rd claim 100+(500*2) = 1100 etc...

# Edit this part ONLY if you select MULTIPLICATIVE or ADDITIVE as the claim mode.
starting_claim_price: 100
factor: 2 # multiplicative or additive factor (depending on the claim mode you chose)
max_claims: 5

# Claims of the same faction have to be next to each other (first claim can be anywhere) (true|false)
adjacent_claims: false

# Prefix usable in languages. {prefix}
PREFIX: "§6[§fSimpleFaction§6]§f"
PLAYER_ONLY: "{prefix} §cThis command can't be used in the console !"
MAP_HEADER: "§6--------------------(§f{X}§6, §f{Z}§6)--------------------"

# Banned faction names
banned_names: ["op", "staff", "admin", "fuck", "shit"]

zones_colors:
  "Wilderness": "§d"
  "Own-Faction": "§a"
  "Allies": "§e"
  "Enemies": "§c"

# RGB Colors
border_colors:
  "Wilderness": "255, 0, 180"
  "Own-Faction": "0, 255, 0"
  "Allies": "255, 255, 0"
  "Enemies": "255, 0, 0"

# Top floating text
# true | false
floating_text: false
# "X:Y:Z:WORLD"
floating_text_coordinates: "0:100:0:world"
floating_text_title: "§6- §fTop 10 factions with the most power §6-"
# TAGS: {number} {faction} {power} {members} {bank}
# Sort by power.
floating_text_line: "§6#{number} §f- §6{faction} §fwith §6{power} §fpower(s) and §6{bank} §fmoney"
# Line limit
floating_text_limit: 10
```
