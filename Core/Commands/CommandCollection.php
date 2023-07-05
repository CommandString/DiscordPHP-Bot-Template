<?php

namespace Core\Commands;

use Core\HMR\HotMessageCommand;

class CommandCollection {
    private $collection = [];

    public function addHotCommand($commandName, $value, HotMessageCommand $hotMessageCommand) {
        $value["instance"] = $hotMessageCommand;
        $this->collection[$commandName] = $value;
    }

    public function get($command) {
        [$commandName, $subCommand] = explode(' ', $command, 2);

        if (isset($this->collection[$commandName])) {
            $commandData = $this->collection[$commandName];

            if ($subCommand && isset($commandData['subCommands'][$subCommand])) {
                return [
                    'instance' => clone $commandData['instance']->createInstance(),
                    'method' => $commandData['subCommands'][$subCommand]
                ];
            }

            return [
                'instance' => clone $commandData['instance']->createInstance(),
                'method' => $commandData['method']
            ];
        }

        return null;
    }

    public function reloadCommand($commandName) {
        if (!isset($this->collection[$commandName])) {
            throw new \LogicException("Attempt to reload a message command that does not exist in the collection");
        }
        $this->collection[$commandName]['instance']->reload();
    }

    public function isCommandExist($command) {
        [$commandName, $subCommand] = explode(' ', $command, 2);

        if (isset($this->collection[$commandName])) {
            $commandData = $this->collection[$commandName];

            if ($subCommand && isset($commandData['subCommands'][$subCommand])) {
                return true;
            }

            return true;
        }

        return false;
    }
}
