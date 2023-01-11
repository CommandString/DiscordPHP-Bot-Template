<?php

namespace Interactions;

use CommandString\Env\Env;
use CommandString\Utils\ArrayUtils;
use Discord\Discord;
use Discord\Parts\Interactions\Interaction;
use Discord\WebSockets\Event;

abstract class Template {
    protected static string $id = "";
    protected static bool $runOnce = false;
    private static array $listen = [];

    abstract public static function handler(Interaction $interaction, Discord $discord);

    final public static function listen(): void
    {
        if (empty(self::$listen)) {
            $handler = function (Interaction $interaction, Discord $discord) {
                if (!isset(static::$id)) {
                    return;
                }

                $parts = explode("|", $interaction->data->custom_id);

                if (isset(self::$listen[$parts[0]])) {
                    $class = self::$listen[$parts[0]];

                    $class::handler($interaction, $discord);

                    if ($class::$runOnce) {
                        unset(self::$listen[$parts[0]]);
                    }
                }
            };

            Env::get()->discord->on(Event::INTERACTION_CREATE, $handler);
        }

        self::$listen[static::$id] = static::class;
    }

    public static function getId(): string
    {
        return static::$id;
    }
}