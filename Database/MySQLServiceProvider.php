<?php

namespace Database;

class MySQLServiceProvider extends SqlCompatibleDatabaseProvider
{
    protected function getDsn()
    {
        return "mysql:host={$this->host};port={$this->port};dbname={$this->database}";
    }
}
