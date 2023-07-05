<?php

namespace Core\HMR;

class HotCache
{
    public readonly string $fqn;

    public function __construct(
        public readonly string $className,
        public readonly string $filePath,
    ) {
        $this->fqn = '\\Core\\HMR\\Cached\\' . $className;
    }

    public function createInstance(): object
    {
        return new $this->fqn();
    }

    public function deleteCachedFile(): void
    {
        unlink($this->filePath);
    }
}
