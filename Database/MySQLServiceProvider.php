<?php

namespace Database;

use Core\Database\DatabaseInterface;


class MySQLServiceProvider extends SqlCompatibleDatabaseProvider
{
    protected function getDsn()
    {
        return "mysql:host={$this->host};dbname={$this->database}";
    }
}
