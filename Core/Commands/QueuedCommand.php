<?php

namespace Core\Commands;

use ArrayAccess;
use Discord\Builders\CommandBuilder;
use Discord\Parts\Interactions\Command\Command as DiscordCommand;
use Exception;
use LogicException;
use RuntimeException;

class QueuedCommand
{
    protected const IGNORE_COMPARISON_FIELDS = ['default_permission', 'required'];

    protected bool $needsRegistered = false;
    public readonly string|array $name;

    public function __construct(
        public readonly Command $properties,
        public readonly CommandHandler $handler
    ) {
        if (!isset($this->properties->name)) {
            $config = $this->handler->getConfig();

            if ($config instanceof CommandBuilder) {
                $config = $config->toArray();
            }

            $name = $config['name'] ?? null;
        } else {
            $name = $this->properties->name;
        }

        if (empty($name)) {
            $className = get_class($this->handler);
            throw new LogicException("Command {$className} has no name");
        }

        $this->name = $name;
    }

    public function getName(): string
    {
        return is_array($this->name) ? $this->name[0] : $this->name;
    }

    public function hasCommandChanged(DiscordCommand $rCommand): bool
    {
        $command = $this->handler->getConfig();
        try {
            $rCommand = $rCommand->jsonSerialize();
        } catch (Exception $e) {
            throw new RuntimeException($e->getMessage(), previous: $e);
        }

        if ($command instanceof CommandBuilder) {
            $command = $command->jsonSerialize();
        }

        return static::compareCommands($command, $rCommand);
    }

    protected static function compareCommands(array $command, array|ArrayAccess $other): bool
    {
        foreach ($command as $key => $value) {
            if (in_array($key, static::IGNORE_COMPARISON_FIELDS)) {
                continue;
            }

            if (!isset($other[$key])) {
                return false;
            }

            $otherValue = $other[$key];

            if ($value === $otherValue) {
                continue;
            }

            if (is_array($value) && (is_array($otherValue) || $otherValue instanceof ArrayAccess)) {
                if (!self::compareCommands($value, $otherValue)) {
                    return false;
                }

                continue;
            }

            return false;
        }

        return true;
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
