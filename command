<?php

use cmdstr\Discord\Config;
use Discord\Discord;
use React\EventLoop\Loop;

require_once "./vendor/autoload.php";

if (!isset($argv[2])) {
    throw new Exception("The command must have the an action with a command name appending it.");
}

$action = strtolower($argv[1]);

unset($argv[0], $argv[1]);

if (!in_array($action, ["save", "delete"])) {
    throw new Exception("$action is an invalid action, you can only save and delete commands!");
}

Config::getInstance()->discord = new Discord([
    "token" => Config::getInstance()->token
]);

Config::getInstance()->discord->on("ready", function () use ($argv, $action) {
    foreach ($argv as $command) {
        $command_class = "cmdstr\\Discord\\Commands\\$command";
    
        if (!class_exists($command_class)) {
            throw new Exception("$command cannot be found!");
        }

        (new $command_class)->$action();
    
        echo "A request for $command to be {$action}d was sent!";

        Loop::addTimer(3, function () {
            Config::getInstance()->discord->close();
        });
    }
});

Config::getInstance()->discord->run();
