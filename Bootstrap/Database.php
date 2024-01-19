<?php

use Core\Env;
use Database\MySQLServiceProvider;

$database = new MySQLServiceProvider();
$database->connect();
Env::get()->db = $database;
