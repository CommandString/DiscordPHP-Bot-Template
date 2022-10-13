<?php

namespace cmdstr\Discord\Events;

use Discord\Discord;

/**
 * @inheritDoc Template
 */
class ready extends Template {
    public function handler(Discord $discord = null): void
    {
        echo "\n\n{$discord->application->name} ready!\n\n";
    }
  
    public function getEvent(): string
    {
        return "ready";
    }

    public function runOnce(): bool
    {
        return false;
    }
}