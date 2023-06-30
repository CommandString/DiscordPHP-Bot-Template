<?php

namespace Core\Commands;

use Discord\Builders\CommandBuilder;
use Discord\Parts\Interactions\Command\Command as DiscordCommand;

class QueuedCommand
{
    protected bool $needsRegistered = false;

    public function __construct(
        public readonly Command $properties,
        public readonly CommandHandler $handler
    ) {
    }

    public function hasCommandChanged(DiscordCommand $rCommand): bool
    {
        $command = $this->handler->getConfig();
        $rCommand = $rCommand->jsonSerialize();

        if ($command instanceof CommandBuilder) {
            $command = $command->jsonSerialize();
        }

        var_dump($rCommand, $command);

        return false;
    }

    public function setNeedsRegistered(bool $needsRegistered): void
    {
        $this->needsRegistered = $needsRegistered;
    }

    public function needsRegistered(): bool
    {
        return $this->needsRegistered;
    }
}
