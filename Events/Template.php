<?php

namespace Events;

use CommandString\Env\Env;

abstract class Template {
    protected static string $event = "";
    protected static bool $runOnce = false;

    /**
     * @return void
     */
    abstract public static function handler(): void;

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