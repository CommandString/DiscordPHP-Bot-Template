<?php

namespace Discord\Bot;

use Exception;
use InvalidArgumentException;

/**
 * Configuration Singleton Class
 */
class Config {
    private static self $instance;
    private object $env;

    public function __construct($file_path = "./env.json")
    {
        if (!file_exists($file_path)) {
            file_put_contents($file_path, file_get_contents("./env.example.json"));
            throw new Exception("Please configure env.json!");
        }

        $this->env = json_decode(file_get_contents($file_path));

        if (empty($this->env->token)) {
            throw new Exception("You must define a token!");
        }

        if (!isset(self::$instance)) {
            self::$instance = $this;
        }
    }

    public function __get(string $name): mixed
    {
        return $this->env->$name ?? $this->$name;
    }

    public function __set(string $name, mixed $value): void
    {
        if (isset($this->env->$name)) {
            throw new InvalidArgumentException("$name already is set!");
        }

        $this->env->$name = $value;
    }

    /**
     * @param string $key
     * @param object $array
     * 
     * @return bool
     */
    private function keyExistsInObject(string $key, object|array $object): bool
    {
        foreach ($object as $object_key => $value) {
            if (is_array($value) && $this->keyExistsInObject($key, $value)) {
                return true;
            }

            if ($object_key === $key) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return self
     */
    public static function get(): self
    {
        if (!isset(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }
}
