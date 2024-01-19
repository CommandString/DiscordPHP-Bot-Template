<?php

namespace Core\HMR;

interface InstanceHandler
{
    public function createInstance();

    public function set(string $name, string $filePath);
}
