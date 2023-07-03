<?php

namespace Database;

class MySQLServiceProvider extends SqlCompatibleDatabaseProvider
{
    protected function getDsn()
    {
        return "mysql:host={$this->host};dbname={$this->database}";
    }
}
