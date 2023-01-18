<?php

namespace Commands;

use CommandString\Env\Env;
use Discord\Builders\CommandBuilder;
use Discord\Parts\Interactions\Command\Command;
use Discord\Parts\Interactions\Interaction;
use Exception;
use React\Promise\ExtendedPromiseInterface;
use React\Promise\PromiseInterface;

use function React\Async\await;

abstract class BaseCommand {
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

    public static function getGuild(): string
    {
        return static::$guild;
    }

    public static function getName(): string|array
    {
        return static::$name;
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
        return strlen(static::getGuild());
    }

    /**
     * @return void
     */
    public static function delete(): ExtendedPromiseInterface
    {
        /**
         * @var \Discord\Discord
         */
        $discord = Env::get()->discord;
        $return = null;

        if (static::isGuildCommand()) {
            $commands = await($discord->guilds->get("id", static::$guild)->commands->freshen());
        } else {        
            $commands = await($discord->application->commands->freshen());
        }

        $command = $commands->get("name", static::$name);

        if (is_null($command)) {
            throw new Exception("Command ".static::$name." isn't registered to the discord bot!");
        }

        if (!static::isGuildCommand()) {
            return $discord->application->commands->delete($command);
        } else {
            return $discord->guilds->get("id", static::$guild)->commands->delete($command);
        }
    }

    /**
     * @return void
     */
    public static function save(): ExtendedPromiseInterface
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
            return $discord->guilds[static::$guild]->commands->save($command);
        } else {
            return $discord->application->commands->save($command);
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
