<?php

namespace Common;

use ArrayAccess;

class MagicProps implements ArrayAccess
{
    protected array $props = [];

    public function __set(mixed $name, mixed $value): void
    {
        $this->props[$name] = $value;
    }

    public function __unset(mixed $name): void
    {
        unset($this->props[$name]);
    }

    public function __get(mixed $name): mixed
    {
        return $this->props[$name] ?? null;
    }

    public function __isset(mixed $name): bool
    {
        return isset($this->props[$name]);
    }

    public function offsetExists(mixed $offset): bool
    {
        return $this->__get($offset) !== null;
    }

    public function offsetGet(mixed $offset): mixed
    {
        return $this->__get($offset);
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->__set($offset, $value);
    }

    public function offsetUnset(mixed $offset): void
    {
        $this->__unset($offset);
    }
}