<?php

namespace Events;

use CommandString\Env\Env;
use Discord\Discord;

class ready extends BaseEvent {
    protected static string $event = "ready";

    private const COMMANDS = 0;
    private const EVENTS = 1;
    private const INTERACTIONS = 2;

    public static function handler(Discord $discord = null): void
    {
        echo "\n{$discord->application->name} ready!\n\n";

        $listened = [Env::get("commands"), Env::get("events"), Env::get("interactions")];

        foreach ($listened as $type => $classes) {
			$typeString = match ($type) {
				self::COMMANDS => "command",
				self::EVENTS => "event",
				self::INTERACTIONS => "interaction",
				default => "",
			};

            foreach ($classes as $class) {
                if ($class !== self::class) {
                    $class::listen();
                }

                echo "Listening for {$typeString}: \"{$class}\"\n";
            }
        }
    }
}
