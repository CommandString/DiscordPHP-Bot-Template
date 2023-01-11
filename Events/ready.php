<?php

namespace Events;

use CommandString\Env\Env;
use Discord\Discord;

class ready extends Template {
    protected static string $event = "ready";
    
    public static function handler(Discord $discord = null): void
    {
        echo "\n{$discord->application->name} ready!\n\n";

        foreach (Env::get("commands") as $command) {
            echo "Listening for command: \"$command\"\n";
            $command::listen();
        }
        
        foreach (Env::get("events") as $event) {
            echo "Listening for event: \"$event\"\n";
        }
    }
}