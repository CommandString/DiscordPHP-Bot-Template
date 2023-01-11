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

    abstract public static function handler(Interaction $interaction, Discord $discord);

    public static function listen(): void
    {
        $handler = function (Interaction $interaction, Discord $discord) {
            $parts = explode("|", $interaction->data->custom_id);

            if ($parts[0] === ArrayUtils::getLastItem(explode('\\', static::class))) {
                static::handler($interaction, $discord);
            }
        };

        if (static::$runOnce) {
            Env::get()->discord->once(Event::INTERACTION_CREATE, $handler);
        } else {
            Env::get()->discord->on(Event::INTERACTION_CREATE, $handler);
        }
    }

    public static function getId(): string
    {
        return static::$id;
    }
}