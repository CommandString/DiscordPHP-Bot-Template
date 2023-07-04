<?php

namespace Core;

use Core\Database\DatabaseInterface;
use Core\Commands\CommandCollection;

/**
 * @property DatabaseInterface $db
 * @property CommandCollection $cmdCollection
 */
class System
{
    protected static ?self $instance = null;

    public static function get()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }
}
