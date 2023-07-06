<?php

namespace Core\Commands;

use Core\HMR\HotMessageCommand;

class CommandCollection
{
    private $collection = [];

    public function addHotCommand($commandName, $value, HotMessageCommand $hotMessageCommand)
    {
        $value['instance'] = $hotMessageCommand;
        $this->collection[$commandName] = $value;
    }

    public function get($command)
    {

        // this shit causes Warning: Undefined array key 1 in D:\project\discord-bot\PHP\DiscordPHP-Bot-Template\Core\Commands\CommandCollection.php on line 17
        // [$commandName, $subCommand] = explode(' ', $command, 2);

        $subCommand = null;
        $exploded = explode(' ', $command, 2);
        $commandName = $exploded[0];
        if (isset($exploded[1])) {
            $subCommand = $exploded[1];
        }

        if (isset($this->collection[$commandName])) {
            $commandData = $this->collection[$commandName];

            if ($subCommand && isset($commandData['subCommands'][$subCommand])) {
                return (object) [
                    'instance' => $commandData['instance']->createInstance(),
                    'method' => $commandData['subCommands'][$subCommand],
                ];
            }

            return (object) [
                'instance' => $commandData['instance']->createInstance(),
                'method' => $commandData['method'],
            ];
        }

        return null;
    }

    public function reloadCommand($commandName)
    {
        if (!isset($this->collection[$commandName])) {
            throw new \LogicException('Attempt to reload a message command that does not exist in the collection');
        }
        $this->collection[$commandName]['instance']->reload();
    }

    public function isCommandExist($command)
    {
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
