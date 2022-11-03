<?php

use Discord\Bot\Config;
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

$config = Config::get();

$config->discord = new Discord([
    "token" => $config->token
]);

$config->discord->on("ready", function () use ($argv, $action) {
    /**
     * @var \Discord\Discord
     */
    $discord = Config::get()->discord;
    

    if ($action === "delete" && $argv[2] === "all") {
        $discord->application->commands->freshen()->done(function ($results) use ($discord) {
            foreach ($results as $command) {
                $discord->application->commands->delete($command);
            }
        });

        foreach ($discord->guilds as $guild) {
            $guild->commands->freshen()->done(function ($results) use ($guild) {
                foreach ($results as $command) {
                    $guild->commands->delete($command);
                }
            });
        }
    
        Loop::addTimer(30, function () {
            Config::get()->discord->close();
        });
    } else {
        foreach ($argv as $command) {
            $command_class = "Discord\\Bot\\Commands\\$command";
        
            if (!class_exists($command_class)) {
                throw new Exception("Command $command cannot be found!");
            }

            (new $command_class)->$action();
        
            echo "\nA request for command $command to be {$action}d was sent!\n\n";
        }
    
        Loop::addTimer(30, function () {
            Config::get()->discord->close();
        });
    }
});

$config->discord->run();