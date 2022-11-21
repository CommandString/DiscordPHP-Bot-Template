<?php

namespace Discord\Bot\Events;

use Discord\Bot\Commands\Examples\Ping;
use Discord\Bot\Commands\Examples\Randomize;
use Discord\Bot\Commands\Examples\Up;
use Discord\Discord;

/**
 * @inheritDoc Template
 */
class ready extends Template {
    public function handler(Discord $discord = null): void
    {
        echo "\n{$discord->application->name} ready!\n\n";

        # _______  _____  _______ _______ _______ __   _ ______  _______
        # |       |     | |  |  | |  |  | |_____| | \  | |     \ |______
        # |_____  |_____| |  |  | |  |  | |     | |  \_| |_____/ ______|

        (new Ping)->listen();
        (new Randomize)->listen();
        (new Up)->listen();
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