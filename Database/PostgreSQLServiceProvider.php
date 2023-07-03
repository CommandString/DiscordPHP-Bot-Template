<?php

namespace Database;

use Core\Database\DatabaseInterface;


class PostgreSQLServiceProvider extends SqlCompatibleDatabaseProvider
{
    protected function getDsn()
    {
        return "pgsql:host={$this->host};dbname={$this->database}";
    }
}
