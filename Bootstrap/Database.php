<?php

use Core\System;
use Database\MySQLServiceProvider;

$database = new MySQLServiceProvider();
$database->connect();

System::get()->db = $database;
