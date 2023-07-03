<?php

namespace Core\Commands;

use Base\Command\Interface\DynamicCommandInterface;
use Core\BaseCommand\Command;
use Discord\Builders\MessageBuilder;

abstract class DynamicCommand 
{
    protected $startTime; // the time when the command instance was created
    protected $endLimit;

    public function __construct() {
        $this->startTime = time();
    }

    public function setTimeLimit(int $timeLimit): void {
        if ($timeLimit < $this->startTime) {
            throw new \Exception("Attempted to set a value less than the time the command was instantiated.");
        }
        $this->endLimit = $timeLimit;
    }

    public function addTimeLimit(int $minutes): void {
        $newEndTime = $this->startTime + ($minutes * 60);
        $this->setTimeLimit($newEndTime);
    }

    public function isCommandExpired(): bool {
        $currentTime = time();
        return ($currentTime - $this->startTime) > $this->endLimit;
    }


}
