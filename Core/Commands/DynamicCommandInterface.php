<?php

namespace Core\Commands;

use Discord\Builders\MessageBuilder;

interface DynamicCommandInterface {
    public function isCommandExpired(): bool;
    public function setTimeLimit(int $timeLimit): void;
    public function getCommandDescription(): array;
    public function help(): MessageBuilder;
}
