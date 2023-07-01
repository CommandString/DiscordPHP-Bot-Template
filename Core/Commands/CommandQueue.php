<?php

namespace Core\Commands;

use Discord\Repository\Guild\GuildCommandRepository;
use Discord\Repository\Interaction\GlobalCommandRepository;
use Throwable;

use function Core\discord;
use function React\Async\async;
use function React\Async\await;

class CommandQueue
{
    /** @var QueuedCommand[] */
    protected array $queue = [];

    public function appendCommand(QueuedCommand $command): void
    {
        $this->queue[] = $command;
    }

    public function runQueue(): void
    {
        $discord = discord();
        $discord->getLogger()->info('Running command queue...');

        async(function () use ($discord) {
            /** @var GlobalCommandRepository $globalCommands */
            $globalCommands = await($discord->application->commands->freshen());

            /** @var GuildCommandRepository[] $guildCommands */
            $guildCommands = [];

            foreach ($this->queue as $command) {
                /** @var GlobalCommandRepository|GuildCommandRepository $rCommands */
                $rCommands = $command->properties->guild === null ?
                    $globalCommands :
                    $guildCommands[$command->properties->guild] ??= await($discord->guilds->get('id', $command->properties->guild)->commands->freshen());

                $rCommand = $rCommands->get('name', $command->name);

                if ($rCommand === null || $command->hasCommandChanged($rCommand)) {
                    $discord->getLogger()->info("Command {$command->name} has changed, re-registering it...");
                    $command->setNeedsRegistered(true);
                }
            }

            $this->loadCommands();
        })();
    }

    protected function loadCommands(): void
    {
        $discord = discord();

        foreach ($this->queue as $command) {
            $discord->listenCommand($command->name, $command->handler->handle(...), $command->handler->autocomplete(...));
            $discord->getLogger()->debug("Loaded command {$command->name}");

            if (!$command->needsRegistered()) {
                continue;
            }

            $commands = $command->properties->guild === null ?
                $discord->application->commands :
                $discord->guilds->get('id', $command->properties->guild)?->commands ?? null;

            if ($commands === null) {
                $discord->getLogger()->error("Failed to register command {$command->name}: Guild {$command->properties->guild} not found");

                continue;
            }

            $commands->save(
                $commands->create(
                    $command->handler->getConfig()->toArray()
                )
            )->otherwise(static function (Throwable $e) use ($discord, $command) {
                $discord->getLogger()->error("Failed to register command {$command->name}: {$e->getMessage()}");
            });
        }
    }
}
