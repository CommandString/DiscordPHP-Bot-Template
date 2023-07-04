<?php

namespace Core\Commands;

/**
 * Class DynamicCommand, a
 */
abstract class BaseDynamicCommand implements DynamicCommand
{
    /**
     * @var int The time when the command instance was created.
     */
    protected $startTime;

    /**
     * @var int The time limit in seconds.
     */
    protected $endLimit;

    /**
     * Sets the start time of the command.
     */
    public function startTime(): void
    {
        $this->startTime = time();
    }

    public function getTimeLimit()
    {
        return $this->endLimit;
    }

    /**
     * Manualy Set timeLimit using UNIXTIME to set the time limit for the command.
     *
     * @param int $timeLimit The time limit in seconds.
     *
     * @throws \LogicException If the time limit is less than the start time.
     */
    public function setTimeLimit(int $timeLimit): void
    {
        if ($timeLimit < $this->startTime) {
            throw new \logicException('Attempted to set a value less than the time the command was instantiated.');
        }
        $this->endLimit = $timeLimit;
    }

    /**
     * Adds a time to the time limit of the command.
     *
     * @param int $minutes The number of minutes to add to the time limit.
     */
    public function addTimeLimit(int $minutes): void
    {
        $newEndTime = $this->endLimit + ($minutes * 60);
        $this->setTimeLimit($newEndTime);
    }

    /**
     * Checks if the command has expired.
     *
     * @return bool True if the command has expired, false otherwise.
     */
    public function isCommandExpired(): bool
    {
        $currentTime = time();

        return $currentTime > $this->endLimit;
    }
}
