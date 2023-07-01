<?php

namespace Core\Commands;

use Discord\Builders\CommandBuilder;
use Discord\Parts\Interactions\Command\Command as DiscordCommand;
use LogicException;

class QueuedCommand
{
    protected bool $needsRegistered = false;

    public readonly string $name;

    public function __construct(
        public readonly Command $properties,
        public readonly CommandHandler $handler
    ) {
        $name = $this->handler->getConfig()->toArray()['name'] ?? null;

        if ($name === null) {
            $className = get_class($this->handler);
            throw new LogicException("Command {$className} has no name");
        }

        $this->name = $name;
    }

    public function hasCommandChanged(DiscordCommand $rCommand): bool
    {
        $command = $this->handler->getConfig();
        $rCommand = $rCommand->jsonSerialize();

        if ($command instanceof CommandBuilder) {
            $command = $command->jsonSerialize();
        }

        $areTheSame = static function (array $a, array $b) use (&$areTheSame): bool {
            $ignoreFields = ['default_permission'];

            foreach ($a as $key => $value) {
                $bValue = $b[$key] ?? null;

                if ($value === $bValue || in_array($key, $ignoreFields)) {
                    continue;
                }

                if (is_array($value) && is_array($bValue)) {
                    if (!$areTheSame($value, $bValue)) {
                        return false;
                    }
                } else {
                    return false;
                }
            }

            return true;
        };

        return !$areTheSame($command, $rCommand);
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
