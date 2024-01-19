<?php

namespace Core\Commands;

/**
 *  The purpose of this class is to serve as a repository for managing message commands, providing methods to add, retrieve, and validate commands, as well as checking the existence of specific commands
 */
class MessageCommandRepository
{
    /** @var array<MessageCommandHandler> */
    private array $commands = [];

    public function addMsgCommand(MessageCommandHandler $msgCommand): void
    {
        $msgCommand->validate();
        $commandName = $msgCommand->getCommandName();

        $this->commands[$commandName] = $msgCommand;
    }

    /**
     * getCommandMap
     *
     * @param  string $commands passed command with prefix excluded
     */
    public function getCommandMapping(string $commands): ?object
    {
        $subCommand = null;
        $exploded = explode(' ', $commands);
        $commandName = $exploded[0];
        if (isset($exploded[1])) {
            $subCommand = $exploded[1];
        }

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

    public function getCommandInstanceHandler($commandName)
    {
        return $this->commands[$commandName] ?? null;
    }

    /**
     * doesCommandExist, to check if the command exist
     *
     * @param  string $commands passed command string with prefix excluded
     */
    public function doesCommandExist($commands): bool
    {
        [$commandName, $subCommand] = explode(' ', $commands, 2);

        if (isset($this->commands[$commandName])) {
            $msgCommand = $this->commands[$commandName];

            if ($subCommand && $msgCommand->getSubCommand($subCommand)) {
                return true;
            }

            return true;
        }

        return false;
    }
}
