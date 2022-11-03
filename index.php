<?php

use Discord\Bot\Config;
use Discord\Bot\Events\ready;
use Discord\Discord;
use Discord\WebSockets\Intents;

require_once "./vendor/autoload.php";

$config = new Config();

$config->discord = new Discord([
    "token" => $config->token,
    "loadAllMembers" => true,
    "intents" => Intents::getAllIntents()
]);

(new ready)->listen();

$config->discord->run();