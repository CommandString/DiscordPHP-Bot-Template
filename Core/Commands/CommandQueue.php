<?php

namespace Core\Commands;

use Discord\Repository\Guild\GuildCommandRepository;
use Discord\Repository\Interaction\GlobalCommandRepository;
use React\Promise\Promise;
use React\Promise\PromiseInterface;
use Throwable;

use function Core\debug;
use function Core\discord;
use function Core\error;
use function React\Async\async;
use function React\Async\await;

class CommandQueue
{
    /** @var QueuedCommand[] */
    protected array $queue = [];

    public function appendCommand(QueuedCommand $command): self
    {
        $this->queue[] = $command;

        return $this;
    }

    public function runQueue(bool $loadCommands = true, bool $registerCommands = true): PromiseInterface
    {
        $discord = discord();
        $discord->getLogger()->info('Running command queue...');

        return new Promise(function ($resolve) use ($registerCommands, $discord, $loadCommands) {
            debug('Running Loop for ' . count($this->queue) . ' commands...');
            async(function () use ($registerCommands, $discord, $loadCommands, $resolve) {
                if ($registerCommands) {
                    debug('Getting commands...');
                    /** @var GlobalCommandRepository $globalCommands */
                    $globalCommands = await($discord->application->commands->freshen());

                    /** @var GuildCommandRepository[] $guildCommands */
                    $guildCommands = [];

                    foreach ($this->queue as $command) {
                        debug("Checking {$command->getName()}...");
                        /** @var GlobalCommandRepository|GuildCommandRepository $rCommands */
                        $rCommands = $command->properties->guild === null ?
                            $globalCommands :
                            $guildCommands[$command->properties->guild] ??= await($discord->guilds->get('id', $command->properties->guild)->commands->freshen());

                        $rCommand = $rCommands->get('name', $command->getName());

                        if ($rCommand === null || $command->hasCommandChanged($rCommand)) {
                            debug("Command {$command->getName()} has changed, re-registering it...");
                            $command->setNeedsRegistered(true);
                        }
                    }
                }

                if ($loadCommands) {
                    $this->loadCommands();
                }

                $resolve();
            })();
        });
    }

    protected function loadCommands(): void
    {
        debug('Loading commands...');
        $discord = discord();

        $listen = static function (string|array $name, QueuedCommand $command) use ($discord) {
            try {
                $registered = $discord->listenCommand($command->getName(), $command->handler->handle(...), $command->handler->autocomplete(...));

                if (!is_array($command->name) || count($command->name) === 1) {
                    return;
                }

                $loop = static function (array $commands) use (&$loop, $registered, $command) {
                    foreach ($commands as $commandName) {
                        if (is_array($commandName)) {
                            $loop($commandName);
                        }

                        $registered->addSubCommand($commandName, $command->handler->handle(...), $command->handler->autocomplete(...));
                    }
                };
                $names = $command->name;
                array_shift($names);

                $loop($names);
            } catch (Throwable $e) {
                if (preg_match_all('/The command `(\w+)` already exists\./m', $e->getMessage(), $matches, PREG_SET_ORDER)) {
                    return;
                }

                error($e);
            }
        };

        foreach ($this->queue as $command) {
            $listen($command->name, $command);

            debug("Loaded command {$command->getName()}");

            if (!$command->needsRegistered()) {
                debug("Command {$command->getName()} does not need to be registered");

                continue;
            }

            $this->registerCommand($command);
            debug("Command {$command->getName()} was registered");
        }
    }

    protected function registerCommand(QueuedCommand $command): PromiseInterface
    {
        return new Promise(static function ($resolve, $reject) use ($command) {
            $discord = discord();
            $commands = $command->properties->guild === null ?
                $discord->application->commands :
                $discord->guilds->get('id', $command->properties->guild)?->commands ?? null;

            if ($commands === null && $command->properties->guild !== null) {
                $discord->getLogger()->error("Failed to register command {$command->getName()}: Guild {$command->properties->guild} not found");

                return;
            }

            try {
                $commands->save(
                    $commands->create(
                        $command->handler->getConfig()->toArray()
                    )
                )->then(static function () use ($command, $resolve) {
                    debug("Command {$command->getName()} was registered");
                    $resolve();
                })->otherwise(static function (Throwable $e) use ($command, $reject) {
                    error("Failed to register command {$command->getName()}: {$e->getMessage()}");
                    $reject($e);
                });
            } catch (Throwable $e) {
                error("Failed to register command {$command->getName()}: {$e->getMessage()}");
                $reject($e);
            }
        });
    }

    public static function queueAndRunCommands(bool $loadCommands = true, bool $registerCommands = true, QueuedCommand ...$commands): PromiseInterface
    {
        $queue = (new self());

        foreach ($commands as $command) {
            $queue->appendCommand($command);
        }

        return $queue->runQueue($loadCommands, $registerCommands);
    }
}
