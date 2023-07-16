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

        return new Promise(function ($resolve, $reject) use ($registerCommands, $discord, $loadCommands) {
            debug('Running Loop for ' . count($this->queue) . ' commands...');
            async(function () use ($registerCommands, $discord, $loadCommands, $resolve) {
                if ($registerCommands) {
                    debug('Getting commands...');
                    /** @var GlobalCommandRepository $globalCommands */
                    $globalCommands = await($discord->application->commands->freshen());

                    /** @var GuildCommandRepository[] $guildCommands */
                    $guildCommands = [];

                    foreach ($this->queue as $command) {
                        debug("Checking {$command->name}...");
                        /** @var GlobalCommandRepository|GuildCommandRepository $rCommands */
                        $rCommands = $command->properties->guild === null ?
                            $globalCommands :
                            $guildCommands[$command->properties->guild] ??= await($discord->guilds->get('id', $command->properties->guild)->commands->freshen());

                        $rCommand = $rCommands->get('name', $command->name);

                        if ($rCommand === null || $command->hasCommandChanged($rCommand)) {
                            debug("Command {$command->name} has changed, re-registering it...");
                            $command->setNeedsRegistered(true);
                        }
                    }
                }

                $commands = $loadCommands ? $this->loadCommands() : [];

                $resolve($commands);
            })();
        });
    }

    protected function loadCommands(): array
    {
        debug('Loading commands...');
        $discord = discord();
        $registeredCommands = [];

        try {
            foreach ($this->queue as $command) {
                $registeredCommands[$command->name] = [$command, $discord->listenCommand($command->name, $command->handler->handle(...), $command->handler->autocomplete(...))];
                $discord->getLogger()->debug("Loaded command {$command->name}");

                if (!$command->needsRegistered()) {
                    debug("Command {$command->name} does not need to be registered");

                    continue;
                }

                $this->registerCommand($command);
                debug("Command {$command->name} was registered");
            }

            return $registeredCommands;
        } catch (Throwable $e) {
            if (preg_match_all('/The command `(\w+)` already exists\./m', $e->getMessage(), $matches, PREG_SET_ORDER)) {
                return []; // TODO: Prevent HotLoader from throw this exceptions
            }

            error($e);

            return [];
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
                $discord->getLogger()->error("Failed to register command {$command->name}: Guild {$command->properties->guild} not found");

                return;
            }

            try {
                $commands->save(
                    $commands->create(
                        $command->handler->getConfig()->toArray()
                    )
                )->then(static function () use ($command, $resolve) {
                    debug("Command {$command->name} was registered");
                    $resolve();
                })->otherwise(static function (Throwable $e) use ($command, $reject) {
                    error("Failed to register command {$command->name}: {$e->getMessage()}");
                    $reject($e);
                });
            } catch (Throwable $e) {
                error("Failed to register command {$command->name}: {$e->getMessage()}");
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
