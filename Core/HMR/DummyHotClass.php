<?php

namespace Core\HMR;

class DummyHotClass implements InstanceManager
{
    public readonly string $className;

    public function set(string $name, string $className)
    {
        $this->className = $className;
    }

    public function createInstance()
    {
        return new $this->className();
    }
}
