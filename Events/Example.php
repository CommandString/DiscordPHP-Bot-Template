<?php

namespace Events;

use Discord\WebSockets\Event;

class Example extends Template {
    protected static string $event = Event::MESSAGE_CREATE;
    protected static bool $runOnce = false;

    public static function handler(): void
    {
        
    }
}