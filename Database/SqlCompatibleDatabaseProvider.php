<?php

namespace Database;

use PDO;
use Core\Database\DatabaseInterface;
use Core\Env;

abstract class SqlCompatibleDatabaseProvider implements DatabaseInterface
{
    protected $host;
    protected $port;
    protected $username;
    protected $password;
    protected $database;
    protected $connection;

    public function __construct()
    {
        $this->host = Env::get()->HOST;
        $this->username = Env::get()->USERNAME;
        $this->password = Env::get()->PASSWORD;
        $this->database = Env::get()->DATABASE;
        $this->port = Env::get()->PORTt;

    }

    public function connect()
    {
        $dsn = $this->getDsn();
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            // Add additional connection options here if needed
        ];

        $this->connection = new PDO($dsn, $this->username, $this->password, $options);
    }

    abstract protected function getDsn();

    public function query($sql)
    {
        return $this->connection->query($sql);
    }

    public function close()
    {
        unset($this->connection);
    }
}
