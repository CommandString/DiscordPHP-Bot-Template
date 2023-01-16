<?php

namespace Events;

use CommandString\Env\Env;

abstract class BaseEvent {
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
        $function = function (mixed...$args) {
            static::handler(...$args);
        };

        if (static::$runOnce) {
            Env::get()->discord->once(static::$event, $function);
        } else {
            Env::get()->discord->on(static::$event, $function);
        }
    }
}