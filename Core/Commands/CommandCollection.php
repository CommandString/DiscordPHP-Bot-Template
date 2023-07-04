<?php

namespace Core\Commands;

use Core\HMR\HotMessageCommand;

class CommandCollection
{
    private $collection = [];

    public function addHotCommand($commandName, $value, HotMessageCommand $hotMessageCommand)
    {
        $value['instance'] = $hotMessageCommand;
        $this->collection[$commandName] = (object) $value;
    }

    public function get($commandName)
    {
        if (isset($this->collection[$commandName])) {
            return $this->collection[$commandName];
        }

        return null;
    }

    public function reloadCommand($commandName)
    {
        if (!isset($this->collection[$commandName])) {
            throw new \LogicException('Attempt to reload a message command that does not exist in the collection');
        }
        $this->collection[$commandName]->instance->reload();
    }

    public function isCommandExist($commandName)
    {
        return isset($this->collection[$commandName]);
    }
}
