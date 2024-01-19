<?php

namespace Core\Commands;

/**
 * A Base Class for Dynamic type Command
 */
abstract class DynamicCommand
{
    /**
     * @var int The time when the command instance was created.
     */
    protected $startTime;

    /**
     * @var int The time limit in seconds.
     */
    protected $endLimit;

    public function __construct()
    {
        $this->setTimeLimit(time());
        // By Defailt it will asign 30 min for expiration countdown
        $this->addTimeLimit(30);
    }

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

    /**
     * __destruct, will unset assosiated the properties inside the curent object
     *
     * @return void
     */
    public function __destruct()
    {
        foreach (get_object_vars($this) as $property => $value) {
            unset($this->$property);
        }
    }
}
