<?php

use cmdstr\Discord\Config;
use cmdstr\Discord\Events\ready;
use Discord\Discord;
use Discord\WebSockets\Intents;

require_once "./vendor/autoload.php";

$config = Config::getInstance();

$config->discord = new Discord([
    "token" => $config->token,
    "loadAllMembers" => true,
    "intents" => Intents::getAllIntents()
]);

(new ready)->listen();

$config->discord->run();