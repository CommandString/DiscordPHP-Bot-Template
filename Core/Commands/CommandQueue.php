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
                $rCommand = $globalCommands->get('name', $command->properties->name);

                if ($rCommand === null) {
                    $command->setNeedsRegistered(true);

                    continue;
                }

                $command->hasCommandChanged($rCommand);
            }

            $this->loadCommands();
        })();
    }

    protected function loadCommands(): void
    {
        $discord = discord();

        foreach ($this->queue as $command) {
            $discord->listenCommand($command->properties->name, $command->handler->handle(...), $command->handler->autocomplete(...));

            if ($command->needsRegistered()) {
                $discord->application->commands->save(
                    $discord->application->commands->create(
                        $command->handler->getConfig()->toArray()
                    )
                )->otherwise(static function (Throwable $e) use ($discord, $command) {
                    $discord->getLogger()->error("Failed to register command {$command->properties->name}: {$e->getMessage()}");
                });
            }
        }
    }
}
