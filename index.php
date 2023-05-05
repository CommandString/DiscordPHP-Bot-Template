<?php

# ______   _____  _     _  _____       ______   _____  _______
# |     \ |_____] |_____| |_____]      |_____] |     |    |
# |_____/ |       |     | |            |_____] |_____|    |
# _______ _______ _______  _____         _______ _______ _______
#    |    |______ |  |  | |_____] |      |_____|    |    |______
#    |    |______ |  |  | |       |_____ |     |    |    |______

use Discord\Discord;
use Discord\WebSockets\Intents;
use Common\Env;

require_once __DIR__ . "/vendor/autoload.php";

# _______ __   _ _    _ _____  ______  _____  __   _ _______ _______ __   _ _______
# |______ | \  |  \  /    |   |_____/ |     | | \  | |  |  | |______ | \  |    |
# |______ |  \_|   \/   __|__ |    \_ |_____| |  \_| |  |  | |______ |  \_|    |

$env = Env::createFromEnv(__DIR__ . "/.env");

# ______  _____ _______ _______  _____   ______ ______
# |     \   |   |______ |       |     | |_____/ |     \
# |_____/ __|__ ______| |_____  |_____| |    \_ |_____/

$env->discord = new Discord([
    "token" => $env->TOKEN,
    "intents" => Intents::getDefaultIntents()
]);

# _______  _____  _______ _______ _______ __   _ ______  _______
# |       |     | |  |  | |  |  | |_____| | \  | |     \ |______
# |_____  |_____| |  |  | |  |  | |     | |  \_| |_____/ ______|

$env->commands = [
    Commands\Ping::class,
    Commands\Guild::class
];

# _______ _    _ _______ __   _ _______ _______
# |______  \  /  |______ | \  |    |    |______
# |______   \/   |______ |  \_|    |    ______|

$env->events = [
    Events\Init::class
];

# _____ __   _ _______ _______  ______ _______ _______ _______ _____  _____  __   _ _______
#   |   | \  |    |    |______ |_____/ |_____| |          |      |   |     | | \  | |______
# __|__ |  \_|    |    |______ |    \_ |     | |_____     |    __|__ |_____| |  \_| ______|

$env->interactions = [
    Interactions\GuildInfo::class
];

Events\Init::listen();

#  ______ _     _ __   _
# |_____/ |     | | \  |
# |    \_ |_____| |  \_|

$env->discord->run();
