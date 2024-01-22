<?php

namespace Core\Commands;

/**
 * Class DynamicCommand
 *
 * A base class for dynamic type commands with expiration functionality.
 */
abstract class DynamicCommand
{
    /**
     * @var int The UNIX time when the command instance was created.
     */
    protected $startTime;

    /**
     * @var int The instance expiration UNIX time in seconds.
     */
    protected $endLimit;

    /**
     * DynamicCommand constructor.
     *
     * Initializes the command with a start time and default expiration of 30 minutes.
     */
    public function __construct()
    {
        $this->setTimeLimit(time());
        // By default, it will assign 30 minutes for expiration countdown
        $this->addTimeLimit(30);
    }

    /**
     * Sets the start time of the command.
     */
    public function startTime(): void
    {
        $this->startTime = time();
    }

    /**
     * Gets the time limit of the command.
     *
     * @return int The time limit in seconds.
     */
    public function getTimeLimit(): int
    {
        return $this->endLimit;
    }

    /**
     * Sets the time limit for the command using UNIX time.
     *
     * @param int $timeLimit The time limit in seconds.
     *
     * @throws \LogicException If the time limit is less than the start time.
     */
    public function setTimeLimit(int $timeLimit): void
    {
        if ($timeLimit < $this->startTime) {
            throw new \LogicException('Attempted to set a value less than the time the command was instantiated.');
        }
        $this->endLimit = $timeLimit;
    }

    /**
     * Adds time to the existing time limit of the command.
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
    public function isExpired(): bool
    {
        $currentTime = time();

        return $currentTime > $this->endLimit;
    }

    /**
     * Destructor for DynamicCommand.
     *
     * Unsets associated properties inside the current object.
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
