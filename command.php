<?php

use CommandString\Env\Env;
use Discord\Discord;

require_once "./vendor/autoload.php";

if (!isset($argv[2])) {
    throw new Exception("The command must have the an action with a command name appending it.");
}

$action = strtolower($argv[1]);

unset($argv[0], $argv[1]);

if (!in_array($action, ["save", "delete"])) {
    throw new Exception("$action is an invalid action, you can only save and delete commands!");
}

$env = Env::createFromJsonFile("./env.json");

$env->discord = new Discord([
    "token" => $env->token
]);

$env->discord->on("ready", function () use ($argv, $action) {
    /**
     * @var \Discord\Discord
     */
    $discord = Env::get()->discord;
    
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
    } else {
        foreach ($argv as $command) {
            $command = "Commands\\$command";
        
            if (!class_exists($command)) {
                throw new Exception("Command $command cannot be found!");
            }

            $commandName = $command::getName();

            if (is_array($commandName)) {
                $commandName = $commandName[0];
            }

            echo json_encode(["command" => $commandName, "action" => $action]).PHP_EOL;

            $command::$action();
        }
    }
});

$env->discord->run();