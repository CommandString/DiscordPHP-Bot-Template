<?php

namespace Events;

use function Common\env;
use Discord\Discord;

class Init extends BaseEvent
{
    protected static string $event = "init";

    private const COMMANDS = 0;
    private const EVENTS = 1;
    private const INTERACTIONS = 2;

    public static function handler(Discord $discord = null): void
    {
        echo "\n{$discord->application->name} ready!\n\n";

        $listened = [env()->commands, env()->events, env()->interactions];

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
