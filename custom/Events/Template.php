<?php

namespace Discord\Bot\Events;

use Discord\Bot\Config;

abstract class Template {
    /**
     * @param string $name
     * 
     * @return mixed
     */
    public function __get(string $name): mixed
    {
        switch ($name) {
            case "event":
                return $this->getEvent();
            case "runOnce":
                return $this->runOnce();
        }

        return $this->$name;
    }

    /**
     * @return void
     */
    abstract public function handler(): void;
    
    /**
     * @return string
     */
    abstract public function getEvent(): string;

    /**
     * @return bool
     */
    abstract public function runOnce(): bool;

    public function listen(): void
    {
        if ($this->runOnce) {
            Config::get()->discord->once($this->event, function (mixed ...$args) {
                $this->handler(...$args);
            });
        } else {
            Config::get()->discord->on($this->event, function (mixed ...$args) {
                $this->handler(...$args);
            });
        }
    }
}