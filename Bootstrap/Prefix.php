<?php

use Core\Manager\PrefixManager;
use Core\Env;
use Core\Manager\JsonPrefixManager;

$prefixManager = new PrefixManager(Env::get()->db);
// $prefixManager = new JsonPrefixManager("guild-prefixes.json");

Env::get()->prefixManager = $prefixManager;
