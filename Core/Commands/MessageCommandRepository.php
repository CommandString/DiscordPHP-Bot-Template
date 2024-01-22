<?php

namespace Core\Commands;

use LogicException;

/**
 *  This class is to serve as a repository for managing message commands, providing methods to add, retrieve, and validate commands, as well as checking the existence of specific commands
 */
class MessageCommandRepository
{
    /** @var array<MessageCommandHandler> */
    private array $commands = [];

    /**
     * Adds a MessageCommandHandler to the command manager.
     *
     * @param MessageCommandHandler $msgCommand The MessageCommandHandler instance to be added.
     *
     * @throws LogicException If the provided MessageCommandHandler fails validation.
     */
    public function addCommand(MessageCommandHandler $msgCommand): void
    {
        $msgCommand->validate();
        $commandName = $msgCommand->getCommandName();

        $this->commands[$commandName] = $msgCommand;
    }

    /**
     * Retrieves the command mapping for the specified command and optional subcommand.
     *
     * @param string $commands The full command string (excluding any prefix) containing the command and optional subcommand.
     *
     * @return object|null An object representing the command mapping, or null if the specified command or subcommand is not found.
     *                      The object structure includes the command instance and method to be executed.
     *                      - instance: An instance of the command class created using the createInstance method.
     *                      - method: The method to be executed, either the specified subcommand method or the default method
     *                        if no subcommand is provided or if the specified subcommand is not found.
     */
    public function getCommandMapping(string $commands): ?object
    {
        $exploded = explode(' ', $commands, 3);
        $commandName = $exploded[0];
        $subCommand = $exploded[1] ?? null;

        if (isset($this->commands[$commandName])) {
            $msgCommand = $this->commands[$commandName];

            if ($subCommand && $method = $msgCommand->getSubCommand($subCommand)) {
                return (object) [
                    'instance' => $msgCommand->createInstance(),
                    'method' => $method,
                ];
            }

            return (object) [
                'instance' => $msgCommand->createInstance(),
                'method' => $msgCommand->getDefaultMethod(),
            ];
        }

        return null;
    }

    public function getCommandInstanceManager($commandName)
    {
        return $this->commands[$commandName] ?? null;
    }

    /**
     * Checks if a command exists and, optionally, if a subcommand exists for the specified command.
     *
     * @param string $commandName The name of the command to check for existence.
     * @param string|null $subCommandName The name of the subcommand to check for existence (optional).
     *
     * @return bool True if the command exists. If $subCommandName is provided, returns false
     *              if the subcommand does not exist for the specified command.
     */
    public function doesCommandExist(string $commandName, ?string $subCommandName = null): bool
    {
        if (isset($this->commands[$commandName])) {
            $msgCommand = $this->commands[$commandName];

            if ($subCommandName !== null) {
                $subCommandExists = $msgCommand->getSubCommand($subCommandName);

                return $subCommandExists !== null;
            }

            return true;
        }

        return false;
    }
}
