<?php

namespace Core\Commands;

use Discord\Helpers\RegisteredCommand;
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

    /**
     * @return PromiseInterface<RegisteredCommand[]>
     */
    public function runQueue(bool $loadCommands = false, bool $registerCommands = true): PromiseInterface
    {
        $discord = discord();
        $discord->getLogger()->info('Running command queue...');

        return new Promise(function ($resolve, $reject) use ($discord, $registerCommands) {
            debug('Running Loop for ' . count($this->queue) . ' commands...');
            async(function () use ($discord, $registerCommands, $resolve) {
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

                    if (
                        ($rCommand === null || $command->hasCommandChanged($rCommand)) &&
                        $registerCommands
                    ) {
                        $discord->getLogger()->info("Command {$command->name} has changed, re-registering it...");
                        $command->setNeedsRegistered(true);
                    }
                }

                $commands = $this->loadCommands();

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
        $discord = discord();

        return new Promise(static function ($resolve, $reject) use ($command, $discord) {
            $commands = $command->properties->guild === null ?
                $discord->application->commands :
                $discord->guilds->get('id', $command->properties->guild)?->commands ?? null;

            if ($commands === null) {
                $discord->getLogger()->error("Failed to register command {$command->name}: Guild {$command->properties->guild} not found");

                return;
            }

            $commands->save(
                $commands->create(
                    $command->handler->getConfig()->toArray()
                )
            )->otherwise(static function (Throwable $e) use ($discord, $command) {
                $discord->getLogger()->error("Failed to register command {$command->name}: {$e->getMessage()}");
            });
        });
    }
}
