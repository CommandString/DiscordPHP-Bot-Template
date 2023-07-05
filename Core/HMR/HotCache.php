<?php

namespace Core\HMR;

use Throwable;

use function Core\discord;

class HotCache
{
    public readonly string $fqn;

    public function __construct(
        public readonly string $className,
        public readonly string $filePath,
    ) {
        $this->fqn = '\\Core\\HMR\\Cached\\' . $className;
    }

    public function createInstance(): ?object
    {
        try {
            return new $this->fqn();
        } catch (Throwable $e) {
            discord()->getLogger()->error($e);

            return null;
        }
    }

    public function deleteCachedFile(): void
    {
        unlink($this->filePath);
    }
}
