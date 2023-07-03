<?php

namespace Core\Manager;

use Core\Commands\DynamicCommandInterface;

class CommandInstanceManager
{
    private $commandInstances = [];

    public function addCommand(DynamicCommandInterface $command): void
    {
        $this->commandInstances[] = $command;
    }

    public function cleanupCommands(): void
    {
        $currentTime = time();
        foreach ($this->commandInstances as $key => $command) {
            if ($command->isCommandExpired()) {
                unset($this->commandInstances[$key]);
                unset($command);
            }
        }
    }
}
