<?php

use Core\Manager\PrefixManager;
use Core\Env;

$prefixManager = new PrefixManager(Env::get()->db);

Env::get()->prefixManager = $prefixManager;
