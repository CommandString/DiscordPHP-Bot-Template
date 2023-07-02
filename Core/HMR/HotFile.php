<?php

namespace Core\HMR;

use Evenement\EventEmitter;
use React\EventLoop\Loop;

class HotFile extends EventEmitter
{
    public const EVENT_HAS_CHANGED = 'hasChanged';

    protected int $lastModified;

    public function __construct(
        public readonly string $file
    ) {
        $this->lastModified = filemtime($file);

        Loop::addTimer(1, function () {
            if ($this->hasChanged()) {
                $this->emit(self::EVENT_HAS_CHANGED, [$this]);
            }
        });
    }

    public function hasChanged(): bool
    {
        return $this->lastModified !== filemtime($this->file);
    }
}
