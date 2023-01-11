<?php

namespace Commands;

use CommandString\Env\Env;
use Discord\Builders\CommandBuilder;
use Discord\Parts\Interactions\Command\Command;
use Discord\Parts\Interactions\Interaction;
use Exception;

abstract class Template {
    protected static string $guild = "";
    protected static string|array $name = "";

    /**
     * @param Interaction $interaction
     * 
     * @return void
     */
    abstract public static function handler(Interaction $interaction): void;

    /**
     * @param Interaction $interaction
     * 
     * @return void
     */
    public static function autocomplete(Interaction $interaction): void
    {
        
    }
    
    /**
     * @return CommandBuilder
     */
    abstract public static function getConfig(): CommandBuilder|array;

    /** 
     * @return bool
     */
    private static function isGuildCommand(): bool
    {
        return (strlen(static::$guild) > 0);
    }

    /**
     * @return void
     */
    public static function delete(): void
    {
        /**
         * @var \Discord\Discord
         */
        $discord = Env::get()->discord;

        $function = function ($commands) use ($discord) {
            $command = $commands->get("name", static::$name);

            if (is_null($command)) {
                throw new Exception("Command ".static::$name." isn't registered to the discord bot!");
            }

            if (!$this->isGuildCommand()) {
                $discord->application->commands->delete($command);
            } else {
                $discord->guilds->get("id", static::$guild)->commands->delete($command);
            }
        };

        if (static::isGuildCommand()) {
            $discord->guilds->get("id", static::$guild)->commands->freshen()->done($function);
        } else {        
            $discord->application->commands->freshen()->done($function);
        }
    }

    /**
     * @return void
     */
    public static function save(): void
    {
        $config = static::getConfig();

        if ($config instanceof CommandBuilder) {
            $config = $config->toArray();
        }

        $command = new Command(Env::get()->discord, $config);

        /**
         * @var \Discord\Discord
         */
        $discord = Env::get()->discord;

        if (static::isGuildCommand()) {
            $discord->guilds[static::$guild]->commands->save($command);
        } else {
            $discord->application->commands->save($command);
        }
    }

    /**
     * @return void
     */
    public static function listen(): void
    {
        /**
         * @var \Discord\Discord
         */
        $discord = Env::get()->discord;
        
        $listen = function (string|array $name) use ($discord) {
            try {
                $discord->listenCommand($name, function (Interaction $interaction) {
                    static::handler($interaction);
                }, function (Interaction $interaction) {
                    static::autocomplete($interaction);
                });
            } catch (\LogicException $e) {
                echo "Warning caught: {$e->getMessage()}\nIf this is about a command already existing for a command you're listening for that has a separate subcommand handler you can safely ignore this :)\n";
            }
        };

        $cmd_name = static::$name;
        if (is_array($cmd_name)) {
            foreach ($cmd_name as $name) {
                if (!is_array($name)) {
                    $listen($name);
                } else {
                    $names = $name;
                    $listen([$cmd_name[0], ...$names]);
                }
            }
        } else {
            $listen($cmd_name);
        }
    }
}
