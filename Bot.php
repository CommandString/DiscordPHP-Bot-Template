<?php

use Commands\Ping;

use function Core\discord;

const BOT_ROOT = __DIR__;
require_once __DIR__ . '/Bootstrap/Requires.php';
require_once __DIR__ . '/Bootstrap/Config.php';

$reflection = new ReflectionClass(Ping::class);

discord()->run(); // Run the bot
