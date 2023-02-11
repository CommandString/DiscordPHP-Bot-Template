<?php

namespace Commands;

use CommandString\Env\Env;
use Discord\Builders\CommandBuilder;
use Discord\Discord;
use Discord\Parts\Interactions\Command\Command;
use Discord\Parts\Interactions\Interaction;
use Exception;
use LogicException;
use React\Promise\ExtendedPromiseInterface;

use function React\Async\await;

abstract class BaseCommand {
    protected static string $guild = "";
    protected static string|array $name = "";

    abstract public static function handler(Interaction $interaction): void;

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

    abstract public static function getConfig(): CommandBuilder|array;

    private static function isGuildCommand(): bool
    {
        return strlen(static::getGuild());
    }

    public static function delete(): ExtendedPromiseInterface
    {
		/** @var Discord $discord */
        $discord = Env::get()->discord;

        if (static::isGuildCommand()) {
            $commands = await($discord->guilds->get("id", static::$guild)->commands->freshen());
        } else {
            $commands = await($discord->application->commands->freshen());
        }

        $command = $commands->get("name", static::$name);

        if ($command === null) {
            throw new Exception("Command ".static::$name." isn't registered to the discord bot!");
        }

        if (!static::isGuildCommand()) {
            return $discord->application->commands->delete($command);
        }

		return $discord->guilds->get("id", static::$guild)->commands->delete($command);
	}

    public static function save(): ExtendedPromiseInterface
    {
        $config = static::getConfig();

        if ($config instanceof CommandBuilder) {
            $config = $config->toArray();
        }

        $command = new Command(Env::get()->discord, $config);

        /** @var Discord $discord */
        $discord = Env::get()->discord;

        if (static::isGuildCommand()) {
            return $discord->guilds[static::$guild]->commands->save($command);
        }

		return $discord->application->commands->save($command);
	}

    public static function listen(): void
    {
        /** @var Discord $discord */
        $discord = Env::get()->discord;

        $listen = static function (string|array $name) use ($discord) {
            try {
                $discord->listenCommand($name, static function (Interaction $interaction) {
                    static::handler($interaction);
                }, static function (Interaction $interaction) {
                    static::autocomplete($interaction);
                });
            } catch (LogicException $e) {
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
