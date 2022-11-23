<?php

namespace Discord\Bot\Events;

use Discord\WebSockets\Event;

class Example extends Template {
    public function handler(): void
    {
        
    }

    public function getEvent(): string
    {
        return Event::MESSAGE_CREATE;
    }

    public function runOnce(): bool
    {
        return false;
    }
}
