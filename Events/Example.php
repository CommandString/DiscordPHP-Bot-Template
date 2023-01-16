<?php

namespace Events;

use Discord\WebSockets\Event;

class Example extends BaseEvent {
    protected static string $event = Event::MESSAGE_CREATE;
    protected static bool $runOnce = false;

    public static function handler(): void
    {
        
    }
}