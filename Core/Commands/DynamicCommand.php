<?php

namespace Core\Commands;

interface DynamicCommand
{
    public function isCommandExpired(): bool;

    public function setTimeLimit(int $timeLimit): void;
}
