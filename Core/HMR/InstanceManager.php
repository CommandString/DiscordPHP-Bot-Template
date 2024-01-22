<?php

namespace Core\HMR;

interface InstanceManager
{
    public function createInstance();

    public function set(string $name, string $filePath);
}
