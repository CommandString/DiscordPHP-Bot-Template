<?php

namespace Events;

use function Common\env;

abstract class BaseEvent
{
    protected static string $event = "";
    protected static bool $runOnce = false;

    /**
     * @return void
     */
    abstract public static function handler(): void;

    public static function getEvent(): string
    {
        return static::$event;
    }

    public static function runOnce(): bool
    {
        return static::$runOnce;
    }

    public static function listen(): void
    {
        $function = static function (mixed ...$args) {
            static::handler(...$args);
        };

        if (static::$runOnce) {
            env()->discord->once(static::$event, $function);
        } else {
            env()->discord->on(static::$event, $function);
        }
    }
}
