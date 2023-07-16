<?php

namespace Core\HMR;

use Evenement\EventEmitter;
use LogicException;
use React\EventLoop\Loop;
use React\EventLoop\TimerInterface;

class HotFile extends EventEmitter
{
    public const EVENT_CHANGED = 'hasChanged';
    public const EVENT_REMOVED = 'removed';

    protected string $hash = '';
    public readonly string $name;
    private TimerInterface $timer;

    public function __construct(
        public readonly string $file,
        int $interval = 1
    ) {
        if (!file_exists($file)) {
            throw new LogicException("File {$file} does not exist");
        }

        $this->name = basename($file);
        $this->hash = $this->createHash();

        $this->timer = Loop::addPeriodicTimer($interval, function () {
            if (!file_exists($this->file)) {
                $this->emit(self::EVENT_REMOVED, [$this]);
                $this->__destruct();

                return;
            }

            if (!$this->hasChanged()) {
                return;
            }

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

    public function __destruct()
    {
        Loop::cancelTimer($this->timer);
    }
}
