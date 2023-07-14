<?php

namespace Core\HMR;

use Evenement\EventEmitter;
use React\EventLoop\Loop;

use function Core\discord;

class HotFile extends EventEmitter
{
    public const EVENT_CHANGED = 'hasChanged';
    public const EVENT_REMOVED = 'removed';

    protected string $hash = '';

    public function __construct(
        public readonly string $file
    ) {
        if (!file_exists($file)) {
            discord()->getLogger()->error("File {$file} does not exist");

            return;
        }

        $this->hash = $this->createHash();

        Loop::addPeriodicTimer(1, function () {
            if (!file_exists($this->file)) {
                $this->emit(self::EVENT_REMOVED, [$this]);

                return;
            }

            if (!$this->hasChanged()) {
                return;
            }

            discord()->getLogger()->debug("File {$this->file} has changed");
            $this->hash = $this->createHash();
            $this->emit(self::EVENT_CHANGED, [$this]);
        });
    }

    public function getContents(): string
    {
        return file_get_contents($this->file);
    }

    private function createHash(): string
    {
        return hash('sha256', $this->getContents());
    }

    public function hasChanged(): bool
    {
        return $this->createHash() !== $this->hash;
    }
}
