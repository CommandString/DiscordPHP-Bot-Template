<?php

namespace Core\HMR;

use Evenement\EventEmitter;
use React\EventLoop\Loop;

use function Core\discord;

class HotFile extends EventEmitter
{
    public const EVENT_HAS_CHANGED = 'hasChanged';

    protected string $contents = '';

    public function __construct(
        public readonly string $file
    ) {
        if (!file_exists($file)) {
            discord()->getLogger()->error("File {$file} does not exist");

            return;
        }

        $this->contents = $this->getContents();

        Loop::addPeriodicTimer(1, function () {
            if (!$this->hasChanged()) {
                return;
            }

            discord()->getLogger()->debug("File {$this->file} has changed");
            $this->contents = $this->getContents();
            $this->emit(self::EVENT_HAS_CHANGED, [$this]);
        });
    }

    public function getContents(): string
    {
        return file_get_contents($this->file);
    }

    public function hasChanged(): bool
    {
        return $this->contents !== $this->getContents();
    }
}
