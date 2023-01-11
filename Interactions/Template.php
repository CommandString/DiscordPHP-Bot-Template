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
        if (empty($listen)) {
            $handler = function (Interaction $interaction, Discord $discord) {
                if (!isset(static::$id)) {
                    return;
                }

                $parts = explode("|", $interaction->data->custom_id);

                if ($parts[0] === static::$id) {
                    static::handler($interaction, $discord);
                }
            };

            if (static::$runOnce) {
                Env::get()->discord->once(Event::INTERACTION_CREATE, $handler);
            } else {
                Env::get()->discord->on(Event::INTERACTION_CREATE, $handler);
            }
        }

        $listen[] = static::$id;
    }

    public static function getId(): string
    {
        return static::$id;
    }
}