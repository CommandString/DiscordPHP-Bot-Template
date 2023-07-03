<?php

namespace Database;

class SqliteServiceProvider extends SqlCompatibleDatabaseProvider
{
    protected function getDsn()
    {
        return "sqlite:{$this->database}";
    }
}
