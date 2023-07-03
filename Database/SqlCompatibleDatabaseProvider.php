<?php

namespace Database;

use PDO;

use Core\Database\DatabaseInterface;


abstract class SqlCompatibleDatabaseProvider implements DatabaseInterface
{
    protected $host;
    protected $username;
    protected $password;
    protected $database;

    protected $connection;

    public function __construct($host, $username, $password, $database)
    {
        $this->host = $host;
        $this->username = $username;
        $this->password = $password;
        $this->database = $database;
    }

    public function connect()
    {
        $this->connection = new PDO($this->getDsn(), $this->username, $this->password);
        // TODO Additional connection settings and error handling must added here
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
