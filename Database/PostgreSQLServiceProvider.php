<?php

namespace Database;

class PostgreSQLServiceProvider extends SqlCompatibleDatabaseProvider
{
    protected function getDsn()
    {
        return "pgsql:host={$this->host};dbname={$this->database}";
    }
}
