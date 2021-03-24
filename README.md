[![Discord](https://img.shields.io/discord/800828802921529355.svg?label=&logo=discord&logoColor=ffffff&color=7389D8&labelColor=6A7EC2)](https://discord.gg/wuNvKw948n) [![](https://poggit.pmmp.io/shield.api/SimpleFaction)](https://poggit.pmmp.io/p/SimpleFaction) [![](https://poggit.pmmp.io/shield.dl.total/SimpleFaction)](https://poggit.pmmp.io/p/SimpleFaction)
# SimpleFaction

###### Simple faction plugin replacing FactionsPro which is no longer updated.

## Commands

| Command Name   | Command Description                                      | Available for                         |
|----------------|----------------------------------------------------------|---------------------------------------|
| `/f help`      | Allows you to teleport to a player.                      | <font color="#03fc73"> everyone       |
| `/f create`    | Create a faction.                                        | <font color="#03fc73"> everyone       |
| `/f info`      | Get information of a faction.                            | <font color="#03fc73"> everyone       |
| `/f who`       | Get information of a player's faction.                   | <font color="#03fc73"> everyone       |
| `/f accept`    | Accept faction invitation.                               | <font color="#03fc73"> everyone       |
| `/f deny`      | Deny faction invitation.                                 | <font color="#03fc73"> everyone       |
| `/f chat`      | Change your chatting configuration.                      | <font color="#03fc73"> everyone       |
| `/f home`      | Quick way to get to your faction's home.                 | <font color="#03fc73"> everyone       |
| `/f top`       | Shows the top factions.                                  | <font color="#03fc73"> everyone       |
| `/f bank`      | Manage your faction's bank.                              | <font color="#03fc73"> everyone       |
| `/f leave`     | Leave your current faction.                              | <font color="#03fc73"> everyone       |
| `/f map`       | Show the nearby claims.                                  | <font color="#03fc73"> everyone       |
| `/f border`    | Show the chunk border limit.                             | <font color="#03fc73"> everyone       |
| `/f here  `    | Show claims informations.                                | <font color="#03fc73"> everyone       |
| `/f claim`     | Claim a chunk.                                           | <font color="#fca503"> officers       |
| `/f unclaim`   | Unclaim your claim.                                      | <font color="#fca503"> officers       |
| `/f invite`    | Invite a player into your faction.                       | <font color="#fca503"> officers       |
| `/f kick`      | Kicks a player from your faction.                        | <font color="#fca503"> officers       |
| `/f sethome`   | Sets your faction home.                                  | <font color="#fca503"> officers       |
| `/f delhome`   | Removes your faction home.                               | <font color="#fca503"> officers       |
| `/f delete`    | Delete your faction.                                     | <font color="#1589F0"> leader         |
| `/f allies`    | Manage your faction's alliance.                          | <font color="#1589F0"> leader         |
| `/f promote`   |  Promote a member.                                       | <font color="#1589F0"> leader         |
| `/f demote`    | Demote an officer.                                       | <font color="#1589F0"> leader         |
| `/f transfer`  | Make a new player the leader.                            | <font color="#1589F0"> leader         |
| `/f admin`     | Administrative commands.                                 | <font color="red"> staff              | 

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

## Additional plugins
| Name              | Usage                         | Download                                                          |
|-------------------|-------------------------------|-------------------------------------------------------------------| 
| PureChat          | Chat integration              | [Download](https://poggit.pmmp.io/r/118622/PureChat_dev-1.phar)   |
| ScoreHUD          | Scoreboard integration        | [Download](https://poggit.pmmp.io/p/ScoreHud)                     |
| EssentialsFaction | Chat & Scoreboard integration | [Download](https://github.com/Zoumi-Dev/FacEssential)             |
| Scoreboard        | Scoreboard integration        | [Download](https://github.com/AyzrixYTB/Scoreboard)               |
| EconomyAPI        | Bank system                   | [Download](https://poggit.pmmp.io/p/EconomyAPI)                   |

## Translators
- **English** - @Ayzrix & Se7en-dev.
- **French** - @Ayzrix.
- **Spanish** - @Santi.
- **German** - Soon.

## Config
```
#     _____ _                 _      ______         _   _
#    / ____(_)               | |    |  ____|       | | (_)
#   | (___  _ _ __ ___  _ __ | | ___| |__ __ _  ___| |_ _  ___  _ __
#    \___ \| | '_ ` _ \| '_ \| |/ _ \  __/ _` |/ __| __| |/ _ \| '_ \
#    ____) | | | | | | | |_) | |  __/ | | (_| | (__| |_| | (_) | | | |
#   |_____/|_|_| |_| |_| .__/|_|\___|_|  \__,_|\___|\__|_|\___/|_| |_|
#                      | |
#                      |_|
#

# SQLITE | MYSQL
PROVIDER: "SQLITE"

# Edit this only if 'PROVIDER' is on MYSQL
mysql_address: "SERVER ADDRESS"
mysql_user: "USER"
mysql_password: "YOUR PASSWORD"
mysql_db: "YOUR DB"

# Broadcast created system.
broadcast_message_created: true
broadcast_message_disband: true

# Activate or deactivate the bank system (/f bank)
economy_system: false

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

# Default power in a faction
default_power: 0

# Powers required for each claim.
# One - = 1 claim. If you setup 2 -, the max claim number would be 2.
# Usage: - POWERS NEEDED
claims:
  - 100
  - 500
  - 800
  - 1000
  - 2500
  - 5000

# Prefix usable in languages. {prefix}
PREFIX: "§6[§fSimpleFaction§6]§f"
PLAYER_ONLY: "{prefix} §cThis command can't be used in the console !"
MAP_HEADER: "§6--------------------(§f{X}§6, §f{Z}§6)--------------------"

# Banned faction names
banned_names: ["op", "staff", "admin", "fuck", "shit"]
```