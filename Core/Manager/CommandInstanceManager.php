<?php

namespace Core\Manager;

use Core\Commands\DynamicCommand;

class CommandInstanceManager
{
    /**
     * commandInstances
     *
     * @var object
     */
    private $commandInstances = [];

    public function addCommand(DynamicCommand $command): void
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
