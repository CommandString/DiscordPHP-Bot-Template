<?php

namespace Database;

use Core\Database\DatabaseInterface;

class MongoDBServiceProvider implements DatabaseInterface
{
    private $host;
    private $port;
    private $database;
    private $collection;
    private $connection;

    public function __construct($host, $port, $database, $collection)
    {
        $this->host = $host;
        $this->port = $port;
        $this->database = $database;
        $this->collection = $collection;
    }

    public function connect()
    {
        $this->connection = new \MongoDB\Client("mongodb://{$this->host}:{$this->port}");
    }

    public function query($filter)
    {
        $collection = $this->connection->selectCollection($this->database, $this->collection);
        return $collection->findOne($filter);
    }

    public function close()
    {
        // MongoDB connection doesn't need to be explicitly closed
    }
}
